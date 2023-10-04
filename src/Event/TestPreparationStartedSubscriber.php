<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\PreparationStarted;
use PHPUnit\Event\Test\PreparationStartedSubscriber;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

final class TestPreparationStartedSubscriber implements PreparationStartedSubscriber
{
    public function __construct(
        private readonly TestLifecycleInterface $testLifecycle,
    ) {
    }

    public function notify(PreparationStarted $event): void
    {
        $test = $event->test();
        $method = $test instanceof TestMethod ? $test->nameWithClass() : null;
        if (!isset($method)) {
            return;
        }

        $this
            ->testLifecycle
            ->switchTo($method)
            ->reset()
            ->create();
    }
}
