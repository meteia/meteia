<?php

declare(strict_types=1);

namespace Meteia\Database\CommandLine;

use Doctrine\Inflector\Inflector;
use Meteia\Application\ApplicationPath;
use Meteia\CommandLine\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewMigration implements Command
{
    public function __construct(
        private readonly InputInterface $input,
        private readonly OutputInterface $output,
        private readonly ApplicationPath $applicationPath,
        private readonly Inflector $inflector,
    ) {
    }

    public static function description(): string
    {
        return 'Create a new migration';
    }

    public function execute()
    {
        $id = date('YmdHis');

        $isIdempotent = $this->input->getArgument(NewMigrationInputDefinition::TYPE);
        if (!in_array(strtolower($isIdempotent), ['i', 'idempotent', 'ni', 'non-idempotent'], true)) {
            throw new \Exception('Valid migration type are: i (idempotent) or ni (non-idempotent)');
        }
        $isIdempotent = in_array(strtolower($isIdempotent), ['i', 'idempotent'], true);
        $migrationType = $isIdempotent ? 'i' : 'ni';

        $filename = $this->input->getArgument(NewMigrationInputDefinition::NAME);
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        $filename = $this->inflector->tableize($filename) . '.sql';
        $filename = mb_substr($filename, 0, 63);
        $filename = implode('.', [$id, $migrationType, $filename]);
        $target = $this->applicationPath->join('migrations', $filename);
        $template = <<<SQL
        -- writing your migration in an idempotent fashion is advised (where possible, ALTER for example can't be for MYSQL)
        SQL;
        file_put_contents((string) $target, $template);
        $this->output->writeln('Created Migration: ' . $filename);
    }
}
