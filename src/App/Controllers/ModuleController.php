<?php

declare(strict_types=1);

namespace Kinodash\App\Controllers;

use Auth0\SDK\Auth0;
use Exception;
use Kinodash\Dashboard\Module\ConfigCollection;
use Kinodash\Dashboard\Module\ModuleCollection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ModuleController
{
    private ModuleCollection $modules;

    /**
     * @var Auth0
     */
    private Auth0 $auth0;

    public function __construct(ModuleCollection $modules, Auth0 $auth0)
    {
        $this->modules = $modules;
        $this->auth0 = $auth0;
    }

    public function __invoke(Request $request, Response $response, string $moduleId, string $params = ''): Response
    {
        $configs = ConfigCollection::lookup($this->auth0->getUser(), $request);
        $moduleConfig = $configs->getById($moduleId);

        $module = $this->modules->getById($moduleId);
        $module->boot($moduleConfig);
        if (!$module->isBooted()) {
            return $response->withStatus(500);
        }

        try {
            return $module->api($request, $response, explode('/', $params));
        } catch (Exception $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR, 512));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
