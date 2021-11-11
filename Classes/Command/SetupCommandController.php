<?php

declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Command;

/*
 * This file is part of the Flowpack.SiteKickstarter package.
 */

use Flowpack\SiteKickstarter\Infrastructure\ImageHandler\ImageHandlerService;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Utility\Arrays;
use Flowpack\SiteKickstarter\Infrastructure\Database\DatabaseConnectionService;
use Neos\Setup\Exception as SetupException;
use Symfony\Component\Yaml\Yaml;

/**
 * @Flow\Scope("singleton")
 */
class SetupCommandController extends CommandController
{

    /**
     * @var string
     * @Flow\InjectConfiguration(package="Neos.Flow", path="core.context")
     */
    protected $flowContext;

    /**
     * @var DatabaseConnectionService
     * @Flow\Inject
     */
    protected $databaseConnectionService;

    /**
     * @var array
     * @Flow\InjectConfiguration(package="Neos.Flow", path="persistence.backendOptions")
     */
    protected $persistenceConfiguration;

    /**
     * @var ImageHandlerService
     * @Flow\Inject
     */
    protected $imageHandlerService;

    /**
     * @var string
     * @Flow\InjectConfiguration(package="Neos.Imagine", path="driver")
     */
    protected $imagineDriver;

    /**
     * Configure the database connection for flow persistence
     *
     * @param string|null $driver Driver
     * @param string|null $host Hostname or IP
     * @param string|null $dbname Database name
     * @param string|null $user Username
     * @param string|null $password Password
     */
    public function databaseCommand(?string $driver = null, ?string $host = null, ?string $dbname = null, ?string $user = null, ?string $password = null): void
    {
        $availableDrivers = $this->databaseConnectionService->getAvailableDrivers();
        if (count($availableDrivers) == 0) {
            $this->outputLine("No supported database driver found");
            $this->quit(1);
        }

        if (is_null($driver)) {
            $driver = $this->output->select(
                sprintf('DB Driver (<info>%s</info>): ', $this->persistenceConfiguration['driver']),
                $availableDrivers,
                $this->persistenceConfiguration['driver']
            );
        }

        if (is_null($host)) {
            $host = $this->output->ask(
                sprintf('Host (<info>%s</info>): ', $this->persistenceConfiguration['host']),
                $this->persistenceConfiguration['host']
            );
        }

        if (is_null($dbname)) {
            $dbname = $this->output->ask(
                sprintf('Database (<info>%s</info>): ', $this->persistenceConfiguration['dbname']),
                $this->persistenceConfiguration['dbname']
            );
        }

        if (is_null($user)) {
            $user = $this->output->ask(
                sprintf('Username (<info>%s</info>): ', $this->persistenceConfiguration['user']),
                $this->persistenceConfiguration['user']
            );
        }

        if (is_null($password)) {
            $password = $this->output->ask(
                sprintf('Password (<info>%s</info>): ', $this->persistenceConfiguration['password']),
                $this->persistenceConfiguration['password']
            );
        }

        $persistenceConfiguration = [
            'driver' => $driver,
            'host' => $host,
            'dbname' => $dbname,
            'user' => $user,
            'password' => $password
        ];

        try {
            $this->databaseConnectionService->verifyDatabaseConnectionWorks($persistenceConfiguration);
        } catch (SetupException $e) {
            $this->outputLine($e->getMessage());
            $this->quit(1);
        }

        $filename = $this->getSettingsFilename();

        $this->outputLine();
        $this->output($this->writeSettings($filename, 'Neos.Flow.persistence.backendOptions',$persistenceConfiguration));
        $this->outputLine();
        $this->outputLine(sprintf("The new database settings are written to file <info>%s</info>", $filename));
    }

    /**
     * @param string|null $driver
     */
    public function imageHandlerCommand(string $driver = null): void
    {
        $availableHandlers = $this->imageHandlerService->getAvailableImageHandlers();

        if (count($availableHandlers) == 0) {
            $this->outputLine("No supported image handler was found.");
            $this->quit(1);
        }

        if (is_null($driver)) {
            $driver = $this->output->select(
                sprintf('Image Handler (<info>%s</info>): ', $this->persistenceConfiguration['driver']),
                $availableHandlers,
                $this->persistenceConfiguration['driver']
            );
        }

        $filename = $this->getSettingsFilename();
        $this->outputLine();
        $this->output($this->writeSettings($filename, 'Neos.Imagine.driver', $driver));
        $this->outputLine();
        $this->outputLine(sprintf("The new imagine hander setting was writtento file <info>%s</info>", $filename));
    }

    /**
     * @return string
     */
    protected function getSettingsFilename(): string
    {
        $filename = 'Configuration/' . $this->flowContext . '/Settings.yaml';
        return $filename;
    }

    /**
     * Write the settings to the given path, existing configuration files are created or modified
     *
     * @param string $filename
     * @param string $path
     * @param mixed $settings The settings for the
     * @return string The added yaml code
     */
    protected function writeSettings(string $filename, string $path, $settings): string
    {
        if (file_exists($filename)) {
            $previousSettings = Yaml::parseFile($filename);
        } else {
            $previousSettings = [];
        }
        $newSettings = Arrays::setValueByPath($previousSettings,$path, $settings);
        file_put_contents($filename, YAML::dump($newSettings, 10, 2));
        return YAML::dump(Arrays::setValueByPath([],$path, $settings), 10, 2);
    }
}
