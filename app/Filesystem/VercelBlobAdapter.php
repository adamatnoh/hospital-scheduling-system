<?php

namespace App\Filesystem;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use League\Flysystem\Config;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\UnableToCheckExistence;
use League\Flysystem\UnableToCopyFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToRetrieveMetadata;
use League\Flysystem\UnableToSetVisibility;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\Visibility;
use Throwable;

/**
 * Minimal Flysystem adapter for a public Vercel Blob store.
 *
 * Used in production on Vercel, where the local disk is ephemeral, so
 * announcement posters survive between requests and deployments.
 */
class VercelBlobAdapter implements FilesystemAdapter
{
    private const API_URL = 'https://vercel.com/api/blob';

    private const API_VERSION = '13';

    private Client $http;

    private string $storeId;

    public function __construct(private string $token, private string $prefix = '', ?Client $http = null)
    {
        $parts = explode('_', $token);
        $this->storeId = strtolower($parts[3] ?? '');
        $this->prefix = trim($prefix, '/');
        $this->http = $http ?? new Client(['timeout' => 30]);
    }

    public function getUrl(string $path): string
    {
        return 'https://'.$this->storeId.'.public.blob.vercel-storage.com/'.$this->encodePath($this->prefixed($path));
    }

    public function fileExists(string $path): bool
    {
        try {
            return $this->head($path) !== null;
        } catch (Throwable $e) {
            throw UnableToCheckExistence::forLocation($path, $e);
        }
    }

    public function directoryExists(string $path): bool
    {
        try {
            return $this->list($path.'/', 1)['blobs'] !== [];
        } catch (Throwable $e) {
            throw UnableToCheckExistence::forLocation($path, $e);
        }
    }

    public function write(string $path, string $contents, Config $config): void
    {
        $this->upload($path, $contents, $config);
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        $this->upload($path, $contents, $config);
    }

