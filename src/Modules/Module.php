<?php

namespace Kinodash\Modules;

use League\Plates\Engine as View;
use Psr\Http\Message\UriInterface;

interface Module
{
    public function boot(UriInterface $config, View $view): void;

    /**
     * Return a list of HTML tags for the Module runtime
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
}
