<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit;

use Qameta\Allure\AllureLifecycleInterface;
use Qameta\Allure\Model\ResultFactoryInterface;
use Qameta\Allure\PHPUnit\AllureAdapterInterface;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;
use Qameta\Allure\PHPUnit\Setup\ConfiguratorInterface;
use Qameta\Allure\PHPUnit\Setup\ThreadDetectorInterface;
use Qameta\Allure\Setup\StatusDetectorInterface;

final class TestConfigurator implements TestConfiguratorInterface
{

    private static ?array $args = null;

    public function __construct(mixed ...$args)
    {
        self::$args = $args;
    }

    public static function reset(): void
    {
        self::$args = null;
    }

    public static function getArgs(): ?array
    {
        return self::$args;
    }

    public function createTestLifecycle(ConfiguratorInterface $configurator): TestLifecycleInterface
    {
        return new TestTestLifecycle();
    }

    public function setupAllure(string $outputDirectory): void
    {
    }

    public function getAllureAdapter(): ?AllureAdapterInterface
    {
        return null;
    }

    public function getAllureLifecycle(): ?AllureLifecycleInterface
    {
        return null;
    }

    public function getResultFactory(): ?ResultFactoryInterface
    {
        return null;
    }

    public function getThreadDetector(): ?ThreadDetectorInterface
    {
        return null;
    }

    public function getStatusDetector(): ?StatusDetectorInterface
    {
        return null;
    }
}
