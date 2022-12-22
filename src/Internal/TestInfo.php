<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

/**
 * @internal
 */
final class TestInfo
{
    /**
     * @param string            $test
     * @param class-string|null $class
     * @param string|null       $method
     * @param string|null       $dataLabel
     * @param string|null       $host
     * @param string|null       $thread
     */
    public function __construct(
        private readonly string $test,
        private readonly ?string $class,
        private readonly ?string $method,
        private readonly ?string $dataLabel,
        private readonly ?string $host,
        private readonly ?string $thread,
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

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getThread(): ?string
    {
        return $this->thread;
    }
}
