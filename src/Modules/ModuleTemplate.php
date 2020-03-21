<?php

declare(strict_types=1);

namespace Kinodash\Modules;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

trait ModuleTemplate
{
    private bool $booted = false;

    public function api(RequestInterface $request, ResponseInterface $response, array $params): ResponseInterface
    {
        return $response->withStatus(204);
    }

    /**
     * @inheritDoc
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }
}
