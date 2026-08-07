<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Integration\Fixture;

use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Sample tests for hook-failure integration.
 *
 * Not covered: dynamic teardowns registered via registerMockObject / addTearDown-style
 * callbacks — same exclusion as Dart Allure adapter.
 */
final class SetUpFailureTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        throw new RuntimeException('setUp boom');
    }

    public function testBodyNeverRuns(): void
    {
        self::assertTrue(true);
    }
}
