<?php

declare(strict_types=1);

namespace Meteia\Cache;

use Cake\Chronos\Chronos;
use Meteia\Cache\Configuration\CacheDirectory;
use Meteia\Cache\Configuration\CacheHmacSecretKey;
use Meteia\ValueObjects\Identity\FilesystemPath;

readonly class Cache
{
    public function __construct(
        private CacheDirectory $path,
        private CacheHmacSecretKey $secretKey,
    ) {}

    public function remember(string $key, \DateTimeInterface $expires, callable $default): mixed
    {
        $hashedKey = hash_hmac('sha256', $key, $this->secretKey->bytes);
        $dataPath = $this->path->join(substr($hashedKey, 0, 2), $hashedKey);
        $metadataPath = new FilesystemPath($dataPath . '.meta');
        while (true) {
            if ($metadataPath->exists() && $dataPath->exists()) {
                $metadata = $metadataPath->readJson();
                $expiresAt = Chronos::createFromFormat(\DateTimeInterface::RFC3339, $metadata->expires);
                if ($expiresAt->isFuture()) {
                    $data = $dataPath->read();

                    return igbinary_unserialize($data);
                }
            }

            if (!$this->acquireLock($hashedKey)) {
                continue;
            }

            $data = $default();
            $dataPath->write(igbinary_serialize($data));
            $metadataPath->writeJson([
                'key' => $key,
                'expires' => $expires->format(\DateTimeInterface::RFC3339),
            ]);
            $this->releaseLock($hashedKey);

            return $data;
        }
    }

    private function acquireLock(string $key)
    {
        $timeoutSeconds = 6;
        $until = time() + $timeoutSeconds;

        while (time() <= $until) {
            if (apcu_add($key, 1, $timeoutSeconds * 10)) {
                return true;
            }
        }

        return false;
    }

    private function releaseLock(string $key)
    {
        return apcu_delete($key);
    }
}
