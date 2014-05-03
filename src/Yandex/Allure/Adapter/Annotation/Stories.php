<?php

namespace Yandex\Allure\Adapter\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Stories
{
    /**
     * @var array
     * @Required
     */
    public $stories;

    /**
     * @return array
     */
    public function getStories()
    {
        return $this->stories;
    }

}