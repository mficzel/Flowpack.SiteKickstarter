<?php

declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Generator\Fusion;

use Flowpack\SiteKickstarter\Domain\Specification\NodeTypeSpecification;
use Flowpack\SiteKickstarter\Domain\Modification\CreateFileModification;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;
use Neos\Flow\Package\FlowPackageInterface;

class InheritedFusionGenerator extends AbstractFusionGenerator
{

    /**
     * @param FlowPackageInterface $package
     * @param NodeTypeSpecification $nodeType
     * @return ModificationIterface
     */
    public function generate(FlowPackageInterface $package, NodeTypeSpecification $nodeType): ModificationIterface
    {
        $packagePath = $this->getRelativePackagePath($package);

        $prototype = <<<EOT
            #
            # The rendering of NodeType {$nodeType->getName()->getFullName()}
            # as configured in {$packagePath}{$nodeType->getNodeTypeConfigurationPath()}
            # is inherited from {$nodeType->getPrimarySuperTypeName()->getFullName()}
            #
            # @see https://docs.neos.io/cms/manual/rendering
            #
            prototype({$nodeType->getName()->getFullName()}) < prototype({$nodeType->getPrimarySuperTypeName()->getFullName()})
            EOT;

        return new CreateFileModification(
            $packagePath . $nodeType->getFusionRenderPath(),
            $prototype
        );
    }
}
