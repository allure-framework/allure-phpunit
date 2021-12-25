<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Allure;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\AllureExtension;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;

/**
 * @covers \Qameta\Allure\PHPUnit\AllureExtension
 */
class AllureExtensionTest extends TestCase
{
    public function setUp(): void
    {
        Allure::reset();
    }

    public function testExecuteBeforeTest_Constructed_CreatesTestAfterResettingSwitchedContext(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $extension = new AllureExtension($testLifecycle);
        $testLifecycle
            ->expects(self::once())
            ->id('switch')
            ->method('switchTo')
            ->with(self::identicalTo('b'))
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

        $extension->executeBeforeTest('b');
    }

    public function testExecuteBeforeTest_Constructed_UpdatesInfoAndStartsCreatedTest(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $extension = new AllureExtension($testLifecycle);
        $testLifecycle
            ->method('switchTo')
            ->willReturnSelf();
        $testLifecycle
            ->method('reset')
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->id('create')
            ->method('create')
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->id('update')
            ->after('create')
            ->method('updateInfo')
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->after('update')
            ->method('start');

        $extension->executeBeforeTest('b');
    }

    public function testExecuteAfterTest_Constructed_StopsTestAfterSwitchingContext(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $extension = new AllureExtension($testLifecycle);

        $testLifecycle
            ->expects(self::once())
            ->id('switch')
            ->method('switchTo')
            ->with(self::identicalTo('b'))
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->after('switch')
            ->method('stop');
        $extension->executeAfterTest('b', 1.2);
    }

    public function testExecuteAfterTest_Constructed_UpdatesRunForStoppedTestAndWritesIt(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $extension = new AllureExtension($testLifecycle);

        $testLifecycle
            ->method('switchTo')
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->id('stop')
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
        $extension->executeAfterTest('b', 1.2);
    }

    public function testExecuteAfterTestFailure_Constructed_SetsDetectedOrFailedStatusForSwitchedTest(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $extension = new AllureExtension($testLifecycle);

        $testLifecycle
            ->expects(self::once())
            ->id('switch')
            ->method('switchTo')
            ->with(self::identicalTo('b'))
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->after('switch')
            ->method('updateDetectedStatus')
            ->with(
                self::identicalTo('c'),
                self::identicalTo(Status::failed()),
                self::identicalTo(Status::failed()),
            );
        $extension->executeAfterTestFailure('b', 'c', 1.2);
    }


    public function testExecuteAfterTestError_Constructed_SetsDetectedOrFailedStatusForSwitchedTest(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $extension = new AllureExtension($testLifecycle);

        $testLifecycle
            ->expects(self::once())
            ->id('switch')
            ->method('switchTo')
            ->with(self::identicalTo('b'))
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->after('switch')
            ->method('updateDetectedStatus')
            ->with(
                self::identicalTo('c'),
                self::identicalTo(Status::broken()),
                self::identicalTo(null),
            );
        $extension->executeAfterTestError('b', 'c', 1.2);
    }

    public function testExecuteAfterIncompleteTest_Constructed_SetsBrokenStatusForSwitchedTest(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $extension = new AllureExtension($testLifecycle);

        $testLifecycle
            ->expects(self::once())
            ->id('switch')
            ->method('switchTo')
            ->with(self::identicalTo('b'))
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->after('switch')
            ->method('updateStatus')
            ->with(self::identicalTo('c'), self::identicalTo(Status::broken()));
        $extension->executeAfterIncompleteTest('b', 'c', 1.2);
    }

    public function testExecuteAfterSkippedTest_Constructed_SetsSkippedStatusForSwitchedTest(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $extension = new AllureExtension($testLifecycle);

        $testLifecycle
            ->expects(self::once())
            ->id('switch')
            ->method('switchTo')
            ->with(self::identicalTo('b'))
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->after('switch')
            ->method('updateStatus')
            ->with(self::identicalTo('c'), self::identicalTo(Status::skipped()));
        $extension->executeAfterSkippedTest('b', 'c', 1.2);
    }

    public function testExecuteAfterTestWarning_Constructed_SetsBrokenStatusForSwitchedTest(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $extension = new AllureExtension($testLifecycle);

        $testLifecycle
            ->expects(self::once())
            ->id('switch')
            ->method('switchTo')
            ->with(self::identicalTo('b'))
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->after('switch')
            ->method('updateStatus')
            ->with(self::identicalTo('c'), self::identicalTo(Status::broken()));
        $extension->executeAfterTestWarning('b', 'c', 1.2);
    }

    public function testExecuteAfterRiskyTest_Constructed_SetsFailedStatusForSwitchedTest(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $extension = new AllureExtension($testLifecycle);

        $testLifecycle
            ->expects(self::once())
            ->id('switch')
            ->method('switchTo')
            ->with(self::identicalTo('b'))
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->after('switch')
            ->method('updateStatus')
            ->with(self::identicalTo('c'), self::identicalTo(Status::failed()));
        $extension->executeAfterRiskyTest('b', 'c', 1.2);
    }

    public function testExecuteAfterSuccessfulTest_Constructed_SetsPassedStatusForSwitchedTest(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $extension = new AllureExtension($testLifecycle);

        $testLifecycle
            ->expects(self::once())
            ->id('switch')
            ->method('switchTo')
            ->with(self::identicalTo('b'))
            ->willReturnSelf();
        $testLifecycle
            ->expects(self::once())
            ->after('switch')
            ->method('updateStatus')
            ->with(self::identicalTo(null), self::identicalTo(Status::passed()));
        $extension->executeAfterSuccessfulTest('b', 1.2);
    }
}
