<?php

declare(strict_types=1);

namespace Kinodash\Dashboard\Module;

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

    public static function lookup(ServerRequestInterface $request): self
    {
        $configString = $request->getServerParams()[self::ENV_CONFIG_KEY] ?? '';

        if (empty($configString)) {
            throw new RuntimeException('No Module configured');
        }

        $configs = explode(self::CONFIG_DELIMITER, $configString);
        $configs = array_map(fn(string $config) => Config::fromString($config), $configs);

        return new self(...$configs);
    }

    public function getById(string $moduleId): Config
    {
        $map = array_flip(array_map(fn(Config $config) => $config->getModuleId(), $this->configs));

        $key = $map[$moduleId] ?? null;
        if ($key === null) {
            throw new RuntimeException(sprintf('No config found for the module with id "%s"', $moduleId));
        }

        return $this->configs[$key];
    }

    /**
     * @inheritDoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->configs);
    }
}
