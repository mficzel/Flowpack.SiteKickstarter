<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Generator\NodeType;

use Neos\Flow\Annotations as Flow;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;
use Flowpack\SiteKickstarter\Domain\Modification\WholeFileModification;
use Flowpack\SiteKickstarter\Domain\Model\NodeType;
use Flowpack\SiteKickstarter\Domain\Model\NodeProperty;
use Flowpack\SiteKickstarter\Domain\Model\NodePropertyCollection;

use Symfony\Component\Yaml\Yaml;

abstract class AbstractNodeTypeGenerator
{
    /**
     * @var array
     * @Flow\InjectConfiguration(path="nodeTypePropertyTemplates")
     */
    protected $propertyTemplates;

    /**
     * @param NodeType $nodeType
     * @return array
     */
    abstract function getSuperTypes(NodeType $nodeType): array;

    /**
     * @param NodeType $nodeType
     * @return ModificationIterface
     */
    public function generate(NodeType $nodeType): ModificationIterface
    {
        $nodeTypeConfiguration = [
            'superTypes' => $this->getSuperTypes($nodeType),
            'ui' => [
                'label' => $nodeType->getShortName(),
                'icon' => 'rocket'
            ]
        ];

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
             * @var NodeProperty $nodeProperty
             */
            foreach ($nodeType->getNodeProperties() as $nodeProperty) {
                $propertyTemplate = $this->propertyTemplates[$nodeProperty->getPreset()] ?? $this->propertyTemplates['default'];
                $nodeTypeConfiguration['properties'][$nodeProperty->getName()] = Yaml::parse(
                    str_replace(
                        ['__name__', '__type__', '__group__'],
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
