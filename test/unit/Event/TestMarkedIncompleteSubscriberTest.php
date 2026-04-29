<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Event;

use PHPUnit\Event\Code\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Event\TestMarkedIncompleteSubscriber;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

#[CoversClass(TestMarkedIncompleteSubscriber::class)]
final class TestMarkedIncompleteSubscriberTest extends TestCase
{
    use EventTestTrait;

    public function testNotify_InvalidTestMethod_NeverSwitchesLifecycle(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $subscriber = new TestMarkedIncompleteSubscriber($testLifecycle);
        $event = $this->createTestMarkedIncompleteEvent(
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
        $subscriber = new TestMarkedIncompleteSubscriber($testLifecycle);
        $event = $this->createTestMarkedIncompleteEvent(
            test: $this->createTestMethod(class: 'a', methodName: 'b'),
            message: 'c',
        );

        $switched = false;
        $testLifecycle
            ->expects(self::once())
            ->method('switchTo')
            ->with(self::identicalTo('a::b'))
            ->willReturnCallback(
                function () use (&$switched, $testLifecycle) {
                    $switched = true;

                    return $testLifecycle;
                }
            );
        $testLifecycle
            ->expects(self::once())
            ->method('updateStatus')
            ->with(
                self::identicalTo('c'),
                self::identicalTo(Status::broken()),
            )
            ->willReturnCallback(
                function () use (&$switched, $testLifecycle) {
                    self::assertTrue($switched, "updateStatus() was called before switchTo()");

                    return $testLifecycle;
                }
            );
        $subscriber->notify($event);
    }
}
