-- Failed-login counters used to rate-limit the staff (site/login) and student/parent (site/userlogin) forms.
-- Keys are SHA-256 hashes of form + username + IP, so no usernames or IPs are stored in plain text.
-- The application also creates this table automatically on first use; importing this file is optional.

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `attempt_key` CHAR(64) NOT NULL,
  `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
  `window_start` INT UNSIGNED NOT NULL,
  `locked_until` INT UNSIGNED NOT NULL DEFAULT 0,
  `updated_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`attempt_key`),
  KEY `idx_login_attempts_updated` (`updated_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
