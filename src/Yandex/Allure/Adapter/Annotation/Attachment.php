<?php

namespace Yandex\Allure\Adapter\Annotation;
use Yandex\Allure\Adapter\Model\AttachmentType;

/**
 * @Annotation
 * @Target({"METHOD"})
 * @package Yandex\Allure\Adapter\Annotation
 */
class Attachment {

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $type = AttachmentType::OTHER;

}