<?php

declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Modification;

use Neos\Utility\Files;
use Neos\Utility\Arrays;
use Symfony\Component\Yaml\Yaml;

class SettingModification implements ModificationIterface
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var mixed
     */
    protected $values;

    /**
     * @var bool
     */
    protected $fileExists;

    public function __construct(string $filePath, string $path, $settings)
    {
        $this->filePath = $filePath;
        $this->path = $path;
        $this->values = $settings;
        $this->fileExists = (file_exists($this->filePath));
    }

    /**
     * @return bool
     */
    public function isForceRequired(): bool
    {
        return $this->fileExists;
    }

    /**
     * @return string
     */
    public function getAbstract(): string
    {
        $path = $this->filePath;
        if (substr($this->filePath, 0, strlen(FLOW_PATH_ROOT)) == FLOW_PATH_ROOT) {
            $path = substr($path, strlen(FLOW_PATH_ROOT));
        }

        if ($this->fileExists) {
            return sprintf("Overwrite file %s", $path);
        } else {
            return sprintf("Create file %s", $path);
        }
    }

    /**
     * @param bool $force
     * @throws \Neos\Utility\Exception\FilesException
     */
    public function apply(bool $force = false): void
    {
        if (!$force && $this->isForceRequired()) {
            throw new \Exception('Force is required');
        }

        $dirname = dirname($this->filePath);

        if (!file_exists($dirname)) {
            Files::createDirectoryRecursively($dirname);
        }

        if (file_exists($this->filePath)) {
            $existingConfiguration = Yaml::parseFile($this->filePath);
            $mergedConfiguration = Arrays::setValueByPath($existingConfiguration, $this->path, $this->values);
            file_put_contents($this->filePath, YAML::dump($mergedConfiguration, 10));
        } else {
            $configuration = Arrays::setValueByPath([], $this->path, $this->values);
            file_put_contents($this->filePath, YAML::dump($configuration, 10));
        }
    }
}
