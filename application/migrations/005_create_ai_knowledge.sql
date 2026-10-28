-- Knowledge base for the School AI assistant (retrieval memory: the app's menu, pages, code, tables and docs,
-- plus facts admins teach it and SQL that answered earlier questions).
-- The application also creates this table automatically on first use; importing this file is optional.

CREATE TABLE IF NOT EXISTS `ai_widget_knowledge` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `source` VARCHAR(20) NOT NULL,
  `topic` VARCHAR(255) NOT NULL,
  `keywords` TEXT NULL,
  `content` MEDIUMTEXT NOT NULL,
  `ref` VARCHAR(255) NULL,
  `created_by` INT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ai_knowledge_source` (`source`),
  KEY `idx_ai_knowledge_topic` (`topic`(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
