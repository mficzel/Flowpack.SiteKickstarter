<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Command;

/*
 * This file is part of the Flowpack.SiteKickstarter package.
 */

use Flowpack\SiteKickstarter\Domain\Generator\Fusion\InheritedFusionGenerator;
use Flowpack\SiteKickstarter\Domain\Generator\GeneratorInterface;
use Flowpack\SiteKickstarter\Domain\Specification\ChildrenSpecification;
use Flowpack\SiteKickstarter\Domain\Modification\FileContentModification;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Package\FlowPackageInterface;
use Neos\Flow\Package\PackageManager;
use Neos\Flow\Package;
use Flowpack\SiteKickstarter\Domain\Generator\Fusion\ContentFusionGenerator;
use Flowpack\SiteKickstarter\Domain\Generator\Fusion\DocumentFusionGenerator;
use Flowpack\SiteKickstarter\Domain\Generator\NodeType\ContentNodeTypeGenerator;
use Flowpack\SiteKickstarter\Domain\Generator\NodeType\DocumentNodeTypeGenerator;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationCollection;
use Flowpack\SiteKickstarter\Domain\Specification\PropertiesSpecification;
use Flowpack\SiteKickstarter\Domain\Specification\NodeTypeSpecification;

/**
 * @Flow\Scope("singleton")
 */
class KickstartCommandController extends CommandController
{

    /**
     * @var PackageManager
     * @Flow\Inject
     */
    protected $packageManager;

    /**
     * @var ContentFusionGenerator
     * @Flow\Inject
     */
    protected $contentFusionGenerator;

    /**
     * @var DocumentFusionGenerator
     * @Flow\Inject
     */
    protected $documentFusionGenerator;

    /**
     * @var ContentNodeTypeGenerator
     * @Flow\Inject
     */
    protected $contentNodeTypeGenerator;

    /**
     * @var DocumentNodeTypeGenerator
     * @Flow\Inject
     */
    protected $documentNodeTypeGenerator;

    /**
     * @var InheritedFusionGenerator
     * @Flow\Inject
     */
    protected $inheritedFusionGenerator;

    /**
     * @param string $packageKey
     */
    public function sitepackageCommand(string $packageKey)
    {
        $this->packageManager->createPackage($packageKey, [
            'type' => 'neos-site',
            "require" => [
                "neos/neos" => "*"
            ],
            "suggest" => [
                "neos/seo" => "*"
            ]
        ]);

        $this->outputLine(sprintf("Package %s was sucessfully created", $packageKey));

        $package = $this->getFlowPackage($packageKey);

        $modifications = new ModificationCollection(
            $this->prepareNodeTypeModifications(
                $package,
                'Document',
                ['Neos.Neos:Document'],
                ['main:content'],
                [],
                true,
                [$this->documentNodeTypeGenerator]
            ),
            $this->prepareNodeTypeModifications(
                $package,
                'Shortcut',
                [$package->getPackageKey() . ':Document', 'Neos.Neos:Shortcut'],
                ['main:content'],
                [],
                false,
                [$this->documentNodeTypeGenerator]
            ),
            $this->prepareNodeTypeModifications(
                $package,
                'Page',
                ['Neos.Neos:Document'],
                ['main:content'],
                [],
                false,
                [$this->documentNodeTypeGenerator, $this->documentFusionGenerator]
            ),
           $this->prepareNodeTypeModifications(
                $package,
                'HomePage',
               [$package->getPackageKey() . ':Document.Page'],
                ['main:content'],
                [],
               false,
               [$this->documentNodeTypeGenerator, $this->inheritedFusionGenerator]
           )
        );

        $modifications = $this->addDefaultIncludeModifications($package, $modifications);

        $this->executeModifications($modifications, false);
        $this->outputLine("Done");
    }

