DROP TABLE IF EXISTS `projection_checkpoints`;
CREATE TABLE IF NOT EXISTS `projection_checkpoints`
(
  `projection_name` VARBINARY(255)  NOT NULL,
  `global_sequence` BIGINT UNSIGNED NOT NULL,
  `updated_at`      DATETIME(6)     NOT NULL,
  PRIMARY KEY (`projection_name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
