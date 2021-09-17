<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Specification;

use Neos\Flow\Annotations as Flow;

/**
 * Class NodeTypeSpecification
 * @package Flowpack\SiteKickstarter\Domain\Specification
 * @Flow\Proxy(false)
 */
class NodeTypeSpecification
{
    /**
     * @var NameSpecification
     */
    protected $name;

    /**
     * @var SuperTypesSpecification
     */
    protected $superTypes;

    /**
     * @var PropertiesSpecification
     */
    protected $nodeProperties;

    /**
     * @var ChildrenSpecification
     */
    protected $childNodes;

    /**
     * @var bool
     */
    protected $abstract;

    /**
     * NodeType constructor.
     * @param NameSpecification $name
     * @param SuperTypesSpecification $superTypes
     * @param ChildrenSpecification $childNodes
     * @param PropertiesSpecification $nodeProperties
     */
    private function __construct(NameSpecification $name, SuperTypesSpecification $superTypes, ChildrenSpecification $childNodes, PropertiesSpecification $nodeProperties, bool $abstract)
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
        $nodeTypeName = NameSpecification::fromString($name);
        $nodeSuperTypes = SuperTypesSpecification::fromStringArray($superTypes);
        $childNodes = ChildrenSpecification::fromCliArguments($childnodeCliArguments);
        $properties = PropertiesSpecification::fromCliArguments($propertCliArguments);
        return new static($nodeTypeName, $nodeSuperTypes,$childNodes, $properties, $abstract);
    }

    /**
     * @return NameSpecification
     */
    public function getName(): NameSpecification
    {
        return $this->name;
    }

    /**
     * @return NameSpecification
     */
    public function getPrimarySuperTypeName(): NameSpecification
    {
        return $this->superTypes->getPrimaryNameSpecification();
    }

    /**
     * @return SuperTypesSpecification
     */
    public function getSuperTypes(): SuperTypesSpecification
    {
        return $this->superTypes;
    }

    /**
     * @return ChildrenSpecification
     */
    public function getChildNodes(): ChildrenSpecification
    {
        return $this->childNodes;
    }

    /**
     * @return PropertiesSpecification
     */
    public function getNodeProperties(): PropertiesSpecification
    {
        return $this->nodeProperties;
    }

    /**
     * @return string
     */
    public function getNodeTypeConfigurationPath(): string
    {
        return 'NodeTypes'
            . '/' . implode('/', $this->getName()->getLocalNameParts())
            . '/' . $this->getName()->getNickname() . '.fusion';
    }

    /**
     * @return string
     */
    public function getFusionRenderPath(): string
    {
        return 'Resources/Private/Fusion'
            . '/' . implode('/', $this->getName()->getLocalNameParts())
            . '/' . $this->getName()->getNickname() . '.yaml';
    }

    /**
     * @return bool
     */
    public function isAbstract(): bool
    {
        return $this->abstract;
    }
}
