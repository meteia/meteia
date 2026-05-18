-- Durable delivery ledger for domain events to the message bus.
-- Rows are inserted inside the same transaction as the corresponding
-- domain_events rows (via afterSuccessfulPersist in the UnitOfWork).
--
-- We use the natural (aggregate_root_id, aggregate_sequence) pair as the
-- stable identifier so recording can happen without needing the internal
-- auto-increment id from domain_events at insert time.
CREATE TABLE IF NOT EXISTS `event_publications` (
    `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `aggregate_root_id`  BINARY(20) NOT NULL,
    `aggregate_sequence` BIGINT UNSIGNED NOT NULL,
    `status`             ENUM('pending', 'published', 'failed', 'dead_lettered')
                         NOT NULL DEFAULT 'pending',
    `attempts`           INT UNSIGNED NOT NULL DEFAULT 0,
    `last_attempt_at`    DATETIME(6) NULL,
    `published_at`       DATETIME(6) NULL,
    `last_error`         TEXT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `event_publications_stream_seq` (`aggregate_root_id`, `aggregate_sequence`),
    KEY `status_last_attempt` (`status`, `last_attempt_at`),
    KEY `status` (`status`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
