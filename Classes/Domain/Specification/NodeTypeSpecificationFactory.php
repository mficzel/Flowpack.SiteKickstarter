<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Specification;

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Service\NodeTypeManager;
use Neos\Flow\Package\FlowPackageInterface;

class NodeTypeSpecificationFactory
{

    /**
     * @var NodeTypeManager
     * @Flow\Inject
     */
    protected $nodeTypeManager;

    /**
     * @param FlowPackageInterface $package
     * @param string $name
     * @param array $superTypes
     * @param array $childnodeCliArguments
     * @param array $propertCliArguments
     * @param bool $abstract
     * @return NodeTypeSpecification
     */
    public function createForPackageAndCliArguments(FlowPackageInterface $package, string $name, array $superTypes, array $childnodeCliArguments, array $propertCliArguments, bool $abstract = false): NodeTypeSpecification
    {

        // prefix nodeTypes with package key
        if (strpos($name, ':') === false) {
            $name = $package->getPackageKey() . ':' . $name;
        }

        // prefix superTypes with package key
        $superTypes = array_map(
            function(string $name) use ($package) {
                if (strpos($name, ':') === false) {
                    return $package->getPackageKey() . ':' . $name;
                } else {
                    return $name;
                }
            },
            $superTypes
        );

        return NodeTypeSpecification::fromCliArguments($name, $superTypes, $childnodeCliArguments, $propertCliArguments, $abstract);
    }

}
