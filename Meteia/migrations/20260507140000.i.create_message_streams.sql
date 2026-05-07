CREATE TABLE IF NOT EXISTS `message_streams` (
    `message_stream_id`       BINARY(20)      NOT NULL,
    `message_stream_sequence` BIGINT UNSIGNED NOT NULL,
    `message_type_id`         BINARY(20)      NOT NULL,
    `message`                 MEDIUMTEXT      NOT NULL,
    `causation_id`            BINARY(20)      NOT NULL,
    `correlation_id`          BINARY(20)      NOT NULL,
    `occurred_at`             DATETIME(6)     NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    UNIQUE KEY `message_stream_id_sequence` (`message_stream_id`, `message_stream_sequence`),
    KEY `message_type_id` (`message_type_id`),
    KEY `correlation_id` (`correlation_id`),
    KEY `causation_id` (`causation_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
