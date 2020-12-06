<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use GuzzleHttp\Client as HttpClient;
use Kinodash\App\Controllers\DashboardController;
use Kinodash\App\Controllers\ModuleController;
use Kinodash\Dashboard\Module\ModuleCollection;
use Kinodash\Modules\Bing\BingModule;
use Kinodash\Modules\Greeting\GreetingModule;
use Kinodash\Modules\Jira\JiraModule;
use League\Flysystem\Filesystem;
use League\Plates\Engine as Plates;
use Psr\Container\ContainerInterface;
use Spatie\Dropbox\Client as DropboxClient;
use Spatie\FlysystemDropbox\DropboxAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter as Cache;
use function DI\env;

$builder = new ContainerBuilder();

$settings = [
    'cache.redis' => env('REDIS_URL'),
    'storage.dropbox' => [
        'access_token' => env('DROPBOX_ACCESS_TOKEN'),
    ],
];

$infra = [
    Cache::class => static function (ContainerInterface $c) {
        return new Cache(Cache::createConnection($c->get('cache.redis')));
    },

    HttpClient::class => static function (ContainerInterface $c) {
        return new GuzzleHttp\Client();
    },

    Plates::class => static function (ContainerInterface $c) {
        return new Plates(__DIR__ . '/../Dashboard/templates');
    },

    Filesystem::class => static function (ContainerInterface $c) {
        $client = new DropboxClient($c->get('storage.dropbox')['access_token']);
        $adapter = new DropboxAdapter($client);

        return new Filesystem($adapter, ['case_sensitive' => false]);
    },
];

$modules = [
    BingModule::class => static function (ContainerInterface $c) {
        return new BingModule($c->get(HttpClient::class), $c->get(Cache::class));
    },

    GreetingModule::class => static function (ContainerInterface $c) {
        return new GreetingModule();
    },

    JiraModule::class => static function (ContainerInterface $c) {
        return new JiraModule(
            $c->get(HttpClient::class),
            $c->get(Cache::class),
            $c->get(Filesystem::class)
        );
    },

    ModuleCollection::class => static function (ContainerInterface $c) {
        $modules = [
            $c->get(BingModule::class),
            $c->get(GreetingModule::class),
            $c->get(JiraModule::class),
        ];

        return new ModuleCollection(...$modules);
    }
];

$controllers = [
    DashboardController::class => static function (ContainerInterface $c) {
        return new DashboardController($c->get(Plates::class), $c->get(ModuleCollection::class));
    },

    ModuleController::class => static function (ContainerInterface $c) {
        return new ModuleController($c->get(ModuleCollection::class));
    }
];

$builder->addDefinitions(
    array_merge(
        $settings,
        $infra,
        $modules,
        $controllers
    )
);

return $builder->build();
