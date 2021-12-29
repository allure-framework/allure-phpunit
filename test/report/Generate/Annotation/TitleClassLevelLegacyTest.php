<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Report\Generate\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute;
use Yandex\Allure\Adapter\Annotation\Title;

/**
 * @Title ("Test without title annotation uses class-level legacy title annotation")
 */
#[
    Attribute\Epic('Annotations'),
    Attribute\Feature('Title'),
]
class TitleClassLevelLegacyTest extends TestCase
{
    public function testWithoutTitleAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
