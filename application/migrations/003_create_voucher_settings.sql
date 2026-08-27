-- Client-managed settings for the standard single and bulk fee vouchers.
-- Additive and idempotent: existing fee and voucher data is not changed.

CREATE TABLE IF NOT EXISTS `voucher_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_id` INT NOT NULL,
  `copy_count` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `copy_1_label` VARCHAR(50) NOT NULL DEFAULT 'Student Copy',
  `copy_2_label` VARCHAR(50) NOT NULL DEFAULT 'School Copy',
  `copy_3_label` VARCHAR(50) NOT NULL DEFAULT 'Bank Copy',
  `header_note` TEXT NULL,
  `footer_notes` TEXT NULL,
  `updated_by` INT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_voucher_settings_school` (`setting_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
