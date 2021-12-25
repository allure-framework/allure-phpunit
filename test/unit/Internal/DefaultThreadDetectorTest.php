<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\PHPUnit\Internal\DefaultThreadDetector;

/**
 * @covers \Qameta\Allure\PHPUnit\Internal\DefaultThreadDetector
 */
class DefaultThreadDetectorTest extends TestCase
{
    public function testGetThread_WithoutParatestToken_ReturnsNull(): void
    {
        unset($_ENV['TEST_TOKEN']);
        $detector = new DefaultThreadDetector();
        self::assertNull($detector->getThread());
    }

    public function testGetThread_WithParatestToken_ReturnsTokenValue(): void
    {
        $_ENV['TEST_TOKEN'] = 'a';
        $detector = new DefaultThreadDetector();
        self::assertSame('a', $detector->getThread());
    }

    public function testGetHost_Constructed_ReturnsHostName(): void
    {
        $detector = new DefaultThreadDetector();
        self::assertStringMatchesFormat('%a', $detector->getHost() ?? '');
    }
}
