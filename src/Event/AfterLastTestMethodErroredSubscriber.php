<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\AfterLastTestMethodErrored;
use PHPUnit\Event\Test\AfterLastTestMethodErroredSubscriber as AfterLastTestMethodErroredSubscriberInterface;
use Qameta\Allure\Model\Status;

final class AfterLastTestMethodErroredSubscriber extends AbstractHookMethodSubscriber implements
    AfterLastTestMethodErroredSubscriberInterface
{
    #[\Override]
    public function notify(AfterLastTestMethodErrored $event): void
    {
        $this
            ->testLifecycle
            ->ensureClassContainer($event->testClassName());
        $this->completeFailure($event->throwable(), Status::broken());
        $this->testLifecycle->writeClassContainer($event->testClassName());
    }
}
