<?php

declare(strict_types=1);

namespace Kinodash\Modules;

use ArrayIterator;
use IteratorAggregate;

class Collection implements IteratorAggregate
{
    /**
     * @var array|Module[]
     */
    private array $modules;

    public function __construct(Module ...$modules)
    {
        $this->modules = $modules;
    }

    /**
     * Boot modules
     */
    public function boot(): void
    {
        array_walk(
            $this->modules,
            static function (Module $module) {
                $module->boot();
            },
        );
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->modules);
    }
}
