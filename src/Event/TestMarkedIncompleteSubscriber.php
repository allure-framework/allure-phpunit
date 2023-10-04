<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\MarkedIncompleteSubscriber;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

final class TestMarkedIncompleteSubscriber implements MarkedIncompleteSubscriber
{
    public function __construct(
        private readonly TestLifecycleInterface $testLifecycle,
    ) {
    }

    public function notify(MarkedIncomplete $event): void
    {
        $test = $event->test();
        $method = $test instanceof TestMethod ? $test->nameWithClass() : null;
        if (!isset($method)) {
            return;
        }

        $this
            ->testLifecycle
            ->switchTo($method)
            ->updateStatus($event->throwable()->message(), Status::broken());
    }
}
