<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function array_filter;
use function array_map;
use function array_values;
use function escapeshellarg;
use function file_get_contents;
use function glob;
use function implode;
use function is_array;
use function json_encode;
use function mkdir;
use function sprintf;
use function str_starts_with;
use function sys_get_temp_dir;
use function uniqid;

/**
 * Runs sample hook-failure tests in a subprocess and asserts Allure fixtures + globals.
 *
 * Dynamic/registerMockObject-style teardowns are intentionally not covered
 * (same exclusion as Dart Allure adapter addTearDown).
 */
final class HookFailureIntegrationTest extends TestCase
{
    #[DataProvider('providerHookFailures')]
    public function testHookFailure_EmitsFixtureAndSinglePrefixedGlobal(
        string $filter,
        string $hookName,
        string $messagePrefix,
        string $fixtureStatus,
    ): void {
        $outputDir = sys_get_temp_dir() . '/allure-phpunit-hook-int-' . uniqid('', true);
        mkdir($outputDir);

        $phpunit = escapeshellarg(__DIR__ . '/../../vendor/bin/phpunit');
        $filterArg = escapeshellarg($filter);
        $cmd = sprintf(
            'cd %s && ALLURE_HOOK_OUTPUT=%s %s --configuration=phpunit.hooks.xml --filter=%s 2>&1',
            escapeshellarg(__DIR__),
            escapeshellarg($outputDir),
            $phpunit,
            $filterArg,
        );

        exec($cmd, $output, $exitCode);
        self::assertNotSame(0, $exitCode, implode("\n", $output));

        $globals = $this->readGlobalMessages($outputDir);
        $matching = array_values(array_filter(
            $globals,
            static fn (string $message): bool => str_starts_with($message, $messagePrefix),
        ));
        self::assertCount(
            1,
            $matching,
            sprintf(
                "Expected one global starting with %s, got: %s\nPHPUnit output:\n%s",
                $messagePrefix,
                (string) json_encode($globals),
                implode("\n", $output),
            ),
        );

        $fixtures = $this->readFixturesNamed($outputDir, $hookName);
        self::assertNotEmpty($fixtures, "Expected fixture named {$hookName}");
        $statuses = array_map(
            static fn (array $fixture): ?string => $fixture['status'] ?? null,
            $fixtures,
        );
        self::assertContains($fixtureStatus, $statuses, (string) json_encode($fixtures));
    }

    /**
     * @return iterable<string, array{string, string, string, string}>
     */
    public static function providerHookFailures(): iterable
    {
        yield 'setUp throw' => [
            'SetUpFailureTest',
            'setUp',
            'setUp failed',
            'broken',
        ];
        yield 'tearDown throw' => [
            'TearDownFailureTest',
            'tearDown',
            'tearDown failed',
            'broken',
        ];
        yield 'setUpBeforeClass throw' => [
            'SetUpBeforeClassFailureTest',
            'setUpBeforeClass',
            'setUpBeforeClass failed',
            'broken',
        ];
        yield 'tearDownAfterClass throw' => [
            'TearDownAfterClassFailureTest',
            'tearDownAfterClass',
            'tearDownAfterClass failed',
            'broken',
        ];
        yield 'setUp assertion' => [
            'SetUpAssertionFailureTest',
            'setUp',
            'setUp failed',
            'failed',
        ];
        yield 'setUp empty message' => [
            'SetUpEmptyMessageFailureTest',
            'setUp',
            'setUp failed',
            'broken',
        ];
    }

    /**
     * @return list<string>
     */
    private function readGlobalMessages(string $outputDir): array
    {
        $messages = [];
        $files = glob($outputDir . '/*-globals.json');
        if (!is_array($files)) {
            return [];
        }

        foreach ($files as $file) {
            /** @var array{errors?: list<array{message?: string}>} $data */
            $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            foreach ($data['errors'] ?? [] as $error) {
                if (isset($error['message'])) {
                    $messages[] = $error['message'];
                }
            }
        }

        return $messages;
    }

    /**
     * @return list<array{name?: string, status?: string}>
     */
    private function readFixturesNamed(string $outputDir, string $hookName): array
    {
        $fixtures = [];
        $files = glob($outputDir . '/*-container.json');
        if (!is_array($files)) {
            return [];
        }

        foreach ($files as $file) {
            /**
             * @var array{
             *     befores?: list<array{name?: string, status?: string}>,
             *     afters?: list<array{name?: string, status?: string}>
             * } $data
             */
            $data = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
            foreach ([...($data['befores'] ?? []), ...($data['afters'] ?? [])] as $fixture) {
                if (($fixture['name'] ?? null) === $hookName) {
                    $fixtures[] = $fixture;
                }
            }
        }

        return $fixtures;
    }
}
