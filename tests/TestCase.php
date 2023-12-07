<?php

/**
 * This file is part of the Dealerdirect PHP_CodeSniffer Standards
 * Composer Installer Plugin package.
 *
 * @copyright 2022 PHPCodeSniffer Composer Installer Contributors
 * @license MIT
 */

namespace PHPCSStandards\Composer\Plugin\Installers\PHPCodeSniffer\Tests;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Yoast\PHPUnitPolyfills\TestCases\TestCase as PolyfillTestCase;

abstract class TestCase extends PolyfillTestCase
{
    protected static $tempDir;

    protected static $tempGlobalPath;

    protected static $tempLocalPath;


    /* ***** SETUP AND TEARDOWN HELPERS ***** */

    public static function createTestEnvironment()
    {
        // Make temp directory
        $class           = substr(strrchr(get_called_class(), '\\'), 1);
        static::$tempDir = sys_get_temp_dir() . '/PHPCSPluginTest/' . uniqid("{$class}_", true);

        $subDirs = array(
            'tempLocalPath'  => 'local',
            'tempGlobalPath' => 'global',
        );

        foreach ($subDirs as $property => $subDir) {
            $path = static::$tempDir . '/' . $subDir;
            if (mkdir($path, 0766, true) === false || is_dir($path) === false) {
                throw new RuntimeException("Failed to create the $path directory for the test");
            }

            static::${$property} = $path;
        }

        putenv('COMPOSER_HOME=' . static::$tempGlobalPath);
    }

    public static function removeTestEnvironment()
    {
        if (file_exists(static::$tempDir) === true) {
            // Remove temp directory, including all files.
            if (static::onWindows() === true) {
                // Windows.
                exec(sprintf('rd /s /q %s', escapeshellarg(static::$tempDir)), $output, $exitCode);
            } else {
                exec(sprintf('rm -rf %s', escapeshellarg(static::$tempDir)), $output, $exitCode);
            }

            if ($exitCode !== 0) {
                throw new RuntimeException(
                    'Failed to remove the temp directory created for the test: ' . \PHP_EOL . 'Error: ' . $output
                );
            }

            clearstatcache();
        }

        putenv('COMPOSER_HOME');
    }


    /* ***** CUSTOM ASSERTIONS ***** */

    /**
     * Assert that a composer.json file is valid for use in the tests.
     *
     * @param string $workingDir The working directory in which to execute the command.
     * @param string $file       The file to execute the command on.
     *                           By default the command will execute on the `composer.json` file
     *                           in the current or working directory.
     *
     * @return void
     *
     * @throws RuntimeException When either passed argument is not a string.
     * @throws RuntimeException When both arguments are passed as Composer can only handle one.
     */
    public function assertComposerValidates($workingDir = '', $file = '')
    {
        if (is_string($workingDir) === false) {
            throw new RuntimeException('Working directory must be a string.');
        }

        if (is_string($file) === false) {
            throw new RuntimeException('File must be a string.');
        }

        if ($workingDir !== '' && $file !== '') {
            throw new RuntimeException(
                'Pass either the working directory OR a file name. Composer does not handle both in the same command.'
            );
        }

        $command = 'composer validate --no-check-all --no-check-publish --no-check-lock --no-ansi';
        $stderr  = '%s is valid';
        $message = 'Provided Composer configuration is not valid.';

        if ($workingDir !== '') {
            $command .= sprintf(' --working-dir=%s', escapeshellarg($workingDir));
            $stderr   = sprintf($stderr, 'composer.json');
            $message .= ' Working directory: ' . $workingDir;
        }

        if ($file !== '') {
            $command .= ' ' . escapeshellarg($file);
            $stderr   = sprintf($stderr, $file);
            $message .= ' File: ' . $file;
        }

        $this->assertExecute(
            $command,
            0,       // Expected exit code.
            null,    // Expected stdout.
            $stderr, // Expected stderr.
            $message
        );
    }

