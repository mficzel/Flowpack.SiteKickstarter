<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Specification;

use Flowpack\SiteKickstarter\Domain\Specification\ChildSpecification;
use Flowpack\SiteKickstarter\Domain\Specification\PropertySpecification;
use Neos\Flow\Annotations as Flow;


/**
 * @Flow\Proxy(false)
 */
class ChildrenSpecification implements \IteratorAggregate
{
    /**
     * @var array<int, ChildSpecification>
     */
    protected $nodeProperties;

    /**
     * @param PropertySpecification ...$nodeProperties
     */
    private function __construct(ChildSpecification ...$nodeProperties)
    {
        $this->nodeProperties = $nodeProperties;
    }

    /**
     * @param array $cliArguments
     * @return static
     */
    public static function fromCliArguments(array $cliArguments): self
    {
        return new static(...array_map(
            function(string $cliArgument) {
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
     * @return \ArrayIterator<int, PropertySpecification>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->nodeProperties);
    }
}
