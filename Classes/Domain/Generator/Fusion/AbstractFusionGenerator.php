<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Generator\Fusion;

use Neos\Flow\Annotations as Flow;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;
use Flowpack\SiteKickstarter\Domain\Modification\WholeFileModification;
use Flowpack\SiteKickstarter\Domain\Model\NodeType;
use Flowpack\SiteKickstarter\Domain\Model\NodeProperty;
use Flowpack\SiteKickstarter\Domain\Model\NodePropertyCollection;

abstract class AbstractFusionGenerator
{
    /**
     * @var array
     * @Flow\InjectConfiguration(path="fusionPropertyAccesingTemplates")
     */
    protected $propertyAccesingTemplates;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="fusionPropertyRenderingAfxTemplates")
     */
    protected $propertyRenderingAfxTemplates;

    /**
     * @param NodeType $nodeType
     * @return ModificationIterface
     */
    abstract public function generate(NodeType $nodeType): ModificationIterface;

    /**
     * @param NodeType $nodeType
     * @return array
     */
    protected function createPropertyAccessors(NodeType $nodeType): string
    {
        $properties = [];

        /**
         * @var NodeProperty $nodeProperty
         */
        foreach ($nodeType->getNodeProperties() as $nodeProperty) {
            $renderingTemplate = $this->propertyAccesingTemplates[$nodeProperty->getPreset()] ?? $this->propertyAccesingTemplates['default'];
            $properties[] = $nodeProperty->getName() . ' = ' . str_replace('__name__', $nodeProperty->getName(), trim($renderingTemplate));
        }

        return implode(PHP_EOL, $properties);
    }

    /**
     * @param NodeType $nodeType
     * @return string
     */
    protected function createAfxRenderer(NodeType $nodeType): string
    {
        $name = $nodeType->getFullName();
        $properties = $this->indent($this->createPropertiesList($nodeType));

        return <<<EOT
            <div>
                <h4>$name</h4>
                $properties
            </div>
            EOT;
    }

    /**
     * @param NodeType $nodeType
     * @return string
     */
    protected function createPropertiesList(NodeType $nodeType): string
    {
        $properties = '';

        /**
         * @var NodeProperty $nodeProperty
         */
        foreach ($nodeType->getNodeProperties() as $nodeProperty) {
            $properties .= $this->createPropertyRenderer($nodeProperty);
        }

        $properties = $this->indent($properties);

        return <<<EOT
            <dl>
                $properties
            </dl>
            EOT;
    }

    /**
     * @param NodeProperty $nodeProperty
     * @return string
     */
    protected function createPropertyRenderer(NodeProperty $nodeProperty): string
    {
        $renderingTemplate = $this->propertyRenderingAfxTemplates[$nodeProperty->getPreset()] ?? $this->propertyRenderingAfxTemplates['default'];
        $name = $nodeProperty->getName();
        $propRenderer = $this->indent(str_replace('__name__', $nodeProperty->getName(), trim($renderingTemplate)));
        return <<<EOT
            <dt>$name</dt>
            <dd>
                $propRenderer
            </dd>
            EOT;
    }

    /**
     * @param string $text
     * @param int $indentation
     * @return string
     */
    protected function indent(string $text, int $indentation = 4): string
    {
        $indent = str_pad('', $indentation);
        return implode(PHP_EOL . $indent, explode(PHP_EOL, $text));
    }

}
