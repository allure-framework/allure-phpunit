<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\AfterTestMethodFinished;
use PHPUnit\Event\Test\AfterTestMethodFinishedSubscriber as AfterTestMethodFinishedSubscriberInterface;

final class AfterTestMethodFinishedSubscriber extends AbstractHookMethodSubscriber implements
    AfterTestMethodFinishedSubscriberInterface
{
    #[\Override]
    public function notify(AfterTestMethodFinished $event): void
    {
        if (!$this->switchToHookEvent($event)) {
            return;
        }

        $this
            ->testLifecycle
            ->completeHookFixtureSuccess()
            ->flushPendingFinalize();
    }
}
