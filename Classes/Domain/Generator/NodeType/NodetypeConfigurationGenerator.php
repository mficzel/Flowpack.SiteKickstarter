<?php

declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Generator\NodeType;

use Flowpack\SiteKickstarter\Domain\Generator\AbstractGenerator;
use Flowpack\SiteKickstarter\Domain\Specification\NameSpecification;
use Neos\Flow\Annotations as Flow;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;
use Flowpack\SiteKickstarter\Domain\Modification\CreateFileModification;
use Flowpack\SiteKickstarter\Domain\Specification\NodeTypeSpecification;
use Flowpack\SiteKickstarter\Domain\Specification\PropertySpecification;
use Flowpack\SiteKickstarter\Domain\Specification\ChildSpecification;
use Neos\Flow\Package\FlowPackageInterface;
use Symfony\Component\Yaml\Yaml;

class NodetypeConfigurationGenerator extends AbstractGenerator
{

    /**
     * @Flow\InjectConfiguration(path="nodeTypePropertyTemplates")
     * @var array<string, string>
     */
    protected $propertyTemplates;

    /**
     * @Flow\InjectConfiguration(path="nodeTypeChildNodeTemplates")
     * @var array<string, string>
     */
    protected $childNodeTemplates;

    /**
     * @param FlowPackageInterface $package
     * @param NodeTypeSpecification $nodeType
     * @return ModificationIterface
     */
    public function generate(FlowPackageInterface $package, NodeTypeSpecification $nodeType): ModificationIterface
    {
        $nodeTypeConfiguration = [
            'superTypes' => array_reduce(
                iterator_to_array($nodeType->getSuperTypes()),
                function (array $carry, NameSpecification $superType) {
                    $carry[$superType->getFullName()] = true;
                    return $carry;
                },
                []
            ),
            'ui' => [
                'label' => $nodeType->getName()->getNickname(),
                'icon' => 'rocket'
            ]
        ];

        if (!$nodeType->getChildNodes()->isEmpty()) {

            /**
             * @var ChildSpecification $childNode
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
             * @var PropertySpecification $nodeProperty
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

        $yaml = Yaml::dump([$nodeType->getName()->getFullName() => $nodeTypeConfiguration], 99);
        $packagePath = $this->getRelativePackagePath($package);

        $nodeTypeConfigurationAsString = <<<EOT
            #
            # Definition of NodeType {$nodeType->getName()->getFullName()}
            # that is rendered by {$packagePath}{$nodeType->getFusionRenderPath()}
            #
            # @see https://docs.neos.io/cms/manual/content-repository/nodetype-definition
            # @see https://docs.neos.io/cms/manual/content-repository/nodetype-properties
            #
            {$yaml}
            EOT;

        return new CreateFileModification(
            $packagePath . $nodeType->getNodeTypeConfigurationPath(),
            $nodeTypeConfigurationAsString
        );
    }
}
