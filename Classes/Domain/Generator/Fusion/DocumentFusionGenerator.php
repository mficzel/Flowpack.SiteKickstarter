<?php


namespace Flowpack\SiteKickstarter\Domain\Generator\Fusion;


use Flowpack\SiteKickstarter\Domain\Model\NodeType;
use Flowpack\SiteKickstarter\Domain\Modification\WholeFileModification;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;

class DocumentFusionGenerator extends AbstractFusionGenerator
{
    /**
     * @param NodeType $nodeType
     * @return ModificationIterface
     */
    public function generate(NodeType $nodeType): ModificationIterface
    {
        $name = $nodeType->getFullName();
        $accessors = $this->indent($this->createPropertyAccessors($nodeType));
        $renderer = $this->indent($this->createAfxRenderer($nodeType), 12);
        $packageKey = $nodeType->getPackage()->getPackageKey();

        $prototype = <<<EOT
            prototype($name) < prototype(Neos.Fusion:Component) {

                $accessors

                renderer = Neos.Neos:Page {

                    head {
                        resources = afx`
                            <link href={StaticResource.uri('$packageKey', 'Public/Styles/main.css')} rel="stylesheet" media="all" />
                            <style src={StaticResource.uri('$packageKey', 'Public/Scripts/main.js')}></style>
                        `
                    }

                    body = afx`
                        $renderer
                    `
                }
            }
            EOT;

        return new WholeFileModification(
            $nodeType->getFusionFilePath(),
            $prototype
        );
    }
}
