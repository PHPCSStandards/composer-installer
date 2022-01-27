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

    /**
     * Create a composer.json file based on a given configuration.
     *
     * @param array  $config    Composer configuration as an array.
     * @param string $directory Location to write the resulting `composer.json` file to (without trailing slash).
     *
     * @return void
     *
     * @throws RuntimeException When either of the passed parameters are of the wrong data type.
     * @throws RuntimeException When the provided configuration is invalid.
     * @throws RuntimeException When the configuration could not be written to a file.
     */
    protected static function writeComposerJsonFile($config, $directory)
    {
        if (is_array($config) === false || $config === array()) {
            throw new RuntimeException('Configuration must be a non-empty array.');
        }

        if (is_string($directory) === false || $directory === '') {
            throw new RuntimeException('Directory must be a non-empty string.');
        }

        // Inject artifact for this plugin.
        if (isset($config['repositories']) === false) {
            $config['repositories'][] = array(
                'type' => 'artifact',
                'url'  => \ZIP_ARTIFACT_DIR,
            );
        }

        // Inject ability to run the plugin via a script.
        if (isset($config['scripts']['install-codestandards']) === false) {
            $config['scripts']['install-codestandards'] = array(
                'Dealerdirect\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\Plugin::run',
            );
        }

        // Inject permission for this plugin to run (Composer 2.2 compat).
        if (isset($config['config']['allow-plugins']['dealerdirect/phpcodesniffer-composer-installer']) === false) {
            $config['config']['allow-plugins']['dealerdirect/phpcodesniffer-composer-installer'] = true;
        }

        /*
         * Disable TLS when on Windows with Composer 1.x and PHP 5.4.
         * @link https://github.com/composer/composer/issues/10495
         */
        if (static::onWindows() === true && \CLI_PHP_MINOR === '5.4' && substr(\COMPOSER_VERSION, 0, 1) === '1') {
            $config['config']['disable-tls'] = true;
        }

        $encoded = json_encode($config, \JSON_UNESCAPED_SLASHES | \JSON_PRETTY_PRINT);
        if (json_last_error() !== \JSON_ERROR_NONE || $encoded === false) {
            throw new RuntimeException('Provided configuration can not be encoded to valid JSON');
        }

        $written = file_put_contents($directory . '/composer.json', $encoded);

        if ($written === false) {
            throw new RuntimeException('Failed to create the composer.json file in the temp directory for the test');
        }
    }
}
