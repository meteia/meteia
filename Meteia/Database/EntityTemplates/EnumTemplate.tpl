declare(strict_types=1);

namespace <?= $namespace ?>\Entities;

enum <?= $enumName ?>: string
{
    <?php foreach ($cases as $case): ?>case <?= $case->name ?> = '<?= $case->value ?>';
    <?php endforeach; ?>

}
