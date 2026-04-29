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

    #[\Override]
    public function notify(Skipped $event): void
    {
        $test = $event->test();
        if (!$test instanceof TestMethod) {
            return;
        }

        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateStatus($event->message(), Status::skipped());
    }
}
