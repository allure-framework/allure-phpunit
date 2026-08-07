<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\BeforeFirstTestMethodFinished;
use PHPUnit\Event\Test\BeforeFirstTestMethodFinishedSubscriber as BeforeFirstTestMethodFinishedSubscriberInterface;

final class BeforeFirstTestMethodFinishedSubscriber extends AbstractHookMethodSubscriber implements
    BeforeFirstTestMethodFinishedSubscriberInterface
{
    #[\Override]
    public function notify(BeforeFirstTestMethodFinished $event): void
    {
        $this
            ->testLifecycle
            ->completeHookFixtureSuccess()
            ->writeClassContainer($event->testClassName());
    }
}
