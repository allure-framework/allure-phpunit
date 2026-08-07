<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Integration\Fixture;

use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SetUpBeforeClassFailureTest extends TestCase
{
    #[\Override]
    public static function setUpBeforeClass(): void
    {
        throw new RuntimeException('setUpBeforeClass boom');
    }

    public function testBodyNeverRuns(): void
    {
        self::assertTrue(true);
    }
}
