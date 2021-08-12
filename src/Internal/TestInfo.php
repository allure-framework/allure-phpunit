<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use function array_pad;
use function class_exists;
use function explode;
use function preg_match;

final class TestInfo
{

    public static function parse(string $test): self
    {
        $dataLabelMatchResult = preg_match(
            '#^([^\s]+)\s+with\s+data\s+set\s+"(.*)"\s+\(.+\)$#',
            $test,
            $matches,
        );

        /** @var list<string> $matches */
        if (1 === $dataLabelMatchResult) {
            $classAndMethod = $matches[1] ?? null;
            $dataLabel = $matches[2] ?? '?';
        } else {
            $classAndMethod = $test;
            $dataLabel = null;
        }

        [$class, $method] = isset($classAndMethod)
            ? array_pad(explode('::', $classAndMethod, 2), 2, null)
            : [null, null];

        /** @psalm-suppress MixedArgument */
        return new self(
            test: $test,
            class: isset($class) && class_exists($class) ? $class : null,
            method: $method,
            dataLabel: $dataLabel,
        );
    }

    /**
     * @param string      $test
     * @param class-string|null $class
     * @param string|null $method
     * @param string|null $dataLabel
     */
    private function __construct(
        private string $test,
        private ?string $class,
        private ?string $method,
        private ?string $dataLabel,
    ) {
    }

    public function getTest(): string
    {
        return $this->test;
    }

    /**
     * @return class-string|null
     */
    public function getClass(): ?string
    {
        return $this->class;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function getDataLabel(): ?string
    {
        return $this->dataLabel;
    }

    public function getFullName(): ?string
    {
        return isset($this->class, $this->method)
            ? "{$this->class}::{$this->method}"
            : null;
    }

    public function getName(): string
    {
        return $this->getFullName() ?? $this->getTest();
    }
}
