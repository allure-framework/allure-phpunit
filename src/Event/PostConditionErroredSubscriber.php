<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\PostConditionErrored;
use PHPUnit\Event\Test\PostConditionErroredSubscriber as PostConditionErroredSubscriberInterface;
use Qameta\Allure\Model\Status;

final class PostConditionErroredSubscriber extends AbstractHookMethodSubscriber implements
    PostConditionErroredSubscriberInterface
{
    #[\Override]
    public function notify(PostConditionErrored $event): void
    {
        if (!$this->switchToHookEvent($event)) {
            return;
        }

        $this->completeFailure($event->throwable(), Status::broken());
    }
}