    public function read(string $path): string
    {
        try {
            return (string) $this->http->get($this->getUrl($path), ['query' => ['v' => microtime(true)]])->getBody();
        } catch (Throwable $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage(), $e);
        }
    }

    public function readStream(string $path)
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $this->read($path));
        rewind($stream);

        return $stream;
    }

    public function delete(string $path): void
    {
        try {
            $this->api('POST', '/delete', ['json' => ['urls' => [$this->getUrl($path)]]]);
        } catch (Throwable $e) {
            throw UnableToDeleteFile::atLocation($path, $e->getMessage(), $e);
        }
    }

    public function deleteDirectory(string $path): void
    {
        $cursor = null;

        do {
            $page = $this->list(rtrim($path, '/').'/', 1000, $cursor);
            $urls = array_column($page['blobs'], 'url');

            if ($urls !== []) {
                $this->api('POST', '/delete', ['json' => ['urls' => $urls]]);
            }

            $cursor = $page['hasMore'] ? $page['cursor'] : null;
        } while ($cursor);
    }

    public function createDirectory(string $path, Config $config): void
    {
        // Blob stores have no real directories.
    }

    public function setVisibility(string $path, string $visibility): void
    {
        if ($visibility !== Visibility::PUBLIC) {
            throw UnableToSetVisibility::atLocation($path, 'This Vercel Blob store only serves public files.');
        }
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, null, Visibility::PUBLIC);
    }

    public function mimeType(string $path): FileAttributes
    {
        return $this->attributes($path, 'mimeType');
    }

    public function lastModified(string $path): FileAttributes
    {
        return $this->attributes($path, 'lastModified');
    }

    public function fileSize(string $path): FileAttributes
    {
        return $this->attributes($path, 'fileSize');
    }

    public function listContents(string $path, bool $deep): iterable
    {
        $prefix = trim($path, '/') === '' ? '' : trim($path, '/').'/';
        $cursor = null;

        do {
            $page = $this->list($prefix, 1000, $cursor, $deep ? null : 'folded');

            foreach ($page['blobs'] as $blob) {
                yield new FileAttributes(
                    $this->unprefixed($blob['pathname']),
                    $blob['size'] ?? null,
                    Visibility::PUBLIC,
                    isset($blob['uploadedAt']) ? strtotime($blob['uploadedAt']) : null,
                );
            }

            foreach ($page['folders'] ?? [] as $folder) {
                yield new DirectoryAttributes(rtrim($this->unprefixed($folder), '/'));
            }

            $cursor = $page['hasMore'] ? $page['cursor'] : null;
        } while ($cursor);
    }

    public function move(string $source, string $destination, Config $config): void
    {
        $this->copy($source, $destination, $config);
        $this->delete($source);
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            $this->upload($destination, $this->read($source), $config);
        } catch (Throwable $e) {
            throw UnableToCopyFile::fromLocationTo($source, $destination, $e);
        }
    }

    /**
     * @param  string|resource  $contents
     */
    private function upload(string $path, $contents, Config $config): void
    {
        $headers = [
            'x-vercel-blob-access' => 'public',
            'x-add-random-suffix' => '0',
            'x-allow-overwrite' => '1',
            'x-cache-control-max-age' => '60',
        ];

        $mimeType = $config->get('mimetype') ?? $this->guessMimeType($path);

        if ($mimeType) {
            $headers['x-content-type'] = $mimeType;
        }

        try {
            $this->api('PUT', '/?'.http_build_query(['pathname' => $this->prefixed($path)]), [
                'headers' => $headers,
                'body' => $contents,
            ]);
        } catch (Throwable $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage(), $e);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function head(string $path): ?array
    {
        try {
            return $this->api('GET', '?'.http_build_query(['url' => $this->getUrl($path)]));
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 404) {
                return null;
            }

            throw $e;
        }
    }

    private function attributes(string $path, string $type): FileAttributes
    {
        try {
            $blob = $this->head($path);
        } catch (Throwable $e) {
            throw UnableToRetrieveMetadata::create($path, $type, $e->getMessage(), $e);
        }

        if ($blob === null) {
            throw UnableToRetrieveMetadata::create($path, $type, 'File not found.');
        }

        return new FileAttributes(
            $path,
            $blob['size'] ?? null,
            Visibility::PUBLIC,
            isset($blob['uploadedAt']) ? strtotime($blob['uploadedAt']) : null,
            $blob['contentType'] ?? null,
        );
    }

    /**
     * @return array{blobs: array<int, array<string, mixed>>, folders?: array<int, string>, cursor?: string, hasMore: bool}
     */
    private function list(string $prefix, int $limit, ?string $cursor = null, ?string $mode = null): array
    {
        $query = array_filter([
            'prefix' => $this->prefixed($prefix),
            'limit' => $limit,
            'cursor' => $cursor,
            'mode' => $mode,
        ], fn ($value) => $value !== null && $value !== '');

        $page = $this->api('GET', '?'.http_build_query($query)) ?? [];

        return $page + ['blobs' => [], 'hasMore' => false];
    }

    /**
     * @return array<string, mixed>|null
     *
     * @throws GuzzleException
     */
    private function api(string $method, string $uri, array $options = []): ?array
    {
        $options['headers'] = ($options['headers'] ?? []) + [
            'authorization' => 'Bearer '.$this->token,
            'x-api-version' => self::API_VERSION,
            'x-vercel-blob-store-id' => $this->storeId,
        ];

        $response = $this->http->request($method, self::API_URL.$uri, $options);

        return json_decode((string) $response->getBody(), true);
    }

    private function prefixed(string $path): string
    {
        $path = ltrim($path, '/');

        return $this->prefix === '' ? $path : $this->prefix.'/'.$path;
    }

    private function unprefixed(string $path): string
    {
        return $this->prefix !== '' && str_starts_with($path, $this->prefix.'/')
            ? substr($path, strlen($this->prefix) + 1)
            : $path;
    }

    private function encodePath(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    private function guessMimeType(string $path): ?string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'pdf' => 'application/pdf',
            default => null,
        };
    }
}
