# Allure PHPUnit adapter

This an official PHPUnit adapter for Allure Framework - a flexible, lightweight and multi-language framework for writing self-documenting tests.

## What is this for?
The main purpose of this adapter is to accumulate information about your tests and write it out to a set of XML files: one for each test class. Then you can use a standalone command line tool or a plugin for popular continuous integration systems to generate an HTML page showing your tests in a good form.

## Example project
Example project is located at: https://github.com/allure-framework/allure-phpunit-example

## Usage
In order to use this adapter you need to add a new dependency to your **composer.json** file:
```
{
    "require": {
	    "php": ">=5.4.0",
	    "allure-framework/allure-phpunit": "~1.0"
    }
}
```
Then add Allure test listener in **phpunit.xml** file:
```xml
<listeners>
    <listener class="Yandex\Allure\Adapter\AllureAdapter" file="vendor/allure-framework/allure-phpunit/Yandex/Allure/Adapter/AllureAdapter.php">
        <arguments>
            <string>allure-report-data</string> <!-- XML files output directory -->
            <boolean>true</boolean> <!-- Whether to delete previous results on rerun -->
        </arguments>
    </listener>
</listeners>
```
After running PHPUnit tests a new folder will be created (**allure-report-data** in the example above). This folder will contain generated XML files. See [framework help](https://github.com/allure-framework/allure-core/wiki) for details about how to generate report from XML files. By default generated report will only show a limited set of information but you can use of cool Allure features by adding a minimum of test code changes. Read next section for details.

## Advanced features
This adapter comes with a set of PHP annotations and traits allowing to use some advanced Allure features.

### Human-readable test class or test method title
In order to add such title to any test class or test case method you need to annotate it with **@Title** annotation:
```php
namespace Example\Tests;

use PHPUnit_Framework_TestCase;
use Yandex\Allure\Adapter\Annotation\Title;

/**
 * @Title("Human-readable test class title")
 */
class SomeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @Title("Human-readable test method title")
     */
    public function testCase()
    {
        //Some implementation here...
    }
}
```

### Extended test class or test method description
Similarly you can add detailed description for each test class and test method. To add such description simply use **@Description** annotation:
```php
namespace Example\Tests;

use PHPUnit_Framework_TestCase;
use Yandex\Allure\Adapter\Annotation\Description;
use Yandex\Allure\Adapter\Model\DescriptionType;

/**
 * @Description("Detailed description for test class")
 */
class SomeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @Description(value = "Detailed description for <b>test class</b>.", type = DescriptionType::HTML)
     */
    public function testCase()
    {
        //Some implementation here...
    }
}
```
Description can be added in plain text, HTML or Markdown format - simply assign different **type** value.

### Set test severity
**@Severity** annotation is used in order to prioritize test methods by severity:
```php
namespace Example\Tests;

use PHPUnit_Framework_TestCase;
use Yandex\Allure\Adapter\Annotation\Severity;
use Yandex\Allure\Adapter\Model\SeverityLevel;

class SomeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @Severity(level = SeverityLevel::MINOR)
     */
    public function testCase()
    {
        //Some implementation here...
    }
}
```

### Specify test parameters information
In order to add information about test method parameters you should use **@Parameter** annotation:
```php
namespace Example\Tests;

use PHPUnit_Framework_TestCase;
use Yandex\Allure\Adapter\Annotation\Parameter;
use Yandex\Allure\Adapter\Model\ParameterKind;

class SomeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @Parameter(name = "param1", value = "value1")
     * @Parameter(name = "param2", value = "value2", kind = ParameterKind::SYSTEM_PROPERTY)
     */
    public function testCase()
    {
        //Some implementation here...
    }
}
```

### Map test classes and test methods to features and stories
In some development approaches tests are classified by **stories** and **features**. If you're using this then you can annotate your test with **@Stories** and **@Features** annotations:
```php
namespace Example\Tests;

use PHPUnit_Framework_TestCase;
use Yandex\Allure\Adapter\Annotation\Features;
use Yandex\Allure\Adapter\Annotation\Stories;

/**
 * @Stories({"story1", "story2"})
 * @Features({"feature1", "feature2", "feature3"})
 */
class SomeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @Features({"feature2"})
     * @Stories({"story1"})
     */
    public function testCase()
    {
        //Some implementation here...
    }
}
```
You will then be able to filter tests by specified features and stories in generated Allure report.

### Attach files to report
If you wish to attach some files generated during PHPUnit run (screenshots, log files, dumps and so on) to report - then you need to use **AttachmentSupport** trait in your test class:
```php
namespace Example\Tests;

use PHPUnit_Framework_TestCase;
use Yandex\Allure\Adapter\Model\AttachmentType;
use Yandex\Allure\Adapter\Support\AttachmentSupport;

class SomeTest extends PHPUnit_Framework_TestCase
{

    use AttachmentSupport;

    public function testCase()
    {
        //Some implementation here...
        $filePath = $this->outputSomeContentToTemporaryFile();
        $this->addAttachment($filePath, 'Attachment human-readable name', AttachmentType::TXT);
        //Some implementation here...
    }

    private function outputSomeContentToTemporaryFile()
    {
        $tmpPath = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmpPath, 'Some content to be outputted to temporary file.');
        return $tmpPath;
    }

}
```
In order to create an attachment simply call **AttachmentSupport::addAttachment()** method. This method accepts attachment type, human-readable name and a string either storing full path to the file we need to attach or file contents.

### Divide test methods into steps
Allure framework also supports very useful feature called **steps**. Consider a test method which has complex logic inside and several assertions. When an exception is thrown or one of assertions fails sometimes it's very difficult to determine which one caused the failure. Allure steps allow to divide test method logic into several isolated pieces having independent run statuses such as **passed** or **failed**. This allows to have much more cleaner understanding of what really happens. In order to use steps simply import **StepSupport** trait in your test class:
```php
namespace Example\Tests;

use PHPUnit_Framework_TestCase;
use Yandex\Allure\Adapter\Support\StepSupport;

class SomeTest extends PHPUnit_Framework_TestCase
{

    use StepSupport;

    public function testCase()
    {
        //Some implementation here...
        $this->executeStep("This is step one", function () {
            $this->stepOne();
        });

        $this->executeStep("This is step two", function () {
            $this-stepTwo();
        });

        $this->executeStep("This is step three", function () {
            $this->stepThree('someArgument');
        });
        //Some implementation here...
    }

    private function stepOne()
    {
        //Some implementation here...
    }

    private function stepTwo()
    {
        //Some implementation here...
    }

    private function stepThree($argument)
    {
        //Some implementation here...
    }

}
```
The entire test method execution status will depend on every step but information about steps status will be stored separately.
