<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\BeforeTestMethodErrored;
use PHPUnit\Event\Test\BeforeTestMethodErroredSubscriber as BeforeTestMethodErroredSubscriberInterface;
use Qameta\Allure\Model\Status;

final class BeforeTestMethodErroredSubscriber extends AbstractHookMethodSubscriber implements
    BeforeTestMethodErroredSubscriberInterface
{
    #[\Override]
    public function notify(BeforeTestMethodErrored $event): void
    {
        if (!$this->switchToHookEvent($event)) {
            return;
        }

        $this->completeFailure($event->throwable(), Status::broken());
        // Exception during setUp: PHPUnit may skip TestFinished — defer write until after tearDown.
        $this->testLifecycle->markPendingFinalize();
    }
}
