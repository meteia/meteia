<?php

declare(strict_types=1);

namespace Meteia\Backblaze\Api;

use GuzzleHttp\Client;
use Meteia\Backblaze\Configuration\ApplicationKey;
use Meteia\Backblaze\Configuration\KeyId;

class Api
{
    public function authorizeAccount(KeyId $keyId, ApplicationKey $applicationKey): AuthorizedAccount
    {
        $client = new Client();

        $response = $client->get('https://api.backblazeb2.com/b2api/v2/b2_authorize_account', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . base64_encode($keyId . ':' . $applicationKey),
            ],
        ]);
        $result = json_decode($response->getBody()->getContents(), false, 512, JSON_THROW_ON_ERROR);

        return new AuthorizedAccount($result['apiUrl'], $result['authorizationToken']);
    }
}
