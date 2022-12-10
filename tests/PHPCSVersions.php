<?php

/**
 * This file is part of the Dealerdirect PHP_CodeSniffer Standards
 * Composer Installer Plugin package.
 *
 * @copyright 2022 PHPCodeSniffer Composer Installer Contributors
 * @license MIT
 */

namespace PHPCSStandards\Composer\Plugin\Installers\PHPCodeSniffer\Tests;

/**
 * Helper class to retrieve PHPCS versions suitable for the current PHP version.
 */
final class PHPCSVersions
{
    /**
     * Composer name for the development branch.
     *
     * @var string
     */
    const MASTER = 'dev-master';

    /**
     * Composer name for the development branch for the next major.
     *
     * @var string
     */
    const NEXT_MAJOR = '4.0.x-dev as 3.9.99';

    /**
     * List of all PHPCS version which are supported by this plugin.
     *
     * Note: PHPCS 3.0.0, 3.0.1 and 3.0.2 are not included in this list as they are not supported.
     * This matches the version constraint in the `composer.json` file of this package.
     * {@link https://github.com/PHPCSStandards/composer-installer/pull/152}
     *
     * @var array
     */
    private static $allPhpcsVersions = array(
        '2.0.0' => '2.0.0',
        '2.1.0' => '2.1.0',
        '2.2.0' => '2.2.0',
        '2.3.0' => '2.3.0',
        '2.3.1' => '2.3.1',
        '2.3.2' => '2.3.2',
        '2.3.3' => '2.3.3',
        '2.3.4' => '2.3.4',
        '2.4.0' => '2.4.0',
        '2.5.0' => '2.5.0',
        '2.5.1' => '2.5.1',
        '2.6.0' => '2.6.0',
        '2.6.1' => '2.6.1',
        '2.6.2' => '2.6.2',
        '2.7.0' => '2.7.0',
        '2.7.1' => '2.7.1',
        '2.8.0' => '2.8.0',
        '2.8.1' => '2.8.1',
        '2.9.0' => '2.9.0',
        '2.9.1' => '2.9.1',
        '2.9.2' => '2.9.2',
        '3.1.0' => '3.1.0',
        '3.1.1' => '3.1.1',
        '3.2.0' => '3.2.0',
        '3.2.1' => '3.2.1',
        '3.2.2' => '3.2.2',
        '3.2.3' => '3.2.3',
        '3.3.0' => '3.3.0',
        '3.3.1' => '3.3.1',
        '3.3.2' => '3.3.2',
        '3.4.0' => '3.4.0',
        '3.4.1' => '3.4.1',
        '3.4.2' => '3.4.2',
        '3.5.0' => '3.5.0',
        '3.5.1' => '3.5.1',
        '3.5.2' => '3.5.2',
        '3.5.3' => '3.5.3',
        '3.5.4' => '3.5.4',
        '3.5.5' => '3.5.5',
        '3.5.6' => '3.5.6',
        '3.5.7' => '3.5.7',
        '3.5.8' => '3.5.8',
        '3.6.0' => '3.6.0',
        '3.6.1' => '3.6.1',
        '3.6.2' => '3.6.2',
        '3.7.0' => '3.7.0',
        '3.7.1' => '3.7.1',
    );

    /**
     * Retrieve an array with a specific number of PHPCS versions valid for the current PHP version.
     *
     * @param int  $number       Number of PHPCS versions to retrieve (excluding master/next major).
     *                           Defaults to `0` = all supported versions for the current PHP version.
     *                           When a non-0 value is passed, a random selection of versions supported
     *                           by the current PHP version will be returned.
     * @param bool $addMaster    Whether or not `dev-master` should be added to the version array (providing
     *                           it supports the current PHP version).
     *                           Defaults to `false`.
     * @param bool $addNextMajor Whether or not the development branch for the next PHPCS major should be
     *                           added to the version array (providing it supports the current PHP version).
     *                           Defaults to `false`.
     *                           Note: if `true`, the version will be returned in a Composer usable format.
     *
     * @return array Numerically indexed array with PHPCS version identifiers as values.
     */
    public static function get($number = 0, $addMaster = false, $addNextMajor = false)
    {
        if (is_int($number) === false || $number < 0) {
            throw new RuntimeException('The number parameter must be a positive integer.');
        }

        $versions = self::getSupportedVersions();

        $selection = array_values($versions);
        if ($number !== 0 && empty($versions) === false) {
            $number    = min($number, count($versions));
            $selection = (array) array_rand($versions, $number);
        }

        if ($addMaster === true) {
            $selection[] = self::MASTER;
        }

        if ($addNextMajor === true && self::isNextMajorSupported()) {
            $selection[] = self::NEXT_MAJOR;
        }

        return $selection;
    }

