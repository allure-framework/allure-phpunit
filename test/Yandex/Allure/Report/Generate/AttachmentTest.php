<?php

declare(strict_types=1);

namespace Yandex\Allure\Report\Generate;

use PHPUnit\Framework\TestCase;
use Yandex\Allure\Adapter\Annotation\Description;
use Yandex\Allure\Adapter\Support\AttachmentSupport;

/**
 * @Description ("Attachment tests for allure-phpunit")
 */
class AttachmentTest extends TestCase
{
    use AttachmentSupport;

    public function testFileAttachment(): void
    {
        $this->expectNotToPerformAssertions();
        $this->addAttachment(__FILE__, 'File attachment');
    }

    public function testDataAttachment(): void
    {
        $this->expectNotToPerformAssertions();
        $this->addAttachment('text', 'Text attachment');
    }
}
