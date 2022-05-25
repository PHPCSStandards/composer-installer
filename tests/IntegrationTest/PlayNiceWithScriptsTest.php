<?php

/**
 * This file is part of the Dealerdirect PHP_CodeSniffer Standards
 * Composer Installer Plugin package.
 *
 * @copyright 2022 PHPCodeSniffer Composer Installer Contributors
 * @license MIT
 */

namespace Dealerdirect\Composer\Plugin\Installers\PHPCodeSniffer\Tests\IntegrationTest;

use Dealerdirect\Composer\Plugin\Installers\PHPCodeSniffer\Plugin;
use Dealerdirect\Composer\Plugin\Installers\PHPCodeSniffer\Tests\TestCase;

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
                'Dealerdirect\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\Plugin::run',
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

        if ($this->willPluginOutputShow()) {
            $this->assertStringContainsString(
                Plugin::MESSAGE_RUNNING_INSTALLER,
                $result['stdout'],
                'Output from initial composer install missing expected contents.'
            );
        }

        $output = $this->willPluginOutputShow() ? $result['stdout'] : $result['stderr'];
        $this->assertStringContainsString(
            'post-update-cmd successfully run',
            $output,
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
        $data           = array();
        $willOutputShow = self::willPluginOutputShow();

        $data['install:command'] = array(
            'command'         => 'composer install',
            'expectedOutputs' => array(
                'post-install-cmd successfully run',
            ),
        );
        if ($willOutputShow) {
            $data['install:command']['expectedOutputs'][] = Plugin::MESSAGE_RUNNING_INSTALLER;
        }

        $data['update:command'] = array(
            'command'         => 'composer update',
            'expectedOutputs' => array(
                'post-update-cmd successfully run',
            ),
        );
        if ($willOutputShow) {
            $data['update:command']['expectedOutputs'][] = Plugin::MESSAGE_RUNNING_INSTALLER;
        }

        $data['post-install-cmd:script'] = array(
            'command'         => 'composer run-script post-install-cmd',
            'expectedOutputs' => array(
                'post-install-cmd successfully run',
            ),
        );
        if ($willOutputShow) {
            $data['post-install-cmd:script']['expectedOutputs'][] = Plugin::MESSAGE_RUNNING_INSTALLER;
        }

        $data['post-update-cmd:script'] = array(
            'command'         => 'composer run-script post-update-cmd',
            'expectedOutputs' => array(
                'post-update-cmd successfully run',
            ),
        );
        if ($willOutputShow) {
            $data['post-update-cmd:script']['expectedOutputs'][] = Plugin::MESSAGE_RUNNING_INSTALLER;
        }

        $data['install-codestandards:script'] = array(
            'command'         => 'composer run-script install-codestandards',
            'expectedOutputs' => array(
                'install-codestandards successfully run',
            ),
        );
        if ($willOutputShow) {
            $data['install-codestandards:script']['expectedOutputs'][] = Plugin::MESSAGE_RUNNING_INSTALLER;
        }

        return $data;
    }
}
