<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Event;

use PHPUnit\Event\Code\Test;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\PHPUnit\Event\TestFinishedSubscriber;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

#[CoversClass(TestFinishedSubscriber::class)]
final class TestFinishedSubscriberTest extends TestCase
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
            ->method('stop')
            ->willReturnCallback(
                function () use (&$lastMethod, $testLifecycle) {
                    self::assertEquals("switchTo", $lastMethod, "stop() was called before switchTo()");

                    $lastMethod = "stop";

                    return $testLifecycle;
                }
            );
        $testLifecycle
            ->expects(self::once())
            ->method('updateRunInfo')
            ->willReturnCallback(
                function () use (&$lastMethod, $testLifecycle) {
                    self::assertEquals("stop", $lastMethod, "updateRunInfo() was called before stop()");

                    $lastMethod = "updateRunInfo";

                    return $testLifecycle;
                }
            );
        $testLifecycle
            ->expects(self::once())
            ->method('write')
            ->willReturnCallback(
                function () use (&$lastMethod, $testLifecycle) {
                    self::assertEquals("updateRunInfo", $lastMethod, "write() was called before updateRunInfo()");

                    return $testLifecycle;
                }
            );
        $subscriber->notify($event);
    }
}
