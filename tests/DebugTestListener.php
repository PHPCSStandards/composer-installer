<?php

/**
 * This file is part of the Dealerdirect PHP_CodeSniffer Standards
 * Composer Installer Plugin package.
 *
 * @copyright 2022 PHPCodeSniffer Composer Installer Contributors
 * @license MIT
 */

namespace PHPCSStandards\Composer\Plugin\Installers\PHPCodeSniffer\Tests;

use PHPUnit\Framework\TestListener;
use Yoast\PHPUnitPolyfills\TestListeners\TestListenerDefaultImplementation;

final class DebugTestListener implements TestListener
{
    use TestListenerDefaultImplementation;

    /**
     * Log of CLI commands and their output as run for a test.
     *
     * @var string
     */
    private static $debugLog = '';

    /**
     * Clear the log when a new test starts.
     *
     * @param Test $test Test object.
     *
     * @return void
     */
    public function start_test($test)
    {
        self::$debugLog = '';
    }

    /**
     * Display the debug log when a test fails.
     *
     * @param Test                 $test Test object.
     * @param AssertionFailedError $e    Instance of the assertion failure exception encountered.
     * @param float                $time Execution time of this test.
     *
     * @return void
     */
    public function add_failure($test, $e, $time)
    {
        if (empty(self::$debugLog) === false) {
            echo \PHP_EOL, 'Debug information for failing test: ', $test->getName(), \PHP_EOL, self::$debugLog;
        }
    }

    /**
     * Add information to the debug log.
     *
     * @param string The information to add to the log.
     *
     * @return void
     */
    public static function debugLog($str)
    {
        self::$debugLog .= $str . \PHP_EOL;
    }
}
