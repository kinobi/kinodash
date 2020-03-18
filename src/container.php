<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use GuzzleHttp\Client as HttpClient;
use Kinodash\App\Controllers\DashboardController;
use Kinodash\Modules\Bing\Module as ModuleBing;
use League\Flysystem\MountManager as Filesystem;
use League\Plates\Engine as Plates;
use Psr\Container\ContainerInterface;

$builder = new ContainerBuilder();

$infra = [
    HttpClient::class => static function (ContainerInterface $c) {
        return new GuzzleHttp\Client();
    },
    Plates::class => static function (ContainerInterface $c) {
        return new Plates(__DIR__ . '/../templates');
    },
    Filesystem::class => static function (ContainerInterface $c) {
        $local = new League\Flysystem\Adapter\Local(__DIR__ . '/../var/storage');

        return new Filesystem(
            [
                'local' => new League\Flysystem\Filesystem($local),
            ]
        );
    },
];

$modules = [
    ModuleBing::class => static function (ContainerInterface $c) {
        return new ModuleBing($c->get(HttpClient::class), $c->get(Filesystem::class));
    }
];

$controllers = [
    DashboardController::class => static function (ContainerInterface $c) {
        $modules = [
            $c->get(ModuleBing::class),
        ];

        return new DashboardController($c->get(Plates::class), ...$modules);
    },
];

$builder->addDefinitions(
    array_merge(
        $infra,
        $modules,
        $controllers
    )
);

return $builder->build();
