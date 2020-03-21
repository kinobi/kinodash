<?php

declare(strict_types=1);

namespace Kinodash\Modules;

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
     * Return a view model to place in the body center column
     */
    public function center(): ?ModuleView;

    /**
     * Return a view model to place in the HTML head
     */
    public function head(): ?ModuleView;

    /**
     * Return the Module id
     */
    public function id(): string;

    /**
     * Check the boot status of the Module
     */
    public function isBooted(): bool;

    /**
     * Return a list of script for the Module runtime
     */
    public function script(): ?ModuleView;

    /**
     * Return the path to Module templates
     */
    public function templateFolder(): string;
}
