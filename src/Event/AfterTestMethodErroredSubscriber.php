<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\AfterTestMethodErrored;
use PHPUnit\Event\Test\AfterTestMethodErroredSubscriber as AfterTestMethodErroredSubscriberInterface;
use Qameta\Allure\Model\Status;

final class AfterTestMethodErroredSubscriber extends AbstractHookMethodSubscriber implements
    AfterTestMethodErroredSubscriberInterface
{
    #[\Override]
    public function notify(AfterTestMethodErrored $event): void
    {
        if (!$this->switchToHookEvent($event)) {
            return;
        }

        $this->completeFailure($event->throwable(), Status::broken());
    }
}
