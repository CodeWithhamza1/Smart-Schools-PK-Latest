-- Prevent duplicate generated fee rows without changing any existing ledger data.
-- If legacy duplicates exist, the unique key is intentionally not added; review
-- them first from Change Fee. The application-level transaction lock still skips
-- duplicates during all new Generate Fee operations.

SET @fee_payload_column_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
    AND table_name = 'fee_generation_runs'
    AND column_name = 'payload_json'
);
SET @fee_payload_sql = IF(
  @fee_payload_column_exists = 0,
  'ALTER TABLE `fee_generation_runs` ADD COLUMN `payload_json` LONGTEXT NULL AFTER `payload_hash`',
  'SELECT 1'
);
PREPARE fee_payload_stmt FROM @fee_payload_sql;
EXECUTE fee_payload_stmt;
DEALLOCATE PREPARE fee_payload_stmt;

SET @fee_duplicate_count = (
  SELECT COUNT(*) FROM (
    SELECT 1
    FROM `fee_groups_feetype`
    GROUP BY `fee_session_group_id`, `feetype_id`, `session_id`, `year`, `clnd_month`
    HAVING COUNT(*) > 1
  ) duplicate_groups
);

SET @fee_unique_exists = (
  SELECT COUNT(*)
  FROM information_schema.statistics
  WHERE table_schema = DATABASE()
    AND table_name = 'fee_groups_feetype'
    AND index_name = 'uniq_generated_student_fee'
);

SET @fee_unique_sql = IF(
  @fee_duplicate_count = 0 AND @fee_unique_exists = 0,
  'ALTER TABLE `fee_groups_feetype` ADD UNIQUE KEY `uniq_generated_student_fee` (`fee_session_group_id`,`feetype_id`,`session_id`,`year`,`clnd_month`)',
  'SELECT @fee_duplicate_count AS legacy_duplicate_groups'
);

PREPARE fee_unique_stmt FROM @fee_unique_sql;
EXECUTE fee_unique_stmt;
DEALLOCATE PREPARE fee_unique_stmt;
