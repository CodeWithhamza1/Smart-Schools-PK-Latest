<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Coa_mapping_model extends CI_Model {

    private $table = 'coa_fee_type_map';

    public function __construct() {
        parent::__construct();
        $this->ensure_table();
    }

    private function ensure_table() {
        // Create table if it does not exist
        $sql = "CREATE TABLE IF NOT EXISTS `" . $this->db->dbprefix($this->table) . "` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `fee_type_id` INT UNSIGNED NOT NULL,
            `account_id` INT UNSIGNED NOT NULL,
            `created_at` DATETIME NULL,
            `updated_at` DATETIME NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_fee_type` (`fee_type_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $this->db->query($sql);
    }

    public function get_mapping_map() {
        $result = $this->db->get($this->table)->result_array();
        $map = array();
        foreach ($result as $row) {
            $map[$row['fee_type_id']] = (int)$row['account_id'];
        }
        return $map;
    }

    public function save_batch($mapping) {
        $now = date('Y-m-d H:i:s');
        foreach ($mapping as $fee_type_id => $account_id) {
            $fee_type_id = (int)$fee_type_id;
            $account_id = (int)$account_id;
            if ($fee_type_id <= 0) { continue; }
            if ($account_id <= 0) { 
                // Skip empty selections. Could also delete mapping if desired.
                continue;
            }

            $existing = $this->db->get_where($this->table, array('fee_type_id' => $fee_type_id))->row_array();
            if ($existing) {
                $this->db->where('id', $existing['id']);
                $this->db->update($this->table, array(
                    'account_id' => $account_id,
                    'updated_at' => $now,
                ));
            } else {
                $this->db->insert($this->table, array(
                    'fee_type_id' => $fee_type_id,
                    'account_id' => $account_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ));
            }
        }
    }
}


