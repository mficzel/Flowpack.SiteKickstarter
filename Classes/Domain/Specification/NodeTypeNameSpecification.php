<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Specification;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\FlowPackageInterface;

/**
 * @Flow\Proxy(false)
 */
class NodeTypeNameSpecification
{
    /**
     * @var string
     */
    protected $fullName;

    /**
     * @var string
     */
    protected $packageKey;

    /**
     * @var string
     */
    protected $localName;

    /**
     * @var string[]
     */
    protected $localNameParts;

    /**
     * @var string
     */
    protected $nickname;

    /**
     * NodeType constructor.
     * @param string $fullName
     * @param string[] $superTypeNames
     * @param ChildNodeCollectionSpecification $childNodes
     * @param NodePropertySpecificationCollection $nodeProperties
     */
    private function __construct(string $fullName)
    {
        $this->fullName = $fullName;
        list ($this->packageKey, $this->localName) = explode(':', $fullName);
        $this->localNameParts = explode('.', $this->localName);
        $this->nickname = $this->localNameParts[array_key_last($this->localNameParts)];
    }

    public static function fromString(string $name): self
    {
        return new static ($name);
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }

    /**
     * @return string
     */
    public function getPackageKey(): string
    {
        return $this->packageKey;
    }

    /**
     * @return string
     */
    public function getLocalName(): string
    {
        return $this->localName;
    }

    /**
     * @return string[]
     */
    public function getLocalNameParts(): array
    {
        return $this->localNameParts;
    }

    /**
     * @return string
     */
    public function getNickname(): string
    {
        return $this->nickname;
    }
}
