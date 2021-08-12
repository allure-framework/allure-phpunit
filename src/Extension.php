<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use LogicException;
use PHPUnit\Runner\AfterIncompleteTestHook;
use PHPUnit\Runner\AfterRiskyTestHook;
use PHPUnit\Runner\AfterSkippedTestHook;
use PHPUnit\Runner\AfterSuccessfulTestHook;
use PHPUnit\Runner\AfterTestErrorHook;
use PHPUnit\Runner\AfterTestFailureHook;
use PHPUnit\Runner\AfterTestHook;
use PHPUnit\Runner\AfterTestWarningHook;
use PHPUnit\Runner\BeforeTestHook;
use Qameta\Allure\Allure;
use Qameta\Allure\AllureLifecycleInterface;
use Qameta\Allure\Attribute\AttributeParser;
use Qameta\Allure\Attribute\AttributeReader;
use Qameta\Allure\Attribute\LegacyAttributeReader;
use Qameta\Allure\Model\Label;
use Qameta\Allure\Model\Parameter;
use Qameta\Allure\Model\ResultFactoryInterface;
use Qameta\Allure\Model\Status;
use Qameta\Allure\Model\StatusDetails;
use Qameta\Allure\Model\TestResult;
use Qameta\Allure\PHPUnit\Internal\TestInfo;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

final class Extension implements
    BeforeTestHook,
    AfterTestHook,
    AfterTestFailureHook,
    AfterTestErrorHook,
    AfterIncompleteTestHook,
    AfterSkippedTestHook,
    AfterTestWarningHook,
    AfterRiskyTestHook,
    AfterSuccessfulTestHook
{

    private AllureLifecycleInterface $lifecycle;

    private ResultFactoryInterface $resultFactory;

    public function __construct(
        string $outputDirectory,
    ) {
        Allure::setOutputDirectory($outputDirectory);
        $this->lifecycle = Allure::getLifecycle();
        $this->resultFactory = Allure::getResultFactory();
    }

    public function executeBeforeTest(string $test): void
    {
        $test = $this->createAnnotatedTest(TestInfo::parse($test));
        $this
            ->lifecycle
            ->scheduleTest($test);
        $this
            ->lifecycle
            ->startTest($test->getUuid());
    }

    public function executeAfterTest(string $test, float $time): void
    {
        $testId = $this->lifecycle->getCurrentTest();
        $this->lifecycle->stopTest($testId);
        $this->lifecycle->writeTest($testId);
    }

    public function executeAfterTestFailure(string $test, string $message, float $time): void
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $testResult
                ->setStatus(Status::failed())
                ->setStatusDetails($this->createStatusDetails($message)),
        );
    }

    public function executeAfterTestError(string $test, string $message, float $time): void
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $testResult
                ->setStatus(Status::failed())
                ->setStatusDetails($this->createStatusDetails($message)),
        );
    }

    public function executeAfterIncompleteTest(string $test, string $message, float $time): void
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $testResult
                ->setStatus(Status::broken())
                ->setStatusDetails($this->createStatusDetails($message)),
        );
    }

    public function executeAfterSkippedTest(string $test, string $message, float $time): void
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $testResult
                ->setStatus(Status::skipped())
                ->setStatusDetails($this->createStatusDetails($message)),
        );
    }

    public function executeAfterTestWarning(string $test, string $message, float $time): void
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $testResult
                ->setStatus(Status::failed())
                ->setStatusDetails($this->createStatusDetails($message)),
        );
    }

    public function executeAfterRiskyTest(string $test, string $message, float $time): void
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $testResult
                ->setStatus(Status::failed())
                ->setStatusDetails($this->createStatusDetails($message)),
        );
    }

    public function executeAfterSuccessfulTest(string $test, float $time): void
    {
        $this->lifecycle->updateTest(
            fn (TestResult $testResult) => $testResult
                ->setStatus(Status::passed())
                ->setStatusDetails($this->createStatusDetails()),
        );
    }

    private function createAnnotatedTest(TestInfo $info): TestResult
    {
        $test = $this
            ->resultFactory
            ->createTest();
        $class = $info->getClass();
        if (isset($class)) {
            $test->addLabels(Label::testClass($class));
        }
        $method = $info->getMethod();
        if (isset($method)) {
            $test->addLabels(Label::testMethod($method));
        }
        $parser = new AttributeParser($this->loadAnnotations($class, $method));

        $fullName = $info->getFullName();
        $test
            ->setName($parser->getTitle() ?? $info->getName())
            ->setFullName($fullName)
            ->setDescriptionHtml($parser->getDescriptionHtml())
            ->setDescription($parser->getDescription())
            ->addLabels(...$parser->getLabels())
            ->addLinks(...$parser->getLinks())
            ->addParameters(...$parser->getParameters());

        $dataLabel = $info->getDataLabel();
        if (isset($dataLabel)) {
            $test->addParameters(new Parameter('dataset', $dataLabel));
        }
        if (isset($fullName)) {
            $parameterNames = implode(
                '::',
                array_map(
                    fn (Parameter $parameter) => $parameter->getName(),
                    $test->getParameters(),
                ),
            );
            $test->setTestCaseId(md5("{$fullName}::{$parameterNames}"));
            if (isset($dataLabel)) {
                $parameterValues = implode(
                    '::',
                    array_map(
                        fn (Parameter $parameter) => $parameter->getValue() ?? '',
                        $test->getParameters(),
                    ),
                );
                $test->setHistoryId(md5("{$fullName}::{$parameterValues}"));
            }
        }

        return $test;
    }

    /**
     * @param class-string|null $class
     * @param string|null $method
     * @return list<object>
     */
    private function loadAnnotations(?string $class, ?string $method): array
    {
        $annotations = [];
        if (!isset($class)) {
            return $annotations;
        }

        $reader = new LegacyAttributeReader(
            new DoctrineAnnotationReader(),
            new AttributeReader(),
        );
        try {
            $classRef = new ReflectionClass($class);
            $annotations = [
                ...$annotations,
                ...$reader->getClassAnnotations($classRef),
            ];
        } catch (Throwable $e) {
            throw new LogicException("Annotations not loaded", 0, $e);
        }

        if (!isset($method)) {
            return $annotations;
        }

        try {
            $methodRef = new ReflectionMethod($class, $method);
            $annotations = [
                ...$annotations,
                ...$reader->getMethodAnnotations($methodRef),
            ];
        } catch (Throwable $e) {
            throw new LogicException("Annotations not loaded", 0, $e);
        }

        return $annotations;
    }

    private function createStatusDetails(?string $message = null): StatusDetails
    {
        return new StatusDetails(message: $message);
    }
}
