<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\PreConditionCalled;
use PHPUnit\Event\Test\PreConditionCalledSubscriber as PreConditionCalledSubscriberInterface;

final class PreConditionCalledSubscriber extends AbstractHookMethodSubscriber implements
    PreConditionCalledSubscriberInterface
{
    #[\Override]
    public function notify(PreConditionCalled $event): void
    {
        if (!$this->switchToHookEvent($event)) {
            return;
        }

        $this
            ->testLifecycle
            ->startBeforeHookFixture($this->hookName($event->calledMethod()));
    }
}
