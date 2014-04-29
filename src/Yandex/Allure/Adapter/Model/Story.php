<?php

namespace Yandex\Allure\Adapter\Model;

/**
 * @package Yandex\Allure\Adapter\Model
 */
class Story extends Label {

    function getName()
    {
        return LabelName::STORY;
    }

}