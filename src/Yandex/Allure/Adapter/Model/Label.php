<?php

namespace Yandex\Allure\Adapter\Model;
use JMS\Serializer\Annotation\XmlRoot;


/**
 * @package Yandex\Allure\Adapter\Model
 * @XmlRoot("label")
 */
interface Label {

    /**
     * @return string
     */
    function getName();

    /**
     * @return string
     */
    function getValue();
    
}