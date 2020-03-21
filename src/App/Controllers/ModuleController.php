<?php

declare(strict_types=1);

namespace Kinodash\App\Controllers;

use Kinodash\Modules\ModuleCollection;
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
        return $this->modules->api($request, $response, $moduleId, explode('/', $params));
    }
}
