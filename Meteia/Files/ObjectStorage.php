<?php

declare(strict_types=1);

namespace Meteia\Files;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use Meteia\Files\Configuration\AccessKey;
use Meteia\Files\Configuration\BucketName;
use Meteia\Files\Configuration\Endpoint;
use Meteia\Files\Configuration\PublicUri;
use Meteia\Files\Configuration\Region;
use Meteia\Files\Configuration\SecretKey;
use Meteia\Files\Contracts\Storage;
use Meteia\ValueObjects\Identity\Resource;
use Meteia\ValueObjects\Identity\Uri;

class ObjectStorage implements Storage
{
    private const DATE = 'Ymd';
    private const DATETIME = 'Ymd\THis\Z';

    public function __construct(
        private readonly Endpoint $endpoint,
        private readonly PublicUri $publicUri,
        private readonly BucketName $bucketName,
        private readonly AccessKey $accessKey,
        private readonly SecretKey $secretKey,
        private readonly Region $region,
        private readonly ExtensionMimeTypeDetector $extensionMimeTypeDetector,
    ) {}

    #[\Override]
    public function canonicalUri(string $dest): Uri
    {
        return $this->publicUri->withPath($dest);
    }

    #[\Override]
    public function internalUri(string $dest): Uri
    {
        return new Uri($this->endpoint->withPath(implode('/', [
            $this->bucketName,
            $dest,
        ])));
    }

    #[\Override]
    public function exists(string $dest): bool
    {
        $client = new Client();

        try {
            $publicUri = $this->canonicalUri($dest);

            return $client->head($publicUri)->getStatusCode() === 200;
        } catch (ClientException) {
            return false;
        }
    }

    #[\Override]
    public function delete(string $dest): void
    {
        // noop
    }

    #[\Override]
    public function store(Resource $src, string $dest): StoredFile
    {
        $publicUri = $this->canonicalUri($dest);
        if ($this->exists($dest)) {
            return new StoredFile($publicUri);
        }

        $internalUri = $this->internalUri($dest);
        $hashedPayload = $src->hash('sha256')->hex();

        $now = new \DateTime('now', new \DateTimeZone('utc'));
        $scope = implode('/', [
            $now->format(self::DATE),
            $this->region,
            's3',
            'aws4_request',
        ]);

        $contentType = $this->extensionMimeTypeDetector->detectMimeTypeFromFile($dest) ?? 'application/octet-stream';
        $contentLength = $src->size();
        if (!$contentLength) {
            throw new \Exception('Trying to upload an empty file?');
        }

        $canonicalHeaders = [
            'host' => $this->endpoint->getHost(),
            'content-length' => $contentLength,
            'content-type' => $contentType,
            'cache-control' => 'public, max-age=31536000, immutable',
            'x-amz-content-sha256' => $hashedPayload,
            'x-amz-date' => $now->format(self::DATETIME),
        ];
        ksort($canonicalHeaders);
        $signedHeaders = implode(';', array_map(strtolower(...), array_keys($canonicalHeaders)));

        $canonicalRequest = implode("\n", [
            'PUT',
            $internalUri->getPath(),
            '',
            $this->canonicalHeaders($canonicalHeaders),
            $signedHeaders,
            $hashedPayload,
        ]);
        $canonicalRequestHash = hash('sha256', $canonicalRequest);

        $stringToSign = implode("\n", [
            'AWS4-HMAC-SHA256',
            $now->format(self::DATETIME),
            $scope,
            $canonicalRequestHash,
        ]);

        $signature = $this->sign($now, $stringToSign);

        $client = new Client();

        try {
            $client->request('PUT', (string) $internalUri, [
                'headers' => [
                    'Authorization' =>
                        'AWS4-HMAC-SHA256 '
                        . implode(', ', [
                            sprintf('Credential=%s/%s', $this->accessKey, $scope),
                            "SignedHeaders={$signedHeaders}",
                            "Signature={$signature}",
                        ]),
                    ...$canonicalHeaders,
                ],
                'body' => $src->resource(),
            ]);
        } catch (ClientException $e) {
            echo $e->getResponse()->getBody()->getContents() . PHP_EOL;

            throw $e;
        }

        return new StoredFile($publicUri);
    }

    private function canonicalHeaders(array $headers): string
    {
        ksort($headers);
        array_map(trim(...), $headers);

        return (
            implode("\n", array_map(
                static fn($key, $value) => sprintf('%s:%s', strtolower($key), trim((string) $value)),
                array_keys($headers),
                $headers,
            )) . "\n"
        );
    }

    private function sign(\DateTime $now, string $content): string
    {
        $dateKey = hash_hmac('sha256', $now->format(self::DATE), 'AWS4' . $this->secretKey, true);
        $dateRegionKey = hash_hmac('sha256', (string) $this->region, $dateKey, true);
        $dateRegionServiceKey = hash_hmac('sha256', 's3', $dateRegionKey, true);
        $signingKey = hash_hmac('sha256', 'aws4_request', $dateRegionServiceKey, true);

        return hash_hmac('sha256', $content, $signingKey);
    }
}
