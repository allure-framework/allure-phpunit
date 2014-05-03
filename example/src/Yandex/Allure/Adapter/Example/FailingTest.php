<?php

namespace Yandex\Allure\Adapter\Example;

use PHPUnit_Framework_TestCase;
use Yandex\Allure\Adapter\Annotation\Title;

/**
 * @package Yandex\Allure\Adapter\Example
 * @Title("A set of failing tests")
 */
class FailingTest extends PHPUnit_Framework_TestCase
{

    /**
     * @Title("Assertion error example")
     */
    public function testAssertionErrorExample()
    {
        $this->assertTrue(false);
    }

    /**
     * @Title("Test execution error example")
     */
    public function testErrorExample()
    {
        throw new \Exception('I\'m an unexpected exception.');
    }

    /**
     * @Title("Skipped test example")
     */
    public function testSkippedTestExample()
    {
        $this->markTestSkipped('I\'m a lazy bastard. Skip me!');
    }

    /**
     * @Title("Incomplete test example")
     */
    public function testIncompleteTestExample()
    {
        $this->markTestIncomplete('Oops... Something is missing in my logic.');
    }

    /**
     * @Title("Risky test example")
     */
    public function testRiskyTestExample()
    {
        //I contain no assertions at all. This is why I'm a risky test. :'(
    }

} 