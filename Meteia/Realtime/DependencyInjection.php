<?php

declare(strict_types=1);

use Meteia\Configuration\Configuration;
use Meteia\Realtime\LiveViewExchange;
use Meteia\Realtime\LiveViewSessionLifetime;
use Meteia\Realtime\LiveViewSessionSecretKey;
use Meteia\Realtime\LiveViewSubject;
use Meteia\Realtime\LiveViewSubscriptions;
use Meteia\Realtime\LiveViewUpdates;
use Meteia\Realtime\RabbitMqLiveViewUpdates;

return [
    LiveViewUpdates::class => RabbitMqLiveViewUpdates::class,

    LiveViewExchange::class =>
        static fn(Configuration $configuration): LiveViewExchange => new LiveViewExchange($configuration->string(
            'METEIA_REALTIME_EVENTS_EXCHANGE',
            'live-view-events',
        )),

    LiveViewSessionLifetime::class =>
        static fn(Configuration $configuration): LiveViewSessionLifetime => new LiveViewSessionLifetime(
            $configuration->int('METEIA_LIVE_VIEW_SESSION_LIFETIME_SECONDS', 3_600),
        ),

    LiveViewSessionSecretKey::class => static function (Configuration $configuration): LiveViewSessionSecretKey {
        $value = $configuration->string('METEIA_LIVE_VIEW_SESSION_SECRET_KEY', '');
        if ($value === '') {
            throw new RuntimeException('METEIA_LIVE_VIEW_SESSION_SECRET_KEY not set');
        }

        return LiveViewSessionSecretKey::fromToken($value);
    },

    LiveViewSubscriptions::class => static fn(): LiveViewSubscriptions => new LiveViewSubscriptions(),

    // App should override this with a factory that pulls from its current user
    // abstraction. Empty subject indicates an anonymous session; layouts should
    // not render LiveViewConnection in that case.
    LiveViewSubject::class => static fn(): LiveViewSubject => new LiveViewSubject(''),
];
