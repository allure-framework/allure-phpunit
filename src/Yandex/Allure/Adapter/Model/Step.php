<?php

namespace Yandex\Allure\Adapter\Model;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlAttribute;
use JMS\Serializer\Annotation\XmlList;

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