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
    public const USER_MODULE_CONFIGS_KEY = 'http://kinodash/module_configs';

    private array $configs;

    public function __construct(Config ...$configs)
    {
        $this->configs = $configs;
    }

    public static function lookup(?array $userData, ServerRequestInterface $request): self
    {
        $configString = $userData[self::USER_MODULE_CONFIGS_KEY]
            ?? $request->getServerParams()[self::ENV_CONFIG_KEY]
            ?? '';

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
