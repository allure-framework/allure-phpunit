<?php

namespace src;

use PHPUnit_Framework_TestCase;
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
     * @Title("Stack push and pop operations test")
     * @Step("Core stack operations")
     */
    public function testPushAndPop()
    {
        $stack = array();
        $this->assertEquals(0, count($stack));

        array_push($stack, 'foo');
        $this->assertEquals('foo', $stack[count($stack)-1]);
        $this->assertEquals(1, count($stack));

        $this->assertEquals('foo', array_pop($stack));
        $this->assertEquals(0, count($stack));
    }
}