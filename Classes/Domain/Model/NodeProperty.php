<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Model;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 */
class NodeProperty
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $typePreset;

    /**
     * @param $name
     * @param $typePreset
     */
    private function __construct(string $name, string $typePreset)
    {
        $this->name = $name;
        $this->typePreset = $typePreset;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPreset(): string
    {
        return $this->typePreset;
    }

    /**
     * @param string $cliArgument
     * @return static
     */
    public static function fromCliArgument(string $cliArgument): self
    {
        $parts = explode(':', $cliArgument);
        $name = trim($parts[0]);
        $typePreset = isset($parts[1]) ? trim($parts[1]) : 'default';
        return new static($name, $typePreset);
    }
}
