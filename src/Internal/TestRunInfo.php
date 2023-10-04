<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

/**
 * @internal
 */
final class TestRunInfo
{
    public function __construct(
        private readonly TestInfo $testInfo,
        private readonly string $uuid,
        private readonly ?string $rerunOf,
        private readonly int $runIndex,
        private readonly string $testCaseId,
        private readonly string $historyId,
    ) {
    }

    public function getTestInfo(): TestInfo
    {
        return $this->testInfo;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getRerunOf(): ?string
    {
        return $this->rerunOf;
    }

    public function getRunIndex(): int
    {
        return $this->runIndex;
    }

    public function getTestCaseId(): string
    {
        return $this->testCaseId;
    }

    public function getHistoryId(): string
    {
        return $this->historyId;
    }
}
