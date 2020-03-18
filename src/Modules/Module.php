<?php

namespace Kinodash\Modules;

interface Module
{
    public function boot(): void;

    /**
     * @todo Return a typed Collection of HTML head tags
     */
    public function head(): ?string;

    /**
     * @todo Return a typed Collection of scripts with src only or body
     */
    public function script(): ?string;
}
