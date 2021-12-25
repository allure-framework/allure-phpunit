<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Unit\Internal;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\PHPUnit\Internal\TestInfo;
use Qameta\Allure\PHPUnit\Internal\TestRunInfo;

/**
 * @covers \Qameta\Allure\PHPUnit\Internal\TestRunInfo
 */
class TestRunInfoTest extends TestCase
{
    public function testGetTestInfo_ConstructedWithTestInfo_ReturnsSameInstance(): void
    {
        $testInfo = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $runInfo = new TestRunInfo(
            testInfo: $testInfo,
            uuid: 'b',
            rerunOf: null,
            runIndex: 1,
            testCaseId: 'c',
            historyId: 'd',
        );
        self::assertSame($testInfo, $runInfo->getTestInfo());
    }

    public function testGetUuid_ConstructedWithUuid_ReturnsSameUuid(): void
    {
        $testInfo = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $runInfo = new TestRunInfo(
            testInfo: $testInfo,
            uuid: 'b',
            rerunOf: null,
            runIndex: 1,
            testCaseId: 'c',
            historyId: 'd',
        );
        self::assertSame('b', $runInfo->getUuid());
    }

    /**
     * @param string|null $rerunOf
     * @dataProvider providerRerunOf
     */
    public function testGetRerunOf_ConstructedWithRerunOf_ReturnsSameRerunOf(?string $rerunOf): void
    {
        $testInfo = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $runInfo = new TestRunInfo(
            testInfo: $testInfo,
            uuid: 'b',
            rerunOf: $rerunOf,
            runIndex: 1,
            testCaseId: 'c',
            historyId: 'd',
        );
        self::assertSame($rerunOf, $runInfo->getRerunOf());
    }

    /**
     * @return iterable<string, array{string|null}>
     */
    public function providerRerunOf(): iterable
    {
        return [
            'Null' => [null],
            'Non-null' => ['e'],
        ];
    }

    public function testGetRunIndex_ConstructedWithRunIndex_ReturnsSameIndex(): void
    {
        $testInfo = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $runInfo = new TestRunInfo(
            testInfo: $testInfo,
            uuid: 'b',
            rerunOf: null,
            runIndex: 1,
            testCaseId: 'c',
            historyId: 'd',
        );
        self::assertSame(1, $runInfo->getRunIndex());
    }

    public function testGetTestCaseId_ConstructedWithTestCaseId_ReturnsSameId(): void
    {
        $testInfo = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $runInfo = new TestRunInfo(
            testInfo: $testInfo,
            uuid: 'b',
            rerunOf: null,
            runIndex: 1,
            testCaseId: 'c',
            historyId: 'd',
        );
        self::assertSame('c', $runInfo->getTestCaseId());
    }

    public function testGetHistoryId_ConstructedWithHistoryId_ReturnsSameId(): void
    {
        $testInfo = new TestInfo(
            test: 'a',
            class: null,
            method: null,
            dataLabel: null,
            host: null,
            thread: null,
        );
        $runInfo = new TestRunInfo(
            testInfo: $testInfo,
            uuid: 'b',
            rerunOf: null,
            runIndex: 1,
            testCaseId: 'c',
            historyId: 'd',
        );
        self::assertSame('d', $runInfo->getHistoryId());
    }
}
