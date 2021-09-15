<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Report\Generate;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Allure;
use Qameta\Allure\Attribute\Title;
use Qameta\Allure\PHPUnit\ExceptionDetailsTrait;
use RuntimeException;

class NegativeTest extends TestCase
{
    use ExceptionDetailsTrait;

    #[Title('Test with failed assertion is reported as failed')]
    public function testFailedWithoutSteps(): void
    {
        self::fail("Failure message");
    }

    #[Title('Test with failed assertion in step is reported as failed')]
    public function testFailedWithSteps(): void
    {
        Allure::addStep('Successful step');
        Allure::runStep(
            #[Title('Failed step')]
            function () {
                self::fail('Failure message');
            }
        );
    }

    #[Title('Test that throws exception is reported as failed')]
    public function testException(): void
    {
        throw new RuntimeException('Exception message');
    }

    #[Title('Test that throws exception in step is reported as failed')]
    public function testExceptionWithSteps(): void
    {
        Allure::addStep('Successful step');
        Allure::runStep(
            #[Title('Failed step')]
            function () {
                throw new RuntimeException('Exception message');
            }
        );
    }

    #[Title('Test that emits warning is reported as broken')]
    public function testWarning(): void
    {
        /** @psalm-suppress InternalMethod */
        $this->addWarning('Warning message');
    }

    #[Title('Skipped test is reported as skipped')]
    public function testSkipped(): void
    {
        self::markTestSkipped('Skipped message');
    }

    #[Title('Incomplete test is reported as broken')]
    public function testIncomplete(): void
    {
        self::markTestIncomplete('Incomplete message');
    }

    #[Title('Risky test is reported as failed')]
    public function testRisky(): void
    {
        self::markAsRisky();
        $this->expectNotToPerformAssertions();
    }
}
