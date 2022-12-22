# Allure PHPUnit adapter

[![Latest Stable Version](http://poser.pugx.org/allure-framework/allure-phpunit/v)](https://packagist.org/packages/allure-framework/allure-phpunit)
[![Build](https://github.com/allure-framework/allure-phpunit/actions/workflows/build.yml/badge.svg)](https://github.com/allure-framework/allure-phpunit/actions/workflows/build.yml)
[![Type Coverage](https://shepherd.dev/github/allure-framework/allure-phpunit/coverage.svg)](https://shepherd.dev/github/allure-framework/allure-phpunit)
[![Psalm Level](https://shepherd.dev/github/allure-framework/allure-phpunit/level.svg)](https://shepherd.dev/github/allure-framework/allure-phpunit)
[![Total Downloads](http://poser.pugx.org/allure-framework/allure-phpunit/downloads)](https://packagist.org/packages/allure-framework/allure-phpunit)
[![License](http://poser.pugx.org/allure-framework/allure-phpunit/license)](https://packagist.org/packages/allure-framework/allure-phpunit)

This an official PHPUnit adapter for Allure Framework - a flexible, lightweight and multi-language framework for writing self-documenting tests.

## Table of Contents
* [What is this for?](#what-is-this-for)
* [Example Project](#example-project)
* [How to Generate Report](#how-to-generate-report)
* [Installation and Usage](#installation-and-usage)
* [Main Features](#main-features)
  * [Title](#human-readable-test-class-or-test-method-title)
  * [Description](#extended-test-class-or-test-method-description)
  * [Test severity](#set-test-severity)
  * [Test parameters](#specify-test-parameters-information)
  * [Features and Stories](#map-test-classes-and-test-methods-to-features-and-stories)
  * [Attachments](#attach-files-to-report)
  * [Steps](#divide-test-methods-into-steps)

## What is this for?
The main purpose of this adapter is to accumulate information about your tests and write it out to a set of JSON files: one for each test class. Then you can use a standalone command line tool or a plugin for popular continuous integration systems to generate an HTML page showing your tests in a good form.

## Examples
Please take a look at [these example tests](./test/report/Generate).

## How to generate report
This adapter only generates JSON files containing information about tests. See [wiki section](https://docs.qameta.io/allure/#_reporting) on how to generate report.

## Installation && Usage
**Note:** this adapter supports Allure 2.x.x only.

Supported PHP versions: 8.1-8.3.

In order to use this adapter you need to add a new dependency to your **composer.json** file:
```
{
    "require": {
	    "php": "^8.1",
	    "allure-framework/allure-phpunit": "^3"
    }
}
```
Then add Allure test listener in **phpunit.xml** file:
```xml
<extensions>
    <bootstrap class="Qameta\Allure\PHPUnit\AllureExtension">
          <!-- Path to config file (default is config/allure.config.php) -->
          <parameter name="config" value="config/allure.config.php" />
    </bootstrap>
</extensions>
```
Config is common PHP file that should return an array: 
```php
<?php

return [
    // Path to output directory (default is build/allure-results)
    'outputDirectory' => 'build/allure-results',
    'linkTemplates' => [
        // Class or object must implement \Qameta\Allure\Setup\LinkTemplateInterface
        'tms' => \My\LinkTemplate::class,
    ],
    'setupHook' => function (): void {
        // Some actions performed before starting the lifecycle
    },
     // Class or object must implement \Qameta\Allure\PHPUnit\Setup\ThreadDetectorInterface
    'threadDetector' => \My\ThreadDetector::class,
    'lifecycleHooks' => [
        // Class or object must implement one of \Qameta\Allure\Hook\LifecycleHookInterface descendants.
        \My\LifecycleHook::class,
    ],
];
```

After running PHPUnit tests a new folder will be created (**build/allure-results** in the example above). This folder will contain generated JSON files. See [framework help](https://docs.qameta.io/allure/#_reporting) for details about how to generate report from JSON files. By default generated report will only show a limited set of information but you can use cool Allure features by adding a minimum of test code changes. Read next section for details.

## Main features
This adapter comes with a set of PHP annotations and traits allowing to use main Allure features.

### Human-readable test class or test method title
In order to add such title to any test class or [test case](https://github.com/allure-framework/allure1/wiki/Glossary#test-case) method you need to annotate it with **#[DisplayName]** annotation:
```php
namespace Example\Tests;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\DisplayName;

#[DisplayName("Human-readable test class title")]
class SomeTest extends TestCase
{
    #[DisplayName("Human-readable test method title")]
    public function testCaseMethod(): void
    {
        //Some implementation here...
    }
}
```

### Extended test class or test method description
Similarly you can add detailed description for each test class and [test method](https://github.com/allure-framework/allure1/wiki/Glossary#test-case). To add such description simply use **#[Description]** annotation:
```php
namespace Example\Tests;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\Description;

#[Description("Detailed description for **test** class")]
class SomeTest extends TestCase
{
    #[Description("Detailed description for <b>test class</b>", isHtml: true)]
    public function testCaseMethod(): void
    {
        //Some implementation here...
    }
}
```
Description can be added in Markdown format (which is default one) or in HTML format. For HTML simply pass `true` value for optional `isHtml` argument.

### Set test severity
**#[Severity]** annotation is used in order to prioritize test methods by severity:

```php
namespace Example\Tests;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\Severity;

class SomeTest extends TestCase
{
    #[Severity(Severity::MINOR)]
    public function testCaseMethod(): void
    {
        //Some implementation here...
    }
}
```

### Specify test parameters information
In order to add information about test method [parameters](https://github.com/allure-framework/allure-core/wiki/Glossary#parameter) you should use **#[Parameter]** annotation. You can also use static shortcut if your marameter has dynamic value:

```php
namespace Example\Tests;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Allure;
use Qameta\Allure\Attribute\Parameter;

class SomeTest extends TestCase
{
    #[
        Parameter("param1", "value1"),
        Parameter("param2", "value2"),
    ]
    public function testCaseMethod(): void
    {
        //Some implementation here...
        Allure::parameter("param3", $someVar);
    }
}
```

### Map test classes and test methods to features and stories
In some development approaches tests are classified by [stories](https://github.com/allure-framework/allure-core/wiki/Glossary#user-story) and [features](https://github.com/allure-framework/allure-core/wiki/Glossary#feature). If you're using this then you can annotate your test with **#[Story]** and **#[Feature]** annotations:
```php
namespace Example\Tests;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Attribute\Feature;
use Qameta\Allure\Attribute\Story;

#[
    Story("story1"),
    Story("story2"),
    Feature("feature1"),
    Feature("feature2"),
    Feature("feature3"),
]
class SomeTest extends TestCase
{
    #[
        Story("story3"),
        Feature("feature4"),
    ]
    public function testCaseMethod(): void
    {
        //Some implementation here...
    }
}
```
You will then be able to filter tests by specified features and stories in generated Allure report.

### Attach files to report
If you wish to [attach some files](https://github.com/allure-framework/allure-core/wiki/Glossary#attachment) generated during PHPUnit run (screenshots, log files, dumps and so on) to report - then you need to use static shortcuts in your test class:
```php
namespace Example\Tests;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Allure;

class SomeTest extends TestCase
{

    public function testCaseMethod()
    {
        //Some implementation here...
        Allure::attachment("Attachment 1", "attachment content", 'text/plain');
        Allure::attachmentFile("Attachment 2", "/path/to/file.png", 'image/png');
        //Some implementation here...
    }
}
```
In order to create an [attachment](https://github.com/allure-framework/allure-core/wiki/Glossary#attachment) simply call **Allure::attachment()** method. This method accepts human-readable name, string content and MIME attachment type. To attach a file, use **Allure::attachmentFile()** method that accepts file name instead of string content.

### Divide test methods into steps
Allure framework also supports very useful feature called [steps](https://github.com/allure-framework/allure-core/wiki/Glossary#test-step). Consider a test method which has complex logic inside and several assertions. When an exception is thrown or one of assertions fails sometimes it's very difficult to determine which one caused the failure. Allure steps allow dividing test method logic into several isolated pieces having independent run statuses such as **passed** or **failed**. This allows to have much cleaner understanding of what really happens. In order to use steps simply use static shortcuts:

```php
namespace Example\Tests;

use PHPUnit\Framework\TestCase;
use Qameta\Allure\Allure;
use Qameta\Allure\Attribute\Parameter;
use Qameta\Allure\Attribute\Title;
use Qameta\Allure\StepContextInterface;

class SomeTest extends TestCase
{

    public function testCaseMethod(): void
    {
        //Some implementation here...
        $x = Allure::runStep(
            #[Title('First step')]
            function (StepContextInterface $step): string {
                $step->parameter('param1', $someValue);
                
                return 'foo';
            },
        );
        Allure::runStep([$this, 'stepTwo']);
        //Some implementation here...
    }

    #[
        Title("Second step"),
        Parameter("param2", "value2"),
    ]
    private function stepTwo(): void
    {
        //Some implementation here...
    }
}
```
The entire test method execution status will depend on every step but information about steps status will be stored separately.
