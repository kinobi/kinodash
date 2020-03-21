<?php

declare(strict_types=1);

namespace Kinodash\Modules\Bing;

use Carbon\CarbonImmutable;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Kinodash\Modules\Module;
use Kinodash\Modules\ModuleTemplate;
use Kinodash\Modules\ModuleView;
use League\Flysystem\Filesystem;
use Psr\Http\Message\UriInterface;

class BingModule implements Module
{
    use ModuleTemplate;

    public const PATH = 'public/bing.jpg';
    public const BING_BASE_URL = 'http://www.bing.com';

    private string $id = 'bing';

    private HttpClient $httpClient;

    private Filesystem $filesystem;

    public function __construct(HttpClient $httpClient, Filesystem $filesystem)
    {
        $this->httpClient = $httpClient;
        $this->filesystem = $filesystem;
    }

    /**
     * @param UriInterface $config
     * @see https://github.com/whizzzkid/bing-wallpapers-for-linux/blob/master/bingwallpaper
     * @todo error checks
     */
    public function boot(UriInterface $config): void
    {
        $queryString = $this->createQueryString(
            $config,
            [
                'format' => 'js',
                'idx' => 0,
                'mkt' => 'en-US',
                'n' => 1,
            ]
        );

        $apiUri = (new Uri(self::BING_BASE_URL . '/HPImageArchive.aspx'))->withQuery($queryString);
        $request = new Request('GET', $apiUri);
        $response = $this->httpClient->send($request);
        $data = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

        $request = new Request('GET', self::BING_BASE_URL . $data->images[0]->url);
        $response = $this->httpClient->send($request);

        $this->filesystem->put(self::PATH, $response->getBody()->getContents());
        $this->booted = true;
    }

    public function head(): ?ModuleView
    {
        return new ModuleView('head', ['url' => $this->getBackgroundUri()]);
    }

    /**
     * @inheritDoc
     */
    public function templateFolder(): string
    {
        return __DIR__ . '/templates';
    }

    private function getBackgroundUri(): UriInterface
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

    private function createQueryString(UriInterface $config, array $defaults = []): string
    {
        parse_str($config->getQuery(), $configQuery);

        return http_build_query(
            array_merge(
                $defaults,
                $configQuery
            )
        );
    }
}
