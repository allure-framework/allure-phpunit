<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use PHPUnit\Event\Code\TestMethod;
use Qameta\Allure\Allure;
use Qameta\Allure\AllureLifecycleInterface;
use Qameta\Allure\Model\FixtureResult;
use Qameta\Allure\Model\ResultFactoryInterface;
use Qameta\Allure\Model\Status;
use Qameta\Allure\Model\StatusDetails;
use Qameta\Allure\Model\TestResult;
use Qameta\Allure\PHPUnit\Setup\ThreadDetectorInterface;
use Qameta\Allure\PHPUnit\AllureAdapterInterface;
use Qameta\Allure\Setup\StatusDetectorInterface;
use RuntimeException;

use function class_exists;
use function is_int;

/**
 * @internal
 */
final class TestLifecycle implements TestLifecycleInterface
{
    private ?TestInfo $currentTest = null;

    private ?string $currentClassName = null;

    public function __construct(
        private readonly AllureLifecycleInterface $lifecycle,
        private readonly ResultFactoryInterface $resultFactory,
        private readonly StatusDetectorInterface $statusDetector,
        private readonly ThreadDetectorInterface $threadDetector,
        private readonly AllureAdapterInterface $adapter,
        private readonly TestUpdaterInterface $testUpdater,
    ) {
    }

    #[\Override]
    public function create(): self
    {
        $containerResult = $this->resultFactory->createContainer();
        $this->lifecycle->startContainer($containerResult);

        $testResult = $this->resultFactory->createTest();
        $this->lifecycle->scheduleTest($testResult, $containerResult->getUuid());

        $this->adapter->registerStart($containerResult, $testResult, $this->getCurrentTest());

        return $this;
    }

