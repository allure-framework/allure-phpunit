<?php

namespace Yandex\Allure\Adapter\Model;

use Doctrine\Common\Annotations\AnnotationRegistry;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\Annotation\Exclude;
use JMS\Serializer\Annotation\ExclusionPolicy;
use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlAttribute;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlNamespace;
use JMS\Serializer\Annotation\XmlRoot;
use JMS\Serializer\Annotation\XmlValue;
use Rhumsaa\Uuid\Uuid;

AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation',
    dirname(__FILE__)."/../../../../../vendor/jms/serializer/src"
);

/**
 * @package Yandex\Allure\Adapter\Model
 * @XmlNamespace(uri="urn:model.allure.qatools.yandex.ru")
 * @XmlRoot("test-suite")
 * @ExclusionPolicy("none")
 */
class TestSuite implements \Serializable {

    /**
     * @var int
     * @Type("integer")
     * @XmlAttribute
     */
    private $start;

    /**
     * @var int
     * @Type("integer")
     * @XmlAttribute
     */
    private $stop;

    /**
     * @var string
     * @Type("string")
     */
    private $name;

    /**
     * @var string
     * @Type("string")
     */
    private $title;

    /**
     * @var Description
     * @Type("Yandex\Allure\Adapter\Model\Description")
     */
    private $description;

    /**
     * @var array
     * @Type("array<Yandex\Allure\Adapter\Model\TestCase>")
     * @XmlList(inline = true, entry = "test-case")
     * @SerializedName("test-cases")
     */
    private $testCases;

    /**
     * @var array
     * @Type("array<Yandex\Allure\Adapter\Model\Label>")
     * @XmlList(inline = true, entry = "label")
     */
    private $labels;

    /**
     * @var Serializer
     * @Exclude
     */
    private $serializer;

    /**
     * @var Uuid
     * @Exclude
     */
    private $uuid;

    function __construct($name, $start)
    {
        $this->name = $name;
        $this->start = $start;
        $this->stop = $start;
        $this->testCases = array();
        $this->labels = array();
        $this->uuid = Uuid::uuid4();
    }

