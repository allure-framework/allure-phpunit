<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use Qameta\Allure\Hook\OnLifecycleErrorHookInterface;
use Throwable;

final class ThrowExceptionOnLifecycleErrorHook implements OnLifecycleErrorHookInterface
{

    /**
     * @throws Throwable
     */
    public function onLifecycleError(Throwable $error): void
    {
        throw $error;
    }
}
