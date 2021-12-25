<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Internal;

use Qameta\Allure\PHPUnit\Setup\ThreadDetectorInterface;

final class TestThreadDetector implements ThreadDetectorInterface
{
    public function getHost(): ?string
    {
        return null;
    }

    public function getThread(): ?string
    {
        return null;
    }
}
