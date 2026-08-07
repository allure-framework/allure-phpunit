<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Test\PreConditionFinished;
use PHPUnit\Event\Test\PreConditionFinishedSubscriber as PreConditionFinishedSubscriberInterface;

/**
 * Success for pre-conditions is finalized on TestPrepared / next hook Called.
 */
final class PreConditionFinishedSubscriber extends AbstractHookMethodSubscriber implements
    PreConditionFinishedSubscriberInterface
{
    #[\Override]
    public function notify(PreConditionFinished $event): void
    {
        // Intentionally no-op for fixture completion.
    }
}
