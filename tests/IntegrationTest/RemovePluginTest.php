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
 * Test plugin does not throw errors when the plugin is still in memory, but PHPCS and the plugin are uninstalled.
 *
 * These tests verify:
 * - That the plugin exits with error code 0 when PHPCS and the plugin are uninstalled due to a no-dev install.
 * - That the plugin exits with error code 0 when the standard which required the plugin is uninstalled.
 *
 * @link https://github.com/PHPCSStandards/composer-installer/issues/103
 * @link https://github.com/PHPCSStandards/composer-installer/pull/104
 * @link https://gist.github.com/Potherca/49bdf3fb36d2c03c643c28084d74e4a7
 */
final class RemovePluginTest extends TestCase
{
    private $composerConfigRequireDev = array(
        'name'        => 'phpcs-composer-installer/remove-phpcs-test',
        'require-dev' => array(
            'phpcs-composer-installer/multistandard' => '*',
        ),
    );

    private $composerConfigRequire = array(
        'name'    => 'phpcs-composer-installer/remove-phpcs-test',
        'require' => array(
            'phpcs-composer-installer/multistandard' => '*',
        ),
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
     * Helper method to get the stdout expectation for when the plugin is uninstalled.
     *
     * As of Composer 2.0.0, plugins which have been uninstalled will not run anymore
     * after the uninstall (which is good).
     * It is unclear which particular change this can be attributed to.
     *
     * @return string
     */
    private function getUninstallStdOutExpectation()
    {
        if (strpos(\COMPOSER_VERSION, '1') === 0) {
            return Plugin::MESSAGE_PLUGIN_UNINSTALLED;
        }

        return '';
    }

    /**
     * Test the plugin doesn't exit with a non-0 exit code and doesn't throw errors when PHPCS and the plugin
     * were installed via --dev and a no-dev install is run, which removes the plugin and PHPCS.
     *
     * @return void
     */
    public function testRemovePHPCSViaNoDevGlobal()
    {
        $this->writeComposerJsonFile($this->composerConfigRequireDev, static::$tempGlobalPath);

        // Install dependencies and make sure the plugin runs.
        $this->assertExecute(
            'composer global install -v --no-ansi',
            0,    // Expected exit code.
            Plugin::MESSAGE_RUNNING_INSTALLER, // Expected stdout.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        /*
         * Switch to a no-dev install, which should remove the plugin, but the
         * plugin will still be loaded in memory, so we need to make sure it doesn't
         * throw errors.
         */
        $this->assertExecute(
            'composer global install --no-dev -v --no-ansi',
            0,    // Expected exit code.
            $this->getUninstallStdOutExpectation(), // Expected stdout.
            null, // No stderr expectation.
            'Uninstall by switching to no-dev did not meet expectations.'
        );
    }

    /**
     * Test the plugin doesn't exit with a non-0 exit code and doesn't throw errors when PHPCS and the plugin
     * were installed via --dev and a no-dev install is run, which removes the plugin and PHPCS.
     *
     * @return void
     */
    public function testRemovePHPCSViaNoDevLocal()
    {
        $this->writeComposerJsonFile($this->composerConfigRequireDev, static::$tempLocalPath);

        // Install dependencies and make sure the plugin runs.
        $this->assertExecute(
            sprintf('composer install -v --no-ansi --working-dir=%s', escapeshellarg(static::$tempLocalPath)),
            0,    // Expected exit code.
            Plugin::MESSAGE_RUNNING_INSTALLER, // Expected stdout.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        /*
         * Switch to a no-dev install, which should remove the plugin, but the
         * plugin will still be loaded in memory, so we need to make sure it exits with 0.
         */
        $this->assertExecute(
            sprintf('composer install --no-dev -v --no-ansi --working-dir=%s', escapeshellarg(static::$tempLocalPath)),
            0,    // Expected exit code.
            $this->getUninstallStdOutExpectation(), // Expected stdout.
            null, // No stderr expectation.
            'Uninstall by switching to no-dev did not meet expectations.'
        );
    }

    /**
     * Test the plugin doesn't exit with a non-0 exit code and doesn't throw errors when removing PHPCS
     * and the plugin when installed via require-dev by removing the dependency which installed it.
     *
     * @return void
     */
    public function testRemovePHPCSViaDevUninstallGlobal()
    {
        $this->writeComposerJsonFile($this->composerConfigRequireDev, static::$tempGlobalPath);

        // Install dependencies and make sure the plugin runs.
        $this->assertExecute(
            'composer global install -v --no-ansi',
            0,    // Expected exit code.
            Plugin::MESSAGE_RUNNING_INSTALLER, // Expected stdout.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        /*
         * Remove the dev dependency which caused the install of the plugin.
         * This should also remove the plugin, but the plugin will still be loaded in memory,
         * so we need to make sure it exits with 0.
         */
        $this->assertExecute(
            'composer global remove --dev phpcs-composer-installer/multistandard -v --no-ansi',
            0,    // Expected exit code.
            $this->getUninstallStdOutExpectation(), // Expected stdout.
            null, // No stderr expectation.
            'Uninstall of dev dependency did not meet expectations.'
        );
    }

    /**
     * Test the plugin doesn't exit with a non-0 exit code and doesn't throw errors when removing PHPCS
     * and the plugin when installed via require-dev by removing the dependency which installed it.
     *
     * @return void
     */
    public function testRemovePHPCSViaDevUninstallLocal()
    {
        $this->writeComposerJsonFile($this->composerConfigRequireDev, static::$tempLocalPath);

        // Install dependencies and make sure the plugin runs.
        $this->assertExecute(
            sprintf('composer install -v --no-ansi --working-dir=%s', escapeshellarg(static::$tempLocalPath)),
            0,    // Expected exit code.
            Plugin::MESSAGE_RUNNING_INSTALLER, // Expected stdout.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        /*
         * Remove the dev dependency which caused the install of the plugin.
         * This should also remove the plugin, but the plugin will still be loaded in memory,
         * so we need to make sure it exits with 0.
         */
        $command = sprintf(
            'composer remove --dev phpcs-composer-installer/multistandard -v --no-ansi --working-dir=%s',
            escapeshellarg(static::$tempLocalPath)
        );
        $this->assertExecute(
            $command,
            0,    // Expected exit code.
            $this->getUninstallStdOutExpectation(), // Expected stdout.
            null, // No stderr expectation.
            'Uninstall of dev dependency did not meet expectations.'
        );
    }

    /**
     * Test the plugin doesn't exit with a non-0 exit code and doesn't throw errors when removing PHPCS
     * and the plugin when installed via require by removing the dependency which installed it.
     *
     * @return void
     */
    public function testRemovePHPCSViaNoDevUninstallGlobal()
    {
        $this->writeComposerJsonFile($this->composerConfigRequire, static::$tempGlobalPath);

        // Install dependencies and make sure the plugin runs.
        $this->assertExecute(
            'composer global install -v --no-ansi',
            0,    // Expected exit code.
            Plugin::MESSAGE_RUNNING_INSTALLER, // Expected stdout.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        /*
         * Remove the dev dependency which caused the install of the plugin.
         * This should also remove the plugin, but the plugin will still be loaded in memory,
         * so we need to make sure it exits with 0.
         */
        $this->assertExecute(
            'composer global remove phpcs-composer-installer/multistandard -v --no-ansi',
            0,    // Expected exit code.
            $this->getUninstallStdOutExpectation(), // Expected stdout.
            null, // No stderr expectation.
            'Uninstall of no-dev dependency did not meet expectations.'
        );
    }

    /**
     * Test the plugin doesn't exit with a non-0 exit code and doesn't throw errors when removing PHPCS
     * and the plugin when installed via require by removing the dependency which installed it.
     *
     * @return void
     */
    public function testRemovePHPCSViaNoDevUninstallLocal()
    {
        $this->writeComposerJsonFile($this->composerConfigRequire, static::$tempLocalPath);

        // Install dependencies and make sure the plugin runs.
        $this->assertExecute(
            sprintf('composer install -v --no-ansi --working-dir=%s', escapeshellarg(static::$tempLocalPath)),
            0,    // Expected exit code.
            Plugin::MESSAGE_RUNNING_INSTALLER, // Expected stdout.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        /*
         * Remove the dev dependency which caused the install of the plugin.
         * This should also remove the plugin, but the plugin will still be loaded in memory,
         * so we need to make sure it exits with 0.
         */
        $command = sprintf(
            'composer remove phpcs-composer-installer/multistandard -v --no-ansi --working-dir=%s',
            escapeshellarg(static::$tempLocalPath)
        );
        $this->assertExecute(
            $command,
            0,    // Expected exit code.
            $this->getUninstallStdOutExpectation(), // Expected stdout.
            null, // No stderr expectation.
            'Uninstall of no-dev dependency did not meet expectations.'
        );
    }
}
