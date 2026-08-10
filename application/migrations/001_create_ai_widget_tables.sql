CREATE TABLE IF NOT EXISTS ai_widget_sessions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  session_id CHAR(36) NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(80) NOT NULL DEFAULT 'New chat',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  PRIMARY KEY (id), UNIQUE KEY uq_ai_widget_session (session_id),
  KEY idx_ai_widget_sessions_user_activity (user_id, deleted_at, updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ai_widget_conversations (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  session_id CHAR(36) NOT NULL,
  role ENUM('user','assistant','tool') NOT NULL,
  content MEDIUMTEXT NOT NULL,
  tool_used VARCHAR(100) NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id), KEY idx_ai_widget_conversations_session (user_id, session_id, id),
  CONSTRAINT fk_ai_widget_conversations_session FOREIGN KEY (session_id) REFERENCES ai_widget_sessions(session_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ai_widget_usage (
  user_id BIGINT UNSIGNED NOT NULL,
  request_count INT UNSIGNED NOT NULL DEFAULT 0,
  window_start DATETIME NOT NULL,
  PRIMARY KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
