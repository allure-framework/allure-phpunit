<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\Test\WarningTriggeredSubscriber;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

final class TestWarningTriggeredSubscriber implements WarningTriggeredSubscriber
{
    public function __construct(
        private readonly TestLifecycleInterface $testLifecycle,
    ) {
    }

    public function notify(WarningTriggered $event): void
    {
        $test = $event->test();
        $method = $test instanceof TestMethod ? $test : null;
        if (!isset($method)) {
            return;
        }

        $this
            ->testLifecycle
            ->switchTo($method->nameWithClass())
            ->updateStatus($event->message(), Status::broken());
    }
}
