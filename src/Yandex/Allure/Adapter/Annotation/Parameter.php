<?php

namespace Yandex\Allure\Adapter\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Yandex\Allure\Adapter\Model\ParameterKind;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Parameter
{
    /**
     * @var string
     * @Required
     */
    public $name;

    /**
     * @var string
     * @Required
     */
    public $value;

    /**
     * @var string
     */
    public $kind = ParameterKind::ARGUMENT;

}