<?php

declare(strict_types=1);

namespace Meteia\Cache;

use Cake\Chronos\Chronos;
use Meteia\Cache\Configuration\CacheDirectory;
use Meteia\Cache\Configuration\CacheHmacSecretKey;
use Meteia\ValueObjects\Identity\FilesystemPath;

readonly class Cache
{
    public function __construct(private CacheDirectory $path, private CacheHmacSecretKey $secretKey)
    {
    }

    public function remember(string $key, \DateTimeInterface $expires, callable $default): mixed
    {
        $hashedKey = hash_hmac('sha256', $key, $this->secretKey->bytes);
        $dataPath = $this->path->join(substr($hashedKey, 0, 2), $hashedKey);
        $metadataPath = new FilesystemPath($dataPath . '.meta');
        $retryUntil = time() + 5;
        while (time() <= $retryUntil) {
            if ($metadataPath->exists() && $dataPath->exists()) {
                $metadata = $metadataPath->readJson();
                $expiresAt = Chronos::createFromFormat(\DateTimeInterface::RFC3339, $metadata->expires);
                if ($expiresAt->isPast()) {
                    $dataPath->delete();
                    $metadataPath->delete();

                    continue;
                }

                $data = $dataPath->read();

                return igbinary_unserialize($data);
            }

            if (!$this->acquireLock($hashedKey, 1)) {
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

        throw new \Exception('Could not acquire lock, retry later.');
    }

    private function acquireLock(string $lockname, int $timeout = 10)
    {
        $until = time() + $timeout;

        while (time() <= $until) {
            if (apcu_add($lockname, 1, $timeout * 10)) {
                return true;
            }
        }

        return false;
    }

    private function releaseLock(string $lockname)
    {
        return apcu_delete($lockname);
    }
}
