<?php

declare(strict_types=1);

namespace Kinodash\Modules\Bing;

use Carbon\CarbonImmutable;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Kinodash\Dashboard\Spot;
use Kinodash\Modules\Config;
use Kinodash\Modules\Module;
use Kinodash\Modules\ModuleTemplate;
use Kinodash\Modules\ModuleView;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;


/**
 * @see https://github.com/whizzzkid/bing-wallpapers-for-linux/blob/master/bingwallpaper
 */
class BingModule implements Module
{
    use ModuleTemplate;

    public const BING_BASE_URL = 'https://www.bing.com';
    public const EXPIRATION_IN_MINUTES = 5;

    private string $id = 'bing';

    private string $url;

    private HttpClient $httpClient;

    private CacheInterface $cache;

    public function __construct(HttpClient $httpClient, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function boot(Config $config): void
    {
        $this->url = $this->cache->get(
            'bing.url',
            function (ItemInterface $item) use ($config) {
                $item->expiresAt(CarbonImmutable::now()->addRealMinutes(self::EXPIRATION_IN_MINUTES));
                $queryString = $this->createQueryString(
                    $config,
                    [
                        'format' => 'js',
                        'idx' => random_int(0, 5),
                        'mkt' => 'en-US',
                        'n' => 1,
                    ]
                );

                $apiUri = (new Uri(self::BING_BASE_URL . '/HPImageArchive.aspx'))->withQuery($queryString);
                $request = new Request('GET', $apiUri);
                $response = $this->httpClient->send($request);
                $data = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

                return self::BING_BASE_URL . $data->images[0]->url;
            }
        );

        $this->booted = true;
    }

    /**
     * @inheritDoc
     */
    public function templateFolder(): string
    {
        return __DIR__ . '/templates';
    }

    /**
     * @inheritDoc
     */
    public function view(Spot $spot): ?ModuleView
    {
        if ($spot->equals(Spot::HEAD())) {
            return new ModuleView('head', ['url' => $this->url]);
        }

        return null;
    }

    private function createQueryString(Config $config, array $defaults = []): string
    {
        return http_build_query(
            array_merge(
                $defaults,
                $config->getOptions()
            )
        );
    }
}
