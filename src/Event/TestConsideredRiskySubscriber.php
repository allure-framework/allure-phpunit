<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\ConsideredRiskySubscriber;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

final class TestConsideredRiskySubscriber implements ConsideredRiskySubscriber
{
    public function __construct(
        private readonly TestLifecycleInterface $testLifecycle,
    ) {
    }

    #[\Override]
    public function notify(ConsideredRisky $event): void
    {
        $test = $event->test();
        if (!$test instanceof TestMethod) {
            return;
        }

        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateStatus($event->message(), Status::failed());
    }
}
