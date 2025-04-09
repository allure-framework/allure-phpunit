<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit;

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
    public function switchTo(string $test): TestLifecycleInterface
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
}
