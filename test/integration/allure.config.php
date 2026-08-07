<?php

declare(strict_types=1);

return [
    'outputDirectory' => ($output = getenv('ALLURE_HOOK_OUTPUT')) !== false && $output !== ''
        ? $output
        : sys_get_temp_dir() . '/allure-phpunit-hook-int',
];
