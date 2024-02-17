<?php

declare(strict_types=1);

namespace Meteia\Cache;

use Cake\Chronos\Chronos;
use Meteia\Cache\Configuration\CacheDirectory;
use Meteia\Cache\Configuration\CacheHmacSecretKey;
use Meteia\ValueObjects\Identity\FilesystemPath;

class FileCache
{
    public function __construct(private CacheDirectory $path, private CacheHmacSecretKey $secretKey)
    {
    }

    public function remember(string $key, \DateTimeInterface $expires, callable $default): FilesystemPath
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

                return $dataPath;
            }

            if (!$this->acquireLock($hashedKey, 1)) {
                continue;
            }

            $value = $default();
            if (!$value instanceof FilesystemPath) {
                throw new \Exception('The default value must be a FilesystemPath');
            }
            $value->rename($dataPath);
            $metadataPath->writeJson([
                'expires' => $expires->format(\DateTimeInterface::RFC3339),
            ]);
            $this->releaseLock($hashedKey);

            return $dataPath;
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
