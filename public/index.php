<?php

declare(strict_types=1);

use DI\Bridge\Slim\Bridge;
use Kinodash\App\Controllers\DashboardController;
use Kinodash\App\Controllers\ModuleController;

require __DIR__ . '/../vendor/autoload.php';

if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

$app = Bridge::create(require __DIR__ . '/../src/container.php');

$app->addErrorMiddleware(true, true, true);

$app->any('/{moduleId:[a-z]+}[/{params:.*}]', ModuleController::class);

$app->get('/', DashboardController::class);

$app->run();
