<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\FailedSubscriber;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

final class TestFailedSubscriber implements FailedSubscriber
{
    public function __construct(
        private readonly TestLifecycleInterface $testLifecycle,
    ) {
    }

    #[\Override]
    public function notify(Failed $event): void
    {
        $test = $event->test();
        if (!$test instanceof TestMethod) {
            return;
        }

        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateDetectedStatus($event->throwable()->message(), Status::failed(), Status::failed());
    }
}
