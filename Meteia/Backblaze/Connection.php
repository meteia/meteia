<?php

declare(strict_types=1);

namespace Meteia\Backblaze;

use Meteia\Backblaze\Configuration\ApplicationKey;
use Meteia\Backblaze\Configuration\HmacKey;
use Meteia\Backblaze\Configuration\KeyId;

class Connection
{
    /**
     * @var object
     */
    private $authorization;

    /**
     * @var HmacKey
     */
    private $hmacKey;

    public function __construct(KeyId $keyId, ApplicationKey $applicationKey)
    {
        $this->hmacKey = $hmacKey;

        $credentials = base64_encode($keyId . ':' . $applicationKey);
        $url = 'https://api.backblazeb2.com/b2api/v2/b2_authorize_account';

        $session = curl_init($url);

        // Add headers
        $headers = [];
        $headers[] = 'Accept: application/json';
        $headers[] = 'Authorization: Basic ' . $credentials;
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);  // Add headers

        curl_setopt($session, CURLOPT_HTTPGET, true);  // HTTP GET
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true); // Receive server response
        $server_output = curl_exec($session);
        $http_status = curl_getinfo($session, CURLINFO_HTTP_CODE);
        curl_close($session);
        if ($http_status !== 200) {
            throw new \Exception('Unknown error');
        }

        $this->authorization = json_decode($server_output, false, 512, JSON_THROW_ON_ERROR);
    }

    public function uploadResource($resource): string
    {
        if (!\is_resource($resource)) {
            throw new \InvalidArgumentException('Not a Resource');
        }
        rewind($resource);

        $hashCtx = hash_init('sha256', HASH_HMAC, $this->hmacKey);
        hash_update_stream($hashCtx, $resource);
        $fileHash = hash_final($hashCtx);

        rewind($resource);
        $read_file = fread($resource, filesize($filePath));

        $content_type = mime_content_type($filePath);
        $sha1_of_file_data = sha1_file($filePath);

        $session = curl_init($uploadUrl->uploadUrl);

        // Add read file as post field
        curl_setopt($session, CURLOPT_POSTFIELDS, $read_file);

        // Add headers
        $headers = [];
        $headers[] = 'Authorization: ' . $uploadUrl->authorizationToken;
        $headers[] = 'X-Bz-File-Name: ' . $fileName;
        $headers[] = 'Content-Type: ' . $content_type;
        $headers[] = 'X-Bz-Content-Sha1: ' . $sha1_of_file_data;
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($session, CURLOPT_POST, true); // HTTP POST
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);  // Receive server response
        $server_output = curl_exec($session); // Let's do this!
        $http_status = curl_getinfo($session, CURLINFO_HTTP_CODE);
        curl_close($session); // Clean up
        $result = json_decode($server_output, false, 512, JSON_THROW_ON_ERROR);
        if ($http_status !== 200) {
            throw new \Exception('Unknown error');
        }

        return implode('/', ['https://static.mylzh.net', 'b2', $fileName]);
    }

    public function upload(string $filePath, string $fileExtension): string
    {
        if (!isset($this->authorization->allowed->bucketId)) {
            throw new \Exception('Application Keys that are not limited to a bucket are not supported');
        }
        $uploadUrl = $this->uploadUrl($this->authorization->allowed->bucketId);

        // fopen($filePath);

        $fileExtension = $this->normalizeExtension($fileExtension);
        $fileHash = hash_hmac_file('sha256', $filePath, $this->hmacKey);
        $fileName = implode('/', [substr($fileHash, 0, 2), substr($fileHash, 2, 2), $fileHash]) . '.' . $fileExtension;
    }

    private function uploadUrl(string $bucketId): UploadUrl
    {
        $session = curl_init($this->authorization->apiUrl . '/b2api/v2/b2_get_upload_url');

        // Add post fields
        $data = ['bucketId' => $bucketId];
        $post_fields = json_encode($data);
        curl_setopt($session, CURLOPT_POSTFIELDS, $post_fields);

        // Add headers
        $headers = [];
        $headers[] = 'Authorization: ' . $this->authorization->authorizationToken;
        curl_setopt($session, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($session, CURLOPT_POST, true); // HTTP POST
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);  // Receive server response
        $server_output = curl_exec($session); // Let's do this!
        $http_status = curl_getinfo($session, CURLINFO_HTTP_CODE);
        curl_close($session);
        if ($http_status !== 200) {
            throw new \Exception('Unknown error');
        }

        $result = json_decode($server_output, false, 512, JSON_THROW_ON_ERROR);

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