    /**
     * @param string $packageKey
     * @param string $nodeType
     * @param array $superTypes
     * @param array $childnode
     * @param array $property
     * @param bool $force
     * @throws \Neos\Flow\Cli\Exception\StopCommandException
     */
    public function documentCommand(string $packageKey, string $nodeType, array $superTypes = [], array $childnode = [], array $property = [], bool $force = false) {
        $package = $this->getFlowPackage($packageKey);

        $modifications = $this->prepareNodeTypeModifications(
            $package,
            'Document.' . $nodeType,
            empty($superTypes) ? ['Neos.Neos:Document'] : $superTypes,
            $childnode,
            array_merge($property, $this->request->getExceedingArguments()),
            false,
            [$this->documentFusionGenerator, $this->documentNodeTypeGenerator]
        );

        $modifications = $this->addDefaultIncludeModifications($package, $modifications);

        $this->executeModifications($modifications, $force);
        $this->outputLine("Done");
    }

    /**
     * @param string $packageKey
     * @param string $nodeType
     * @param array $superTypes
     * @param array $childnode
     * @param array $property
     * @param bool $force
     * @throws \Neos\Flow\Cli\Exception\StopCommandException
     */
    public function contentCommand(string $packageKey, string $nodeType, array $superTypes = [], array $childnode = [], array $property = [], bool $force = false) {
        $package = $this->getFlowPackage($packageKey);

        $modifications = $this->prepareNodeTypeModifications(
            $package,
            'Content.' . $nodeType,
            empty($superTypes) ? ['Neos.Neos:Content'] : $superTypes,
            $childnode,
            array_merge($property, $this->request->getExceedingArguments()),
            false,
            [$this->contentFusionGenerator, $this->contentNodeTypeGenerator]
        );

        $modifications = $this->addDefaultIncludeModifications($package, $modifications);

        $this->executeModifications($modifications, $force);
        $this->outputLine("Done");
    }

    /**
     * @param FlowPackageInterface $package
     * @return ModificationIterface
     */
    protected function addDefaultIncludeModifications(FlowPackageInterface $package, ModificationIterface $modification): ModificationIterface
    {
        return new ModificationCollection(
            new FileContentModification( $package->getPackagePath() . 'Resources/Private/Fusion/Root.fusion', 'include: ../../../../NodeTypes/**/*'),
            $modification
        );
    }

    /**
     * @param $packageKey
     * @return FlowPackageInterface
     * @throws \Exception
     */
    protected function getFlowPackage($packageKey): FlowPackageInterface
    {
        $package = $this->packageManager->getPackage($packageKey);
        if ($package instanceof FlowPackageInterface) {
            return $package;
        }
        throw new \Exception("Package has to be a Flow Package Type");
    }

    /**
     * @param ModificationCollection $modifications
     * @param bool $force
     * @throws \Neos\Flow\Cli\Exception\StopCommandException
     */
    protected function executeModifications(ModificationCollection $modifications, bool $force): void
    {
        if (!$force && $modifications->isForceRequired()) {
            $this->outputLine();
            $this->outputLine("The --force argument is required to apply following modifications:");
            $this->outputLine($modifications->getAbstract());
            $this->quit(1);
        }

        $modifications->apply($force);

        $this->outputLine();
        $this->outputLine("The following modifications were applied:");
        $this->outputLine($modifications->getAbstract());
    }

    /**
     * @param FlowPackageInterface $package
     * @param string $name
     * @param array $superTypes
     * @param array $childnodes
     * @param array $properties
     * @param false $abstract
     * @param GeneratorInterface[] $generators
     * @return ModificationIterface
     */
    protected function prepareNodeTypeModifications(FlowPackageInterface $package, string $name, array $superTypes, array $childnodes, array $properties, $abstract = false, array $generators = []): ModificationIterface
    {
        $nodeTypeSpecification = NodeTypeSpecification::fromCliArguments(
            $package->getPackageKey() . ':' . $name,
            $superTypes,
            $childnodes,
            $properties,
            $abstract
        );

        $modifications = new ModificationCollection(
            ...array_map(
                function(GeneratorInterface $generator) use ($package, $nodeTypeSpecification) {
                    return $generator->generate($package, $nodeTypeSpecification);
                },
                $generators
            )
        );

        return $modifications;
    }
}
