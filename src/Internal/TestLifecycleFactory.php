<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use Qameta\Allure\Allure;
use Qameta\Allure\PHPUnit\Setup\ConfiguratorInterface;
use Qameta\Allure\PHPUnit\AllureAdapter;

final class TestLifecycleFactory implements TestLifecycleFactoryInterface
{

    public function createTestLifecycle(ConfiguratorInterface $configurator): TestLifecycleInterface
    {
        return new TestLifecycle(
            $configurator->getAllureLifecycle() ?? Allure::getLifecycle(),
            $configurator->getResultFactory() ?? Allure::getResultFactory(),
            $configurator->getStatusDetector() ?? Allure::getStatusDetector(),
            $configurator->getThreadDetector() ?? new DefaultThreadDetector(),
            $configurator->getAllureAdapter() ?? AllureAdapter::getInstance(),
            new TestUpdater(),
        );
    }
}
