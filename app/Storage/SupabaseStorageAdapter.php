<?php

namespace App\Storage;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\DirectoryAttributes;
use League\Flysystem\UnableToWriteFile;
use League\Flysystem\UnableToReadFile;
use League\Flysystem\UnableToDeleteFile;
use League\Flysystem\UnableToCheckFileExistence;

class SupabaseStorageAdapter implements FilesystemAdapter
{
    protected Client $client;
    protected string $bucket;
    protected string $url;
    protected string $publicUrl;

    public function __construct(array $config)
    {
        $this->bucket = $config['bucket'];
        $this->url = rtrim($config['url'], '/');
        $this->publicUrl = "{$this->url}/storage/v1/object/public/{$this->bucket}";

        $this->client = new Client([
            'base_uri' => "{$this->url}/storage/v1/",
            'headers' => [
                'Authorization' => 'Bearer ' . $config['key'],
                'apikey' => $config['key'],
            ],
        ]);
    }

    public function fileExists(string $path): bool
    {
        try {
            $response = $this->client->head("object/{$this->bucket}/{$path}");
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function directoryExists(string $path): bool
    {
        return true; // Supabase doesn't have directory concept
    }

    public function write(string $path, string $contents, Config $config): void
    {
        try {
            // Detect MIME type from file extension if not provided
            $mimeType = $config->get('mimetype');
            if (!$mimeType) {
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                $mimeTypes = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'pdf' => 'application/pdf',
                ];
                $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
            }

            $this->client->post("object/{$this->bucket}/{$path}", [
                'headers' => [
                    'Content-Type' => $mimeType,
                    'x-upsert' => 'true',
                ],
                'body' => $contents,
            ]);
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
        }
    }

    public function writeStream(string $path, $contents, Config $config): void
    {
        try {
            // If it's a resource stream, read it
            if (is_resource($contents)) {
                $streamContents = stream_get_contents($contents);
                rewind($contents);
            } else {
                $streamContents = $contents;
            }

            // Detect MIME type from file extension if not provided
            $mimeType = $config->get('mimetype');
            if (!$mimeType) {
                $extension = pathinfo($path, PATHINFO_EXTENSION);
                $mimeTypes = [
                    'jpg' => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'png' => 'image/png',
                    'gif' => 'image/gif',
                    'pdf' => 'application/pdf',
                ];
                $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
            }

            Log::info('Uploading to Supabase', [
                'path' => $path,
                'bucket' => $this->bucket,
                'size' => strlen($streamContents),
                'mimetype' => $mimeType,
                'url' => "object/{$this->bucket}/{$path}"
            ]);

            $response = $this->client->post("object/{$this->bucket}/{$path}", [
                'headers' => [
                    'Content-Type' => $mimeType,
                    'x-upsert' => 'true',
                ],
                'body' => $streamContents,
            ]);

            Log::info('Supabase upload response', [
                'status' => $response->getStatusCode(),
                'body' => $response->getBody()->getContents()
            ]);
        } catch (\Exception $e) {
            Log::error('Supabase upload failed', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            throw UnableToWriteFile::atLocation($path, $e->getMessage());
        }
    }

    public function read(string $path): string
    {
        try {
            $response = $this->client->get("object/{$this->bucket}/{$path}");
            return $response->getBody()->getContents();
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage());
        }
    }

    public function readStream(string $path)
    {
        try {
            $response = $this->client->get("object/{$this->bucket}/{$path}", [
                'stream' => true,
            ]);
            return $response->getBody()->detach();
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage());
        }
    }

    public function delete(string $path): void
    {
        try {
            $this->client->delete("object/{$this->bucket}/{$path}");
        } catch (\Exception $e) {
            throw UnableToDeleteFile::atLocation($path, $e->getMessage());
        }
    }

    public function deleteDirectory(string $path): void
    {
        // Supabase doesn't have directories, nothing to do
    }

    public function createDirectory(string $path, Config $config): void
    {
        // Supabase doesn't have directories, nothing to do
    }

    public function setVisibility(string $path, string $visibility): void
    {
        // Supabase bucket visibility is set at bucket level
    }

    public function visibility(string $path): FileAttributes
    {
        return new FileAttributes($path, null, 'public');
    }

    public function mimeType(string $path): FileAttributes
    {
        try {
            $response = $this->client->head("object/{$this->bucket}/{$path}");
            $contentType = $response->getHeader('Content-Type')[0] ?? null;
            return new FileAttributes($path, null, null, null, $contentType);
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage());
        }
    }

    public function lastModified(string $path): FileAttributes
    {
        try {
            $response = $this->client->head("object/{$this->bucket}/{$path}");
            $lastModified = $response->getHeader('Last-Modified')[0] ?? null;
            $timestamp = $lastModified ? strtotime($lastModified) : null;
            return new FileAttributes($path, null, null, $timestamp);
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage());
        }
    }

    public function fileSize(string $path): FileAttributes
    {
        try {
            $response = $this->client->head("object/{$this->bucket}/{$path}");
            $size = $response->getHeader('Content-Length')[0] ?? null;
            return new FileAttributes($path, (int) $size);
        } catch (\Exception $e) {
            throw UnableToReadFile::fromLocation($path, $e->getMessage());
        }
    }

    public function listContents(string $path, bool $deep): iterable
    {
        try {
            $response = $this->client->post("object/list/{$this->bucket}", [
                'json' => [
                    'prefix' => $path,
                    'limit' => 1000,
                ],
            ]);

            $contents = json_decode($response->getBody()->getContents(), true);

            foreach ($contents as $item) {
                if (isset($item['name'])) {
                    yield new FileAttributes($item['name']);
                }
            }
        } catch (\Exception $e) {
            // Return empty if listing fails
            return;
        }
    }

    public function move(string $source, string $destination, Config $config): void
    {
        try {
            $this->client->post("object/move", [
                'json' => [
                    'bucketId' => $this->bucket,
                    'sourceKey' => $source,
                    'destinationKey' => $destination,
                ],
            ]);
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($destination, $e->getMessage());
        }
    }

    public function copy(string $source, string $destination, Config $config): void
    {
        try {
            $this->client->post("object/copy", [
                'json' => [
                    'bucketId' => $this->bucket,
                    'sourceKey' => $source,
                    'destinationKey' => $destination,
                ],
            ]);
        } catch (\Exception $e) {
            throw UnableToWriteFile::atLocation($destination, $e->getMessage());
        }
    }

    /**
     * Get the public URL for a file.
     */
    public function getUrl(string $path): string
    {
        return "{$this->publicUrl}/{$path}";
    }
}
