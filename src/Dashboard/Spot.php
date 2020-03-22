<?php

declare(strict_types=1);

namespace Kinodash\Dashboard;

final class Spot
{
    private const HEAD = 'head';
    private const TOP_LEFT = 'body_top_left';
    private const MIDDLE_CENTER = 'body_middle_center';
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

    public static function TOP_LEFT(): self
    {
        return new self(self::TOP_LEFT);
    }

    public static function MIDDLE_CENTER(): self
    {
        return new self(self::MIDDLE_CENTER);
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
