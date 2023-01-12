<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Report\Generate;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Allure;
use Qameta\Allure\Attribute\Description;
use Qameta\Allure\Attribute\DisplayName;
use Qameta\Allure\PHPUnit\ExceptionDetailsTrait;

class RetriesTest extends TestCase
{
    use ExceptionDetailsTrait;

    /**
     * @var array<string, int>
     */
    private static array $runCounters = [];

    #[DisplayName('Reruns of successful test are reported correctly')]
    public function testRerunsOfSuccessfulTest(): void
    {
        $this->expectNotToPerformAssertions();
    }

    #[DisplayName('Reruns of failed test are reported correctly')]
    public function testRerunsOfFailedTest(): void
    {
        self::assertNotSame(1, $this->getRunIndex(__METHOD__));
    }

    /**
     * @dataProvider providerData
     */
    #[
        DisplayName('Reruns of test with data provider are reported correctly'),
        Description("Parameter `retry` has different value on each run but is excluded and doesn't have effect"),
    ]
    public function testRerunsOfTestWithDataProvider(string $firstValue, string $secondValue): void
    {
        Allure::parameter('First argument', $firstValue);
        Allure::parameter('Second argument', $secondValue);
        Allure::parameter('Run index', (string) $this->getRunIndex(__METHOD__), true);
        $this->expectNotToPerformAssertions();
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public function providerData(): iterable
    {
        return [
            'First dataset' => ['a', 'b'],
            'Second dataset' => ['b', 'b'],
        ];
    }

    /**
     * @dataProvider providerIndexedData
     */
    #[
        DisplayName('Reruns of test with indexed data provider are reported correctly'),
        Description("Parameter `retry` has different value on each run but is excluded and doesn't have effect"),
    ]
    public function testRerunsOfTestWithIndexedDataProvider(string $firstValue, string $secondValue): void
    {
        Allure::parameter('First argument', $firstValue);
        Allure::parameter('Second argument', $secondValue);
        Allure::parameter('Run index', (string) $this->getRunIndex(__METHOD__), true);
        $this->expectNotToPerformAssertions();
    }

    /**
     * @return iterable<int, array{string, string}>
     */
    public function providerIndexedData(): iterable
    {
        return [
            ['a', 'b'],
            ['b', 'b'],
        ];
    }

    private function getRunIndex(string $method): int
    {
        self::$runCounters[$method] ??= 0;

        return ++self::$runCounters[$method];
    }
}
