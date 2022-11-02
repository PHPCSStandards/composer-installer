<?php

/**
 * This file is part of the Dealerdirect PHP_CodeSniffer Standards
 * Composer Installer Plugin package.
 *
 * @copyright 2022 PHPCodeSniffer Composer Installer Contributors
 * @license MIT
 */

namespace PHPCSStandards\Composer\Plugin\Installers\PHPCodeSniffer\Tests;

use DirectoryIterator;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use ZipArchive;

/**
 * Helper class to create the Composer artifact zip packages for use in the tests.
 *
 * This class is used in the test `bootstrap.php` file.
 */
class CreateComposerZipArtifacts
{
    /**
     * Default version number to use for fixture packages.
     *
     * This version number can be overruled by setting the `version` key in the fixture package
     * `composer.json` file or by passing the version number explicitly to the
     * `createZipArtifact()` method.
     *
     * @var string
     */
    const FIXTURE_VERSION = '1.0.0';

    /**
     * The full path to the directory to place the zipped artifacts in (including trailing slash).
     *
     * @var string
     */
    private $artifactDir;

    /**
     * List of file subpaths which should be excluded from the zip archives.
     *
     * Note: no need to list files starting with a `.` as those will always be filtered out.
     *
     * @link https://www.php.net/manual/en/recursivedirectoryiterator.getsubpathname.php
     *
     * @var array
     */
    private $excludedFiles = array(
        'composer.lock'    => 'composer.lock',
        'phpcs.xml.dist'   => 'phpcs.xml.dist',
        'phpunit.xml.dist' => 'phpunit.xml.dist',
        'phpunit.xml'      => 'phpunit.xml',
    );

    /**
     * List of file extensions for files which should be excluded from the zip archives.
     *
     * @var array
     */
    private $excludedExtensions = array(
        'md'   => 'md',
        'bak'  => 'bak',
        'orig' => 'orig',
    );

    /**
     * List of top-level directories which should be excluded from the zip archives.
     *
     * Note: no need to list directories starting with a `.` as those will always be filtered out.
     *
     * @var array
     */
    private $excludedDirs = array(
        'bin'    => 'bin',
        'build'  => 'build', // PHPUnit code coverage directory.
        'tests'  => 'tests',
        'vendor' => 'vendor',
    );

    /**
     * Constructor.
     *
     * @param string $artifactDir Full path to the directory to place the sipped artifacts in.
     */
    public function __construct($artifactDir)
    {
        // Make sure the directory has a trailing slash.
        $this->artifactDir = rtrim($artifactDir, '/') . '/';
    }

    /**
     * Delete all zip artifacts from the artifacts directory.
     *
     * @return void
     */
    public function clearOldArtifacts()
    {
        $di = new DirectoryIterator($this->artifactDir);
        foreach ($di as $fileinfo) {
            if ($fileinfo->isFile() && $fileinfo->getExtension() === 'zip') {
                @unlink($fileinfo->getPathname());
            }
        }
    }

    /**
     * Create a zip file of the *current* state of the plugin to be passed to Composer as an artifact.
     *
     * @param string $source  Path to the directory to package up.
     * @param string $version Version number to use for the package.
     *
     * @return void
     */
    public function createPluginArtifact($source, $version)
    {
        $fileName = "dealerdirect-phpcodesniffer-composer-installer-{$version}.zip";
        $this->createZipArtifact($source, \ZIP_ARTIFACT_DIR . $fileName, $version);
    }

    /**
     * Create a zip package artifact for each test fixture.
     *
     * @param string $source The source directory where the fixtures can be found.
     *                       Each subdirectory of this directory will be treated as a
     *                       package to be zipped up.
     *
     * @return void
     */
    public function createFixtureArtifacts($source)
    {
        $di = new DirectoryIterator($source);
        foreach ($di as $fileinfo) {
            if ($fileinfo->isDot() || $fileinfo->isDir() === false) {
                continue;
            }

            $sourcePath   = $fileinfo->getRealPath();
            $composerFile = $sourcePath . '/composer.json';
            if (file_exists($composerFile) === false) {
                throw new RuntimeException(
                    sprintf(
                        'Each fixture MUST contain a composer.json file. File not found in %s',
                        $composerFile
                    )
                );
            }

            $config = json_decode(file_get_contents($composerFile), true);
            if (isset($config['name']) === false) {
                throw new RuntimeException(
                    sprintf('The fixture composer.json file is missing the "name" for the package in %s', $composerFile)
                );
            }

            $targetVersion = self::FIXTURE_VERSION;
            if (isset($config['version'])) {
                $targetVersion = $config['version'];
            }

            $package    = $config['name'];
            $targetFile = str_replace('/', '-', $package) . "-{$targetVersion}.zip";
            $targetPath = $this->artifactDir . $targetFile;

            $this->createZipArtifact($sourcePath, $targetPath, $targetVersion);
        }
    }

    /**
     * Create a zip file of an arbitrary directory and package it for use by Composer.
     *
     * Inspired by https://github.com/composer/package-versions-deprecated/blob/c6522afe5540d5fc46675043d3ed5a45a740b27c/test/PackageVersionsTest/E2EInstallerTest.php#L262-L301
     *
     * @param string $source  Path to the directory to package up.
     * @param string $target  Path to the file where to save the zip.
     * @param string $version Version number to use for the package.
     *
     * @return void
     */
    private function createZipArtifact($source, $target, $version)
    {
        if (file_exists($target) === true) {
            @unlink($target);
        }

        $zip = new ZipArchive();
        $zip->open($target, ZipArchive::CREATE);

        $directoryIterator = new RecursiveDirectoryIterator(
            realpath($source),
            RecursiveDirectoryIterator::SKIP_DOTS
        );

        $filteredFileIterator = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator(
                $directoryIterator,
                function (SplFileInfo $file, $key, RecursiveDirectoryIterator $iterator) {
                    $subPathName = $iterator->getSubPathname();
                    $extension   = $file->getExtension();

                    return (isset($this->excludedFiles[$subPathName]) === false)
                        && isset($this->excludedExtensions[$extension]) === false
                        && isset($this->excludedDirs[$subPathName]) === false
                        && $subPathName[0] !== '.'; // Not a .dot-file.
                }
            ),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($filteredFileIterator as $file) {
            if ($file->isFile() === false) {
                continue;
            }

            /*
             * DO NOT REMOVE!
             * While this block may seem unnecessary, adding an arbitrary version number in the composer.json
             * file **IS** necessary for Composer installs via a repo artifact to actual work.
             * This does not seem to be documented in the Composer documentation, but if the
             * version is not declared in the composer.json of the artifact, the install will fail
             * with a "Package ... has no version defined." exception.
             */
            if ($file->getFilename() === 'composer.json') {
                $contents            = json_decode(file_get_contents($file->getRealPath()), true);
                $contents['version'] = $version;

                $zip->addFromString(
                    'composer.json',
                    json_encode($contents, \JSON_UNESCAPED_SLASHES | \JSON_PRETTY_PRINT)
                );

                continue;
            }

            $zip->addFile(
                $file->getRealPath(),
                str_replace('\\', '/', substr($file->getRealPath(), strlen(realpath($source)) + 1))
            );
        }

        $zip->close();
    }
}