    /**
     * Assert that a command when executed meets certain expectations for exit code and output.
     *
     * Note: the stdout and stderr assertions will verify that the passed expectation is a **substring**
     * of the actual output using `assertStringContainsString()`.
     *
     * The stdout and stderr assertions will disregard potential color codes in the actual output
     * when no color codes are included in the expectation.
     *
     * If more specific assertions are needed, use the `TestCase::executeCliCommand()` directly and
     * apply assertions to the results from that function call.
     *
     * @param string      $command          The CLI command to execute.
     * @param int|null    $expectedExitCode Optional. The expected exit code for the command.
     * @param string|null $expectedStdOut   Optional. The expected command output to stdout.
     * @param string|null $expectedStdErr   Optional. The expected command output to stderr.
     * @param string      $message          Optional. Message to display when an assertion fails.
     * @param string|null $workingDir       Optional. The directory in which to execute the command.
     *                                      Defaults to `null` = the working directory of the current PHP process.
     *                                      Note: if the command itself already contains a "working directory" argument,
     *                                      this parameter will normally not need to be passed.
     *
     * @return void
     *
     * @throws RuntimeException When neither $expectedExitCode, $expectedStdOut or $expectedStdErr are passed.
     */
    public function assertExecute(
        $command,
        $expectedExitCode = null,
        $expectedStdOut = null,
        $expectedStdErr = null,
        $message = '',
        $workingDir = null
    ) {
        if ($expectedExitCode === null && $expectedStdOut === null && $expectedStdErr === null) {
            throw new RuntimeException('At least one expectation has to be set for the executed command.');
        }

        $result = $this->executeCliCommand($command, $workingDir);

        if (is_string($expectedStdOut)) {
            $msg = 'stdOut did not contain the expected output. ' . $message;

            if ($expectedStdOut === '') {
                $this->assertSame($expectedStdOut, $result['stdout'], $msg);
            } else {
                $stdout = $this->maybeStripColors($expectedStdOut, $result['stdout']);
                $this->assertStringContainsString($expectedStdOut, $stdout, $msg);
            }
        }

        if (is_string($expectedStdErr)) {
            $msg = 'stdErr did not contain the expected output. ' . $message;

            if ($expectedStdErr === '') {
                $this->assertSame($expectedStdErr, $result['stderr'], $msg);
            } else {
                $stderr = $this->maybeStripColors($expectedStdErr, $result['stderr']);
                $this->assertStringContainsString($expectedStdErr, $stderr, $msg);
            }
        }

        if (is_int($expectedExitCode)) {
            $msg = 'Exit code did not match expected code. ' . $message;
            $this->assertSame($expectedExitCode, $result['exitcode'], $msg);
        }
    }


    /* ***** HELPER METHODS ***** */

    /**
     * Determine whether or not the tests are being run on Windows.
     *
     * @return bool
     */
    protected static function onWindows()
    {
        return stripos(\PHP_OS, 'WIN') === 0;
    }

    /**
     * Create a composer.json file based on a given configuration.
     *
     * @param array  $config    Composer configuration as an array.
     * @param string $directory Location to write the resulting `composer.json` file to (without trailing slash).
     *
     * @return void
     *
     * @throws RuntimeException When either of the passed parameters are of the wrong data type.
     * @throws RuntimeException When the provided configuration is invalid.
     * @throws RuntimeException When the configuration could not be written to a file.
     */
    protected static function writeComposerJsonFile($config, $directory)
    {
        if (is_array($config) === false || $config === array()) {
            throw new RuntimeException('Configuration must be a non-empty array.');
        }

        if (is_string($directory) === false || $directory === '') {
            throw new RuntimeException('Directory must be a non-empty string.');
        }

        // Inject artifact for this plugin and some dummy standards.
        if (isset($config['repositories']) === false) {
            $config['repositories'][] = array(
                'type' => 'artifact',
                'url'  => \ZIP_ARTIFACT_DIR,
            );
        }

        // Inject ability to run the plugin via a script.
        if (isset($config['scripts']['install-codestandards']) === false) {
            $config['scripts']['install-codestandards'] = array(
                'PHPCSStandards\\Composer\\Plugin\\Installers\\PHPCodeSniffer\\Plugin::run',
            );
        }

        // Inject permission for this plugin to run (Composer 2.2 compat).
        if (isset($config['config']['allow-plugins']['dealerdirect/phpcodesniffer-composer-installer']) === false) {
            $config['config']['allow-plugins']['dealerdirect/phpcodesniffer-composer-installer'] = true;
        }

        /*
         * Disable TLS when on Windows with Composer 1.x and PHP 5.4.
         * @link https://github.com/composer/composer/issues/10495
         */
        if (static::onWindows() === true && \CLI_PHP_MINOR === '5.4' && strpos(\COMPOSER_VERSION, '1') === 0) {
            $config['config']['disable-tls'] = true;
        }

        $encoded = json_encode($config, \JSON_UNESCAPED_SLASHES | \JSON_PRETTY_PRINT);
        if (json_last_error() !== \JSON_ERROR_NONE || $encoded === false) {
            throw new RuntimeException('Provided configuration can not be encoded to valid JSON');
        }

        $written = file_put_contents($directory . '/composer.json', $encoded);

        if ($written === false) {
            throw new RuntimeException('Failed to create the composer.json file in the temp directory for the test');
        }

        // Add debug information to the test listener which will be displayed in case the test fails.
        DebugTestListener::debugLog(
            '---------------------------------------' . \PHP_EOL
            . 'composer.json: ' . \PHP_EOL
            . $encoded . \PHP_EOL
            . '---------------------------------------' . \PHP_EOL
        );
    }

