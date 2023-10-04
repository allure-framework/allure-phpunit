<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Event;

use PHPUnit\Event\Code\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\PHPUnit\Event\TestFinishedSubscriber;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

#[CoversClass(TestFinishedSubscriber::class)]
class TestFinishedSubscriberTest extends TestCase
{
    use EventTestTrait;

    public function testNotify_InvalidTestMethod_NeverSwitchesLifecycle(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $subscriber = new TestFinishedSubscriber($testLifecycle);
        $event = $this->createTestFinishedEvent(
            $this->createStub(Test::class),
        );

        $testLifecycle
            ->expects(self::never())
            ->method('switchTo');
        $subscriber->notify($event);
    }

    public function testNotify_ValidTestMethod_WritesUpdatedStoppedTestAfterSwitchingContext(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $subscriber = new TestFinishedSubscriber($testLifecycle);
        $event = $this->createTestFinishedEvent(
            $this->createTestMethod(class: 'a', methodName: 'b'),
        );

        $testLifecycle
            ->expects(self::once())
            ->id('switch')
            ->method('switchTo')
            ->with(self::identicalTo('a::b'))
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->id('stop')
            ->after('switch')
            ->method('stop')
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->id('update')
            ->after('stop')
            ->method('updateRunInfo')
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->after('update')
            ->method('write');
        $subscriber->notify($event);
    }
}
