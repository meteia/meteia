<?php

declare(strict_types=1);

namespace Meteia\ErrorHandling;

class HighlightSnippet
{
    public function highlightStacktrace($stackTrace)
    {
        foreach ($stackTrace as $index => $frame) {
            if (isset($frame['file'], $frame['line'])) {
                $stackTrace[$index]['highlightedSource'] = $this->highlightSnippet($frame['file'], $frame['line'], 11);
            }
        }

        return $stackTrace;
    }

    public function highlightSnippet($filename, int $lineNumber, int $showLines)
    {
        $lines = file_get_contents($filename);
        $lines = explode("\n", $lines);

        $offset = (int) max(0, $lineNumber - ceil($showLines / 2));

        $lines = \array_slice($lines, $offset, $showLines);

        $output = [];
        foreach ($lines as $line) {
            ++$offset;
            $line = rtrim($line);
            $line = htmlentities($line, ENT_QUOTES | ENT_HTML5);

            $output[] = [
                'href' => 'idea://open?' . http_build_query(['file' => $filename, 'line' => $offset]),
                'number' => $offset,
                'activeLine' => $offset === $lineNumber,
                'source' => $line,
            ];
        }

        return $output;
    }
}