    /**
     * Helper function for CLI commands.
     *
     * This function stabilizes the CLI command for the purpose of these tests when the
     * tests are run in a non-isolated environment with multiple installed PHP versions
     * and multiple installed Composer versions.
     *
     * This prevents the system default PHP version being used instead of the PHP version
     * which was used to initiate the test run.
     * Similarly, this prevents the system default Composer version being used instead of the
     * target Composer version for this test run.
     *
     * @param string      $command    The command to stabilize.
     * @param string|null $workingDir Optional. The directory in which the command will be executed.
     *                                Defaults to `null` = the working directory of the current PHP process.
     *
     * @return string
     *
     * @throws RuntimeException When the passed command is not a string.
     */
    protected static function stabilizeCommand($command, $workingDir = null)
    {
        if (is_string($command) === false) {
            throw new RuntimeException('Command must be a string.');
        }

        if (strpos($command, 'vendor/bin/phpcs') !== false) {
            $phpcsCommand = static::getPhpcsCommand($workingDir);
            if (strpos($command, 'vendor/bin/phpcs') === 0) {
                $command = '"' . \PHP_BINARY . '" ' . $phpcsCommand . substr($command, 16);
            }

            if (strpos($command, '"vendor/bin/phpcs"') === 0) {
                $command = '"' . \PHP_BINARY . '" ' . $phpcsCommand . substr($command, 18);
            }

            if (strpos($command, ' vendor/bin/phpcs ') !== false) {
                $command = str_replace(' vendor/bin/phpcs ', ' ' . $phpcsCommand . ' ', $command);
            }

            if (strpos($command, ' "vendor/bin/phpcs" ') !== false) {
                $command = str_replace(' "vendor/bin/phpcs" ', ' ' . $phpcsCommand . ' ', $command);
            }
        }

        if (strpos($command, 'php composer.phar ') !== false) {
            $command = str_replace('php composer.phar ', '"' . \PHP_BINARY . '" "' . \COMPOSER_PHAR . '" ', $command);
        }

        if (strpos($command, 'php ') === 0) {
            $command = '"' . \PHP_BINARY . '" ' . substr($command, 3);
        }

        if (strpos($command, ' php ') !== false) {
            $command = str_replace(' php ', ' "' . \PHP_BINARY . '" ', $command);
        }

        if (strpos($command, 'composer ') !== false) {
            $command = str_replace('composer ', '"' . \PHP_BINARY . '" "' . \COMPOSER_PHAR . '" ', $command);
        }

        // Make sure the `--no-interaction` flag is set for all Composer commands to prevent tests hanging.
        if (strpos($command, '"' . \COMPOSER_PHAR . '"') !== false && strpos($command, ' --no-interaction') === false) {
            $command = str_replace('"' . \COMPOSER_PHAR . '"', '"' . \COMPOSER_PHAR . '" --no-interaction', $command);
        }

        /*
         * If the command will be run on Windows in combination with PHP < 8.0, wrap it in an extra set of quotes.
         * Note: it is unclear what changes in PHP 8.0, but the quotes will now suddenly break things.
         * Ref: https://www.php.net/manual/en/function.proc-open.php#example-3331
         */
        if (static::onWindows() === true && substr(\CLI_PHP_MINOR, 0, 1) < 8) {
            $command = '"' . $command . '"';
        }

        return $command;
    }

    /**
     * Retrieve the command to use to run PHPCS.
     *
     * @param string|null $workingDir Optional. The directory in which the command will be executed.
     *                                Defaults to `null` = the working directory of the current PHP process.
     *
     * @return string
     */
    protected static function getPhpcsCommand($workingDir = null)
    {
        $command = '"vendor/squizlabs/php_codesniffer/bin/phpcs"'; // PHPCS 3.x.

        if (is_string($workingDir) && file_exists($workingDir . '/vendor/squizlabs/php_codesniffer/scripts/phpcs')) {
            // PHPCS 2.x.
            $command = '"vendor/squizlabs/php_codesniffer/scripts/phpcs"';
        }

        return $command;
    }