    /**
     * Retrieve an array of the highest and lowest PHPCS versions valid for the current PHP version.
     *
     * @param bool $addMaster    Whether or not `dev-master` should be added to the version array (providing
     *                           it supports the current PHP version).
     *                           Defaults to `false`.
     * @param bool $addNextMajor Whether or not the development branch for the next PHPCS major should be
     *                           added to the version array (providing it supports the current PHP version).
     *                           Defaults to `false`.
     *                           Note: if `true`, the version will be returned in a Composer usable format.
     *
     * @return array Numerically indexed array with PHPCS version identifiers as values.
     */
    public static function getHighLow($addMaster = false, $addNextMajor = false)
    {
        $versions  = self::getSupportedVersions();
        $selection = array();

        if (empty($versions) === false) {
            $selection[] = min($versions);
            $selection[] = max($versions);
        }

        if ($addMaster === true) {
            $selection[] = self::MASTER;
        }

        if ($addNextMajor === true && self::isNextMajorSupported()) {
            $selection[] = self::NEXT_MAJOR;
        }

        return $selection;
    }

    /**
     * Retrieve an array of the highest and lowest supported PHPCS versions for each PHPCS major
     * (valid for the current PHP version).
     *
     * @param bool $addMaster    Whether or not `dev-master` should be added to the version array (providing
     *                           it supports the current PHP version).
     *                           Defaults to `false`.
     * @param bool $addNextMajor Whether or not the development branch for the next PHPCS major should be
     *                           added to the version array (providing it supports the current PHP version).
     *                           Defaults to `false`.
     *                           Note: if `true`, the version will be returned in a Composer usable format.
     *
     * @return array Numerically indexed array with PHPCS version identifiers as values.
     */
    public static function getHighLowEachMajor($addMaster = false, $addNextMajor = false)
    {
        $versions  = self::getSupportedVersions();
        $versions2 = array();
        $versions3 = array();

        if (empty($versions) === false) {
            $versions2 = array_filter(
                $versions,
                function ($v) {
                    return $v[0] === '2';
                }
            );
            $versions3 = array_filter(
                $versions,
                function ($v) {
                    return $v[0] === '3';
                }
            );
        }

        $selection = array();
        if (empty($versions2) === false) {
            $selection[] = min($versions2);
            $selection[] = max($versions2);
        }

        if (empty($versions3) === false) {
            $selection[] = min($versions3);
            $selection[] = max($versions3);
        }

        if ($addMaster === true) {
            $selection[] = self::MASTER;
        }

        if ($addNextMajor === true && self::isNextMajorSupported()) {
            $selection[] = self::NEXT_MAJOR;
        }

        return $selection;
    }

    /**
     * Get a random PHPCS version which is valid for the current PHP version.
     *
     * @param bool $inclMaster    Whether or not `dev-master` should be included in the array to pick
     *                            the version from (providing it supports the current PHP version).
     *                            Defaults to `false`.
     * @param bool $inclNextMajor Whether or not the development branch for the next PHPCS major should be included
     *                            in the array to pick the version (providing it supports the current PHP version).
     *                            Defaults to `false`.
     *                            Note: if `true`, the version will be returned in a Composer usable format.
     *
     * @return string
     */
    public static function getRandom($inclMaster = false, $inclNextMajor = false)
    {
        $versions = self::getSupportedVersions();

        if ($inclMaster === true) {
            $versions[self::MASTER] = self::MASTER;
        }

        if ($inclNextMajor === true && self::isNextMajorSupported()) {
            $versions[self::NEXT_MAJOR] = self::NEXT_MAJOR;
        }

        return array_rand($versions);
    }

    /**
     * Convert a versions array to an array suitable for use as a PHPUnit dataprovider.
     *
     * @param array $versions Array with PHPCS version numbers as values.
     *
     * @return array Array of PHPCS version identifiers in a format usable for a test data provider.
     */
    public static function toDataprovider($versions)
    {
        if (is_array($versions) === false || $versions === array()) {
            throw new RuntimeException('The versions parameter must be a non-empty array.');
        }

        $data = array();
        foreach ($versions as $version) {
            $data['phpcs ' . $version] = array(
                'phpcsVersion' => $version,
            );
        }

        return $data;
    }

