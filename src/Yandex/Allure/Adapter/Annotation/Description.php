<?php

namespace Yandex\Allure\Adapter\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Yandex\Allure\Adapter\Model\DescriptionType;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Description
{
    /**
     * @var string
     * @Required
     */
    public $value;

    /**
     * @var string
     */
    public $type = DescriptionType::TEXT;
}