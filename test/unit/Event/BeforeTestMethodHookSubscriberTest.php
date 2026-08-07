<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Event;

use PHPUnit\Event\Test\BeforeTestMethodCalled;
use PHPUnit\Event\Test\BeforeTestMethodFailedSubscriber as BeforeTestMethodFailedSubscriberInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Event\BeforeTestMethodCalledSubscriber;
use Qameta\Allure\PHPUnit\Event\BeforeTestMethodErroredSubscriber;
use Qameta\Allure\PHPUnit\Event\BeforeTestMethodFailedSubscriber;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

use function interface_exists;
use function method_exists;

#[CoversClass(BeforeTestMethodCalledSubscriber::class)]
#[CoversClass(BeforeTestMethodFailedSubscriber::class)]
#[CoversClass(BeforeTestMethodErroredSubscriber::class)]
final class BeforeTestMethodHookSubscriberTest extends TestCase
{
    use EventTestTrait;

    public function testCalled_StartsBeforeHookFixtureWithMethodName(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $subscriber = new BeforeTestMethodCalledSubscriber($testLifecycle);
        $test = $this->createTestMethod(class: 'ExampleTest', methodName: 'testSomething');
        $event = $this->createBeforeTestMethodCalledEvent($test, 'setUp');

        if (method_exists(BeforeTestMethodCalled::class, 'test')) {
            $testLifecycle
                ->expects(self::once())
                ->method('switchTo')
                ->with(self::identicalTo($test))
                ->willReturnSelf();
        } else {
            $testLifecycle
                ->expects(self::never())
                ->method('switchTo');
        }
        $testLifecycle
            ->expects(self::once())
            ->method('startBeforeHookFixture')
            ->with(self::identicalTo('setUp'))
            ->willReturnSelf();

        $subscriber->notify($event);
    }

    public function testFailed_CompletesHookFixtureAsFailed(): void
    {
        if (!interface_exists(BeforeTestMethodFailedSubscriberInterface::class)) {
            $this->markTestSkipped('BeforeTestMethodFailed is not available on this PHPUnit version');
        }

        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $subscriber = new BeforeTestMethodFailedSubscriber($testLifecycle);
        $test = $this->createTestMethod(class: 'ExampleTest', methodName: 'testSomething');
        $event = $this->createBeforeTestMethodFailedEvent($test, 'assertion failed', 'setUp');

        $testLifecycle
            ->expects(self::once())
            ->method('switchTo')
            ->with(self::identicalTo($test))
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->method('completeHookFixtureFailure')
            ->with(
                self::identicalTo(Status::failed()),
                self::identicalTo('assertion failed'),
                self::anything(),
            )
            ->willReturnSelf();

        $subscriber->notify($event);
    }

    public function testErrored_CompletesHookFixtureAsBrokenAndMarksPendingFinalize(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $subscriber = new BeforeTestMethodErroredSubscriber($testLifecycle);
        $test = $this->createTestMethod(class: 'ExampleTest', methodName: 'testSomething');
        $event = $this->createBeforeTestMethodErroredEvent($test, 'boom', 'setUp');

        if (method_exists(BeforeTestMethodCalled::class, 'test')) {
            $testLifecycle
                ->expects(self::once())
                ->method('switchTo')
                ->willReturnSelf();
        } else {
            $testLifecycle
                ->expects(self::never())
                ->method('switchTo');
        }
        $testLifecycle
            ->expects(self::once())
            ->method('completeHookFixtureFailure')
            ->with(
                self::identicalTo(Status::broken()),
                self::identicalTo('boom'),
                self::anything(),
            )
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->method('markPendingFinalize')
            ->willReturnSelf();

        $subscriber->notify($event);
    }
}
