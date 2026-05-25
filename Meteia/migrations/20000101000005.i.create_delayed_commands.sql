DROP TABLE IF EXISTS `delayed_commands`;
CREATE TABLE IF NOT EXISTS `delayed_commands`
(
  `command_id`     BINARY(20)     NOT NULL,
  `command_type`   VARBINARY(255) NOT NULL,
  `command`        JSON           NOT NULL,
  `causation_id`   BINARY(20)     NOT NULL,
  `correlation_id` BINARY(20)     NOT NULL,
  `process_id`     BINARY(20)     NOT NULL,
  `defer_until`    DATETIME(6)    NOT NULL,
  `claimed_at`     DATETIME(6)    NULL,
  `claim_id`       CHAR(32)       NULL,
  `published_at`   DATETIME(6)    NULL,
  `failed_at`      DATETIME(6)    NULL,
  `failure`        VARCHAR(1024)  NULL,
  PRIMARY KEY (`command_id`),
  KEY `due_delayed_commands` (`published_at`, `defer_until`, `claimed_at`),
  KEY `claim_id` (`claim_id`),
  KEY `command_type` (`command_type`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
