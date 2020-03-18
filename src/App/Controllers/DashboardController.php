<?php

declare(strict_types=1);

namespace Kinodash\App\Controllers;

use Kinodash\Modules\Module;
use League\Plates\Engine as View;
use Psr\Http\Message\ResponseInterface as Response;

class DashboardController
{
    /**
     * @var View
     */
    private View $view;

    /**
     * @var array|Module[]
     */
    private array $modules;

    public function __construct(View $view, Module ...$modules)
    {
        $this->view = $view;
        $this->modules = $modules;
    }

    public function __invoke(Response $response): Response
    {
        array_walk(
            $this->modules,
            static function (Module $module) {
                $module->boot();
            },
        );

        $response->getBody()->write(
            $this->view->render('dashboard', ['modules' => $this->modules])
        );

        return $response;
    }
}
