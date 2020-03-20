<?php

declare(strict_types=1);

namespace Kinodash\App\Controllers;

use Kinodash\Modules\ModuleCollection;
use Kinodash\Modules\ModuleConfiguration;
use League\Plates\Engine as View;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class DashboardController
{
    private View $view;

    private ModuleCollection $modules;

    public function __construct(View $view, ModuleCollection $modules)
    {
        $this->view = $view;
        $this->modules = $modules;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $this->modules->boot(ModuleConfiguration::fromRequest($request));

        $response->getBody()->write(
            $this->view->render('dashboard', ['modules' => $this->modules->filterBooted()])
        );

        return $response;
    }
}
