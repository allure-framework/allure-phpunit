<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

final class TestPreparedSubscriber implements PreparedSubscriber
{
    public function __construct(
        private readonly TestLifecycleInterface $testLifecycle,
    ) {
    }

    public function notify(Prepared $event): void
    {
        $test = $event->test();
        $method = $test instanceof TestMethod ? $test->nameWithClass() : null;
        if (!isset($method)) {
            return;
        }

        $this
            ->testLifecycle
            ->switchTo($method)
            ->updateInfo()
            ->start();
    }
}
