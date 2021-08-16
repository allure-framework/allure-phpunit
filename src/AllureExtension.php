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
use Qameta\Allure\Allure;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Internal\AllureAdapter;
use Qameta\Allure\PHPUnit\Internal\DefaultThreadDetector;
use Qameta\Allure\PHPUnit\Internal\TestRegistry;
use Qameta\Allure\PHPUnit\Internal\TestUpdater;
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

    private SharedTestStateInterface $sharedTestState;

    private AllureAdapter $adapter;

    public function __construct(
        ?string $outputDirectory = null,
        ?string $configuratorClass = null,
        mixed ...$args,
    ) {
        $configurator = $this->createConfigurator(
            $configuratorClass ?? DefaultConfigurator::class,
            ...$args,
        );
        $configurator->setupAllure($outputDirectory ?? self::DEFAULT_OUTPUT_DIRECTORY);
        $this->sharedTestState = $configurator->getSharedTestState() ?? SharedTestState::getInstance();
        $this->adapter = new AllureAdapter(
            $configurator->getAllureLifecycle() ?? Allure::getLifecycle(),
            $configurator->getResultFactory() ?? Allure::getResultFactory(),
            $configurator->getStatusDetector() ?? Allure::getStatusDetector(),
            $configurator->getThreadDetector() ?? new DefaultThreadDetector(),
            new TestRegistry(),
            new TestUpdater(),
        );
    }

    private function createConfigurator(string $class, mixed ...$args): ConfiguratorInterface
    {
        return
            class_exists($class) &&
            is_a($class, ConfiguratorInterface::class, true)
            ? new $class(...$args)
            : throw new LogicException("Invalid configurator class: {$class}");
    }

    public function executeBeforeTest(string $test): void
    {
        $this->sharedTestState->reset();
        $this
            ->adapter
            ->switchToTest($test)
            ->createTest()
            ->updateInitialInfo()
            ->start();
    }

    public function executeAfterTest(string $test, float $time): void
    {
        $this
            ->adapter
            ->switchToTest($test)
            ->stop()
            ->updateRunInfo()
            ->write();
    }

    public function executeAfterTestFailure(string $test, string $message, float $time): void
    {
        $this
            ->adapter
            ->switchToTest($test)
            ->updateStatus($message, Status::failed());
    }

    public function executeAfterTestError(string $test, string $message, float $time): void
    {
        $this->adapter->switchToTest($test);
        $exception = $this->sharedTestState->getLastException();
        isset($exception)
            ? $this->adapter->updateDetectedStatus($exception)
            : $this->adapter->updateStatus($message, Status::failed());
    }

    public function executeAfterIncompleteTest(string $test, string $message, float $time): void
    {
        $this
            ->adapter
            ->switchToTest($test)
            ->updateStatus($message, Status::broken());
    }

    public function executeAfterSkippedTest(string $test, string $message, float $time): void
    {
        $this
            ->adapter
            ->switchToTest($test)
            ->updateStatus($message, Status::skipped());
    }

    public function executeAfterTestWarning(string $test, string $message, float $time): void
    {
        $this
            ->adapter
            ->switchToTest($test)
            ->updateStatus($message, Status::broken());
    }

    public function executeAfterRiskyTest(string $test, string $message, float $time): void
    {
        $this
            ->adapter
            ->switchToTest($test)
            ->updateStatus($message, Status::failed());
    }

    public function executeAfterSuccessfulTest(string $test, float $time): void
    {
        $this
            ->adapter
            ->switchToTest($test)
            ->updateStatus(status: Status::passed());
    }
}
