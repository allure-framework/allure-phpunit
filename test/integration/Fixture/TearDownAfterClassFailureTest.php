<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Integration\Fixture;

use PHPUnit\Framework\TestCase;
use RuntimeException;

final class TearDownAfterClassFailureTest extends TestCase
{
    public function testPassesThenClassTearDownFails(): void
    {
        self::assertTrue(true);
    }

    #[\Override]
    public static function tearDownAfterClass(): void
    {
        throw new RuntimeException('tearDownAfterClass boom');
    }
}
