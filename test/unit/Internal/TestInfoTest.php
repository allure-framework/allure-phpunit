<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\PHPUnit\Internal\TestInfo;
use stdClass;

/**
 * @covers \Qameta\Allure\PHPUnit\Internal\TestInfo
 */
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
     * @dataProvider providerGetClass
     */
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
    public function providerGetClass(): iterable
    {
        return [
            'Null' => [null],
            'Non-null' => [stdClass::class],
        ];
    }

    /**
     * @dataProvider providerNullableString
     */
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
    public function providerNullableString(): iterable
    {
        return [
            'Null' => [null],
            'Non-null' => ['b'],
        ];
    }

    /**
     * @dataProvider providerNullableString
     */
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

    /**
     * @dataProvider providerNullableString
     */
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

    /**
     * @dataProvider providerNullableString
     */
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
     * @dataProvider providerGetFullName
     */
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
    public function providerGetFullName(): iterable
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
     * @dataProvider providerGetName
     */
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
    public function providerGetName(): iterable
    {
        return [
            'Class is not set' => ['a', null, 'b', 'a'],
            'Method is not set' => ['a', stdClass::class, null, 'a'],
            'Class and method are set' => ['a', stdClass::class, 'b', 'stdClass::b'],
        ];
    }
}
