<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Report\Generate;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Allure;
use Qameta\Allure\Attribute\DisplayName;

class DataProviderTest extends TestCase
{
    #[DataProvider('providerNamed'), DisplayName('Test with named data set')]
    public function testNamedDataSet(int $x, int $y): void
    {
        Allure::runStep(fn () => self::assertSame($x, $y));
    }

    public static function providerNamed(): iterable
    {
        return [
            'Simple name' => [1, 1],
            '' => [2, 2],
            '"Double-quoted" name' => [3, 3],
            "'Single-quoted' name" => [4, 4],
            ' ' => [5, 5],
        ];
    }

    #[DataProvider('providerListed')]
    public function testListedDataSet(int $x, int $y): void
    {
        Allure::runStep(fn () => self::assertSame($x, $y));
    }

    public static function providerListed(): iterable
    {
        return [
            [1, 1],
            [2, 2],
        ];
    }
}
