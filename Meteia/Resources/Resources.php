<?php

declare(strict_types=1);

namespace Meteia\Resources;

use Meteia\Html\Elements\Link;
use Meteia\Html\Elements\Script;

interface Resources
{
    /**
     * @return iterable<Script>
     */
    public function scriptsFor(EntryTarget $entry): iterable;

    /**
     * @return iterable<Link>
     */
    public function stylesheetsFor(EntryTarget $entry): iterable;

    /**
     * @return iterable<Script>
     */
    public function moduleScripts(string $path): iterable;

    /**
     * @return iterable<Link>
     */
    public function styleLinks(string $path): iterable;
}
