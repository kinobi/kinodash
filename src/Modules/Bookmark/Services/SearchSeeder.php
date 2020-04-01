<?php

declare(strict_types=1);

namespace Kinodash\Modules\Bookmark\Services;

use Elastica\Client as ElasticsearchClient;
use Elastica\Document;
use Elastica\Mapping;
use League\Csv\Reader;
use League\Flysystem\Filesystem;

class SearchSeeder
{
    private const BOOKMARKS_CSV = '/bookmark/bookmarks.csv';

    private ElasticsearchClient $elasticClient;

    private Filesystem $filesystem;

    public function __construct(ElasticsearchClient $elasticClient, Filesystem $filesystem)
    {
        $this->elasticClient = $elasticClient;
        $this->filesystem = $filesystem;
    }

    public function __invoke(): void
    {
        $index = $this->elasticClient->getIndex('bookmark');
        $index->create([], true);

        $mapping = new Mapping();

        $mapping->setProperties(
            [
                'title' => ['type' => 'text'],
                'uri' => ['type' => 'text'],
                'tags' => [
                    'properties' => ['tag' => ['type' => 'keyword']],
                ],
            ]
        );

        $index->setMapping($mapping);

        $csv = Reader::createFromStream($this->getCsvFromFS());
        $csv->setHeaderOffset(0);
        $documents = [];
        foreach ($csv as $bookmark) {
            $documents[] = new Document(
                $bookmark['hash'],
                [
                    'title' => $bookmark['title'],
                    'uri' => $bookmark['uri'],
                    'tag' => explode(',', $bookmark['tags']),
                ]
            );
        }

        $index->addDocuments($documents);
        $index->refresh();
    }

    private function getCsvFromFS()
    {
        $csv = fopen('php://memory', 'wb');

        if ($this->filesystem->has(self::BOOKMARKS_CSV)) {
            stream_copy_to_stream($this->filesystem->readStream(self::BOOKMARKS_CSV), $csv);
        } else {
            fputcsv($csv, ['hash', 'uri', 'tags', 'title', 'added', 'status']);
        }

        return $csv;
    }
}
