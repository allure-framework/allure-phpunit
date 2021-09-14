<?php

declare(strict_types=1);

namespace Yandex\Allure\Report\Generate;

use Exception;
use PHPUnit\Framework\TestCase;
use Yandex\Allure\Adapter\Annotation\Description;
use Yandex\Allure\Adapter\Support\AttachmentSupport;
use Yandex\Allure\Adapter\Support\StepSupport;

/**
 * @Description ("Steps tests for allure-phpunit")
 */
class StepsTest extends TestCase
{
    use StepSupport;
    use AttachmentSupport;

    public function testNoStepsSuccess(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @throws Exception
     */
    public function testNoStepsError(): void
    {
        throw new Exception('Error');
    }

    public function testNoStepsFailure(): void
    {
        self::fail('Failure');
    }

    public function testNoStepsSkipped(): void
    {
        self::markTestSkipped('Skipped');
    }

    public function testSingleSuccessfulStepWithTitle(): void
    {
        $this->expectNotToPerformAssertions();

        $this->executeStep(
            'Step 1 name',
            function () {
                $this->addAttachment('step 1', 'Attachment for step 1');
            },
            'Step 1 title'
        );
    }

    public function testTwoSuccessfulSteps(): void
    {
        $this->expectNotToPerformAssertions();

        $this->executeStep(
            'Step 1 name',
            function () {
            }
        );

        $this->executeStep(
            'Step 2 name',
            function () {
            }
        );
    }

    public function testTwoStepsFirstFails(): void
    {
        $this->expectNotToPerformAssertions();

        $this->executeStep(
            'Step 1 name',
            function () {
                self::fail('Failure');
            }
        );

        $this->executeStep(
            'Step 2 name',
            function () {
            }
        );
    }

    public function testTwoStepsSecondFails(): void
    {
        $this->expectNotToPerformAssertions();

        $this->executeStep(
            'Step 1 name',
            function () {
            }
        );

        $this->executeStep(
            'Step 2 name',
            function () {
                self::fail('Failure');
            }
        );
    }
}
