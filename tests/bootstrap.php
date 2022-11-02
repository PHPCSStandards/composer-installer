<?php

/**
 * This file is part of the Dealerdirect PHP_CodeSniffer Standards
 * Composer Installer Plugin package.
 *
 * @copyright 2022 PHPCodeSniffer Composer Installer Contributors
 * @license MIT
 */

namespace PHPCSStandards\Composer\Plugin\Installers\PHPCodeSniffer\Tests;

/*
 * Make sure the tests always start with a clean slate.
 */
$tempDir = sys_get_temp_dir() . '/PHPCSPluginTest';
if (file_exists($tempDir) === true) {
    if (stripos(\PHP_OS, 'WIN') === 0) {
        // Windows.
        shell_exec(sprintf('rd /s /q %s', escapeshellarg($tempDir)));
    } else {
        shell_exec(sprintf('rm -rf %s', escapeshellarg($tempDir)));
    }
}

if (is_dir(dirname(__DIR__) . '/vendor') && file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    $vendorDir = dirname(__DIR__) . '/vendor';
} else {
    echo 'Please run `composer install` before attempting to run the unit tests.
You can still run the tests using a PHPUnit phar file, but some test dependencies need to be available.
';
    die(1);
}

/*
 * Set up autoloading.
 */
if (\defined('__PHPUNIT_PHAR__')) {
    // Testing via a PHPUnit phar.

    // Load the PHPUnit Polyfills autoloader.
    require_once $vendorDir . '/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';

    /*
     * Autoloader specifically for the test files.
     * This allows for the tests to be run via a Phar.
     */
    \spl_autoload_register(function ($className) {
        // Only try & load our own classes.
        if (\stripos($className, __NAMESPACE__) !== 0) {
            return;
        }

        // Strip namespace prefix.
        $relativeClass = \substr($className, 61);
        $file          = \realpath(__DIR__) . \DIRECTORY_SEPARATOR
            . \strtr($relativeClass, '\\', \DIRECTORY_SEPARATOR) . '.php';

        if (\file_exists($file)) {
            include_once $file;
        }
    });
} else {
    // Testing via a Composer setup.
    require_once $vendorDir . '/autoload.php';
}

/*
 * Create Zip artifacts of the plugin itself as well as the test fixtures.
 */
define('ZIP_ARTIFACT_DIR', __DIR__ . '/artifact/');

if (extension_loaded('zip') === true) {
    define('PLUGIN_ARTIFACT_VERSION', '1.0.0');

    $zipCreator = new CreateComposerZipArtifacts(\ZIP_ARTIFACT_DIR);
    $zipCreator->clearOldArtifacts();
    $zipCreator->createPluginArtifact(dirname(__DIR__), \PLUGIN_ARTIFACT_VERSION);
    $zipCreator->createFixtureArtifacts(__DIR__ . '/fixtures/');
    unset($zipCreator);
} else {
    echo 'Please enable the zip extension before running the tests.';
    die(1);
}

/*
 * Set a few constants for use throughout the tests.
 */

define('CLI_PHP_MINOR', substr(\PHP_VERSION, 0, strpos(\PHP_VERSION, '.', 2)));

if (\getenv('COMPOSER_PHAR') !== false) {
    define('COMPOSER_PHAR', getenv('COMPOSER_PHAR'));
} elseif (strpos(strtoupper(\PHP_OS), 'WIN') === 0) {
    // Windows.
    exec('where composer.phar', $output, $exitcode);
    if ($exitcode === 0 && empty($output) === false) {
        define('COMPOSER_PHAR', trim(implode('', $output)));
    }
} else {
    exec('which composer.phar', $output, $exitcode);
    if ($exitcode === 0 && empty($output) === false) {
        define('COMPOSER_PHAR', trim(implode('', $output)));
    }
}

if (defined('COMPOSER_PHAR') === false) {
    echo 'Please add a <php><env name="COMPOSER_PHAR" value="..."></php> configuration to your local phpunit.xml'
        . ' overload file before running the tests.' . \PHP_EOL
        . 'The value should point to the local Composer phar file you want to use for the tests.';
    die(1);
}

// Get the version of Composer being used.
$command  = '"' . \PHP_BINARY . '" "' . \COMPOSER_PHAR . '" --version --no-ansi --no-interaction';
$lastLine = exec($command, $output, $exitcode);
if ($exitcode === 0 && preg_match('`Composer (?:version )?([^\s]+)`', $lastLine, $matches) === 1) {
    define('COMPOSER_VERSION', $matches[1]);
} else {
    echo 'Could not determine the version of Composer being used.';
    die(1);
}
