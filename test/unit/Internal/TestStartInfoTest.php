<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Internal;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\PHPUnit\Internal\TestStartInfo;

#[CoversClass(TestStartInfo::class)]
class TestStartInfoTest extends TestCase
{
    public function testGetContainerId_ConstructedWithContainerId_ReturnsSameId(): void
    {
        $info = new TestStartInfo('a', 'b');
        self::assertSame('a', $info->getContainerId());
    }

    public function testGetTestId_ConstructedWithContainerId_ReturnsSameId(): void
    {
        $info = new TestStartInfo('a', 'b');
        self::assertSame('b', $info->getTestId());
    }
}
