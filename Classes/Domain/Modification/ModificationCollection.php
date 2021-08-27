<?php
declare(strict_types=1);

namespace Flowpack\SiteKickstarter\Domain\Modification;

class ModificationCollection implements ModificationIterface, \IteratorAggregate
{
    /**
     * @var ModificationIterface[]
     */
    protected $modifications;

    /**
     * ModificationCollection constructor.
     * @param ModificationIterface ...$modifications
     */
    public function __construct(ModificationIterface ...$modifications)
    {
        $this->modifications = [];
        foreach ($modifications as $modification) {
            if ($modification instanceof ModificationCollection) {
                foreach (iterator_to_array($modification) as $partialModification) {
                    $this->modifications[] = $partialModification;
                }
            } else {
                $this->modifications[] = $modification;
            }
        }
    }

    public function isForceRequired(): bool
    {
        foreach ($this->modifications as $modification) {
            if ($modification->isForceRequired()) {
                return true;
            }
        }
        return false;
    }

    public function getAbstract(): string
    {
        $abstracts = array_map(
            function(ModificationIterface $modification) {
                return $modification->getAbstract();
            },
            $this->modifications
        );
        return ' - ' . implode(PHP_EOL . ' - ', $abstracts);
    }

    public function apply(bool $force = false): void
    {
        if (!$force && $this->isForceRequired()) {
            throw new \Exception('Force is required');
        }

        foreach ($this->modifications as $modification) {
            $modification->apply($force);
        }
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->modifications);
    }
}
