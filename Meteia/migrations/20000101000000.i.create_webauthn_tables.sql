DROP TABLE IF EXISTS `webauthn_credentials`;
DROP TABLE IF EXISTS `webauthn_users`;

CREATE TABLE IF NOT EXISTS `webauthn_users`
(
  `user_handle`  VARBINARY(64) NOT NULL,
  `username`     VARCHAR(255)  NOT NULL,
  `display_name` VARCHAR(255)  NOT NULL,
  `created_at`   DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_handle`),
  UNIQUE KEY `username` (`username`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `webauthn_credentials`
(
  `public_key_credential_id` VARBINARY(255)  NOT NULL,
  `user_handle`              VARBINARY(64)   NOT NULL,
  `counter`                  BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `data`                     LONGTEXT        NOT NULL,
  `created_at`               DATETIME                 DEFAULT CURRENT_TIMESTAMP,
  `updated_at`               DATETIME                 DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`public_key_credential_id`),
  KEY `user_handle` (`user_handle`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4;
