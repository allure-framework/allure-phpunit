<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Internal;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\PHPUnit\Internal\DefaultThreadDetector;

/**
 * @covers \Qameta\Allure\PHPUnit\Internal\DefaultThreadDetector
 */
class DefaultThreadDetectorTest extends TestCase
{

    public function testGetThread_WithoutParatestToken_ReturnsNull(): void
    {
        unset($_ENV['UNIQUE_TEST_TOKEN']);
        $detector = new DefaultThreadDetector();
        self::assertNull($detector->getThread());
    }
}
