<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Modification;

interface ModificationIterface
{
    public function isForceRequired(): bool;

    public function getAbstract(): string;

    public function apply(bool $force = false): void;
}
