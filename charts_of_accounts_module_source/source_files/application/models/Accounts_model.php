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

    public function update_account($id, $data) {
        $this->db->where('id', $id);
        $this->db->update('accounts', $data);
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

            // Insert into journal_entry_detail table — one row per leg (proper double-entry)
            foreach ($voucher_entries as $entry) {
                $base = array(
                    'user_id'          => $draft_entry->user_id,
                    'journal_entry_id' => $journal_entry_id,
                    'voucher_type'     => $type,
                    'voucher_date'     => $draft_entry->voucher_date,
                    'description'      => $entry['description'],
                    // zero out unused side
                    'debit_account_id'     => 0, 'debit_account_name'   => '', 'debit_account_number' => '', 'debit_amount' => 0,
                    'credit_account_id'    => 0, 'credit_account_name'  => '', 'credit_account_number'=> '', 'credit_amount'=> 0,
                );
                // Debit row
                $debit_row = $base;
                $debit_row['debit_account_id']     = $entry['debit_account_id'];
                $debit_row['debit_account_name']   = $entry['debit_account_name'];
                $debit_row['debit_account_number'] = $entry['debit_account_number'];
                $debit_row['debit_amount']         = $entry['debit_amount'];
                $this->db->insert('journal_entry_detail', $debit_row);
                // Credit row
                $credit_row = $base;
                $credit_row['credit_account_id']     = $entry['credit_account_id'];
                $credit_row['credit_account_name']   = $entry['credit_account_name'];
                $credit_row['credit_account_number'] = $entry['credit_account_number'];
                $credit_row['credit_amount']         = $entry['credit_amount'];
                $this->db->insert('journal_entry_detail', $credit_row);
            }
        }


            // Delete the processed draft entry
            $this->db->delete('draft_voucher_entry', array('id' => $draft_entry->id));
    }

    public function get_fee_collection_report($filters = []) {
        // Select bank_account_id from amount_detail JSON via subquery to avoid fan-out joins
        $this->db->select("
            sfd.id            AS dep_id,
            sfd.created_at    AS payment_date,
            sfd.amount_detail,
            s.admission_no,
            CONCAT(s.firstname,' ',IFNULL(NULLIF(TRIM(s.middlename),''),' '),'',IFNULL(s.lastname,'')) AS student_name,
            c.class,
            ft.type           AS fee_type,
            fgf.clnd_month,
            fgf.year          AS fee_year,
            fgf.Session       AS fee_session
        ", false);
        $this->db->from('student_fees_deposite sfd');
        $this->db->join('student_fees_master sfm', 'sfm.id = sfd.student_fees_master_id');
        $this->db->join('student_session ss',      'ss.id = sfm.student_session_id');
        $this->db->join('students s',              's.id = ss.student_id');
        $this->db->join('classes c',               'c.id = ss.class_id');
        $this->db->join('fee_groups_feetype fgf',  'fgf.id = sfd.fee_groups_feetype_id');
        $this->db->join('feetype ft',              'ft.id = fgf.feetype_id');
        // No journal_entry join — avoids fan-out; bank account resolved from amount_detail in PHP

        if (!empty($filters['start_date'])) {
            $this->db->where('DATE(sfd.created_at) >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->where('DATE(sfd.created_at) <=', $filters['end_date']);
        }
        if (!empty($filters['class_id'])) {
            $this->db->where('ss.class_id', (int)$filters['class_id']);
        }
        if (!empty($filters['fee_type_id'])) {
            $this->db->where('fgf.feetype_id', (int)$filters['fee_type_id']);
        }
        if (!empty($filters['admission_no'])) {
            $this->db->like('s.admission_no', $filters['admission_no']);
        }

        $this->db->order_by('sfd.created_at', 'DESC');
        $result = $this->db->get()->result_array();

        // Pre-load all accounts for bank name lookup
        $accs_raw = $this->db->get('accounts')->result_array();
        $accs = [];
        foreach ($accs_raw as $a) { $accs[$a['id']] = $a; }

        $out = [];
        foreach ($result as $r) {
            $ad = json_decode($r['amount_detail'], true);
            if (!is_array($ad)) continue;
            $total = 0;
            $mode  = '—';
            $bank_id = 0;
            foreach ($ad as $item) {
                $total   += (float)($item['amount'] ?? 0);
                $mode     = $item['payment_mode'] ?? $mode;
                $bank_id  = (int)($item['bank_account_id'] ?? 0);
            }
            if ($total <= 0) continue; // skip zero-amount rows

            $r['amount']           = $total;
            $r['mode']             = $mode;
            $r['bank_account']     = isset($accs[$bank_id]) ? $accs[$bank_id]['account_name'] : '—';
            $r['bank_account_no']  = isset($accs[$bank_id]) ? $accs[$bank_id]['account_number'] : '';
            $out[] = $r;
        }
        return $out;
    }

    public function get_all_journal_entries() {
        return $this->db->get('journal_entry')->result_array();
    }

    public function get_journal_entries_with_totals($filters = array()) {
        $this->db->select('
            je.id,
            je.voucher_date,
            je.voucher_number,
            je.voucher_narration,
            je.voucher_type,
            COALESCE(SUM(jed.debit_amount), 0) as total
        ');
        $this->db->from('journal_entry je');
        $this->db->join('journal_entry_detail jed', 'jed.journal_entry_id = je.id', 'left');

        if (!empty($filters['account_id'])) {
            $aid = (int)$filters['account_id'];
            $this->db->where("(jed.debit_account_id = $aid OR jed.credit_account_id = $aid)", null, false);
        }
        if (!empty($filters['ref_no'])) {
            $this->db->like('je.voucher_number', $filters['ref_no']);
        }
        if (!empty($filters['start_date'])) {
            $this->db->where('je.voucher_date >=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $this->db->where('je.voucher_date <=', $filters['end_date']);
        }

        $this->db->group_by('je.id');
        $this->db->order_by('je.id', 'DESC');
        return $this->db->get()->result_array();
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
        $ids = implode(',', array_map('intval', $account_ids));

        $sql = "
        SELECT account_id,
               SUM(debit_amount)  AS total_debit,
               SUM(credit_amount) AS total_credit
        FROM (
            SELECT jed.debit_account_id AS account_id, jed.debit_amount, 0 AS credit_amount
            FROM journal_entry_detail jed
            JOIN journal_entry je ON je.id = jed.journal_entry_id
            WHERE jed.debit_amount > 0
              AND jed.debit_account_id IN ($ids)
              AND je.voucher_date < ?
            UNION ALL
            SELECT jed.credit_account_id, 0, jed.credit_amount
            FROM journal_entry_detail jed
            JOIN journal_entry je ON je.id = jed.journal_entry_id
            WHERE jed.credit_amount > 0
              AND jed.credit_account_id IN ($ids)
              AND je.voucher_date < ?
        ) mv
        GROUP BY account_id";

        $rows = $this->db->query($sql, array($start_date, $start_date))->result_array();

        $opening_balances = [];
        foreach ($rows as $row) {
            $opening_balances[$row['account_id']] = (float)$row['total_debit'] - (float)$row['total_credit'];
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
        /*
         * Returns one row per ledger movement for the given accounts.
         * Uses UNION ALL so that:
         *   - Old-style rows (debit_account_id != credit_account_id, both amounts > 0)
         *     appear once on the debit side and once on the credit side.
         *   - New-style rows (same account on both sides, only one amount > 0)
         *     appear exactly once for whichever side is non-zero.
         * Each row has: account_id, side ('Dr'|'Cr'), amount, voucher_date,
         *               voucher_number, description, journal_entry_id, det_id
         */
        $ids = implode(',', array_map('intval', $account_ids));

        $sql = "
        SELECT
            jed.debit_account_id  AS account_id,
            'Dr'                  AS side,
            jed.debit_amount      AS amount,
            je.voucher_date,
            je.voucher_number,
            je.voucher_narration,
            jed.description,
            je.id                 AS journal_entry_id,
            jed.id                AS det_id
        FROM journal_entry_detail jed
        JOIN journal_entry je ON je.id = jed.journal_entry_id
        WHERE jed.debit_amount > 0
          AND jed.debit_account_id IN ($ids)
          AND je.voucher_date BETWEEN ? AND ?

        UNION ALL

        SELECT
            jed.credit_account_id AS account_id,
            'Cr'                  AS side,
            jed.credit_amount     AS amount,
            je.voucher_date,
            je.voucher_number,
            je.voucher_narration,
            jed.description,
            je.id                 AS journal_entry_id,
            jed.id                AS det_id
        FROM journal_entry_detail jed
        JOIN journal_entry je ON je.id = jed.journal_entry_id
        WHERE jed.credit_amount > 0
          AND jed.credit_account_id IN ($ids)
          AND je.voucher_date BETWEEN ? AND ?

        ORDER BY voucher_date, journal_entry_id, det_id, side DESC
        ";

        return $this->db->query($sql, array($start_date, $end_date, $start_date, $end_date))->result_array();
    }

    public function get_account_balances($type, $start_date, $end_date) {
        /*
         * Each journal_entry_detail row represents one ledger line.
         * Rows may be "paired" (old-style: debit_account_id != credit_account_id,
         * both amounts > 0) or "split" (new-style: debit_account_id = credit_account_id,
         * only one of debit_amount / credit_amount is non-zero per row).
         *
         * Strategy: compute total debits and credits for each account separately
         * using two UNION subqueries — one for the debit side, one for the credit side —
         * then aggregate.  This avoids double-counting when debit_account_id = credit_account_id.
         */
        $sql = "
        SELECT
            a.id,
            a.account_number,
            a.account_name,
            a.account_group,
            a.account_sub_group,
            a.account_type,
            COALESCE(SUM(CASE WHEN mv.voucher_date < ? THEN mv.debit_amount  ELSE 0 END), 0)
              - COALESCE(SUM(CASE WHEN mv.voucher_date < ? THEN mv.credit_amount ELSE 0 END), 0)
              AS opening_balance,
            COALESCE(SUM(CASE WHEN mv.voucher_date BETWEEN ? AND ? THEN mv.debit_amount  ELSE 0 END), 0) AS total_debit,
            COALESCE(SUM(CASE WHEN mv.voucher_date BETWEEN ? AND ? THEN mv.credit_amount ELSE 0 END), 0) AS total_credit
        FROM accounts a
        LEFT JOIN (
            /* Debit side: each row contributes its debit_amount to the debit account */
            SELECT
                jed.debit_account_id  AS account_id,
                je.voucher_date,
                jed.debit_amount      AS debit_amount,
                0                     AS credit_amount
            FROM journal_entry_detail jed
            JOIN journal_entry je ON je.id = jed.journal_entry_id
            WHERE jed.debit_amount > 0

            UNION ALL

            /* Credit side: each row contributes its credit_amount to the credit account */
            SELECT
                jed.credit_account_id AS account_id,
                je.voucher_date,
                0                     AS debit_amount,
                jed.credit_amount     AS credit_amount
            FROM journal_entry_detail jed
            JOIN journal_entry je ON je.id = jed.journal_entry_id
            WHERE jed.credit_amount > 0
        ) mv ON mv.account_id = a.id
        WHERE a.account_type = ?
        GROUP BY a.id, a.account_number, a.account_name, a.account_group, a.account_sub_group, a.account_type";

        $query = $this->db->query($sql, array(
            $start_date, $start_date,           // opening_balance debit < start
            $start_date, $end_date,             // total_debit BETWEEN
            $start_date, $end_date,             // total_credit BETWEEN
            $type                               // WHERE account_type
        ));

        return $query->result();
    }

    public function get_bank_cash_accounts() {
        return $this->db->where_in('account_sub_group', ['Bank', 'Cash'])
                        ->order_by('account_sub_group, account_number')
                        ->get('accounts')->result_array();
    }

    public function get_bank_payments($filters = []) {
        $sql = "
        SELECT
            je.id,
            je.voucher_number,
            je.voucher_date,
            je.voucher_narration,
            jed.debit_account_id   AS nominal_id,
            jed.debit_account_name AS nominal_name,
            jed.credit_account_id  AS bank_id,
            jed.credit_account_name AS bank_name,
            jed.debit_amount       AS amount,
            jed.description        AS details,
            a.account_sub_group    AS bank_sub_group
        FROM journal_entry je
        JOIN journal_entry_detail jed ON jed.journal_entry_id = je.id
        JOIN accounts a ON a.id = jed.credit_account_id
        WHERE je.voucher_type = 'BPV'
        ";
        $params = [];

        if (!empty($filters['bank_id'])) {
            $sql .= " AND jed.credit_account_id = ?";
            $params[] = (int)$filters['bank_id'];
        }
        if (!empty($filters['nominal_id'])) {
            $sql .= " AND jed.debit_account_id = ?";
            $params[] = (int)$filters['nominal_id'];
        }
        if (!empty($filters['ref_no'])) {
            $sql .= " AND je.voucher_number LIKE ?";
            $params[] = '%' . $filters['ref_no'] . '%';
        }
        if (!empty($filters['details'])) {
            $sql .= " AND jed.description LIKE ?";
            $params[] = '%' . $filters['details'] . '%';
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND je.voucher_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND je.voucher_date <= ?";
            $params[] = $filters['end_date'];
        }
        $sql .= " ORDER BY je.voucher_date DESC, je.id DESC";
        return $this->db->query($sql, $params)->result_array();
    }

    public function get_reconciliation_transactions($bank_id, $statement_date) {
        // Ensure reconciled column exists before querying it
        $this->_ensure_reconciled_columns();

        $sql = "
        SELECT
            je.id              AS je_id,
            je.voucher_date,
            je.voucher_type,
            je.voucher_number,
            je.voucher_narration,
            jed.id             AS detail_id,
            jed.description,
            jed.debit_amount,
            jed.credit_amount,
            jed.debit_account_id,
            jed.credit_account_id,
            CASE
                WHEN jed.debit_account_id = ? THEN ca.account_name
                ELSE da.account_name
            END AS counterpart_name,
            CASE
                WHEN jed.debit_account_id = ? THEN jed.debit_amount
                ELSE 0
            END AS receipt_amount,
            CASE
                WHEN jed.credit_account_id = ? THEN jed.credit_amount
                ELSE 0
            END AS payment_amount,
            COALESCE(jed.reconciled, 0) AS reconciled
        FROM journal_entry_detail jed
        JOIN journal_entry je ON je.id = jed.journal_entry_id
        LEFT JOIN accounts da ON da.id = jed.debit_account_id
        LEFT JOIN accounts ca ON ca.id = jed.credit_account_id
        WHERE (jed.debit_account_id = ? OR jed.credit_account_id = ?)
          AND je.voucher_date <= ?
        ORDER BY je.voucher_date ASC, je.id ASC, jed.id ASC";

        return $this->db->query($sql, [
            $bank_id, $bank_id, $bank_id, $bank_id, $bank_id, $statement_date
        ])->result_array();
    }

    public function get_reconciliation_opening_balance($bank_id, $statement_date) {
        // Net balance for this bank account for all transactions strictly BEFORE
        // the first transaction in the loaded period — returns 0 if none exist before.
        $sql = "
        SELECT
            COALESCE(SUM(CASE WHEN jed.debit_account_id  = ? THEN jed.debit_amount  ELSE 0 END), 0) -
            COALESCE(SUM(CASE WHEN jed.credit_account_id = ? THEN jed.credit_amount ELSE 0 END), 0)
            AS opening_balance
        FROM journal_entry_detail jed
        JOIN journal_entry je ON je.id = jed.journal_entry_id
        WHERE (jed.debit_account_id = ? OR jed.credit_account_id = ?)
          AND je.voucher_date < IFNULL(
              (SELECT MIN(je2.voucher_date)
               FROM journal_entry_detail jed2
               JOIN journal_entry je2 ON je2.id = jed2.journal_entry_id
               WHERE (jed2.debit_account_id = ? OR jed2.credit_account_id = ?)
                 AND je2.voucher_date <= ?),
              '1900-01-01'
          )";
        $row = $this->db->query($sql, [
            $bank_id, $bank_id, $bank_id, $bank_id, $bank_id, $bank_id, $statement_date
        ])->row_array();
        return $row ? (float)$row['opening_balance'] : 0.0;
    }

    private function _ensure_reconciled_columns() {
        if (!$this->db->field_exists('reconciled', 'journal_entry_detail')) {
            $this->db->query("ALTER TABLE journal_entry_detail ADD COLUMN reconciled TINYINT(1) NOT NULL DEFAULT 0");
        }
        if (!$this->db->field_exists('reconciled_bank_id', 'journal_entry_detail')) {
            $this->db->query("ALTER TABLE journal_entry_detail ADD COLUMN reconciled_bank_id INT DEFAULT NULL");
        }
        if (!$this->db->field_exists('reconciled_date', 'journal_entry_detail')) {
            $this->db->query("ALTER TABLE journal_entry_detail ADD COLUMN reconciled_date DATE DEFAULT NULL");
        }
    }

    public function save_reconciliation($bank_id, $statement_date, $statement_balance, $reconciled_detail_ids) {
        $this->_ensure_reconciled_columns();

        // Reset any prior reconciliation for this bank on this statement date
        $this->db->query(
            "UPDATE journal_entry_detail SET reconciled=0, reconciled_bank_id=NULL, reconciled_date=NULL
             WHERE reconciled_bank_id = ? AND reconciled_date = ?",
            [$bank_id, $statement_date]
        );

        // Mark selected detail rows as reconciled
        if (!empty($reconciled_detail_ids)) {
            foreach ($reconciled_detail_ids as $detail_id) {
                $detail_id = (int)$detail_id;
                if ($detail_id < 1) continue;
                $this->db->query(
                    "UPDATE journal_entry_detail SET reconciled=1, reconciled_bank_id=?, reconciled_date=?
                     WHERE id=? AND (debit_account_id=? OR credit_account_id=?)",
                    [$bank_id, $statement_date, $detail_id, $bank_id, $bank_id]
                );
            }
        }
    }

    public function get_bank_transfers($filters = []) {
        $sql = "
        SELECT
            je.id,
            je.voucher_number,
            je.voucher_date,
            je.voucher_narration,
            jed.credit_account_id   AS from_bank_id,
            jed.credit_account_name AS from_bank_name,
            jed.debit_account_id    AS to_bank_id,
            jed.debit_account_name  AS to_bank_name,
            jed.debit_amount        AS amount,
            jed.description         AS details
        FROM journal_entry je
        JOIN journal_entry_detail jed ON jed.journal_entry_id = je.id
        WHERE je.voucher_type = 'TRF'
        ";
        $params = [];

        if (!empty($filters['from_bank_id'])) {
            $sql .= " AND jed.credit_account_id = ?";
            $params[] = (int)$filters['from_bank_id'];
        }
        if (!empty($filters['to_bank_id'])) {
            $sql .= " AND jed.debit_account_id = ?";
            $params[] = (int)$filters['to_bank_id'];
        }
        if (!empty($filters['ref_no'])) {
            $sql .= " AND je.voucher_number LIKE ?";
            $params[] = '%' . $filters['ref_no'] . '%';
        }
        if (!empty($filters['details'])) {
            $sql .= " AND jed.description LIKE ?";
            $params[] = '%' . $filters['details'] . '%';
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND je.voucher_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND je.voucher_date <= ?";
            $params[] = $filters['end_date'];
        }
        $sql .= " ORDER BY je.voucher_date DESC, je.id DESC";
        return $this->db->query($sql, $params)->result_array();
    }

    public function get_bank_receipts($filters = []) {
        $sql = "
        SELECT
            je.id,
            je.voucher_number,
            je.voucher_date,
            je.voucher_narration,
            jed.credit_account_id   AS nominal_id,
            jed.credit_account_name AS nominal_name,
            jed.debit_account_id    AS bank_id,
            jed.debit_account_name  AS bank_name,
            jed.debit_amount        AS amount,
            jed.description         AS details,
            a.account_sub_group     AS bank_sub_group
        FROM journal_entry je
        JOIN journal_entry_detail jed ON jed.journal_entry_id = je.id
        JOIN accounts a ON a.id = jed.debit_account_id
        WHERE je.voucher_type = 'BRV'
        ";
        $params = [];

        if (!empty($filters['bank_id'])) {
            $sql .= " AND jed.debit_account_id = ?";
            $params[] = (int)$filters['bank_id'];
        }
        if (!empty($filters['nominal_id'])) {
            $sql .= " AND jed.credit_account_id = ?";
            $params[] = (int)$filters['nominal_id'];
        }
        if (!empty($filters['ref_no'])) {
            $sql .= " AND je.voucher_number LIKE ?";
            $params[] = '%' . $filters['ref_no'] . '%';
        }
        if (!empty($filters['details'])) {
            $sql .= " AND jed.description LIKE ?";
            $params[] = '%' . $filters['details'] . '%';
        }
        if (!empty($filters['start_date'])) {
            $sql .= " AND je.voucher_date >= ?";
            $params[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= " AND je.voucher_date <= ?";
            $params[] = $filters['end_date'];
        }
        $sql .= " ORDER BY je.voucher_date DESC, je.id DESC";
        return $this->db->query($sql, $params)->result_array();
    }

    public function get_all_account_balances($as_of_date) {
        $sql = "
        SELECT
            a.id,
            a.account_number,
            a.account_name,
            a.account_type,
            a.account_group,
            a.account_sub_group,
            COALESCE(SUM(mv.debit_amount), 0) - COALESCE(SUM(mv.credit_amount), 0) AS net_balance
        FROM accounts a
        LEFT JOIN (
            SELECT jed.debit_account_id AS account_id, jed.debit_amount AS debit_amount, 0 AS credit_amount
            FROM journal_entry_detail jed
            JOIN journal_entry je ON je.id = jed.journal_entry_id
            WHERE jed.debit_amount > 0 AND je.voucher_date <= ?

            UNION ALL

            SELECT jed.credit_account_id AS account_id, 0 AS debit_amount, jed.credit_amount AS credit_amount
            FROM journal_entry_detail jed
            JOIN journal_entry je ON je.id = jed.journal_entry_id
            WHERE jed.credit_amount > 0 AND je.voucher_date <= ?
        ) mv ON mv.account_id = a.id
        WHERE a.account_sub_group IN ('Bank', 'Cash')
        GROUP BY a.id, a.account_number, a.account_name, a.account_type, a.account_group, a.account_sub_group
        ORDER BY a.account_sub_group, a.account_number";

        return $this->db->query($sql, array($as_of_date, $as_of_date))->result_array();
    }

    // Payable voucher methods
    public function get_payable($user_id = null) {
        // Check if table exists
        if (!$this->db->table_exists('draft_payable_entry')) {
            // Table doesn't exist, return null/false
            log_message('error', 'Table draft_payable_entry does not exist. Please run SQL_COMMANDS.sql to create it.');
            return null;
        }
        
        $this->db->select('*')->from('draft_payable_entry');
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

    public function insert_payable($data) {
        // Check if table exists
        if (!$this->db->table_exists('draft_payable_entry')) {
            log_message('error', 'Table draft_payable_entry does not exist. Please run SQL_COMMANDS.sql to create it.');
            return false;
        }
        return $this->db->insert('draft_payable_entry', $data);
    }

    // ── Opening Balances ────────────────────────────────────────────────────

    public function get_opening_balances_list() {
        $sql = "SELECT ob.*, a.account_number, a.account_name, a.account_type, a.account_group
                FROM account_opening_balances ob
                JOIN accounts a ON a.id = ob.account_id
                ORDER BY a.account_type, a.account_group, a.account_name";
        return $this->db->query($sql)->result_array();
    }

    public function get_opening_balance_by_id($id) {
        return $this->db->where('id', (int)$id)->get('account_opening_balances')->row_array();
    }

    public function get_opening_balance_by_account($account_id, $financial_year = null) {
        $this->db->where('account_id', (int)$account_id);
        if ($financial_year) {
            $this->db->where('financial_year', $financial_year);
        }
        return $this->db->get('account_opening_balances')->row_array();
    }

    public function save_opening_balance($data) {
        $existing = $this->get_opening_balance_by_account($data['account_id'], $data['financial_year'] ?? null);
        if ($existing) {
            $this->db->where('id', $existing['id'])->update('account_opening_balances', $data);
            return $existing['id'];
        } else {
            $this->db->insert('account_opening_balances', $data);
            return $this->db->insert_id();
        }
    }

    public function delete_opening_balance($id) {
        $this->db->where('id', (int)$id)->delete('account_opening_balances');
    }

    public function update_payable($id, $data) {
        // Check if table exists
        if (!$this->db->table_exists('draft_payable_entry')) {
            log_message('error', 'Table draft_payable_entry does not exist. Please run SQL_COMMANDS.sql to create it.');
            return false;
        }
        $this->db->where('id', $id);
        return $this->db->update('draft_payable_entry', $data);
    }

    public function delete_payable($id) {
        // Check if table exists
        if (!$this->db->table_exists('draft_payable_entry')) {
            log_message('error', 'Table draft_payable_entry does not exist. Please run SQL_COMMANDS.sql to create it.');
            return false;
        }
        $this->db->where('id', $id);
        return $this->db->delete('draft_payable_entry');
    }

    public function transfer_payable_to_journal($user_id, $selected_entries) {
        // Retrieve payable entry
        $payable_entry = $this->get_payable($user_id);
        if (!$payable_entry) {
            return false;
        }

        // Get voucher type for payable
        $voucher_types = $this->get_voucher_type();
        $payable_voucher_type = 'Payable Voucher';
        foreach ($voucher_types as $vt) {
            if (stripos($vt['name'], 'payable') !== false || stripos($vt['type'], 'payable') !== false) {
                $payable_voucher_type = $vt['type'];
                break;
            }
        }

        // Generate voucher number
        $voucher_number = $payable_entry->payable_number;
        if (empty($voucher_number)) {
            $voucher_number = strtoupper(substr(md5(rand()), 0, 4)) . '-' . strtoupper(substr(md5(rand()), 0, 6)) . '-' . strtoupper(substr(md5(rand()), 0, 3));
        }

        // Insert into journal_entry table
        $journal_entry_data = array(
            'user_id' => $payable_entry->user_id,
            'voucher_date' => $payable_entry->payable_date,
            'voucher_type' => $payable_voucher_type,
            'voucher_number' => $voucher_number,
            'voucher_narration' => $payable_entry->payable_narration
        );
        $this->db->insert('journal_entry', $journal_entry_data);
        $journal_entry_id = $this->db->insert_id();
        
        // Process each selected entry - use the selected debit and credit accounts
        foreach ($selected_entries as $entry) {
            $debit_account_id = isset($entry['debit_account_id']) ? intval($entry['debit_account_id']) : 0;
            $credit_account_id = isset($entry['credit_account_id']) ? intval($entry['credit_account_id']) : 0;
            $description = isset($entry['description']) ? $entry['description'] : 'Payable Entry';
            $amount = isset($entry['amount']) ? floatval($entry['amount']) : 0;
            
            if ($debit_account_id > 0 && $credit_account_id > 0 && $amount > 0) {
                // Get debit and credit account details
                $debit_account = $this->get($debit_account_id);
                $credit_account = $this->get($credit_account_id);
                
                if ($debit_account && $credit_account) {
                    $journal_entry_detail_data = array(
                        'user_id' => $payable_entry->user_id,
                        'journal_entry_id' => $journal_entry_id,
                        'voucher_type' => $payable_voucher_type,
                        'voucher_date' => $payable_entry->payable_date,
                        'debit_account_id' => $debit_account_id,
                        'debit_account_name' => $debit_account->account_name,
                        'debit_account_number' => $debit_account->account_number,
                        'debit_amount' => $amount,
                        'credit_account_id' => $credit_account_id,
                        'credit_account_name' => $credit_account->account_name,
                        'credit_account_number' => $credit_account->account_number,
                        'credit_amount' => $amount,
                        'description' => $payable_entry->payable_narration . ' - ' . $description
                    );
                    $this->db->insert('journal_entry_detail', $journal_entry_detail_data);
                }
            }
        }

        // Delete the processed payable entry
        $this->db->delete('draft_payable_entry', array('id' => $payable_entry->id));
        
        return true;
    }
}
