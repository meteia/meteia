<?php

declare(strict_types=1);

namespace Meteia\Database\CommandLine;

use DateTimeInterface;
use Doctrine\Inflector\Inflector;
use Meteia\Application\ApplicationNamespace;
use Meteia\Application\ApplicationPath;
use Meteia\CommandLine\Command;
use Meteia\Database\Database;
use Meteia\ValueObjects\Identity\FilesystemPath;
use Symfony\Component\Console\Input\InputDefinition;

class GenerateEntities implements Command
{
    private readonly FilesystemPath $outputPath;


    public function __construct(
        ApplicationPath $applicationPath,
        private readonly ApplicationNamespace $applicationNamespace,
        private readonly Database $database,
        private readonly Inflector $inflector,
    ) {
        $this->outputPath = $applicationPath->join((string) $this->applicationNamespace, 'Database', 'Entities');
    }

    public static function description(): string
    {
        return 'Generate PHP classes for database tables';
    }

    public function execute(): void
    {
        $tables = $this->database->fetchCol('SHOW TABLES');
        foreach ($tables as $table) {
            if (in_array($table, ['migrations'], true)) {
                continue;
            }
            $entityName = $this->inflector->classify($table);
            $entityName = $this->inflector->singularize($entityName);
            $entityName = match ($entityName) {
                'AuthenticationCooky' => 'AuthenticationCookie',
                default => $entityName,
            };
            $columns = $this->database->fetchObjects(sprintf('SHOW COLUMNS FROM `%s`', $table));
            $this->writeClass((string) $this->applicationNamespace, $entityName, $table, $columns);
        }
    }

    public static function inputDefinition(): InputDefinition
    {
        return new InputDefinition();
    }

    private function propertyType(string $tableName, string $columnName, string $mysqlType, object $column): string
    {
        if ($columnName === 'id') {
            $className = $tableName . 'Id';
            $prefix = $this->inflector->tableize($tableName);
            $this->writeUniqueId((string) $this->applicationNamespace, $className, $prefix);

            return $className;
        }
        if (str_ends_with($columnName, '_id')) {
            // $prefix = $this->inflector->tableize($columnName);
            // $this->writeUniqueId((string)$this->applicationNamespace, $columnName, $prefix);
            return $this->inflector->classify($columnName);
        }
        $parts = explode('(', $mysqlType, 2);
        $type = array_shift($parts);
        $definition = array_shift($parts);

        if ($definition) {
            $definition = trim($definition, '()');
        }

        switch ($type) {
            case 'enum':
                $enumName = $tableName . $this->inflector->classify($columnName);
                $this->writeEnum((string) $this->applicationNamespace, $enumName, $definition);

                return $enumName;
            case 'bit':
                return 'bool';
            case 'datetime':
                return '\\' . DateTimeInterface::class;
            case 'char':
            case 'varchar':
            case 'text':
            case 'mediumtext':
            case 'decimal':
            case 'binary':
                return 'string';
            case 'int':
            case 'bigint':
            case 'tinyint':
                return 'int';
            case 'float':
            case 'double':
                return 'float';
            default:
                return 'unk';
        }
    }

    private function writeClass(string $namespace, string $entityName, string $tableName, array $columns): void
    {
        $className = $entityName . 'Entity';
        $file = $this->outputPath->join($className . '.php');
        echo sprintf('%s -> %s', $entityName, $file) . PHP_EOL;
        //if ($file->exists()) {
        //    return;
        //}

        $properties = array_map(function ($column) use ($entityName) {
            $optional = $column->Null === 'YES';

            return (object) [
                'name' => $this->inflector->camelize($column->Field),
                'type' => ($optional ? '?' : '') . $this->propertyType($entityName, $column->Field, $column->Type, $column),
            ];
        }, $columns);

        ob_start();
        include join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'EntityTemplates', 'ClassTemplate.tpl']);
        $content = '<?php' . PHP_EOL . PHP_EOL . ob_get_clean();
        $file->write($content);
    }

    private function writeEnum(string $namespace, string $enumName, string $definition): void
    {
        $file = $this->outputPath->join($enumName . '.php');
        echo sprintf('%s -> %s', $enumName, $file) . PHP_EOL;
        //if ($file->exists()) {
        //    return;
        //}

        $cases = explode(',', $definition);
        $cases = array_map(fn ($s) => trim($s, "'"), $cases);
        $cases = array_map(function ($case) {
            return (object) [
                'name' => $this->inflector->classify($case),
                'value' => $case,
            ];
        }, $cases);

        ob_start();
        include join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'EntityTemplates', 'EnumTemplate.tpl']);
        $content = '<?php' . PHP_EOL . PHP_EOL . ob_get_clean();
        $file->write($content);
    }

    private function writeUniqueId(string $namespace, string $name, string $prefix): void
    {
        $file = $this->outputPath->join($name . '.php');
        echo sprintf('%s -> %s', $name, $file) . PHP_EOL;
        //if ($file->exists()) {
        //    return;
        //}

        ob_start();
        include join(DIRECTORY_SEPARATOR, [__DIR__, '..', 'EntityTemplates', 'UniqueIdTemplate.tpl']);
        $content = '<?php' . PHP_EOL . PHP_EOL . ob_get_clean();
        $file->write($content);
    }
}
