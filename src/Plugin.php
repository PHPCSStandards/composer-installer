<?php

/**
 * This file is part of the Dealerdirect PHP_CodeSniffer Standards
 * Composer Installer Plugin package.
 *
 * @copyright 2016 Dealerdirect B.V.
 * @license MIT
 */

namespace Dealerdirect\Composer\Plugin\Installers\PHPCodeSniffer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\ProcessBuilder;

/**
 * PHP_CodeSniffer standard installation manager.
 *
 * @author Franck Nijhof <f.nijhof@dealerdirect.nl>
 */
class Plugin implements PluginInterface, EventSubscriberInterface
{

    const PACKAGE_TYPE = 'phpcodesniffer-standard';

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var IOInterface
     */
    private $io;

    /**
     * @var array
     */
    private $installedPaths;

    /**
     * @var ProcessBuilder
     */
    private $processBuilder;

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException
     * @throws LogicException
     * @throws RuntimeException
     * @throws ProcessFailedException
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $this->installedPaths = [];

        $this->processBuilder = new ProcessBuilder();
        $this->processBuilder->setPrefix($composer->getConfig()->get('bin-dir') . DIRECTORY_SEPARATOR . 'phpcs');

        $this->loadInstalledPaths();
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ScriptEvents::POST_INSTALL_CMD => [
                ['onDependenciesChangedEvent', 0],
            ],
            ScriptEvents::POST_UPDATE_CMD => [
                ['onDependenciesChangedEvent', 0],
            ],
        ];
    }

    /**
     * Entry point for post install and post update events
     *
     * @throws RuntimeException
     * @throws LogicException
     * @throws ProcessFailedException
     */
    public function onDependenciesChangedEvent()
    {
        if ($this->isPHPCodeSnifferInstalled() === true ) {
            $installPathCleaned = $this->cleanInstalledPaths();
            $installPathUpdated = $this->updateInstalledPaths();

            if ($installPathCleaned === true || $installPathUpdated === true) {
                $this->saveInstalledPaths();
            }
        }
    }

    /**
     * Load all paths from PHP_CodeSniffer into an array
     *
     * @throws RuntimeException
     * @throws LogicException
     * @throws ProcessFailedException
     */
    private function loadInstalledPaths()
    {

        $output = $this->processBuilder
            ->setArguments(['--config-show', 'installed_paths'])
            ->getProcess()
            ->mustRun()
            ->getOutput();

        $phpcsInstalledPaths = str_replace('installed_paths: ', '', $output);
        $phpcsInstalledPaths = trim($phpcsInstalledPaths);

        if ($phpcsInstalledPaths !== '') {
            $this->installedPaths = explode(',', $phpcsInstalledPaths);
        }
    }

    /**
     * Save all coding standard paths back into PHP_CodeSniffer
     *
     * @throws RuntimeException
     * @throws LogicException
     * @throws ProcessFailedException
     */
    private function saveInstalledPaths()
    {
        // By default we delete the installed paths
        $arguments = ['--config-delete', 'installed_paths'];

        // This changes in case we do have installed_paths
        if (count($this->installedPaths) !== 0) {
            $arguments = ['--config-set', 'installed_paths', implode(',', $this->installedPaths)];
        }

        $this->processBuilder
            ->setArguments($arguments)
            ->getProcess()
            ->mustRun();
    }

    /**
     * Iterate trough all known paths and check if they are still valid.
     *
     * If path does not exists, is not an directory or isn't readble, the path is removed from the list.
     *
     * @return bool True if changes where made, false otherwise
     */
    private function cleanInstalledPaths()
    {
        $changes = false;
        foreach ($this->installedPaths as $key => $path) {
            if (file_exists($path) === false || is_dir($path) === false || is_readable($path) === false) {
                unset($this->installedPaths[$key]);
                $changes = true;
            }
        }
        return $changes;
    }

    /**
     * Check all installed packages against the installed paths from PHP_CodeSniffer and add the missing ones.
     *
     * @return bool True if changes where made, false otherwise
     */
    private function updateInstalledPaths()
    {
        $changes = false;
        $codingStandardPackages = $this->getPHPCodingStandardPackages();

        foreach ($codingStandardPackages as $package) {
            $packageInstallPath = $this->composer->getInstallationManager()->getInstallPath($package);
            if (in_array($packageInstallPath, $this->installedPaths, true) === false) {
                $this->installedPaths[] = $packageInstallPath;
                $changes = true;
            }
        }

        return $changes;
    }

    /**
     * Iterates trough Composers' local repository looking for valid Coding Standard packages
     *
     * @return array Composer packages containing coding standard(s)
     */
    private function getPHPCodingStandardPackages()
    {
        $codingStandardPackages = array_filter(
            $this->composer->getRepositoryManager()->getLocalRepository()->getPackages(),
            function (PackageInterface $package) {
                if ($package instanceof AliasPackage) {
                    return false;
                }
                return $package->getType() === Plugin::PACKAGE_TYPE;
            }
        );

        if ($this->composer->getPackage()->getType() === self::PACKAGE_TYPE) {
            $codingStandardPackages[] = $this->composer->getPackage();
        }

        return $codingStandardPackages;
    }

    /**
     * Simple check if PHP_CodeSniffer is installed.
     *
     * @return bool PHP_CodeSniffer is installed
     */
    private function isPHPCodeSnifferInstalled()
    {
        // Check if PHP_CodeSniffer is actually installed
        return (count(
            $this
                ->composer
                ->getRepositoryManager()
                ->getLocalRepository()
                    ->findPackages('squizlabs/php_codesniffer')
        ) !== 0);
    }
}
