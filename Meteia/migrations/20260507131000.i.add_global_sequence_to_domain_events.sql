-- Adds a globally-ordered auto-increment id so projections can stream events in commit order.
-- Existing tables created without this column should be altered before projections begin running.
ALTER TABLE `domain_events`
    ADD COLUMN `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    ADD PRIMARY KEY (`id`);
