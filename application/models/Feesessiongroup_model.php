<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Feesessiongroup_model extends MY_Model {

    public function __construct() {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function add($data) {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $parentid = $this->group_exists($data['fee_groups_id']);
        $data['fee_session_group_id'] = $parentid;
        $this->db->insert('fee_groups_feetype', $data);
        $id = $this->db->insert_id();
        $message = INSERT_RECORD_CONSTANT . " On  fee groups feetype id " . $id;
        $action = "Insert";
        $record_id = $id;
        $this->log($message, $record_id, $action);       
        //======================Code End==============================

        $this->db->trans_complete(); # Completing transaction
        /* Optional */

        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }

    public function getFeesByGroup($id = null) {
        $this->db->select('fee_session_groups.*,fee_groups.name as `group_name`');
        $this->db->from('fee_session_groups');
        $this->db->join('fee_groups', 'fee_groups.id = fee_session_groups.fee_groups_id');
        $this->db->where('fee_session_groups.session_id', $this->current_session);
        $this->db->where('fee_groups.is_system', 0);
        if ($id != null) {
            $this->db->where('fee_session_groups.id', $id);
        }
        $query = $this->db->get();

        $result = $query->result();
        foreach ($result as $key => $value) {
            $value->feetypes = $this->getfeeTypeByGroup($value->id, $value->fee_groups_id);
        }
        return $result;
    }

    public function getfeeTypeByGroup($fee_session_group_id, $id = null) {
        $this->db->select('fee_groups_feetype.*,feetype.type,feetype.code');
        $this->db->from('fee_groups_feetype');
        $this->db->join('feetype', 'feetype.id=fee_groups_feetype.feetype_id');
        $this->db->where('fee_groups_feetype.fee_groups_id', $id);
        $this->db->where('fee_groups_feetype.fee_session_group_id', $fee_session_group_id);
        $this->db->order_by('fee_groups_feetype.id', 'asc');
        $query = $this->db->get();
        return $query->result();
    }

    function group_exists($fee_groups_id) {
        $this->db->where('fee_groups_id', $fee_groups_id);
        $this->db->where('session_id', $this->current_session);
        $query = $this->db->get('fee_session_groups');
        if ($query->num_rows() > 0) {
            return $query->row()->id;
        } else {
            $data = array('fee_groups_id' => $fee_groups_id, 'session_id' => $this->current_session);
            $this->db->insert('fee_session_groups', $data);
            return $this->db->insert_id();
        }
    }

    public function remove($id) {
        $this->db->trans_start(); # Starting Transaction
        $this->db->trans_strict(false); # See Note 01. If you wish can remove as well
        //=======================Code Start===========================
        $sql = "delete fee_groups_feetype.* FROM fee_groups_feetype JOIN fee_session_groups ON fee_session_groups.id = fee_groups_feetype.fee_session_group_id WHERE fee_session_groups.id = ?";
        $this->db->query($sql, array($id));
        $this->db->where('id', $id);
        $this->db->delete('fee_session_groups');

        $message = DELETE_RECORD_CONSTANT . " On fee session groups id " . $id;
        $action = "Delete";
        $record_id = $id;
        $this->log($message, $record_id, $action);
        //======================Code End==============================
        $this->db->trans_complete(); # Completing transaction
        /* Optional */
        if ($this->db->trans_status() === false) {
            # Something went wrong.
            $this->db->trans_rollback();
            return false;
        } else {
            //return $return_value;
        }
    }

    function checkExists($data) {
        $this->db->where('fee_session_group_id', $data['fee_session_group_id']);
        $this->db->where('fee_groups_id', $data['fee_groups_id']);
        $this->db->where('feetype_id', $data['feetype_id']);
        $this->db->where('session_id', $this->current_session);
        $q = $this->db->get('fee_groups_feetype');

        if ($q->num_rows() > 0) {
            return $q->row()->id;
        } else {
            return false;
        }
    }

    public function valid_check_exists($str) {
        $fee_groups_id = $this->input->post('fee_groups_id');
        $feetype_id = $this->input->post('feetype_id');
        $id = $this->input->post('id');

        if (!isset($id)) {
            $id = 0;
        }

        if ($this->check_data_exists($fee_groups_id, $feetype_id, $id)) {
            $this->form_validation->set_message('check_exists', 'FeeGroup combination already exists');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function check_data_exists($fee_groups_id, $feetype_id, $id) {
        $this->db->where('fee_groups_id', $fee_groups_id);
        $this->db->where('session_id', $this->current_session);
        $query = $this->db->get('fee_session_groups');

        if ($query->num_rows() > 0) {
            $fee_session_group_id = $query->row()->id;
            $this->db->where('fee_session_group_id', $fee_session_group_id);
            $this->db->where('fee_groups_id', $fee_groups_id);
            $this->db->where('feetype_id', $feetype_id);
            $this->db->where('id !=', $id);
            $query = $this->db->get('fee_groups_feetype');
            if ($query->num_rows() > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    public function getCalenderMonths()
    {
        $this->db->select('financial_calender.*');
        $this->db->from('financial_calender');
        $query = $this->db->get();
        $result = $query->result();
        return $result;
    }

    public function valid_check_exists2($str) {
        $fee_groups_id = $this->input->post('fee_groups_id');
        $feetype_id = $this->input->post('feetype_id');
        $year = $this->input->post('year');
        $start = $this->input->post('fee_start_month');
        $end = $this->input->post('fee_end_month');
        $id = $this->input->post('id');
        if (!isset($id)) {
            $id = 0;
        }
        if ($this->check_data_exists2($fee_groups_id, $feetype_id, $id,$year,$start,$end)) {
            $this->form_validation->set_message('check_exists', 'FeeGroup combination already exists');
            return FALSE;
        } else {
            return TRUE;
        }
    }

    function check_data_exists2($fee_groups_id, $feetype_id, $id,$year,$start,$end) {
        $this->db->where('fee_groups_id', $fee_groups_id);
        $this->db->where('session_id', $this->current_session);
        $query = $this->db->get('fee_session_groups');

        if ($query->num_rows() > 0) {
            $fee_session_group_id = $query->row()->id;
            $this->db->where('fee_session_group_id', $fee_session_group_id);
            $this->db->where('fee_groups_id', $fee_groups_id);
            $this->db->where('feetype_id', $feetype_id);
            $this->db->where('year', $year);
            $this->db->where("clnd_month BETWEEN  $start and $end");
            $this->db->where('id !=', $id);
            $query = $this->db->get('fee_groups_feetype');
            if ($query->num_rows() > 0) {
                return TRUE;
            } else {
                return FALSE;
            }
        } else {
            return FALSE;
        }
    }

    function getFeeGroup($group)
    {
        $sql = "select fee_groups.student_id,class_id,section_id ,student_session.id
                from fee_groups
                join student_session on fee_groups.student_id=student_session.student_id
                where fee_groups.id = $group and  student_session.session_id =$this->current_session";
        $query = $this->db->query($sql);
        $result = $query->result();
        return $result;

    }

    function fee_exists($data)
    {
//        $this->db->select('id');
        $this->db->where('fee_session_group_id', $data['fee_session_group_id']);
        $this->db->where('feetype_id', $data['feetype_id']);
        $this->db->where('session_id', $data['session_id']);
        $this->db->where('clnd_month', $data['clnd_month']);
        $this->db->where('year', $data['year']);
        $this->db->where('fee_groups_id', $data['fee_groups_id']);
        $this->db->where('clnd_month', $data['clnd_month']);
        $query = $this->db->get('fee_groups_feetype');
        if($query->num_rows()>0)
        {
            return $query->row()->id;
        }
        else
        {
            return FALSE;
        }
    }

    public function add_old($data) {
        $this->db->trans_start();
        $this->db->trans_strict(FALSE);
        $parentid = $this->group_exists($data['fee_groups_id']);
        $data['fee_session_group_id'] = $parentid;
        $feeExists_id = $this->fee_exists($data);

        if($feeExists_id)
        {
//            var_dump($feeExists); die();
            $dataToUpdate = array(
                "amount"=>$data['amount'],
                "due_date"=>$data['due_date'],
            );
            $this->db->where('id', $feeExists_id);
            $this->db->update('fee_groups_feetype', $dataToUpdate);
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return FALSE;
            } else {
                $this->db->trans_commit();
                return 0;
            }
        }
        else
        {

            $this->db->insert('fee_groups_feetype', $data);
            $insert_id = $this->db->insert_id();
            $this->db->trans_complete();
            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                return FALSE;
            } else {
                return $insert_id;

            }
        }
    }

}
