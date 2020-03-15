<?php

declare(strict_types=1);

use DI\Bridge\Slim\Bridge;
use Kinodash\App\Controllers\DashboardController;

require __DIR__ . '/../vendor/autoload.php';

$app = Bridge::create(require __DIR__ . '/../src/container.php');

$app->addErrorMiddleware(true, true, true);

$app->get('/', DashboardController::class);

$app->run();
