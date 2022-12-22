<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Event;

use PHPUnit\Event\Code\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\PHPUnit\Event\TestPreparedSubscriber;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

#[CoversClass(TestPreparedSubscriber::class)]
class TestPreparedSubscriberTest extends TestCase
{
    use EventTestTrait;

    public function testNotify_InvalidTestMethod_NeverSwitchesLifecycle(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $subscriber = new TestPreparedSubscriber($testLifecycle);
        $event = $this->createTestPreparedEvent(
            test: $this->createStub(Test::class),
        );

        $testLifecycle
            ->expects(self::never())
            ->method('switchTo');
        $subscriber->notify($event);
    }

    public function testNotify_ValidTestMethod_StartsTestsWithUpdatedInfoInSwitchedContext(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $subscriber = new TestPreparedSubscriber($testLifecycle);
        $event = $this->createTestPreparedEvent(
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
            ->id('update')
            ->after('switch')
            ->method('updateInfo')
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->after('update')
            ->method('start');
        $subscriber->notify($event);
    }
}
