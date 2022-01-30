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
            'phpcompatibility/php-compatibility'             => null,
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
     * @param string $phpcsVersion     PHPCS version to use in this test.
     *                                 This version is randomly selected from the PHPCS versions compatible
     *                                 with the PHP version used in the test.
     * @param string $phpcompatVersion PHPCompatibility version to use in this test.
     * @param string $sniff            Name of the sniff to use for a PHPCS test run.
     *
     * @return void
     */
    public function testRegisterOneStandardGlobal($phpcsVersion, $phpcompatVersion, $sniff)
    {
        $config                                                      = $this->configOneStandard;
        $config['require-dev']['squizlabs/php_codesniffer']          = $phpcsVersion;
        $config['require-dev']['phpcompatibility/php-compatibility'] = $phpcompatVersion;

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

        /*
         * In PHPCompatibility 7.x, the directory layout was not yet compatible with a Composer install,
         * so we need to "move" the directory to make it compatible.
         * Note: PHPCompatibility >= 8 has a conflict setting with PHPCS 2.6.2, which means that
         * PHPCompatibility 7.x would be used in that case.
         */
        if ($phpcompatVersion === '^7.0' || $phpcsVersion === '2.6.2') {
            $moveCommand = sprintf(
                '%1$s %2$s/vendor/phpcompatibility/php-compatibility %2$s/vendor/phpcompatibility/PHPCompatibility',
                (\DIRECTORY_SEPARATOR === '\\') ? 'move' : 'mv',
                escapeshellarg(static::$tempGlobalPath)
            );

            $this->assertExecute(
                $moveCommand,
                0,    // Expected exit code.
                null, // No stdout expectation.
                '',   // Empty stderr expectation.
                'Moving the PHPCompatibility directory failed.'
            );
        }

        // Verify that the standard registers correctly.
        $installResult = $this->executeCliCommand('composer global install-codestandards --no-ansi');
        $this->assertSame(0, $installResult['exitcode'], 'Exitcode for install-codestandards did not match 0');

        $regex = sprintf(
            '`^PHP CodeSniffer Config installed_paths set to '
            . '[^\s]+/phpcompatibility%s$`',
            ($phpcompatVersion === '^7.0' || $phpcsVersion === '2.6.2') ? '' : '/(?:php-|PHP)compatibility'
        );
        $this->assertMatchesRegularExpression(
            $regex,
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
            0,                      // Expected exit code.
            'and PHPCompatibility', // Expected stdout.
            '',                     // Empty stderr expectation.
            'Running phpcs -i failed.',
            static::$tempGlobalPath
        );

        // Make sure there is a PHP file to scan.
        $this->createFile(static::$tempGlobalPath . '/test.php');

        // Verify that PHPCS can run with the external standard.
        $phpcsCommand = sprintf(
            '"vendor/bin/phpcs" -psl . --standard=PHPCompatibility --sniffs=%s --runtime-set testVersion %s',
            $sniff,
            \CLI_PHP_MINOR
        );
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
     * @param string $phpcsVersion     PHPCS version to use in this test.
     *                                 This version is randomly selected from the PHPCS versions compatible
     *                                 with the PHP version used in the test.
     * @param string $phpcompatVersion PHPCompatibility version to use in this test.
     * @param string $sniff            Name of the sniff to use for a PHPCS test run.
     *
     * @return void
     */
    public function testRegisterOneStandardLocal($phpcsVersion, $phpcompatVersion, $sniff)
    {
        $config                                                      = $this->configOneStandard;
        $config['require-dev']['squizlabs/php_codesniffer']          = $phpcsVersion;
        $config['require-dev']['phpcompatibility/php-compatibility'] = $phpcompatVersion;

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

        /*
         * In PHPCompatibility 7.x, the directory layout was not yet compatible with a Composer install,
         * so we need to "move" the directory to make it compatible.
         * Note: PHPCompatibility >= 8 has a conflict setting with PHPCS 2.6.2, which means that
         * PHPCompatibility 7.x would be used in that case.
         */
        if ($phpcompatVersion === '^7.0' || $phpcsVersion === '2.6.2') {
            $moveCommand = sprintf(
                '%1$s %2$s/vendor/phpcompatibility/php-compatibility %2$s/vendor/phpcompatibility/PHPCompatibility',
                (\DIRECTORY_SEPARATOR === '\\') ? 'move' : 'mv',
                escapeshellarg(static::$tempLocalPath)
            );

            $this->assertExecute(
                $moveCommand,
                0,    // Expected exit code.
                null, // No stdout expectation.
                '',   // Empty stderr expectation.
                'Moving the PHPCompatibility directory failed.'
            );
        }

        // Verify that the standard registers correctly.
        $installCommand = sprintf(
            'composer install-codestandards --no-ansi --working-dir=%s',
            escapeshellarg(static::$tempLocalPath)
        );
        $installResult  = $this->executeCliCommand($installCommand);
        $this->assertSame(0, $installResult['exitcode'], 'Exitcode for install-codestandards did not match 0');

        $regex = sprintf(
            '`^PHP CodeSniffer Config installed_paths set to '
            . '[^\s]+/phpcompatibility%s$`',
            ($phpcompatVersion === '^7.0' || $phpcsVersion === '2.6.2') ? '' : '/(?:php-|PHP)compatibility'
        );
        $this->assertMatchesRegularExpression(
            $regex,
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
            0,                      // Expected exit code.
            'and PHPCompatibility', // Expected stdout.
            '',                     // Empty stderr expectation.
            'Running phpcs -i failed.',
            static::$tempLocalPath
        );

        // Make sure there is a PHP file to scan.
        $this->createFile(static::$tempLocalPath . '/test.php');

        // Verify that PHPCS can run with the external standard.
        $phpcsCommand = sprintf(
            '"vendor/bin/phpcs" -psl . --standard=PHPCompatibility --sniffs=%s --runtime-set testVersion %s',
            $sniff,
            \CLI_PHP_MINOR
        );
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

        $data = array();
        foreach ($versions as $phpcs) {
            switch (true) {
                case $phpcs === PHPCSVersions::MASTER:
                case $phpcs === PHPCSVersions::NEXT_MAJOR:
                default:
                    $phpcompat = '*';
                    $sniff     = 'PHPCompatibility.FunctionUse.RemovedFunctions';
                    break;

                case version_compare($phpcs, '2.2.0', '<'):
                    // PHPCompatibility 7.x is the last version supporting PHPCS < 2.2.0.
                    $phpcompat = '^7.0';
                    $sniff     = 'PHPCompatibility.PHP.DeprecatedFunctions';
                    break;

                case version_compare($phpcs, '2.3.0', '<'):
                    // PHPCompatibility 8.x is the last version supporting PHPCS < 2.3.0.
                    $phpcompat = '^8.0';
                    $sniff     = 'PHPCompatibility.PHP.DeprecatedFunctions';
                    break;

                case (version_compare($phpcs, '2.6.0', '<')):
                    // PHPCompatibility 9.x is the last version supporting PHPCS < 2.6.0.
                    $phpcompat = '^9.0';
                    $sniff     = 'PHPCompatibility.FunctionUse.RemovedFunctions';
                    break;
            }

            $data["phpcs $phpcs - phpcompat $phpcompat"] = array(
                'phpcsVersion'     => $phpcs,
                'phpcompatVersion' => $phpcompat,
                'sniff'            => $sniff,
            );
        }

        return $data;
    }
}
