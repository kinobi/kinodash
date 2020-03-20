<?php

namespace Kinodash\Modules;

use Psr\Http\Message\UriInterface;

interface Module
{
    public function boot(UriInterface $config): void;

    /**
     * Return a list of HTML tags for the Module runtime
     *
     * @todo Return a typed Collection of HTML head tags
     */
    public function head(): ?string;

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
     *
     * @todo Return a typed Collection of scripts with src only or body
     */
    public function script(): ?string;
}
