<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Passed;
use PHPUnit\Event\Test\PassedSubscriber;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

final class TestPassedSubscriber implements PassedSubscriber
{
    public function __construct(
        private readonly TestLifecycleInterface $testLifecycle,
    ) {
    }

    #[\Override]
    public function notify(Passed $event): void
    {
        $test = $event->test();
        if (!$test instanceof TestMethod) {
            return;
        }

        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateStatus(status: Status::passed());
    }
}
