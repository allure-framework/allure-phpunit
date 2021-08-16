<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use Qameta\Allure\PHPUnit\Setup\ThreadDetectorInterface;

/**
 * Supported parallel runners:
 *
 * - Paratest {@link https://github.com/paratestphp/paratest}
 * 
 * @internal
 */
final class DefaultThreadDetector implements ThreadDetectorInterface
{

    public function getThread(): ?string
    {
        return $_ENV['UNIQUE_TEST_TOKEN'] ?? null;
    }
}
