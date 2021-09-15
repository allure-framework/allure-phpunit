<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use Qameta\Allure\PHPUnit\Setup\ConfiguratorInterface;

interface TestLifecycleFactoryInterface
{

    public function createTestLifecycle(ConfiguratorInterface $configurator): TestLifecycleInterface;
}
