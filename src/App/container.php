<?php

declare(strict_types=1);

use Auth0\SDK\Auth0;
use DI\ContainerBuilder;
use Elastica\Client as ElasticsearchClient;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Uri;
use Kinodash\App\Controllers\DashboardController;
use Kinodash\App\Controllers\ModuleController;
use Kinodash\Dashboard\Module\ModuleCollection;
use Kinodash\Modules\Auth0\Auth0Module;
use Kinodash\Modules\Bing\BingModule;
use Kinodash\Modules\Bookmark\BookmarkModule;
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
    'auth.auth0' => [
        'domain' => env('AUTH0_DOMAIN'),
        'client_id' => env('AUTH0_CLIENT_ID'),
        'client_secret' => env('AUTH0_CLIENT_SECRET'),
        'redirect_uri' => env('AUTH0_CALLBACK_URL'),
        'scope' => 'openid profile email',
    ],
    'cache.redis' => env('REDIS_URL'),
    'cache.elasticsearch' => env('BONSAI_URL'),
    'storage.dropbox' => [
        'access_token' => env('DROPBOX_ACCESS_TOKEN'),
    ],
];

$infra = [
    Auth0::class => static function (ContainerInterface $c) {
        return new Auth0($c->get('auth.auth0'));
    },

    Cache::class => static function (ContainerInterface $c) {
        return new Cache(Cache::createConnection($c->get('cache.redis')));
    },

    ElasticsearchClient::class => static function (ContainerInterface $c) {
        return new ElasticsearchClient($c->get('cache.elasticsearch'));
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

    BookmarkModule::class => static function (ContainerInterface $c) {
        return new BookmarkModule(
            $c->get(Filesystem::class),
            $c->get(HttpClient::class),
            $c->get(ElasticsearchClient::class)
        );
    },

    Auth0Module::class => static function (ContainerInterface $c) {
        return new Auth0Module($c->get(Auth0::class), new Uri($c->get('auth.auth0')['redirect_uri']));
    },

    GreetingModule::class => static function (ContainerInterface $c) {
        return new GreetingModule($c->get(Auth0::class));
    },

    JiraModule::class => static function (ContainerInterface $c) {
        return new JiraModule(
            $c->get(Auth0::class),
            $c->get(HttpClient::class),
            $c->get(Cache::class),
            $c->get(Filesystem::class)
        );
    },

    ModuleCollection::class => static function (ContainerInterface $c) {
        $modules = [
            $c->get(Auth0Module::class),
            $c->get(BingModule::class),
            $c->get(BookmarkModule::class),
            $c->get(GreetingModule::class),
            $c->get(JiraModule::class),
        ];

        return new ModuleCollection(...$modules);
    }
];

$controllers = [
    DashboardController::class => static function (ContainerInterface $c) {
        return new DashboardController($c->get(Plates::class), $c->get(ModuleCollection::class), $c->get(Auth0::class));
    },

    ModuleController::class => static function (ContainerInterface $c) {
        return new ModuleController($c->get(ModuleCollection::class), $c->get(Auth0::class));
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
