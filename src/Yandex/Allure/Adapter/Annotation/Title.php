<?php

namespace Yandex\Allure\Adapter\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Title
{
    /**
     * @var string
     * @Required
     */
    public $value;
}