<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Report\Hook;

use Qameta\Allure\Hook\OnLifecycleErrorHookInterface;
use Throwable;

final class OnLifecycleErrorHook implements OnLifecycleErrorHookInterface
{
    /**
     * @throws Throwable
     */
    public function onLifecycleError(Throwable $error): void
    {
        throw $error;
    }
}