    #[\Override]
    public function updateInfo(): self
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $this->testUpdater->setInfo($testResult, $this->getCurrentTest()),
            $this->adapter->getTestId($this->getCurrentTest()),
        );

        return $this;
    }

    #[\Override]
    public function start(): self
    {
        $this->lifecycle->startTest(
            $this->adapter->getTestId($this->getCurrentTest()),
        );
        $this->adapter->markTestStarted($this->getCurrentTest());

        return $this;
    }

    #[\Override]
    public function stop(): self
    {
        $info = $this->getCurrentTest();
        if ($this->adapter->wasTestWritten($info)) {
            return $this;
        }

        $this->lifecycle->stopTest(
            $this->adapter->getTestId($info),
        );
        $this->lifecycle->stopContainer(
            $this->adapter->getContainerId($info),
        );

        return $this;
    }

    #[\Override]
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

    #[\Override]
    public function write(): self
    {
        $info = $this->getCurrentTest();
        if ($this->adapter->wasTestWritten($info)) {
            return $this;
        }

        $this->lifecycle->writeTest(
            $this->adapter->getTestId($info),
        );
        $this->lifecycle->writeContainer(
            $this->adapter->getContainerId($info),
        );
        $this->adapter->markTestWritten($info);

        return $this;
    }

    #[\Override]
    public function updateStatus(?string $message = null, ?Status $status = null): self
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $this->testUpdater->setStatus($testResult, $message, $status),
            $this->adapter->getTestId($this->getCurrentTest()),
        );

        return $this;
    }

    #[\Override]
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

    #[\Override]
    public function switchTo(TestMethod $test): self
    {
        $thread = $this->threadDetector->getThread();
        $this->lifecycle->switchThread($thread);

        $this->currentClassName = null;
        $this->currentTest = $this->buildTestInfo(
            $test,
            $this->threadDetector->getHost(),
            $thread,
        );

        return $this;
    }

    #[\Override]
    public function reset(): self
    {
        $this->adapter->resetLastException();

        return $this;
    }

    #[\Override]
    public function ensureClassContainer(string $className): self
    {
        $this->currentClassName = $className;
        $this->currentTest = null;

        if ($this->adapter->getClassContainerId($className) !== null) {
            return $this;
        }

        $containerResult = $this->resultFactory->createContainer();
        $this->lifecycle->startContainer($containerResult);
        $this->adapter->registerClassContainer($className, $containerResult->getUuid());

        return $this;
    }

    #[\Override]
    public function writeClassContainer(?string $className = null): self
    {
        $className ??= $this->currentClassName;
        if ($className === null) {
            return $this;
        }

        $containerId = $this->adapter->getClassContainerId($className);
        if ($containerId === null) {
            return $this;
        }

        $this->completeHookFixtureSuccess();
        $this->lifecycle->stopContainer($containerId);
        $this->lifecycle->writeContainer($containerId);
        $this->adapter->clearClassContainer($className);

        return $this;
    }

    #[\Override]
    public function startBeforeHookFixture(string $hookName): self
    {
        return $this->startHookFixture($hookName, before: true);
    }

    #[\Override]
    public function startAfterHookFixture(string $hookName): self
    {
        return $this->startHookFixture($hookName, before: false);
    }

    #[\Override]
    public function completeHookFixtureSuccess(): self
    {
        $uuid = $this->adapter->getActiveFixtureUuid();
        if ($uuid === null) {
            return $this;
        }

        $this->lifecycle->updateFixture(
            static fn (FixtureResult $fixture) => $fixture->setStatus(Status::passed()),
            $uuid,
        );
        $this->lifecycle->stopFixture($uuid);
        $this->adapter->clearActiveFixture();

        return $this;
    }

    #[\Override]
    public function completeHookFixtureFailure(
        Status $status,
        ?string $message = null,
        ?string $trace = null,
    ): self {
        $uuid = $this->adapter->getActiveFixtureUuid();
        $hookName = $this->adapter->getActiveHookName() ?? 'hook';
        if ($uuid === null) {
            return $this;
        }

        $prefixed = HookFailureMessage::format($hookName, $message);

        $this->lifecycle->updateFixture(
            static function (FixtureResult $fixture) use ($status, $prefixed, $trace): void {
                $fixture
                    ->setStatus($status)
                    ->setStatusDetails(
                        (new StatusDetails())
                            ->setMessage($prefixed)
                            ->setTrace($trace),
                    );
            },
            $uuid,
        );
        $this->lifecycle->stopFixture($uuid);

        if (!$this->adapter->hasEmittedHookGlobalError($uuid)) {
            Allure::globalError($prefixed, $trace);
            $this->adapter->markHookGlobalErrorEmitted($uuid);
        }

        $this->adapter->clearActiveFixture();

        return $this;
    }

    #[\Override]
    public function finalizeWithoutFinished(): self
    {
        if ($this->currentTest === null) {
            return $this;
        }

        $info = $this->getCurrentTest();
        if ($this->adapter->wasTestWritten($info)) {
            $this->adapter->clearPendingFinalize();

            return $this;
        }

        $this->completeHookFixtureSuccess();
        $this->lifecycle->stopTest($this->adapter->getTestId($info));
        $this->lifecycle->stopContainer($this->adapter->getContainerId($info));
        $this->updateRunInfo();
        $this->write();
        $this->adapter->clearPendingFinalize();

        return $this;
    }

    #[\Override]
    public function markPendingFinalize(): self
    {
        if ($this->currentTest !== null) {
            $this->adapter->markPendingFinalize($this->getCurrentTest());
        }

        return $this;
    }

    #[\Override]
    public function flushPendingFinalize(): self
    {
        $pending = $this->adapter->getPendingFinalizeTest();
        if ($pending === null) {
            return $this;
        }

        $previous = $this->currentTest;
        $this->currentTest = $pending;
        $this->finalizeWithoutFinished();
        $this->currentTest = $previous;

        return $this;
    }

    #[\Override]
    public function completeOrphanHookFailure(
        Status $status,
        ?string $message = null,
        ?string $trace = null,
    ): self {
        if ($this->currentTest === null) {
            return $this;
        }

        if ($this->adapter->getActiveFixtureUuid() === null) {
            return $this;
        }

        if ($this->adapter->wasTestStarted($this->getCurrentTest())) {
            return $this;
        }

        $this->completeHookFixtureFailure($status, $message, $trace);
        $this->markPendingFinalize();

        return $this;
    }

    private function startHookFixture(string $hookName, bool $before): self
    {
        $this->completeHookFixtureSuccess();

        $fixture = $this->resultFactory
            ->createFixture()
            ->setName($hookName);

        $containerId = $this->resolveContainerId();
        if ($before) {
            $this->lifecycle->startBeforeFixture($fixture, $containerId);
        } else {
            $this->lifecycle->startAfterFixture($fixture, $containerId);
        }

        $this->adapter->setActiveFixture($fixture->getUuid(), $hookName);

        return $this;
    }

    private function resolveContainerId(): string
    {
        if ($this->currentClassName !== null) {
            return $this->adapter->getClassContainerId($this->currentClassName)
                ?? throw new RuntimeException("Class container is not set for {$this->currentClassName}");
        }

        return $this->adapter->getContainerId($this->getCurrentTest());
    }

    private function getCurrentTest(): TestInfo
    {
        return $this->currentTest ?? throw new RuntimeException("Current test is not set");
    }

    private function buildTestInfo(TestMethod $test, ?string $host = null, ?string $thread = null): TestInfo
    {
        $className = $test->className();
        $methodName = $test->methodName();

        $testData = $test->testData();
        $dataSetName = $testData->hasDataFromDataProvider()
            ? $testData->dataFromDataProvider()->dataSetName()
            : null;

        /** @psalm-suppress MixedArgument */
        return new TestInfo(
            test: $test->nameWithClass(),
            class: class_exists($className) ? $className : null,
            method: $methodName,
            dataLabel: is_int($dataSetName) ? "#" . $dataSetName : $dataSetName,
            host: $host,
            thread: $thread,
        );
    }
}
