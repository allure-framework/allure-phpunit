<?php

namespace Yandex\Allure\Adapter\Annotation;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Features {
    /**
     * @var array
     */
    private $featureNames;

    function __construct(array $featureNames)
    {
        $this->featureNames = $featureNames;
    }

    /**
     * @return array
     */
    public function getFeatureNames()
    {
        return $this->featureNames;
    }

}