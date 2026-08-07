<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\AfterLastTestMethodCalled;
use PHPUnit\Event\Test\AfterLastTestMethodCalledSubscriber as AfterLastTestMethodCalledSubscriberInterface;

final class AfterLastTestMethodCalledSubscriber extends AbstractHookMethodSubscriber implements
    AfterLastTestMethodCalledSubscriberInterface
{
    #[\Override]
    public function notify(AfterLastTestMethodCalled $event): void
    {
        $this
            ->testLifecycle
            ->ensureClassContainer($event->testClassName())
            ->startAfterHookFixture($this->hookName($event->calledMethod()));
    }
}
