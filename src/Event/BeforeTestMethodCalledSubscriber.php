<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\BeforeTestMethodCalled;
use PHPUnit\Event\Test\BeforeTestMethodCalledSubscriber as BeforeTestMethodCalledSubscriberInterface;

final class BeforeTestMethodCalledSubscriber extends AbstractHookMethodSubscriber implements
    BeforeTestMethodCalledSubscriberInterface
{
    #[\Override]
    public function notify(BeforeTestMethodCalled $event): void
    {
        if (!$this->switchToHookEvent($event)) {
            return;
        }

        $this
            ->testLifecycle
            ->startBeforeHookFixture($this->hookName($event->calledMethod()));
    }
}
