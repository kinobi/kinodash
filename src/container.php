<?php

declare(strict_types=1);

use Aws\S3\S3Client;
use DI\ContainerBuilder;
use GuzzleHttp\Client as HttpClient;
use Kinodash\App\Controllers\DashboardController;
use Kinodash\Modules\Bing\Module as ModuleBing;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use League\Plates\Engine as Plates;
use Psr\Container\ContainerInterface;

use function DI\env;

$builder = new ContainerBuilder();

$settings = [
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
    HttpClient::class => static function (ContainerInterface $c) {
        return new GuzzleHttp\Client();
    },
    Plates::class => static function (ContainerInterface $c) {
        return new Plates(__DIR__ . '/../templates');
    },
    Filesystem::class => static function (ContainerInterface $c) {
        $client = new S3Client($c->get('storage.s3')['client']);

        $adapter = new AwsS3Adapter($client, $c->get('storage.s3')['bucket']);

        return new Filesystem($adapter);
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
        $settings,
        $infra,
        $modules,
        $controllers
    )
);

return $builder->build();
