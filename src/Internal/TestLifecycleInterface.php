<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

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

    public function switchTo(string $test): TestLifecycleInterface;

    public function reset(): TestLifecycleInterface;
}
