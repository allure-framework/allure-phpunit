<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Integration\Fixture;

use PHPUnit\Framework\TestCase;
use RuntimeException;

final class TearDownFailureTest extends TestCase
{
    public function testPassesThenTearDownFails(): void
    {
        self::assertTrue(true);
    }

    #[\Override]
    protected function tearDown(): void
    {
        throw new RuntimeException('tearDown boom');
    }
}
