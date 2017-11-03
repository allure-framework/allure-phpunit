<?php

namespace Yandex\Allure\Adapter;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestSuite;
use Exception;
use org\bovigo\vfs\vfsStream;
use Yandex\Allure\Adapter\Event\TestCaseBrokenEvent;
use Yandex\Allure\Adapter\Event\TestCaseCanceledEvent;
use Yandex\Allure\Adapter\Event\TestCaseFailedEvent;
use Yandex\Allure\Adapter\Event\TestCasePendingEvent;
use Yandex\Allure\Adapter\Support\MockedLifecycle;

const EXCEPTION_MESSAGE = 'test-exception-message';
const ROOT_DIRECTORY = 'test-root-directory';
const TEST_DIRECTORY = 'test-directory';

class AllureAdapterTest extends TestCase
{

    /**
     * @var MockedLifecycle
     */
    private $mockedLifecycle;

    /**
     * @var AllureAdapter
     */
    private $allureAdapter;

    protected function setUp()
    {
        parent::setUp();
        $this->mockedLifecycle = new MockedLifecycle();
        Allure::setLifecycle($this->getMockedLifecycle());
        $this->allureAdapter = new AllureAdapter('test-output-directory', true);
    }

    public function testPrepareOutputDirectory()
    {
        $rootDirectory = vfsStream::setup(ROOT_DIRECTORY);
        $this->assertFalse($rootDirectory->hasChild(TEST_DIRECTORY));
        $newDirectoryPath = vfsStream::url(ROOT_DIRECTORY) . DIRECTORY_SEPARATOR . TEST_DIRECTORY;
        Model\Provider::setOutputDirectory(null);
        new AllureAdapter($newDirectoryPath, true);
        $this->assertTrue($rootDirectory->hasChild(TEST_DIRECTORY));
        $this->assertEquals($newDirectoryPath, Model\Provider::getOutputDirectory());
    }

    public function testAddError()
    {
        $exception = $this->getException();
        $time = $this->getTime();
        $this->getAllureAdapter()->addError($this, $exception, $time);
        $events = $this->getMockedLifecycle()->getEvents();
        $event = new TestCaseBrokenEvent();
        $event->withException($exception)->withMessage(EXCEPTION_MESSAGE);
        $this->assertEquals(1, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseBrokenEvent', $events[0]);
        $this->assertEquals($event, $events[0]);
    }

    public function testAddFailure()
    {
        $exception = new AssertionFailedError(EXCEPTION_MESSAGE);
        $time = $this->getTime();
        $this->getAllureAdapter()->addFailure($this, $exception, $time);
        $events = $this->getMockedLifecycle()->getEvents();
        $event = new TestCaseFailedEvent();
        $event->withException($exception)->withMessage(EXCEPTION_MESSAGE);
        $this->assertEquals(1, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseFailedEvent', $events[0]);
        $this->assertEquals($event, $events[0]);
    }

    public function testAddIncompleteTest()
    {
        $exception = $this->getException();
        $time = $this->getTime();
        $this->getAllureAdapter()->addIncompleteTest($this, $exception, $time);
        $this->pendingTestCaseEventAssertions($exception);
    }

    private function pendingTestCaseEventAssertions(\Exception $exception)
    {
        $events = $this->getMockedLifecycle()->getEvents();
        $event = new TestCasePendingEvent();
        $event->withException($exception);
        $this->assertEquals(1, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCasePendingEvent', $events[0]);
        $this->assertEquals($event, $events[0]);
    }

    public function testAddRiskyTest()
    {
        $exception = $this->getException();
        $time = $this->getTime();
        $this->getAllureAdapter()->addRiskyTest($this, $exception, $time);
        $this->pendingTestCaseEventAssertions($exception);
    }

    public function testAddSkippedTest()
    {
        $exception = $this->getException();
        $time = $this->getTime();
        $this->getAllureAdapter()->addSkippedTest($this, $exception, $time);
        $events = $this->getMockedLifecycle()->getEvents();
        $event = new TestCaseCanceledEvent();
        $event->withException($exception)->withMessage(EXCEPTION_MESSAGE);
        $this->assertEquals(3, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseStartedEvent', $events[0]);
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseCanceledEvent', $events[1]);
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseFinishedEvent', $events[2]);
        $this->assertEquals($event, $events[1]);
    }

    public function testStartTestSuite()
    {
        $this->getAllureAdapter()->startTestSuite($this->getTestSuite());
        $events = $this->getMockedLifecycle()->getEvents();
        $this->assertEquals(1, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestSuiteStartedEvent', $events[0]);
    }

    public function testEndTestSuite()
    {
        $this->getAllureAdapter()->endTestSuite($this->getTestSuite());
        $events = $this->getMockedLifecycle()->getEvents();
        $this->assertEquals(1, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestSuiteFinishedEvent', $events[0]);
    }

    public function testStartTest()
    {
        $this->getAllureAdapter()->startTestSuite($this->getTestSuite()); //Is needed to set $adapter->suiteName field
        $this->getAllureAdapter()->startTest($this);
        $events = $this->getMockedLifecycle()->getEvents();
        $this->assertEquals(2, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestSuiteStartedEvent', $events[0]);
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseStartedEvent', $events[1]);
    }

    public function testEndTest()
    {
        $this->getAllureAdapter()->endTest($this, $this->getTime());
        $events = $this->getMockedLifecycle()->getEvents();
        $this->assertEquals(1, sizeof($events));
        $this->assertInstanceOf('\Yandex\Allure\Adapter\Event\TestCaseFinishedEvent', $events[0]);
    }

    private function getMockedLifecycle()
    {
        return $this->mockedLifecycle;
    }

    private function getAllureAdapter()
    {
        return $this->allureAdapter;
    }

    private function getException()
    {
        return new Exception(EXCEPTION_MESSAGE);
    }

    private function getTime()
    {
        return (float)time();
    }
    
    private function getTestSuite()
    {
        return new TestSuite(__CLASS__);
    }

}
