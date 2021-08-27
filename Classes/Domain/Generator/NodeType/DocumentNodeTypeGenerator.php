<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Generator\NodeType;

use Flowpack\SiteKickstarter\Domain\Generator\NodeType\AbstractNodeTypeGenerator;
use Flowpack\SiteKickstarter\Domain\Model\NodeType;

class DocumentNodeTypeGenerator extends AbstractNodeTypeGenerator
{
    function getSuperTypes(NodeType $nodeType): array
    {
        return ['Neos.Neos:Document'];
    }
}
