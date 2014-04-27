<?php

namespace Yandex\Allure\Adapter\Annotation;
use Yandex\Allure\Adapter\Model\DescriptionType;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Description {
    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $type = DescriptionType::TEXT;
}