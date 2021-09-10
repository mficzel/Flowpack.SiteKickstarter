<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Specification;

use Exception;
use Traversable;

class NodeTypeNameSpecificationCollection implements \IteratorAggregate
{
    /**
     * @var NodeTypeNameSpecification[]
     */
    protected $nameSpecifications;

    /**
     * NodeTypeNameSpecificationCollection constructor.
     * @param NodeTypeNameSpecification ...$nameSpecifications
     */
    private function __construct(NodeTypeNameSpecification ...$nameSpecifications) {
        $this->nameSpecifications = $nameSpecifications;
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
     * @return bool
     */
    public function isEmpty(): bool
    {
        return ($this->nameSpecifications ? false : true);
    }
}
