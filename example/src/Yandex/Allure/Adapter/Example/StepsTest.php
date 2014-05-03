<?php

namespace Yandex\Allure\Adapter\Example;

use Exception;
use PHPUnit_Framework_TestCase;
use Yandex\Allure\Adapter\Support\StepSupport;
use Yandex\Allure\Adapter\Support;

class StepsTest extends PHPUnit_Framework_TestCase
{

    use StepSupport;

    /**
     * This test contains three steps. Thus we can isolate execution results related to one test case but being
     * less or more independent. Using steps we can see which part of the test broke it.
     */
    public function testSimpleSteps()
    {

        $number = 10;

        $this->executeStep("$number is positive", function () use ($number) {
            $this->isPositive($number);
        });

        $this->executeStep("$number is greater than 11 (failing assertion)", function () use ($number) {
            $this->greaterThanEleven($number);
        });

        $this->executeStep("Some exception in assertion about $number", function () use ($number) {
            $this->assertionWithException($number);
        }, 'Custom title for this step');

    }

    /**
     * This test runs the same three steps but organized in a tree.
     */
    public function testStepsTree()
    {
        $number = 8;

        $this->executeSteps(array(
            "$number is positive (contains child step)" => array(
                Support\STEP_LOGIC_KEY => function () use ($number) {
                    $this->isPositive($number);
                },
                Support\STEP_CHILD_STEPS_KEY => array(
                    "$number is greater than 11 (failing assertion)" => array(
                        Support\STEP_LOGIC_KEY => function () use ($number) {
                            $this->greaterThanEleven($number);
                        }
                    ),
                )
            ),
            "Some exception in assertion about $number" => array(
                Support\STEP_LOGIC_KEY => function () use ($number) {
                    $this->assertionWithException($number);
                },
                Support\STEP_TITLE_KEY => 'Custom title for this step'
            )
        ));
    }

    private function isPositive($number)
    {
        $this->assertTrue($number > 0);
    }

    private function greaterThanEleven($number)
    {
        $this->assertTrue($number > 11);
    }

    private function assertionWithException($number)
    {
        throw new Exception("Unexpected exception for assertion about $number");
    }

}