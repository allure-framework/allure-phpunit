<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Internal;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\PHPUnit\Internal\TestInfo;
use stdClass;

#[CoversClass(TestInfo::class)]
class TestInfoTest extends TestCase
{
    public function testGetTest_ConstructedWithTest_ReturnsSameValue(): void
    {
        $info = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        self::assertSame('a', $info->getTest());
    }

    /**
     * @param class-string|null $class
     */
    #[DataProvider('providerGetClass')]
    public function testGetClass_ConstructedWithClass_ReturnsSameClass(?string $class): void
    {
        $info = new TestInfo(
            test: 'a',
            class: $class,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        self::assertSame($class, $info->getClass());
    }

    /**
     * @return iterable<string, array{class-string|null}>
     */
    public static function providerGetClass(): iterable
    {
        return [
            'Null' => [null],
            'Non-null' => [stdClass::class],
        ];
    }

    #[DataProvider('providerNullableString')]
    public function testGetMethod_ConstructedWithMethod_ReturnsSameMethod(?string $method): void
    {
        $info = new TestInfo(
            test: 'a',
            class: null,
            method: $method,
            dataLabel: null,
            host: null,
            thread: null,
        );
        self::assertSame($method, $info->getMethod());
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public static function providerNullableString(): iterable
    {
        return [
            'Null' => [null],
            'Non-null' => ['b'],
        ];
    }

    #[DataProvider('providerNullableString')]
    public function testGetDataLabel_ConstructedWithDataLabel_ReturnsSameLabel(?string $dataLabel): void
    {
        $info = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: $dataLabel,
            host: null,
            thread: null,
        );
        self::assertSame($dataLabel, $info->getDataLabel());
    }

    #[DataProvider('providerNullableString')]
    public function testGetHost_ConstructedWithHost_ReturnsSameHost(?string $host): void
    {
        $info = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: $host,
            thread: null,
        );
        self::assertSame($host, $info->getHost());
    }

    #[DataProvider('providerNullableString')]
    public function testGetThread_ConstructedWithThread_ReturnsSameThread(?string $thread): void
    {
        $info = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: $thread,
        );
        self::assertSame($thread, $info->getThread());
    }

    /**
     * @param class-string|null $class
     * @param string|null $method
     * @param string|null $expectedFullName
     */
    #[DataProvider('providerGetFullName')]
    public function testGetFullName_ConstructedWithGivenClassAndMethod_ReturnsMatchingValue(
        ?string $class,
        ?string $method,
        ?string $expectedFullName,
    ): void {
        $info = new TestInfo(
            test: 'a',
            class: $class,
            method: $method,
            dataLabel: null,
            host: null,
            thread: null,
        );
        self::assertSame($expectedFullName, $info->getFullName());
    }

    /**
     * @return iterable<string, array{class-string|null, string|null, string|null}>
     */
    public static function providerGetFullName(): iterable
    {
        return [
            'Both class and method are null' => [null, null, null],
            'Only class is null' => [null, 'b', null],
            'Only method is null' => [stdClass::class, null, null],
            'Both class and method are not null' => [stdClass::class, 'b', 'stdClass::b'],
        ];
    }

    /**
     * @param string      $test
     * @param class-string|null $class
     * @param string|null $method
     * @param string      $expectedName
     */
    #[DataProvider('providerGetName')]
    public function testGetName_Constructed_ReturnsMatchingTest(
        string $test,
        ?string $class,
        ?string $method,
        string $expectedName,
    ): void {
        $info = new TestInfo(
            test: $test,
            class: $class,
            method: $method,
            dataLabel: null,
            host: null,
            thread: null,
        );
        self::assertSame($expectedName, $info->getName());
    }

    /**
     * @return iterable<string, array{string, class-string|null, string|null, string}>
     */
    public static function providerGetName(): iterable
    {
        return [
            'Class is not set' => ['a', null, 'b', 'a'],
            'Method is not set' => ['a', stdClass::class, null, 'a'],
            'Class and method are set' => ['a', stdClass::class, 'b', 'stdClass::b'],
        ];
    }
}
