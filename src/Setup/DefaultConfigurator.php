<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Setup;

use Qameta\Allure\Allure;
use Qameta\Allure\AllureLifecycleInterface;
use Qameta\Allure\Model\ResultFactoryInterface;
use Qameta\Allure\PHPUnit\Internal\ThrowExceptionOnLifecycleErrorHook;
use Qameta\Allure\PHPUnit\SharedTestStateInterface;
use Qameta\Allure\Setup\StatusDetectorInterface;

final class DefaultConfigurator implements ConfiguratorInterface
{

    public function __construct(
        private bool $loggerThrowsException = true,
    ) {
    }

    public function setupAllure(string $outputDirectory): void
    {
        Allure::setOutputDirectory($outputDirectory);
        if ($this->loggerThrowsException) {
            Allure::getLifecycleConfigurator()
                ->addHooks(new ThrowExceptionOnLifecycleErrorHook());
        }
    }

    public function getAllureLifecycle(): ?AllureLifecycleInterface
    {
        return null;
    }

    public function getResultFactory(): ?ResultFactoryInterface
    {
        return null;
    }

    public function getStatusDetector(): ?StatusDetectorInterface
    {
        return null;
    }

    public function getSharedTestState(): ?SharedTestStateInterface
    {
        return null;
    }

    public function getThreadDetector(): ?ThreadDetectorInterface
    {
        return null;
    }
}
