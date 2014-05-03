<?php

namespace Yandex\Allure\Adapter\Example;

use PHPUnit_Framework_TestCase;
use Yandex\Allure\Adapter\Annotation\Features;
use Yandex\Allure\Adapter\Annotation\Stories;
use Yandex\Allure\Adapter\Annotation\Title;

/**
 * @package Yandex\Allure\Adapter\Example
 * @Title("Stack Test")
 */
class StackTest extends PHPUnit_Framework_TestCase
{
    /**
     * @Title("Stack creation test")
     * @Features({"Initialization"})
     * @Stories({"Stack should be a LIFO data structure"})
     */
    public function testCreate()
    {
        $stack = array();
        $this->assertEquals(0, count($stack));
    }

    /**
     * @Title("Stack push operation test")
     * @Features({"Write operation"})
     * @Stories({"Stack should be a LIFO data structure"})
     */
    public function testPush()
    {
        $stack = array();
        array_push($stack, 'foo');
        $this->assertEquals('foo', $stack[count($stack) - 1]);
        $this->assertEquals(1, count($stack));
    }

    /**
     * @Title("Stack pop operation test")
     * @Features({"Read operation"})
     * @Stories({"Stack should be a LIFO data structure"})
     */
    public function testPop()
    {
        $stack = array('foo');
        $this->assertEquals('foo', array_pop($stack));
        $this->assertEquals(0, count($stack));
    }
}