<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

final class TestStartInfo
{
    public function __construct(
        private readonly string $containerId,
        private readonly string $testId,
    ) {
    }

    public function getContainerId(): string
    {
        return $this->containerId;
    }

    public function getTestId(): string
    {
        return $this->testId;
    }
}
