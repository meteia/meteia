declare(strict_types=1);

namespace <?= $namespace ?>\Database\Entities;

use Meteia\Database\Database;
use Meteia\Database\DatabaseEntity;

class <?= $className . PHP_EOL ?>
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
