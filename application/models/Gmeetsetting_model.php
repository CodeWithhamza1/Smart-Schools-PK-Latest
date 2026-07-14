<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Gmeetsetting_model extends MY_Model
{

    public function __construct()
    {
        parent::__construct();
    }

    public function get()
    {
        // Check if table exists first
        if (!$this->db->table_exists('gmeet_settings')) {
            return null;
        }
        $this->db->select('*');
        $this->db->from('gmeet_settings');
        $this->db->order_by('gmeet_settings.id');
        $query = $this->db->get();
        return $query->row();
    }

    public function add($data)
    {

        $this->db->trans_begin();

        $q = $this->db->get('gmeet_settings');

        if ($q->num_rows() > 0) {
            $results = $q->row();
            $this->db->where('id', $results->id);
            $this->db->update('gmeet_settings', $data);
        } else {

            $this->db->insert('gmeet_settings', $data);
        }

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
        } else {
            $this->db->trans_commit();
        }
    }

}
