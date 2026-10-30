<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Rate limiting for the staff and student/parent login forms.
 *
 * Failed logins are counted in two buckets per form:
 *  - username + IP: a small limit that stops password guessing against one account from one client;
 *  - IP only:       a larger limit that stops one client spraying many usernames.
 * When either bucket locks, the client's IP is locked out of that form for every username until it expires.
 * There is deliberately no username-only bucket, so nobody can lock a real user out of their account
 * just by typing wrong passwords for it from somewhere else.
 *
 * Counters live in the database (not the session) so a client cannot reset them by dropping cookies,
 * and each failure is a single atomic upsert so parallel requests cannot slip past the limit.
 */
class Login_attempt_model extends CI_Model
{
    const TABLE = 'login_attempts';

    const USER_MAX_ATTEMPTS = 5;
    const IP_MAX_ATTEMPTS   = 30;
    const WINDOW_SECONDS    = 900;  // failures are counted over 15 minutes
    const LOCK_SECONDS      = 900;  // lockout length once a limit is reached (keep >= WINDOW_SECONDS)
    const PURGE_AFTER       = 86400;

    /* ------------------------------------------------------------------ */
    /* storage                                                             */
    /* ------------------------------------------------------------------ */

    public function ensure_table()
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        if ($this->db->table_exists(self::TABLE)) return;
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . self::TABLE . "` (
            `attempt_key` CHAR(64) NOT NULL,
            `attempts` INT UNSIGNED NOT NULL DEFAULT 0,
            `window_start` INT UNSIGNED NOT NULL,
            `locked_until` INT UNSIGNED NOT NULL DEFAULT 0,
            `updated_at` INT UNSIGNED NOT NULL,
            PRIMARY KEY (`attempt_key`),
            KEY `idx_login_attempts_updated` (`updated_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    }

    /* ------------------------------------------------------------------ */
    /* public api                                                          */
    /* ------------------------------------------------------------------ */

    /**
     * Seconds the caller must wait before trying again, or 0 when the login may proceed.
     * Call this before checking the password so a locked-out client learns nothing about it.
     */
    public function seconds_locked($form, $username)
    {
        $this->ensure_table();
        $keys = array_values($this->keys($form, $username));
        $row  = $this->db->select_max('locked_until')->where_in('attempt_key', $keys)->get(self::TABLE)->row();
        $wait = $row ? (int) $row->locked_until - time() : 0;
        return $wait > 0 ? $wait : 0;
    }

    /**
     * Seconds this client (IP) is locked out of the form for every username, or 0. Checked before anything
     * else on the login page so a locked client gets a disabled form, and re-enabling it in the browser's
     * dev tools or changing the username does not help: the server refuses the post either way.
     */
    public function client_seconds_locked($form)
    {
        $this->ensure_table();
        $keys = $this->keys($form, '');
        $row  = $this->db->select('locked_until')->where('attempt_key', $keys['ip'])->get(self::TABLE)->row();
        $wait = $row ? (int) $row->locked_until - time() : 0;
        return $wait > 0 ? $wait : 0;
    }

    public function record_failure($form, $username)
    {
        $this->ensure_table();
        $keys = $this->keys($form, $username);
        $this->bump($keys['user'], self::USER_MAX_ATTEMPTS);
        $this->bump($keys['ip'], self::IP_MAX_ATTEMPTS);
        // Once a username is locked for this client, lock the whole client too, so it cannot carry on
        // with another username.
        $user_row = $this->db->select('locked_until')->where('attempt_key', $keys['user'])->get(self::TABLE)->row();
        if ($user_row && (int) $user_row->locked_until > time()) {
            $this->db->where('attempt_key', $keys['ip'])->where('locked_until <', (int) $user_row->locked_until)
                ->update(self::TABLE, array('locked_until' => (int) $user_row->locked_until));
        }
        if (mt_rand(1, 50) === 1) {
            $this->db->where('updated_at <', time() - self::PURGE_AFTER)->where('locked_until <', time())->delete(self::TABLE);
        }
    }

    /**
     * Only the username + IP bucket is cleared: clearing the IP bucket too would let someone reset their
     * counter by logging into an account of their own between guesses.
     */
    public function record_success($form, $username)
    {
        $this->ensure_table();
        $keys = $this->keys($form, $username);
        $this->db->where('attempt_key', $keys['user'])->delete(self::TABLE);
    }

    /* ------------------------------------------------------------------ */
    /* helpers                                                             */
    /* ------------------------------------------------------------------ */

    private function keys($form, $username)
    {
        $ip       = (string) $this->input->ip_address();
        $username = strtolower(trim((string) $username));
        return array(
            'user' => hash('sha256', $form . '|user|' . $username . '|' . $ip),
            'ip'   => hash('sha256', $form . '|ip|' . $ip),
        );
    }

    // MySQL applies ON DUPLICATE KEY assignments left to right, so `attempts` is updated first, `window_start`
    // still sees the old value when it is evaluated, and `locked_until` sees the new attempt count.
    private function bump($key, $max_attempts)
    {
        $now          = time();
        $window_floor = $now - self::WINDOW_SECONDS;
        $lock_until   = $now + self::LOCK_SECONDS;
        $this->db->query("INSERT INTO `" . self::TABLE . "` (`attempt_key`, `attempts`, `window_start`, `locked_until`, `updated_at`)
            VALUES (?, 1, ?, 0, ?)
            ON DUPLICATE KEY UPDATE
                `attempts`     = IF(`window_start` < ?, 1, `attempts` + 1),
                `window_start` = IF(`window_start` < ?, ?, `window_start`),
                `locked_until` = IF(`attempts` >= ?, ?, `locked_until`),
                `updated_at`   = ?",
            array($key, $now, $now, $window_floor, $window_floor, $now, $max_attempts, $lock_until, $now));
    }
}
