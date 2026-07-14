<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

#[\AllowDynamicProperties]
class Accounts_model extends CI_Model {

    public function __construct() {
        parent::__construct();

        $this->current_session = $this->setting_model->getCurrentSession();
        $this->current_session_name = $this->setting_model->getCurrentSessionName();
        $this->start_month = $this->setting_model->getStartMonth();
    }

    /**
     * This funtion takes id as a parameter and will fetch the record.
     * If id is not provided, then it will fetch all the records form the table.
     * @param int $id
     * @return mixed
     */
    public function get($id = null) {
        $this->db->select()->from('accounts');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row();
        } else {
            return $query->result_array();
        }
    }


    public function get_account_type() {
        // SQL query to get unique account types
        $sql = "SELECT DISTINCT account_type FROM accounts";

        // Execute the query
        $query = $this->db->query($sql);

        if (!$query) {
            // Query failed, log error and last executed query
            return [];
        }

        // Return the result set as an array of account types
        return $query->result_array();
    }


    public function get_account_ids_by_type($type) {
        $this->db->select('id');
        $this->db->from('accounts');
        $this->db->where('account_type', $type);
        $query = $this->db->get();
        return array_column($query->result_array(), 'id');
    }

    // Function to get account details by IDs
    public function get_accounts_by_ids($account_ids) {
        $this->db->select('*');
        $this->db->from('accounts');
        $this->db->where_in('id', $account_ids);
        $query = $this->db->get();
        return $query->result_array();
    }
    /**
     * This function will delete the record based on the id
     * @param $id
     */
    public function delete_account($id) {
        $this->db->where('id', $id);
        $this->db->delete('accounts');
    }

    /**
     * This function will take the post data passed from the controller
     * If id is present, then it will do an update
     * else an insert. One function doing both add and edit.
     * @param $data
     */
    public function add($data) {
        if (isset($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('accounts', $data);
        } else {
            $this->db->insert('accounts', $data);
        }
    }

    public function get_voucher_type($id = null) {
        $this->db->select()->from('voucher_type');
        if ($id != null) {
            $this->db->where('id', $id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($id != null) {
            return $query->row();
        } else {
            return $query->result_array();
        }
    }

    public function get_voucher($user_id=null) {



        $this->db->select('*')->from('draft_voucher_entry');
        if ($user_id != null) {
            $this->db->where('user_id', $user_id);
        } else {
            $this->db->order_by('id');
        }
        $query = $this->db->get();
        if ($user_id != null) {
            return $query->row();
        } else {
            return $query->result_array();
        }
    }
    public function insert_voucher($data) {
        return $this->db->insert('draft_voucher_entry', $data);
    }
    public function update_voucher($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('draft_voucher_entry', $data);
    }
    public function delete_voucher($id) {
        $this->db->where('id', $id);
        return $this->db->delete('draft_voucher_entry');
    }


    public function transfer_draft_voucher_to_journal($user_id) {
        // Retrieve all draft entries
        $draft_entry = $this->get_voucher($user_id);
        $entries = json_decode($draft_entry->voucher_entry, true);

// Initialize an empty array to hold voucher types
        $vouchers = [];

// Loop through each entry and sort into the appropriate array dynamically
        foreach ($entries as $entry) {
            $voucher_type = $entry['voucher_type'];
            if (!isset($vouchers[$voucher_type])) {
                $vouchers[$voucher_type] = [];
            }
            $vouchers[$voucher_type][] = $entry;
        }

// Output the arrays to verify the separation
        $total_entry=0;
        foreach ($vouchers as $type => $voucher_entries) {

            $total_entry++;

//            echo "<pre> ";
//            print_r($voucher_entries[0]['voucher_narration']);
//            echo "</pre>\n";die();
            // Insert into journal_entry table
            $voucher_number=$draft_entry->voucher_number;
            if($total_entry>1){
                $voucher_number=$draft_entry->voucher_number."-".$total_entry;
            }
            $journal_entry_data = array(
                'user_id' => $draft_entry->user_id,
                'voucher_date' => $draft_entry->voucher_date,
                'voucher_type' => $type,
                'voucher_number' => $voucher_number,
                'voucher_narration' => $voucher_entries[0]['voucher_narration']
            );
            $this->db->insert('journal_entry', $journal_entry_data);
            $journal_entry_id = $this->db->insert_id();

            // Insert into journal_entry_detail table
            foreach ($voucher_entries as $entry) {
                $journal_entry_detail_data = array(
                    'user_id' => $draft_entry->user_id,
                    'journal_entry_id' => $journal_entry_id,
                    'voucher_type' => $type,
                    'voucher_date' => $draft_entry->voucher_date,
                    'debit_account_id' => $entry['debit_account_id'],
                    'debit_account_name' => $entry['debit_account_name'],
                    'debit_account_number' => $entry['debit_account_number'],
                    'debit_amount' => $entry['debit_amount'],
                    'credit_account_id' => $entry['credit_account_id'],
                    'credit_account_name' => $entry['credit_account_name'],
                    'credit_account_number' => $entry['credit_account_number'],
                    'credit_amount' => $entry['credit_amount'],
                    'description' => $entry['description']
                );
                $this->db->insert('journal_entry_detail', $journal_entry_detail_data);
            }
        }


            // Delete the processed draft entry
            $this->db->delete('draft_voucher_entry', array('id' => $draft_entry->id));
    }

    public function get_all_journal_entries() {
        return $this->db->get('journal_entry')->result_array();
    }
    public function get_journal_entries_range($start_date, $end_date) {
        if (empty($start_date) || empty($end_date)) {
            $start_date = date('Y-m-01'); // Current month's start date
            $end_date = date('Y-m-t');   // Current month's end date
        }

        // Query the journal_entry table with the date range
        return $this->db->where('voucher_date >=', $start_date)
            ->where('voucher_date <=', $end_date)
            ->get('journal_entry')
            ->result_array();
//        return $this->db->get('journal_entry')->result_array();
    }

    public function get_journal_entry($id) {
        return $this->db->get_where('journal_entry', array('id' => $id))->row_array();
    }

    public function get_journal_entry_details($id) {
        return $this->db->get_where('journal_entry_detail', array('journal_entry_id' => $id))->result_array();
    }
    public function insert_journal($data) {
        return $this->db->insert('journal_entry', $data);
    }
    public function update_journal($id, $data) {
        $this->db->where('id', $id);
        return $this->db->update('journal_entry', $data);
    }
    public function delete_journal($id) {
        $this->db->where('id', $id);
        return $this->db->delete('journal_entry');
    }


    public function get_opening_balances($start_date) {
        // Fetch cumulative debits and credits up to the start date for each account
        $this->db->select('
            debit_account_id as account_id,
            SUM(debit_amount) as total_debit,
        ');
        $this->db->from('journal_entry_detail');
        $this->db->join('journal_entry', 'journal_entry.id = journal_entry_detail.journal_entry_id');
        $this->db->where('journal_entry.voucher_date <', $start_date);
        $this->db->group_by('debit_account_id');
        $query = $this->db->get();
        $result = $query->result_array();

        $this->db->select('
            credit_account_id as account_id,
            SUM(credit_amount) as total_credit
        ');
        $this->db->from('journal_entry_detail');
        $this->db->join('journal_entry', 'journal_entry.id = journal_entry_detail.journal_entry_id');
        $this->db->where('journal_entry.voucher_date <', $start_date);
        $this->db->group_by('credit_account_id');
        $query = $this->db->get();
        $result2 = $query->result_array();

        $balances = [];
        foreach ($result as $debit) {
            $balances[$debit['account_id']]['total_debit'] = $debit['total_debit'];
        }
        foreach ($result2 as $credit) {
            $balances[$credit['account_id']]['total_credit'] = $credit['total_credit'];
        }

// Calculate opening balances
        $opening_balances = [];
        foreach ($balances as $account_id => $totals) {
            $total_debit = $totals['total_debit'] ?? 0; // Default to 0 if not set
            $total_credit = $totals['total_credit'] ?? 0; // Default to 0 if not set
            $opening_balances[$account_id] = $total_debit - $total_credit;
        }

        return $opening_balances;
    }

    public function get_entries_by_date_range($start_date, $end_date) {
        $this->db->select('journal_entry_detail.*');
        $this->db->from('journal_entry');
        $this->db->join('journal_entry_detail', 'journal_entry.id = journal_entry_detail.journal_entry_id');
        $this->db->where('journal_entry.voucher_date >=', $start_date);
        $this->db->where('journal_entry.voucher_date <=', $end_date);
        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_opening_balances_acounts($account_ids, $start_date) {
        $this->db->select('
            debit_account_id as account_id,
            SUM(debit_amount) as total_debit,
        ');
        $this->db->from('journal_entry_detail');
        $this->db->join('journal_entry', 'journal_entry.id = journal_entry_detail.journal_entry_id');
        $this->db->where_in('debit_account_id', $account_ids);
        $this->db->where('journal_entry.voucher_date <', $start_date);
        $this->db->group_by('debit_account_id');
        $query = $this->db->get();
        $result = $query->result_array();

        $this->db->select('
            credit_account_id as account_id,
            SUM(credit_amount) as total_credit
        ');
        $this->db->from('journal_entry_detail');
        $this->db->join('journal_entry', 'journal_entry.id = journal_entry_detail.journal_entry_id');
        $this->db->where_in('credit_account_id', $account_ids);

        $this->db->where('journal_entry.voucher_date <', $start_date);
        $this->db->group_by('credit_account_id');
        $query = $this->db->get();
        $result2 = $query->result_array();

        $balances = [];
        foreach ($result as $debit) {
            $balances[$debit['account_id']]['total_debit'] = $debit['total_debit'];
        }
        foreach ($result2 as $credit) {
            $balances[$credit['account_id']]['total_credit'] = $credit['total_credit'];
        }

// Calculate opening balances
        $opening_balances = [];
        foreach ($balances as $account_id => $totals) {
            $total_debit = $totals['total_debit'] ?? 0; // Default to 0 if not set
            $total_credit = $totals['total_credit'] ?? 0; // Default to 0 if not set
            $opening_balances[$account_id] = $total_debit - $total_credit;
        }

        return $opening_balances;
    }

    // Function to get entries by date range
    public function get_entries_by_date_range_acounts($account_ids, $start_date, $end_date) {
        $this->db->select('journal_entry_detail.*');
        $this->db->from('journal_entry');
        $this->db->join('journal_entry_detail', 'journal_entry.id = journal_entry_detail.journal_entry_id');
        $this->db->where('journal_entry.voucher_date >=', $start_date);
        $this->db->where('journal_entry.voucher_date <=', $end_date);
        $this->db->group_start();
        $this->db->where_in('journal_entry_detail.debit_account_id', $account_ids);
        $this->db->or_where_in('journal_entry_detail.credit_account_id', $account_ids);
        $this->db->group_end();
        $query = $this->db->get();
        return $query->result_array();
    }
    public function get_entries_by_date_range_acounts_details($account_ids, $start_date, $end_date) {
        $this->db->select('journal_entry_detail.*');
        $this->db->from('journal_entry');
        $this->db->join('journal_entry_detail', 'journal_entry.id = journal_entry_detail.journal_entry_id');
        $this->db->where('journal_entry.voucher_date >=', $start_date);
        $this->db->where('journal_entry.voucher_date <=', $end_date);
        $this->db->group_start();
        $this->db->where_in('journal_entry_detail.debit_account_id', $account_ids);
        $this->db->or_where_in('journal_entry_detail.credit_account_id', $account_ids);
        $this->db->group_end();
        $this->db->group_by('journal_entry.voucher_date');

        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_account_balances($type, $start_date, $end_date) {
        // Define SQL query
        $sql = "
        SELECT
            a.id,
            a.account_number,
            a.account_name,
            a.account_group,
            a.account_sub_group,
            a.account_type,
            COALESCE(SUM(o.opening_balance), 0) AS opening_balance,
            COALESCE(SUM(d.total_debit), 0) AS total_debit,
            COALESCE(SUM(c.total_credit), 0) AS total_credit
        FROM
            accounts a
        LEFT JOIN
            (
                SELECT
                    accounts.id,
                    SUM(CASE WHEN journal_entry_detail.voucher_date < ? THEN journal_entry_detail.debit_amount - journal_entry_detail.credit_amount ELSE 0 END) AS opening_balance
                FROM
                    accounts
                LEFT JOIN
                    journal_entry_detail ON accounts.id = journal_entry_detail.debit_account_id OR accounts.id = journal_entry_detail.credit_account_id
                WHERE
                    accounts.account_type = ?
                GROUP BY
                    accounts.id
            ) o ON a.id = o.id
        LEFT JOIN
            (
                SELECT
                    accounts.id,
                    SUM(CASE WHEN journal_entry_detail.voucher_date BETWEEN ? AND ? THEN journal_entry_detail.debit_amount ELSE 0 END) AS total_debit
                FROM
                    accounts
                LEFT JOIN
                    journal_entry_detail ON accounts.id = journal_entry_detail.debit_account_id
                WHERE
                    accounts.account_type = ?
                GROUP BY
                    accounts.id
            ) d ON a.id = d.id
        LEFT JOIN
            (
                SELECT
                    accounts.id,
                    SUM(CASE WHEN journal_entry_detail.voucher_date BETWEEN ? AND ? THEN journal_entry_detail.credit_amount ELSE 0 END) AS total_credit
                FROM
                    accounts
                LEFT JOIN
                    journal_entry_detail ON accounts.id = journal_entry_detail.credit_account_id
                WHERE
                    accounts.account_type = ?
                GROUP BY
                    accounts.id
            ) c ON a.id = c.id
        WHERE
            a.account_type = ?
        GROUP BY
            a.id, a.account_number, a.account_name, a.account_group, a.account_sub_group, a.account_type";

        // Execute the query
        $query = $this->db->query($sql, array($start_date, $type, $start_date, $end_date, $type, $start_date, $end_date, $type, $type));

        // Return the result set
        return $query->result();
    }
}
