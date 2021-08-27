<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Model;

use Neos\Flow\Annotations as Flow;


/**
 * @Flow\Proxy(false)
 */
class NodePropertyCollection implements \IteratorAggregate
{
    /**
     * @var array<int, NodeProperty>
     */
    protected $nodeProperties;

    /**
     * @param NodeProperty ...$nodeProperties
     */
    private function __construct(NodeProperty ...$nodeProperties)
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
                return NodeProperty::fromCliArgument($cliArgument);
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
     * @return \ArrayIterator<int, NodeProperty>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->nodeProperties);
    }
}
