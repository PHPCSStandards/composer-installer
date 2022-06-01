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
 * Tests the plugin avoids a fatal error which can occur when the package including the
 * plugin has a dependency on Symfony Finder at a different version than the one
 * included with the Composer phar file.
 * This can lead to the Composer autoloader loading some files from the Finder
 * version included in the Composer phar file and some from the vendor directory,
 * which means that files from different Finder versions are being loaded and those
 * may not be compatible with each other.
 *
 * While this isn't strictly a problem with this plugin, rather with Composer itself,
 * as this plugin is widely used, it is still a good idea if we can try and avoid the error.
 *
 * @link https://github.com/PHPCSStandards/composer-installer/issues/143
 * @link https://github.com/composer/composer/issues/10413
 */
final class FinderVersionMismatchTest extends TestCase
{
    private $composerConfig = array(
        'name'        => 'phpcs-composer-installer/finder-version-mismatch',
        'require-dev' => array(
            'squizlabs/php_codesniffer'                      => '*',
            'phpcs-composer-installer/dummy-subdir'          => '*',
            'dealerdirect/phpcodesniffer-composer-installer' => '*',
            'symfony/finder'                                 => '*',
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
     * @dataProvider dataFinderRootDependencyDoesNotCauseFatalError
     *
     * @param string $phpcsVersion PHPCS version to use in this test.
     *                             This version is randomly selected from the PHPCS versions compatible
     *                             with the PHP version used in the test.
     *
     * @return void
     */
    public function testFinderRootDependencyDoesNotCauseFatalError($finderVersion)
    {
        $config = $this->composerConfig;
        $config['require-dev']['symfony/finder'] = $finderVersion;

        $this->writeComposerJsonFile($config, static::$tempLocalPath);

        // Run an install and verify that there is no fatal error.
        $command       = sprintf('composer install -v --no-ansi --working-dir=%s', escapeshellarg(static::$tempLocalPath));
        $installResult = $this->executeCliCommand($command);

        $this->assertSame(0, $installResult['exitcode'], 'Exitcode for install-codestandards did not match 0');
        $this->assertStringNotContainsString('Fatal error:', $installResult['stdout']);
        $this->assertStringNotContainsString('Fatal error:', $installResult['stderr']);

        // And that the standard is installed.
        $this->assertMatchesRegularExpression(
            '`PHP CodeSniffer Config installed_paths set to [^\s]+/dummy-subdir`',
            trim($installResult['stdout']),
            'Installing the standards failed.'
        );
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataFinderRootDependencyDoesNotCauseFatalError()
    {
/*
Composer | Finder
1.x      | ^2.7 || ^3.0 || ^4.0 || ^5.0
2.0      | ^2.8.52 || ^3.4.35 || ^4.4 || ^5.0
2.0.14   | ^2.8.52 || ^3.4.35 || ^4.4 || ^5.0 || ^6.0
2.3      | ^5.4 || ^6.0

Composer phar | Includes Finder => NEEDS VERIFICATION, this is just a guess!!!
1.x | 2.x
2.0 | 2.x
2.3 | 5.x

Finder | PHP
2.7.0  | >=5.3.9
3.0.0  | >=5.5.9
3.3.7  | ^5.5.9|>=7.0.8
4.0.0  | ^7.1.3
5.0.0  | ^7.2.5
6.0.0  | >=8.0.2
6.1.0  | >=8.1

*/
        $data = [
            'finder 2.x' => ['^2.7'],
        ];

        if (version_compare(\CLI_PHP_MINOR, '5.5', '>=')) {
            $data['finder 3.x'] = ['^3.0'];
        }

        if (version_compare(\CLI_PHP_MINOR, '7.1', '>=')) {
            $data['finder 4.x'] = ['^4.0'];
        }

        if (version_compare(\CLI_PHP_MINOR, '7.2', '>=')) {
            $data['finder 5.x'] = ['^5.0'];
        }

        if (version_compare(\CLI_PHP_MINOR, '8.0', '>=')) {
            $data['finder 6.0'] = ['^6.0'];
        }

        if (version_compare(\CLI_PHP_MINOR, '8.1', '>=')) {
            $data['finder 6.1'] = ['^6.1'];
        }

        return $data;
    }
}
