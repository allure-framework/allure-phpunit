<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\SkippedSubscriber;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

final class TestSkippedSubscriber implements SkippedSubscriber
{
    public function __construct(
        private readonly TestLifecycleInterface $testLifecycle,
    ) {
    }

    public function notify(Skipped $event): void
    {
        $test = $event->test();
        $method = $test instanceof TestMethod ? $test->nameWithClass() : null;
        if (!isset($method)) {
            return;
        }

        $this
            ->testLifecycle
            ->switchTo($method)
            ->updateStatus($event->message(), Status::skipped());
    }
}
