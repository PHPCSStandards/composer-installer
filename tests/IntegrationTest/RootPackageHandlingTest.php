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
 * Test that the plugin correctly registers standards found in the root package if it is an external standard,
 * but doesn't act on root packages which do not have the correct "type".
 *
 * This test is about Composer and the plugin, so does not need to be tested against multiple PHPCS versions.
 *
 * @link https://github.com/PHPCSStandards/composer-installer/issues/19
 * @link https://github.com/PHPCSStandards/composer-installer/pull/21
 * @link https://github.com/PHPCSStandards/composer-installer/issues/20
 * @link https://github.com/PHPCSStandards/composer-installer/pull/25
 * @link https://github.com/PHPCSStandards/composer-installer/issues/32
 */
final class RootPackageHandlingTest extends TestCase
{
    /**
     * Set up test environment.
     */
    protected function set_up()
    {
        $this->createTestEnvironment();
    }

    /**
     * Clean up.
     */
    protected function tear_down()
    {
        $this->removeTestEnvironment();
    }

    /**
     * Test that the plugin registers a standard found in the root package.
     *
     * @return void
     */
    public function testSetInstalledPathsWhenRootPackageIsExternalStandard()
    {
        /*
         * Copy one of the fixtures to the test directory.
         */
        $this->recursiveDirectoryCopy(dirname(__DIR__) . '/fixtures/multistandard/', static::$tempLocalPath);

        // Install the dependencies, including the plugin.
        $command = sprintf('composer install -v --no-ansi --working-dir=%s', escapeshellarg(static::$tempLocalPath));
        $this->assertExecute(
            $command,
            0,    // Expected exit code.
            Plugin::MESSAGE_RUNNING_INSTALLER, // Expected stdout.
            null, // No stderr expectation.
            'Failed to install the plugin.'
        );

        // Verify that the root package standard is registered correctly.
        $result = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempLocalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for "phpcs --config-show" did not match 0 (first run)');

        $this->assertSame(
            1,
            preg_match('`installed_paths:\s+([^\n\r]+)\s+`', $result['stdout'], $matches),
            'Could not find the installed paths in the config'
        );

        // Work around differences in paths being returned between *nix and Windows.
        $hasExpectedPath = false;
        if ($matches[1] === '../../../') { // Most common.
            $hasExpectedPath = true;
        } else {
            $needle = str_replace(sys_get_temp_dir(), '', static::$tempLocalPath);
            if (substr_compare($matches[1], $needle, -\strlen($needle)) === 0) {
                $hasExpectedPath = true;
            }
        }

        $this->assertTrue($hasExpectedPath, $matches[1], 'PHPCS configuration does not contain the root standard');
    }

    /**
     * Test that the plugin does not act on root packages which aren't valid PHPCS external standards
     * for the purposes of this plugin.
     *
     * @dataProvider dataInvalidRootPackages
     *
     * @param string $srcDir The path to the fixture to use as the root package.
     *
     * @return void
     */
    public function testDontSetInstalledPathsForInvalidPackages($srcDir)
    {
        // Copy the fixture to the test directory.
        $this->recursiveDirectoryCopy($srcDir, static::$tempLocalPath);

        // Install the dependencies, including the plugin.
        $command = sprintf('composer install -v --no-ansi --working-dir=%s', escapeshellarg(static::$tempLocalPath));
        $this->assertExecute(
            $command,
            0,    // Expected exit code.
            Plugin::MESSAGE_RUNNING_INSTALLER, // Expected stdout.
            null, // No stderr expectation.
            'Failed to install the plugin.'
        );

        // Verify that the root package is not registered as a standard.
        $result = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempLocalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for "phpcs --config-show" did not match 0 (first run)');

        // As the fixture doesn't contain other external standards, the installed_paths config should not exist.
        $this->assertStringNotContainsString(
            'installed_paths:',
            $result['stdout'],
            'Root package registered as an external standard with PHPCS, while it shouldn\'t be'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataInvalidRootPackages()
    {
        return array(
            'Root package without ruleset file' => array(
                'srcDir' => dirname(__DIR__) . '/fixtures/no-ruleset/',
            ),
            'Root package with incorrect type' => array(
                'srcDir' => dirname(__DIR__) . '/fixtures/incorrect-type/',
            ),
        );
    }
}
