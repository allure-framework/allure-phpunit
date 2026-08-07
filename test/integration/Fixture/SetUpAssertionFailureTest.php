<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Integration\Fixture;

use PHPUnit\Framework\TestCase;

final class SetUpAssertionFailureTest extends TestCase
{
    #[\Override]
    protected function setUp(): void
    {
        self::fail('setUp assertion');
    }

    public function testBodyNeverRuns(): void
    {
        self::assertTrue(true);
    }
}
