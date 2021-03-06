<?php

declare(strict_types=1);

use DI\Bridge\Slim\Bridge;
use Kinodash\App\Controllers\DashboardController;
use Kinodash\App\Controllers\ModuleController;
use Slim\Error\Renderers\HtmlErrorRenderer;
use Zeuxisoo\Whoops\Slim\WhoopsMiddleware;

require __DIR__ . '/../vendor/autoload.php';

if (PHP_SAPI === 'cli-server') {
    $url = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

$app = Bridge::create(require __DIR__ . '/../src/App/container.php');
if ($_ENV['DEBUG'] === 'true') {
    $app->add(new WhoopsMiddleware(['enable' => true]));
} else {
    $errorMiddleware = $app->addErrorMiddleware(false, true, true);
    $errorHandler = $errorMiddleware->getDefaultErrorHandler();
    $errorHandler->registerErrorRenderer('text/html', HtmlErrorRenderer::class);
}

$app->any('/{moduleId:[a-z0-9]+}[/{params:.*}]', ModuleController::class);

$app->get('/', DashboardController::class);

$app->run();
