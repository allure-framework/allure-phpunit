<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Internal;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Hook\LifecycleHookInterface;
use Qameta\Allure\PHPUnit\Internal\Config;
use Qameta\Allure\PHPUnit\Setup\ThreadDetectorInterface;
use Qameta\Allure\PHPUnit\Test\Unit\Setup\OnSetupHook;
use Qameta\Allure\Setup\LinkTemplateInterface;
use RuntimeException;
use stdClass;

#[CoversClass(Config::class)]
class ConfigTest extends TestCase
{
    #[DataProvider('providerNoOutputDirectory')]
    public function testGetOutputDirectory_EmptyData_ReturnsNull(array $data): void
    {
        $config = new Config($data);
        self::assertNull($config->getOutputDirectory());
    }

    /**
     * @return iterable<string, array{array}>
     */
    public static function providerNoOutputDirectory(): iterable
    {
        return [
            'No entry' => [[]],
            'Null entry' => [['outputDirectory' => null]],
        ];
    }

    public function testGetOutputDirectory_StringInData_ReturnsSameString(): void
    {
        $config = new Config(['outputDirectory' => 'a']);
        self::assertSame('a', $config->getOutputDirectory());
    }

    public function testGetOutputDirectory_InvalidData_ThrowsException(): void
    {
        $config = new Config(['outputDirectory' => 1]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config key "outputDirectory" should contain a string');
        $config->getOutputDirectory();
    }

    #[DataProvider('providerNoLinkTemplates')]
    public function testGetLinkTemplates_EmptyData_ReturnsEmptyList(array $data): void
    {
        $config = new Config($data);
        self::assertEmpty($config->getLinkTemplates());
    }

    /**
     * @return iterable<string, array{array}>
     */
    public static function providerNoLinkTemplates(): iterable
    {
        return [
            'No entry' => [[]],
            'Null entry' => [['linkTemplates' => null]],
            'Empty entry' => [['linkTemplates' => []]],
        ];
    }

    public function testGetLinkTemplates_InvalidData_ThrowsException(): void
    {
        $config = new Config(['linkTemplates' => 1]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config key "linkTemplates" should contain an array');
        $config->getLinkTemplates();
    }

    public function testGetLinkTemplates_InvalidKeyInData_ThrowsException(): void
    {
        $data = [
            'linkTemplates' => [1 => $this->createStub(LinkTemplateInterface::class)],
        ];
        $config = new Config($data);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config key "linkTemplates" should contain an array with string keys only');
        $config->getLinkTemplates();
    }

    public function testGetLinkTemplates_ValidObjectsInData_ReturnsSameObjects(): void
    {
        $firstLink = $this->createStub(LinkTemplateInterface::class);
        $secondLink = $this->createStub(LinkTemplateInterface::class);
        $data = [
            'linkTemplates' => ['tms' => $firstLink, 'issue' => $secondLink],
        ];
        $config = new Config($data);
        $expectedData = ['tms' => $firstLink, 'issue' => $secondLink];
        self::assertSame($expectedData, $config->getLinkTemplates());
    }

    public function testGetLinkTemplates_InvalidTypeInData_ThrowsException(): void
    {
        $data = [
            'linkTemplates' => ['tms' => 1],
        ];
        $config = new Config($data);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Config key "linkTemplates/tms" contains invalid source of Qameta\Allure\Setup\LinkTemplateInterface',
        );
        $config->getLinkTemplates();
    }

    public function testGetLinkTemplates_CallableInDataProvidesObject_ReturnsSameObject(): void
    {
        $template = $this->createStub(LinkTemplateInterface::class);
        $data = [
            'linkTemplates' => ['tms' => fn (): LinkTemplateInterface => $template],
        ];
        $config = new Config($data);
        $expectedData = ['tms' => $template];
        self::assertSame($expectedData, $config->getLinkTemplates());
    }

    public function testGetLinkTemplates_InvalidClassNameInData_ThrowsException(): void
    {
        $data = [
            'linkTemplates' => ['tms' => stdClass::class],
        ];
        $config = new Config($data);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Config key "linkTemplates/tms" contains ' .
            'invalid source of Qameta\Allure\Setup\LinkTemplateInterface',
        );
        $config->getLinkTemplates();
    }

    public function testGetLinkTemplates_ValidClassNameInData_ReturnsObjectOfSameClass(): void
    {
        $data = [
            'linkTemplates' => ['tms' => TestLinkTemplate::class],
        ];
        $config = new Config($data);
        $expectedData = ['tms' => new TestLinkTemplate()];
        self::assertEquals($expectedData, $config->getLinkTemplates());
    }

    #[DataProvider('providerNoSetupHook')]
    public function testGetSetupHook_EmptyData_ReturnsNull(array $data): void
    {
        $config = new Config($data);
        self::assertNull($config->getSetupHook());
    }

    /**
     * @return iterable<string, array{array}>
     */
    public static function providerNoSetupHook(): iterable
    {
        return [
            'No entry' => [[]],
            'Null entry' => [['onSetup' => null]],
        ];
    }

    #[DataProvider('providerInvalidSetupHook')]
    public function testGetSetupHook_InvalidTypeInData_ThrowsException(array $data): void
    {
        $config = new Config($data);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Config key "setupHook" should contain a callable');
        $config->getSetupHook();
    }

