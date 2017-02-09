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
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Symfony\Component\Finder\Finder;
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

    const PHPCS_CONFIG_KEY = 'installed_paths';

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

    public static function run(Event $event)
    {
        $instance = new static();

        $composer = $event->getComposer();
        $instance->composer = $composer;
        $instance->init();
        $instance->onDependenciesChangedEvent();
    }

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

        $this->init();
    }

    public function init()
    {
        $this->installedPaths = [];

        $this->processBuilder = new ProcessBuilder();
        $this->processBuilder->setPrefix($this->composer->getConfig()->get('bin-dir') . DIRECTORY_SEPARATOR . 'phpcs');

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
     * Entry point for post install and post update events.
     *
     * @throws RuntimeException
     * @throws LogicException
     * @throws ProcessFailedException
     */
    public function onDependenciesChangedEvent()
    {
        if ($this->isPHPCodeSnifferInstalled() === true) {
            $installPathCleaned = $this->cleanInstalledPaths();
            $installPathUpdated = $this->updateInstalledPaths();

            if ($installPathCleaned === true || $installPathUpdated === true) {
                $this->saveInstalledPaths();
            }
        }
    }

    /**
     * Load all paths from PHP_CodeSniffer into an array.
     *
     * @throws RuntimeException
     * @throws LogicException
     * @throws ProcessFailedException
     */
    private function loadInstalledPaths()
    {
        if ($this->isPHPCodeSnifferInstalled() === true) {
            $output = $this->processBuilder
                ->setArguments(['--config-show', self::PHPCS_CONFIG_KEY])
                ->getProcess()
                ->mustRun()
                ->getOutput();

            $phpcsInstalledPaths = str_replace(self::PHPCS_CONFIG_KEY . ': ', '', $output);
            $phpcsInstalledPaths = trim($phpcsInstalledPaths);

            if ($phpcsInstalledPaths !== '') {
                $this->installedPaths = explode(',', $phpcsInstalledPaths);
            }
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
        // Check if we found installed paths to set.
        if (count($this->installedPaths) !== 0) {
            $paths = implode(',', $this->installedPaths);
            $arguments = ['--config-set', self::PHPCS_CONFIG_KEY, $paths];
            $configMessage = sprintf(
                'PHP CodeSniffer Config <info>%s</info> <comment>set to</comment> <info>%s</info>',
                self::PHPCS_CONFIG_KEY,
                $paths
            );
        } else {
            // Delete the installed paths if none were found.
            $arguments = ['--config-delete', self::PHPCS_CONFIG_KEY];
            $configMessage = sprintf(
                'PHP CodeSniffer Config <info>%s</info> <comment>delete</comment>',
                self::PHPCS_CONFIG_KEY
            );
        }

        $this->io->write($configMessage);

        $configResult = $this->processBuilder
            ->setArguments($arguments)
            ->getProcess()
            ->mustRun()
            ->getOutput()
        ;
        if ($this->io->isVerbose() && !empty($configResult)) {
            $this->io->write(sprintf('<info>%s</info>', $configResult));
        }
    }

    /**
     * Iterate trough all known paths and check if they are still valid.
     *
     * If path does not exists, is not an directory or isn't readable, the path
     * is removed from the list.
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
     * Check all installed packages against the installed paths from
     * PHP_CodeSniffer and add the missing ones.
     *
     * @return bool True if changes where made, false otherwise
     */
    private function updateInstalledPaths()
    {
        $changes = false;
        $codingStandardPackages = $this->getPHPCodingStandardPackages();

        foreach ($codingStandardPackages as $package) {
            $packageInstallPath = $this->composer->getInstallationManager()->getInstallPath($package);
            $finder = new Finder();
            $finder->files()
              ->ignoreVCS(true)
              ->in($packageInstallPath)
              ->depth('> 1')
              ->depth('< 4')
              ->name('ruleset.xml');
            foreach ($finder as $ruleset) {
                $standardsPath = dirname(dirname($ruleset));
                if (in_array($standardsPath, $this->installedPaths, true) === false) {
                    $this->installedPaths[] = $standardsPath;
                    $changes = true;
                }
            }
        }

        return $changes;
    }

    /**
     * Iterates through Composers' local repository looking for valid Coding
     * Standard packages.
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
