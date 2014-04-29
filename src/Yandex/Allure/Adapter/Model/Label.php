<?php

namespace Yandex\Allure\Adapter\Model;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlAttribute;
use JMS\Serializer\Annotation\XmlRoot;


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