<?php

declare(strict_types=1);

namespace Kinodash\Modules\Bing;

use Carbon\CarbonImmutable;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use Kinodash\Modules\Module as KinodashModule;
use League\Flysystem\Filesystem;

class Module implements KinodashModule
{
    public const PATH = 'public/bing.jpg';

    private HttpClient $httpClient;

    private Filesystem $filesystem;

    public function __construct(HttpClient $httpClient, Filesystem $filesystem)
    {
        $this->httpClient = $httpClient;
        $this->filesystem = $filesystem;
    }

    /**
     * @todo error checks
     * @see https://github.com/whizzzkid/bing-wallpapers-for-linux/blob/master/bingwallpaper
     */
    public function boot(): void
    {
        $bing = 'http://www.bing.com';
        $api = '/HPImageArchive.aspx?';
        $format = '&format=js';
        $day = '&idx=0';
        $market = '&mkt=en-US';
        $const = '&n=1';

        $request = new Request('GET', "{$bing}{$api}{$format}{$day}{$market}{$const}");

        $response = $this->httpClient->send($request);
        $data = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

        $request = new Request('GET', $bing . $data->images[0]->url);
        $response = $this->httpClient->send($request);

        $this->filesystem->put(self::PATH, $response->getBody()->getContents());
    }

    public function head(): ?string
    {
        $url = $this->getBackgroundUri();

        return <<<HEAD
<style>
body,
html {
 height:100%;
}
html {
    background-image: url($url);
    background-position: top center;
    background-repeat: no-repeat;
}
</style>
HEAD;
    }

    public function script(): ?string
    {
        return null;
    }

    private function getBackgroundUri()
    {
        $s3Adapter = $this->filesystem->getAdapter();
        $s3Client = $s3Adapter->getClient();
        $command = $s3Client->getCommand(
            'GetObject',
            array_merge(
                [
                    'Bucket' => $s3Adapter->getBucket(),
                    'Key' => self::PATH,
                ],
                []
            )
        );

        return $s3Client
            ->createPresignedRequest(
                $command,
                CarbonImmutable::now()->addRealDay()
            )
            ->getUri();
    }
}
