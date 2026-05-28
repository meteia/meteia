<?php

declare(strict_types=1);

use Meteia\Configuration\Configuration;
use Meteia\Htmx\Realtime\StompAdjustDestination;
use Meteia\Htmx\Realtime\StompOpenDestination;
use Meteia\Htmx\Realtime\StompPassword;
use Meteia\Htmx\Realtime\StompUsername;
use Meteia\Htmx\Realtime\StompVhost;

return [
    StompUsername::class =>
        static fn(Configuration $configuration): StompUsername => new StompUsername($configuration->string(
            'METEIA_REALTIME_STOMP_USERNAME',
            'ws-anon',
        )),
    StompPassword::class =>
        static fn(Configuration $configuration): StompPassword => new StompPassword($configuration->string(
            'METEIA_REALTIME_STOMP_PASSWORD',
            'ws-anon-dev',
        )),
    StompVhost::class =>
        static fn(Configuration $configuration): StompVhost => new StompVhost($configuration->string(
            'METEIA_REALTIME_STOMP_VHOST',
            '/',
        )),
    StompOpenDestination::class =>
        static fn(Configuration $configuration): StompOpenDestination => new StompOpenDestination(
            $configuration->string('METEIA_REALTIME_OPEN_DESTINATION', ''),
        ),
    StompAdjustDestination::class =>
        static fn(Configuration $configuration): StompAdjustDestination => new StompAdjustDestination(
            $configuration->string('METEIA_REALTIME_ADJUST_DESTINATION', ''),
        ),
];
