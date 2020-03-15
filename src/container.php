<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use League\Plates\Engine as Plates;
use Psr\Container\ContainerInterface;

$builder = new ContainerBuilder();

$definitions = [
    Plates::class => static function (ContainerInterface $c) {
        return new Plates(__DIR__ . '/../templates');
    },
];

$builder->addDefinitions($definitions);

return $builder->build();
