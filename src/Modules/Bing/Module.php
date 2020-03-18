<?php

namespace Kinodash\Modules\Bing;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use Kinodash\Modules\Module as KinodashModule;
use League\Flysystem\Filesystem;

class Module implements KinodashModule
{
    const PATH = 'public/bing.jpg';

    /**
     * @var HttpClient
     */
    private HttpClient $httpClient;

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    public function __construct(HttpClient $httpClient, Filesystem $filesystem)
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

        $this->filesystem->put(self::PATH, $response->getBody()->getContents());
    }

    public function head(): ?string
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

        $url = $s3Client
            ->createPresignedRequest(
                $command,
                (new \DateTimeImmutable())->add(new \DateInterval('P1D'))
            )
            ->getUri();

        return <<<HEAD
<style>
html {
    background-image: url($url);
    background-position: center;
}
</style>
HEAD;
    }

    public function script(): ?string
    {
        return null;
    }
}
