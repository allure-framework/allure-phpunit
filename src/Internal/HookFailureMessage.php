<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use function trim;

/**
 * @internal
 */
final class HookFailureMessage
{
    public static function format(string $hookName, ?string $originalMessage): string
    {
        $trimmed = trim((string) $originalMessage);

        return $trimmed === ''
            ? "{$hookName} failed"
            : "{$hookName} failed: {$trimmed}";
    }
}
