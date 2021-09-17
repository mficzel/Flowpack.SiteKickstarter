<?php

namespace Flowpack\SiteKickstarter\Domain\Generator;

use Neos\Flow\Package\FlowPackageInterface;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;
use Flowpack\SiteKickstarter\Domain\Specification\NodeTypeSpecification;

abstract class AbstractGenerator implements GeneratorInterface
{

    /**
     * @param FlowPackageInterface $package
     * @return string
     */
    public function getRelativePackagePath(FlowPackageInterface $package): string
    {
        $path = $package->getPackagePath();
        if (substr($path, 0, strlen(FLOW_PATH_ROOT)) == FLOW_PATH_ROOT) {
            $path = substr($path, strlen(FLOW_PATH_ROOT));
        }
        return $path;
    }

    /**
     * @param FlowPackageInterface $package
     * @param NodeTypeSpecification $nodeType
     * @return ModificationIterface
     */
    abstract public function generate(FlowPackageInterface $package, NodeTypeSpecification $nodeType): ModificationIterface;
}
