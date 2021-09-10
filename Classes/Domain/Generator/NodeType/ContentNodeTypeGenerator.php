<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Generator\NodeType;

use Flowpack\SiteKickstarter\Domain\Specification\NodeTypeSpecification;

class ContentNodeTypeGenerator extends AbstractNodeTypeGenerator
{
    function getSuperTypes(NodeTypeSpecification $nodeType): array
    {
        return ['Neos.Neos:Content'];
    }

}
