<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Specification;

use Flowpack\SiteKickstarter\Domain\Specification\ChildNodeCollectionSpecification;
use Flowpack\SiteKickstarter\Domain\Specification\NodePropertySpecificationCollection;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\FlowPackageInterface;

/**
 * @Flow\Proxy(false)
 */
class NodeTypeSpecification
{
    /**
     * @var FlowPackageInterface
     */
    protected $package;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string[]
     */
    protected $nameParts;

    /**
     * @var NodePropertySpecificationCollection
     */
    protected $nodeProperties;

    /**
     * @var ChildNodeCollectionSpecification
     */
    protected $childNodes;

    /**
     * NodeType constructor.
     * @param FlowPackageInterface $package
     * @param string $name
     * @param ChildNodeCollectionSpecification $childNodes
     * @param NodePropertySpecificationCollection $nodeProperties
     */
    private function __construct(FlowPackageInterface $package, string $name, ChildNodeCollectionSpecification $childNodes, NodePropertySpecificationCollection $nodeProperties)
    {
        $this->package = $package;
        $this->name = $name;
        $this->nameParts = explode('.', $name);
        $this->childNodes = $childNodes;
        $this->nodeProperties = $nodeProperties;
    }

    /**
     * @param FlowPackageInterface $package
     * @param string $name
     * @param array $childnodeCliArguments
     * @param array $propertCliArguments
     * @return static
     */
    public static function fromCliArguments(FlowPackageInterface $package, string $name, array $childnodeCliArguments, array $propertCliArguments):self {
        $childNodes = ChildNodeCollectionSpecification::fromCliArguments($childnodeCliArguments);
        $properties = NodePropertySpecificationCollection::fromCliArguments($propertCliArguments);

        return new static($package, $name, $childNodes, $properties);
    }

    /**
     * @return FlowPackageInterface
     */
    public function getPackage(): FlowPackageInterface
    {
        return $this->package;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
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
     * @return string
     */
    public function getFullName(): string
    {
        return $this->package->getPackageKey() . ':' . implode('.', $this->nameParts);
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return end($this->nameParts);
    }

    public function getFusionFilePath(): string
    {
        return $this->getLocalPackagePath()
            . 'NodeTypes/' . implode('/', $this->nameParts) . '/'
            . $this->getShortName() . '.fusion';
    }

    public function getYamlFilePath(): string
    {
        return $this->getLocalPackagePath()
            . 'NodeTypes/' . implode('/', $this->nameParts) . '/'
            . $this->getShortName() . '.yaml';
    }

    public function getLocalPackagePath(): string
    {
        $path = $this->package->getPackagePath();
        if (substr($path, 0, strlen(FLOW_PATH_ROOT)) == FLOW_PATH_ROOT) {
            $path = substr($path, strlen(FLOW_PATH_ROOT));
        }
        return $path;
    }
}
