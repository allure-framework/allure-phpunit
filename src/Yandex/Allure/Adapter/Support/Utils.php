<?php

namespace Yandex\Allure\Adapter\Support;


use Rhumsaa\Uuid\Uuid;

trait Utils
{

    public static function getTimestamp()
    {
        return round(microtime(true) * 1000);
    }

    public static function getUUID()
    {
        return Uuid::uuid4();
    }

}