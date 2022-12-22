<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Event;

use PHPUnit\Event\Code\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Event\TestWarningTriggeredSubscriber;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

#[CoversClass(TestWarningTriggeredSubscriber::class)]
class TestWarningTriggeredSubscriberTest extends TestCase
{
    use EventTestTrait;

    public function testNotify_InvalidTestMethod_NeverSwitchesLifecycle(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $subscriber = new TestWarningTriggeredSubscriber($testLifecycle);
        $event = $this->createTestWarningTriggeredEvent(
            test: $this->createStub(Test::class),
        );

        $testLifecycle
            ->expects(self::never())
            ->method('switchTo');
        $subscriber->notify($event);
    }

    public function testNotify_ValidTestMethod_UpdatesStatusAsBrokenForSwitchedContext(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $subscriber = new TestWarningTriggeredSubscriber($testLifecycle);
        $event = $this->createTestWarningTriggeredEvent(
            test: $this->createTestMethod(class: 'a', methodName: 'b'),
            message: 'c',
        );

        $testLifecycle
            ->expects(self::once())
            ->id('switch')
            ->method('switchTo')
            ->with(self::identicalTo('a::b'))
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->after('switch')
            ->method('updateStatus')
            ->with(
                self::identicalTo('c'),
                self::identicalTo(Status::broken()),
            );
        $subscriber->notify($event);
    }
}
