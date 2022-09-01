<?php

declare(strict_types=1);

namespace Meteia\Database\CommandLine;

use Meteia\Application\ApplicationPath;
use Meteia\CommandLine\Command;
use Meteia\Database\Database;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Migrate implements Command
{
    public function __construct(
        private readonly InputInterface $input,
        private readonly OutputInterface $output,
        private readonly ApplicationPath $applicationPath,
        private readonly Database $db,
    ) {
    }

    public static function description(): string
    {
        return 'Apply any pending migrations';
    }

    public function execute()
    {
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS migrations (
                id        DATETIME NOT NULL,
                name      VARCHAR(63) NOT NULL,
                performed DATETIME DEFAULT CURRENT_TIMESTAMP NULL,
                UNIQUE INDEX id(id)
            )
        ');
        if ($this->input->getOption(MigrateInputDefinition::RESET)) {
            $this->output->writeln('truncated migrations');
            $this->db->exec('TRUNCATE migrations;');
        }

        $migrationIds = $this->db->fetchCol("SELECT DATE_FORMAT(id, '%Y%m%d%H%i%s') FROM migrations ORDER BY id;");

        $files = (string) $this->applicationPath->join('migrations', '*.sql');
        /** @var \SplFileInfo $file */
        foreach (new \GlobIterator($files) as $file) {
            $filename = $file->getBasename('.sql');
            [$id, $type, $name] = explode('.', $filename, 3);
            if (in_array($id, $migrationIds, true)) {
                $this->output->writeln(sprintf('existing : %s', $filename));
                continue;
            }
            $realPath = $file->getRealPath();
            if (!$realPath) {
                continue;
            }
            $sql = file_get_contents($realPath);

            try {
                $this->output->writeln(sprintf('applying : %s', $filename));
                $this->db->exec($sql);
                $this->output->writeln(sprintf('applied  : %s', $filename));
            } catch (\PDOException $t) {
                if ($type === 'ni') {
                    $this->output->writeln(sprintf('ignoring error during non-idempotent migration %s', $filename));
                    $this->output->writeln(sprintf("\t%s", $t->getMessage()));
                } else {
                    throw $t;
                }
            }

            if (!$this->input->getOption(MigrateInputDefinition::DEV)) {
                $this->db->perform('INSERT INTO migrations (id, name) VALUES (:id, :name)', [
                    'id' => $id,
                    'name' => $name,
                ]);
                $this->output->writeln(sprintf('recorded : %s', $filename));
            }
        }
    }
}
