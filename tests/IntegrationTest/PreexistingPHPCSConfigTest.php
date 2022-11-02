<?php

/**
 * This file is part of the Dealerdirect PHP_CodeSniffer Standards
 * Composer Installer Plugin package.
 *
 * @copyright 2022 PHPCodeSniffer Composer Installer Contributors
 * @license MIT
 */

namespace PHPCSStandards\Composer\Plugin\Installers\PHPCodeSniffer\Tests\IntegrationTest;

use PHPCSStandards\Composer\Plugin\Installers\PHPCodeSniffer\Tests\PHPCSVersions;
use PHPCSStandards\Composer\Plugin\Installers\PHPCodeSniffer\Tests\TestCase;

/**
 * Test correctly handling a pre-existing PHPCS configuration file.
 *
 * These tests verify:
 * - That the plugin will add the `installed_paths` setting correctly.
 * - That the plugin does not remove or alter existing configurations, except for the `installed_paths`.
 *
 * As PHPCS behaves the same whether installed locally or globally, it is unnecessary to run the tests
 * in both type of environments.
 *
 * Note: it is necessary to run against multiple PHPCS versions however, as the output returned
 * by the `--config-show` command has changed across versions and we need to make sure that
 * the plugin handles this correctly in all supported PHPCS versions.
 *
 * @link https://github.com/PHPCSStandards/composer-installer/pull/98
 * @link https://github.com/squizlabs/PHP_CodeSniffer/wiki/Configuration-Options
 */
final class PreexistingPHPCSConfigTest extends TestCase
{
    private $composerConfig = array(
        'name'        => 'phpcs-composer-installer/preexisting-config-test',
        'require-dev' => array(
            'squizlabs/php_codesniffer'                      => null,
            'dealerdirect/phpcodesniffer-composer-installer' => '*',
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
     * Test correctly handling a pre-existing PHPCS configuration file (which doesn't involve
     * a pre-existing `installed_paths` setting).
     *
     * @dataProvider dataPHPCSVersions
     *
     * @param string $phpcsVersion  PHPCS version to use in this test.
     *                              This version is randomly selected from the PHPCS versions compatible
     *                              with the PHP version used in the test.
     *
     * @return void
     */
    public function testPreexistingNonInstalledPathsConfigIsKeptIntact($phpcsVersion)
    {
        $config = $this->composerConfig;
        $config['require-dev']['squizlabs/php_codesniffer'] = $phpcsVersion;

        $this->writeComposerJsonFile($config, static::$tempLocalPath);

        /*
         * 1. Install PHPCS and the plugin.
         */
        $this->assertExecute(
            sprintf('composer install -v --working-dir=%s', escapeshellarg(static::$tempLocalPath)),
            0,    // Expected exit code.
            null, // No stdout expectation.
            null, // No stderr expectation.
            'Failed to install PHPCS.'
        );

        // Verify the CodeSniffer.conf file does not exist to start with.
        $this->assertFileDoesNotExist(
            static::$tempLocalPath . '/vendor/squizlabs/php_codesniffer/CodeSniffer.conf'
        );

        // Verify that the config is empty to start with.
        $result = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempLocalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for "phpcs --config-show" did not match 0 (first run)');
        $this->assertMatchesRegularExpression(
            '`^\s*(Using config file:)?\s*$`',
            $result['stdout'],
            'PHPCS configuration is not empty to start with.'
        );

        /*
         * 2. Set some configs and verify they are registered correctly.
         *
         * Note: PHPCS will "show" the config settings in alphabetic order.
         * These settings have been chosen to ensure there will be settings to show
         * both before *and* after the `installed_paths` setting.
         */
        $command    = '"vendor/bin/phpcs" --config-set %s %s';
        $failureMsg = 'Exitcode for "phpcs --config-set %s %s" did not match 0';
        $settings   = array(
            'default_standard' => 'PSR12',
            'colors'           => '1',
            'show_progress'    => '1',
        );

        foreach ($settings as $name => $value) {
            $result = $this->executeCliCommand(sprintf($command, $name, $value), static::$tempLocalPath);
            $this->assertSame(0, $result['exitcode'], sprintf($failureMsg, $name, $value));
        }

        // Make sure the CodeSniffer.conf file has been created.
        $this->assertFileExists(
            static::$tempLocalPath . '/vendor/squizlabs/php_codesniffer/CodeSniffer.conf'
        );

        // Verify that the config contains the newly set values.
        $result = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempLocalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for "phpcs --config-show" did not match 0 (second run)');

        $regex = '`colors:\s+1\s+'
            . 'default_standard:\s+PSR12\s+'
            . 'show_progress:\s+1\s*$`';
        $this->assertMatchesRegularExpression(
            $regex,
            $result['stdout'],
            'PHPCS configuration does not show the newly set values correctly.'
        );

        /*
         * 3. Install an external standard.
         */
        $command = sprintf(
            'composer require --dev phpcs-composer-installer/dummy-subdir --no-ansi -v --working-dir=%s',
            escapeshellarg(static::$tempLocalPath)
        );
        $this->assertExecute(
            $command,
            0,    // Expected exit code.
            'PHP CodeSniffer Config installed_paths set to ', // Expectation for stdout.
            null, // No stderr expectation.
            'Failed to install Dummy subdir standard.'
        );

        // Verify that the originally set configs are retained and the standard is registered correctly.
        $result = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempLocalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for "phpcs --config-show" did not match 0 (third run)');

        $regex = '`colors:\s+1\s+'
            . 'default_standard:\s+PSR12\s+'
            . 'installed_paths:\s+[^\n\r]+/phpcs-composer-installer/dummy-subdir\s+'
            . 'show_progress:\s+1\s*$`';
        $this->assertMatchesRegularExpression(
            $regex,
            $result['stdout'],
            'PHPCS configuration has not been retained and/or updated correctly.'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataPHPCSVersions()
    {
        // Test against the highest and lowest supported PHPCS version for each major + `master` + PHPCS 4.x dev.
        $versions = PHPCSVersions::getHighLowEachMajor(true, true);
        return PHPCSVersions::toDataprovider($versions);
    }
}
