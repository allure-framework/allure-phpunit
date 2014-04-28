<?php

namespace src;

use PHPUnit_Framework_TestCase;
use Yandex\Allure\Adapter\Annotation\Features;
use Yandex\Allure\Adapter\Annotation\Step;
use Yandex\Allure\Adapter\Annotation\Title;

/**
 * Class StackTest
 * @package src
 * @Title("Stack Test")
 */
class StackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @Title("Stack creation test")
     * @Step("Core operations")
     * @Features({"Initialization"})
     */
    public function testCreate()
    {
        $stack = array();
        $this->assertEquals(0, count($stack));
    }

    /**
     * @Title("Stack push operation test")
     * @Step("Core operations")
     * @Features({"Write operation"})
     */
    public function testPush()
    {
        $stack = array();
        array_push($stack, 'foo');
        $this->assertEquals('foo', $stack[count($stack)-1]);
        $this->assertEquals(1, count($stack));
    }

    /**
     * @Title("Stack pop operation test")
     * @Step("Core operations")
     * @Features({"Read operation"})
     */
    public function testPop()
    {
        $stack = array('foo');
        $this->assertEquals('foo', array_pop($stack));
        $this->assertEquals(0, count($stack));
    }
}