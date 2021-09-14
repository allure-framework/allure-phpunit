<?php

namespace Yandex\Allure\Adapter;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use Exception;
use org\bovigo\vfs\vfsStream;
use Throwable;
use Yandex\Allure\Adapter\Event\TestCaseBrokenEvent;
use Yandex\Allure\Adapter\Event\TestCaseCanceledEvent;
use Yandex\Allure\Adapter\Event\TestCaseFailedEvent;
use Yandex\Allure\Adapter\Event\TestCasePendingEvent;
use Yandex\Allure\Adapter\Support\MockedLifecycle;
use Yandex\Allure\PhpUnit\AllurePhpUnit;

class AllureAdapterTest extends TestCase
{

    private const EXCEPTION_MESSAGE = 'test-exception-message';
    private const ROOT_DIRECTORY = 'test-root-directory';
    private const TEST_DIRECTORY = 'test-directory';

    /**
     * @var MockedLifecycle
     */
    private $mockedLifecycle;

    /**
     * @var AllurePhpUnit
     */
    private $allureAdapter;

    protected function setUp(): void
    {
        date_default_timezone_set('UTC');
        $this->mockedLifecycle = new MockedLifecycle();
        Allure::setLifecycle($this->getMockedLifecycle());
        $this->allureAdapter = new AllurePhpUnit('test-output-directory', true);
    }

    public function testPrepareOutputDirectory(): void
    {
        $rootDirectory = vfsStream::setup(self::ROOT_DIRECTORY);
        $this->assertFalse($rootDirectory->hasChild(self::TEST_DIRECTORY));
        $newDirectoryPath = vfsStream::url(self::ROOT_DIRECTORY) . DIRECTORY_SEPARATOR . self::TEST_DIRECTORY;
        Model\Provider::setOutputDirectory(null);
        new AllurePhpUnit($newDirectoryPath, true);
        $this->assertTrue($rootDirectory->hasChild(self::TEST_DIRECTORY));
        $this->assertEquals($newDirectoryPath, Model\Provider::getOutputDirectory());
    }

    public function testAddError(): void
    {
        $exception = $this->getException();
        $time = $this->getTime();
        $this->getAllureAdapter()->addError($this, $exception, $time);
        $events = $this->getMockedLifecycle()->getEvents();
        $event = new TestCaseBrokenEvent();
        $event->withException($exception)->withMessage(self::EXCEPTION_MESSAGE);
        $this->assertEquals(1, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseBrokenEvent', $events[0]);
        $this->assertEquals($event, $events[0]);
    }

    public function testAddWarning(): void
    {
        $warning = new Warning(self::EXCEPTION_MESSAGE);
        $time = $this->getTime();
        $this->getAllureAdapter()->addWarning($this, $warning, $time);
        $events = $this->getMockedLifecycle()->getEvents();
        $event = new TestCaseFailedEvent();
        $event->withException($warning)->withMessage(self::EXCEPTION_MESSAGE);
        $this->assertEquals(1, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseFailedEvent', $events[0]);
        $this->assertEquals($event, $events[0]);
    }
    
    public function testAddFailure(): void
    {
        $exception = new AssertionFailedError(self::EXCEPTION_MESSAGE);
        $time = $this->getTime();
        $this->getAllureAdapter()->addFailure($this, $exception, $time);
        $events = $this->getMockedLifecycle()->getEvents();
        $event = new TestCaseFailedEvent();
        $event->withException($exception)->withMessage(self::EXCEPTION_MESSAGE);
        $this->assertEquals(1, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseFailedEvent', $events[0]);
        $this->assertEquals($event, $events[0]);
    }

    public function testAddIncompleteTest(): void
    {
        $exception = $this->getException();
        $time = $this->getTime();
        $this->getAllureAdapter()->addIncompleteTest($this, $exception, $time);
        $this->pendingTestCaseEventAssertions($exception);
    }

    private function pendingTestCaseEventAssertions(\Exception $exception): void
    {
        $events = $this->getMockedLifecycle()->getEvents();
        $event = new TestCasePendingEvent();
        $event->withException($exception);
        $this->assertEquals(1, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCasePendingEvent', $events[0]);
        $this->assertEquals($event, $events[0]);
    }

    public function testAddRiskyTest(): void
    {
        $exception = $this->getException();
        $time = $this->getTime();
        $this->getAllureAdapter()->addRiskyTest($this, $exception, $time);
        $this->pendingTestCaseEventAssertions($exception);
    }

    public function testAddSkippedTest(): void
    {
        $exception = $this->getException();
        $time = $this->getTime();
        $this->getAllureAdapter()->addSkippedTest($this, $exception, $time);
        $events = $this->getMockedLifecycle()->getEvents();
        $event = new TestCaseCanceledEvent();
        $event->withException($exception)->withMessage(self::EXCEPTION_MESSAGE);
        $this->assertEquals(3, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseStartedEvent', $events[0]);
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseCanceledEvent', $events[1]);
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseFinishedEvent', $events[2]);
        $this->assertEquals($event, $events[1]);
    }

    public function testStartTestSuite(): void
    {
        $this->getAllureAdapter()->startTestSuite($this->getTestSuite());
        $events = $this->getMockedLifecycle()->getEvents();
        $this->assertEquals(1, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestSuiteStartedEvent', $events[0]);
    }

    public function testEndTestSuite(): void
    {
        $this->getAllureAdapter()->endTestSuite($this->getTestSuite());
        $events = $this->getMockedLifecycle()->getEvents();
        $this->assertEquals(1, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestSuiteFinishedEvent', $events[0]);
    }

    public function testStartTest(): void
    {
        $this->getAllureAdapter()->startTestSuite($this->getTestSuite()); //Is needed to set $adapter->suiteName field
        $this->getAllureAdapter()->startTest($this);
        $events = $this->getMockedLifecycle()->getEvents();
        $this->assertEquals(2, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestSuiteStartedEvent', $events[0]);
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseStartedEvent', $events[1]);
    }

    public function testEndTest(): void
    {
        $this->getAllureAdapter()->endTest($this, $this->getTime());
        $events = $this->getMockedLifecycle()->getEvents();
        $this->assertEquals(1, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseFinishedEvent', $events[0]);
    }

    private function getMockedLifecycle(): MockedLifecycle
    {
        return $this->mockedLifecycle;
    }

    private function getAllureAdapter(): AllurePhpUnit
    {
        return $this->allureAdapter;
    }

    private function getException(): Throwable
    {
        return new Exception(self::EXCEPTION_MESSAGE);
    }

    private function getTime(): int
    {
        return (float) time();
    }
    
    private function getTestSuite(): TestSuite
    {
        return new TestSuite(__CLASS__);
    }
}
