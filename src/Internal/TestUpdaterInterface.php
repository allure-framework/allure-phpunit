<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use Qameta\Allure\Model\Status;
use Qameta\Allure\Model\TestResult;
use Qameta\Allure\Setup\StatusDetectorInterface;
use Throwable;

/**
 * @internal
 */
interface TestUpdaterInterface
{
    public function setInfo(TestResult $testResult, TestInfo $info): void;

    public function setRunInfo(TestResult $testResult, TestRunInfo $runInfo): void;

    public function setDetectedStatus(
        TestResult $test,
        StatusDetectorInterface $statusDetector,
        Throwable $e,
        ?Status $overrideStatus = null,
    ): void;

    public function setStatus(TestResult $test, ?string $message = null, ?Status $status = null): void;
}
