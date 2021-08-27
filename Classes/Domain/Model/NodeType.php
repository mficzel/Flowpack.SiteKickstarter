<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Package\FlowPackageInterface;

/**
 * @Flow\Proxy(false)
 */
class NodeType
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
     * @var NodePropertyCollection
     */
    protected $nodeProperties;

    /**
     * NodeType constructor.
     * @param FlowPackageInterface $package
     * @param string $name
     */
    private function __construct(FlowPackageInterface $package, string $name, NodePropertyCollection $nodeProperties)
    {
        $this->package = $package;
        $this->name = $name;
        $this->nameParts = explode('.', $name);
        $this->nodeProperties = $nodeProperties;
    }

    /**
     * @param FlowPackageInterface $package
     * @param string $name
     * @return static
     */
    public static function create(FlowPackageInterface $package, string $name, NodePropertyCollection $nodeProperties): self
    {
        return new static($package, $name, $nodeProperties);
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
     * @return NodePropertyCollection
     */
    public function getNodeProperties(): NodePropertyCollection
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
        return $this->package->getPackagePath()
            . 'NodeTypes/' . implode('/', $this->nameParts) . '/'
            . $this->getShortName() . '.fusion';
    }

    public function getYamlFilePath(): string
    {
        return $this->package->getPackagePath()
            . 'NodeTypes/' . implode('/', $this->nameParts) . '/'
            . $this->getShortName() . '.yaml';
    }
}
