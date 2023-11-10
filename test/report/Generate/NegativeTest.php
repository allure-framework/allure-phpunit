<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Report\Generate;

use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Qameta\Allure\Allure;
use Qameta\Allure\Attribute\DisplayName;
use Qameta\Allure\PHPUnit\ExceptionDetailsTrait;
use RuntimeException;

use function trigger_error;

use const E_USER_WARNING;

class NegativeTest extends TestCase
{
    use ExceptionDetailsTrait;

    #[DisplayName('Test with failed assertion is reported as failed')]
    public function testFailedWithoutSteps(): void
    {
        self::fail("Failure message");
    }

    #[DisplayName('Test with failed assertion in step is reported as failed')]
    public function testFailedWithSteps(): void
    {
        Allure::addStep('Successful step');
        Allure::runStep(
            #[DisplayName('Failed step')]
            function () {
                self::fail('Failure message');
            }
        );
    }

    #[DisplayName('Test that throws exception is reported as failed')]
    public function testException(): void
    {
        throw new RuntimeException('Exception message');
    }

    #[DisplayName('Test that throws exception in step is reported as failed')]
    public function testExceptionWithSteps(): void
    {
        Allure::addStep('Successful step');
        Allure::runStep(
            #[DisplayName('Failed step')]
            function () {
                throw new RuntimeException('Exception message');
            }
        );
    }

    #[DisplayName('Test that emits warning is reported as broken'), DoesNotPerformAssertions]
    public function testWarning(): void
    {
        trigger_error('"Test triggered warning"', E_USER_WARNING);
    }

    #[DisplayName('Skipped test is reported as skipped')]
    public function testSkipped(): void
    {
        self::markTestSkipped('Skipped message');
    }

    #[DisplayName('Incomplete test is reported as broken')]
    public function testIncomplete(): void
    {
        self::markTestIncomplete('Incomplete message');
    }

    #[DisplayName('Risky test is reported as failed')]
    public function testRisky(): void
    {
        // Not performing assertions makes test risky
    }
}
