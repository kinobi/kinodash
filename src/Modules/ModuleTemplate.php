<?php

declare(strict_types=1);

namespace Kinodash\Modules;

use Psr\Http\Message\UriInterface;

trait ModuleTemplate
{
    /**
     * @inheritDoc
     */
    public function head(): ?string
    {
        return null;
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

    /**
     * @inheritDoc
     */
    public function script(): ?string
    {
        return null;
    }
}
