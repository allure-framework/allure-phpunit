<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Report\Generate;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute;
use Yandex\Allure\Adapter\Annotation\Description;
use Yandex\Allure\Adapter\Annotation\Features;
use Yandex\Allure\Adapter\Annotation\Parameter;
use Yandex\Allure\Adapter\Annotation\Severity;
use Yandex\Allure\Adapter\Annotation\Stories;
use Yandex\Allure\Adapter\Model\DescriptionType;
use Yandex\Allure\Adapter\Model\ParameterKind;
use Yandex\Allure\Adapter\Model\SeverityLevel;

class AnnotationTest extends TestCase
{
    /**
     * @Description ("Legacy description with `markdown`", type = DescriptionType::MARKDOWN)
     */
    #[Attribute\DisplayName('Legacy description annotation is reported as test description')]
    public function testLegacyDescriptionAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    #[
        Attribute\DisplayName('Native description annotation is reported as test description'),
        Attribute\Description('Test native description with `markdown`'),
    ]
    public function testNativeDescriptionAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Description ("Test legacy description with `markdown`", type = DescriptionType::MARKDOWN)
     */
    #[Attribute\Description('Test native description with <b>HTML</b>', true)]
    public function testMixedDescriptionAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Severity (level = SeverityLevel::MINOR)
     */
    #[Attribute\DisplayName('Legacy severity annotation is reported as test severity')]
    public function testLegacySeverityAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    #[
        Attribute\DisplayName('Native severity annotation is reported as test severity'),
        Attribute\Severity(Attribute\Severity::CRITICAL),
    ]
    public function testNativeSeverityAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Severity (level = SeverityLevel::MINOR)
     */
    #[
        Attribute\DisplayName('Legacy severity annotation overrides native one'),
        Attribute\Severity(Attribute\Severity::CRITICAL),
    ]
    public function testMixedSeverityAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Parameter (name = "foo", value = "legacy foo")
     */
    #[Attribute\DisplayName('Legacy parameter')]
    public function testLegacyParameterAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    #[Attribute\Parameter('foo', 'native foo')]
    #[Attribute\Parameter('bar', 'native bar')]
    public function testNativeParameterAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Parameter (name = "foo", value = "legacy bar", kind = ParameterKind::ARGUMENT)
     */
    #[Attribute\Parameter('foo', 'native foo')]
    #[Attribute\Parameter('bar', 'native baz')]
    public function testMixedParameterAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Stories ("Legacy story 1", "Legacy story 2")
     */
    public function testLegacyStoriesAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    #[Attribute\Story('Native story 1')]
    #[Attribute\Story('Native story 2')]
    public function testNativeStoriesAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Stories ("Legacy story 1", "Mixed story 2")
     */
    #[Attribute\Story('Native story 1')]
    #[Attribute\Story('Mixed story 2')]
    public function testMixedStoriesAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Features ("Legacy feature 1", "Legacy feature 2")
     */
    public function testLegacyFeaturesAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    #[Attribute\Feature('Native feature 1')]
    #[Attribute\Feature('Native feature 2')]
    public function testNativeFeaturesAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Features ("Legacy feature 1", "Mixed feature 2")
     */
    #[Attribute\Feature('Native feature 1')]
    #[Attribute\Feature('Mixed feature 2')]
    public function testMixedFeaturesAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
