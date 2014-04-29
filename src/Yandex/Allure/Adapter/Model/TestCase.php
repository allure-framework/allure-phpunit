<?php

namespace Yandex\Allure\Adapter\Model;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlAttribute;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\XmlRoot;

/**
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