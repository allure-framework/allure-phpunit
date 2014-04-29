<?php

namespace Yandex\Allure\Adapter\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Stories {
    /**
     * @var array
     */
    private $stories;

    function __construct(array $stories)
    {
        $this->stories = $stories;
    }

    /**
     * @return array
     */
    public function getStories()
    {
        return $this->stories;
    }

}