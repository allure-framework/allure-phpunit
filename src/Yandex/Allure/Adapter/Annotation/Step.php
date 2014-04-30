<?php

namespace Yandex\Allure\Adapter\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Step {
    /**
     * @var string
     * @Required
     */
    public $name;
}