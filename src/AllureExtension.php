<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit;

use PHPUnit\Runner\AfterIncompleteTestHook;
use PHPUnit\Runner\AfterRiskyTestHook;
use PHPUnit\Runner\AfterSkippedTestHook;
use PHPUnit\Runner\AfterSuccessfulTestHook;
use PHPUnit\Runner\AfterTestErrorHook;
use PHPUnit\Runner\AfterTestFailureHook;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\AfterTestWarningHook;
use PHPUnit\Runner\BeforeTestHook;
use Qameta\Allure\Allure;
use Qameta\Allure\Model\LinkType;
use Qameta\Allure\Model\Status;
use Qameta\Allure\PHPUnit\Internal\Config;
use Qameta\Allure\PHPUnit\Internal\ConfigInterface;
use Qameta\Allure\PHPUnit\Internal\DefaultThreadDetector;
use Qameta\Allure\PHPUnit\Internal\TestLifecycle;
use Qameta\Allure\PHPUnit\Internal\TestLifecycleInterface;
use Qameta\Allure\PHPUnit\Internal\TestUpdater;
use RuntimeException;

use function file_exists;
use function is_array;

use const DIRECTORY_SEPARATOR;

final class AllureExtension implements
    BeforeTestHook,
    AfterTestHook,
    AfterTestFailureHook,
    AfterTestErrorHook,
    AfterIncompleteTestHook,
    AfterSkippedTestHook,
    AfterTestWarningHook,
    AfterRiskyTestHook,
    AfterSuccessfulTestHook
{
    private const DEFAULT_OUTPUT_DIRECTORY = 'build' . DIRECTORY_SEPARATOR . 'allure-results';

    private const DEFAULT_CONFIG_FILE = 'config' . DIRECTORY_SEPARATOR . 'allure.config.php';

    private TestLifecycleInterface $testLifecycle;

    public function __construct(
        string|array|ConfigInterface|TestLifecycleInterface|null $configOrTestLifecycle = null,
    ) {
        $this->testLifecycle = $configOrTestLifecycle instanceof TestLifecycleInterface
            ? $configOrTestLifecycle
            : $this->createTestLifecycle($configOrTestLifecycle);
    }

    private function createTestLifecycle(string|array|ConfigInterface|null $configSource): TestLifecycleInterface
    {
        $config = $configSource instanceof ConfigInterface
            ? $configSource
            : $this->loadConfig($configSource);

        $this->setupAllure($config);

        return new TestLifecycle(
            Allure::getLifecycle(),
            Allure::getConfig()->getResultFactory(),
            Allure::getConfig()->getStatusDetector(),
            $config->getThreadDetector() ?? new DefaultThreadDetector(),
            AllureAdapter::getInstance(),
            new TestUpdater(Allure::getConfig()->getLinkTemplates()),
        );
    }

    private function setupAllure(ConfigInterface $config): void
    {
        Allure::getLifecycleConfigurator()->setOutputDirectory(
            $config->getOutputDirectory() ?? self::DEFAULT_OUTPUT_DIRECTORY,
        );

        foreach ($config->getLinkTemplates() as $linkType => $linkTemplate) {
            Allure::getLifecycleConfigurator()->addLinkTemplate(
                LinkType::fromOptionalString($linkType),
                $linkTemplate,
            );
        }

        if (!empty($config->getLifecycleHooks())) {
            Allure::getLifecycleConfigurator()->addHooks(...$config->getLifecycleHooks());
        }

        $setupHook = $config->getSetupHook();
        if (isset($setupHook)) {
            $setupHook();
        }
    }

    private function loadConfig(string|array|null $configSource): ConfigInterface
    {
        return new Config(
            is_array($configSource)
                ? $configSource
                : $this->loadConfigData($configSource),
        );
    }

    private function loadConfigData(?string $configFile): array
    {
        $fileShouldExist = isset($configFile);
        $configFile ??= self::DEFAULT_CONFIG_FILE;
        if (file_exists($configFile)) {
            /** @psalm-var mixed $data */
            $data = require $configFile;

            return is_array($data)
                ? $data
                : throw new RuntimeException("Config file {$configFile} must return array");
        } elseif ($fileShouldExist) {
            throw new RuntimeException("Config file {$configFile} doesn't exist");
        }

        return [];
    }
    public function executeBeforeTest(string $test): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->reset()
            ->create()
            ->updateInfo()
            ->start();
    }

    public function executeAfterTest(string $test, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->stop()
            ->updateRunInfo()
            ->write();
    }

    public function executeAfterTestFailure(string $test, string $message, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateDetectedStatus($message, Status::failed(), Status::failed());
    }

    public function executeAfterTestError(string $test, string $message, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateDetectedStatus($message, Status::broken());
    }

    public function executeAfterIncompleteTest(string $test, string $message, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateStatus($message, Status::broken());
    }

    public function executeAfterSkippedTest(string $test, string $message, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateStatus($message, Status::skipped());
    }

    public function executeAfterTestWarning(string $test, string $message, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateStatus($message, Status::broken());
    }

    public function executeAfterRiskyTest(string $test, string $message, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateStatus($message, Status::failed());
    }

    public function executeAfterSuccessfulTest(string $test, float $time): void
    {
        $this
            ->testLifecycle
            ->switchTo($test)
            ->updateStatus(status: Status::passed());
    }
}
