<?php

declare(strict_types=1);

use Meteia\Html\Elements\Title;
use Meteia\Html\Footer;
use Meteia\Html\Header;
use Meteia\Html\Layout;
use Meteia\Html\Placeholders\PlaceholderFooter;
use Meteia\Html\Placeholders\PlaceholderHeader;
use Meteia\Html\Placeholders\PlaceholderLayout;

return [
    Header::class => PlaceholderHeader::class,
    Footer::class => PlaceholderFooter::class,
    Layout::class => PlaceholderLayout::class,
    Title::class => function () {
        return new Title('Untitled');
    },
];
