<?php

declare(strict_types=1);

namespace Yandex\Allure\Report\Check;

use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yandex\Allure\Report\Generate\AnnotationTest;
use Yandex\Allure\Report\Generate\AttachmentTest;
use Yandex\Allure\Report\Generate\StepsTest;

use function file_get_contents;
use function is_file;
use function pathinfo;
use function scandir;
use function sprintf;

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
                if (isset($this->sources[$name])) {
                    throw new RuntimeException("Duplicate test suite: {$name}");
                }
                $this->sources[$name] = $path;
            }
        }
    }

    /**
     * @param string $class
     * @param string $xpath
     * @param string $expectedValue
     * @dataProvider providerSingleTextNode
     */
    public function testSingleTextNode(string $class, string $xpath, string $expectedValue): void
    {
        self::assertArrayHasKey($class, $this->sources);
        $actualValue = $this
            ->sources[$class]
            ->query($xpath)
            ->item(0)
            ->textContent;
        self::assertSame($expectedValue, $actualValue);
    }

    public function providerSingleTextNode(): iterable
    {
        return [
            'Test suite description annotation' => [
                AnnotationTest::class,
                '/alr:test-suite/description',
                'Annotation tests for allure-phpunit',
            ],
            'Test case title annotation' => [
                AnnotationTest::class,
                $this->buildTestXPath(
                    'testTitleAnnotation',
                    '/title'
                ),
                'Test title',
            ],
            'Test case description annotation' => [
                AnnotationTest::class,
                $this->buildTestXPath(
                    'testDescriptionAnnotation',
                    '/description[@type="markdown"]'
                ),
                'Test description with `markdown`',
            ],
            'Test case severity annotation' => [
                AnnotationTest::class,
                $this->buildTestXPath(
                    'testSeverityAnnotation',
                    '/labels/label[@name="severity" and @value="minor"]'
                ),
                '',
            ],
            'Test case parameter annotation' => [
                AnnotationTest::class,
                $this->buildTestXPath(
                    'testParameterAnnotation',
                    '/parameters/parameter[@name="foo" and @value="bar" and @kind="argument"]'
                ),
                '',
            ],
            'Test case stories annotation: first story' => [
                AnnotationTest::class,
                $this->buildTestXPath(
                    'testStoriesAnnotation',
                    '/labels/label[@name="story" and @value="Story 1"]'
                ),
                '',
            ],
            'Test case stories annotation: second story' => [
                AnnotationTest::class,
                $this->buildTestXPath(
                    'testStoriesAnnotation',
                    '/labels/label[@name="story" and @value="Story 2"]'
                ),
                '',
            ],
            'Test case features annotation: first feature' => [
                AnnotationTest::class,
                $this->buildTestXPath(
                    'testFeaturesAnnotation',
                    '/labels/label[@name="feature" and @value="Feature 1"]'
                ),
                '',
            ],
            'Test case features annotation: second feature' => [
                AnnotationTest::class,
                $this->buildTestXPath(
                    'testFeaturesAnnotation',
                    '/labels/label[@name="feature" and @value="Feature 2"]'
                ),
                '',
            ],
            'Successful test case without steps' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testNoStepsSuccess',
                '[@status="passed"]/name'
                ),
                'testNoStepsSuccess',
            ],
            'Error in test case without steps' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testNoStepsError',
                '[@status="broken"]/failure/message'
                ),
                'Error',
            ],
            'Failure in test case without steps' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testNoStepsFailure',
                '[@status="failed"]/failure/message'
                ),
                'Failure',
            ],
            'Test case without steps skipped' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testNoStepsSkipped',
                '[@status="canceled"]/failure/message'
                ),
                'Skipped',
            ],
            'Successful test case with single step: name' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testSingleSuccessfulStepWithTitle',
                '[@status="passed"]/steps/step[1][@status="passed"]/name'
                ),
                'Step 1 name',
            ],
            'Successful test case with single step: title' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testSingleSuccessfulStepWithTitle',
                '[@status="passed"]/steps/step[1][@status="passed"]/title'
                ),
                'Step 1 title',
            ],
            'Successful test case with single step: attachment' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testSingleSuccessfulStepWithTitle',
                '[@status="passed"]/steps/step[1][@status="passed"]/attachments/attachment[@title="Attachment for step 1"]'
                ),
                '',
            ],
            'Successful test case with two successful steps: step 2 name' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testTwoSuccessfulSteps',
                    '[@status="passed"]/steps/step[2][@status="passed"]/name'
                ),
                'Step 2 name',
            ],
            'First step in test case with two steps fails: failure' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testTwoStepsFirstFails',
                    '[@status="failed"]/failure/message'
                ),
                'Failure',
            ],
            'First step in test case with two steps fails: step 1 name' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testTwoStepsFirstFails',
                    '[@status="failed"]/steps/step[1][@status="failed"]/name'
                ),
                'Step 1 name',
            ],
            'Second step in test case with two steps fails: failure' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testTwoStepsSecondFails',
                    '[@status="failed"]/failure/message'
                ),
                'Failure',
            ],
            'Second step in test case with two steps fails: step 1 name' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testTwoStepsSecondFails',
                    '[@status="failed"]/steps/step[1][@status="passed"]/name'
                ),
                'Step 1 name',
            ],
            'Second step in test case with two steps fails: step 2 name' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testTwoStepsSecondFails',
                    '[@status="failed"]/steps/step[2][@status="failed"]/name'
                ),
                'Step 2 name',
            ],
        ];
    }

    public function testFileAttachment(): void
    {
        $attachmentNode = $this
            ->sources[AttachmentTest::class]
            ->query(
                $this->buildTestXPath(
                    'testFileAttachment',
                    '/attachments/attachment[@title="File attachment"]'
                )
            )
            ->item(0);
        self::assertNotNull($attachmentNode);
        self::assertTrue($attachmentNode->hasAttributes());
        $file =
            $this->buildPath . DIRECTORY_SEPARATOR .
            $attachmentNode->attributes->getNamedItem('source')->textContent;
        $content = file_get_contents($file);
        $expectedContent = file_get_contents(__DIR__ . '/../Generate/AttachmentTest.php');
        self::assertSame($expectedContent, $content);
    }

    public function testDataAttachment(): void
    {
        $attachmentNode = $this
            ->sources[AttachmentTest::class]
            ->query(
                $this->buildTestXPath(
                    'testDataAttachment',
                    '/attachments/attachment[@title="Text attachment"]'
                )
            )
            ->item(0);
        self::assertNotNull($attachmentNode);
        self::assertTrue($attachmentNode->hasAttributes());
        $file =
            $this->buildPath . DIRECTORY_SEPARATOR .
            $attachmentNode->attributes->getNamedItem('source')->textContent;
        $content = file_get_contents($file);
        self::assertSame('text', $content);
    }

    /**
     * @param string $class
     * @param string $xpath
     * @dataProvider providerNodeNotExists
     */
    public function testNodeNotExists(string $class, string $xpath): void
    {
        $testNode = $this
            ->sources[$class]
            ->query($xpath)
            ->item(0);
        self::assertNull($testNode);
    }

    public function providerNodeNotExists(): iterable
    {
        return [
            'Successful test case without steps: no steps' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testNoStepsSuccess',
                    '/steps'
                )
            ],
            'First step fails in test case with two steps: no second step' => [
                StepsTest::class,
                $this->buildTestXPath(
                    'testTwoStepsFirstFails',
                    '/steps/step[2]'
                )
            ],
        ];
    }

    private function buildTestXPath(string $testName, string $tail): string
    {
        return sprintf('/alr:test-suite/test-cases/test-case[name="%s"]%s', $testName, $tail);
    }
}
