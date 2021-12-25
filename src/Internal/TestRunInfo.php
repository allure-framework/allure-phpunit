<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

/**
 * @internal
 */
final class TestRunInfo
{
    public function __construct(
        private TestInfo $testInfo,
        private string $uuid,
        private ?string $rerunOf,
        private int $runIndex,
        private string $testCaseId,
        private string $historyId,
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
