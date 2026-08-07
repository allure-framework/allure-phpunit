<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit;

use LogicException;
use Qameta\Allure\Model\ContainerResult;
use Qameta\Allure\Model\Parameter;
use Qameta\Allure\Model\TestResult;
use Qameta\Allure\PHPUnit\Internal\TestInfo;
use Qameta\Allure\PHPUnit\Internal\TestRunInfo;
use Qameta\Allure\PHPUnit\Internal\TestStartInfo;
use Throwable;

final class AllureAdapter implements AllureAdapterInterface
{
    private static ?AllureAdapterInterface $instance = null;

    /**
     * @var array<string, TestStartInfo>
     */
    private array $lastStarts = [];

    /**
     * @var array<string, TestRunInfo>
     */
    private array $lastRuns = [];

    /**
     * @var array<string, true>
     */
    private array $startedTests = [];

    /**
     * @var array<string, true>
     */
    private array $writtenTests = [];

    /**
     * @var array<string, string>
     */
    private array $classContainers = [];

    /**
     * @var array<string, true>
     */
    private array $emittedHookGlobals = [];

    private ?Throwable $lastException = null;

    private ?string $activeFixtureUuid = null;

    private ?string $activeHookName = null;

    private ?TestInfo $pendingFinalizeTest = null;

    private function __construct()
    {
    }

    public static function getInstance(): AllureAdapterInterface
    {
        return self::$instance ??= new self();
    }

    public static function setInstance(AllureAdapterInterface $instance): void
    {
        self::$instance = $instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }

    #[\Override]
    public function resetLastException(): void
    {
        $this->lastException = null;
    }

    #[\Override]
    public function setLastException(Throwable $e): void
    {
        $this->lastException = $e;
    }

    #[\Override]
    public function getLastException(): ?Throwable
    {
        return $this->lastException;
    }

    #[\Override]
    public function registerStart(ContainerResult $containerResult, TestResult $testResult, TestInfo $info): string
    {
        $this->lastStarts[$info->getTest()] = new TestStartInfo(
            containerId: $containerResult->getUuid(),
            testId: $testResult->getUuid(),
        );
        unset($this->startedTests[$info->getTest()], $this->writtenTests[$info->getTest()]);

        return $testResult->getUuid();
    }

    #[\Override]
    public function getContainerId(TestInfo $info): string
    {
        $startInfo = $this->lastStarts[$info->getTest()] ?? null;

        return $startInfo?->getContainerId()
            ?? throw new LogicException("Container not registered: {$info->getTest()}");
    }

    #[\Override]
    public function getTestId(TestInfo $info): string
    {
        $startInfo = $this->lastStarts[$info->getTest()] ?? null;

        return $startInfo?->getTestId()
            ?? throw new LogicException("Test not registered: {$info->getTest()}");
    }

    #[\Override]
    public function registerRun(TestResult $testResult, TestInfo $info): TestRunInfo
    {
        $testCaseId = $this->buildTestCaseId($testResult, $info);
        $historyId = $this->buildHistoryId($testResult, $info, $testCaseId);

        $previousRunInfo = $this->lastRuns[$historyId] ?? null;
        $currentRunInfo = new TestRunInfo(
            testInfo: $info,
            uuid: $testResult->getUuid(),
            rerunOf: $previousRunInfo?->getUuid(),
            runIndex: 1 + ($previousRunInfo?->getRunIndex() ?? -1),
            testCaseId: $testCaseId,
            historyId: $historyId,
        );
        $this->lastRuns[$historyId] = $currentRunInfo;

        return $currentRunInfo;
    }

    #[\Override]
    public function markTestStarted(TestInfo $info): void
    {
        $this->startedTests[$info->getTest()] = true;
    }

    #[\Override]
    public function wasTestStarted(TestInfo $info): bool
    {
        return isset($this->startedTests[$info->getTest()]);
    }

    #[\Override]
    public function markTestWritten(TestInfo $info): void
    {
        $this->writtenTests[$info->getTest()] = true;
    }

    #[\Override]
    public function wasTestWritten(TestInfo $info): bool
    {
        return isset($this->writtenTests[$info->getTest()]);
    }

    #[\Override]
    public function registerClassContainer(string $className, string $containerUuid): void
    {
        $this->classContainers[$className] = $containerUuid;
    }

    #[\Override]
    public function getClassContainerId(string $className): ?string
    {
        return $this->classContainers[$className] ?? null;
    }

    #[\Override]
    public function clearClassContainer(string $className): void
    {
        unset($this->classContainers[$className]);
    }

    #[\Override]
    public function setActiveFixture(string $uuid, string $hookName): void
    {
        $this->activeFixtureUuid = $uuid;
        $this->activeHookName = $hookName;
    }

    #[\Override]
    public function getActiveFixtureUuid(): ?string
    {
        return $this->activeFixtureUuid;
    }

    #[\Override]
    public function getActiveHookName(): ?string
    {
        return $this->activeHookName;
    }

    #[\Override]
    public function clearActiveFixture(): void
    {
        $this->activeFixtureUuid = null;
        $this->activeHookName = null;
    }

    #[\Override]
    public function hasEmittedHookGlobalError(string $fixtureUuid): bool
    {
        return isset($this->emittedHookGlobals[$fixtureUuid]);
    }

    #[\Override]
    public function markHookGlobalErrorEmitted(string $fixtureUuid): void
    {
        $this->emittedHookGlobals[$fixtureUuid] = true;
    }

    #[\Override]
    public function markPendingFinalize(TestInfo $info): void
    {
        $this->pendingFinalizeTest = $info;
    }

    #[\Override]
    public function getPendingFinalizeTest(): ?TestInfo
    {
        return $this->pendingFinalizeTest;
    }

    #[\Override]
    public function clearPendingFinalize(): void
    {
        $this->pendingFinalizeTest = null;
    }

    /**
     * @param TestResult $test
     * @return list<Parameter>
     */
    private function getIncludedParameters(TestResult $test): array
    {
        return array_values(
            array_filter(
                $test->getParameters(),
                fn (Parameter $parameter): bool => $parameter->getExcluded() !== true,
            ),
        );
    }

    private function buildTestCaseId(TestResult $test, TestInfo $info): string
    {
        $parameterNames = implode(
            '::',
            array_map(
                fn (Parameter $parameter): string => $parameter->getName(),
                $this->getIncludedParameters($test),
            ),
        );

        return md5("{$info->getName()}::{$parameterNames}");
    }

    private function buildHistoryId(TestResult $test, TestInfo $info, string $testCaseId): string
    {
        $parameterValues = implode(
            '::',
            array_map(
                fn (Parameter $parameter) => $parameter->getValue() ?? '',
                $this->getIncludedParameters($test),
            ),
        );

        return md5("{$testCaseId}::{$info->getName()}::{$parameterValues}");
    }
}
