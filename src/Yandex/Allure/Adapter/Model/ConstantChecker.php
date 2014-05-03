<?php

namespace Yandex\Allure\Adapter\Model;


/**
 * @package Yandex\Allure\Adapter\Model
 */
class ConstantChecker
{

    /**
     * Checks whether constant with the specified value is present. If it's present it's returned. An
     * exception is thrown otherwise.
     * @param $className
     * @param $value
     * @throws \Exception
     * @return
     */
    public static function validate($className, $value)
    {
        $ref = new \ReflectionClass($className);
        foreach ($ref->getConstants() as $constantValue) {
            if ($constantValue === $value) {
                return $value;
            }
        }
        throw new \Exception("Value \"$value\" is not present in class $className. You should use a constant from this class.");
    }

}