<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Voucher_setting_model extends MY_Model
{
    private $table_name = 'voucher_settings';
    private $table_available = null;

    public function defaults()
    {
        return array(
            'copy_count'  => 1,
            'copy_1_label' => 'Student Copy',
            'copy_2_label' => 'School Copy',
            'copy_3_label' => 'Bank Copy',
            'header_note'  => '',
            'footer_notes' => "1. Challan form is submitted only in this campus\n2. Due's must be submitted before validity date\n3. After expiry of validity date fine will charged",
        );
    }

    /**
     * Keep voucher printing available even when a deployment has not yet run
     * the SQL migration. If CREATE is not permitted, callers receive defaults.
     */
    private function ensureTable()
    {
        if ($this->table_available !== null) {
            return $this->table_available;
        }

        if ($this->db->table_exists($this->table_name)) {
            $this->table_available = true;
            return true;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `voucher_settings` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $db_debug = $this->db->db_debug;
        $this->db->db_debug = false;
        $result = $this->db->query($sql);
        $this->db->db_debug = $db_debug;

        $this->table_available = ($result !== false);
        return $this->table_available;
    }

    public function getResolved($setting_id = 1)
    {
        $defaults = $this->defaults();
        if (!$this->ensureTable()) {
            return $defaults;
        }

        $db_debug = $this->db->db_debug;
        $this->db->db_debug = false;
        $query = $this->db
            ->where('setting_id', (int) $setting_id)
            ->limit(1)
            ->get($this->table_name);
        $this->db->db_debug = $db_debug;

        if ($query === false || $query->num_rows() === 0) {
            return $defaults;
        }

        return $this->normalize(array_merge($defaults, $query->row_array()));
    }

    public function normalize($settings)
    {
        $defaults = $this->defaults();
        $settings = array_merge($defaults, is_array($settings) ? $settings : array());

        $copy_count = (int) $settings['copy_count'];
        if ($copy_count < 1 || $copy_count > 3) {
            $copy_count = 1;
        }

        $normalized = array('copy_count' => $copy_count);
        for ($copy_number = 1; $copy_number <= 3; $copy_number++) {
            $key = 'copy_' . $copy_number . '_label';
            $label = trim((string) $settings[$key]);
            $normalized[$key] = ($label === '') ? $defaults[$key] : $label;
        }

        $normalized['header_note'] = trim((string) $settings['header_note']);
        $normalized['footer_notes'] = trim((string) $settings['footer_notes']);
        return $normalized;
    }

    public function saveSettings($setting_id, $settings, $updated_by = null)
    {
        if (!$this->ensureTable()) {
            return false;
        }

        $data = $this->normalize($settings);
        $data['setting_id'] = (int) $setting_id;
        $data['updated_by'] = ($updated_by === null) ? null : (int) $updated_by;
        $data['updated_at'] = date('Y-m-d H:i:s');

        $existing = $this->db
            ->select('id')
            ->where('setting_id', (int) $setting_id)
            ->limit(1)
            ->get($this->table_name)
            ->row_array();

        $this->db->trans_start();
        if (empty($existing)) {
            $data['created_at'] = $data['updated_at'];
            $this->db->insert($this->table_name, $data);
        } else {
            $this->db->where('id', (int) $existing['id']);
            $this->db->update($this->table_name, $data);
        }
        $this->db->trans_complete();

        return $this->db->trans_status();
    }
}
