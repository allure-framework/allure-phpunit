<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use Qameta\Allure\AllureLifecycleInterface;
use Qameta\Allure\Model\ResultFactoryInterface;
use Qameta\Allure\Model\Status;
use Qameta\Allure\Model\TestResult;
use Qameta\Allure\PHPUnit\Setup\ThreadDetectorInterface;
use Qameta\Allure\PHPUnit\AllureAdapterInterface;
use Qameta\Allure\Setup\StatusDetectorInterface;
use RuntimeException;

use function array_pad;
use function class_exists;
use function explode;
use function preg_match;

/**
 * @internal
 */
final class TestLifecycle implements TestLifecycleInterface
{
    private ?TestInfo $currentTest = null;

    public function __construct(
        private readonly AllureLifecycleInterface $lifecycle,
        private readonly ResultFactoryInterface $resultFactory,
        private readonly StatusDetectorInterface $statusDetector,
        private readonly ThreadDetectorInterface $threadDetector,
        private readonly AllureAdapterInterface $adapter,
        private readonly TestUpdaterInterface $testUpdater,
    ) {
    }

    public function create(): self
    {
        $containerResult = $this->resultFactory->createContainer();
        $this->lifecycle->startContainer($containerResult);

        $testResult = $this->resultFactory->createTest();
        $this->lifecycle->scheduleTest($testResult, $containerResult->getUuid());

        $this->adapter->registerStart($containerResult, $testResult, $this->getCurrentTest());

        return $this;
    }

    public function updateInfo(): self
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $this->testUpdater->setInfo($testResult, $this->getCurrentTest()),
            $this->adapter->getTestId($this->getCurrentTest()),
        );

        return $this;
    }

    public function start(): self
    {
        $this->lifecycle->startTest(
            $this->adapter->getTestId($this->getCurrentTest()),
        );

        return $this;
    }

    public function stop(): self
    {
        $this->lifecycle->stopTest(
            $this->adapter->getTestId($this->getCurrentTest()),
        );
        $this->lifecycle->stopContainer(
            $this->adapter->getContainerId($this->getCurrentTest()),
        );

        return $this;
    }

    public function updateRunInfo(): self
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $this->testUpdater->setRunInfo(
                $testResult,
                $this->adapter->registerRun($testResult, $this->getCurrentTest()),
            ),
            $this->adapter->getTestId($this->getCurrentTest()),
        );

        return $this;
    }

    public function write(): self
    {
        $this->lifecycle->writeTest(
            $this->adapter->getTestId($this->getCurrentTest()),
        );
        $this->lifecycle->writeContainer(
            $this->adapter->getContainerId($this->getCurrentTest()),
        );

        return $this;
    }

    public function updateStatus(?string $message = null, ?Status $status = null): self
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $this->testUpdater->setStatus($testResult, $message, $status),
            $this->adapter->getTestId($this->getCurrentTest()),
        );

        return $this;
    }

    public function updateDetectedStatus(
        ?string $message = null,
        ?Status $status = null,
        ?Status $overrideStatus = null,
    ): self {
        $exception = $this->adapter->getLastException();
        if (!isset($exception)) {
            return $this->updateStatus($message, $status);
        }

        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $this->testUpdater->setDetectedStatus(
                $testResult,
                $this->statusDetector,
                $exception,
                $overrideStatus,
            ),
            $this->adapter->getTestId($this->getCurrentTest()),
        );

        return $this;
    }

    public function switchTo(string $test): self
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

    public function reset(): self
    {
        $this->adapter->resetLastException();

        return $this;
    }

    private function getCurrentTest(): TestInfo
    {
        return $this->currentTest ?? throw new RuntimeException("Current test is not set");
    }

    private function buildTestInfo(string $test, ?string $host = null, ?string $thread = null): TestInfo
    {
        /** @var list<string> $matches */
        $classAndMethodMatchResult = preg_match(
            '#^(\S+)(.*)$#',
            $test,
            $matches,
        );
        [$classAndMethod, $dataSetInfo] = 1 === $classAndMethodMatchResult
            ? [$matches[1] ?? null, $matches[2] ?? null]
            : [$test, null];
        $dataLabelMatchResult = isset($dataSetInfo)
            ? preg_match(
                '/^\s+with\s+data\s+set\s+(?:(#\d+)|"(.*)")$/',
                $dataSetInfo,
                $matches,
            )
            : false;
        $dataLabel = 1 === $dataLabelMatchResult
            ? $matches[2] ?? $matches[1] ?? null
            : null;
        if ('' === $dataLabel) {
            $dataLabel = null;
        }

        /** @psalm-suppress PossiblyUndefinedArrayOffset */
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
