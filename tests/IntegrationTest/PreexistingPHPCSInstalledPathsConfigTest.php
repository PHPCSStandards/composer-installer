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
use RuntimeException;

/**
 * Test correctly handling a pre-existing PHPCS configuration file, which includes a valid `installed_paths` setting.
 *
 * These tests verify:
 * - That the plugin does not remove or alter any valid paths which already existed in `installed_paths`.
 * - That the plugin removes invalid paths which already existed in `installed_paths`.
 *
 * Note: it is important to run these tests against multiple PHPCS versions, as the output returned
 * by the `--config-show` command has changed across versions and we need to make sure that
 * the plugin handles this correctly in all supported PHPCS versions.
 */
final class PreexistingPHPCSInstalledPathsConfigTest extends TestCase
{
    private $tempExtraStndsSubdir = '/extrastnds/dummy-subdir';

    private $tempExtraStndsPath;

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

        /*
         * Create an extra directory in the test environment, but outside of the directory used
         * in the test (where Composer is run) and copy an external standard fixture to it.
         */
        $this->tempExtraStndsPath = static::$tempDir . $this->tempExtraStndsSubdir;

        // Create all needed subdirectories in one go.
        $path = $this->tempExtraStndsPath . '/DummySubDir';
        if (mkdir($path, 0766, true) === false || is_dir($path) === false) {
            throw new RuntimeException("Failed to create the $path directory for the test");
        }

        // We only need the ruleset.xml file for the standard to be valid.
        copy(
            dirname(__DIR__) . '/fixtures/dummy-subdir/DummySubDir/ruleset.xml',
            $this->tempExtraStndsPath . '/DummySubDir/ruleset.xml'
        );
    }

    /**
     * Clean up after each test.
     */
    protected function tear_down()
    {
        $this->removeTestEnvironment();
    }

    /**
     * Test correctly handling a pre-existing PHPCS configuration file which includes a pre-set,
     * valid `installed_paths`.
     *
     * @dataProvider dataPHPCSVersions
     *
     * @param string $phpcsVersion  PHPCS version to use in this test.
     *                              This version is randomly selected from the PHPCS versions compatible
     *                              with the PHP version used in the test.
     *
     * @return void
     */
    public function testPreexistingValidInstalledPathsConfigIsKeptIntact($phpcsVersion)
    {
        $config = $this->composerConfig;
        $config['require-dev']['squizlabs/php_codesniffer'] = $phpcsVersion;

        $this->writeComposerJsonFile($config, static::$tempGlobalPath);

        /*
         * 1. Install PHPCS and the plugin.
         */
        $this->assertExecute(
            'composer global install',
            0,    // Expected exit code.
            null, // No stdout expectation.
            null, // No stderr expectation.
            'Failed to install PHPCS.'
        );

        /*
         * 2. Set the installed_paths and verify it is registered correctly.
         */
        $command = sprintf(
            '"vendor/bin/phpcs" --config-set installed_paths %s',
            escapeshellarg($this->tempExtraStndsPath)
        );
        $result  = $this->executeCliCommand($command, static::$tempGlobalPath);
        $this->assertSame(
            0,
            $result['exitcode'],
            'Exitcode for "phpcs --config-set installed_paths" did not match 0'
        );

        // Verify that the config contains the newly set value.
        $result = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempGlobalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for "phpcs --config-show" did not match 0 (first run)');

        $expected = array(
            $this->tempExtraStndsPath,
        );

        $this->assertSame(
            $expected,
            $this->configShowToPathsArray($result['stdout']),
            'PHPCS configuration does not show the manually set installed_paths correctly'
        );

        /*
         * 3. Install an external standard.
         */
        $command = 'composer global require --dev phpcs-composer-installer/dummy-subdir --no-ansi -v';
        $this->assertExecute(
            $command,
            0,    // Expected exit code.
            'PHP CodeSniffer Config installed_paths set to ', // Expectation for stdout.
            null, // No stderr expectation.
            'Failed to install Dummy subdir standard.'
        );

        // Verify that the originally set path is retained and the new standard is registered correctly as well.
        $result = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempGlobalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for "phpcs --config-show" did not match 0 (second run)');


        $expected = array(
            $this->tempExtraStndsPath,
            '/phpcs-composer-installer/dummy-subdir',
        );
        sort($expected, \SORT_NATURAL);

        $this->assertSame(
            $expected,
            $this->configShowToPathsArray($result['stdout']),
            'Paths as updated by the plugin does not contain the expected paths'
        );
    }

    /**
     * Test correctly handling a pre-existing PHPCS configuration file which includes both
     * a pre-set, valid path, as well as an invalid path in `installed_paths`.
     *
     * @dataProvider dataPHPCSVersions
     *
     * @param string $phpcsVersion  PHPCS version to use in this test.
     *                              This version is randomly selected from the PHPCS versions compatible
     *                              with the PHP version used in the test.
     *
     * @return void
     */
    public function testPreexistingInvalidInstalledPathsConfigIsRemoved($phpcsVersion)
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

        /*
         * 2. Set the installed_paths and verify it is registered correctly.
         */
        $command = sprintf(
            '"vendor/bin/phpcs" --config-set installed_paths %s',
            escapeshellarg($this->tempExtraStndsPath)
        );
        $result  = $this->executeCliCommand($command, static::$tempLocalPath);
        $this->assertSame(
            0,
            $result['exitcode'],
            'Exitcode for "phpcs --config-set installed_paths" did not match 0'
        );

        /*
         * Manipulate the value of installed_paths as registered in PHPCS.
         *
         * Note: for the test we do this "manually". In real life, this may be a standard which
         * used to be installed, but was removed without the installed_paths having been updated
         * in PHPCS (prior to the plugin being used).
         *
         * Also note: depending on the OS and the PHP version, passing an invalid path to `--config-set`
         * will error on an exception from the DirectoryIterator as used by PHPCS itself.
         * The manual setting prevents this exception, but still allows us to test this use-case.
         */
        $confFile     = static::$tempLocalPath . '/vendor/squizlabs/php_codesniffer/CodeSniffer.conf';
        $confContents = file_get_contents($confFile);
        $this->assertNotFalse($confContents);
        $confContents = str_replace(
            $this->tempExtraStndsSubdir,
            $this->tempExtraStndsSubdir . ',path/to/somecloned-stnd',
            $confContents
        );
        $this->assertNotFalse(file_put_contents($confFile, $confContents));

        // Verify that the config contains the newly set value.
        $result = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempLocalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for "phpcs --config-show" did not match 0 (first run)');

        $expected = array(
            $this->tempExtraStndsPath,
            'path/to/somecloned-stnd',
        );
        sort($expected, \SORT_NATURAL);

        $this->assertSame(
            $expected,
            $this->configShowToPathsArray($result['stdout']),
            'PHPCS configuration does not show the manually set installed_paths correctly'
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

        /*
         * Verify that the valid preset path is retained, that the invalid path is removed
         * and the new standard is registered correctly.
         */
        $result = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempLocalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for "phpcs --config-show" did not match 0 (second run)');

        $expected = array(
            $this->tempExtraStndsPath,
            '/phpcs-composer-installer/dummy-subdir',
        );
        sort($expected, \SORT_NATURAL);

        $this->assertSame(
            $expected,
            $this->configShowToPathsArray($result['stdout']),
            'Paths as updated by the plugin does not contain the expected paths'
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
