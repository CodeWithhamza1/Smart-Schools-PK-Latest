-- Unified Generate Fee infrastructure.
-- Additive only: no fee ledger rows are changed or deleted.

CREATE TABLE IF NOT EXISTS `fee_generation_runs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `request_id` VARCHAR(96) NOT NULL,
  `action` VARCHAR(20) NOT NULL,
  `staff_id` INT NOT NULL,
  `session_id` INT NOT NULL,
  `class_id` INT NOT NULL,
  `section_id` INT NOT NULL DEFAULT 0,
  `fee_year` SMALLINT NOT NULL,
  `fee_month` TINYINT NOT NULL,
  `payload_hash` CHAR(64) NOT NULL,
  `status` VARCHAR(20) NOT NULL,
  `result_json` LONGTEXT NULL,
  `error_summary` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_fee_generation_request` (`request_id`),
  KEY `idx_fee_generation_scope` (`session_id`, `class_id`, `section_id`, `fee_year`, `fee_month`),
  KEY `idx_fee_generation_status` (`status`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Guard each performance index so the script can safely be run once on
-- installations whose legacy schemas may already contain an equivalent name.
SET @fee_idx_sql = IF(
  (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'student_session' AND index_name = 'idx_ss_fee_scope') = 0,
  'ALTER TABLE `student_session` ADD INDEX `idx_ss_fee_scope` (`session_id`,`class_id`,`section_id`,`student_id`)',
  'SELECT 1'
);
PREPARE fee_idx_stmt FROM @fee_idx_sql; EXECUTE fee_idx_stmt; DEALLOCATE PREPARE fee_idx_stmt;

SET @fee_idx_sql = IF(
  (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'fee_groups' AND index_name = 'idx_fg_student_system') = 0,
  'ALTER TABLE `fee_groups` ADD INDEX `idx_fg_student_system` (`student_id`,`is_system`)',
  'SELECT 1'
);
PREPARE fee_idx_stmt FROM @fee_idx_sql; EXECUTE fee_idx_stmt; DEALLOCATE PREPARE fee_idx_stmt;

SET @fee_idx_sql = IF(
  (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'fee_session_groups' AND index_name = 'idx_fsg_session_group') = 0,
  'ALTER TABLE `fee_session_groups` ADD INDEX `idx_fsg_session_group` (`session_id`,`fee_groups_id`)',
  'SELECT 1'
);
PREPARE fee_idx_stmt FROM @fee_idx_sql; EXECUTE fee_idx_stmt; DEALLOCATE PREPARE fee_idx_stmt;

SET @fee_idx_sql = IF(
  (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'fee_groups_feetype' AND index_name = 'idx_fgft_generation_lookup') = 0,
  'ALTER TABLE `fee_groups_feetype` ADD INDEX `idx_fgft_generation_lookup` (`fee_session_group_id`,`fee_groups_id`,`feetype_id`,`session_id`,`year`,`clnd_month`)',
  'SELECT 1'
);
PREPARE fee_idx_stmt FROM @fee_idx_sql; EXECUTE fee_idx_stmt; DEALLOCATE PREPARE fee_idx_stmt;

SET @fee_idx_sql = IF(
  (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'student_fees_master' AND index_name = 'idx_sfm_student_group') = 0,
  'ALTER TABLE `student_fees_master` ADD INDEX `idx_sfm_student_group` (`student_session_id`,`fee_session_group_id`)',
  'SELECT 1'
);
PREPARE fee_idx_stmt FROM @fee_idx_sql; EXECUTE fee_idx_stmt; DEALLOCATE PREPARE fee_idx_stmt;

SET @fee_idx_sql = IF(
  (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'student_fees_deposite' AND index_name = 'idx_sfd_fee_lookup') = 0,
  'ALTER TABLE `student_fees_deposite` ADD INDEX `idx_sfd_fee_lookup` (`student_fees_master_id`,`fee_groups_feetype_id`)',
  'SELECT 1'
);
PREPARE fee_idx_stmt FROM @fee_idx_sql; EXECUTE fee_idx_stmt; DEALLOCATE PREPARE fee_idx_stmt;

SET @fee_idx_sql = IF(
  (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'student_fees_discounts' AND index_name = 'idx_sfd_student_discount_status') = 0,
  'ALTER TABLE `student_fees_discounts` ADD INDEX `idx_sfd_student_discount_status` (`student_session_id`,`fees_discount_id`,`status`)',
  'SELECT 1'
);
PREPARE fee_idx_stmt FROM @fee_idx_sql; EXECUTE fee_idx_stmt; DEALLOCATE PREPARE fee_idx_stmt;

