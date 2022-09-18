<?php

declare(strict_types=1);

namespace Meteia\ObjectStorage;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Meteia\Files\Contracts\Storage;
use Meteia\Files\Contracts\StoredFile;

class ObjectStorage implements Storage
{
    private const DATE = 'Ymd';
    private const DATETIME = 'Ymd\THis\Z';

    public function __construct(
        private readonly Endpoint $endpoint,
        private readonly BucketName $bucketName,
        private readonly AccessKey $accessKey,
        private readonly SecretKey $secretKey,
        private readonly Region $region,
    ) {
    }

    private function canonicalHeaders(array $headers): string {
        ksort($headers);
        array_map(trim(...), $headers);

        return implode("\n", array_map(function ($key, $value) {
            return sprintf("%s:%s", strtolower($key), trim($value));
        }, array_keys($headers), $headers)) . "\n";
    }

    public function store($src, string $dest, string $mimeType): StoredFile
    {
        $ctx = hash_init('sha256');
        rewind($src);
        hash_update_stream($ctx, $src);
        $hashedPayload = hash_final($ctx);

        $now = new \DateTime('now', new \DateTimeZone('utc'));
        $scope = implode('/', [
            $now->format(self::DATE),
            $this->region,
            's3',
            'aws4_request'
        ]);

        //$policy = $this->policy($credentials, $now);
        //$signature = $this->signature($now, $policy);

        $canonicalUri = $this->endpoint->withPath(implode('/', [$this->bucketName, $dest]));
        $canonicalHeaders = [
            'host' => $this->endpoint->getHost(),
            'x-amz-content-sha256' => $hashedPayload,
            'x-amz-date' => $now->format(self::DATETIME),
        ];
        ksort($canonicalHeaders);
        $signedHeaders = implode(";", array_map(strtolower(...), array_keys($canonicalHeaders)));

        $canonicalRequest = implode("\n", [
            'PUT',
            '/' . $canonicalUri->getPath(),
            '',
            $this->canonicalHeaders($canonicalHeaders),
            $signedHeaders,
            $hashedPayload,
        ]);
        //echo $canonicalRequest;
        //die;
        $canonicalRequestHash = hash('sha256', $canonicalRequest);

        $stringToSign = implode("\n", [
            'AWS4-HMAC-SHA256',
            $now->format(self::DATETIME),
            $scope,
            $canonicalRequestHash,
        ]);

        //echo $stringToSign;
        //die;
        $signature = $this->sign($now, $stringToSign);

        //echo $signature;
        //die;
        rewind($src);

        $client = new Client();
        try {
            $response = $client->request('PUT', (string) $canonicalUri, [
                'headers' => [
                    'Authorization' => 'AWS4-HMAC-SHA256 ' . implode(', ', [
                        sprintf("Credential=%s/%s", $this->accessKey, $scope),
                        "SignedHeaders=$signedHeaders",
                        "Signature=$signature",
                    ]),
                    ...$canonicalHeaders,
                ],
                'debug' => true,
                //'form_params' => [
                //    'Content-Type' => $mimeType,
                //    'acl' => 'public-read',
                //    'key' => $dest,
                //    'policy' =>  $policy,
                //    'x-amz-algorithm' => 'AWS4-HMAC-SHA256',
                //    'x-amz-credential' => $credentials,
                //    'x-amz-signature' => $signature,
                //],
                'body' => $src,
            ]);
        } catch (ClientException $t) {
            echo $t->getResponse()->getBody()->getContents();
            die;
        }
        jdd($response->getStatusCode(), $response->getBody());
    }

    private function sign(\DateTime $now, string $content): string {
        $dateKey = hash_hmac('sha256', $now->format(self::DATE), 'AWS4' . $this->secretKey, true);
        $dateRegionKey = hash_hmac('sha256', (string) $this->region, $dateKey, true);
        $dateRegionServiceKey = hash_hmac('sha256', 's3', $dateRegionKey, true);
        $signingKey = hash_hmac('sha256', 'aws4_request', $dateRegionServiceKey, true);

        return hash_hmac('sha256', $content, $signingKey);
    }

    private function policy(string $credentials, \DateTime $now): string {
        return base64_encode(json_encode([
            'expiration' => $now->add(new \DateInterval('PT1H'))->format('Y-m-d\TH:i:s\Z'),
            'conditions' => [
                ['acl' => 'public-read'],
                ['bucket' => (string) $this->bucketName],
                ['starts-with', '$Content-Type', ''],
                ['starts-with', '$key', ''],
                ['x-amz-algorithm' => 'AWS4-HMAC-SHA256'],
                ['x-amz-credential' => $credentials],
                ['x-amz-date' => $now->format('Ymd\THis\Z')]
            ],
        ]));
    }
}
