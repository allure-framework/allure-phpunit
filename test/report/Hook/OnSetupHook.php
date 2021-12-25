<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Report\Hook;

use Qameta\Allure\Allure;

final class OnSetupHook
{
    public function __invoke()
    {
        Allure::getLifecycleConfigurator()
            ->addHooks(new OnLifecycleErrorHook());
    }
}
