<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Generator\NodeType;

use Neos\Flow\Annotations as Flow;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;
use Flowpack\SiteKickstarter\Domain\Modification\WholeFileModification;
use Flowpack\SiteKickstarter\Domain\Specification\NodeTypeSpecification;
use Flowpack\SiteKickstarter\Domain\Specification\NodePropertySpecification;
use Flowpack\SiteKickstarter\Domain\Specification\ChildNodeSpecification;

use Symfony\Component\Yaml\Yaml;

abstract class AbstractNodeTypeGenerator
{
    /**
     * @var array
     * @Flow\InjectConfiguration(path="nodeTypePropertyTemplates")
     */
    protected $propertyTemplates;

    /**
     * @var array
     * @Flow\InjectConfiguration(path="nodeTypeChildNodeTemplates")
     */
    protected $childNodeTemplates;

    /**
     * @param NodeTypeSpecification $nodeType
     * @return array
     */
    abstract function getSuperTypes(NodeTypeSpecification $nodeType): array;

    /**
     * @param NodeTypeSpecification $nodeType
     * @return ModificationIterface
     */
    public function generate(NodeTypeSpecification $nodeType): ModificationIterface
    {
        $nodeTypeConfiguration = [
            'superTypes' => $this->getSuperTypes($nodeType),
            'ui' => [
                'label' => $nodeType->getShortName(),
                'icon' => 'rocket'
            ]
        ];

        if (!$nodeType->getChildNodes()->isEmpty()) {

            /**
             * @var ChildNodeSpecification $childNode
             */
            foreach ($nodeType->getChildNodes() as $childNode) {
                $propertyTemplate = $this->childNodeTemplates[$childNode->getPreset()];
                $nodeTypeConfiguration['childNodes'][$childNode->getName()] = Yaml::parse(
                    str_replace(
                        ['__name__', '__preset__', '__group__'],
                        [$childNode->getName(), $childNode->getPreset(), 'default'],
                        $propertyTemplate
                    )
                );
            }
        }

        if (!$nodeType->getNodeProperties()->isEmpty()) {

            $nodeTypeConfiguration['ui']['inspector'] = [
                'groups' => [
                    'default' => [
                        'icon' => 'rocket',
                        'title' => $nodeType->getName(),
                        'tab' => 'default'
                    ]
                ]
            ];

            /**
             * @var NodePropertySpecification $nodeProperty
             */
            foreach ($nodeType->getNodeProperties() as $nodeProperty) {
                $propertyTemplate = $this->propertyTemplates[$nodeProperty->getPreset()] ?? $this->propertyTemplates['default'];
                $nodeTypeConfiguration['properties'][$nodeProperty->getName()] = Yaml::parse(
                    str_replace(
                        ['__name__', '__preset__', '__group__'],
                        [$nodeProperty->getName(), $nodeProperty->getPreset(), 'default'],
                        $propertyTemplate
                    )
                );
            }
        }

        $yaml = Yaml::dump([$nodeType->getFullName() => $nodeTypeConfiguration], 99);
        $nodeTypeConfigurationAsString = <<<EOT
            #
            # Definition of NodeType {$nodeType->getFullName()}
            # that is rendered by {$nodeType->getFusionFilePath()}
            #
            # @see https://docs.neos.io/cms/manual/content-repository/nodetype-definition
            # @see https://docs.neos.io/cms/manual/content-repository/nodetype-properties
            #
            {$yaml}
            EOT;

        return new WholeFileModification(
            $nodeType->getYamlFilePath(),
            $nodeTypeConfigurationAsString
        );
    }
}
