<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\AfterTestMethodCalled;
use PHPUnit\Event\Test\AfterTestMethodCalledSubscriber as AfterTestMethodCalledSubscriberInterface;

final class AfterTestMethodCalledSubscriber extends AbstractHookMethodSubscriber implements
    AfterTestMethodCalledSubscriberInterface
{
    #[\Override]
    public function notify(AfterTestMethodCalled $event): void
    {
        if (!$this->switchToHookEvent($event)) {
            return;
        }

        $this
            ->testLifecycle
            ->startAfterHookFixture($this->hookName($event->calledMethod()));
    }
}
