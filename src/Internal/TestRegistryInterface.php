<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use Qameta\Allure\Model\TestResult;

/**
 * @internal
 */
interface TestRegistryInterface
{

    public function registerTest(TestResult $testResult, TestInfo $info): string;

    public function getTestId(TestInfo $info): string;

    public function registerRun(TestResult $testResult, TestInfo $info): TestRunInfo;
}
