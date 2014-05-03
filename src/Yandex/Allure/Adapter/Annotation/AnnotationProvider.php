<?php

namespace Yandex\Allure\Adapter\Annotation;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\IndexedReader;

class AnnotationProvider {

    private static $annotationsReader;

    /**
     * Returns a list of class annotations
     * @param $instance
     * @return array
     */
    public static function getClassAnnotations($instance)
    {
        $ref = new \ReflectionClass($instance);
        return self::getAnnotationsReader()->getClassAnnotations($ref);
    }

    /**
     * Returns a list of method annotations
     * @param $instance
     * @param $methodName
     * @return array
     */
    public static function getMethodAnnotations($instance, $methodName)
    {
        $ref = new \ReflectionMethod($instance, $methodName);
        return self::getAnnotationsReader()->getMethodAnnotations($ref);
    }

    /**
     * @return IndexedReader
     */
    private static function getAnnotationsReader()
    {
        if (!isset(self::$annotationsReader)){
            self::$annotationsReader = new IndexedReader(new AnnotationReader());
        }
        return self::$annotationsReader;
    }

}