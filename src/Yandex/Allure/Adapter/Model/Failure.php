<?php

namespace Yandex\Allure\Adapter\Model;

use JMS\Serializer\Annotation\SerializedName;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\XmlElement;

class Failure {
    /**
     * @var string
     * @Type("string")
     * @XmlElement(cdata=false)
     */
    private $message;

    /**
     * @var string
     * @Type("string")
     * @XmlElement(cdata=false)
     * @SerializedName("stack-trace")
     */
    private $stackTrace;

    function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getStackTrace()
    {
        return $this->stackTrace;
    }

    /**
     * @param string $stackTrace
     */
    public function setStackTrace($stackTrace)
    {
        $this->stackTrace = $stackTrace;
    }

}