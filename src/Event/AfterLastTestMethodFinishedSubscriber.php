<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\AfterLastTestMethodFinished;
use PHPUnit\Event\Test\AfterLastTestMethodFinishedSubscriber as AfterLastTestMethodFinishedSubscriberInterface;

final class AfterLastTestMethodFinishedSubscriber extends AbstractHookMethodSubscriber implements
    AfterLastTestMethodFinishedSubscriberInterface
{
    #[\Override]
    public function notify(AfterLastTestMethodFinished $event): void
    {
        $this
            ->testLifecycle
            ->completeHookFixtureSuccess()
            ->writeClassContainer($event->testClassName());
    }
}
