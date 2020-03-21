<?php

declare(strict_types=1);

use Aws\S3\S3Client;
use DI\ContainerBuilder;
use GuzzleHttp\Client as HttpClient;
use Kinodash\App\Controllers\DashboardController;
use Kinodash\App\Controllers\ModuleController;
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

    ModuleCollection::class => static function (ContainerInterface $c) {
        $modules = [
            new GreetingModule(),
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
