<?php

declare(strict_types=1);

namespace Yandex\Allure\Report\Generate;

use PHPUnit\Framework\TestCase;
use Yandex\Allure\Adapter\Annotation\Description;
use Yandex\Allure\Adapter\Annotation\Features;
use Yandex\Allure\Adapter\Annotation\Parameter;
use Yandex\Allure\Adapter\Annotation\Severity;
use Yandex\Allure\Adapter\Annotation\Stories;
use Yandex\Allure\Adapter\Annotation\Title;
use Yandex\Allure\Adapter\Model\DescriptionType;
use Yandex\Allure\Adapter\Model\ParameterKind;
use Yandex\Allure\Adapter\Model\SeverityLevel;

/**
 * @Description ("Annotation tests for allure-phpunit")
 */
class AnnotationTest extends TestCase
{

    /**
     * @Title ("Test title")
     */
    public function testTitleAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Description ("Test description with `markdown`", type = DescriptionType::MARKDOWN)
     */
    public function testDescriptionAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Severity (level = SeverityLevel::MINOR)
     */
    public function testSeverityAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Parameter (name = "foo", value = "bar", kind = ParameterKind::ARGUMENT)
     */
    public function testParameterAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Stories ("Story 1", "Story 2")
     */
    public function testStoriesAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Features ("Feature 1", "Feature 2")
     */
    public function testFeaturesAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
