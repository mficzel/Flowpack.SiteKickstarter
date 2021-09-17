<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Generator\NodeType;

use Flowpack\SiteKickstarter\Domain\Specification\NodeTypeNameSpecification;
use Neos\Flow\Annotations as Flow;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;
use Flowpack\SiteKickstarter\Domain\Modification\CreateFileModification;
use Flowpack\SiteKickstarter\Domain\Specification\NodeTypeSpecification;
use Flowpack\SiteKickstarter\Domain\Specification\NodePropertySpecification;
use Flowpack\SiteKickstarter\Domain\Specification\ChildNodeSpecification;
use Neos\Flow\Package\FlowPackageInterface;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractNodeTypeGeneratorInterface implements NodeTypeGeneratorInterface
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
     * @param FlowPackageInterface $package
     * @param NodeTypeSpecification $nodeType
     * @return ModificationIterface
     */
    public function generate(FlowPackageInterface $package, NodeTypeSpecification $nodeType): ModificationIterface
    {
        $nodeTypeConfiguration = [
            'superTypes' => array_reduce(
                    iterator_to_array($nodeType->getSuperTypes()),
                    function(array $carry, NodeTypeNameSpecification $superType) {
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

        $yaml = Yaml::dump([$nodeType->getName()->getFullName() => $nodeTypeConfiguration], 99);
        $filePath = $this->getFilePath($package, $nodeType);

        $nodeTypeConfigurationAsString = <<<EOT
            #
            # Definition of NodeType {$nodeType->getName()->getFullName()}
            # that is rendered by {$filePath}.fusion
            #
            # @see https://docs.neos.io/cms/manual/content-repository/nodetype-definition
            # @see https://docs.neos.io/cms/manual/content-repository/nodetype-properties
            #
            {$yaml}
            EOT;


        return new CreateFileModification(
            $filePath.'.yaml',
            $nodeTypeConfigurationAsString
        );
    }

    /**
     * @param FlowPackageInterface $package
     * @param NodeTypeSpecification $nodeType
     * @return string
     */
    protected function getFilePath(FlowPackageInterface $package, NodeTypeSpecification $nodeType): string
    {
        $path = $package->getPackagePath();
        if (substr($path, 0, strlen(FLOW_PATH_ROOT)) == FLOW_PATH_ROOT) {
            $path = substr($path, strlen(FLOW_PATH_ROOT));
        }

        return $path
            . 'NodeTypes/' . implode('/', $nodeType->getName()->getLocalNameParts()) . '/'
            . $nodeType->getName()->getNickname();
    }
}
