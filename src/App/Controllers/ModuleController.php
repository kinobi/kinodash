<?php

declare(strict_types=1);

namespace Kinodash\App\Controllers;

use Kinodash\Dashboard\Module\ConfigCollection;
use Kinodash\Dashboard\Module\ModuleCollection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ModuleController
{
    private ModuleCollection $modules;

    public function __construct(ModuleCollection $modules)
    {
        $this->modules = $modules;
    }

    public function __invoke(Request $request, Response $response, string $moduleId, string $params = ''): Response
    {
        $configs = ConfigCollection::lookup($request);
        $moduleConfig = $configs->getById($moduleId);

        $module = $this->modules->getById($moduleId);
        $module->boot($moduleConfig);
        if (!$module->isBooted()) {
            return $response->withStatus(500);
        }

        return $module->api($request, $response, explode('/', $params));
    }
}
