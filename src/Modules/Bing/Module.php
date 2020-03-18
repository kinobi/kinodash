<?php

namespace Kinodash\Modules\Bing;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use Kinodash\Modules\Module as KinodashModule;
use League\Flysystem\FilesystemInterface;

class Module implements KinodashModule
{
    /**
     * @var HttpClient
     */
    private HttpClient $httpClient;

    /**
     * @var FilesystemInterface
     */
    private FilesystemInterface $filesystem;

    public function __construct(HttpClient $httpClient, FilesystemInterface $filesystem)
    {
        $this->httpClient = $httpClient;
        $this->filesystem = $filesystem;
    }

    /**
     * @todo error checks
     */
    public function boot(): void
    {
        $bing = 'http://www.bing.com';
        $api = '/HPImageArchive.aspx?';
        $format = '&format=js';
        $day = '&idx=0';
        $market = '&mkt=en-US';
        $const = '&n=1';

        /**
         * @todo url format
         */
        $request = new Request('GET', $bing . $api . $format . $day . $market . $const);

        $response = $this->httpClient->send($request);
        $data = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

        /**
         * @todo Choose the size ?
         * @see https://github.com/whizzzkid/bing-wallpapers-for-linux/blob/master/bingwallpaper
         */
        $request = new Request('GET', $bing . $data->images[0]->url);
        $response = $this->httpClient->send($request);

        $this->filesystem->put('local://public/bing.jpg', $response->getBody()->getContents());
    }

    public function head(): ?string
    {
        return <<<'HEAD'
<style>
html {
    background-image: url("/assets/bing.jpg");
}
</style>
HEAD;
    }

    public function script(): ?string
    {
        return null;
    }
}
