<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit;

use Throwable;

final class SharedTestState implements SharedTestStateInterface
{

    private static ?self $instance = null;

    private ?Throwable $lastException = null;

    private function __construct()
    {
    }

    public static function getInstance(): SharedTestStateInterface
    {
        return self::$instance ??= new self();
    }

    public static function setInstance(?SharedTestStateInterface $instance): void
    {
        self::$instance = $instance;
    }

    public function reset(): void
    {
        $this->lastException = null;
    }

    public function setLastException(Throwable $e): void
    {
        $this->lastException = $e;
    }

    public function getLastException(): ?Throwable
    {
        return $this->lastException;
    }
}
