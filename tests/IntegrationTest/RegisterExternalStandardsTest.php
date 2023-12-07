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

    private $configOneStandardMultiRuleset = array(
        'name'        => 'phpcs-composer-installer/register-external-stnds-multistnd',
        'require-dev' => array(
            'squizlabs/php_codesniffer'              => null,
            'phpcs-composer-installer/multistandard' => '*',
        ),
    );

    private $configOneStandardInSrcSubdir = array(
        'name'        => 'phpcs-composer-installer/register-external-stnds-in-src-subdir',
        'require-dev' => array(
            'squizlabs/php_codesniffer'          => null,
            'phpcs-composer-installer/dummy-src' => '*',
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
        $config = $this->configOneStandard;
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

        // Verify that PHPCS can with the external standard set as the standard.
        $phpcsCommand = '"vendor/bin/phpcs" --standard=DummySubDir -e';
        $phpcsResult  = $this->executeCliCommand($phpcsCommand, static::$tempGlobalPath);

        $this->assertSame(0, $phpcsResult['exitcode'], 'Exitcode for PHPCS explain did not match 0');
        $this->assertMatchesRegularExpression(
            '`DummySubDir \(1 sniffs?\)\s+[-]+\s+DummySubDir\.Demo\.Demo(?:[\r\n]+|$)`',
            $phpcsResult['stdout'],
            'Output of the PHPCS explain command did not match the expectation.'
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
        $config = $this->configOneStandard;
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

        // Verify that PHPCS can with the external standard set as the standard.
        $phpcsCommand = '"vendor/bin/phpcs" --standard=DummySubDir -e';
        $phpcsResult  = $this->executeCliCommand($phpcsCommand, static::$tempLocalPath);

        $this->assertSame(0, $phpcsResult['exitcode'], 'Exitcode for PHPCS explain did not match 0');
        $this->assertMatchesRegularExpression(
            '`DummySubDir \(1 sniffs?\)\s+[-]+\s+DummySubDir\.Demo\.Demo(?:[\r\n]+|$)`',
            $phpcsResult['stdout'],
            'Output of the PHPCS explain command did not match the expectation.'
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

    /**
     * Test registering one external standard with multiple rulesets.
     *
     * @dataProvider dataRegisterOneStandardMultipleRulesets
     *
     * @param string $phpcsVersion PHPCS version to use in this test.
     *                             This version is randomly selected from the PHPCS versions compatible
     *                             with the PHP version used in the test.
     *
     * @return void
     */
    public function testRegisterOneStandardWithMultipleRulesets($phpcsVersion)
    {
        $config = $this->configOneStandardMultiRuleset;
        $config['require-dev']['squizlabs/php_codesniffer'] = $phpcsVersion;

        $this->writeComposerJsonFile($config, static::$tempLocalPath);

        // Install the dependencies and verify that the plugin has run.
        $this->assertExecute(
            sprintf('composer install -v --no-ansi --working-dir=%s', escapeshellarg(static::$tempLocalPath)),
            0,    // Expected exit code.
            'PHP CodeSniffer Config installed_paths set to ', // Expectation for stdout.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        // Verify that only the one path is registered.
        $result = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempLocalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for "phpcs --config-show" did not match 0');

        $expected = array(
            '/phpcs-composer-installer/multistandard',
        );

        $this->assertSame(
            $expected,
            $this->configShowToPathsArray($result['stdout']),
            'Paths as updated by the plugin does not contain the expected path'
        );

        // Verify that PHPCS sees all three external standards.
        $result = $this->executeCliCommand('"vendor/bin/phpcs" -i', static::$tempLocalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for "phpcs -i" did not match 0');

        $expected   = PHPCSVersions::getStandards($phpcsVersion);
        $expected[] = 'MyFirstStandard';
        $expected[] = 'MySecondStandard';
        $expected[] = 'My-Third-Standard';
        sort($expected, \SORT_NATURAL);

        $this->assertSame(
            $expected,
            $this->standardsPhraseToArray($result['stdout']),
            'Installed standards do not match the expected standards.'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataRegisterOneStandardMultipleRulesets()
    {
        // Test against the highest and lowest supported PHPCS version of each major + PHPCS 4.x dev.
        $versions = PHPCSVersions::getHighLowEachMajor(false, true);
        return PHPCSVersions::toDataprovider($versions);
    }

    /**
     * Test registering an external standard which has the ruleset in a subdirectory nested in `src`.
     *
     * @dataProvider dataRandomPHPCSVersion
     *
     * @param string $phpcsVersion PHPCS version to use in this test.
     *
     * @return void
     */
    public function testRegisterOneStandardInSrcSubdir($phpcsVersion)
    {
        $config = $this->configOneStandardInSrcSubdir;
        $config['require-dev']['squizlabs/php_codesniffer'] = $phpcsVersion;

        $this->writeComposerJsonFile($config, static::$tempGlobalPath);

        // Install the dependencies and verify that the plugin has run.
        $this->assertExecute(
            'composer global install -v --no-ansi',
            0,    // Expected exit code.
            'PHP CodeSniffer Config installed_paths set to ', // Expectation for stdout.
            null, // No stderr expectation.
            'Failed to install dependencies.'
        );

        // Verify that the path for the directory above the ruleset is registered.
        $result = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempGlobalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for "phpcs --config-show" did not match 0');

        $expected = array(
            '/phpcs-composer-installer/dummy-src/src',
        );

        $this->assertSame(
            $expected,
            $this->configShowToPathsArray($result['stdout']),
            'Paths as updated by the plugin does not contain the expected path'
        );

        // Verify that PHPCS sees the external standard.
        $result = $this->executeCliCommand('"vendor/bin/phpcs" -i', static::$tempGlobalPath);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for "phpcs -i" did not match 0');

        $expected   = PHPCSVersions::getStandards($phpcsVersion);
        $expected[] = 'DummySrcSubDir';
        sort($expected, \SORT_NATURAL);

        $this->assertSame(
            $expected,
            $this->standardsPhraseToArray($result['stdout']),
            'Installed standards do not match the expected standards.'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataRandomPHPCSVersion()
    {
        // Test against one random PHPCS version.
        $versions = array(PHPCSVersions::getRandom(true, true));
        return PHPCSVersions::toDataprovider($versions);
    }
}
