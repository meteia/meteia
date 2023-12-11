<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\FixerInterface;
use PhpCsFixer\Tokenizer\Tokens;

/**
 * Fixer for using prettier-php to fix.
 */
final class PrettierPHPFixer implements FixerInterface
{
    #[\Override]
    public function getPriority(): int
    {
        // Allow prettier to pre-process the code before php-cs-fixer
        return 999;
    }

    #[\Override]
    public function isCandidate(Tokens $tokens): bool
    {
        return true;
    }

    #[\Override]
    public function isRisky(): bool
    {
        return false;
    }

    #[\Override]
    public function fix(SplFileInfo $file, Tokens $tokens): void
    {
        if ($tokens->count() > 0 && $this->isCandidate($tokens) && $this->supports($file)) {
            $this->applyFix($file, $tokens);
        }
    }

    #[\Override]
    public function getName(): string
    {
        return 'Prettier/php';
    }

    #[\Override]
    public function supports(SplFileInfo $file): bool
    {
        return true;
    }

    #[\Override]
    public function getDefinition(): PhpCsFixer\FixerDefinition\FixerDefinitionInterface
    {
        return new \PhpCsFixer\FixerDefinition\FixerDefinition('Format PHP files with prettier', [], null, null);
    }

    private function applyFix(SplFileInfo $file, Tokens $tokens): void
    {
        $executable = implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'node_modules', '.bin', 'prettier']);
        $cmd = implode(' ', [$executable, $file]);
        exec($cmd, $prettierOutput, $resultCode);
        if ($resultCode !== 0) {
            throw new \Exception('Prettier failed to run on ' . $file . PHP_EOL . implode(PHP_EOL, $prettierOutput));
        }
        $code = implode(PHP_EOL, $prettierOutput);
        $tokens->setCode($code);
    }
}
