<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

final class TestErroredSubscriber implements ErroredSubscriber
{
    public function __construct(
        private readonly TestLifecycleInterface $testLifecycle,
    ) {
    }

    public function notify(Errored $event): void
    {
        $test = $event->test();
        $method = $test instanceof TestMethod ? $test->nameWithClass() : null;
        if (!isset($method)) {
            return;
        }

        $this
            ->testLifecycle
            ->switchTo($method)
            ->updateDetectedStatus($event->throwable()->message(), Status::broken());
    }
}