    /**
     * Helper function to execute a CLI command.
     *
     * @param string      $command    The CLI command to execute.
     * @param string|null $workingDir Optional. The directory in which to execute the command.
     *                                Defaults to `null` = the working directory of the current PHP process.
     *                                Note: if the command itself already contains a "working directory" argument,
     *                                this parameter will normally not need to be passed.
     * @param bool        $autoRetry  Internal. Whether the command should be retried if it fails on a particular
     *                                Composer exception. This parameter should only be set by the method itself
     *                                when recursing on itself.
     *
     * @return array Format:
     *               'exitcode' int    The exit code from the command.
     *               'stdout'   string The output send to stdout.
     *               'stderr'   string The output send to stderr.
     *
     * @throws RuntimeException When the passed arguments do not comply.
     * @throws RuntimeException When no resource could be obtained to execute the command.
     */
    public static function executeCliCommand($command, $workingDir = null, $autoRetry = true)
    {
        if (is_string($command) === false || $command === '') {
            throw new RuntimeException('Command must be a non-empty string.');
        }

        if (is_null($workingDir) === false && (is_string($workingDir) === false || $workingDir === '')) {
            throw new RuntimeException('Working directory must be a non-empty string or null.');
        }

        $command        = static::stabilizeCommand($command, $workingDir);
        $descriptorspec = array(
           0 => array("pipe", "r"),  // stdin
           1 => array("pipe", "w"),  // stdout
           2 => array("pipe", "w"),  // stderr
        );

        $process = proc_open($command, $descriptorspec, $pipes, $workingDir);

        if (is_resource($process) === false) {
            throw new RuntimeException('Could not obtain a resource with proc_open() to execute the command.');
        }

        $result = array();
        fclose($pipes[0]);

        $result['stdout'] = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $result['stderr'] = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        $result['exitcode'] = proc_close($process);

        // Add debug information to the test listener which will be displayed in case the test fails.
        DebugTestListener::debugLog(
            '---------------------------------------' . \PHP_EOL
            . 'Command: ' . $command . \PHP_EOL
            . 'Output: ' . var_export($result, true) . \PHP_EOL
            . '---------------------------------------' . \PHP_EOL
        );

        /*
         * Prevent the complete CI run failing on a particular error which Composer sometimes
         * runs into. Retry the command instead. In most cases, that should fix it.
         * If the command still fails, just return the results.
         */
        if (
            $autoRetry === true
            && $result['exitcode'] === 1
            && strpos($command, '"' . \COMPOSER_PHAR . '"') !== false
            && strpos($result['stderr'], '[Composer\\Downloader\\TransportException]') !== false
            && strpos($result['stderr'], 'Peer fingerprint did not match') !== false
        ) {
            // Retry and return the results of the retry.
            return self::executeCliCommand($command, $workingDir, false);
        }

        return $result;
    }

    /**
     * Helper function which strips potential CLI colour codes from the actual output
     * when the expected output does not contain any colour codes.
     *
     * @param string $expected Expected output.
     * @param string $actual   Actual output.
     *
     * @return string Actual output, potentially stripped of colour codes.
     *
     * @throws RuntimeException When either passed argument is not a string.
     */
    protected function maybeStripColors($expected, $actual)
    {
        if (is_string($expected) === false) {
            throw new RuntimeException('Expected output must be a string.');
        }

        if (is_string($actual) === false) {
            throw new RuntimeException('Actual output must be a string.');
        }

        if ($expected === '') {
            // Nothing to do.
            return $actual;
        }

        if (
            (strpos($expected, "\033") === false && strpos($actual, "\033") !== false)
            || (strpos($expected, "\x1b") === false && strpos($actual, "\x1b") !== false)
        ) {
            $actual = preg_replace('`(?:\\\\033|\\\\x1b)\\\\[[0-9]+(;[0-9]*)[A-Za-z]`', '', $actual);
        }

        return $actual;
    }

    /**
     * Helper function to create a file which can be used to run PHPCS against.
     *
     * @param string $path     The full path, including filename, to write the file to.
     * @param string $contents Optional. The ccntents for the file.
     *                         Defaults to a simple `echo 'Hello world';` PHP file.
     *
     * @return void
     *
     * @throws RuntimeException When either passed argument is not a string.
     * @throws RuntimeException When the file could not be created.
     */
    protected function createFile($path, $contents = '')
    {
        if (is_string($path) === false || $path === '') {
            throw new RuntimeException('Path must be a non-empty string.');
        }

        if (is_string($contents) === false) {
            throw new RuntimeException('Contents must be a string.');
        }

        if ($contents === '') {
            $contents = <<<'PHP'
<?php
echo 'Hello world!';
PHP;
        }

        if (file_exists($path) === true) {
            unlink($path);
        }

        $written = file_put_contents($path, $contents);

        if ($written === false) {
            throw new RuntimeException('Failed to create the file.');
        }
    }

