<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Modification;

use Neos\Utility\Files;

class CreateFileModification implements ModificationIterface
{
    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var bool
     */
    protected $fileExists;

    public function __construct(string $filePath, string $content)
    {
        $this->filePath = $filePath;
        $this->content = $content;
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

        file_put_contents($this->filePath, $this->content);
    }
}
