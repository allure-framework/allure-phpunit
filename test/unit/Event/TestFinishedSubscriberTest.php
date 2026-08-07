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
        $test = $this->createTestMethod(class: 'a', methodName: 'b');
        $event = $this->createTestFinishedEvent($test);

        $lastMethod = null;
        $testLifecycle
            ->expects(self::once())
            ->method('switchTo')
            ->with(self::identicalTo($test))
            ->willReturnCallback(
                function () use (&$lastMethod, $testLifecycle) {
                    $lastMethod = "switchTo";

                    return $testLifecycle;
                }
            );
        $testLifecycle
            ->expects(self::once())
            ->method('completeHookFixtureSuccess')
            ->willReturnCallback(
                function () use (&$lastMethod, $testLifecycle) {
                    self::assertEquals("switchTo", $lastMethod);

                    $lastMethod = "completeHookFixtureSuccess";

                    return $testLifecycle;
                }
            );
        $testLifecycle
            ->expects(self::once())
            ->method('stop')
            ->willReturnCallback(
                function () use (&$lastMethod, $testLifecycle) {
                    self::assertEquals(
                        "completeHookFixtureSuccess",
                        $lastMethod,
                        "stop() was called before completeHookFixtureSuccess()",
                    );

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

                    $lastMethod = "write";

                    return $testLifecycle;
                }
            );
        $testLifecycle
            ->expects(self::once())
            ->method('flushPendingFinalize')
            ->willReturnCallback(
                function () use (&$lastMethod, $testLifecycle) {
                    self::assertEquals("write", $lastMethod);

                    return $testLifecycle;
                }
            );
        $subscriber->notify($event);
    }
}
