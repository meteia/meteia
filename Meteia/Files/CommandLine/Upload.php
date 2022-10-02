<?php

declare(strict_types=1);

namespace Meteia\Files\CommandLine;

use Meteia\CommandLine\Command;
use Meteia\Files\ContentAddressableStorage;
use Meteia\ValueObjects\Identity\FilesystemPath;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;

class Upload implements Command
{
    private const ARG_FILES = 'files';

    public function __construct(
        private readonly ContentAddressableStorage $contentAddressableStorage,
        private readonly InputInterface $input,
    ) {
    }

    public static function description(): string
    {
        return 'Upload one or more files to the configured storage';
    }

    public function execute(): void
    {
        $files = $this->input->getArgument(self::ARG_FILES);
        foreach ($files as $file) {
            $file = new FilesystemPath($file);
            $storedFile = $this->contentAddressableStorage->store($file->open(), $file->extension());
            echo sprintf('%s => %s', $file, $storedFile) . PHP_EOL;
        }
    }

    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition([
            new InputArgument(self::ARG_FILES, InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'file(s) to upload'),
        ]);
    }
}
