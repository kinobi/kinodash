<?php

declare(strict_types=1);

namespace Kinodash\Dashboard\Module;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use League\Plates\Engine as View;
use RuntimeException;

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
     * @param ConfigCollection $configs
     * @param View $view
     * @throws Exception
     */
    public function boot(ConfigCollection $configs, View $view): void
    {
        $moduleList = $this->registerModules();

        /** @var Config $config */
        foreach ($configs as $config) {
            $key = $moduleList[$config->getModuleId()] ?? null;
            if ($key === null) { // No module registered for this config
                continue;
            }

            try {
                $module = $this->modules[$key];
                $module->boot($config);
                if ($folder = $module->templateFolder()) {
                    $view->addFolder($module->id(), $folder);
                }
            } catch (Exception $e) {
                if ($_ENV['DEBUG'] === 'true') {
                    throw $e;
                }
            }
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

    public function getById(string $moduleId): Module
    {
        $moduleList = $this->registerModules();

        $key = $moduleList[$moduleId] ?? null;
        if ($key === null) {
            throw new RuntimeException(sprintf('No module registered with the id "%s"', $moduleId));
        }

        return $this->modules[$key];
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
