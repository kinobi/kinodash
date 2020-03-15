<?php

declare(strict_types=1);

namespace Kinodash\App\Controllers;

use League\Plates\Engine as View;
use Psr\Http\Message\ResponseInterface as Response;

class DashboardController
{
    /**
     * @var View
     */
    private View $view;

    public function __construct(View $view)
    {
        $this->view = $view;
    }

    public function __invoke(Response $response): Response
    {
        $response->getBody()->write(
            $this->view->render('base/dashboard', [])
        );

        return $response;
    }
}
