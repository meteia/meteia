<?php

declare(strict_types=1);

namespace Meteia\Projections;

use Aura\Sql\ExtendedPdoInterface;
use DateTimeImmutable;
use Meteia\Projections\Contracts\Checkpoint;
use Meteia\Projections\Contracts\CheckpointStore;
use Override;

final readonly class PdoCheckpointStore implements CheckpointStore
{
    public function __construct(
        private ExtendedPdoInterface $db,
    ) {}

    #[Override]
    public function load(ProjectionName $name): Checkpoint
    {
        $row = $this->db->fetchObject('
            SELECT global_sequence
            FROM projection_checkpoints
            WHERE projection_name = :name
        ', ['name' => (string) $name]);

        if (!$row) {
            return new PersistedCheckpoint(GlobalSequence::start());
        }

        return new PersistedCheckpoint(new GlobalSequence((int) $row->global_sequence));
    }

    #[Override]
    public function save(ProjectionName $name, Checkpoint $checkpoint): void
    {
        $this->db->fetchAffected('
            REPLACE INTO projection_checkpoints (projection_name, global_sequence, updated_at)
            VALUES (:name, :position, :updatedAt)
        ', [
            'name' => (string) $name,
            'position' => $checkpoint->position()->asInt(),
            'updatedAt' => new DateTimeImmutable()->format('Y-m-d H:i:s.u'),
        ]);
    }
}
