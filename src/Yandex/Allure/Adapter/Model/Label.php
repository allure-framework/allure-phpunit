<?php

namespace Yandex\Allure\Adapter\Model;
use JMS\Serializer\Annotation\XmlRoot;
use JMS\Serializer\Annotation\XmlAttribute;
use JMS\Serializer\Annotation\Discriminator;

/**
 * @package Yandex\Allure\Adapter\Model
 * @XmlRoot("label")
 * @Discriminator(field = "value", map = {"story": "Story", "feature": "Feature"})
 */
abstract class Label {

    /**
     * @var string
     * @XmlAttribute
     */
    private $name;

    /**
     * @var string
     * @XmlAttribute
     */
    private $value;

    /**
     * @return string
     *
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     *
     */
    public function getValue()
    {
        return $this->value;
    }
    
}