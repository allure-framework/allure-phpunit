<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use Qameta\Allure\Model\Status;

final class AfterTestMethodFailedSubscriber extends AbstractHookMethodSubscriber
{
    public function notify(object $event): void
    {
        if (!$this->switchToHookEvent($event)) {
            return;
        }

        $throwable = $this->eventThrowable($event);
        if ($throwable === null) {
            return;
        }

        $this->completeFailure($throwable, Status::failed());
    }
}
