<?php

namespace Yandex\Allure\Adapter\Annotation;
use Doctrine\Common\Annotations\Annotation\Required;
use Yandex\Allure\Adapter\Model\AttachmentType;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Attachment {

    /**
     * @var string
     * @Required
     */
    public $name;

    /**
     * @var string
     */
    public $type = AttachmentType::OTHER;

}