<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Setup;

use Qameta\Allure\AllureLifecycleInterface;
use Qameta\Allure\Model\ResultFactoryInterface;
use Qameta\Allure\PHPUnit\AllureAdapterInterface;
use Qameta\Allure\Setup\StatusDetectorInterface;

interface ConfiguratorInterface
{

    public function setupAllure(string $outputDirectory): void;

    public function getAllureLifecycle(): ?AllureLifecycleInterface;

    public function getResultFactory(): ?ResultFactoryInterface;

    public function getStatusDetector(): ?StatusDetectorInterface;

    public function getAllureAdapter(): ?AllureAdapterInterface;

    public function getThreadDetector(): ?ThreadDetectorInterface;
}
