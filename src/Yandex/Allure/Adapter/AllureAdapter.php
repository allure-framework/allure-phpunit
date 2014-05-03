<?php

namespace Yandex\Allure\Adapter;

use Exception;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestListener;
use PHPUnit_Framework_TestSuite;
use Yandex\Allure\Adapter\Annotation;
use Yandex\Allure\Adapter\Model;
use Yandex\Allure\Adapter\Model\Status;
use Yandex\Allure\Adapter\Support\Utils;

const DEFAULT_OUTPUT_DIRECTORY = "allure-report";

class AllureAdapter implements PHPUnit_Framework_TestListener
{

    use Utils;

    function __construct($outputDirectory = DEFAULT_OUTPUT_DIRECTORY, $deletePreviousResults = false)
    {
        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory, 0755, true);
        }
        if ($deletePreviousResults) {
            $files = glob($outputDirectory . DIRECTORY_SEPARATOR . '{,.}*', GLOB_BRACE);
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        if (is_null(Model\Provider::getOutputDirectory())) {
            Model\Provider::setOutputDirectory($outputDirectory);
        }
    }

    /**
     * An error occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception $e
     * @param float $time
     */
    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->handleUnsuccessfulTest($test, $e, Status::BROKEN);
    }

    /**
     * A failure occurred.
     *
     * @param PHPUnit_Framework_Test $test
     * @param PHPUnit_Framework_AssertionFailedError $e
     * @param float $time
     */
    public function addFailure(PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->handleUnsuccessfulTest($test, $e, Status::FAILED);
    }

    /**
     * Incomplete test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception $e
     * @param float $time
     */
    public function addIncompleteTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->addError($test, $e, $time);
    }

    /**
     * Risky test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception $e
     * @param float $time
     * @since  Method available since Release 4.0.0
     */
    public function addRiskyTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->addError($test, $e, $time);
    }

    /**
     * Skipped test.
     *
     * @param PHPUnit_Framework_Test $test
     * @param Exception $e
     * @param float $time
     * @since  Method available since Release 3.0.0
     */
    public function addSkippedTest(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->handleUnsuccessfulTest($test, $e, Status::SKIPPED);
    }

    private function handleUnsuccessfulTest(PHPUnit_Framework_Test $test, Exception $e, $status)
    {
        $this->doIfTestIsValid($test, function (Model\TestCase $testCase) use ($e, $status) {
            $failure = new Model\Failure($e->getMessage());
            $failure->setStackTrace($e->getTraceAsString());
            $testCase->setStatus($status);
            $testCase->setFailure($failure);
        });
    }

    /**
     * A test suite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $suiteName = $suite->getName();
        $suiteStart = self::getTimestamp();
        $testSuite = new Model\TestSuite($suiteName, $suiteStart);
        foreach (Annotation\AnnotationProvider::getClassAnnotations($suite) as $annotation) {
            if ($annotation instanceof Annotation\Title) {
                $testSuite->setTitle($annotation->value);
            } else if ($annotation instanceof Annotation\Description) {
                $testSuite->setDescription(new Model\Description(
                    $annotation->type,
                    $annotation->value
                ));
            } else if ($annotation instanceof Annotation\Features) {
                foreach ($annotation->getFeatureNames() as $featureName) {
                    $testSuite->addLabel(Model\Label::feature($featureName));
                }
            } else if ($annotation instanceof Annotation\Stories) {
                foreach ($annotation->getStories() as $storyName) {
                    $testSuite->addLabel(Model\Label::story($storyName));
                }
            }
        }
        Model\Provider::pushTestSuite($testSuite);
    }

    /**
     * A test suite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        $suiteStop = self::getTimestamp();
        $testSuite = Model\Provider::popTestSuite();
        if ($testSuite instanceof Model\TestSuite) {
            $testSuite->setStop($suiteStop);
            if ($testSuite->size() > 0) {
                $xml = $testSuite->serialize();
                $fileName = self::getUUID() . '-testsuite.xml';
                $filePath = Model\Provider::getOutputDirectory() . DIRECTORY_SEPARATOR . $fileName;
                file_put_contents($filePath, $xml);
            }
        }
    }

    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        $testInstance = self::validateTestInstance($test);
        if (!is_null($testInstance)) {
            $testName = $testInstance->getName();
            $testStart = self::getTimestamp();
            $testCase = new Model\TestCase($testName, $testStart);
            foreach (Annotation\AnnotationProvider::getMethodAnnotations($testInstance, $testName) as $annotation) {
                if ($annotation instanceof Annotation\Title) {
                    $testCase->setTitle($annotation->value);
                } else if ($annotation instanceof Annotation\Description) {
                    $testCase->setDescription(new Model\Description(
                        $annotation->type,
                        $annotation->value
                    ));
                } else if ($annotation instanceof Annotation\Features) {
                    foreach ($annotation->getFeatureNames() as $featureName) {
                        $testCase->addLabel(Model\Label::feature($featureName));
                    }
                } else if ($annotation instanceof Annotation\Stories) {
                    foreach ($annotation->getStories() as $storyName) {
                        $testCase->addLabel(Model\Label::story($storyName));
                    }
                } else if ($annotation instanceof Annotation\Severity) {
                    $testCase->setSeverity($annotation->level);
                }
            }
            Model\Provider::getCurrentTestSuite()->addTestCase($testCase);
            Model\Provider::getCurrentTestSuite()->setCurrentTestCase($testCase);
        }
    }

    /**
     * A test ended.
     *
     * @param PHPUnit_Framework_Test $test
     * @param float $time
     * @throws \Exception
     */
    public function endTest(PHPUnit_Framework_Test $test, $time)
    {
        $testInstance = self::validateTestInstance($test);
        if (!is_null($testInstance)) {
            $testName = $testInstance->getName();
            $testStop = self::getTimestamp();
            $testCase = Model\Provider::getCurrentTestSuite()->getTestCase($testName);
            if ($testCase instanceof Model\TestCase) {
                $testCase->setStop($testStop);
            }
        }
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @return \PHPUnit_Framework_TestCase|void
     */
    private static function validateTestInstance(PHPUnit_Framework_Test $test)
    {
        if ($test instanceof \PHPUnit_Framework_TestCase) {
            return $test;
        }
        echo("Warning: skipping test $test as it doesn't inherit from PHPUnit_Framework_TestCase.");
        return null;
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @param $action
     */
    private function doIfTestIsValid(PHPUnit_Framework_Test $test, $action)
    {
        $testInstance = self::validateTestInstance($test);
        if (!is_null($testInstance)) {
            $testCase = Model\Provider::getCurrentTestSuite()->getTestCase($testInstance->getName());
            if (isset($testCase)) {
                $action($testCase);
            }
        }

    }

}