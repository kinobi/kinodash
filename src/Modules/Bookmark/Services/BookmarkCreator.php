<?php

declare(strict_types=1);

namespace Kinodash\Modules\Bookmark\Services;

use Carbon\CarbonImmutable;
use DOMDocument;
use DOMXPath;
use GuzzleHttp\Client as HttpClient;
use InvalidArgumentException;
use Kinodash\Modules\Bookmark\Bookmark;
use League\Csv\Reader;
use League\Csv\Writer;
use League\Flysystem\Filesystem;
use Psr\Http\Message\ResponseInterface;
use SplTempFileObject;

class BookmarkCreator
{
    private const BOOKMARKS_CSV = '/bookmark/bookmarks.csv';

    private Filesystem $filesystem;

    private HttpClient $httpClient;

    public function __construct(Filesystem $filesystem, HttpClient $httpClient)
    {
        $this->filesystem = $filesystem;
        $this->httpClient = $httpClient;
    }

    public function __invoke(string $uriString, string ...$tags): void
    {
        $this->validateUri($uriString);

        $csv = Reader::createFromStream($this->getCsvFromFS());
        $csv->setHeaderOffset(0);
        $bookmark = Bookmark::createFromString($uriString);

        if ($this->isDuplicate($csv, $bookmark)) {
            return;
        }

        // Get title and status
        $doc = $this->httpClient->get($bookmark->uri());
        $docStatus = $doc->getStatusCode();
        $docTitle = $this->getBookmarkTitle($doc);

        // Prepare Tags
        $tagString = $this->prepareTags($tags);

        // Write in CSV
        $newCsv = $this->createNewCsv($tagString, $csv, $bookmark, $docTitle, $docStatus);

        $this->filesystem->put(self::BOOKMARKS_CSV, $newCsv->getContent());
    }

    private function validateUri(string $uriString): void
    {
        if (filter_var($uriString, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(sprintf('Not a valid URI: "%s"', $uriString));
        }
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

    private function isDuplicate(Reader $csvReader, $bookmark): bool
    {
        foreach ($csvReader->fetchColumn('hash') as $hash) {
            if ($hash === $bookmark->hash()) {
                return true;
            }
        }

        return false;
    }

    private function getBookmarkTitle(ResponseInterface $doc): string
    {
        $docDom = new DOMDocument();
        libxml_use_internal_errors(true);
        $docDom->loadHTML($doc->getBody()->getContents());
        $docXpath = new DOMXPath($docDom);

        return $docXpath->query('//title')->item(0)->nodeValue;
    }

    private function prepareTags(array $tags): string
    {
        $tags = array_map(fn(string $tag) => trim($tag), $tags);
        sort($tags, SORT_NATURAL);

        return implode(',', $tags);
    }

    private function createNewCsv(
        string $tags,
        Reader $csv,
        Bookmark $bookmark,
        string $docTitle,
        int $docStatus
    ): Writer {
        $newCsv = Writer::createFromFileObject(new SplTempFileObject());
        $newCsv->insertOne($csv->getHeader());
        $newCsv->insertAll($csv->getRecords());
        $newCsv->insertOne(
            [
                $bookmark->hash(),
                (string)$bookmark->uri(),
                $tags,
                $docTitle,
                CarbonImmutable::now()->toIso8601String(),
                $docStatus,
            ]
        );

        return $newCsv;
    }
}
