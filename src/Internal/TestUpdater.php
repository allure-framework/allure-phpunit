<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Internal;

use Doctrine\Common\Annotations\AnnotationReader as DoctrineAnnotationReader;
use LogicException;
use Qameta\Allure\Attribute\AttributeParser;
use Qameta\Allure\Attribute\AttributeReader;
use Qameta\Allure\Attribute\LegacyAttributeReader;
use Qameta\Allure\Model\Label;
use Qameta\Allure\Model\Parameter;
use Qameta\Allure\Model\Status;
use Qameta\Allure\Model\StatusDetails;
use Qameta\Allure\Model\TestResult;
use Qameta\Allure\Setup\StatusDetectorInterface;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

/**
 * @internal
 */
class TestUpdater implements TestUpdaterInterface
{

    public function setInfo(TestResult $testResult, TestInfo $info): void
    {
        $parser = $this->parseAnnotations($info);

        $testResult
            ->setName($parser->getTitle() ?? $info->getName())
            ->setFullName($info->getFullName())
            ->setDescriptionHtml($parser->getDescriptionHtml())
            ->setDescription($parser->getDescription())
            ->addLabels(
                ...$this->createSystemLabels($info),
                ...$parser->getLabels(),
            )
            ->addParameters(
                ...$this->createSystemParameters($info),
                ...$parser->getParameters(),
            )
            ->addLinks(...$parser->getLinks());
    }

    /**
     * @param TestInfo $info
     * @return AttributeParser
     */
    private function parseAnnotations(TestInfo $info): AttributeParser
    {
        $class = $info->getClass();
        if (!isset($class)) {
            return new AttributeParser([]);
        }

        $annotations = [];
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

        $method = $info->getMethod();
        if (!isset($method)) {
            return new AttributeParser($annotations);
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

        return new AttributeParser($annotations);
    }

    /**
     * @return list<Label>
     */
    private function createSystemLabels(TestInfo $info): array
    {
        return [
            Label::testClass($info->getClass()),
            Label::testMethod($info->getMethod()),
            Label::host($info->getHost()),
            Label::thread($info->getThread()),
        ];
    }

    /**
     * @return list<Parameter>
     */
    private function createSystemParameters(TestInfo $info): array
    {
        $dataLabel = $info->getDataLabel();

        return isset($dataLabel)
            ? [new Parameter('Data set', $dataLabel)]
            : [];
    }

    public function setRunInfo(TestResult $testResult, TestRunInfo $runInfo): void
    {
        $testResult
            ->setTestCaseId($runInfo->getTestCaseId())
            ->setHistoryId($runInfo->getHistoryId())
            ->setRerunOf($runInfo->getRerunOf());
    }

    public function setDetectedStatus(TestResult $test, StatusDetectorInterface $statusDetector, Throwable $e): void
    {
        $test
            ->setStatus($statusDetector->getStatus($e))
            ->setStatusDetails($statusDetector->getStatusDetails($e));
    }

    public function setStatus(TestResult $test, ?string $message = null, ?Status $status = null): void
    {
        $test
            ->setStatus($status)
            ->setStatusDetails(new StatusDetails(message: $message));
    }
}
