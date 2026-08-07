<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\TestSuite\Finished;
use PHPUnit\Event\TestSuite\FinishedSubscriber;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

final class TestSuiteFinishedSubscriber implements FinishedSubscriber
{
    public function __construct(
        private readonly TestLifecycleInterface $testLifecycle,
    ) {
    }

    #[\Override]
    public function notify(Finished $event): void
    {
        $this->testLifecycle->flushPendingFinalize();
    }
}
