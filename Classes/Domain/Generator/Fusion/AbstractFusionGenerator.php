<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Generator\Fusion;

use Neos\Flow\Annotations as Flow;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;
use Flowpack\SiteKickstarter\Domain\Modification\WholeFileModification;
use Flowpack\SiteKickstarter\Domain\Specification\NodeTypeSpecification;
use Flowpack\SiteKickstarter\Domain\Specification\NodePropertySpecification;
use Flowpack\SiteKickstarter\Domain\Specification\NodePropertySpecificationCollection;

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
     * @param NodeTypeSpecification $nodeType
     * @return ModificationIterface
     */
    abstract public function generate(NodeTypeSpecification $nodeType): ModificationIterface;

    /**
     * @param NodeTypeSpecification $nodeType
     * @return array
     */
    protected function createPropertyAccessors(NodeTypeSpecification $nodeType): string
    {
        $properties = [];

        /**
         * @var NodePropertySpecification $nodeProperty
         */
        foreach ($nodeType->getNodeProperties() as $nodeProperty) {
            $renderingTemplate = $this->propertyAccesingTemplates[$nodeProperty->getPreset()] ?? $this->propertyAccesingTemplates['default'];
            $properties[] = $nodeProperty->getName() . ' = ' . str_replace('__name__', $nodeProperty->getName(), trim($renderingTemplate));
        }

        return implode(PHP_EOL, $properties);
    }

    /**
     * @param NodeTypeSpecification $nodeType
     * @return string
     */
    protected function createAfxRenderer(NodeTypeSpecification $nodeType): string
    {
        $properties = $this->indent($this->createPropertiesList($nodeType));

        return <<<EOT
            <div>
                <h4>{$nodeType->getFullName()}</h4>
                $properties
            </div>
            EOT;
    }

    /**
     * @param NodeTypeSpecification $nodeType
     * @return string
     */
    protected function createPropertiesList(NodeTypeSpecification $nodeType): string
    {
        $properties = '';

        /**
         * @var NodePropertySpecification $nodeProperty
         */
        foreach ($nodeType->getNodeProperties() as $nodeProperty) {
            $properties .= $this->createPropertyRenderer($nodeProperty);
        }

        return <<<EOT
            <dl>
                {$this->indent($properties)}
            </dl>
            EOT;
    }

    /**
     * @param NodePropertySpecification $nodeProperty
     * @return string
     */
    protected function createPropertyRenderer(NodePropertySpecification $nodeProperty): string
    {
        $renderingTemplate = $this->propertyRenderingAfxTemplates[$nodeProperty->getPreset()] ?? $this->propertyRenderingAfxTemplates['default'];
        return <<<EOT
            <dt>{$nodeProperty->getName()}</dt>
            <dd>
                {$this->indent(str_replace('__name__', $nodeProperty->getName(), trim($renderingTemplate)))}
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
