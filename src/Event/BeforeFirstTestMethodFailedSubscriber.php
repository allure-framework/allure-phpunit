<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use Qameta\Allure\Model\Status;

final class BeforeFirstTestMethodFailedSubscriber extends AbstractHookMethodSubscriber
{
    public function notify(object $event): void
    {
        $className = $this->eventTestClassName($event);
        $throwable = $this->eventThrowable($event);
        if ($className === null || $throwable === null) {
            return;
        }

        $this
            ->testLifecycle
            ->ensureClassContainer($className);
        $this->completeFailure($throwable, Status::failed());
        $this->testLifecycle->writeClassContainer($className);
    }
}
