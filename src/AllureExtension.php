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

use function class_exists;
use function file_exists;
use function interface_exists;
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

    #[\Override]
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $configSource = $parameters->has('config')
            ? $parameters->get('config')
            : null;

        $testLifecycle = $this->testLifecycle ?? $this->createTestLifecycle($configSource);

        $subscribers = [
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
            new Event\TestSuiteFinishedSubscriber($testLifecycle),
        ];

        $facade->registerSubscribers(...$subscribers, ...$this->createHookSubscribers($testLifecycle));
    }

    /**
     * @return list<object>
     */
    private function createHookSubscribers(TestLifecycleInterface $testLifecycle): array
    {
        $subscribers = [];

        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\BeforeTestMethodCalledSubscriber::class,
            static fn () => new Event\BeforeTestMethodCalledSubscriber($testLifecycle),
        );
        $this->addFailedHookSubscriberIfSupported(
            $subscribers,
            'PHPUnit\\Event\\Test\\BeforeTestMethodFailedSubscriber',
            Event\BeforeTestMethodFailedSubscriber::class,
            $testLifecycle,
        );
        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\BeforeTestMethodErroredSubscriber::class,
            static fn () => new Event\BeforeTestMethodErroredSubscriber($testLifecycle),
        );
        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\BeforeTestMethodFinishedSubscriber::class,
            static fn () => new Event\BeforeTestMethodFinishedSubscriber($testLifecycle),
        );

        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\AfterTestMethodCalledSubscriber::class,
            static fn () => new Event\AfterTestMethodCalledSubscriber($testLifecycle),
        );
        $this->addFailedHookSubscriberIfSupported(
            $subscribers,
            'PHPUnit\\Event\\Test\\AfterTestMethodFailedSubscriber',
            Event\AfterTestMethodFailedSubscriber::class,
            $testLifecycle,
        );
        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\AfterTestMethodErroredSubscriber::class,
            static fn () => new Event\AfterTestMethodErroredSubscriber($testLifecycle),
        );
        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\AfterTestMethodFinishedSubscriber::class,
            static fn () => new Event\AfterTestMethodFinishedSubscriber($testLifecycle),
        );

        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\PreConditionCalledSubscriber::class,
            static fn () => new Event\PreConditionCalledSubscriber($testLifecycle),
        );
        $this->addFailedHookSubscriberIfSupported(
            $subscribers,
            'PHPUnit\\Event\\Test\\PreConditionFailedSubscriber',
            Event\PreConditionFailedSubscriber::class,
            $testLifecycle,
        );
        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\PreConditionErroredSubscriber::class,
            static fn () => new Event\PreConditionErroredSubscriber($testLifecycle),
        );
        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\PreConditionFinishedSubscriber::class,
            static fn () => new Event\PreConditionFinishedSubscriber($testLifecycle),
        );

        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\PostConditionCalledSubscriber::class,
            static fn () => new Event\PostConditionCalledSubscriber($testLifecycle),
        );
        $this->addFailedHookSubscriberIfSupported(
            $subscribers,
            'PHPUnit\\Event\\Test\\PostConditionFailedSubscriber',
            Event\PostConditionFailedSubscriber::class,
            $testLifecycle,
        );
        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\PostConditionErroredSubscriber::class,
            static fn () => new Event\PostConditionErroredSubscriber($testLifecycle),
        );
        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\PostConditionFinishedSubscriber::class,
            static fn () => new Event\PostConditionFinishedSubscriber($testLifecycle),
        );

        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\BeforeFirstTestMethodCalledSubscriber::class,
            static fn () => new Event\BeforeFirstTestMethodCalledSubscriber($testLifecycle),
        );
        $this->addFailedHookSubscriberIfSupported(
            $subscribers,
            'PHPUnit\\Event\\Test\\BeforeFirstTestMethodFailedSubscriber',
            Event\BeforeFirstTestMethodFailedSubscriber::class,
            $testLifecycle,
        );
        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\BeforeFirstTestMethodErroredSubscriber::class,
            static fn () => new Event\BeforeFirstTestMethodErroredSubscriber($testLifecycle),
        );
        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\BeforeFirstTestMethodFinishedSubscriber::class,
            static fn () => new Event\BeforeFirstTestMethodFinishedSubscriber($testLifecycle),
        );

        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\AfterLastTestMethodCalledSubscriber::class,
            static fn () => new Event\AfterLastTestMethodCalledSubscriber($testLifecycle),
        );
        $this->addFailedHookSubscriberIfSupported(
            $subscribers,
            'PHPUnit\\Event\\Test\\AfterLastTestMethodFailedSubscriber',
            Event\AfterLastTestMethodFailedSubscriber::class,
            $testLifecycle,
        );
        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\AfterLastTestMethodErroredSubscriber::class,
            static fn () => new Event\AfterLastTestMethodErroredSubscriber($testLifecycle),
        );
        $this->addSubscriberIfSupported(
            $subscribers,
            \PHPUnit\Event\Test\AfterLastTestMethodFinishedSubscriber::class,
            static fn () => new Event\AfterLastTestMethodFinishedSubscriber($testLifecycle),
        );

        return $subscribers;
    }

    /**
     * Failed-hook subscriber interfaces exist only on PHPUnit >= 12.5.
     * Build the implementing wrapper at runtime so Psalm on older PHPUnit
     * does not see missing interface names in source.
     *
     * @param list<object> $subscribers
     * @param non-empty-string $subscriberInterface
     * @param class-string $implementationClass
     *
     * @psalm-suppress ForbiddenCode
     * @psalm-suppress MixedAssignment
     * @psalm-suppress MixedArgument
     * @psalm-suppress MixedMethodCall
     * @psalm-suppress ReferenceConstraintViolation
     */
    private function addFailedHookSubscriberIfSupported(
        array &$subscribers,
        string $subscriberInterface,
        string $implementationClass,
        TestLifecycleInterface $testLifecycle,
    ): void {
        if (!interface_exists($subscriberInterface)) {
            return;
        }

        /** @var object $inner */
        $inner = new $implementationClass($testLifecycle);

        /** @var object $wrapper */
        $wrapper = eval(
            'return new class ($inner) implements \\' . $subscriberInterface
            . ' {
                public function __construct(private readonly object $inner)
                {
                }

                public function notify(object $event): void
                {
                    $this->inner->notify($event);
                }
            };'
        );

        $subscribers[] = $wrapper;
    }

    /**
     * @param list<object> $subscribers
     * @param class-string|non-empty-string $subscriberInterface
     * @param callable():object $factory
     */
    private function addSubscriberIfSupported(array &$subscribers, string $subscriberInterface, callable $factory): void
    {
        if (!interface_exists($subscriberInterface) && !class_exists($subscriberInterface)) {
            return;
        }

        $subscribers[] = $factory();
    }
}
