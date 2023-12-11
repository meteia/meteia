<?php

declare(strict_types=1);

namespace Meteia\Backblaze\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class AuthorizedAccount
{
    public function __construct(string $apiUrl, string $authorizationToken)
    {
        $this->client = new Client([
            'base_uri' => $apiUrl,
            'headers' => [
                'Authorization' => $authorizationToken,
            ],
        ]);
    }

    // public function send(RequestInterface $request)
    // {
    //    return $this->client->send($request);
    // }

    public function upload(StreamInterface $stream, string $bucket, string $path): void
    {
        dd('TODO');
        $request = new Request('POST', '', [
            'headers' => [],
        ]);

        $response = $this->client->send($request);
    }

    private function getUploadUrl(): void
    {
        dd('TODO');
        $request = new Request('GET', '', [
            'headers' => [],
        ]);

        $response = $this->client->send($request);
    }
}

//
// {
// "absoluteMinimumPartSize": 5000000,
// "accountId": "YOUR_ACCOUNT_ID",
// "allowed": {
// "bucketId": "BUCKET_ID",
// "bucketName": "BUCKET_NAME",
// "capabilities": [
// "listBuckets",
// "listFiles",
// "readFiles",
// "shareFiles",
// "writeFiles",
// "deleteFiles"
// ],
// "namePrefix": null
// },
// "apiUrl": "https://apiNNN.backblazeb2.com",
//  "authorizationToken": "4_0022623512fc8f80000000001_0186e431_d18d02_acct_tH7VW03boebOXayIc43-sxptpfA=",
//  "downloadUrl": "https://f002.backblazeb2.com",
//  "recommendedPartSize": 100000000
// }
