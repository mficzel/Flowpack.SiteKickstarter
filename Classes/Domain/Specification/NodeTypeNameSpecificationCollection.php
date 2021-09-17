<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Specification;

use Neos\Flow\Annotations as Flow;

/**
 * Class NodeTypeNameSpecificationCollection
 * @package Flowpack\SiteKickstarter\Domain\Specification
 * @Flow\Proxy(false)
 */
class NodeTypeNameSpecificationCollection implements \IteratorAggregate
{

    /**
     * @var NodeTypeNameSpecification
     */
    protected $primaryNameSpecification;

    /**
     * @var NodeTypeNameSpecification[]
     */
    protected $nameSpecifications;

    /**
     * NodeTypeNameSpecificationCollection constructor.
     * @param NodeTypeNameSpecification $primaryNameSpecification
     * @param NodeTypeNameSpecification ...$nameSpecifications
     */
    private function __construct(NodeTypeNameSpecification $primaryNameSpecification, NodeTypeNameSpecification ...$nameSpecifications) {
        $this->primaryNameSpecification = $primaryNameSpecification;
        $this->nameSpecifications = array_merge([$primaryNameSpecification], $nameSpecifications);
    }

    /**
     * @param string[] $names
     * @return static
     */
    public static function fromStringArray(array $names): self
    {
        return new static(
            ...array_map(
                function(string $name) {
                    return NodeTypeNameSpecification::fromString($name);
                },
                $names
            )
        );
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->nameSpecifications);
    }

    /**
     * @return NodeTypeNameSpecification
     */
    public function getPrimaryNameSpecification(): NodeTypeNameSpecification
    {
        return $this->primaryNameSpecification;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return ($this->nameSpecifications ? false : true);
    }
}
