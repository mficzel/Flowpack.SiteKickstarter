<?php

namespace Flowpack\SiteKickstarter\Domain\Generator;

use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;
use Flowpack\SiteKickstarter\Domain\Specification\NodeTypeSpecification;
use Neos\Flow\Package\FlowPackageInterface;

interface GeneratorInterface
{
    /**
     * @param FlowPackageInterface $package
     * @param NodeTypeSpecification $nodeType
     * @return ModificationIterface
     */
    public function generate(FlowPackageInterface $package, NodeTypeSpecification $nodeType): ModificationIterface;
}
