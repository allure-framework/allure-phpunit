<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use Qameta\Allure\Hook\LifecycleHookInterface;
use Qameta\Allure\PHPUnit\Setup\ThreadDetectorInterface;
use Qameta\Allure\Setup\LinkTemplateInterface;

interface ConfigInterface
{
    public function getOutputDirectory(): ?string;

    /**
     * @return array<string, LinkTemplateInterface>
     */
    public function getLinkTemplates(): array;

    public function getSetupHook(): ?callable;

    public function getThreadDetector(): ?ThreadDetectorInterface;

    /**
     * @return list<LifecycleHookInterface>
     */
    public function getLifecycleHooks(): array;
}