    /**
     * @return Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getStop()
    {
        return $this->stop;
    }

    /**
     * @return int
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param int $stop
     */
    public function setStop($stop)
    {
        $this->stop = $stop;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Description $description
     */
    public function setDescription(Description $description)
    {
        $this->description = $description;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\TestCase $testCase
     */
    public function addTestCase(TestCase $testCase)
    {
        $this->testCases[$testCase->getName()] = $testCase;
    }

    /**
     * Returns test case by name
     * @param string $name
     * @return \Yandex\Allure\Adapter\Model\TestCase
     */
    public function getTestCase($name)
    {
        return $this->testCases[$name];
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Label $label
     */
    public function addLabel(Label $label)
    {
        $this->labels[] = $label;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return $this->getSerializer()->serialize($this, 'xml');
    }

    /**
     * @param string $serialized
     * @return mixed
     */
    public function unserialize($serialized)
    {
        return $this->getSerializer()->deserialize($serialized, 'Yandex\Allure\Adapter\Model\TestSuite', 'xml');
    }

    /**
     * @return Serializer
     */
    private function getSerializer()
    {
        if (!isset($this->serializer)){
            $this->serializer = SerializerBuilder::create()->build();
        }
        return $this->serializer;
    }
}

/**'
 * @package Yandex\Allure\Adapter\Model
 * @XmlRoot("test-case")
 */
class TestCase {

    /**
     * @var int
     * @Type("integer")
     * @XmlAttribute
     */
    private $start;

    /**
     * @var int
     * @Type("integer")
     * @XmlAttribute
     */
    private $stop;

    /**
     * @var string
     * @Type("string")
     */
    private $name;

    /**
     * @var string
     * @Type("string")
     */
    private $severity = SeverityLevel::NORMAL;

    /**
     * @var string
     * @Type("string")
     */
    private $title;

    /**
     * @var Description
     * @Type("Yandex\Allure\Adapter\Model\Description")
     */
    private $description;

    /**
     * @var Failure
     * @Type("Yandex\Allure\Adapter\Model\Failure")
     */
    private $failure;

    /**
     * @var array
     * @Type("array<Yandex\Allure\Adapter\Model\Step>")
     * @XmlList(inline = true, entry = "step")
     */
    private $steps;

    /**
     * @var array
     * @Type("array<Yandex\Allure\Adapter\Model\Attachment>")
     * @XmlList(inline = true, entry = "attachment")
     */
    private $attachments;

    /**
     * @Type("array<Yandex\Allure\Adapter\Model\Label>")
     * @XmlList(inline = true, entry = "label")
     */
    private $labels;

    /**
     * @var array
     * @Type("array<Yandex\Allure\Adapter\Model\Parameter>")
     * @XmlList(inline = true, entry = "parameter")
     */
    private $parameters;

    function __construct($name, $start)
    {
        $this->name = $name;
        $this->start = $start;
        $this->stop = $start;
        $this->steps = array();
        $this->labels = array();
        $this->attachments = array();
        $this->parameters = array();
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return int
     */
    public function getStop()
    {
        return $this->stop;
    }

    /**
     * @return \Yandex\Allure\Adapter\Model\Description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return \Yandex\Allure\Adapter\Model\Failure
     */
    public function getFailure()
    {
        return $this->failure;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param string $severity
     */
    public function setSeverity($severity)
    {
        $this->severity = ConstantChecker::validate('SeverityLevel', $severity);
    }

    /**
     * @param int $stop
     */
    public function setStop($stop)
    {
        $this->stop = $stop;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Description $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Failure $failure
     */
    public function setFailure($failure)
    {
        $this->failure = $failure;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Label $label
     */
    public function addLabel(Label $label)
    {
        $this->labels[] = $label;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Step $step
     */
    public function addStep(Step $step)
    {
        $this->steps[] = $step;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Attachment $attachment
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments[] = $attachment;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Parameter $parameter
     */
    public function addParameter(Parameter $parameter)
    {
        $this->parameters[] = $parameter;
    }

}

class Step {

    /**
     * @var int
     * @Type("integer")
     * @XmlAttribute
     */
    private $start;

    /**
     * @var int
     * @Type("integer")
     * @XmlAttribute
     */
    private $stop;

    /**
     * @var string
     * @Type("string")
     */
    private $name;

    /**
     * @var string
     * @Type("string")
     */
    private $title;

    /**
     * @var array
     * @Type("array<Yandex\Allure\Adapter\Model\Step>")
     * @XmlList(inline = true, entry = "step")
     */
    private $steps;

    /**
     * @var array
     * @Type("array<Yandex\Allure\Adapter\Model\Attachment>")
     * @XmlList(inline = true, entry = "attachment")
     */
    private $attachments;

    /**
     * @var Status
     * @Type("string")
     * @XmlAttribute
     */
    private $status;

    function __construct($name, $start, $stop, $status)
    {
        $this->name = $name;
        $this->start = $start;
        $this->stop = $stop;
        $this->status = ConstantChecker::validate('Status', $status);
        $this->steps = array();
        $this->attachments = array();
    }

    /**
     * @return array
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * @return array
     */
    public function getSteps()
    {
        return $this->steps;
    }

    /**
     * @return int
     */
    public function getStop()
    {
        return $this->stop;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Step $step
     */
    public function addStep(Step $step)
    {
        $this->steps[] = $step;
    }

    /**
     * @param \Yandex\Allure\Adapter\Model\Attachment $attachment
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments[] = $attachment;
    }

}

/**
 * @package Yandex\Allure\Adapter\Model
 */
class Attachment {

    /**
     * @var string
     * @Type("string")
     * @XmlAttribute
     */
    private $title;

    /**
     * @var string
     * @Type("string")
     * @XmlAttribute
     */
    private $source;

    /**
     * @var string
     * @Type("string")
     * @XmlAttribute
     */
    private $type;

    function __construct($title, $source, $type)
    {
        $this->source = $source;
        $this->title = $title;
        $this->type = ConstantChecker::validate('AttachmentType', $type);
    }


    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

}

class Failure {
    /**
     * @var string
     * @Type("string")
     */
    private $message;

    /**
     * @var string
     * @Type("string")
     * @SerializedName("stack-trace")
     */
    private $stackTrace;

    function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getStackTrace()
    {
        return $this->stackTrace;
    }

    /**
     * @param string $stackTrace
     */
    public function setStackTrace($stackTrace)
    {
        $this->stackTrace = $stackTrace;
    }

}

class Parameter {

    /**
     * @var string
     * @Type("string")
     * @XmlAttribute
     */
    private $name;

    /**
     * @var string
     * @Type("string")
     * @XmlAttribute
     */
    private $value;

    /**
     * @var string
     * @Type("string")
     * @XmlAttribute
     */
    private $kind;

    function __construct($name, $value, $kind)
    {
        $this->kind = ConstantChecker::validate('ParameterKind', $kind);
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

}

/**
 * @package Yandex\Allure\Adapter\Model
 * @XmlRoot("label")
 */
abstract class Label {
    /**
     * @Type("string")
     * @XmlAttribute
     */
    private $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     * @XmlAttribute
     */
    abstract function getName();

}

class Story extends Label {

    function getName()
    {
        return LabelName::STORY;
    }

}

class Feature extends Label {

    function getName()
    {
        return LabelName::FEATURE;
    }

}

/**
 * @package Yandex\Allure\Adapter\Model
 * @XmlRoot("description")
 */
class Description {

    /**
     * @Type("string")
     * @XmlAttribute
     */
    private $type;

    /**
     * @Type("string")
     * @XmlValue
     */
    private $value;

    function __construct($type, $value)
    {
        $this->type = ConstantChecker::validate('DescriptionType', $type);
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

}

/**
 * @package Yandex\Allure\Adapter\Model
 */
class ConstantChecker {

    /**
     * Checks whether constant with the specified value is present. If it's present it's returned. An
     * exception is thrown otherwise.
     * @param $className
     * @param $value
     * @throws \Exception
     * @return
     */
    public static function validate($className, $value)
    {
        $ref = new \ReflectionClass($className);
        foreach ($ref->getConstants() as $constantValue) {
            if ($constantValue === $value){
                return $value;
            }
        }
        throw new \Exception("Value \"$value\" is not present in class $className. You should use a constant from this class.");
    }

}

/**
 * Step status
 * @package Yandex\Allure\Adapter\Model
 */
final class Status {
    const FAILED = 'failed';
    const BROKEN = 'broken';
    const PASSED = 'passed';
    const SKIPPED = 'skipped';
}

/**
 * Severity level
 * @package Yandex\Allure\Adapter\Model
 */
final class SeverityLevel {
    const BLOCKER = 'blocker';
    const CRITICAL = 'critical';
    const NORMAL = 'normal';
    const MINOR = 'minor';
    const TRIVIAL = 'trivial';
}

/**
 * Description type
 * @package Yandex\Allure\Adapter\Model
 */
final class DescriptionType {
    const TEXT = 'text';
    const HTML = 'html';
    const MARKDOWN = 'markdown';
}

/**
 * Attachment type
 * @package Yandex\Allure\Adapter\Model
 */
final class AttachmentType {
    const TXT = 'txt';
    const HTML = 'html';
    const XML = 'xml';
    const PNG = 'png';
    const JPG = 'jpg';
    const JSON = 'json';
    const OTHER = 'other';
}

/**
 * Parameter kind
 * @package Yandex\Allure\Adapter\Model
 */
final class ParameterKind {
    const ARGUMENT = 'argument';
    const SYSTEM_PROPERTY = 'system-property';
    const ENVIRONMENT_VARIABLE = 'environment-variable';
}

/**
 * Parameter kind
 * @package Yandex\Allure\Adapter\Model
 */
final class LabelName {
    const STORY = 'story';
    const FEATURE = 'feature';
}