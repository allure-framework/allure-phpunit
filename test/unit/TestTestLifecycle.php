<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit;

use PHPUnit\Event\Code\TestMethod;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

final class TestTestLifecycle implements TestLifecycleInterface
{
    #[\Override]
    public function create(): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function reset(): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function start(): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function stop(): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function switchTo(TestMethod $test): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function updateDetectedStatus(
        ?string $message = null,
        ?Status $status = null,
        ?Status $overrideStatus = null,
    ): TestLifecycleInterface {
        return $this;
    }

    #[\Override]
    public function updateInfo(): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function updateRunInfo(): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function updateStatus(?string $message = null, ?Status $status = null): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function write(): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function ensureClassContainer(string $className): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function writeClassContainer(?string $className = null): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function startBeforeHookFixture(string $hookName): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function startAfterHookFixture(string $hookName): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function completeHookFixtureSuccess(): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function completeHookFixtureFailure(
        Status $status,
        ?string $message = null,
        ?string $trace = null,
    ): TestLifecycleInterface {
        return $this;
    }

    #[\Override]
    public function finalizeWithoutFinished(): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function markPendingFinalize(): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function flushPendingFinalize(): TestLifecycleInterface
    {
        return $this;
    }

    #[\Override]
    public function completeOrphanHookFailure(
        Status $status,
        ?string $message = null,
        ?string $trace = null,
    ): TestLifecycleInterface {
        return $this;
    }
}
