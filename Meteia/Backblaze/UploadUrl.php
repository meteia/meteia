<?php

declare(strict_types=1);

namespace Meteia\Backblaze;

use SensitiveParameter;

class UploadUrl
{
    /**
     * @var string
     */
    public $bucketId;

    /**
     * @var string
     */
    public $uploadUrl;

    /**
     * @var string
     */
    public $authorizationToken;

    public function __construct(string $bucketId, string $uploadUrl, #[SensitiveParameter] string $authorizationToken)
    {
        $this->bucketId = $bucketId;
        $this->uploadUrl = $uploadUrl;
        $this->authorizationToken = $authorizationToken;
    }
}
