<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\PostConditionFinished;
use PHPUnit\Event\Test\PostConditionFinishedSubscriber as PostConditionFinishedSubscriberInterface;

final class PostConditionFinishedSubscriber extends AbstractHookMethodSubscriber implements
    PostConditionFinishedSubscriberInterface
{
    #[\Override]
    public function notify(PostConditionFinished $event): void
    {
        if (!$this->switchToHookEvent($event)) {
            return;
        }

        $this
            ->testLifecycle
            ->completeHookFixtureSuccess();
    }
}
