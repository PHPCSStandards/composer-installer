<?php

/**
 * This file is part of the Dealerdirect PHP_CodeSniffer Standards
 * Composer Installer Plugin package.
 *
 * @copyright 2022 PHPCodeSniffer Composer Installer Contributors
 * @license MIT
 */

namespace DummySubDir\Sniffs\Demo;

/**
 * Dummy sniff which can be used to verify that PHPCS runs with an external standard.
 *
 * Note: this sniff does not implement the PHPCS native Sniff class, nor does it have
 * the typical `File` type declaration in the `process()` method.
 * This is to allow the sniff to be compatible with both PHPCS 2.x as well as 3.x
 * without too much other work-arounds being needed.
 */
class DemoSniff
{
    /**
     * Registers the tokens that this sniff wants to listen for.
     *
     * @return int[]
     */
    public function register()
    {
        return array(\T_OPEN_TAG);
    }

    /**
     * Processes this test, when one of its tokens is encountered.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int                         $stackPtr  The position of the current token
     *                                               in the stack passed in $tokens.
     *
     * @return void
     */
    public function process($phpcsFile, $stackPtr)
    {
        // Do nothing as this is for testing that the sniff can be found only.
    }
}
