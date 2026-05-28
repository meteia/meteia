<?php

declare(strict_types=1);

namespace Meteia\Htmx\Realtime;

use Meteia\Html\Component;
use Meteia\Html\Node;
use Meteia\Realtime\LiveViewSessionTokens;
use Meteia\Realtime\LiveViewSubject;
use Meteia\Realtime\LiveViewSubscriptions;
use Meteia\Realtime\TabId;
use Override;
use SensitiveParameter;

use function Meteia\Html\Elements\el;

final readonly class LiveViewConnection implements Component
{
    public function __construct(
        private LiveViewSubject $subject,
        private LiveViewSubscriptions $subscriptions,
        private LiveViewSessionTokens $tokens,
        private StompUsername $username,
        #[SensitiveParameter]
        private StompPassword $password,
        private StompVhost $vhost,
        private StompOpenDestination $openDestination,
        private StompAdjustDestination $adjustDestination,
    ) {}

    #[Override]
    public function render(): Node
    {
        $tab = TabId::random();
        $issued = $this->tokens->issue($this->subject->toNative(), $tab, $this->subscriptions->topics());

        return el('div', [
            'hidden' => true,
            'aria-hidden' => 'true',
            'hx-ext' => 'ws',
            'ws-connect' => '/stomp?' . http_build_query([
                'token' => $issued->token->toNative(),
                'tab' => $tab->token(),
                'open' => $this->openDestination->toNative(),
                'adjust' => $this->adjustDestination->toNative(),
                'stomp_user' => $this->username->toNative(),
                'stomp_passcode' => $this->password->toNative(),
                'stomp_vhost' => $this->vhost->toNative(),
            ]),
        ]);
    }
}
