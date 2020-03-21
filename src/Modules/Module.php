<?php

declare(strict_types=1);

namespace Kinodash\Modules;

use Kinodash\Dashboard\Spot;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

interface Module
{
    /**
     * Forward HTTP call to the Module
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array $params
     *
     * @return ResponseInterface
     */
    public function api(RequestInterface $request, ResponseInterface $response, array $params): ResponseInterface;

    /**
     * Boot the Module
     *
     * @param UriInterface $config
     */
    public function boot(UriInterface $config): void;

    /**
     * Return the Module id
     */
    public function id(): string;

    /**
     * Check the boot status of the Module
     */
    public function isBooted(): bool;

    /**
     * Return the path to Module templates
     */
    public function templateFolder(): string;

    /**
     * Return a Module view model to place at this spot
     *
     * @param Spot $spot
     * @return ModuleView|null
     */
    public function view(Spot $spot): ?ModuleView;
}
