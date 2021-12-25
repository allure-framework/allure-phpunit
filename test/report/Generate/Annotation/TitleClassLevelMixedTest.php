<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Report\Generate\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute;
use Yandex\Allure\Adapter\Annotation\Title;

/**
 * @Title ("This test has wrong title")
 */
#[
    Attribute\DisplayName('Native class-level annotation overrides legacy one if both are used'),
    Attribute\Epic('Annotations'),
    Attribute\Feature('Title'),
]
class TitleClassLevelMixedTest extends TestCase
{
    public function testWithoutTitleAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
