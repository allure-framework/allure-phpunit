<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Setup;

use LogicException;
use Qameta\Allure\Allure;
use Qameta\Allure\AllureLifecycleInterface;
use Qameta\Allure\Model\ResultFactoryInterface;
use Qameta\Allure\PHPUnit\AllureAdapterInterface;
use Qameta\Allure\Setup\StatusDetectorInterface;

use function call_user_func;
use function class_exists;
use function is_callable;

final class DefaultConfigurator implements ConfiguratorInterface
{

    public function __construct(
        private ?string $onAllureSetup = null,
    ) {
    }

    public function setupAllure(string $outputDirectory): void
    {
        Allure::setOutputDirectory($outputDirectory);
        if (isset($this->onAllureSetup)) {
            call_user_func($this->loadSetupHook($this->onAllureSetup));
        }
    }

    private function loadSetupHook(string $hook): callable
    {
        $callable = $this->prepareHook($hook);

        return is_callable($callable)
            ? $callable
            : throw new LogicException("Invalid setup hook: {$hook}");
    }

    private function prepareHook(string $hook): string|object
    {
        if (class_exists($hook)) {
            /** @psalm-suppress MixedMethodCall */
            return new $hook();
        }

        return $hook;
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

    public function getAllureAdapter(): ?AllureAdapterInterface
    {
        return null;
    }

    public function getThreadDetector(): ?ThreadDetectorInterface
    {
        return null;
    }
}
