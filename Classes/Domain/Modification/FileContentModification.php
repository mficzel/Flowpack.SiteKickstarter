<?php

declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Modification;

use Neos\Utility\Files;

class FileContentModification implements ModificationIterface
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $content;

    public function __construct(string $filePath, string $content)
    {
        $this->filePath = $filePath;
        $this->content = $content;
    }

    /**
     * @return bool
     */
    public function isForceRequired(): bool
    {
        return false;
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
        return sprintf("Ensure file %s contains '%s'", $path, $this->content);
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

        if (!file_exists($this->filePath)) {
            file_put_contents($this->filePath, $this->content);
        } else {
            $previousContent = file_get_contents($this->filePath);
            if ($previousContent === false) {
                throw new \Exception(sprintf('File %s could not be read', $this->filePath));
            }
            if (strpos($previousContent, $this->content) === false) {
                file_put_contents($this->filePath, $previousContent . PHP_EOL . $this->content);
            }
        }
    }
}
