<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\PreConditionErrored;
use PHPUnit\Event\Test\PreConditionErroredSubscriber as PreConditionErroredSubscriberInterface;
use Qameta\Allure\Model\Status;

final class PreConditionErroredSubscriber extends AbstractHookMethodSubscriber implements
    PreConditionErroredSubscriberInterface
{
    #[\Override]
    public function notify(PreConditionErrored $event): void
    {
        if (!$this->switchToHookEvent($event)) {
            return;
        }

        $this->completeFailure($event->throwable(), Status::broken());
        $this->testLifecycle->markPendingFinalize();
    }
}
