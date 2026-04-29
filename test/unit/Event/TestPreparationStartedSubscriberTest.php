<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Event;

use PHPUnit\Event\Code\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\PHPUnit\Event\TestPreparationStartedSubscriber;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

#[CoversClass(TestPreparationStartedSubscriber::class)]
final class TestPreparationStartedSubscriberTest extends TestCase
{
    use EventTestTrait;

    public function testNotify_InvalidTestMethod_NeverSwitchesLifecycle(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $subscriber = new TestPreparationStartedSubscriber($testLifecycle);
        $event = $this->createTestPreparationStartedEvent(
            test: $this->createStub(Test::class),
        );

        $testLifecycle
            ->expects(self::never())
            ->method('switchTo');
        $subscriber->notify($event);
    }

    public function testNotify_ValidTestMethod_CreatesTestAfterResettingSwitchedContext(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $subscriber = new TestPreparationStartedSubscriber($testLifecycle);
        $event = $this->createTestPreparationStartedEvent(
            test: $this->createTestMethod(class: 'a', methodName: 'b'),
        );

        $lastMethod = null;
        $testLifecycle
            ->expects(self::once())
            ->method('switchTo')
            ->with(self::identicalTo('a::b'))
            ->willReturnCallback(
                function () use (&$lastMethod, $testLifecycle) {
                    $lastMethod = "switchTo";

                    return $testLifecycle;
                }
            );
        $testLifecycle
            ->expects(self::once())
            ->method('reset')
            ->willReturnCallback(
                function () use (&$lastMethod, $testLifecycle) {
                    self::assertEquals("switchTo", $lastMethod, "reset() was called before switchTo()");

                    $lastMethod = true;

                    return $testLifecycle;
                }
            );
        $testLifecycle
            ->expects(self::once())
            ->method('create')
            ->willReturnCallback(
                function () use (&$lastMethod, $testLifecycle) {
                    self::assertEquals("reset", $lastMethod, "create() was called before reset()");

                    return $testLifecycle;
                }
            );
        $subscriber->notify($event);
    }
}
