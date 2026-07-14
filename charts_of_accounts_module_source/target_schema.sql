-- Charts of Accounts module schema for the receiving application.
-- Structure-only and idempotent: no table is dropped and no data is inserted.
-- The seven available table definitions follow the existing school-system schema;
-- account_opening_balances and reconciliation fields follow the active model contract.

CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_number` varchar(255) DEFAULT NULL,
  `account_name` varchar(255) DEFAULT NULL,
  `account_type` varchar(255) DEFAULT NULL,
  `account_group` varchar(255) DEFAULT NULL,
  `account_sub_group` varchar(255) DEFAULT NULL,
  `show_in_fee_collection` tinyint(1) DEFAULT 0 COMMENT 'Show this account in fee collection dropdown',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `voucher_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `draft_voucher_entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `voucher_date` date DEFAULT NULL,
  `voucher_number` varchar(255) DEFAULT NULL,
  `voucher_note` varchar(255) DEFAULT NULL,
  `voucher_entry` longtext DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `draft_payable_entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `payable_date` date NOT NULL,
  `payable_number` varchar(50) NOT NULL,
  `payable_narration` text DEFAULT NULL,
  `payable_entry` text DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `journal_entry` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `voucher_date` date DEFAULT NULL,
  `voucher_type` varchar(255) DEFAULT NULL,
  `voucher_number` varchar(255) DEFAULT NULL,
  `voucher_narration` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `journal_entry_detail` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `journal_entry_id` int(11) NOT NULL,
  `voucher_date` date DEFAULT NULL,
  `voucher_type` varchar(255) DEFAULT NULL,
  `debit_account_id` int(11) DEFAULT NULL,
  `debit_account_name` varchar(255) DEFAULT NULL,
  `debit_account_number` varchar(255) DEFAULT NULL,
  `debit_amount` decimal(10,2) DEFAULT NULL,
  `credit_account_id` int(11) DEFAULT NULL,
  `credit_account_name` varchar(255) DEFAULT NULL,
  `credit_account_number` varchar(255) DEFAULT NULL,
  `credit_amount` decimal(10,2) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `fee_unique_id` varchar(100) DEFAULT NULL,
  `reconciled` tinyint(1) NOT NULL DEFAULT 0,
  `reconciled_bank_id` int(11) DEFAULT NULL,
  `reconciled_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `journal_entry_id` (`journal_entry_id`),
  CONSTRAINT `journal_entry_detail_ibfk_1` FOREIGN KEY (`journal_entry_id`) REFERENCES `journal_entry` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE IF NOT EXISTS `account_opening_balances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `debit_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `credit_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `opening_date` date NOT NULL,
  `financial_year` varchar(30) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_account_year` (`account_id`, `financial_year`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `coa_fee_type_map` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fee_type_id` int(11) NOT NULL COMMENT 'Foreign key to feetype table',
  `account_id` int(11) NOT NULL COMMENT 'Foreign key to accounts table',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_fee_type` (`fee_type_id`),
  KEY `account_id` (`account_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Mapping between fee types and Chart of Accounts';
