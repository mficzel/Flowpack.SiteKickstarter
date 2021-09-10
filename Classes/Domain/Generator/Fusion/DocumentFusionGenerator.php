<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Generator\Fusion;

use Flowpack\SiteKickstarter\Domain\Specification\NodeTypeSpecification;
use Flowpack\SiteKickstarter\Domain\Modification\CreateFileModification;
use Flowpack\SiteKickstarter\Domain\Modification\ModificationIterface;
use Neos\Flow\Package\FlowPackageInterface;

class DocumentFusionGenerator extends AbstractFusionGenerator
{

    /**
     * @param FlowPackageInterface $package
     * @param NodeTypeSpecification $nodeType
     * @return ModificationIterface
     */
    public function generate(FlowPackageInterface $package, NodeTypeSpecification $nodeType): ModificationIterface
    {
        $filePath = $this->getFilePath($package, $nodeType);

        $prototype = <<<EOT
            #
            # Renderer for NodeType {$nodeType->getName()->getFullName()}
            # as configured in {$filePath}.yaml
            #
            # @see https://docs.neos.io/cms/manual/rendering
            #
            prototype({$nodeType->getName()->getFullName()}) < prototype(Neos.Fusion:Component) {

                {$this->indent($this->createPropertyAccessors($nodeType))}

                renderer = Neos.Neos:Page {
                    head {
                        resources = afx`
                            <link href={StaticResource.uri('{$package->getPackageKey()}', 'Public/Styles/Main.css')} rel="stylesheet" media="all" />
                            <style src={StaticResource.uri('{$package->getPackageKey()}', 'Public/Scripts/Main.js')}></style>
                        `
                    }

                    body = afx`
                        <div>
                            Autogenerated renderer for NodeType: "{$nodeType->getName()->getFullName()}", adjust the following files and remove this comment!
                            <pre>
                             - Data-Model: {$filePath}.yaml
                             - Rendering: {$filePath}.fusion
                             - Css: {$package->getPackageKey()}/Resources/Public/Styles/Main.css
                             - JS: {$package->getPackageKey()}/Resources/Public/Scripts/Main.css
                            </pre>
                            {$this->indent($this->createAfxRenderer($nodeType), 16)}
                        </div>
                    `
                }
            }
            EOT;

        return new CreateFileModification(
            $filePath . '.fusion',
            $prototype
        );
    }
}
