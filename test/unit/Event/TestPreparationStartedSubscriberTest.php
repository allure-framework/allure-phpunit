<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Event;

use PHPUnit\Event\Code\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\PHPUnit\Event\TestPreparationStartedSubscriber;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

#[CoversClass(TestPreparationStartedSubscriber::class)]
class TestPreparationStartedSubscriberTest extends TestCase
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

        $testLifecycle
            ->expects(self::once())
            ->id('switch')
            ->method('switchTo')
            ->with(self::identicalTo('a::b'))
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->id('reset')
            ->after('switch')
            ->method('reset')
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->after('reset')
            ->method('create');
        $subscriber->notify($event);
    }
}
