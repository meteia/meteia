<?php

declare(strict_types=1);

namespace Meteia\Realtime;

use Meteia\ValueObjects\Primitive\StringLiteral;

/**
 * Identifies whom the current live view session belongs to. The app's DI
 * provides this per-request, typically from the authenticated user's id. The
 * value is embedded in the LiveViewSessionToken as the `sub` claim and used
 * by LiveViewTopicPolicy to gate `user.*` topics.
 *
 * An empty subject indicates an anonymous session; the layout should not
 * render the connection component in that case.
 */
final class LiveViewSubject extends StringLiteral {}
