<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit;

use Throwable;

trait ExceptionDetailsTrait
{

    protected function onNotSuccessfulTest(Throwable $t): void
    {
        SharedTestState::getInstance()->setLastException($t);
        throw $t;
    }
}
