<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Command;

/*
 * This file is part of the Flowpack.SiteKickstarter package.
 */

use Flowpack\SiteKickstarter\Domain\Specification\ChildNodeCollectionSpecification;
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
use Flowpack\SiteKickstarter\Domain\Specification\NodePropertySpecificationCollection;
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
        $this->prepareDocumentNodeTypeModifications(
                $package,
                'Page',
                ['Neos.Neos:Document'],
                ['main:content'],
                []
            ),
           $this->prepareDocumentNodeTypeModifications(
                $package,
                'HomePage',
               [$package->getPackageKey() . ':Document.Page'],
                ['main:content'],
                []
           )
        );

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

        $modifications = $this->prepareDocumentNodeTypeModifications(
            $package,
            $nodeType,
            $superTypes,
            $childnode,
            array_merge($property, $this->request->getExceedingArguments())
        );

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

        $modifications = $this->prepareContentNodeTypeModifications(
            $package,
            $nodeType,
            $superTypes,
            $childnode,
            array_merge($property, $this->request->getExceedingArguments())
        );

        $this->executeModifications($modifications, $force);
        $this->outputLine("Done");
    }

    /**
     * @param FlowPackageInterface $package
     * @return ModificationIterface
     */
    protected function createDefaultIncludeModifications(FlowPackageInterface $package): ModificationIterface
    {
        return new ModificationCollection(
            new FileContentModification( $package->getPackagePath() . 'Resources/Private/Fusion/Root.fusion', 'include: ../../../../NodeTypes/**/*')
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
     * @param string[] $superTypes
     * @param string[] $childnodes
     * @param string[] $properties
     * @return ModificationIterface
     */
    protected function prepareDocumentNodeTypeModifications(FlowPackageInterface $package, string $name, array $superTypes, array $childnodes, array $properties, $abstract = false): ModificationIterface
    {
        $nodeTypeSpecification = NodeTypeSpecification::fromCliArguments(
            $package->getPackageKey() . ':' . 'Document.' . $name,
            $superTypes ?? ['Neos.Neos:Document'],
            $childnodes,
            $properties,
            $abstract
        );

        $modifications = new ModificationCollection(
            $this->documentFusionGenerator->generate($package, $nodeTypeSpecification),
            $this->documentNodeTypeGenerator->generate($package, $nodeTypeSpecification),
            $this->createDefaultIncludeModifications($package)
        );
        return $modifications;
    }

    /**
     * @param FlowPackageInterface $package
     * @param string $name
     * @param string[] $superTypes
     * @param string[] $childnodes
     * @param string[] $properties
     * @return ModificationIterface
     */
    protected function prepareContentNodeTypeModifications(FlowPackageInterface $package, string $name, array $superTypes, array $childnodes, array $properties, $abstract = false): ModificationIterface
    {
        $nodeTypeSpecification = NodeTypeSpecification::fromCliArguments(
            $package->getPackageKey() . ':Content.' . $name,
            $superTypes ?? ['Neos.Neos:Content'],
            $childnodes,
            $properties,
            $abstract
        );

        $modifications = new ModificationCollection(
            $this->contentFusionGenerator->generate($package, $nodeTypeSpecification),
            $this->contentNodeTypeGenerator->generate($package,$nodeTypeSpecification),
            $this->createDefaultIncludeModifications($package)
        );
        return $modifications;
    }
}
