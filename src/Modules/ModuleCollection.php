<?php

declare(strict_types=1);

namespace Kinodash\Modules;

use ArrayIterator;
use IteratorAggregate;
use League\Plates\Engine as View;
use Psr\Http\Message\UriInterface;

class ModuleCollection implements IteratorAggregate
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
     * Boot modules in config
     *
     * @param ModuleConfiguration $configs
     * @param View $view
     */
    public function boot(ModuleConfiguration $configs, View $view): void
    {
        $moduleList = $this->registerModules();

        /** @var UriInterface $config */
        foreach ($configs as $config) {
            $key = $moduleList[$config->getScheme()] ?? null;
            if ($key === null) { // No module registered for this config
                continue;
            }

            $this->modules[$key]->boot($config, $view);
        }
    }

    /**
     * Filter Modules by boot status
     */
    public function filterBooted(): self
    {
        return new self(
            ...array_filter($this->modules, fn(Module $module) => $module->isBooted())
        );
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->modules);
    }

    private function registerModules(): array
    {
        return array_flip(array_map(fn(Module $module) => $module->id(), $this->modules));
    }
}
