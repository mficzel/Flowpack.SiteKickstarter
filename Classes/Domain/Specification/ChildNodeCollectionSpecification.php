<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Specification;

use Flowpack\SiteKickstarter\Domain\Specification\ChildNodeSpecification;
use Flowpack\SiteKickstarter\Domain\Specification\NodePropertySpecification;
use Neos\Flow\Annotations as Flow;


/**
 * @Flow\Proxy(false)
 */
class ChildNodeCollectionSpecification implements \IteratorAggregate
{
    /**
     * @var array<int, ChildNodeSpecification>
     */
    protected $nodeProperties;

    /**
     * @param NodePropertySpecification ...$nodeProperties
     */
    private function __construct(ChildNodeSpecification ...$nodeProperties)
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
                return ChildNodeSpecification::fromCliArgument($cliArgument);
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
     * @return \ArrayIterator<int, NodePropertySpecification>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->nodeProperties);
    }
}
