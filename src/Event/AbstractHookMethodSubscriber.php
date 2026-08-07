<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Event;

use PHPUnit\Event\Code\ClassMethod;
use PHPUnit\Event\Code\TestMethod;
use PHPUnit\Event\Code\Throwable;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

use function is_string;
use function method_exists;

/**
 * Shared helpers for PHPUnit hook-method subscribers.
 */
abstract class AbstractHookMethodSubscriber
{
    public function __construct(
        protected readonly TestLifecycleInterface $testLifecycle,
    ) {
    }

    protected function hookName(ClassMethod $calledMethod): string
    {
        return $calledMethod->methodName();
    }

    /**
     * PHPUnit < 12.5 exposes only testClassName() on per-test hook events;
     * PreparationStarted / the active test already switched the lifecycle.
     * PHPUnit >= 12.5 provides test() with the concrete TestMethod.
     */
    protected function switchToHookEvent(object $event): bool
    {
        if (method_exists($event, 'test')) {
            /** @var mixed $test */
            $test = $event->test();
            if (!$test instanceof TestMethod) {
                return false;
            }

            $this->testLifecycle->switchTo($test);

            return true;
        }

        return method_exists($event, 'testClassName');
    }

    protected function eventThrowable(object $event): ?Throwable
    {
        if (!method_exists($event, 'throwable')) {
            return null;
        }

        /** @var mixed $throwable */
        $throwable = $event->throwable();

        return $throwable instanceof Throwable ? $throwable : null;
    }

    protected function eventTestClassName(object $event): ?string
    {
        if (!method_exists($event, 'testClassName')) {
            return null;
        }

        /** @var mixed $className */
        $className = $event->testClassName();

        return is_string($className) ? $className : null;
    }

    protected function completeFailure(Throwable $throwable, Status $status): void
    {
        $this
            ->testLifecycle
            ->completeHookFixtureFailure(
                $status,
                $throwable->message(),
                $throwable->asString(),
            );
    }
}
