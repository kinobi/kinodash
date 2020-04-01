<?php

declare(strict_types=1);

namespace Kinodash\Modules\Bookmark;

use Elastica\Client as ElasticsearchClient;
use Elastica\Search;
use GuzzleHttp\Client as HttpClient;
use Kinodash\Dashboard\Module\Config;
use Kinodash\Dashboard\Module\Module;
use Kinodash\Dashboard\Module\ModuleTemplate;
use Kinodash\Dashboard\Module\ModuleView;
use Kinodash\Dashboard\Spot;
use Kinodash\Modules\Bookmark\Services\BookmarkCreator;
use Kinodash\Modules\Bookmark\Services\SearchSeeder;
use League\Flysystem\Filesystem;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class BookmarkModule implements Module
{
    use ModuleTemplate;

    private string $id = 'bookmark';

    private Filesystem $filesystem;

    private HttpClient $httpClient;

    private ElasticsearchClient $elasticsearchClient;

    public function __construct(
        Filesystem $filesystem,
        HttpClient $httpClient,
        ElasticsearchClient $elasticsearchClient
    ) {
        $this->filesystem = $filesystem;
        $this->httpClient = $httpClient;
        $this->elasticsearchClient = $elasticsearchClient;
    }

    public function api(RequestInterface $request, ResponseInterface $response, array $params): ResponseInterface
    {
        [$action] = $params;

        switch ($action) {
            case 'add':
                $input = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
                $creator = new BookmarkCreator($this->filesystem, $this->httpClient);
                $creator($input['url'] ?? '', ...explode(',', $input['tags'] ?? ''));
                $this->searchRefresh();

                return $response->withStatus(201);

            case 'refresh':
                $this->searchRefresh();

                return $response->withStatus(201);

            case 'search':
                $search = new Search($this->elasticsearchClient);
                parse_str($request->getUri()->getQuery(), $q);
                ['query' => $query] = $q;
                if ($query === '') {
                    return $response->withStatus(204);
                }

                $results = $search
                    ->addIndex('bookmark')
                    ->search($query)
                    ->getResults();

                $r = [];
                foreach ($results as $result) {
                    $r[] = $result->getDocument()->getData();
                }

                $response->getBody()->write(json_encode(['results' => $r], JSON_THROW_ON_ERROR, 512));

                return $response->withStatus(200);
        }

        return $response->withStatus(404);
    }

    /**
     * @inheritDoc
     */
    public function boot(Config $config): void
    {
        // Check ES seedé
        // Récupérer csv bookmarks
        // Seeder ES
        $this->booted = true;
    }

    /**
     * @inheritDoc
     */
    public function view(Spot $spot): ?ModuleView
    {
        return null;
    }

    private function searchRefresh(): void
    {
        $searchSeeder = new SearchSeeder($this->elasticsearchClient, $this->filesystem);
        $searchSeeder();
    }
}
