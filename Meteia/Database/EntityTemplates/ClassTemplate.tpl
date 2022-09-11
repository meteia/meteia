declare(strict_types=1);

namespace <?= $namespace ?>\Entities;

use Meteia\Database\Database;
use Meteia\Database\DatabaseEntity;

class <?= $entityName . PHP_EOL ?>
{
    use DatabaseEntity;

    public function __construct(
        <?php foreach ($properties as $property): ?>public readonly <?= $property->type ?> $<?= $property->name ?>,
        <?php endforeach; ?>
    )
    { }

    public function insertInto(Database $database): void {
      $this->performInsert($database, '<?= $tableName ?>');
    }
}
