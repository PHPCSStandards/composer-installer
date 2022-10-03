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
 * Test that the plugin is hooked into the right events and doesn't get triggered when those events are blocked.
 *
 * These tests verify that:
 * - The plugin gets triggered on install/update/require/remove/reinstall events.
 * - The plugin doesn't run when Composer is run with `--no-plugins`.
 * - The plugin does run when Composer is run with `--no-scripts`.
 * - Can be run on-demand via a custom script.
 *
 * This test is about Composer and the plugin, so does not need to be tested against multiple PHPCS versions.
 * The behaviour also shouldn't differ between a global vs local Composer install, so only testing one type.
 *
 * {@internal Note: `create-project` can not be tested as it needs a Packagist registered project, which would
 * mean that the test would not use the _current_ version of the plugin, but a previous release.}
 *
 * @link https://github.com/PHPCSStandards/composer-installer/issues/4
 * @link https://github.com/PHPCSStandards/composer-installer/pull/5
 */
final class InstallUpdateEventsTest extends TestCase
{
    private $composerConfig = array(
        'name'        => 'phpcs-composer-installer/plugin-events-test',
        'require-dev' => array(
            'dealerdirect/phpcodesniffer-composer-installer' => '*',
            'phpcs-composer-installer/dummy-subdir'          => '*',
            'ehime/hello-world'                              => '^1.0',
        ),
        'scripts'     => array(
            'custom-runner'    => array(
                'PHPCSStandards\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\Plugin::run',
            ),
            'post-install-cmd' => array(
                'echo "post-install-cmd successfully run"',
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
     * Test that the plugin runs when Composer is invoked with an action triggering a hooked in event.
     *
     * @dataProvider dataComposerActions
     *
     * @param string $action The Composer action to run.
     *
     * @return void
     */
    public function testPluginRuns($action)
    {
        $this->writeComposerJsonFile($this->composerConfig, static::$tempLocalPath);

        $command = sprintf(
            'composer %s -v --no-ansi --working-dir=%s',
            $action,
            escapeshellarg(static::$tempLocalPath)
        );
        $result  = $this->executeCliCommand($command);

        $this->assertSame(0, $result['exitcode'], "Exitcode for composer $action did not match 0");
        $this->assertStringContainsString(
            Plugin::MESSAGE_RUNNING_INSTALLER,
            $result['stdout'],
            "Output from composer $action does not show the plugin as running while it should be."
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
        return array(
            'install'   => array('install'),
            'update'    => array('update'),
            'require'   => array('require --dev phpcs-composer-installer/dummy-src'),
            'remove'    => array('remove --dev ehime/hello-world'),
        );

        $data = array();
        foreach ($actions as $action) {
            $data[$action] = array($action);
        }

        return $data;
    }

    /**
     * Test that the plugin runs when Composer reinstall is run for a project
     * with a require(-dev) for the plugin.
     *
     * @link https://github.com/composer/composer/issues/10508
     *
     * @return void
     */
    public function testPluginRunsOnReinstall()
    {
        if (version_compare(\COMPOSER_VERSION, '2.2.6', '<') === true) {
            $this->markTestSkipped('Plugins don\'t run on reinstall prior to Composer 2.2.6 - Composer bug #10508');
        }

        $this->writeComposerJsonFile($this->composerConfig, static::$tempLocalPath);

        /*
         * 1. Can only reinstall on something which is already installed, so install first.
         */
        $command = sprintf('composer install -v --no-ansi --working-dir=%s', escapeshellarg(static::$tempLocalPath));
        $result  = $this->executeCliCommand($command);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for composer install did not match 0');

        // Track what installed standards this resulted in.
        $installedPaths = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempLocalPath);
        $this->assertSame(0, $installedPaths['exitcode'], 'Exitcode for "--config-show" did not match 0 (install)');

        /*
         * 2. Reinstall PHPCS.
         */
        $command = sprintf(
            'composer reinstall squizlabs/php_codesniffer -v --no-ansi --working-dir=%s',
            escapeshellarg(static::$tempLocalPath)
        );
        $result  = $this->executeCliCommand($command);
        $this->assertSame(0, $result['exitcode'], 'Exitcode for composer reinstall did not match 0');

        // Verify the plugin ran.
        $this->assertStringContainsString(
            Plugin::MESSAGE_RUNNING_INSTALLER,
            $result['stdout'],
            "Output from composer reinstall does not show the plugin as running while it should be."
        );

        /*
         * 3. Ensure the installed paths are the same before and after the reinstall.
         */
        $reinstalledPaths = $this->executeCliCommand('"vendor/bin/phpcs" --config-show', static::$tempLocalPath);
        $this->assertSame(0, $reinstalledPaths['exitcode'], 'Exitcode for "--config-show" did not match 0 (reinstall)');

        $expected = $this->configShowToPathsArray($installedPaths['stdout']);
        $actual   = $this->configShowToPathsArray($reinstalledPaths['stdout']);

        // Verify that the same paths are registered on install as well as reinstall.
        $this->assertSame($expected, $actual);
    }

    /**
     * Test that the plugin runs (or doesn't run) when Composer is invoked with the --no-scripts argument.
     *
     * Note: the behaviour of Composer changed in 2.1.2. Prior to that, `--no-scripts` would
     * also stop plugins from running. As of Composer 2.1.2, `--no-scripts` and `--no-plugins`
     * function independently of each other.
     * {@link https://github.com/composer/composer/pull/9942}
     *
     * @return void
     */
    public function testPluginRunsOnInstallWithNoScripts()
    {
        $this->writeComposerJsonFile($this->composerConfig, static::$tempLocalPath);

        $command = sprintf(
            'composer install --no-scripts -v --no-ansi --working-dir=%s',
            escapeshellarg(static::$tempLocalPath)
        );
        $result  = $this->executeCliCommand($command);

        $this->assertSame(0, $result['exitcode'], 'Exitcode for composer install did not match 0');

        if (version_compare(\COMPOSER_VERSION, '2.1.2', '>=') === true) {
            $this->assertStringContainsString(
                Plugin::MESSAGE_RUNNING_INSTALLER,
                $result['stdout'],
                'Output from running Composer install missing expected contents.'
            );
        } else {
            // Composer 1.x.
            $this->assertStringNotContainsString(
                Plugin::MESSAGE_RUNNING_INSTALLER,
                $result['stdout'],
                'Output from running Composer install contains unexpected contents.'
            );
        }
    }

    /**
     * Test that the plugin doesn't run when Composer init is run with a require for the plugin
     * (as that doesn't install anything yet).
     *
     * Note: this test "should" be in the NonInstallUpdateEventsTest, but it requires a clean environment,
     * so it ended up being more straight-forward to include it in this test class.
     *
     * @return void
     */
    public function testPluginDoesNotRunsOnInitWithRequire()
    {
        $command = sprintf(
            'composer init'
            . ' --name=phpcs-composer-installer/plugin-events-init-test'
            . ' --type=project'
            . ' --require-dev=dealerdirect/phpcodesniffer-composer-installer:*,phpcs-composer-installer/dummy-subdir:*'
            . ' -v --no-ansi --working-dir=%s',
            escapeshellarg(static::$tempLocalPath)
        );
        $result  = $this->executeCliCommand($command);

        $this->assertSame(0, $result['exitcode'], 'Exitcode for composer init did not match 0');

        $this->assertStringNotContainsString(
            Plugin::MESSAGE_RUNNING_INSTALLER,
            $result['stdout'],
            'Output from composer init shows the plugin as running when it shouldn\'t be.'
        );
    }

    /**
     * Test that the plugin does not run when Composer is invoked with the --no-plugins argument.
     *
     * @return void
     */
    public function testPluginDoesNotRunOnInstallWithNoPlugins()
    {
        $this->writeComposerJsonFile($this->composerConfig, static::$tempLocalPath);

        // Verify the plugin doesn't run when install is run with --no-plugins.
        $command = sprintf(
            'composer install --no-plugins -v --no-ansi --working-dir=%s',
            escapeshellarg(static::$tempLocalPath)
        );
        $result  = $this->executeCliCommand($command);

        $this->assertSame(0, $result['exitcode'], 'Exitcode for composer install did not match 0');

        $this->assertStringNotContainsString(
            Plugin::MESSAGE_RUNNING_INSTALLER,
            $result['stdout'],
            'Output from composer install shows the plugin as running when it shouldn\'t .'
        );

        // Verify the plugin doesn't run when post-install-cmd is run with --no-plugins.
        $command = sprintf(
            'composer run-script post-install-cmd --no-plugins -v --no-ansi --working-dir=%s',
            escapeshellarg(static::$tempLocalPath)
        );
        $result  = $this->executeCliCommand($command);

        $this->assertSame(0, $result['exitcode'], 'Exitcode for composer post-install-cmd did not match 0');

        $this->assertStringNotContainsString(
            Plugin::MESSAGE_RUNNING_INSTALLER,
            $result['stdout'],
            'Output from composer post-install-cmd shows the plugin as running when it shouldn\'t be.'
        );
    }

    /**
     * Test that the plugin does not run when Composer is invoked with the --no-plugins AND --no-scripts arguments,
     * but can then still be invoked via a custom script.
     *
     * @return void
     */
    public function testPluginDoesNotRunWithNoScriptsNoPluginsAndRunsViaScript()
    {
        $this->writeComposerJsonFile($this->composerConfig, static::$tempLocalPath);

        // Verify the plugin doesn't run when install is run with --no-plugins and --no-scripts.
        $command = sprintf(
            'composer install --no-scripts --no-plugins -v --no-ansi --working-dir=%s',
            escapeshellarg(static::$tempLocalPath)
        );
        $result  = $this->executeCliCommand($command);

        $this->assertSame(0, $result['exitcode'], 'Exitcode for composer install did not match 0');

        $this->assertStringNotContainsString(
            Plugin::MESSAGE_RUNNING_INSTALLER,
            $result['stdout'],
            'Output from composer install shows the plugin as running when it shouldn\'t be.'
        );

        // Verify that the plugin can be run via a custom script.
        $script = sprintf(
            'composer custom-runner -v --no-ansi --working-dir=%s',
            escapeshellarg(static::$tempLocalPath)
        );
        $result = $this->executeCliCommand($script);

        $this->assertSame(0, $result['exitcode'], 'Exitcode for running Composer script did not match 0');

        $this->assertStringContainsString(
            Plugin::MESSAGE_RUNNING_INSTALLER,
            $result['stdout'],
            'Output from running Composer script missing expected contents.'
        );
    }
}
