<?php

namespace Yandex\Allure\Adapter\Model;

class Provider
{

    /**
     * @var array
     */
    private static $testSuites = array();

    /**
     * @var string
     */
    private static $outputDirectory;

    /**
     * @param string $outputDirectory
     */
    public static function setOutputDirectory($outputDirectory)
    {
        self::$outputDirectory = $outputDirectory;
    }

    /**
     * @return string
     */
    public static function getOutputDirectory()
    {
        return self::$outputDirectory;
    }

    /**
     * @param TestSuite $testSuite
     */
    public static function pushTestSuite(TestSuite $testSuite)
    {
        self::$testSuites[] = $testSuite;
    }

    /**
     * @return TestSuite
     */
    public static function getCurrentTestSuite()
    {
        return end(self::$testSuites);
    }

    /**
     * @return TestSuite
     */
    public static function popTestSuite()
    {
        return array_pop(self::$testSuites);
    }

}