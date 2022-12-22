<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit;

use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;
use Qameta\Allure\Allure;
use Qameta\Allure\Model\LinkType;
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

final class AllureExtension implements Extension
{
    private const DEFAULT_OUTPUT_DIRECTORY = 'build' . DIRECTORY_SEPARATOR . 'allure-results';

    private const DEFAULT_CONFIG_FILE = 'config' . DIRECTORY_SEPARATOR . 'allure.config.php';

    public function __construct(
        private readonly ?TestLifecycleInterface $testLifecycle = null,
    ) {
    }

    private function createTestLifecycle(?string $configSource): TestLifecycleInterface
    {
        $config = new Config($this->loadConfigData($configSource));
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

    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $configSource = $parameters->has('config')
            ? $parameters->get('config')
            : null;

        $testLifecycle = $this->testLifecycle ?? $this->createTestLifecycle($configSource);

        $facade->registerSubscribers(
            new Event\TestPreparationStartedSubscriber($testLifecycle),
            new Event\TestPreparedSubscriber($testLifecycle),
            new Event\TestFinishedSubscriber($testLifecycle),
            new Event\TestFailedSubscriber($testLifecycle),
            new Event\TestErroredSubscriber($testLifecycle),
            new Event\TestMarkedIncompleteSubscriber($testLifecycle),
            new Event\TestSkippedSubscriber($testLifecycle),
            new Event\TestWarningTriggeredSubscriber($testLifecycle),
            new Event\TestConsideredRiskySubscriber($testLifecycle),
            new Event\TestPassedSubscriber($testLifecycle),
        );
    }
}
