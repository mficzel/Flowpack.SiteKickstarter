<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Specification;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\FlowPackageInterface;

/**
 * @Flow\Proxy(false)
 */
class NodeTypeSpecification
{
    /**
     * @var NodeTypeNameSpecification
     */
    protected $name;

    /**
     * @var NodeTypeNameSpecificationCollection
     */
    protected $superTypes;

    /**
     * @var NodePropertySpecificationCollection
     */
    protected $nodeProperties;

    /**
     * @var ChildNodeCollectionSpecification
     */
    protected $childNodes;

    /**
     * @var bool
     */
    protected $abstract;

    /**
     * NodeType constructor.
     * @param NodeTypeNameSpecification $name
     * @param NodeTypeNameSpecificationCollection $superTypes
     * @param ChildNodeCollectionSpecification $childNodes
     * @param NodePropertySpecificationCollection $nodeProperties
     */
    private function __construct(NodeTypeNameSpecification $name, NodeTypeNameSpecificationCollection $superTypes, ChildNodeCollectionSpecification $childNodes, NodePropertySpecificationCollection $nodeProperties, bool $abstract)
    {
        $this->name = $name;
        $this->superTypes = $superTypes;
        $this->childNodes = $childNodes;
        $this->nodeProperties = $nodeProperties;
        $this->abstract = $abstract;
    }

    /**
     * @param string $name
     * @param string[] $superTypes
     * @param string[] $childnodeCliArguments
     * @param string[] $propertCliArguments
     * @param bool $abstract
     * @return static
     */
    public static function fromCliArguments(string $name, array $superTypes, array $childnodeCliArguments, array $propertCliArguments, bool $abstract = false):self {
        $nodeTypeName = NodeTypeNameSpecification::fromString($name);
        $nodeSuperTypes = NodeTypeNameSpecificationCollection::fromStringArray($superTypes);
        $childNodes = ChildNodeCollectionSpecification::fromCliArguments($childnodeCliArguments);
        $properties = NodePropertySpecificationCollection::fromCliArguments($propertCliArguments);
        return new static($nodeTypeName, $nodeSuperTypes,$childNodes, $properties, $abstract);
    }

    /**
     * @return NodeTypeNameSpecification
     */
    public function getName(): NodeTypeNameSpecification
    {
        return $this->name;
    }

    /**
     * @return NodeTypeNameSpecificationCollection
     */
    public function getSuperTypes(): NodeTypeNameSpecificationCollection
    {
        return $this->superTypes;
    }

    /**
     * @return ChildNodeCollectionSpecification
     */
    public function getChildNodes(): ChildNodeCollectionSpecification
    {
        return $this->childNodes;
    }

    /**
     * @return NodePropertySpecificationCollection
     */
    public function getNodeProperties(): NodePropertySpecificationCollection
    {
        return $this->nodeProperties;
    }

    /**
     * @return bool
     */
    public function isAbstract(): bool
    {
        return $this->abstract;
    }
}
