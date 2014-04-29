<?php

namespace Yandex\Allure\Adapter\Model;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlAttribute;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * @package Yandex\Allure\Adapter\Model
 * @XmlRoot("label")
 */
class Story implements Label {

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
     */
    function getName()
    {
        return 'story';
    }

}