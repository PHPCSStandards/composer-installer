<?php

/**
 * This file is part of the Dealerdirect PHP_CodeSniffer Standards
 * Composer Installer Plugin package.
 *
 * @copyright 2022 PHPCodeSniffer Composer Installer Contributors
 * @license MIT
 */

namespace Dealerdirect\Composer\Plugin\Installers\PHPCodeSniffer\Tests;

use RuntimeException;
use Yoast\PHPUnitPolyfills\TestCases\TestCase as PolyfillTestCase;

abstract class TestCase extends PolyfillTestCase
{
    protected static $tempDir;

    protected static $tempGlobalPath;

    protected static $tempLocalPath;


    /* ***** SETUP AND TEARDOWN HELPERS ***** */

    public static function createTestEnvironment()
    {
        // Make temp directory
        $class           = substr(strrchr(get_called_class(), '\\'), 1);
        static::$tempDir = sys_get_temp_dir() . '/PHPCSPluginTest/' . uniqid("{$class}_", true);

        $subDirs = array(
            'tempLocalPath'  => 'local',
            'tempGlobalPath' => 'global',
        );

        foreach ($subDirs as $property => $subDir) {
            $path = static::$tempDir . '/' . $subDir;
            if (mkdir($path, 0766, true) === false || is_dir($path) === false) {
                throw new RuntimeException("Failed to create the $path directory for the test");
            }

            static::${$property} = $path;
        }

        putenv('COMPOSER_HOME=' . static::$tempGlobalPath);
    }

    public static function removeTestEnvironment()
    {
        if (file_exists(static::$tempDir) === true) {
            // Remove temp directory, including all files.
            if (static::onWindows() === true) {
                // Windows.
                exec(sprintf('rd /s /q %s', escapeshellarg(static::$tempDir)), $output, $exitCode);
            } else {
                exec(sprintf('rm -rf %s', escapeshellarg(static::$tempDir)), $output, $exitCode);
            }

            if ($exitCode !== 0) {
                throw new RuntimeException(
                    'Failed to remove the temp directory created for the test: ' . \PHP_EOL . 'Error: ' . $output
                );
            }

            clearstatcache();
        }

        putenv('COMPOSER_HOME');
    }


    /* ***** HELPER METHODS ***** */

    /**
     * Determine whether or not the tests are being run on Windows.
     *
     * @return bool
     */
    protected static function onWindows()
    {
        return strpos(strtoupper(\PHP_OS), 'WIN') === 0;
    }
}
