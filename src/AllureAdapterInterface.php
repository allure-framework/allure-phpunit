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

    public function markTestStarted(TestInfo $info): void;

    public function wasTestStarted(TestInfo $info): bool;

    public function markTestWritten(TestInfo $info): void;

    public function wasTestWritten(TestInfo $info): bool;

    public function registerClassContainer(string $className, string $containerUuid): void;

    public function getClassContainerId(string $className): ?string;

    public function clearClassContainer(string $className): void;

    public function setActiveFixture(string $uuid, string $hookName): void;

    public function getActiveFixtureUuid(): ?string;

    public function getActiveHookName(): ?string;

    public function clearActiveFixture(): void;

    public function hasEmittedHookGlobalError(string $fixtureUuid): bool;

    public function markHookGlobalErrorEmitted(string $fixtureUuid): void;

    public function markPendingFinalize(TestInfo $info): void;

    public function getPendingFinalizeTest(): ?TestInfo;

    public function clearPendingFinalize(): void;
}
