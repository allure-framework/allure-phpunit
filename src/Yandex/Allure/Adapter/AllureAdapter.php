<?php

namespace Yandex\Allure\Adapter;

use Exception;
use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_ExpectationFailedException;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestListener;
use PHPUnit_Framework_TestSuite;
use Yandex\Allure\Adapter\Annotation;
use Yandex\Allure\Adapter\Event\TestCaseBrokenEvent;
use Yandex\Allure\Adapter\Event\TestCaseCanceledEvent;
use Yandex\Allure\Adapter\Event\TestCaseFailedEvent;
use Yandex\Allure\Adapter\Event\TestCaseFinishedEvent;
use Yandex\Allure\Adapter\Event\TestCasePendingEvent;
use Yandex\Allure\Adapter\Event\TestCaseStartedEvent;
use Yandex\Allure\Adapter\Event\TestSuiteFinishedEvent;
use Yandex\Allure\Adapter\Event\TestSuiteStartedEvent;
use Yandex\Allure\Adapter\Model;

class AllureAdapter implements PHPUnit_Framework_TestListener
{

    //NOTE: here we implicitly assume that PHPUnit runs in single-threaded mode
    private $uuid;
    private $suiteName;
    private $methodName;

    /**
     * Annotations that should be ignored by the annotaions parser (especially PHPUnit annotations)
     * @var array
     */
    private $ignoredAnnotations = [
        'after', 'afterClass', 'backupGlobals', 'backupStaticAttributes', 'before', 'beforeClass',
        'codeCoverageIgnore', 'codeCoverageIgnoreStart', 'codeCoverageIgnoreEnd', 'covers',
        'coversDefaultClass', 'coversNothing', 'dataProvider', 'depends', 'expectedException',
        'expectedExceptionCode', 'expectedExceptionMessage', 'group', 'large', 'medium',
        'preserveGlobalState', 'requires', 'runTestsInSeparateProcesses', 'runInSeparateProcess',
        'small', 'test', 'testdox', 'ticket', 'uses',
    ];

    /**
     * @param string $outputDirectory XML files output directory
     * @param bool $deletePreviousResults Whether to delete previous results on return
     * @param array $ignoredAnnotations Extra annotaions to ignore in addition to standard PHPUnit annotations
     */
    public function __construct(
        $outputDirectory,
        $deletePreviousResults = false,
        array $ignoredAnnotations = []
    ) {
        if (!isset($outputDirectory)){
            $outputDirectory = 'build' . DIRECTORY_SEPARATOR . 'allure-results';
        }

        $this->prepareOutputDirectory($outputDirectory, $deletePreviousResults);
        
        // Add standard PHPUnit annotations
        Annotation\AnnotationProvider::addIgnoredAnnotations($this->ignoredAnnotations);
        // Add custom ignored annotations
        Annotation\AnnotationProvider::addIgnoredAnnotations($ignoredAnnotations);
    }

    public function prepareOutputDirectory($outputDirectory, $deletePreviousResults)
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
        $event = new TestCaseBrokenEvent();
        Allure::lifecycle()->fire($event->withException($e)->withMessage($e->getMessage()));
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
        $event = new TestCaseFailedEvent();

        $message = $e->getMessage();

        // Append comparison diff for errors of type ExpectationFailedException (and is subclasses)
        if (($e instanceof PHPUnit_Framework_ExpectationFailedException
            || is_subclass_of($e, '\PHPUnit_Framework_ExpectationFailedException'))
            && $e->getComparisonFailure()
        ) {
            $message .= $e->getComparisonFailure()->getDiff();
        }

        Allure::lifecycle()->fire($event->withException($e)->withMessage($message));
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
        $event = new TestCasePendingEvent();
        Allure::lifecycle()->fire($event->withException($e));
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
        $this->addIncompleteTest($test, $e, $time);
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
        $shouldCreateStartStopEvents = false;
        if ($test instanceof \PHPUnit_Framework_TestCase){
            $methodName = $test->getName();
            if ($methodName !== $this->methodName){
                $shouldCreateStartStopEvents = true;
                $this->startTest($test);
            }
        }

        $event = new TestCaseCanceledEvent();
        Allure::lifecycle()->fire($event->withException($e)->withMessage($e->getMessage()));

        if ($shouldCreateStartStopEvents && $test instanceof \PHPUnit_Framework_TestCase){
            $this->endTest($test, 0);
        }
    }

    /**
     * A test suite started.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function startTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if ($suite instanceof \PHPUnit_Framework_TestSuite_DataProvider) {
            return;
        }

        $suiteName = $suite->getName();
        if (class_exists($suiteName, false)) {
            $event = new TestSuiteStartedEvent($suiteName);
            $this->uuid = $event->getUuid();
            $this->suiteName = $suiteName;
            $annotationManager = new Annotation\AnnotationManager(
                Annotation\AnnotationProvider::getClassAnnotations($suiteName)
            );
            $annotationManager->updateTestSuiteEvent($event);
            Allure::lifecycle()->fire($event);
        }


    }

    /**
     * A test suite ended.
     *
     * @param PHPUnit_Framework_TestSuite $suite
     * @since  Method available since Release 2.2.0
     */
    public function endTestSuite(PHPUnit_Framework_TestSuite $suite)
    {
        if ($suite instanceof \PHPUnit_Framework_TestSuite_DataProvider) {
            return;
        }

        Allure::lifecycle()->fire(new TestSuiteFinishedEvent($this->uuid));
    }

    /**
     * A test started.
     *
     * @param PHPUnit_Framework_Test $test
     */
    public function startTest(PHPUnit_Framework_Test $test)
    {
        if ($test instanceof \PHPUnit_Framework_TestCase) {
            $testName = $test->getName();
            $methodName = $this->methodName = $test->getName(false);

            $event = new TestCaseStartedEvent($this->uuid, $testName);
            if (method_exists($test, $methodName)) {
                $annotationManager = new Annotation\AnnotationManager(
                    Annotation\AnnotationProvider::getMethodAnnotations(get_class($test), $methodName)
                );
                $annotationManager->updateTestCaseEvent($event);
            }
            Allure::lifecycle()->fire($event);
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
        if ($test instanceof \PHPUnit_Framework_TestCase) {
            Allure::lifecycle()->fire(new TestCaseFinishedEvent());
        }
    }
}
