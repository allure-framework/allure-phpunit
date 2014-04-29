<?php

namespace Yandex\Allure\Adapter;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\IndexedReader;
use Exception;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestListener;
use PHPUnit_Framework_TestSuite;
use Rhumsaa\Uuid\Uuid;
use Yandex\Allure\Adapter\Annotation;
use Yandex\Allure\Adapter\Model;

require_once(dirname(__FILE__).'/../../../../vendor/autoload.php');

const DEFAULT_OUTPUT_DIRECTORY = "allure-report";

class AllureAdapter implements PHPUnit_Framework_TestListener {

    private $testSuites;

    private $outputDirectory;

    private $annotationsReader;

    function __construct($outputDirectory = DEFAULT_OUTPUT_DIRECTORY, $deletePreviousResults = false)
    {
        if (!file_exists($outputDirectory)){
            mkdir($outputDirectory, 0755, true);
        }
        if ($deletePreviousResults){
            $files = glob($outputDirectory . DIRECTORY_SEPARATOR . '{,.}*', GLOB_BRACE);
            foreach($files as $file){
                if(is_file($file)){
                    unlink($file);
                }
            }
        }
        $this->outputDirectory = $outputDirectory;
        $this->testSuites = array();
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
        // TODO: Implement addError() method.
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
        // TODO: Implement addFailure() method.
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
        // TODO: Implement addIncompleteTest() method.
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
        // TODO: Implement addRiskyTest() method.
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
        // TODO: Implement addSkippedTest() method.
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
        foreach ($this->getAnnotations($suite) as $annotation){
            if ($annotation instanceof Annotation\Title){
                $testSuite->setTitle($annotation->value);
            } else if ($annotation instanceof Annotation\Description){
                $testSuite->setDescription(new Model\Description(
                    $annotation->type,
                    $annotation->value
                ));
            } else if ($annotation instanceof Annotation\Features){
                foreach ($annotation->featureNames as $featureName){
                    $testSuite->addLabel(new Model\Feature($featureName));
                }
            } else if ($annotation instanceof Annotation\Stories) {
                foreach ($annotation->stories as $storyName){
                    $testSuite->addLabel(new Model\Story($storyName));
                }
            }
        }
        $this->pushTestSuite($testSuite);
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
        $testSuite = $this->popTestSuite();
        if ($testSuite instanceof Model\TestSuite){
            $testSuite->setStop($suiteStop);
            if ($testSuite->size() > 0) {
                $xml = $testSuite->serialize();
                $fileName = self::getUUID() . '-testsuite.xml';
                $filePath = $this->getOutputDirectory() . DIRECTORY_SEPARATOR . $fileName;
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
        $testInstance = self::getTestInstance($test);
        $testName = $testInstance->getName();
        $testStart = self::getTimestamp();
        $testCase = new Model\TestCase($testName, $testStart);
        foreach ($this->getAnnotations($testInstance) as $annotation){
            if ($annotation instanceof Annotation\Title){
                $testCase->setTitle($annotation->value);
            } else if ($annotation instanceof Annotation\Description){
                $testCase->setDescription(new Model\Description(
                    $annotation->type,
                    $annotation->value
                ));
            } else if ($annotation instanceof Annotation\Features){
                foreach ($annotation->featureNames as $featureName){
                    $testCase->addLabel(new Model\Feature($featureName));
                }
            } else if ($annotation instanceof Annotation\Stories) {
                foreach ($annotation->stories as $storyName){
                    $testCase->addLabel(new Model\Story($storyName));
                }
            } else if ($annotation instanceof Annotation\Step) {
                //TODO: to be implemented!
            } else if ($annotation instanceof Annotation\Severity){
                $testCase->setSeverity($annotation->level);
            }
        }
        $this->getCurrentTestSuite()->addTestCase($testCase);
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
        $testInstance = self::getTestInstance($test);
        $testName = $testInstance->getName();
        $testStop = self::getTimestamp();
        $testCase = $this->getCurrentTestSuite()->getTestCase($testName);
        if ($testCase instanceof Model\TestCase){
            $testCase->setStop($testStop);
            foreach ($this->getAnnotations($testInstance) as $annotation){
                if ($annotation instanceof Annotation\Attachment){
                    $path = $annotation->path;
                    $type = $annotation->type;
                    if ($type != Model\AttachmentType::OTHER && file_exists($path)){
                        $newFileName =
                            $this->getOutputDirectory().DIRECTORY_SEPARATOR.
                            self::getUUID().$annotation->name.'-attachment.'.$type;
                        $attachment = new Model\Attachment($annotation->name, $newFileName, $annotation->type);
                        if (!copy($path, $newFileName)){
                            throw new Exception("Failed to copy attachment from $path to $newFileName.");
                        }
                        $testCase->addAttachment($attachment);
                    } else {
                        throw new Exception("Attachment $path doesn't exist.");
                    }
                }
            }
        }
    }

    /**
     * @return string
     */
    public function getOutputDirectory()
    {
        return $this->outputDirectory;
    }

    /**
     * @param Model\TestSuite $testSuite
     */
    public function pushTestSuite(Model\TestSuite $testSuite)
    {
        array_push($this->testSuites, $testSuite);
    }

    /**
     * @return Model\TestSuite
     */
    public function getCurrentTestSuite()
    {
        return end($this->testSuites);
    }

    /**
     * @return Model\TestSuite
     */
    public function popTestSuite()
    {
        return array_pop($this->testSuites);
    }

    /**
     * Returns a list of class annotations
     * @param $instance
     * @return array
     */
    private function getAnnotations($instance)
    {
        if (!isset($this->annotationsReader)){
            $this->annotationsReader = new IndexedReader(new AnnotationReader());
        }
        $ref = new \ReflectionClass($instance);
        return $this->annotationsReader->getClassAnnotations($ref);
    }

    /**
     * @param PHPUnit_Framework_Test $test
     * @return \PHPUnit_Framework_TestCase|void
     */
    private static function getTestInstance(PHPUnit_Framework_Test $test){
        if ($test instanceof \PHPUnit_Framework_TestCase){
            return $test;
        }
        echo("Warning: skipping test $test as it doesn't inherit from PHPUnit_Framework_TestCase.");
        return null;
    }

    public static function getTimestamp()
    {
        return round(microtime(true) * 1000);
    }

    public static function getUUID()
    {
        return Uuid::uuid4();
    }

}