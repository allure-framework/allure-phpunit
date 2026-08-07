<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Internal;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Allure;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\AllureAdapter;
use Qameta\Allure\PHPUnit\Internal\DefaultThreadDetector;
use Qameta\Allure\PHPUnit\Internal\TestLifecycle;
use Qameta\Allure\PHPUnit\Internal\TestUpdater;
use Qameta\Allure\PHPUnit\Test\Unit\Event\EventTestTrait;

use function file_get_contents;
use function glob;
use function is_array;
use function json_decode;
use function mkdir;
use function sys_get_temp_dir;
use function uniqid;

#[CoversClass(TestLifecycle::class)]
final class TestLifecycleHookFixtureTest extends TestCase
{
    use EventTestTrait;

    private string $outputDirectory;

    #[\Override]
    protected function setUp(): void
    {
        Allure::reset();
        AllureAdapter::reset();
        $this->outputDirectory = sys_get_temp_dir() . '/allure-phpunit-hook-' . uniqid('', true);
        mkdir($this->outputDirectory);
        Allure::getLifecycleConfigurator()->setOutputDirectory($this->outputDirectory);
    }

    #[\Override]
    protected function tearDown(): void
    {
        Allure::reset();
        AllureAdapter::reset();
    }

    public function testCompleteHookFixtureFailure_EmitsPrefixedGlobalAndStopsFixture(): void
    {
        $lifecycle = $this->createLifecycle();
        $test = $this->createTestMethod(
            class: self::class,
            methodName: 'testCompleteHookFixtureFailure_EmitsPrefixedGlobalAndStopsFixture',
        );
        $lifecycle->switchTo($test)->create()->startBeforeHookFixture('setUp');
        $lifecycle->completeHookFixtureFailure(Status::broken(), 'boom', 'trace-line');

        $globals = $this->readGlobalErrors();
        self::assertCount(1, $globals);
        self::assertSame('setUp failed: boom', $globals[0]['message'] ?? null);
        self::assertSame('trace-line', $globals[0]['trace'] ?? null);
    }

    public function testCompleteHookFixtureFailure_BlankMessage_UsesFallback(): void
    {
        $lifecycle = $this->createLifecycle();
        $test = $this->createTestMethod(
            class: self::class,
            methodName: 'testCompleteHookFixtureFailure_BlankMessage_UsesFallback',
        );
        $lifecycle->switchTo($test)->create()->startBeforeHookFixture('setUp');
        $lifecycle->completeHookFixtureFailure(Status::failed(), '   ', null);

        $globals = $this->readGlobalErrors();
        self::assertCount(1, $globals);
        self::assertSame('setUp failed', $globals[0]['message'] ?? null);
    }

    public function testCompleteHookFixtureFailure_CalledTwice_DoesNotDuplicateGlobal(): void
    {
        $lifecycle = $this->createLifecycle();
        $test = $this->createTestMethod(
            class: self::class,
            methodName: 'testCompleteHookFixtureFailure_CalledTwice_DoesNotDuplicateGlobal',
        );
        $lifecycle->switchTo($test)->create()->startBeforeHookFixture('setUp');

        $adapter = AllureAdapter::getInstance();
        $uuid = $adapter->getActiveFixtureUuid();
        self::assertNotNull($uuid);

        $lifecycle->completeHookFixtureFailure(Status::broken(), 'once', 't');
        // Simulate a second attempt against the same fixture UUID after clear.
        $adapter->setActiveFixture($uuid, 'setUp');
        $lifecycle->completeHookFixtureFailure(Status::broken(), 'twice', 't');

        $globals = $this->readGlobalErrors();
        self::assertCount(1, $globals);
        self::assertSame('setUp failed: once', $globals[0]['message'] ?? null);
    }

    private function createLifecycle(): TestLifecycle
    {
        return new TestLifecycle(
            Allure::getLifecycle(),
            Allure::getConfig()->getResultFactory(),
            Allure::getConfig()->getStatusDetector(),
            new DefaultThreadDetector(),
            AllureAdapter::getInstance(),
            new TestUpdater(Allure::getConfig()->getLinkTemplates()),
        );
    }

    /**
     * @return list<array{message?: string, trace?: string}>
     */
    private function readGlobalErrors(): array
    {
        $files = glob($this->outputDirectory . '/*-globals.json');
        if (!is_array($files)) {
            return [];
        }

        $errors = [];
        foreach ($files as $file) {
            /** @var array{errors?: list<array{message?: string, trace?: string}>} $data */
            $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            foreach ($data['errors'] ?? [] as $error) {
                $errors[] = $error;
            }
        }

        return $errors;
    }
}
