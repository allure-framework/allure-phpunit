<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Setup;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Allure;
use Qameta\Allure\PHPUnit\Setup\DefaultConfigurator;
use Qameta\Allure\Setup\LifecycleBuilderInterface;

/**
 * @covers \Qameta\Allure\PHPUnit\Setup\DefaultConfigurator
 */
class DefaultConfiguratorTest extends TestCase
{

    public function setUp(): void
    {
        Allure::reset();
        OnSetupHook::reset();
    }

    public function testSetupAllure_GivenOutputDirectory_SetsSameOutputDirectoryOnFirstLifecycleAccess(): void
    {
        $builder = $this->createMock(LifecycleBuilderInterface::class);
        Allure::setLifecycleBuilder($builder);

        $configurator = new DefaultConfigurator();
        $configurator->setupAllure('a');
        $builder
            ->expects(self::once())
            ->method('createResultsWriter')
            ->with(self::identicalTo('a'));
        Allure::getLifecycle();
    }

    public function testSetupAllure_ConstructedWithDynamicSetupHook_CallsSameHook(): void
    {
        $builder = $this->createStub(LifecycleBuilderInterface::class);
        Allure::setLifecycleBuilder($builder);

        $configurator = new DefaultConfigurator(OnSetupHook::class);
        $configurator->setupAllure('a');
        self::assertSame(1, OnSetupHook::getInvocationCount());
    }

    public function testSetupAllure_ConstructedWithStaticSetupHook_CallsSameHook(): void
    {
        $builder = $this->createStub(LifecycleBuilderInterface::class);
        Allure::setLifecycleBuilder($builder);

        $configurator = new DefaultConfigurator(OnSetupHook::class . '::method');
        $configurator->setupAllure('a');
        self::assertSame(1, OnSetupHook::getInvocationCount());
    }

    public function testGetAllureLifecycle_Always_ReturnsNull(): void
    {
        $builder = $this->createStub(LifecycleBuilderInterface::class);
        Allure::setLifecycleBuilder($builder);

        $configurator = new DefaultConfigurator();
        self::assertNull($configurator->getAllureLifecycle());
    }

    public function testGetResultFactory_Always_ReturnsNull(): void
    {
        $builder = $this->createStub(LifecycleBuilderInterface::class);
        Allure::setLifecycleBuilder($builder);

        $configurator = new DefaultConfigurator();
        self::assertNull($configurator->getResultFactory());
    }

    public function testGetStatusDetectorFactory_Always_ReturnsNull(): void
    {
        $builder = $this->createStub(LifecycleBuilderInterface::class);
        Allure::setLifecycleBuilder($builder);

        $configurator = new DefaultConfigurator();
        self::assertNull($configurator->getStatusDetector());
    }

    public function testGetAllureAdapter_Always_ReturnsNull(): void
    {
        $builder = $this->createStub(LifecycleBuilderInterface::class);
        Allure::setLifecycleBuilder($builder);

        $configurator = new DefaultConfigurator();
        self::assertNull($configurator->getAllureAdapter());
    }

    public function testGetThreadDetector_Always_ReturnsNull(): void
    {
        $builder = $this->createStub(LifecycleBuilderInterface::class);
        Allure::setLifecycleBuilder($builder);

        $configurator = new DefaultConfigurator();
        self::assertNull($configurator->getThreadDetector());
    }
}
