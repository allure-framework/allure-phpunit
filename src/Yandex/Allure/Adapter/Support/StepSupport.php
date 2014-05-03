<?php

namespace Yandex\Allure\Adapter\Support;
use Exception;
use PHPUnit_Framework_TestCase;
use Yandex\Allure\Adapter\Model;

const STEP_LOGIC_KEY = 'logic';
const STEP_TITLE_KEY = 'title';
const STEP_CHILD_STEPS_KEY = 'childSteps';

/**
 * Use this trait in order to add Allure steps support
 * @package Yandex\Allure\Adapter\Support
 */
trait StepSupport
{

    use Utils;

    /**
     * Adds a simple step to current test case
     * @param string $name step name
     * @param callable $logic anonymous function containing the entire step logic.
     * @param string $title an optional title for the step
     * PHPUnit_Framework_TestCase class and test method name are passed to this function
     */
    public function executeStep($name, $logic, $title = null)
    {
        $this->executeSteps(array($name => array(STEP_LOGIC_KEY => $logic, STEP_TITLE_KEY => $title)));
    }

    /**
     * Adds a hierarchy of steps
     * @param array $definition an array of the following structure:
     * array('<step name>' => array('logic' => $logic[, 'title' => 'some title', 'childSteps' => array $childStepsDefinition])) where $logic is an anonymous function
     * @throws \Exception
     */
    public function executeSteps(array $definition)
    {
        if ($this instanceof \PHPUnit_Framework_TestCase) {
            $testInstance = Model\Provider::getCurrentTestSuite();
            if (isset($testInstance) && $testInstance instanceof Model\TestSuite) {
                $testCase = $testInstance->getCurrentTestCase();
                if (isset($testInstance) && $testCase instanceof Model\TestCase) {
                    $testStatus = $testCase->getStatus();
                    foreach (self::stepsFromDefinition($definition, $this, $testCase->getName()) as $step) {
                        $testCase->addStep($step);

                        //Decide whether we need to update the entire test case status
                        if (($step instanceof Model\Step) && ($testCase->getStatus() != Model\Status::SKIPPED)) {
                            $stepStatus = $step->getStatus();
                            if (
                                ($testStatus === Model\Status::PASSED) ||
                                (
                                    ($testStatus === Model\Status::FAILED) &&
                                    ($stepStatus === Model\Status::BROKEN)
                                )
                            ) {
                                $testStatus = $stepStatus;
                            }
                        }
                    }
                    $testCase->setStatus($testStatus);
                }
            }
        } else {
            throw new Exception('This method can be called only inside test class.');
        }
    }

    /**
     * Recursively executes definition and returns an array of steps corresponding to this definition
     * @param array $definition
     * @param \PHPUnit_Framework_TestCase $testSuite
     * @param string $testCaseName
     * @return array an array of steps having child steps inside if any
     */
    private static function stepsFromDefinition(array $definition, PHPUnit_Framework_TestCase $testSuite, $testCaseName)
    {
        $ret = array();
        if (isset($definition)) {
            foreach ($definition as $name => $specification) {
                if ((count($specification) == 0) || !isset($specification[STEP_LOGIC_KEY])) {
                    continue;
                }
                $logic = $specification[STEP_LOGIC_KEY];
                $startTime = self::getTimestamp();
                $childrenDefinition = (isset($specification[STEP_CHILD_STEPS_KEY])) ?
                    $specification[STEP_CHILD_STEPS_KEY] : array();
                $childSteps = self::stepsFromDefinition($childrenDefinition, $testSuite, $testCaseName);
                $stepStatus = self::executeStepLogic($name, $logic, $testSuite, $testCaseName);
                $stopTime = self::getTimestamp();
                $step = new Model\Step($name, $startTime, $stopTime, $stepStatus);
                if (isset($specification[STEP_TITLE_KEY])) {
                    $step->setTitle($specification[STEP_TITLE_KEY]);
                }
                foreach ($childSteps as $childStep) {
                    $step->addStep($childStep);
                }
                $ret[] = $step;
            }
        }
        return $ret;
    }

    private static function executeStepLogic($name, $fn, PHPUnit_Framework_TestCase $testSuite, $testCaseName)
    {
        if (is_callable($fn)) {
            try {
                $fn($testSuite, $testCaseName);
                return Model\Status::PASSED;
            } catch (\PHPUnit_Framework_AssertionFailedError $e) {
                return Model\Status::FAILED;
            } catch (\Exception $e) {
                return Model\Status::BROKEN;
            }
        }
        throw new Exception("Step $name logic should be an anonymous function.");
    }

}