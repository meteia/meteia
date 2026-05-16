<?php

declare(strict_types=1);

namespace Meteia\Backblaze;

use Exception;
use SensitiveParameter;

final readonly class BackblazeAuthorization
{
    public function __construct(
        private string $apiUrl,
        #[SensitiveParameter]
        private string $authorizationToken,
        private string $limitedBucketId,
    ) {}

    /**
     * @param array<array-key, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            self::requiredText($payload, 'apiUrl'),
            self::requiredText($payload, 'authorizationToken'),
            self::optionalBucketId($payload),
        );
    }

    public static function fromDecodedPayload(mixed $payload): self
    {
        if (!\is_array($payload)) {
            throw new Exception('Backblaze authorization response must decode to an object');
        }

        return self::fromPayload($payload);
    }

    public function limitedBucketId(): string
    {
        if ($this->limitedBucketId === '') {
            throw new Exception('Application Keys that are not limited to a bucket are not supported');
        }

        return $this->limitedBucketId;
    }

    public function uploadUrlEndpoint(): string
    {
        return $this->apiUrl . '/b2api/v2/b2_get_upload_url';
    }

    public function authorizationHeader(): string
    {
        return 'Authorization: ' . $this->authorizationToken;
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    private static function requiredText(array $payload, string $name): string
    {
        if (!\is_string($payload[$name] ?? null)) {
            throw new Exception('Backblaze authorization response is missing string field: ' . $name);
        }

        return $payload[$name];
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    private static function optionalBucketId(array $payload): string
    {
        if (!\is_array($payload['allowed'] ?? null)) {
            return '';
        }

        if (!\is_string($payload['allowed']['bucketId'] ?? null)) {
            return '';
        }

        return $payload['allowed']['bucketId'];
    }
}