    /**
     * @return iterable<string, array{array}>
     */
    public static function providerInvalidSetupHook(): iterable
    {
        return [
            'Invalid type' => [['setupHook' => 1]],
            'Non-callable class' => [['setupHook' => stdClass::class]],
        ];
    }

    public function testGetSetupHook_ValidCallableInData_ReturnsSameInstance(): void
    {
        $hook = fn (): mixed => null;
        $config = new Config(['setupHook' => $hook]);
        self::assertSame($hook, $config->getSetupHook());
    }

    public function testGetSetupHook_CallableClassInData_ReturnsInstanceOfSameClass(): void
    {
        $config = new Config(['setupHook' => OnSetupHook::class]);
        self::assertInstanceOf(OnSetupHook::class, $config->getSetupHook());
    }

    #[DataProvider('providerNoThreadDetector')]
    public function testGetThreadDetector_EmptyData_ReturnsNull(array $data): void
    {
        $config = new Config($data);
        self::assertNull($config->getThreadDetector());
    }

    /**
     * @return iterable<string, array{array}>
     */
    public static function providerNoThreadDetector(): iterable
    {
        return [
            'No entry' => [[]],
            'Null entry' => [['threadDetector' => null]],
        ];
    }

    public function testGetThreadDetector_InvalidTypeInData_ThrowsException(): void
    {
        $config = new Config(['threadDetector' => 1]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Config key "threadDetector" contains ' .
            'invalid source of Qameta\Allure\PHPUnit\Setup\ThreadDetectorInterface',
        );
        $config->getThreadDetector();
    }

    public function testGetThreadDetector_ValidObjectInData_ReturnsSameInstance(): void
    {
        $detector = $this->createStub(ThreadDetectorInterface::class);
        $config = new Config(['threadDetector' => $detector]);
        self::assertSame($detector, $config->getThreadDetector());
    }

    public function testGetThreadDetector_CallableInDataProvidesValidObject_ReturnsSameInstance(): void
    {
        $detector = $this->createStub(ThreadDetectorInterface::class);
        $config = new Config(['threadDetector' => fn (): ThreadDetectorInterface => $detector]);
        self::assertSame($detector, $config->getThreadDetector());
    }

    public function testGetThreadDetector_InvalidClassInData_ThrowsException(): void
    {
        $config = new Config(['threadDetector' => stdClass::class]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Config key "threadDetector" contains ' .
            'invalid source of Qameta\Allure\PHPUnit\Setup\ThreadDetectorInterface',
        );
        $config->getThreadDetector();
    }

    public function testGetThreadDetector_ValidClassInData_ReturnsInstanceOfSameClass(): void
    {
        $config = new Config(['threadDetector' => TestThreadDetector::class]);
        self::assertInstanceOf(TestThreadDetector::class, $config->getThreadDetector());
    }

    #[DataProvider('providerNoLifecycleHooks')]
    public function testGetLifecycleHooks_EmptyData_ReturnsEmptyList(array $data): void
    {
        $config = new Config($data);
        self::assertEmpty($config->getLifecycleHooks());
    }

    /**
     * @return iterable<string, array{array}>
     */
    public static function providerNoLifecycleHooks(): iterable
    {
        return [
            'No entry' => [[]],
            'Null entry' => [['lifecycleHooks' => null]],
            'Empty entry' => [['lifecycleHooks' => []]],
        ];
    }

    public function testGetLifecycleHooks_InvalidIndexInData_ThrowsException(): void
    {
        $data = [
            'lifecycleHooks' => [
                'a' => $this->createStub(LifecycleHookInterface::class),
            ],
        ];
        $config = new Config($data);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Config key "lifecycleHooks" should contain an array with integer keys only',
        );
        $config->getLifecycleHooks();
    }

    public function testGetLifecycleHooks_ValidObjectsInData_ReturnsSameObjects(): void
    {
        $firstHook = $this->createStub(LifecycleHookInterface::class);
        $secondHook = $this->createStub(LifecycleHookInterface::class);
        $config = new Config(['lifecycleHooks' => [$firstHook, $secondHook]]);
        self::assertSame([$firstHook, $secondHook], $config->getLifecycleHooks());
    }

    public function testGetLifecycleHooks_ValidClassInData_ReturnsInstanceOfSameClass(): void
    {
        $config = new Config(['lifecycleHooks' => [TestLifecycleHook::class]]);
        self::assertEquals([new TestLifecycleHook()], $config->getLifecycleHooks());
    }

    public function testGetLifecycleHooks_ValidCallableInData_ReturnsMatchingObjects(): void
    {
        $config = new Config(['lifecycleHooks' => [fn () => TestLifecycleHook::class]]);
        self::assertEquals([new TestLifecycleHook()], $config->getLifecycleHooks());
    }

    public function testGetLifecycleHooks_InvalidTypeInData_ThrowsException(): void
    {
        $config = new Config(['lifecycleHooks' => [1]]);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'Config key "lifecycleHooks/0" contains ' .
            'invalid source of Qameta\Allure\Hook\LifecycleHookInterface',
        );
        $config->getLifecycleHooks();
    }
}
