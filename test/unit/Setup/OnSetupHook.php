<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Setup;

final class OnSetupHook
{
    private static int $invocationCount = 0;

    public static function reset(): void
    {
        self::$invocationCount = 0;
    }

    public static function getInvocationCount(): int
    {
        return self::$invocationCount;
    }

    public function __invoke(): void
    {
        self::$invocationCount++;
    }

    public static function method(): void
    {
        self::$invocationCount++;
    }
}
