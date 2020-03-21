<?php

declare(strict_types=1);

namespace Kinodash\Modules;

use ArrayIterator;
use IteratorAggregate;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

final class ConfigCollection implements IteratorAggregate
{
    public const CONFIG_DELIMITER = ';';
    public const ENV_CONFIG_KEY = 'KINODASH_MODULE_CONFIGS';
    public const QUERY_CONFIG_KEY = 'config';

    private array $configs;

    public function __construct(Config ...$configs)
    {
        $this->configs = $configs;
    }

    public static function fromRequest(ServerRequestInterface $request): self
    {
        $configString =
            $request->getQueryParams()[self::QUERY_CONFIG_KEY] ??
            $request->getServerParams()[self::ENV_CONFIG_KEY] ??
            null;

        if (empty($configString)) {
            throw new RuntimeException('No Module configured');
        }

        $configs = explode(self::CONFIG_DELIMITER, $configString);
        $configs = array_map(fn(string $config) => Config::fromString($config), $configs);

        return new self(...$configs);
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->configs);
    }
}
