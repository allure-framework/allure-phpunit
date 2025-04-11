<?php declare(strict_types=1);

namespace Qameta\Allure\PHPUnit\Scripts;

final class CiScripts
{
    /**
     * Prints the version of the PHPUnit XML schema.
     * We use this in the CI pipeline to deside which configuration file to use.
     */
    public static function printConfigSchemaVersion(): void
    {
        if (class_exists(\PHPUnit\Runner\Version::class)) {
            echo(preg_replace('/^(\d+\.\d+)\..*$/', '$1', \PHPUnit\Runner\Version::id()));
        }
    }
}
