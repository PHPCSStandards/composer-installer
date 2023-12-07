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
use PHPCSStandards\Composer\Plugin\Installers\PHPCodeSniffer\Tests\PHPCSVersions;
use PHPCSStandards\Composer\Plugin\Installers\PHPCodeSniffer\Tests\TestCase;

/**
 * Test baseline.
 *
 * These tests verify:
 * - That the plugin can be installed and functions correctly with the full range of supported PHPCS versions.
 *   While the test is not run against the full range of supported PHPCS version each time, due to the
 *   random PHPCS version selection, all supported PHPCS versions will be tested over time in CI.
 * - That the plugin runs when installed.
 * - That the PHPCS native standards are the only recognized standards when no external standards are available.
 * - That no `CodeSniffer.conf``file gets created when no external standards are found.
 */
final class BaseLineTest extends TestCase
{
    private $composerConfig = array(
        'name'        => 'phpcs-composer-installer/baseline-test',
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
     * Baseline test for a Composer GLOBAL install.
     *
     * @dataProvider dataBaseLine
     *
     * @param string $phpcsVersion  PHPCS version to use in this test.
     *                              This version is randomly selected from the PHPCS versions compatible
     *                              with the PHP version used in the test.
     * @param array  $expectedStnds List of the standards which are expected to be registered.
     *
     * @return void
     */
    public function testBaseLineGlobal($phpcsVersion, $expectedStnds)
    {
        $config = $this->composerConfig;
        $config['require-dev']['squizlabs/php_codesniffer'] = $phpcsVersion;

        $this->writeComposerJsonFile($config, static::$tempGlobalPath);
        $this->assertComposerValidates(static::$tempGlobalPath);

        // Make sure the plugin runs.
        $this->assertExecute(
            'composer global install -v --no-ansi',
            0,    // Expected exit code.
            Plugin::MESSAGE_RUNNING_INSTALLER, // Expected stdout.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        $result = $this->executeCliCommand('"vendor/bin/phpcs" -i', static::$tempGlobalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for phpcs -i did not match 0');
        $this->assertSame(
            $expectedStnds,
            $this->standardsPhraseToArray($result['stdout']),
            'Installed standards do not match the expected standards.'
        );

        // Make sure the CodeSniffer.conf file does not get created when no external standards are found.
        $this->assertFileDoesNotExist(
            static::$tempGlobalPath . '/vendor/squizlabs/php_codesniffer/CodeSniffer.conf'
        );
    }

    /**
     * Baseline test for a Composer LOCAL install.
     *
     * @dataProvider dataBaseLine
     *
     * @param string $phpcsVersion  PHPCS version to use in this test.
     *                              This version is randomly selected from the PHPCS versions compatible
     *                              with the PHP version used in the test.
     * @param array  $expectedStnds List of the standards which are expected to be registered.
     *
     * @return void
     */
    public function testBaseLineLocal($phpcsVersion, $expectedStnds)
    {
        if (
            $phpcsVersion === PHPCSVersions::MASTER
            && \CLI_PHP_MINOR === '5.5'
            && $this->onWindows() === true
            && substr(\COMPOSER_VERSION, 0, 1) === '1'
        ) {
            $this->markTestSkipped(
                'Composer 1.x on Windows with PHP 5.5 does run the plugin when there are no external standards,'
                . ' but doesn\'t consistently show this in the logs'
            );
        }

        $config = $this->composerConfig;
        $config['require-dev']['squizlabs/php_codesniffer'] = $phpcsVersion;

        $this->writeComposerJsonFile($config, static::$tempLocalPath);
        $this->assertComposerValidates(static::$tempLocalPath);

        // Make sure the plugin runs.
        $this->assertExecute(
            sprintf('composer install -v --no-ansi --working-dir=%s', escapeshellarg(static::$tempLocalPath)),
            0,    // Expected exit code.
            Plugin::MESSAGE_RUNNING_INSTALLER, // Expected stdout.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        $result = $this->executeCliCommand('"vendor/bin/phpcs" -i', static::$tempLocalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for phpcs -i did not match 0');
        $this->assertSame(
            $expectedStnds,
            $this->standardsPhraseToArray($result['stdout']),
            'Installed standards do not match the expected standards.'
        );

        // Make sure the CodeSniffer.conf file does not get created when no external standards are found.
        $this->assertFileDoesNotExist(
            static::$tempLocalPath . '/vendor/squizlabs/php_codesniffer/CodeSniffer.conf'
        );
    }

    /**
     * Data provider.
     *
     * Note: PHPCS does not display the names of the standards in a fixed order, so the order in which standards
     * get displayed may differ depending on the machine/OS on which the tests get run.
     * With that in mind, the verification that the PHPCS native standards are the only recognized standards
     * is done using a regex instead of an exact match.
     * Also see: https://github.com/squizlabs/PHP_CodeSniffer/pull/3539
     *
     * @return array
     */
    public function dataBaseLine()
    {
        // Get two PHPCS versions suitable for this PHP version + `master` + PHPCS 4.x dev.
        $versions = PHPCSVersions::get(2, true, true);

        $data = array();
        foreach ($versions as $version) {
            $data["phpcs $version"] = array(
                'phpcsVersion'  => $version,
                'expectedStnds' => PHPCSVersions::getStandards($version),
            );
        }

        return $data;
    }
}
