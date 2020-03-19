<?php

declare(strict_types=1);

namespace Kinodash\App\Controllers;

use Kinodash\Modules\Collection as ModuleCollection;
use League\Plates\Engine as View;
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

    public function __invoke(Response $response): Response
    {
        $this->modules->boot();

        $response->getBody()->write(
            $this->view->render('dashboard', ['modules' => $this->modules])
        );

        return $response;
    }
}
