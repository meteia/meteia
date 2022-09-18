<?php

declare(strict_types=1);

namespace Meteia\Files\Contracts;

use Meteia\ValueObjects\Identity\Uri;

interface StoredFile
{
    public function uri(): Uri;
}
