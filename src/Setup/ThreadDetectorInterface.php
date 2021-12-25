<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Setup;

interface ThreadDetectorInterface
{
    public function getThread(): ?string;

    public function getHost(): ?string;
}
