<?php

declare(strict_types=1);

namespace Kinodash\Modules\Bookmark;

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\UriInterface;

class Bookmark
{
    private UriInterface $uri;

    private string $hash;

    private function __construct(UriInterface $uri, string $hash)
    {
        $this->uri = $uri;
        $this->hash = $hash;
    }

    public static function createFromString(string $uriString): self
    {
        $uri = new Uri($uriString);
        $hash = sha1(sprintf('%s|%s', $uri->getHost(), $uri->getPath()));

        return new self($uri, $hash);
    }

    public function hash(): string
    {
        return $this->hash;
    }

    public function uri(): UriInterface
    {
        return $this->uri;
    }
}
