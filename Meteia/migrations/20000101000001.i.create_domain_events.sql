DROP TABLE IF EXISTS `domain_event_snapshots`;
DROP TABLE IF EXISTS `domain_events`;

CREATE TABLE IF NOT EXISTS `domain_events`
(
  `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `aggregate_root_id`  BINARY(20)      NOT NULL,
  `aggregate_sequence` BIGINT UNSIGNED NOT NULL,
  `event_type_id`      BINARY(20)      NOT NULL,
  `event`              JSON            NOT NULL,
  `causation_id`       BINARY(20)      NOT NULL,
  `correlation_id`     BINARY(20)      NOT NULL,
  `created`            DATETIME(6)     NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain_events_stream_sequence` (`aggregate_root_id`, `aggregate_sequence`),
  KEY `domain_events_event_type_id` (`event_type_id`),
  KEY `domain_events_correlation_id` (`correlation_id`),
  KEY `domain_events_causation_id` (`causation_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `domain_event_snapshots`
(
  `aggregate_root_id`  BINARY(20)      NOT NULL,
  `aggregate_sequence` BIGINT UNSIGNED NOT NULL,
  `aggregate_hash`     BINARY(16)      NOT NULL,
  `snapshot`           LONGTEXT        NOT NULL,
  `created`            DATETIME(6)     NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
  UNIQUE KEY `domain_event_snapshots_stream` (`aggregate_root_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
