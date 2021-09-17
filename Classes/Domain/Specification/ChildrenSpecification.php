<?php

declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Specification;

use Neos\Flow\Annotations as Flow;

/**
 * @Flow\Proxy(false)
 * @implements \IteratorAggregate<int, ChildSpecification>
 */
class ChildrenSpecification implements \IteratorAggregate
{
    /**
     * @var array<int, ChildSpecification>
     */
    protected $nodeProperties;

    /**
     * @param ChildSpecification ...$nodeProperties
     */
    private function __construct(ChildSpecification ...$nodeProperties)
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
                return ChildSpecification::fromCliArgument($cliArgument);
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
     * @return \ArrayIterator<int, ChildSpecification>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->nodeProperties);
    }
}
