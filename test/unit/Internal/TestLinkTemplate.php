<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Internal;

use Qameta\Allure\Setup\LinkTemplateInterface;

final class TestLinkTemplate implements LinkTemplateInterface
{
    public function buildUrl(?string $name): ?string
    {
        return null;
    }
}
