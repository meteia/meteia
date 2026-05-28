<?php

declare(strict_types=1);

namespace Meteia\Realtime;

use DateInterval;
use JsonException;
use Meteia\Cryptography\Errors\DecryptionFailed;
use Meteia\Cryptography\SecretKey\XChaCha20Poly1305;
use Meteia\Time\Clock;
use SensitiveParameter;

final readonly class LiveViewSessionTokens
{
    private const string ASSOCIATED_DATA = 'meteia.liveview.v1';

    public function __construct(
        private XChaCha20Poly1305 $cipher,
        private LiveViewSessionSecretKey $key,
        private Clock $clock,
        private LiveViewSessionLifetime $lifetime,
    ) {}

    /**
     * @param list<LiveViewTopic> $topics
     */
    public function issue(string $subject, TabId $tab, array $topics): IssuedLiveViewSession
    {
        $now = $this->clock->now();
        $expiresAt = $now->add(new DateInterval('PT' . $this->lifetime->seconds() . 'S'));

        $payload = json_encode([
            'sub' => $subject,
            'tab' => $tab->token(),
            'topics' => array_map(static fn(LiveViewTopic $t): string => $t->toNative(), $topics),
            'iat' => $now->getTimestamp(),
            'exp' => $expiresAt->getTimestamp(),
        ], JSON_THROW_ON_ERROR);

        $result = $this->cipher->encrypt($payload, self::ASSOCIATED_DATA, $this->key);

        return new IssuedLiveViewSession($tab, new LiveViewSessionToken($result->ciphertext), $expiresAt);
    }

    public function verify(#[SensitiveParameter] string $token): LiveViewSessionVerification
    {
        try {
            $result = $this->cipher->decrypt($token, self::ASSOCIATED_DATA, $this->key);
        } catch (DecryptionFailed $e) {
            return new LiveViewSessionRejected('invalid token: ' . $e->getMessage());
        }

        try {
            /** @var array{sub?: string, tab?: string, topics?: list<string>, exp?: int} $claims */
            $claims = json_decode($result->plaintext, true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return new LiveViewSessionRejected('malformed token payload: ' . $e->getMessage());
        }

        $subject = $claims['sub'] ?? null;
        $tab = $claims['tab'] ?? null;
        $rawTopics = $claims['topics'] ?? null;
        $expiresAt = $claims['exp'] ?? null;
        if (!\is_string($subject) || !\is_string($tab) || !\is_array($rawTopics) || !\is_int($expiresAt)) {
            return new LiveViewSessionRejected('malformed token claims');
        }

        if ($expiresAt <= $this->clock->now()->getTimestamp()) {
            return new LiveViewSessionRejected('expired');
        }

        $topics = [];
        foreach ($rawTopics as $rawTopic) {
            $topics[] = new LiveViewTopic($rawTopic);
        }

        return new LiveViewSessionAccepted($subject, TabId::fromToken($tab), $topics);
    }
}
