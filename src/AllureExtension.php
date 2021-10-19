<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit;

use LogicException;
use PHPUnit\Runner\AfterIncompleteTestHook;
use PHPUnit\Runner\AfterRiskyTestHook;
use PHPUnit\Runner\AfterSkippedTestHook;
use PHPUnit\Runner\AfterSuccessfulTestHook;
use PHPUnit\Runner\AfterTestErrorHook;
use PHPUnit\Runner\AfterTestFailureHook;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\AfterTestWarningHook;
use PHPUnit\Runner\BeforeTestHook;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleFactory;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleFactoryInterface;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;
use Qameta\Allure\PHPUnit\Setup\ConfiguratorInterface;
use Qameta\Allure\PHPUnit\Setup\DefaultConfigurator;

use function class_exists;
use function is_a;

use const DIRECTORY_SEPARATOR;

final class AllureExtension implements
    BeforeTestHook,
    AfterTestHook,
    AfterTestFailureHook,
    AfterTestErrorHook,
    AfterIncompleteTestHook,
    AfterSkippedTestHook,
    AfterTestWarningHook,
    AfterRiskyTestHook,
    AfterSuccessfulTestHook
{
    private const DEFAULT_OUTPUT_DIRECTORY = 'build' . DIRECTORY_SEPARATOR . 'allure-results';

    private TestLifecycleInterface $testLifecycle;

    public function __construct(
        ?string $outputDirectory = null,
        string|ConfiguratorInterface|null $configurator = null,
        mixed ...$args,
    ) {
        if (!$configurator instanceof ConfiguratorInterface) {
            $configurator = $this->createConfigurator(
                $configurator ?? DefaultConfigurator::class,
                ...$args,
            );
        }
        $configurator->setupAllure($outputDirectory ?? self::DEFAULT_OUTPUT_DIRECTORY);
        $this->testLifecycle = $this->createTestLifecycleInterface($configurator);
    }

    private function createConfigurator(string $class, mixed ...$args): ConfiguratorInterface
    {
        return
            class_exists($class) &&
            is_a($class, ConfiguratorInterface::class, true)
            ? new $class(...$args)
            : throw new LogicException("Invalid configurator class: {$class}");
    }

    private function createTestLifecycleInterface(ConfiguratorInterface $configurator): TestLifecycleInterface
    {
        $testLifecycleFactory = $configurator instanceof TestLifecycleFactoryInterface
            ? $configurator
            : new TestLifecycleFactory();

        return $testLifecycleFactory->createTestLifecycle($configurator);
    }

    public function executeBeforeTest(string $test): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->reset()
            ->create()
            ->updateInfo()
            ->start();
    }

    public function executeAfterTest(string $test, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->stop()
            ->updateRunInfo()
            ->write();
    }

    public function executeAfterTestFailure(string $test, string $message, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateDetectedStatus($message, Status::failed(), Status::failed());
    }

    public function executeAfterTestError(string $test, string $message, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateDetectedStatus($message, Status::broken());
    }

    public function executeAfterIncompleteTest(string $test, string $message, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateStatus($message, Status::broken());
    }

    public function executeAfterSkippedTest(string $test, string $message, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateStatus($message, Status::skipped());
    }

    public function executeAfterTestWarning(string $test, string $message, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateStatus($message, Status::broken());
    }

    public function executeAfterRiskyTest(string $test, string $message, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateStatus($message, Status::failed());
    }

    public function executeAfterSuccessfulTest(string $test, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateStatus(status: Status::passed());
    }
}
