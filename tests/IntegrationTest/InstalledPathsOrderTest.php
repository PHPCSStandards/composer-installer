<?php

/**
 * This file is part of the Dealerdirect PHP_CodeSniffer Standards
 * Composer Installer Plugin package.
 *
 * @copyright 2022 PHPCodeSniffer Composer Installer Contributors
 * @license MIT
 */

namespace PHPCSStandards\Composer\Plugin\Installers\PHPCodeSniffer\Tests\IntegrationTest;

use PHPCSStandards\Composer\Plugin\Installers\PHPCodeSniffer\Plugin;
use PHPCSStandards\Composer\Plugin\Installers\PHPCodeSniffer\Tests\TestCase;

/**
 * Test that the plugin always registers the installed_paths in the same order.
 *
 * This test is about Composer and the plugin, so does not need to be tested against multiple PHPCS versions.
 *
 * @link https://github.com/PHPCSStandards/composer-installer/issues/125
 * @link https://github.com/PHPCSStandards/composer-installer/pull/126
 */
final class InstalledPathsOrderTest extends TestCase
{
    private $composerConfigA = array(
        'name'              => 'phpcs-composer-installer/sort-order-test',
        'require-dev'       => array(
            'phpcs-composer-installer/dummy-subdir'  => '*',
            'phpcs-composer-installer/multistandard' => '*',
            'phpcs-composer-installer/dummy-src'     => '*',
        ),
        'minimum-stability' => 'dev',
        'prefer-stable'     => true,
    );

    private $composerConfigB = array(
        'name'              => 'phpcs-composer-installer/sort-order-test',
        'require-dev'       => array(
            'phpcs-composer-installer/multistandard' => '*',
            'phpcs-composer-installer/dummy-src'     => '*',
            'phpcs-composer-installer/dummy-subdir'  => '*',
        ),
        'minimum-stability' => 'dev',
        'prefer-stable'     => true,
    );

    /**
     * Set up test environment before each test.
     */
    protected function set_up()
    {
        $this->createTestEnvironment();
    }

    /**
     * Clean up after each test.
     */
    protected function tear_down()
    {
        $this->removeTestEnvironment();
    }

    /**
     * Test that the paths registered through the plugin are always registered in the same (sort) order.
     *
     * @return void
     */
    public function testInstalledPathsAreAlwaysRegisteredInSameOrder()
    {
        /*
         * 1. Install using ConfigA in the Composer global directory.
         */
        $this->writeComposerJsonFile($this->composerConfigA, static::$tempGlobalPath);

        // Make sure the plugin runs.
        $this->assertExecute(
            'composer global install -v --no-ansi',
            0,    // Expected exit code.
            Plugin::MESSAGE_RUNNING_INSTALLER, // Expected stdout.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        /*
         * 2. Install using ConfigB in the Composer local directory.
         */
        $this->writeComposerJsonFile($this->composerConfigB, static::$tempLocalPath);

        // Make sure the plugin runs.
        $this->assertExecute(
            sprintf('composer install -v --no-ansi --working-dir=%s', escapeshellarg(static::$tempLocalPath)),
            0,    // Expected exit code.
            Plugin::MESSAGE_RUNNING_INSTALLER, // Expected stdout.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        /*
         * 3. Retrieve the installed paths from both and compare to ensure the order is the same.
         */
        $globalPaths = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempGlobalPath);
        $this->assertSame(0, $globalPaths['exitcode'], 'Exitcode for "phpcs --config-show" did not match 0 (global)');

        $localPaths = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempLocalPath);
        $this->assertSame(0, $localPaths['exitcode'], 'Exitcode for "phpcs --config-show" did not match 0 (local)');

        // Get the installed paths setting from the config.
        $this->assertSame(
            1,
            preg_match('`installed_paths:\s+([^\n\r]+)\s+`', $globalPaths['stdout'], $matchGlobal),
            'Could not find "installed_paths" in the config-show output (global)'
        );
        $this->assertSame(
            1,
            preg_match('`installed_paths:\s+([^\n\r]+)\s+`', $localPaths['stdout'], $matchLocal),
            'Could not find "installed_paths" in the config-show output (local)'
        );

        // Remove any differences caused by global vs local paths and absolute vs relative paths.
        $matchGlobal = str_replace(array(static::$tempGlobalPath . '/vendor', '../..'), '', $matchGlobal[1]);
        $matchLocal  = str_replace(array(static::$tempLocalPath . '/vendor', '../..'), '', $matchLocal[1]);

        // Verify that the paths are registered in the same order both times.
        $this->assertSame($matchGlobal, $matchLocal, 'Order of paths in "installed_paths" is not the same');
    }
}
