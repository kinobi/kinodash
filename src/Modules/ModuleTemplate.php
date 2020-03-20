<?php

declare(strict_types=1);

namespace Kinodash\Modules;

trait ModuleTemplate
{
    /**
     * @inheritDoc
     */
    public function head(): ?ModuleView
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
    public function script(): ?ModuleView
    {
        return null;
    }
}
