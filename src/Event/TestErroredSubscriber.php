<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\ErroredSubscriber;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

final class TestErroredSubscriber implements ErroredSubscriber
{
    public function __construct(
        private readonly TestLifecycleInterface $testLifecycle,
    ) {
    }

    #[\Override]
    public function notify(Errored $event): void
    {
        $test = $event->test();
        if (!$test instanceof TestMethod) {
            return;
        }

        $throwable = $event->throwable();
        $this
            ->testLifecycle
            ->switchTo($test)
            ->completeOrphanHookFailure(
                Status::broken(),
                $throwable->message(),
                $throwable->asString(),
            )
            ->updateDetectedStatus($throwable->message(), Status::broken());
    }
}
