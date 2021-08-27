<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Command;

/*
 * This file is part of the Flowpack.SiteKickstarter package.
 */

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
use Flowpack\SiteKickstarter\Domain\Model\NodePropertyCollection;
use Flowpack\SiteKickstarter\Domain\Model\NodeType;

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
     * @param string $nodeType
     * @param bool $force
     * @throws \Neos\Flow\Cli\Exception\StopCommandException
     */
    public function documentCommand(string $packageKey, string $nodeType, bool $force = false) {
        $package = $this->getFlowPackage($packageKey);

        $nodeProperties = NodePropertyCollection::fromCliArguments($this->request->getExceedingArguments());
        $nodeType = NodeType::create($package , 'Document.' . $nodeType, $nodeProperties);

        $modifications = new ModificationCollection(
            $this->documentFusionGenerator->generate($nodeType),
            $this->documentNodeTypeGenerator->generate($nodeType),
            $this->createDefaultIncludeModifications($package)
        );

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

        $this->outputLine("Done");
    }

    /**
     * @param string $packageKey
     * @param string $nodeType
     * @param bool $force
     * @throws \Neos\Flow\Cli\Exception\StopCommandException
     */
    public function contentCommand(string $packageKey, string $nodeType, bool $force = false) {
        $package = $this->getFlowPackage($packageKey);

        $nodeProperties = NodePropertyCollection::fromCliArguments($this->request->getExceedingArguments());
        $nodeType = NodeType::create($package , 'Content.' . $nodeType, $nodeProperties);

        $modifications = new ModificationCollection(
            $this->contentFusionGenerator->generate($nodeType),
            $this->contentNodeTypeGenerator->generate($nodeType),
            $this->createDefaultIncludeModifications($package)
        );

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
}
