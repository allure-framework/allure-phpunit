<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit;

use Throwable;

trait ExceptionDetailsTrait
{
    protected function onNotSuccessfulTest(Throwable $t): never
    {
        AllureAdapter::getInstance()->setLastException($t);
        throw $t;
    }
}
