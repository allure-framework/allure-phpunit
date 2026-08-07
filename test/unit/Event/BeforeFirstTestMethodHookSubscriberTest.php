<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Event;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Event\BeforeFirstTestMethodCalledSubscriber;
use Qameta\Allure\PHPUnit\Event\BeforeFirstTestMethodErroredSubscriber;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

#[CoversClass(BeforeFirstTestMethodCalledSubscriber::class)]
#[CoversClass(BeforeFirstTestMethodErroredSubscriber::class)]
final class BeforeFirstTestMethodHookSubscriberTest extends TestCase
{
    use EventTestTrait;

    public function testCalled_EnsuresClassContainerAndStartsBeforeFixture(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $subscriber = new BeforeFirstTestMethodCalledSubscriber($testLifecycle);
        $event = $this->createBeforeFirstTestMethodCalledEvent(self::class, 'setUpBeforeClass');

        $testLifecycle
            ->expects(self::once())
            ->method('ensureClassContainer')
            ->with(self::identicalTo(self::class))
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->method('startBeforeHookFixture')
            ->with(self::identicalTo('setUpBeforeClass'))
            ->willReturnSelf();

        $subscriber->notify($event);
    }

    public function testErrored_WritesGlobalViaFailureAndClassContainer(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $subscriber = new BeforeFirstTestMethodErroredSubscriber($testLifecycle);
        $event = $this->createBeforeFirstTestMethodErroredEvent(
            self::class,
            'class boom',
            'setUpBeforeClass',
        );

        $testLifecycle
            ->expects(self::once())
            ->method('ensureClassContainer')
            ->with(self::identicalTo(self::class))
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->method('completeHookFixtureFailure')
            ->with(
                self::identicalTo(Status::broken()),
                self::identicalTo('class boom'),
                self::anything(),
            )
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->method('writeClassContainer')
            ->with(self::identicalTo(self::class))
            ->willReturnSelf();

        $subscriber->notify($event);
    }
}
