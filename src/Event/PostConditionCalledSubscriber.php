<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\PostConditionCalled;
use PHPUnit\Event\Test\PostConditionCalledSubscriber as PostConditionCalledSubscriberInterface;

final class PostConditionCalledSubscriber extends AbstractHookMethodSubscriber implements
    PostConditionCalledSubscriberInterface
{
    #[\Override]
    public function notify(PostConditionCalled $event): void
    {
        if (!$this->switchToHookEvent($event)) {
            return;
        }

        $this
            ->testLifecycle
            ->startAfterHookFixture($this->hookName($event->calledMethod()));
    }
}
