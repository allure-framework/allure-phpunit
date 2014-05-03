<?php

namespace Yandex\Allure\Adapter\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Features
{
    /**
     * @var array
     * @Required
     */
    public $featureNames;

    /**
     * @return array
     */
    public function getFeatureNames()
    {
        return $this->featureNames;
    }

}