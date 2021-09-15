<?php

declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Test\Report\Generate\Annotation;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute;

#[
    Attribute\Title('Test without title annotation uses class-level native title annotation'),
    Attribute\Epic('Annotations'),
    Attribute\Feature('Title'),
]
class TitleClassLevelNativeTest extends TestCase
{

    public function testWithoutTitleAnnotation(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
