declare(strict_types=1);

namespace <?= $namespace ?>\Entities;

use Meteia\ValueObjects\Identity\UniqueId;

class <?= $name ?> extends UniqueId
{
    public static function prefix(): string
    {
      return '<?= $prefix ?>';
    }
}
