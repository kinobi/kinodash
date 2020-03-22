<?php

declare(strict_types=1);

namespace Kinodash\Modules;

use ArrayIterator;
use Exception;
use IteratorAggregate;
use League\Plates\Engine as View;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

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

    public function api(
        RequestInterface $request,
        ResponseInterface $response,
        string $moduleId,
        array $params
    ): ResponseInterface {
        $moduleList = $this->registerModules();

        $key = $moduleList[$moduleId] ?? null;
        if ($key === null) { // No module registered with this id
            return $response->withStatus(404);
        }

        return $this->modules[$key]->api($request, $response, $params);
    }

    /**
     * Boot modules in config
     *
     * @param ConfigCollection $configs
     * @param View $view
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
