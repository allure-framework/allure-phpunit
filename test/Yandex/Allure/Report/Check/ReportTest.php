<?php

declare(strict_types=1);

namespace Yandex\Allure\Report\Check;

use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;

use Yandex\Allure\Report\Generate\AnnotationTest;
use function is_file;
use function pathinfo;
use function scandir;

use const DIRECTORY_SEPARATOR;
use const PATHINFO_EXTENSION;

class ReportTest extends TestCase
{

    /**
     * @var string
     */
    private $buildPath;

    /**
     * @var DOMXPath[]
     */
    private $sources = [];

    public function setUp(): void
    {
        $this->buildPath = __DIR__ . '/../../../../../build/allure-results';
        $files = scandir($this->buildPath);
        foreach ($files as $fileName) {
            $file = $this->buildPath . DIRECTORY_SEPARATOR . $fileName;
            if (!is_file($file)) {
                continue;
            }
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if ('xml' == $extension) {
                $dom = new DOMDocument();
                $dom->load($file);

                $path = new DOMXPath($dom);
                $name = $path->query('/alr:test-suite/name')->item(0)->textContent;
                $this->sources[$name] = $path;
            }
        }
    }

    public function testSuiteDescriptionAnnotation(): void
    {
        self::assertSame(
            'Annotation tests for allure-phpunit',
            $this->findTextContent(AnnotationTest::class, '/alr:test-suite/description')
        );
    }

    public function testCaseTitleAnnotation(): void
    {
        $xpath = '/alr:test-suite/test-cases/test-case[name="testTitleAnnotation"]/title';
        self::assertSame(
            'Test title',
            $this->findTextContent(AnnotationTest::class, $xpath)
        );
    }

    private function findTextContent(string $class, string $xpath): string
    {
        $path = $this->sources[$class];

        return $path->query($xpath)->item(0)->textContent;
    }
}
