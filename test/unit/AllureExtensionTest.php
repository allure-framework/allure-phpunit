<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit;

use LogicException;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Allure;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\AllureExtension;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;
use Qameta\Allure\PHPUnit\Setup\ConfiguratorInterface;
use stdClass;

use const DIRECTORY_SEPARATOR;
use const STDERR;
use const STDOUT;

/**
 * @covers \Qameta\Allure\PHPUnit\AllureExtension
 */
class AllureExtensionTest extends TestCase
{

    public function setUp(): void
    {
        Allure::reset();
        TestConfigurator::reset();
    }

    /**
     * @dataProvider providerOutputDirectory
     */
    public function testConstruct_GivenOutputDirectory_SetupsAllureWithMatchingDirectory(
        ?string $outputDirectory,
        string $expectedValue,
    ): void {
        $configurator = $this->createMock(TestConfiguratorInterface::class);
        $configurator
            ->expects(self::once())
            ->method('setupAllure')
            ->with(self::identicalTo($expectedValue));
        new AllureExtension($outputDirectory, $configurator);
    }

    /**
     * @return iterable<string, array{string|null, string}>
     */
    public function providerOutputDirectory(): iterable
    {
        return [
            'Null' => [null, 'build' . DIRECTORY_SEPARATOR . 'allure-results'],
            'Non-null' => ['a', 'a'],
        ];
    }

    /**
     * @dataProvider providerInvalidConfigurator
     */
    public function testConstructor_GivenInvalidConfiguratorString_ThrowsException(string $configurator): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Invalid configurator class');
        new AllureExtension(null, $configurator);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public function providerInvalidConfigurator(): iterable
    {
        return [
            'Not a class' => ['a'],
            'Class not implementing configurator' => [stdClass::class],
        ];
    }

    /**
     * @dataProvider providerValidConfigurator
     */
    public function testConstructor_GivenValidConfiguratorString_NeverThrowsException(
        ?string $configurator,
    ): void {
        $this->expectNotToPerformAssertions();
        new AllureExtension(null, $configurator);
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public function providerValidConfigurator(): iterable
    {
        return [
            'Null' => [null],
            'Default configurator' => [TestConfigurator::class],
        ];
    }

    /**
     * @dataProvider providerConfiguratorArgs
     */
    public function testConstructor_GivenValidConfiguratorStringWithArgs_PassesSameArgs(array $args): void
    {
        new AllureExtension(null, TestConfigurator::class, ...$args);
        self::assertSame($args, TestConfigurator::getArgs());
    }

    /**
     * @return iterable<string, array{array}>
     */
    public function providerConfiguratorArgs(): iterable
    {
        return [
            'No args' => [[]],
            'Null args' => [[null, null]],
            'Integer args' => [[1, 2]],
            'Float args' => [[1.2, 3.4]],
            'String args' => [['a', 'b']],
            'Boolean args' => [[true, false]],
            'Array args' => [[['a' => 'b'], [1, 2]]],
            'Object args' => [[(object) ['a' => 'b'], (object) ['c' => 'd']]],
            'Resource args' => [[STDOUT, STDERR]],
        ];
    }

    public function testExecuteBeforeTest_Constructed_CreatesTestAfterResettingSwitchedContext(): void
    {
        $testLifecycle = $this->createMock(TestLifecycleInterface::class);
        $extension = new AllureExtension(
            'a',
            $this->createConfiguratorWithTestLifecycle($testLifecycle),
        );
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
        $extension = new AllureExtension(
            'a',
            $this->createConfiguratorWithTestLifecycle($testLifecycle),
        );
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
        $extension = new AllureExtension(
            'a',
            $this->createConfiguratorWithTestLifecycle($testLifecycle),
        );

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
        $extension = new AllureExtension(
            'a',
            $this->createConfiguratorWithTestLifecycle($testLifecycle),
        );

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
        $extension = new AllureExtension(
            'a',
            $this->createConfiguratorWithTestLifecycle($testLifecycle),
        );

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
        $extension = new AllureExtension(
            'a',
            $this->createConfiguratorWithTestLifecycle($testLifecycle),
        );

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
        $extension = new AllureExtension(
            'a',
            $this->createConfiguratorWithTestLifecycle($testLifecycle),
        );

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
        $extension = new AllureExtension(
            'a',
            $this->createConfiguratorWithTestLifecycle($testLifecycle),
        );

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
        $extension = new AllureExtension(
            'a',
            $this->createConfiguratorWithTestLifecycle($testLifecycle),
        );

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
        $extension = new AllureExtension(
            'a',
            $this->createConfiguratorWithTestLifecycle($testLifecycle),
        );

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
        $extension = new AllureExtension(
            'a',
            $this->createConfiguratorWithTestLifecycle($testLifecycle),
        );

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

    private function createConfiguratorWithTestLifecycle(TestLifecycleInterface $testLifecycle): ConfiguratorInterface
    {
        $configurator = $this->createStub(TestConfiguratorInterface::class);
        $configurator
            ->method('createTestLifecycle')
            ->willReturn($testLifecycle);

        return $configurator;
    }
}
