<?php

namespace Yandex\Allure\Adapter\Model;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlAttribute;
use JMS\Serializer\Annotation\XmlRoot;

/**
 * @package Yandex\Allure\Adapter\Model
 * @XmlRoot("label")
 */
class Label
{

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

    function __construct($name, $value)
    {
        $this->name = $name;
        $this->value = $value;
    }

    /**
     * @return string
     */
    function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    function getValue()
    {
        return $this->value;
    }

    /**
     * @param $featureName
     * @return Label
     */
    public static function feature($featureName)
    {
        return new Label('feature', $featureName);
    }

    /**
     * @param $storyName
     * @return Label
     */
    public static function story($storyName)
    {
        return new Label("story", $storyName);
    }
}