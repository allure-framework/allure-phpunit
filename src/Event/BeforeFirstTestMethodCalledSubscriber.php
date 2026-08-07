<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\BeforeFirstTestMethodCalled;
use PHPUnit\Event\Test\BeforeFirstTestMethodCalledSubscriber as BeforeFirstTestMethodCalledSubscriberInterface;

final class BeforeFirstTestMethodCalledSubscriber extends AbstractHookMethodSubscriber implements
    BeforeFirstTestMethodCalledSubscriberInterface
{
    #[\Override]
    public function notify(BeforeFirstTestMethodCalled $event): void
    {
        $this
            ->testLifecycle
            ->ensureClassContainer($event->testClassName())
            ->startBeforeHookFixture($this->hookName($event->calledMethod()));
    }
}
