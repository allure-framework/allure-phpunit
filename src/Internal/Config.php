<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use Qameta\Allure\Hook\LifecycleHookInterface;
use Qameta\Allure\PHPUnit\Setup\ThreadDetectorInterface;
use Qameta\Allure\Setup\LinkTemplateInterface;
use RuntimeException;

use function class_exists;
use function is_a;
use function is_array;
use function is_callable;
use function is_int;
use function is_string;

final class Config implements ConfigInterface
{
    public function __construct(
        private array $data,
    ) {
    }

    public function getOutputDirectory(): ?string
    {
        $key = 'outputDirectory';
        if (!isset($this->data[$key])) {
            return null;
        }

        /** @psalm-var mixed $outputDirectory */
        $outputDirectory = $this->data[$key];

        return is_string($outputDirectory)
            ? $outputDirectory
            : throw new RuntimeException("Config key \"{$key}\" should contain a string");
    }

    /**
     * @return array<string, LinkTemplateInterface>
     */
    public function getLinkTemplates(): array
    {
        $key = 'linkTemplates';
        $linkTemplates = [];
        /** @psalm-var mixed $linkTemplateSource */
        foreach ($this->getArrayFromData($key) as $linkKey => $linkTemplateSource) {
            if (!is_string($linkKey)) {
                throw new RuntimeException(
                    "Config key \"{$key}\" should contain an array with string keys only",
                );
            }
            $linkTemplates[$linkKey] = $this->buildObject(
                "{$key}/{$linkKey}",
                $linkTemplateSource,
                LinkTemplateInterface::class,
            );
        }

        return $linkTemplates;
    }

    private function getArrayFromData(string $key): array
    {
        /** @psalm-var mixed $source */
        $source = $this->data[$key] ?? [];

        return is_array($source)
            ? $source
            : throw new RuntimeException("Config key \"{$key}\" should contain an array");
    }

    /**
     * @template T
     * @param string          $key
     * @param mixed           $source
     * @param class-string<T> $expectedClass
     * @return T
     * @psalm-suppress MixedMethodCall
     */
    private function buildObject(string $key, mixed $source, string $expectedClass): object
    {
        return match (true) {
            $source instanceof $expectedClass => $source,
            $this->isExpectedClassName($source, $expectedClass) => new $source(),
            is_callable($source) => $this->buildObject($key, $source(), $expectedClass),
            default => throw new RuntimeException(
                "Config key \"{$key}\" contains invalid source of {$expectedClass}",
            ),
        };
    }

    /**
     * @template T
     * @param mixed  $source
     * @param class-string<T> $expectedClass
     * @return bool
     * @psalm-assert-if-true class-string<T> $source
     */
    private function isExpectedClassName(mixed $source, string $expectedClass): bool
    {
        return $this->isClassName($source) && is_a($source, $expectedClass, true);
    }

    /**
     * @psalm-assert-if-true class-string $source
     */
    private function isClassName(mixed $source): bool
    {
        return is_string($source) && class_exists($source);
    }

    public function getSetupHook(): ?callable
    {
        $key = 'setupHook';
        /** @psalm-var mixed $source */
        $source = $this->data[$key] ?? null;

        return isset($source)
            ? $this->buildCallable($key, $source)
            : null;
    }

    /**
     * @psalm-suppress MixedMethodCall
     */
    private function buildCallable(string $key, mixed $source): callable
    {
        return match (true) {
            is_callable($source) => $source,
            $this->isClassName($source) => $this->buildCallable($key, new $source()),
            default => throw new RuntimeException("Config key \"{$key}\" should contain a callable"),
        };
    }

    public function getThreadDetector(): ?ThreadDetectorInterface
    {
        $key = 'threadDetector';
        /** @var mixed $threadDetector */
        $threadDetector = $this->data[$key] ?? null;

        return isset($threadDetector)
            ? $this->buildObject($key, $threadDetector, ThreadDetectorInterface::class)
            : null;
    }

    /**
     * @return list<LifecycleHookInterface>
     */
    public function getLifecycleHooks(): array
    {
        $key = 'lifecycleHooks';
        $hooks = [];
        /** @psalm-var mixed $hookSource */
        foreach ($this->getArrayFromData($key) as $index => $hookSource) {
            if (!is_int($index)) {
                throw new RuntimeException(
                    "Config key \"{$key}\" should contain an array with integer keys only",
                );
            }
            $hooks[] = $this->buildObject(
                "{$key}/{$index}",
                $hookSource,
                LifecycleHookInterface::class,
            );
        }

        return $hooks;
    }
}
