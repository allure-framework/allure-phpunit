<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Internal;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\PHPUnit\Internal\HookFailureMessage;

#[CoversClass(HookFailureMessage::class)]
final class HookFailureMessageTest extends TestCase
{
    #[DataProvider('providerFormat')]
    public function testFormat_MessageVariants_ReturnsPrefixedOrFallback(
        string $hookName,
        ?string $original,
        string $expected,
    ): void {
        self::assertSame($expected, HookFailureMessage::format($hookName, $original));
    }

    /**
     * @return iterable<string, array{string, ?string, string}>
     */
    public static function providerFormat(): iterable
    {
        yield 'with details' => ['setUp', 'boom', 'setUp failed: boom'];
        yield 'empty string' => ['setUp', '', 'setUp failed'];
        yield 'whitespace only' => ['tearDown', "  \t  ", 'tearDown failed'];
        yield 'null' => ['setUpBeforeClass', null, 'setUpBeforeClass failed'];
        yield 'assertion message' => [
            'assertPreConditions',
            'Failed asserting that false is true.',
            'assertPreConditions failed: Failed asserting that false is true.',
        ];
    }
}
