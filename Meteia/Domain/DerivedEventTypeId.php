<?php

declare(strict_types=1);

namespace Meteia\Domain;

use Meteia\EventSourcing\EventTypeId;

trait DerivedEventTypeId
{
    public static function eventTypeId(): EventTypeId
    {
        $class = get_called_class();
        $rand = hash_hmac('sha512', $class, 'D5C4DC5E-9398-4E54-9294-18225886E0A0', true);

        return new EventTypeId(substr($rand, 0, 20));
    }
}
