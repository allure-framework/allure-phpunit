<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Integration\Fixture;

use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SetUpEmptyMessageFailureTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        throw new RuntimeException('');
    }

    public function testBodyNeverRuns(): void
    {
        self::assertTrue(true);
    }
}