    /**
     * Helper function to recursively copy a directory with all its content to another location.
     *
     * Includes making any potentially needed tweaks to `composer.json` files.
     *
     * @param string $src  Full path to the source directory.
     * @param string $dest Full path to the destination directory.
     *
     * @return void
     *
     * @throws RuntimeException When either or the passed arguments is not a string.
     * @throws RuntimeException When a (sub)directory could not be created.
     * @throws RuntimeException When a file could not be copied.
     */
    protected function recursiveDirectoryCopy($srcDir, $destDir)
    {
        if (is_string($srcDir) === false || is_dir($srcDir) === false) {
            throw new RuntimeException('The source directory must be a string pointing to an existing directory.');
        }

        if (is_string($destDir) === false) {
            throw new RuntimeException('The destination directory must be a string.');
        }

        $directory = new RecursiveDirectoryIterator(
            realpath($srcDir),
            RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::UNIX_PATHS
        );
        $iterator  = new RecursiveIteratorIterator($directory, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $path => $fileInfo) {
            $subPath = $iterator->getSubPathname();

            // Special case the Composer config.
            if ($subPath === 'composer.json') {
                $composerConfig = json_decode(file_get_contents($fileInfo->getPathname()), true);
                $this->writeComposerJsonFile($composerConfig, $destDir);
                continue;
            }

            $target = $destDir . '/' . $subPath;

            if ($fileInfo->isDir()) {
                if (mkdir($target, 0766, true) === false || is_dir($target) === false) {
                    throw new RuntimeException("Failed to create the $target directory for the test");
                }
                continue;
            }

            if ($fileInfo->isFile()) {
                if (copy($fileInfo->getPathname(), $target) === false || is_file($target) === false) {
                    throw new RuntimeException("Failed to copy $src to $target ");
                }
            }
        }
    }

    /**
     * Retrieve a list of the standards recognized by PHPCS based on the output of `phpcs -i`.
     *
     * @param string $phrase The output of `phpcs -i`.
     *
     * @return array Numerically indexed array of standards, natural sort applied.
     *
     * @throws RuntimeException When the passed argument is not a string.
     * @throws RuntimeException When the output could not be parsed.
     */
    protected function standardsPhraseToArray($phrase)
    {
        if (is_string($phrase) === false) {
            throw new RuntimeException('The input phrase must be a string.');
        }

        $phrase = trim($phrase);

        if ($phrase === 'No coding standards are installed.') {
            return array();
        }

        if (strpos($phrase, 'The only coding standard installed is ') === 0) {
            $standard = str_replace('The only coding standard installed is ', '', $phrase);
            return (array) trim($standard);
        }

        $phrase = str_replace('The installed coding standards are ', '', $phrase);
        $parts  = explode(' and ', $phrase);

        if (count($parts) !== 2) {
            throw new RuntimeException(
                'Looks like the output from PHPCS for phpcs -i has changed.'
                . ' Please update the `TestCase::standardsPhraseToArray()` method.'
                . ' Input phrase: ' . $phrase
            );
        }

        $standards   = explode(', ', $parts[0]);
        $standards[] = $parts[1];
        $standards   = array_map('trim', $standards); // Trim off whitespace just to be on the safe side.

        sort($standards, \SORT_NATURAL);

        return $standards;
    }

    /**
     * Retrieve a list of the paths registered with PHPCS based on the output of `phpcs --config-show`.
     *
     * @param string $configShow The `stdout` output of `phpcs --config-show`.
     *
     * @return array Numerically indexed array of paths, stripped of absolute/relative path differences,
     *               natural sort applied.
     *
     * @throws RuntimeException When the passed argument is not a string.
     */
    protected function configShowToPathsArray($configShow)
    {
        if (is_string($configShow) === false) {
            throw new RuntimeException('The config-show input must be a string.');
        }

        if (preg_match('`installed_paths:\s+([^\n\r]+)\s+`', $configShow, $matches) !== 1) {
            return array();
        }

        $pathsAsArray = explode(',', $matches[1]);
        $pathsAsArray = array_map(
            function ($value) {
                $search = array(
                    '`^[^\r\n]+/vendor/`',
                    '`^\.\./\.\./`',
                );

                $replaced = preg_replace($search, '/', $value);
                if ($replaced === null) {
                    return $value;
                }

                return trim($replaced); // Trim off whitespace just to be on the safe side.
            },
            $pathsAsArray
        );

        sort($pathsAsArray, \SORT_NATURAL);

        return $pathsAsArray;
    }
}
