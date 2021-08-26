<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use Qameta\Allure\AllureLifecycleInterface;
use Qameta\Allure\Model\ResultFactoryInterface;
use Qameta\Allure\Model\Status;
use Qameta\Allure\Model\TestResult;
use Qameta\Allure\PHPUnit\Setup\ThreadDetectorInterface;
use Qameta\Allure\Setup\StatusDetectorInterface;
use RuntimeException;
use Throwable;

use function array_pad;
use function class_exists;
use function explode;
use function preg_match;

/**
 * @internal
 */
final class AllureAdapter
{

    private ?TestInfo $currentTest = null;

    public function __construct(
        private AllureLifecycleInterface $lifecycle,
        private ResultFactoryInterface $resultFactory,
        private StatusDetectorInterface $statusDetector,
        private ThreadDetectorInterface $threadDetector,
        private TestRegistryInterface $testRegistry,
        private TestUpdaterInterface $testUpdater,
    ) {}

    public function createTest(): self
    {
        $testResult = $this->resultFactory->createTest();
        $this->lifecycle->scheduleTest($testResult);
        $this->testRegistry->registerTest($testResult, $this->getCurrentTest());

        return $this;
    }

    public function updateInitialInfo(): self
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $this->testUpdater->setInfo($testResult, $this->getCurrentTest()),
            $this->testRegistry->getTestId($this->getCurrentTest()),
        );

        return $this;
    }

    public function start(): self
    {
        $this->lifecycle->startTest(
            $this->testRegistry->getTestId($this->getCurrentTest()),
        );

        return $this;
    }

    public function stop(): self
    {
        $this->lifecycle->stopTest(
            $this->testRegistry->getTestId($this->getCurrentTest()),
        );

        return $this;
    }

    public function updateRunInfo(): self
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $this->testUpdater->setRunInfo(
                $testResult,
                $this->testRegistry->registerRun($testResult, $this->getCurrentTest()),
            ),
            $this->testRegistry->getTestId($this->getCurrentTest()),
        );

        return $this;
    }

    public function write(): self
    {
        $this->lifecycle->writeTest(
            $this->testRegistry->getTestId($this->getCurrentTest()),
        );

        return $this;
    }

    public function updateStatus(?string $message = null, ?Status $status = null): self
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $this->testUpdater->setStatus($testResult, $message, $status),
            $this->testRegistry->getTestId($this->getCurrentTest()),
        );

        return $this;
    }

    public function updateDetectedStatus(Throwable $exception): self
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $this->testUpdater->setDetectedStatus(
                $testResult,
                $this->statusDetector,
                $exception,
            ),
            $this->testRegistry->getTestId($this->getCurrentTest()),
        );

        return $this;
    }

    public function switchToTest(string $test): self
    {
        $thread = $this->threadDetector->getThread();
        $this->lifecycle->switchThread($thread);

        $this->currentTest = $this->buildTestInfo(
            $test,
            $this->threadDetector->getHost(),
            $thread,
        );

        return $this;
    }

    private function getCurrentTest(): TestInfo
    {
        return $this->currentTest ?? throw new RuntimeException("Current test is not set");
    }

    private function buildTestInfo(string $test, ?string $host = null, ?string $thread = null): TestInfo
    {
        $dataLabelMatchResult = preg_match(
            '#^([^\s]+)\s+with\s+data\s+set\s+"(.*)"\s+\(.+\)$#',
            $test,
            $matches,
        );

        /** @var list<string> $matches */
        if (1 === $dataLabelMatchResult) {
            $classAndMethod = $matches[1] ?? null;
            $dataLabel = $matches[2] ?? '?';
        } else {
            $classAndMethod = $test;
            $dataLabel = null;
        }

        [$class, $method] = isset($classAndMethod)
            ? array_pad(explode('::', $classAndMethod, 2), 2, null)
            : [null, null];

        /** @psalm-suppress MixedArgument */
        return new TestInfo(
            test: $test,
            class: isset($class) && class_exists($class) ? $class : null,
            method: $method,
            dataLabel: $dataLabel,
            host: $host,
            thread: $thread,
        );
    }
}
