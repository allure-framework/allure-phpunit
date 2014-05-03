<?php

namespace Yandex\Allure\Adapter\Model;

/**
 * Step status
 * @package Yandex\Allure\Adapter\Model
 */
final class Status
{
    const FAILED = 'failed';
    const BROKEN = 'broken';
    const PASSED = 'passed';
    const SKIPPED = 'skipped';
}