<?php

declare(strict_types=1);

namespace Kinodash\Dashboard;

final class Spot
{
    private const HEAD = 'head';
    private const BODY_HEAD = 'body_head';
    private const BODY = 'body';
    private const SCRIPT = 'script';

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(Spot $other): bool
    {
        return $this->value === $other->value;
    }

    public static function BODY_HEAD(): self
    {
        return new self(self::BODY_HEAD);
    }

    public static function BODY(): self
    {
        return new self(self::BODY);
    }

    public static function HEAD(): self
    {
        return new self(self::HEAD);
    }

    public static function SCRIPT(): self
    {
        return new self(self::SCRIPT);
    }
}
