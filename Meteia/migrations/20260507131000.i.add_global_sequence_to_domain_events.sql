-- Historical migration. The `id` column it once added is now part of the base
-- `domain_events` creation migration (20260507100000). Kept as a no-op so
-- environments that ran the original migration retain a contiguous history.
SELECT 1;
