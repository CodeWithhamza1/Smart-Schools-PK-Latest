-- Read-only pre-deployment audit for legacy duplicate fee relationships.
-- Review every returned row; this script intentionally performs no cleanup.

SELECT `student_id`, COUNT(*) AS duplicate_count, GROUP_CONCAT(`id` ORDER BY `id`) AS row_ids
FROM `fee_groups`
WHERE `is_system` = 0 AND `student_id` IS NOT NULL
GROUP BY `student_id`
HAVING COUNT(*) > 1;

SELECT `session_id`, `fee_groups_id`, COUNT(*) AS duplicate_count,
       GROUP_CONCAT(`id` ORDER BY `id`) AS row_ids
FROM `fee_session_groups`
GROUP BY `session_id`, `fee_groups_id`
HAVING COUNT(*) > 1;

SELECT `fee_session_group_id`, `fee_groups_id`, `feetype_id`, `session_id`, `year`, `clnd_month`,
       COUNT(*) AS duplicate_count, GROUP_CONCAT(`id` ORDER BY `id`) AS row_ids
FROM `fee_groups_feetype`
GROUP BY `fee_session_group_id`, `fee_groups_id`, `feetype_id`, `session_id`, `year`, `clnd_month`
HAVING COUNT(*) > 1;

SELECT `student_session_id`, `fee_session_group_id`, COUNT(*) AS duplicate_count,
       GROUP_CONCAT(`id` ORDER BY `id`) AS row_ids
FROM `student_fees_master`
GROUP BY `student_session_id`, `fee_session_group_id`
HAVING COUNT(*) > 1;

SELECT `student_fees_master_id`, `fee_groups_feetype_id`, COUNT(*) AS duplicate_count,
       GROUP_CONCAT(`id` ORDER BY `id`) AS row_ids
FROM `student_fees_deposite`
GROUP BY `student_fees_master_id`, `fee_groups_feetype_id`
HAVING COUNT(*) > 1;
