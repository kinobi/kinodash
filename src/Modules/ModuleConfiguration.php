<?php

declare(strict_types=1);

namespace Kinodash\Modules;

use ArrayIterator;
use GuzzleHttp\Psr7\Uri;
use IteratorAggregate;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

final class ModuleConfiguration implements IteratorAggregate
{
    public const CONFIG_DELIMITER = ';';

    private array $configs;

    public function __construct(UriInterface ...$configs)
    {
        $this->configs = $configs;
    }

    public static function fromRequest(ServerRequestInterface $request): self
    {
        $configString = $request->getQueryParams()['config'] ?? $request->getServerParams()['KINODASH_MODULE_CONFIGS'];

        $configs = explode(self::CONFIG_DELIMITER, $configString);
        $configs = array_map(fn(string $config) => new Uri($config), $configs);

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
