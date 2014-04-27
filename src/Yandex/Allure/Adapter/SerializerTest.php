<?php

namespace Yandex\Allure\Adapter;
require(dirname(__FILE__).'/../../../../vendor/autoload.php');

use Doctrine\Common\Annotations\AnnotationRegistry;
use JMS\Serializer\SerializerBuilder;
AnnotationRegistry::registerAutoloadNamespace(
    'JMS\Serializer\Annotation',
    dirname(__FILE__)."/../../../../vendor/jms/serializer/src"
);

use JMS\Serializer\Annotation\XmlRoot;
use JMS\Serializer\Annotation\XmlList;
use JMS\Serializer\Annotation\Type;
use Rhumsaa\Uuid\Uuid;

/**
 * Class Class1
 * @package Yandex\Allure\Adapter
 * @XmlRoot("class1")
 */
class Class1 {
    /**
     * @Type("string")
     */
    private $field1;

    /**
     * @Type("string")
     */
    private $field2;

    /**
     * @Type("array<Yandex\Allure\Adapter\Class2>")
     * @XmlList(inline = true, entry = "class2")
     */
    private $field3;

    function __construct($field1, $field2, $field3)
    {
        $this->field1 = $field1;
        $this->field2 = $field2;
        $this->field3 = $field3;
    }

    /**
     * @return mixed
     */
    public function getField1()
    {
        return $this->field1;
    }

    /**
     * @return mixed
     */
    public function getField2()
    {
        return $this->field2;
    }

    /**
     * @return mixed
     */
    public function getField3()
    {
        return $this->field3;
    }

}

/**
 * Class Class2
 * @package Yandex\Allure\Adapter
 * @XmlRoot("class2")
 */
class Class2 {
    /**
     * @Type("string")
     */
    private $field4;

    function __construct($field4)
    {
        $this->field4 = $field4;
    }

    /**
     * @return mixed
     */
    public function getField4()
    {
        return $this->field4;
    }

}

$instance = new Class1(1, 2, array(new Class2(3), new Class2(4)));
$serializer = SerializerBuilder::create()->build();
$xml = $serializer->serialize($instance, 'xml');
echo $xml;
$object = $serializer->deserialize($xml, 'Yandex\Allure\Adapter\Class1', 'xml');
var_dump($object);