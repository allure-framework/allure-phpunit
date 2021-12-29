<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use Qameta\Allure\PHPUnit\Setup\ThreadDetectorInterface;

use function gethostname;

/**
 * Supported parallel runners:
 *
 * - Paratest {@link https://github.com/paratestphp/paratest}
 *
 * @internal
 */
final class DefaultThreadDetector implements ThreadDetectorInterface
{
    private string|false|null $hostName = null;

    public function getThread(): ?string
    {
        /** @var mixed $token */
        $token = $_ENV['TEST_TOKEN'] ?? null;

        return isset($token)
            ? (string) $token
            : null;
    }

    public function getHost(): ?string
    {
        $this->hostName ??= @gethostname();

        return false === $this->hostName ? null : $this->hostName;
    }
}
