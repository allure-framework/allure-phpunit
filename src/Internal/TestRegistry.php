<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use Qameta\Allure\Model\Parameter;
use Qameta\Allure\Model\TestResult;
use RuntimeException;

use function array_filter;
use function array_map;
use function array_values;
use function implode;
use function md5;

/**
 * @internal
 */
final class TestRegistry implements TestRegistryInterface
{

    /**
     * @var array<string, string>
     */
    private array $lastStarts = [];

    /**
     * @var array<string, TestRunInfo>
     */
    private array $lastRuns = [];

    public function registerTest(TestResult $testResult, TestInfo $info): string
    {
        $this->lastStarts[$info->getTest()] = $testResult->getUuid();

        return $testResult->getUuid();
    }

    public function getTestId(TestInfo $info): string
    {
        return $this->lastStarts[$info->getTest()]
            ?? throw new RuntimeException("Test not registered");
    }

    public function registerRun(TestResult $testResult, TestInfo $info): TestRunInfo
    {
        $historyId = $this->buildHistoryId($testResult, $info);

        $previousRunInfo = $this->lastRuns[$historyId] ?? null;
        $currentRunInfo = new TestRunInfo(
            testInfo: $info,
            uuid: $testResult->getUuid(),
            rerunOf: $previousRunInfo?->getUuid(),
            runIndex: 1 + ($previousRunInfo?->getRunIndex() ?? -1),
            testCaseId: $this->buildTestCaseId($testResult, $info),
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
        $fullName = $info->getFullName() ?? '';

        return md5("{$fullName}::{$parameterNames}");
    }

    private function buildHistoryId(TestResult $test, TestInfo $info): string
    {
        $parameterValues = implode(
            '::',
            array_map(
                fn (Parameter $parameter) => $parameter->getValue() ?? '',
                $this->getIncludedParameters($test),
            ),
        );
        $fullName = $info->getFullName() ?? '';

        return md5("{$fullName}::{$parameterValues}");
    }
}
