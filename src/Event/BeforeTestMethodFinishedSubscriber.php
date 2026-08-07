<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\BeforeTestMethodFinished;
use PHPUnit\Event\Test\BeforeTestMethodFinishedSubscriber as BeforeTestMethodFinishedSubscriberInterface;

/**
 * Success for before-hooks is finalized on TestPrepared / next hook Called,
 * so missing BeforeTestMethodErrored on older PHPUnit can still fail the fixture
 * via TestErrored + completeOrphanHookFailure.
 */
final class BeforeTestMethodFinishedSubscriber extends AbstractHookMethodSubscriber implements
    BeforeTestMethodFinishedSubscriberInterface
{
    #[\Override]
    public function notify(BeforeTestMethodFinished $event): void
    {
        // Intentionally no-op for fixture completion.
    }
}
