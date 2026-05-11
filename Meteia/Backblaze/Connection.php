<?php

declare(strict_types=1);

namespace Meteia\Backblaze;

use Exception;
use Meteia\Backblaze\Configuration\ApplicationKey;
use Meteia\Backblaze\Configuration\HmacKey;
use Meteia\Backblaze\Configuration\KeyId;
use stdClass;

class Connection
{
    private stdClass $authorization;

    public function __construct(
        KeyId $keyId,
        ApplicationKey $applicationKey,
        private readonly HmacKey $hmacKey,
    ) {
        $credentials = base64_encode($keyId . ':' . $applicationKey);
        $url = 'https://api.backblazeb2.com/b2api/v2/b2_authorize_account';

        $session = curl_init($url);
        \assert($session !== false);

        $headers = [
            'Accept: application/json',
            'Authorization: Basic ' . $credentials,
        ];
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($session, CURLOPT_HTTPGET, true);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        $serverOutput = curl_exec($session);
        $httpStatus = curl_getinfo($session, CURLINFO_HTTP_CODE);
        if ($httpStatus !== 200 || !\is_string($serverOutput)) {
            throw new Exception('Unknown error');
        }

        $authorization = json_decode($serverOutput, false, 512, JSON_THROW_ON_ERROR);
        \assert($authorization instanceof stdClass);
        $this->authorization = $authorization;
    }

    public function upload(string $filePath, string $fileExtension): string
    {
        if (!isset($this->authorization->allowed->bucketId)) {
            throw new Exception('Application Keys that are not limited to a bucket are not supported');
        }
        $bucketId = $this->authorization->allowed->bucketId;
        \assert(\is_string($bucketId));
        $uploadUrl = $this->uploadUrl($bucketId);

        $fileExtension = $this->normalizeExtension($fileExtension);
        $fileHash = hash_hmac_file('sha256', $filePath, $this->hmacKey->bytes());
        \assert($fileHash !== false);
        $fileName =
            implode('/', [
                substr($fileHash, 0, 2),
                substr($fileHash, 2, 2),
                $fileHash,
            ])
            . '.'
            . $fileExtension;

        $fileSize = filesize($filePath);
        \assert($fileSize !== false);
        $resource = fopen($filePath, 'rb');
        \assert($resource !== false);
        $fileData = fread($resource, $fileSize);
        fclose($resource);
        \assert($fileData !== false);

        $contentType = mime_content_type($filePath);
        \assert($contentType !== false);
        $contentSha1 = sha1_file($filePath);
        \assert($contentSha1 !== false);

        $session = curl_init($uploadUrl->uploadUrl);
        \assert($session !== false);
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
        $httpStatus = curl_getinfo($session, CURLINFO_HTTP_CODE);
        if ($httpStatus !== 200 || !\is_string($serverOutput)) {
            throw new Exception('Unknown error');
        }
        json_decode($serverOutput, false, 512, JSON_THROW_ON_ERROR);

        return implode('/', ['https://static.mylzh.net', 'b2', $fileName]);
    }

    private function uploadUrl(string $bucketId): UploadUrl
    {
        $apiUrl = $this->authorization->apiUrl;
        $authorizationToken = $this->authorization->authorizationToken;
        \assert(\is_string($apiUrl));
        \assert(\is_string($authorizationToken));

        $session = curl_init($apiUrl . '/b2api/v2/b2_get_upload_url');
        \assert($session !== false);

        $data = ['bucketId' => $bucketId];
        curl_setopt($session, CURLOPT_POSTFIELDS, json_encode($data, JSON_THROW_ON_ERROR));

        $headers = [
            'Authorization: ' . $authorizationToken,
        ];
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($session, CURLOPT_POST, true);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        $serverOutput = curl_exec($session);
        $httpStatus = curl_getinfo($session, CURLINFO_HTTP_CODE);
        if ($httpStatus !== 200 || !\is_string($serverOutput)) {
            throw new Exception('Unknown error');
        }

        $result = json_decode($serverOutput, false, 512, JSON_THROW_ON_ERROR);
        \assert($result instanceof stdClass);
        \assert(\is_string($result->bucketId));
        \assert(\is_string($result->uploadUrl));
        \assert(\is_string($result->authorizationToken));

        return new UploadUrl($result->bucketId, $result->uploadUrl, $result->authorizationToken);
    }

    private function normalizeExtension(string $extension): string
    {
        $extension = strtolower($extension);
        $map = [
            'jpeg' => 'jpg',
        ];

        return $map[$extension] ?? $extension;
    }
}
