<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\BeforeFirstTestMethodErrored;
use PHPUnit\Event\Test\BeforeFirstTestMethodErroredSubscriber as BeforeFirstTestMethodErroredSubscriberInterface;
use Qameta\Allure\Model\Status;

final class BeforeFirstTestMethodErroredSubscriber extends AbstractHookMethodSubscriber implements
    BeforeFirstTestMethodErroredSubscriberInterface
{
    #[\Override]
    public function notify(BeforeFirstTestMethodErrored $event): void
    {
        $this
            ->testLifecycle
            ->ensureClassContainer($event->testClassName());
        $this->completeFailure($event->throwable(), Status::broken());
        $this->testLifecycle->writeClassContainer($event->testClassName());
    }
}
