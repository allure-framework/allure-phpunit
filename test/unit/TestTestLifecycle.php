<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit;

use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

final class TestTestLifecycle implements TestLifecycleInterface
{
    public function create(): TestLifecycleInterface
    {
        return $this;
    }

    public function reset(): TestLifecycleInterface
    {
        return $this;
    }

    public function start(): TestLifecycleInterface
    {
        return $this;
    }

    public function stop(): TestLifecycleInterface
    {
        return $this;
    }

    public function switchTo(string $test): TestLifecycleInterface
    {
        return $this;
    }

    public function updateDetectedStatus(
        ?string $message = null,
        ?Status $status = null,
        ?Status $overrideStatus = null,
    ): TestLifecycleInterface {
        return $this;
    }

    public function updateInfo(): TestLifecycleInterface
    {
        return $this;
    }

    public function updateRunInfo(): TestLifecycleInterface
    {
        return $this;
    }

    public function updateStatus(?string $message = null, ?Status $status = null): TestLifecycleInterface
    {
        return $this;
    }

    public function write(): TestLifecycleInterface
    {
        return $this;
    }
}
