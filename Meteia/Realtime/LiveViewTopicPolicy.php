<?php

declare(strict_types=1);

namespace Meteia\Realtime;

final class LiveViewTopicPolicy
{
    public function allows(LiveViewSessionAccepted $session, LiveViewTopic $topic): bool
    {
        $value = $topic->toNative();
        $parts = explode('.', $value);

        foreach ($parts as $part) {
            if ($part === '' || strlen($part) > 64) {
                return false;
            }
        }

        if ($parts[0] === 'user') {
            if (\count($parts) !== 2) {
                return false;
            }

            // Only the session's own user topic is allowed.
            return hash_equals($session->subject, $parts[1] ?? '');
        }

        // {context}.{aggregate}.{id|created} — globally readable by any authenticated session.
        return \count($parts) === 3;
    }
}
