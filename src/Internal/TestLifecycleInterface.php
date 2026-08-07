<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use PHPUnit\Event\Code\TestMethod;
use Qameta\Allure\Model\Status;

interface TestLifecycleInterface
{
    public function create(): TestLifecycleInterface;

    public function updateInfo(): TestLifecycleInterface;

    public function start(): TestLifecycleInterface;

    public function stop(): TestLifecycleInterface;

    public function updateRunInfo(): TestLifecycleInterface;

    public function write(): TestLifecycleInterface;

    public function updateStatus(?string $message = null, ?Status $status = null): TestLifecycleInterface;

    public function updateDetectedStatus(
        ?string $message = null,
        ?Status $status = null,
        ?Status $overrideStatus = null,
    ): TestLifecycleInterface;

    public function switchTo(TestMethod $test): TestLifecycleInterface;

    public function reset(): TestLifecycleInterface;

    public function ensureClassContainer(string $className): TestLifecycleInterface;

    public function writeClassContainer(?string $className = null): TestLifecycleInterface;

    public function startBeforeHookFixture(string $hookName): TestLifecycleInterface;

    public function startAfterHookFixture(string $hookName): TestLifecycleInterface;

    public function completeHookFixtureSuccess(): TestLifecycleInterface;

    public function completeHookFixtureFailure(
        Status $status,
        ?string $message = null,
        ?string $trace = null,
    ): TestLifecycleInterface;

    /**
     * Writes the current test/container when PHPUnit skips TestFinished
     * (e.g. setUp throws before Prepared).
     */
    public function finalizeWithoutFinished(): TestLifecycleInterface;

    public function markPendingFinalize(): TestLifecycleInterface;

    public function flushPendingFinalize(): TestLifecycleInterface;

    /**
     * Completes an active hook fixture when dedicated hook Failed/Errored events
     * are missing (older PHPUnit) and the failure surfaces as TestFailed/TestErrored
     * before the test body starts.
     */
    public function completeOrphanHookFailure(
        Status $status,
        ?string $message = null,
        ?string $trace = null,
    ): TestLifecycleInterface;
}
