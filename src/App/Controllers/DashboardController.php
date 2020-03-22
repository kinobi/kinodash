<?php

declare(strict_types=1);

namespace Kinodash\App\Controllers;

use Kinodash\Modules\ConfigCollection;
use Kinodash\Modules\ModuleCollection;
use League\Plates\Engine as View;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use WyriHaximus\HtmlCompress\Factory as HtmlCompressor;

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
        $this->modules->boot(ConfigCollection::fromRequest($request), $this->view);

        $payload = $this->createPayload();

        $response->getBody()->write($payload);

        return $response;
    }

    private function createPayload(): string
    {
        $payload = $this->view->render('dashboard', ['modules' => $this->modules->filterBooted()]);
        if ($_ENV['DEBUG'] !== 'true') {
            $parser = HtmlCompressor::construct();
            $payload = $parser->compress($payload);
        }

        return $payload;
    }
}
