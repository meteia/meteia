<?php

declare(strict_types=1);

namespace Meteia\Dulce\StackTraces;

use Meteia\ValueObjects\Identity\FilesystemPath;

class FileFragments
{
    public function fileFragment(FilesystemPath $path, int $focusedLine, int $contextLines): FileFragment
    {
        $start = (int) max(0, $focusedLine - ceil($contextLines / 2));
        $end = $start + $contextLines;

        $lines = iterator_to_array($path->lines($start, $end));

        $lines = array_map(function ($lineNumber, $line) use ($focusedLine) {
            return new Line($line, $lineNumber, $lineNumber === $focusedLine);
        }, array_keys($lines), $lines);

        // $relativePath = str_replace($this->baseDirectory . '/', '', $absolutePath);
        //
        // $editorUri = $this->editorUri->withQuery([
        //    'file' => $editorUriFile,
        //    'line' => $focusedLine,
        // ]);

        return new FileFragment($path, $lines);
    }
}
