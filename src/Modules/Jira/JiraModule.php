<?php

declare(strict_types=1);

namespace Kinodash\Modules\Jira;

use Auth0\SDK\Auth0;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterval;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Kinodash\Dashboard\Module\Config;
use Kinodash\Dashboard\Module\Module;
use Kinodash\Dashboard\Module\ModuleTemplate;
use Kinodash\Dashboard\Module\ModuleView;
use Kinodash\Dashboard\Spot;
use League\Flysystem\Filesystem;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\CacheItem;

class JiraModule implements Module
{
    use ModuleTemplate;

    private string $id = 'jira';

    private Auth0 $auth0;

    private HttpClient $httpClient;

    private array $issues;

    private ?int $currentTask;

    private UriInterface $jiraHost;

    private RedisAdapter $cache;

    private ?array $user;

    private Filesystem $filesystem;

    public function __construct(Auth0 $auth0, HttpClient $httpClient, RedisAdapter $cache, Filesystem $filesystem)
    {
        $this->auth0 = $auth0;
        $this->httpClient = $httpClient;
        $this->cache = $cache;
        $this->filesystem = $filesystem;
    }

    public function api(RequestInterface $request, ResponseInterface $response, array $params): ResponseInterface
    {
        switch ($params[0]) {
            case 'start':
                $data = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
                return $this->changeTask($response, (int)$data['current']);

            case 'stop':
                return $this->changeTask($response);
        }

        return $response->withStatus(404);
    }

    private function changeTask(ResponseInterface $response, ?int $newTaskId = null): ResponseInterface
    {
        /** @var CacheItem $currentTaskId */
        $currentTaskId = $this->cache->getItem($this->generateUserCacheKey('current'));
        /** @var CacheItem $currentTaskStart */
        $currentTaskStart = $this->cache->getItem($this->generateUserCacheKey('start'));

        $id = $currentTaskId->get();

        // Check not the same task
        if ($newTaskId === $id) {
            return $this->createTaskResponse($response);
        }

        // Previous task finished
        if ($id) {
            $start = CarbonImmutable::createFromFormat(CarbonImmutable::ATOM, $currentTaskStart->get());
            $stop = CarbonImmutable::now();
            $duration = $stop->shortAbsoluteDiffForHumans($start, 3);
            $task = $this->issues[$id]['key'] ?? '-';
            $summary = $this->issues[$id]['fields']['summary'] ?? 'Jira ID: ' . $id;
            $csvFilename = sprintf('/%s/%s/week_%s.csv', $this->id(), $stop->year, $stop->week);
            $csv = fopen('php://memory', 'wb');

            if ($this->filesystem->has($csvFilename)) {
                stream_copy_to_stream($this->filesystem->readStream($csvFilename), $csv);
            } else {
                fputcsv($csv, ['Added', 'Task', 'Summary', 'Start', 'Stop', 'Duration']);
            }

            fputcsv(
                $csv,
                [$stop->toDateString(), $task, $summary, $start->toIso8601String(), $stop->toIso8601String(), $duration]
            );

            $this->filesystem->putStream($csvFilename, $csv);
        }

        $this->currentTask = $newTaskId;
        $currentTaskId->set($this->currentTask);
        $this->cache->save($currentTaskId);

        $currentTaskStart->set(CarbonImmutable::now()->toIso8601String());
        $this->cache->save($currentTaskStart);

        return $this->createTaskResponse($response);
    }

    private function generateUserCacheKey(string $key): string
    {
        return sprintf('jira.%s.%s', sha1($this->user['email']), $key);
    }

    private function createTaskResponse(ResponseInterface $response): ResponseInterface
    {
        $response->getBody()->write(json_encode($this->packCurrentIssue(), JSON_THROW_ON_ERROR, 512));
        return $response
            ->withStatus(201)
            ->withHeader('Content-Type', 'json');
    }

    private function packCurrentIssue(): array
    {
        return $this->currentTask !== null
            ? [
                'a' => (string)$this->jiraHost->withPath('/browse/' . $this->issues[$this->currentTask]['key']),
                'text' => $this->issues[$this->currentTask]['key'] .
                    ' ' .
                    $this->issues[$this->currentTask]['fields']['summary'],
            ]
            : [
                'a' => (string)$this->jiraHost,
                'text' => 'Commencer un nouveau ticket',
            ];
    }

    /**
     * @inheritDoc
     */
    public function boot(Config $config): void
    {
        $this->user = $this->auth0->getUser();
        if (!$this->user) {
            return;
        }

        $this->jiraHost = (new Uri())
            ->withScheme('https')
            ->withHost($config->getHost());

        $this->currentTask = $this->cache->get(
            $this->generateUserCacheKey('current'),
            fn() => null
        );

        $this->issues = $this->cache->get(
            $this->generateUserCacheKey('issues'),
            function (CacheItem $item) use ($config) {
                $item->expiresAfter(CarbonInterval::hour());
                return $this->getIssues($config);
            }
        );

        $this->booted = true;
    }

    private function getIssues(Config $config): array
    {
        $uri = (new Uri())
            ->withScheme('https')
            ->withHost($config->getHost())
            ->withUserInfo($config->getUser(), $config->getPassword())
            ->withPath('/rest/api/2/search')
            ->withQuery($this->searchOpenIssuesJQL());

        $request = new Request('GET', $uri, ['Content-Type' => 'application/json']);

        $response = $this->httpClient->send($request);

        $issues = json_decode(
                $response->getBody()->getContents(),
                true,
                512,
                JSON_THROW_ON_ERROR
            )['issues'] ?? [];

        return array_combine(array_column($issues, 'id'), $issues);
    }

    private function searchOpenIssuesJQL(): string
    {
        return http_build_query(
            [
                'jql' => sprintf(
                    'assignee=currentUser() AND status in (%s) order by priority DESC',
                    implode(
                        ',',
                        [
                            '"CODE REVIEW"',
                            '"Functional Review"',
                            '"ICE BOX"',
                            '"In Progress"',
                            '"QA REVIEW"',
                            '"To Do"'
                        ]
                    )
                ),
                'fields' => 'key,priority,summary,timetracking',
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function templateFolder(): ?string
    {
        return __DIR__ . '/templates';
    }

    /**
     * @inheritDoc
     */
    public function view(Spot $spot): ?ModuleView
    {
        if ($spot->equals(Spot::BODY())) {
            $current = $this->packCurrentIssue();

            return new ModuleView(
                'current',
                ['current' => $current]
            );
        }

        if ($spot->equals(Spot::SCRIPT())) {
            return new ModuleView('script', ['issues' => $this->issues, 'current' => $this->currentTask]);
        }

        return null;
    }
}
