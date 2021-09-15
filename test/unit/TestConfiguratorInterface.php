<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit;

use Qameta\Allure\PHPUnit\Internal\TestLifecycleFactoryInterface;
use Qameta\Allure\PHPUnit\Setup\ConfiguratorInterface;

interface TestConfiguratorInterface extends ConfiguratorInterface, TestLifecycleFactoryInterface
{
}
