<?php

declare(strict_types=1);

namespace Kinodash\Dashboard\Module;

use InvalidArgumentException;

final class Config
{
    private array $params;

    private function __construct(array $params)
    {
        $this->params = $params;
    }

    public static function fromString(string $configString): self
    {
        $params = parse_url($configString);

        if ($params === false) {
            throw new InvalidArgumentException(sprintf('Invalid Module config string: "%s"', $configString));
        }

        return new self($params);
    }

    /**
     * Return host
     */
    public function getHost(): string
    {
        return $this->params['host'] ?? '';
    }

    /**
     * Return Module identifier
     */
    public function getModuleId(): string
    {
        return $this->params['scheme'];
    }

    /**
     * Return Module options
     */
    public function getOptions(): array
    {
        parse_str($this->params['query'] ?? '', $configQuery);

        return $configQuery ?? [];
    }

    /**
     * Return password
     */
    public function getPassword(): string
    {
        return $this->params['pass'] ?? '';
    }

    /**
     * Return user
     */
    public function getUser(): string
    {
        return $this->params['user'] ?? '';
    }

}