    /**
     * Retrieve an array with PHPCS versions valid for the current PHP version.
     *
     * @return array Array with PHPCS version identifiers as both keys and values.
     */
    public static function getSupportedVersions()
    {
        /*
         * Adjust the list of available versions based on the PHP version on which the tests are run.
         */
        switch (\CLI_PHP_MINOR) {
            case '5.3':
                $versions = array_filter(
                    self::$allPhpcsVersions,
                    function ($version) {
                        // PHPCS 2.9.2 is the highest version still supporting PHP 5.3.
                        return version_compare($version, '2.9.2', '<=');
                    }
                );
                break;

            case '7.2':
                $versions = array_filter(
                    self::$allPhpcsVersions,
                    function ($version) {
                        // PHPCS 2.9.2 is the first PHPCS version with runtime support for PHP 7.2.
                        return version_compare($version, '2.9.2', '>=');
                    }
                );
                break;

            case '7.3':
                $versions = array_filter(
                    self::$allPhpcsVersions,
                    function ($version) {
                        // PHPCS 3.3.1 is the first PHPCS version with runtime support for PHP 7.3.
                        return version_compare($version, '3.3.1', '>=');
                    }
                );
                break;

            case '7.4':
                $versions = array_filter(
                    self::$allPhpcsVersions,
                    function ($version) {
                        // PHPCS 3.5.0 is the first PHPCS version with runtime support for PHP 7.4.
                        return version_compare($version, '3.5.0', '>=');
                    }
                );
                break;

            case '8.0':
                $versions = array_filter(
                    self::$allPhpcsVersions,
                    function ($version) {
                        // PHPCS 3.5.7 is the first PHPCS version with runtime support for PHP 8.0.
                        return version_compare($version, '3.5.7', '>=');
                    }
                );
                break;

            case '8.1':
                $versions = array_filter(
                    self::$allPhpcsVersions,
                    function ($version) {
                        // PHPCS 3.6.1 is the first PHPCS version with runtime support for PHP 8.1.
                        return version_compare($version, '3.6.1', '>=');
                    }
                );
                break;

            case '8.2':
                $versions = array_filter(
                    self::$allPhpcsVersions,
                    function ($version) {
                        // PHPCS 3.6.1 is the first PHPCS version with runtime support for PHP 8.2.
                        return version_compare($version, '3.6.1', '>=');
                    }
                );
                break;

            case '8.3':
                /*
                 * At this point in time, it is unclear as of which PHPCS version PHP 8.2 will be supported.
                 * In other words: tests should only use dev-master/4.x when on PHP 8.2 for the time being.
                 */
                $versions = array();
                break;

            default:
                $versions = self::$allPhpcsVersions;
                break;
        }

        return $versions;
    }

    /**
     * Determine if the current PHP version is supported on the "next major" branch of PHPCS.
     *
     * @return bool
     */
    public static function isNextMajorSupported()
    {
        return version_compare(\CLI_PHP_MINOR, '7.2', '>=');
    }

    /**
     * Retrieve an array of the PHPCS native standards which are included in a particular PHPCS version.
     *
     * @param string $version PHPCS version number.
     *
     * @return array Numerically indexed array of standards, natural sort applied.
     */
    public static function getStandards($version)
    {
        if (
            is_string($version) === false
            || (isset(self::$allPhpcsVersions[$version]) === false
            && $version !== self::MASTER
            && $version !== self::NEXT_MAJOR)
        ) {
            throw new RuntimeException('The version parameter must be a valid PHPCS version number as a string.');
        }

        $standards = array(
            'PEAR',
            'PSR1',
            'PSR2',
            'Squiz',
            'Zend',
        );

        if ($version !== self::NEXT_MAJOR) {
            // The MySource standard is available in PHPCS 2.x and 3.x, but will be removed in 4.0.
            $standards[] = 'MySource';
        }

        if (
            $version !== self::MASTER
            && $version !== self::NEXT_MAJOR
            && version_compare($version, '3.0.0', '<')
        ) {
            // The PHPCS standard was available in PHPCS 2.x, but has been removed in 3.0.
            $standards[] = 'PHPCS';
        }

        if (
            $version === self::MASTER
            || $version === self::NEXT_MAJOR
            || version_compare($version, '3.3.0', '>=')
        ) {
            // The PSR12 standard is available since PHPCS 3.3.0.
            $standards[] = 'PSR12';
        }

        sort($standards, \SORT_NATURAL);

        return $standards;
    }
}
