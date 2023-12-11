<?php

declare(strict_types=1);

namespace Meteia\Dulce\StackTraces;

use Meteia\ValueObjects\Identity\FilesystemPath;

class FileFragments
{
    public function fileFragment(FilesystemPath $path, int $focusedLine, int $contextLines): FileFragment
    {
        $start = $focusedLine - $contextLines;
        $end = $focusedLine;

        $lines = iterator_to_array($path->lines($start, $end));

        $lines = array_map(
            static fn ($lineNumber, $line) => new Line($line, $lineNumber, $lineNumber === $focusedLine),
            array_keys($lines),
            $lines,
        );

        // $relativePath = str_replace($this->baseDirectory . '/', '', $absolutePath);
        //
        // $editorUri = $this->editorUri->withQuery([
        //    'file' => $editorUriFile,
        //    'line' => $focusedLine,
        // ]);

        return new FileFragment($path, $lines);
    }
}
