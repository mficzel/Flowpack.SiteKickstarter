<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Generator\Fusion;

use Flowpack\SiteKickstarter\Domain\Specification\NodeTypeSpecification;
use Flowpack\SiteKickstarter\Domain\Modification\WholeFileModification;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;

class ContentFusionGenerator extends AbstractFusionGenerator
{
    /**
     * @param NodeTypeSpecification $nodeType
     * @return ModificationIterface
     */
    public function generate(NodeTypeSpecification $nodeType): ModificationIterface
    {
        $name = $nodeType->getFullName();

        $prototype = <<<EOT
            #
            # Renderer for NodeType {$nodeType->getFullName()}
            # as configured in {$nodeType->getYamlFilePath()}
            #
            # @see https://docs.neos.io/cms/manual/rendering
            #
            prototype($name) < prototype(Neos.Neos:ContentComponent) {

                {$this->indent($this->createPropertyAccessors($nodeType))}

                renderer = afx`
                    <div>
                        Autogenerated renderer for NodeType: {$nodeType->getFullName()}, adjust the following files and remove this comment!
                        <pre>
                         - Data-Model: {$nodeType->getYamlFilePath()}
                         - Rendering: {$nodeType->getFusionFilePath()}
                        </pre>
                        {$this->indent($this->createAfxRenderer($nodeType), 12)}
                    </div>
                `
            }
            EOT;

        return new WholeFileModification(
            $nodeType->getFusionFilePath(),
            $prototype
        );
    }
}
