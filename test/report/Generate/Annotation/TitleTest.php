<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Report\Generate\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute;
use Qameta\Allure\PHPUnit\ExceptionDetailsTrait;
use Yandex\Allure\Adapter\Annotation\Title;

#[
    Attribute\Epic('Annotations'),
    Attribute\Feature('Title'),
]
class TitleTest extends TestCase
{
    use ExceptionDetailsTrait;

    #[Attribute\Description('Test without both method-level and class-level annotations reports full name as a title')]
    public function testWithoutTitleAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Title ("Legacy title annotation is reported as test title")
     */
    public function testLegacyTitleAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    #[Attribute\DisplayName('Native display name annotation is reported as test title')]
    public function testNativeTitleAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }

    /**
     * @Title ("This is wrong title for this test")
     */
    #[Attribute\DisplayName('Native display name annotation overrides legacy one')]
    public function testMixedTitleAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
