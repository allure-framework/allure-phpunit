<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit;

use Qameta\Allure\Model\ContainerResult;
use Qameta\Allure\Model\TestResult;
use Qameta\Allure\PHPUnit\Internal\TestInfo;
use Qameta\Allure\PHPUnit\Internal\TestRunInfo;
use Throwable;

interface AllureAdapterInterface
{
    public function registerStart(ContainerResult $containerResult, TestResult $testResult, TestInfo $info): string;

    public function registerRun(TestResult $testResult, TestInfo $info): TestRunInfo;

    public function getTestId(TestInfo $info): string;

    public function getContainerId(TestInfo $info): string;

    public function resetLastException(): void;

    public function setLastException(Throwable $e): void;

    public function getLastException(): ?Throwable;
}
