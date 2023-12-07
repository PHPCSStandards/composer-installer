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
 * Test that the plugin does not block other post install/update scripts from running.
 *
 * This test is about Composer and the plugin, so does not need to be tested against multiple PHPCS versions.
 * The behaviour also shouldn't differ between a global vs local Composer install, so only testing one type.
 *
 * @link https://github.com/PHPCSStandards/composer-installer/pull/105
 * @link https://gist.github.com/Potherca/6d615b893c2f93ce391e4c7a5c6fade9
 */
final class PlayNiceWithScriptsTest extends TestCase
{
    private $composerConfig = array(
        'name'        => 'phpcs-composer-installer/dont-block-scripts-test',
        'require-dev' => array(
            'squizlabs/php_codesniffer'                      => '*',
            'dealerdirect/phpcodesniffer-composer-installer' => '*',
            'phpcs-composer-installer/dummy-subdir'          => '*',
        ),
        'scripts'     => array(
            'post-install-cmd' => array(
                'echo "post-install-cmd successfully run"',
            ),
            'post-update-cmd' => array(
                'echo "post-update-cmd successfully run"',
            ),
            'install-codestandards' => array(
                'PHPCSStandards\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\Plugin::run',
                'echo "install-codestandards successfully run"',
            ),
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
     * Test that the plugin does not block other post install/update scripts from running.
     *
     * @dataProvider dataScriptsAreNotBlockedFromRunning
     *
     * @param string $secondCommand   The command to run after the initial install has been done.
     * @param array  $expectedOutputs Phrases which are expected to be included in the stdout for the second command.
     *
     * @return void
     */
    public function testScriptsAreNotBlockedFromRunning($command, $expectedOutputs)
    {
        $this->writeComposerJsonFile($this->composerConfig, static::$tempLocalPath);

        /*
         * 1. Run an install, all should be fine to begin with.
         */
        $installCommand = sprintf(
            'composer install -v --no-ansi --working-dir=%s',
            escapeshellarg(static::$tempLocalPath)
        );
        $result         = $this->executeCliCommand($installCommand);

        $this->assertSame(0, $result['exitcode'], 'Exitcode for initial composer install did not match 0');

        $this->assertStringContainsString(
            Plugin::MESSAGE_RUNNING_INSTALLER,
            $result['stdout'],
            'Output from initial composer install missing expected contents.'
        );

        $this->assertStringContainsString(
            'post-update-cmd successfully run',
            $result['stdout'],
            'Output from initial composer install missing expected contents.'
        );

        /*
         * 2. Run the second command to confirm scripts are not blocked.
         */
        $command = sprintf('%s -v --no-ansi --working-dir=%s', $command, escapeshellarg(static::$tempLocalPath));
        $result  = $this->executeCliCommand($command);

        $this->assertSame(0, $result['exitcode'], 'Exitcode for secondary command did not match 0');

        foreach ($expectedOutputs as $expected) {
            $this->assertStringContainsString(
                $expected,
                $result['stdout'],
                'Output from secondary command missing expected contents.'
            );
        }
    }

    /**
     * Data provider.
     *
     * @return array
     */
    public function dataScriptsAreNotBlockedFromRunning()
    {
        return array(
            'install:command' => array(
                'command'         => 'composer install',
                'expectedOutputs' => array(
                    'post-install-cmd successfully run',
                    Plugin::MESSAGE_RUNNING_INSTALLER,
                ),
            ),
            'update:command' => array(
                'command'         => 'composer update',
                'expectedOutputs' => array(
                    'post-update-cmd successfully run',
                    Plugin::MESSAGE_RUNNING_INSTALLER,
                ),
            ),
            'post-install-cmd:script' => array(
                'command'         => 'composer run-script post-install-cmd',
                'expectedOutputs' => array(
                    Plugin::MESSAGE_RUNNING_INSTALLER,
                    'post-install-cmd successfully run',
                ),
            ),
            'post-update-cmd:script' => array(
                'command'         => 'composer run-script post-update-cmd',
                'expectedOutputs' => array(
                    Plugin::MESSAGE_RUNNING_INSTALLER,
                    'post-update-cmd successfully run',
                ),
            ),
            'install-codestandards:script' => array(
                'command'         => 'composer run-script install-codestandards',
                'expectedOutputs' => array(
                    Plugin::MESSAGE_RUNNING_INSTALLER,
                    'install-codestandards successfully run',
                ),
            ),
        );
    }
}
