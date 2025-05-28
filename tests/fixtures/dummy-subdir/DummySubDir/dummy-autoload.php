<?php

/**
 * PHPCS 2.x Had the `PHP_CodeSniffer_Sniff` interface...
 * PHPCS 3.x ... renamed that to `PHP_CodeSniffer\Sniffs\Sniff` and ...
 * PHPCS 4.x ... demands that sniffs implement the interface.
 *
 * Additionally, the Ruleset `<autoload>` directive is only supported as of PHPCS 3.0.
 *
 * So... for a test fixture "sniff" to be cross-version compatible:
 * - it must implement the `Sniff` interface for PHPCS 4 compatibility.
 * - which will need to be class aliased for PHPCS 2 vs 3 compatibility.
 *   => That's what's being done here.
 *
 * Also note that once the interface is being implemented, the `process()` method needs the `File`
 * type declaration, hence, aliasing that class too.
 *
 * And we need to alias to the PHPCS 2.x name as only 3.x can include this autoload file using `<autoload>`.
 */

if (!defined('PHPCS_ALIASES_SET')) {
    if (! interface_exists('\PHP_CodeSniffer_File')) {
        class_alias('PHP_CodeSniffer\Files\File', '\PHP_CodeSniffer_File');
    }

    if (! class_exists('\PHP_CodeSniffer_Sniff')) {
        class_alias('PHP_CodeSniffer\Sniffs\Sniff', '\PHP_CodeSniffer_Sniff');
    }

    define('PHPCS_ALIASES_SET', true);
}
