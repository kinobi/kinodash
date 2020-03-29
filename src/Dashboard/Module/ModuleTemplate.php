<?php

declare(strict_types=1);

namespace Kinodash\Dashboard\Module;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

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
        if (empty($this->id)) {
            throw new RuntimeException(sprintf('Missing id in Module "%s"', __CLASS__));
        }

        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function isBooted(): bool
    {
        return $this->booted;
    }

    /**
     *
     * @inheritDoc
     */
    public function templateFolder(): ?string
    {
        return null;
    }
}
