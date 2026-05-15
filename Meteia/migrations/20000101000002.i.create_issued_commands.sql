CREATE TABLE IF NOT EXISTS `issued_commands` (
    `command_id`        BINARY(20)     NOT NULL,
    `aggregate_root_id` BINARY(20)     NOT NULL,
    `command_type`      VARBINARY(255) NOT NULL,
    `command`           JSON           NOT NULL,
    `causation_id`      BINARY(20)     NOT NULL,
    `correlation_id`    BINARY(20)     NOT NULL,
    `issued_at`         DATETIME(6)    NOT NULL,
    `defer_until`       DATETIME(6)    NOT NULL,
    PRIMARY KEY (`command_id`),
    KEY `correlation_id` (`correlation_id`),
    KEY `causation_id` (`causation_id`),
    KEY `defer_until` (`defer_until`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
