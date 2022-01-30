<?php

/**
 * This file is part of the Dealerdirect PHP_CodeSniffer Standards
 * Composer Installer Plugin package.
 *
 * @copyright 2022 PHPCodeSniffer Composer Installer Contributors
 * @license MIT
 */

namespace Dealerdirect\Composer\Plugin\Installers\PHPCodeSniffer\Tests\IntegrationTest;

use Dealerdirect\Composer\Plugin\Installers\PHPCodeSniffer\Tests\PHPCSVersions;
use Dealerdirect\Composer\Plugin\Installers\PHPCodeSniffer\Tests\TestCase;

/**
 * Test registering external standards.
 */
final class RegisterExternalStandardsTest extends TestCase
{
    private $configOneStandard = array(
        'name'        => 'phpcs-composer-installer/register-external-stnds-one-stnd',
        'require-dev' => array(
            'squizlabs/php_codesniffer'                      => null,
            'phpcs-composer-installer/dummy-subdir'          => '*',
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
     * Test registering one external standard for a Composer GLOBAL install.
     *
     * @dataProvider dataRegisterOneStandard
     *
     * @param string $phpcsVersion PHPCS version to use in this test.
     *                             This version is randomly selected from the PHPCS versions compatible
     *                             with the PHP version used in the test.
     *
     * @return void
     */
    public function testRegisterOneStandardGlobal($phpcsVersion)
    {
        $config                                             = $this->configOneStandard;
        $config['require-dev']['squizlabs/php_codesniffer'] = $phpcsVersion;

        $this->writeComposerJsonFile($config, static::$tempGlobalPath);
        $this->assertComposerValidates(static::$tempGlobalPath);

        // Install the dependencies.
        $this->assertExecute(
            'composer global install --no-plugins',
            0,    // Expected exit code.
            null, // No stdout expectation.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        // Verify that the standard registers correctly.
        $installResult = $this->executeCliCommand('composer global install-codestandards --no-ansi');
        $this->assertSame(0, $installResult['exitcode'], 'Exitcode for install-codestandards did not match 0');

        $this->assertMatchesRegularExpression(
            '`^PHP CodeSniffer Config installed_paths set to [^\s]+/dummy-subdir$`',
            trim($installResult['stdout']),
            'Installing the standards failed.'
        );

        // Make sure the CodeSniffer.conf file has been created.
        $this->assertFileExists(
            static::$tempGlobalPath . '/vendor/squizlabs/php_codesniffer/CodeSniffer.conf'
        );

        // Verify that PHPCS sees the external standard.
        $this->assertExecute(
            '"vendor/bin/phpcs" -i',
            0,                 // Expected exit code.
            'and DummySubDir', // Expected stdout.
            '',                // Empty stderr expectation.
            'Running phpcs -i failed.',
            static::$tempGlobalPath
        );

        // Make sure there is a PHP file to scan.
        $this->createFile(static::$tempGlobalPath . '/test.php');

        // Verify that PHPCS can run with the external standard.
        $phpcsCommand = '"vendor/bin/phpcs" -psl . --standard=DummySubDir';
        $phpcsResult  = $this->executeCliCommand($phpcsCommand, static::$tempGlobalPath);

        $this->assertSame(0, $phpcsResult['exitcode'], 'Exitcode for PHPCS scan did not match 0');
        $this->assertMatchesRegularExpression(
            // PHPCS 3.x added the "1/1 (100%)" annotation, the new line (+timing) was added early in the 2.x cycle.
            '`^\.(?: 1 / 1 \(100%\))?(?:' . \PHP_EOL . '|$)`',
            // Progress reporting moved from stdout to stderr in PHPCS 4.x.
            ($phpcsVersion[0] !== '4') ? trim($phpcsResult['stdout']) : trim($phpcsResult['stderr']),
            'Scanning the directory with PHPCS failed.'
        );
    }

    /**
     * Test registering one external standard for a Composer LOCAL install.
     *
     * @dataProvider dataRegisterOneStandard
     *
     * @param string $phpcsVersion PHPCS version to use in this test.
     *                             This version is randomly selected from the PHPCS versions compatible
     *                             with the PHP version used in the test.
     *
     * @return void
     */
    public function testRegisterOneStandardLocal($phpcsVersion)
    {
        $config                                             = $this->configOneStandard;
        $config['require-dev']['squizlabs/php_codesniffer'] = $phpcsVersion;

        $this->writeComposerJsonFile($config, static::$tempLocalPath);
        $this->assertComposerValidates(static::$tempLocalPath);

        // Install the dependencies.
        $this->assertExecute(
            sprintf('composer install --no-plugins --working-dir=%s', escapeshellarg(static::$tempLocalPath)),
            0,    // Expected exit code.
            null, // No stdout expectation.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        // Verify that the standard registers correctly.
        $installCommand = sprintf(
            'composer install-codestandards --no-ansi --working-dir=%s',
            escapeshellarg(static::$tempLocalPath)
        );
        $installResult  = $this->executeCliCommand($installCommand);
        $this->assertSame(0, $installResult['exitcode'], 'Exitcode for install-codestandards did not match 0');

        $this->assertMatchesRegularExpression(
            '`^PHP CodeSniffer Config installed_paths set to [^\s]+/dummy-subdir$`',
            trim($installResult['stdout']),
            'Installing the standards failed.'
        );

        // Make sure the CodeSniffer.conf file has been created.
        $this->assertFileExists(
            static::$tempLocalPath . '/vendor/squizlabs/php_codesniffer/CodeSniffer.conf'
        );

        // Verify that PHPCS sees the external standard.
        $this->assertExecute(
            '"vendor/bin/phpcs" -i',
            0,                 // Expected exit code.
            'and DummySubDir', // Expected stdout.
            '',                // Empty stderr expectation.
            'Running phpcs -i failed.',
            static::$tempLocalPath
        );

        // Make sure there is a PHP file to scan.
        $this->createFile(static::$tempLocalPath . '/test.php');

        // Verify that PHPCS can run with the external standard.
        $phpcsCommand = '"vendor/bin/phpcs" -psl . --standard=DummySubDir';
        $phpcsResult  = $this->executeCliCommand($phpcsCommand, static::$tempLocalPath);

        $this->assertSame(0, $phpcsResult['exitcode'], 'Exitcode for PHPCS scan did not match 0');
        $this->assertMatchesRegularExpression(
            // PHPCS 3.x added the "1/1 (100%)" annotation, the new line (+timing) was added early in the 2.x cycle.
            '`^\.(?: 1 / 1 \(100%\))?(?:' . \PHP_EOL . '|$)`',
            // Progress reporting moved from stdout to stderr in PHPCS 4.x.
            ($phpcsVersion[0] !== '4') ? ltrim($phpcsResult['stdout']) : ltrim($phpcsResult['stderr']),
            'Scanning the directory with PHPCS failed.'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataRegisterOneStandard()
    {
        // Get two PHPCS versions suitable for this PHP version + `master` + PHPCS 4.x dev.
        $versions = PHPCSVersions::get(2, true, true);
        return PHPCSVersions::toDataprovider($versions);
    }
}
