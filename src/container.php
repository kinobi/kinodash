<?php

declare(strict_types=1);

use Auth0\SDK\Auth0;
use Aws\S3\S3Client;
use DI\ContainerBuilder;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Uri;
use Kinodash\App\Controllers\DashboardController;
use Kinodash\App\Controllers\ModuleController;
use Kinodash\Modules\Auth0\Auth0Module;
use Kinodash\Modules\Bing\BingModule;
use Kinodash\Modules\Greeting\GreetingModule;
use Kinodash\Modules\ModuleCollection;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use League\Plates\Engine as Plates;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Contracts\Cache\CacheInterface;

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
    'storage.s3' => [
        'client' => [
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
            'region' => env('AWS_S3_REGION'),
            'version' => 'latest',
        ],
        'bucket' => env('AWS_S3_BUCKET_NAME'),
    ]
];

$infra = [
    Auth0::class => static function (ContainerInterface $c) {
        return new Auth0($c->get('auth.auth0'));
    },

    CacheInterface::class => static function (ContainerInterface $c) {
        return new RedisAdapter(RedisAdapter::createConnection($c->get('cache.redis')));
    },

    HttpClient::class => static function (ContainerInterface $c) {
        return new GuzzleHttp\Client();
    },

    Plates::class => static function (ContainerInterface $c) {
        return new Plates(__DIR__ . '/Dashboard/templates');
    },

    Filesystem::class => static function (ContainerInterface $c) {
        $client = new S3Client($c->get('storage.s3')['client']);

        $adapter = new AwsS3Adapter($client, $c->get('storage.s3')['bucket']);

        return new Filesystem($adapter);
    },
];

$modules = [
    BingModule::class => static function (ContainerInterface $c) {
        return new BingModule($c->get(HttpClient::class), $c->get(CacheInterface::class));
    },

    Auth0Module::class => static function (ContainerInterface $c) {
        return new Auth0Module($c->get(Auth0::class), new Uri($c->get('auth.auth0')['redirect_uri']));
    },

    GreetingModule::class => static function (ContainerInterface $c) {
        return new GreetingModule($c->get(Auth0::class));
    },

    ModuleCollection::class => static function (ContainerInterface $c) {
        $modules = [
            $c->get(Auth0Module::class),
            $c->get(GreetingModule::class),
            $c->get(BingModule::class),
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
