<?php


namespace Flowpack\SiteKickstarter\Domain\Generator\Fusion;


use Flowpack\SiteKickstarter\Domain\Model\NodeType;
use Flowpack\SiteKickstarter\Domain\Modification\WholeFileModification;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;

class ContentFusionGenerator extends AbstractFusionGenerator
{
    /**
     * @param NodeType $nodeType
     * @return ModificationIterface
     */
    public function generate(NodeType $nodeType): ModificationIterface
    {
        $name = $nodeType->getFullName();
        $accessors = $this->indent($this->createPropertyAccessors($nodeType));
        $renderer = $this->indent($this->createAfxRenderer($nodeType), 8);

        $prototype = <<<EOT
            prototype($name) < prototype(Neos.Neos:ConentComponent) {

                $accessors

                renderer = afx`
                    $renderer
                `
            }
            EOT;

        return new WholeFileModification(
            $nodeType->getFusionFilePath(),
            $prototype
        );
    }
}
