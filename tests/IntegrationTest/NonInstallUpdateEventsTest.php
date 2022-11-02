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
 * Test that the plugin doesn't get triggered on events it isn't hooked into.
 *
 * This test is about Composer and the plugin, so does not need to be tested against multiple PHPCS versions.
 * The behaviour also shouldn't differ between a global vs local Composer install, so only testing one type.
 */
final class NonInstallUpdateEventsTest extends TestCase
{
    private static $composerConfig = array(
        'name'        => 'phpcs-composer-installer/non-plugin-events-test',
        'require-dev' => array(
            'dealerdirect/phpcodesniffer-composer-installer' => '*',
            'phpcs-composer-installer/dummy-subdir'          => '*',
        ),
    );

    /**
     * Set up test environment at the start of the tests.
     */
    public static function set_up_before_class()
    {
        static::createTestEnvironment();

        static::writeComposerJsonFile(self::$composerConfig, static::$tempLocalPath);

        /*
         * Install dependencies.
         * As the commands being run in these tests don't _change_ the environment, we only need to do this once.
         */
        $command = sprintf(
            'composer install --no-plugins --no-ansi --working-dir=%s',
            escapeshellarg(static::$tempLocalPath)
        );
        $result  = static::executeCliCommand($command);

        if ($result['exitcode'] !== 0) {
            throw new RuntimeException('`composer install` failed');
        }
    }

    /**
     * Clean up after all tests have run.
     */
    public static function tear_down_after_class()
    {
        static::removeTestEnvironment();
    }

    /**
     * Test that the plugin doesn't run on commands for which it shouldn't.
     *
     * @dataProvider dataComposerActions
     *
     * @param string $action The Composer action to run.
     *
     * @return void
     */
    public function testPluginDoesNotRun($action)
    {
        $command = sprintf(
            'composer %s -v --no-ansi --working-dir=%s',
            $action,
            escapeshellarg(static::$tempLocalPath)
        );
        $result  = $this->executeCliCommand($command);

        $this->assertStringNotContainsString(
            Plugin::MESSAGE_RUNNING_INSTALLER,
            $result['stdout'],
            "Output from composer $action shows the plugin as running when it shouldn't be."
        );
    }

    /**
     * Data provider.
     *
     * @link https://getcomposer.org/doc/03-cli.md
     * @link https://getcomposer.org/doc/articles/scripts.md#event-names
     *
     * @return array
     */
    public function dataComposerActions()
    {
        $actions = array(
            /*
             * Composer actions which actually have events associated with them,
             * but which the plugin is not hooked into.
             */
            'archive',
            'dump-autoload',
            'status',

            /*
             * Composer actions which don't have events associated with them (just to be sure).
             */
            'check-platform-reqs',
            'config --list',
            'depends',
            'diagnose',
            'fund',
            'help',
            'licenses',
            'outdated',
            'prohibits',
            'search dealerdirect',
            'show',
            'suggests',
            'validate',

            // Excluded to prevent influencing other tests as the Composer version is important for most tests.
            //'self-update',
        );

        $data = array();
        foreach ($actions as $action) {
            $data[$action] = array($action);
        }

        return $data;
    }
}
