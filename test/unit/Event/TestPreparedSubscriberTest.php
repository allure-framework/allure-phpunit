<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Event;

use PHPUnit\Event\Code\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\PHPUnit\Event\TestPreparedSubscriber;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

#[CoversClass(TestPreparedSubscriber::class)]
final class TestPreparedSubscriberTest extends TestCase
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
            ->method('updateInfo')
            ->willReturnCallback(
                function () use (&$lastMethod, $testLifecycle) {
                    self::assertEquals("switchTo", $lastMethod, "updateInfo() was called before switchTo()");

                    $lastMethod = true;

                    return $testLifecycle;
                }
            );
        $testLifecycle
            ->expects(self::once())
            ->method('start')
            ->willReturnCallback(
                function () use (&$lastMethod, $testLifecycle) {
                    self::assertEquals("updateInfo", $lastMethod, "start() was called before updateInfo()");

                    return $testLifecycle;
                }
            );
        $subscriber->notify($event);
    }
}
