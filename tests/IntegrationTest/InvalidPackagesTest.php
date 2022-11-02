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
 * Test that the plugin does not act on packages which are not valid PHPCS standards.
 *
 * Valid PHPCS standards for the purposes of this plugin, are packages which:
 * - have the `phpcodesniffer-standard` type set in their `composer.json` file.
 * - contain at least one `ruleset.xml` file.
 *
 * This test is about Composer and the plugin, so does not need to be tested against multiple PHPCS versions.
 * The behaviour also shouldn't differ between a global vs local Composer install, so only testing one type.
 */
final class InvalidPackagesTest extends TestCase
{
    private $composerConfigNoRuleset = array(
        'name'        => 'phpcs-composer-installer/invalid-package-no-ruleset-test',
        'require-dev' => array(
            'squizlabs/php_codesniffer'           => '*',
            'phpcs-composer-installer/no-ruleset' => '*',
        ),
    );

    private $composerConfigIncorrectType = array(
        'name'        => 'phpcs-composer-installer/invalid-package-incorrect-type-test',
        'require-dev' => array(
            'squizlabs/php_codesniffer'               => '*',
            'phpcs-composer-installer/incorrect-type' => '*',
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
     * Test that the plugin does not set the installed_paths for invalid external PHPCS standards.
     *
     * @dataProvider dataInvalidPackages
     *
     * @param array  $config       The Composer configuration to use.
     * @param string $standardName The name of the PHPCS standard which is expected to NOT be registered.
     *
     * @return void
     */
    public function testDontSetInstalledPathsForInvalidPackages($config, $standardName)
    {
        $this->writeComposerJsonFile($config, static::$tempLocalPath);

        // Make sure the plugin runs.
        $this->assertExecute(
            sprintf('composer install -v --no-ansi --working-dir=%s', escapeshellarg(static::$tempLocalPath)),
            0,    // Expected exit code.
            Plugin::MESSAGE_RUNNING_INSTALLER, // Expected stdout.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        // Make sure the CodeSniffer.conf file does not get created when no (valid) external standards are found.
        $this->assertFileDoesNotExist(
            static::$tempLocalPath . '/vendor/squizlabs/php_codesniffer/CodeSniffer.conf'
        );

        // Make sure that the standard does not show up as registered with PHPCS.
        $result = $this->executeCliCommand('"vendor/bin/phpcs" -i', static::$tempLocalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for phpcs -i did not match 0');
        $this->assertStringNotContainsString(
            $standardName,
            $result['stdout'],
            'Invalid standard registered.'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataInvalidPackages()
    {
        return array(
            'Composer package without ruleset file' => array(
                'configName'   => $this->composerConfigNoRuleset,
                'standardName' => 'NoRuleset',
            ),
            'Composer package with incorrect type' => array(
                'configName'   => $this->composerConfigIncorrectType,
                'standardName' => 'IncorrectType',
            ),
        );
    }
}
