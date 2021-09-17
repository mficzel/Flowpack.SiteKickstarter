<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Specification;

use Neos\Flow\Annotations as Flow;

/**
 * Class NodeTypeNameSpecificationCollection
 * @package Flowpack\SiteKickstarter\Domain\Specification
 * @Flow\Proxy(false)
 */
class SuperTypesSpecification implements \IteratorAggregate
{

    /**
     * @var NameSpecification
     */
    protected $primaryNameSpecification;

    /**
     * @var NameSpecification[]
     */
    protected $nameSpecifications;

    /**
     * NodeTypeNameSpecificationCollection constructor.
     * @param NameSpecification $primaryNameSpecification
     * @param NameSpecification ...$nameSpecifications
     */
    private function __construct(NameSpecification $primaryNameSpecification, NameSpecification ...$nameSpecifications) {
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
                    return NameSpecification::fromString($name);
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
     * @return NameSpecification
     */
    public function getPrimaryNameSpecification(): NameSpecification
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
