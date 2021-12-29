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

    private ?Throwable $lastException = null;

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

    public function resetLastException(): void
    {
        $this->lastException = null;
    }

    public function setLastException(Throwable $e): void
    {
        $this->lastException = $e;
    }

    public function getLastException(): ?Throwable
    {
        return $this->lastException;
    }

    public function registerStart(ContainerResult $containerResult, TestResult $testResult, TestInfo $info): string
    {
        $this->lastStarts[$info->getTest()] = new TestStartInfo(
            containerId: $containerResult->getUuid(),
            testId: $testResult->getUuid(),
        );

        return $testResult->getUuid();
    }

    public function getContainerId(TestInfo $info): string
    {
        $startInfo = $this->lastStarts[$info->getTest()] ?? null;

        return $startInfo?->getContainerId()
            ?? throw new LogicException("Container not registered: {$info->getTest()}");
    }

    public function getTestId(TestInfo $info): string
    {
        $startInfo = $this->lastStarts[$info->getTest()] ?? null;

        return $startInfo?->getTestId()
            ?? throw new LogicException("Test not registered: {$info->getTest()}");
    }

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
