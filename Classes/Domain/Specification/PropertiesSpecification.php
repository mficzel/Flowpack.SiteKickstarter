<?php

declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Specification;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 * @implements \IteratorAggregate<int, PropertySpecification>
 */
class PropertiesSpecification implements \IteratorAggregate
{
    /**
     * @var array<int, PropertySpecification>
     */
    protected $nodeProperties;

    /**
     * @param PropertySpecification ...$nodeProperties
     */
    private function __construct(PropertySpecification ...$nodeProperties)
    {
        $this->nodeProperties = $nodeProperties;
    }

    /**
     * @param mixed[] $cliArguments
     * @return static
     */
    public static function fromCliArguments(array $cliArguments): self
    {
        return new static(...array_map(
            function (string $cliArgument) {
                return PropertySpecification::fromCliArgument($cliArgument);
            },
            $cliArguments
        ));
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return ($this->nodeProperties ? false : true);
    }

    /**
     * @return \ArrayIterator<int, PropertySpecification>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->nodeProperties);
    }
}
