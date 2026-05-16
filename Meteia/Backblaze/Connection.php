<?php

declare(strict_types=1);

namespace Meteia\Backblaze;

use Exception;
use Meteia\Backblaze\Configuration\ApplicationKey;
use Meteia\Backblaze\Configuration\HmacKey;
use Meteia\Backblaze\Configuration\KeyId;

class Connection
{
    private BackblazeAuthorization $authorization;

    public function __construct(
        KeyId $keyId,
        ApplicationKey $applicationKey,
        private readonly HmacKey $hmacKey,
    ) {
        $credentials = base64_encode((string) $keyId . ':' . (string) $applicationKey);
        $url = 'https://api.backblazeb2.com/b2api/v2/b2_authorize_account';

        $session = curl_init($url);
        \assert($session !== false, 'Backblaze authorization request must initialize cURL');

        $headers = [
            'Accept: application/json',
            'Authorization: Basic ' . $credentials,
        ];
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($session, CURLOPT_HTTPGET, true);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        $serverOutput = curl_exec($session);
        if ((int) curl_getinfo($session, CURLINFO_HTTP_CODE) !== 200 || !\is_string($serverOutput)) {
            throw new Exception('Unknown error');
        }

        $this->authorization = BackblazeAuthorization::fromDecodedPayload(
            json_decode($serverOutput, true, 512, JSON_THROW_ON_ERROR),
        );
    }

    public function upload(string $filePath, string $fileExtension): string
    {
        $uploadUrl = $this->uploadUrl($this->authorization->limitedBucketId());

        $fileExtension = $this->normalizeExtension($fileExtension);
        $fileHash = hash_hmac_file('sha256', $filePath, $this->hmacKey->bytes());
        \assert($fileHash !== false, 'Uploaded file hash must be readable');
        $fileName =
            implode('/', [
                substr($fileHash, 0, 2),
                substr($fileHash, 2, 2),
                $fileHash,
            ])
            . '.'
            . $fileExtension;

        $fileSize = filesize($filePath);
        \assert($fileSize !== false, 'Uploaded file size must be readable');
        $resource = fopen($filePath, 'rb');
        \assert($resource !== false, 'Uploaded file must open for reading');
        $fileData = fread($resource, $fileSize);
        fclose($resource);
        \assert($fileData !== false, 'Uploaded file data must be readable');

        $contentType = mime_content_type($filePath);
        \assert($contentType !== false, 'Uploaded file content type must be readable');
        $contentSha1 = sha1_file($filePath);
        \assert($contentSha1 !== false, 'Uploaded file SHA-1 must be readable');

        $session = curl_init($uploadUrl->uploadUrl);
        \assert($session !== false, 'Backblaze upload request must initialize cURL');
        curl_setopt($session, CURLOPT_POSTFIELDS, $fileData);

        $headers = [
            'Authorization: ' . $uploadUrl->authorizationToken,
            'X-Bz-File-Name: ' . $fileName,
            'Content-Type: ' . $contentType,
            'X-Bz-Content-Sha1: ' . $contentSha1,
        ];
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        $serverOutput = curl_exec($session);
        if ((int) curl_getinfo($session, CURLINFO_HTTP_CODE) !== 200 || !\is_string($serverOutput)) {
            throw new Exception('Unknown error');
        }
        json_decode($serverOutput, false, 512, JSON_THROW_ON_ERROR);

        return implode('/', ['https://static.mylzh.net', 'b2', $fileName]);
    }

    private function uploadUrl(string $bucketId): UploadUrl
    {
        $session = curl_init($this->authorization->uploadUrlEndpoint());
        \assert($session !== false, 'Backblaze upload URL request must initialize cURL');

        $data = ['bucketId' => $bucketId];
        curl_setopt($session, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));

        $headers = [
            $this->authorization->authorizationHeader(),
        ];
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        $serverOutput = curl_exec($session);
        if ((int) curl_getinfo($session, CURLINFO_HTTP_CODE) !== 200 || !\is_string($serverOutput)) {
            throw new Exception('Unknown error');
        }

        return $this->uploadUrlFromDecodedPayload(json_decode($serverOutput, true, 512, JSON_THROW_ON_ERROR));
    }

    private function normalizeExtension(string $extension): string
    {
        $extension = strtolower($extension);
        $map = [
            'jpeg' => 'jpg',
        ];

        return $map[$extension] ?? $extension;
    }

    private function uploadUrlFromDecodedPayload(mixed $payload): UploadUrl
    {
        if (!\is_array($payload)) {
            throw new Exception('Backblaze upload URL response must decode to an object');
        }

        return new UploadUrl(
            $this->requiredResponseText($payload, 'bucketId'),
            $this->requiredResponseText($payload, 'uploadUrl'),
            $this->requiredResponseText($payload, 'authorizationToken'),
        );
    }

    /**
     * @param array<array-key, mixed> $payload
     */
    private function requiredResponseText(array $payload, string $name): string
    {
        if (!\is_string($payload[$name] ?? null)) {
            throw new Exception('Backblaze upload URL response is missing string field: ' . $name);
        }

        return $payload[$name];
    }
}
