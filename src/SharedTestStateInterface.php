<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit;

use Throwable;

interface SharedTestStateInterface
{

    public function reset(): void;

    public function setLastException(Throwable $e): void;

    public function getLastException(): ?Throwable;
}
