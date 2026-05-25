<?php

declare(strict_types=1);

namespace Meteia\Http\Responses;

/**
 * Thin wrapper around Laminas Diactoros redirect response for consistency
 * with other Meteia\Http\Responses\* classes used throughout the app.
 */
class RedirectResponse extends \Laminas\Diactoros\Response\RedirectResponse
{
}
