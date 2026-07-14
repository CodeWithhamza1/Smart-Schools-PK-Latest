<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

#[\AllowDynamicProperties]
class Accounts extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('accounts_model'));

    }

    public function all_accounts()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/all_accounts');

        $accounts      = $this->accounts_model->get();
        $account_types = $this->accounts_model->get_account_type();

        // Group: type → group → sub_group → accounts[]
        $grouped = [];
        foreach ($accounts as $acc) {
            $type  = $acc['account_type']  ?: 'Uncategorized';
            $group = $acc['account_group'] ?: 'General';
            $sub   = $acc['account_sub_group'] ?: '';
            $grouped[$type][$group][$sub][] = $acc;
        }
        ksort($grouped);
        foreach ($grouped as $t => &$groups) {
            ksort($groups);
            foreach ($groups as $g => &$subs) {
                ksort($subs);
            }
        }
        unset($groups, $subs);

        $data['accounts']      = $accounts;
        $data['account_types'] = $account_types;
        $data['grouped']       = $grouped;
        $data['school_name']   = $this->setting_model->getCurrentSchoolName();

        $this->load->view('layout/header', $data);
        $this->load->view('account/all_accounts', $data);
        $this->load->view('layout/footer', $data);
    }
    public function create_account() {
        $this->form_validation->set_rules('account_number', 'Account Number', 'required');
        $this->form_validation->set_rules('account_name',   'Account Name',   'required');
        $this->form_validation->set_rules('account_type',   'Account Type',   'required');

        if ($this->form_validation->run() == FALSE) {
            $this->all_accounts();
        } else {
            $account_data = array(
                'account_number'   => $this->input->post('account_number'),
                'account_name'     => $this->input->post('account_name'),
                'account_type'     => $this->input->post('account_type'),
                'account_group'    => $this->input->post('account_group'),
                'account_sub_group'=> $this->input->post('account_sub_group'),
            );
            $this->accounts_model->add($account_data);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left"><i class="fa fa-check"></i> Account created successfully!</div>');
            redirect('accounts/all_accounts');
        }
    }
    public function delete_account($account_id) {

        $this->accounts_model->delete_account($account_id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Account deleted successfully!</div>');

        redirect('accounts/all_accounts');
    }

    public function get_account_json($id) {
        $account = $this->accounts_model->get((int)$id);
        if (!$account) {
            echo json_encode(['success' => false]);
            return;
        }
        echo json_encode([
            'success'          => true,
            'id'               => $account->id,
            'account_number'   => $account->account_number,
            'account_name'     => $account->account_name,
            'account_type'     => $account->account_type,
            'account_group'    => $account->account_group,
            'account_sub_group'=> $account->account_sub_group,
        ]);
    }

    public function update_account() {
        $id = (int)$this->input->post('id');
        if (!$id) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Invalid account.</div>');
            redirect('accounts/all_accounts');
            return;
        }
        $data = [
            'account_number'   => $this->input->post('account_number'),
            'account_name'     => $this->input->post('account_name'),
            'account_type'     => $this->input->post('account_type'),
            'account_group'    => $this->input->post('account_group'),
            'account_sub_group'=> $this->input->post('account_sub_group'),
        ];
        $this->accounts_model->update_account($id, $data);
        $this->session->set_flashdata('msg', '<div class="alert alert-success"><i class="fa fa-check"></i> Account updated successfully!</div>');
        redirect('accounts/all_accounts');
    }

    public function create_voucher()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/create_voucher');
        $user = $this->session->userdata('admin');
        $user_id=$user['id'];
        $voucher=$this->accounts_model->get_voucher($user_id);
        $accounts        = $this->accounts_model->get();
        $voucher_types        = $this->accounts_model->get_voucher_type();
        $voucher_number=$this->generate_random_string();

        // Ensure a draft voucher exists for this user for a smooth UX
        if (!$voucher) {
            $this->accounts_model->insert_voucher(array(
                'user_id' => $user_id,
                'voucher_date' => date('Y-m-d'),
                'voucher_number' => $voucher_number,
                'voucher_note' => '',
                'voucher_entry' => json_encode(array()),
            ));
            $voucher=$this->accounts_model->get_voucher($user_id);
        }

        $data['accounts'] = $accounts;
        $data['voucher'] = $voucher;
        $data['voucher_types'] = $voucher_types;
        $data['voucher_number'] = $voucher_number;
//        print_r($user_id);die();
//        print_r($voucher);die();
        $this->load->view('layout/header', $data);
        $this->load->view('account/create_voucher', $data);
        $this->load->view('layout/footer', $data);
    }

    public function store_voucher() {
        $this->form_validation->set_rules('voucher_date', 'Voucher Date', 'required');
        $this->form_validation->set_rules('voucher_number', 'Voucher Number', 'required');
        $this->form_validation->set_rules('voucher_narration', 'Voucher Narration', 'required');
//        $this->form_validation->set_rules('voucher_entry', 'Voucher Entry', 'required');
        $user = $this->session->userdata('admin');
        $user_id=$user['id'];
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> '.validation_errors().'</div>');
            redirect('accounts/create_voucher');
        } else {
            $voucher_entry[]=array(
                'voucher_type' => $this->input->post('voucher_type'),
                'debit_account_id' => $this->input->post('debit_account'),
                'debit_account_name' =>  $this->accounts_model->get($this->input->post('debit_account'))->account_name,
                'debit_account_number' =>  $this->accounts_model->get($this->input->post('debit_account'))->account_number,
                'debit_amount' => $this->input->post('debit_amount'),
                'credit_account_id' => $this->input->post('credit_account'),
                'credit_account_name' =>  $this->accounts_model->get($this->input->post('credit_account'))->account_name,
                'credit_account_number' =>  $this->accounts_model->get($this->input->post('credit_account'))->account_number,
                'credit_amount' => $this->input->post('credit_amount'),
                'voucher_narration' => $this->input->post('voucher_narration'),
                'description' => $this->input->post('description'),
            );
//            print_r($voucher_entry);die();
            $data = array(
                'user_id' => $user_id,
                'voucher_date' => $this->input->post('voucher_date'),
                'voucher_number' => $this->input->post('voucher_number'),
                'voucher_note' => '',
                'voucher_entry' => (json_encode($voucher_entry))
            );
            $voucher=$this->accounts_model->get_voucher($user_id);
            if($voucher->id){
                $old_entry=json_decode($voucher->voucher_entry);
                $new_entry=array_merge($old_entry,$voucher_entry);
//                print_r($new_entry);die();

                $data['voucher_entry']=json_encode($new_entry);
                $this->accounts_model->update_voucher($voucher->id, $data);
            }else{
                $this->accounts_model->insert_voucher($data);
            }
            $this->session->set_flashdata('msg', '<div class="alert alert-success"><i class="fa fa-check"></i> Entry added to draft.</div>');
            redirect('accounts/create_voucher');
        }
    }
    public function delete_entry($entry_index) {

        $user = $this->session->userdata('admin');
        $user_id=$user['id'];
        $voucher=$this->accounts_model->get_voucher($user_id);
        $records=json_decode($voucher->voucher_entry);

        unset($records[$entry_index]);
        $records = array_values($records);
//        print_r($records);die();
        $data['voucher_entry']=json_encode($records);
        $this->accounts_model->update_voucher($voucher->id, $data);
        redirect('accounts/create_voucher');
    }

    public function post_to_journal($id) {
        $user    = $this->session->userdata('admin');
        $user_id = $user['id'];
        $this->accounts_model->transfer_draft_voucher_to_journal($user_id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success"><i class="fa fa-check"></i> All draft entries posted to journal successfully.</div>');
        redirect('accounts/create_voucher');
    }

    public function post_selected_entries() {
        $user    = $this->session->userdata('admin');
        $user_id = $user['id'];
        $indices = $this->input->post('entry_indices');
        if (empty($indices)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning">No entries selected.</div>');
            redirect('accounts/create_voucher');
            return;
        }
        $indices = array_map('intval', $indices);
        $voucher = $this->accounts_model->get_voucher($user_id);
        if (!$voucher) {
            redirect('accounts/create_voucher');
            return;
        }
        $all_entries = json_decode($voucher->voucher_entry, true);
        if (!is_array($all_entries)) { $all_entries = []; }

        // Separate selected vs remaining
        $selected  = [];
        $remaining = [];
        foreach ($all_entries as $i => $entry) {
            if (in_array($i, $indices, true)) {
                $selected[] = $entry;
            } else {
                $remaining[] = $entry;
            }
        }
        if (empty($selected)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-warning">No matching entries found.</div>');
            redirect('accounts/create_voucher');
            return;
        }

        // Group selected entries by voucher_type and post
        $vouchers = [];
        foreach ($selected as $entry) {
            $vtype = $entry['voucher_type'] ?? 'JNL';
            $vouchers[$vtype][] = $entry;
        }
        $seq = 0;
        foreach ($vouchers as $type => $ventries) {
            $seq++;
            $vnum = $voucher->voucher_number.($seq > 1 ? '-'.$seq : '');
            $je_data = [
                'user_id'          => $user_id,
                'voucher_date'     => $voucher->voucher_date,
                'voucher_type'     => $type,
                'voucher_number'   => $vnum,
                'voucher_narration'=> $ventries[0]['voucher_narration'] ?? '',
            ];
            $this->db->insert('journal_entry', $je_data);
            $je_id = $this->db->insert_id();
            foreach ($ventries as $e) {
                $base = [
                    'user_id'              => $user_id,
                    'journal_entry_id'     => $je_id,
                    'voucher_type'         => $type,
                    'voucher_date'         => $voucher->voucher_date,
                    'description'          => $e['description'] ?? '',
                    'debit_account_id'     => 0, 'debit_account_name'   => '', 'debit_account_number' => '', 'debit_amount' => 0,
                    'credit_account_id'    => 0, 'credit_account_name'  => '', 'credit_account_number'=> '', 'credit_amount'=> 0,
                ];
                $dr = $base;
                $dr['debit_account_id']     = $e['debit_account_id'];
                $dr['debit_account_name']   = $e['debit_account_name'];
                $dr['debit_account_number'] = $e['debit_account_number'];
                $dr['debit_amount']         = $e['debit_amount'];
                $this->db->insert('journal_entry_detail', $dr);
                $cr = $base;
                $cr['credit_account_id']     = $e['credit_account_id'];
                $cr['credit_account_name']   = $e['credit_account_name'];
                $cr['credit_account_number'] = $e['credit_account_number'];
                $cr['credit_amount']         = $e['credit_amount'];
                $this->db->insert('journal_entry_detail', $cr);
            }
        }

        // Save remaining entries back to draft
        $upd = ['voucher_entry' => json_encode(array_values($remaining))];
        if (empty($remaining)) {
            // No entries left — delete draft
            $this->accounts_model->delete_voucher($voucher->id);
        } else {
            $this->accounts_model->update_voucher($voucher->id, $upd);
        }

        $cnt = count($selected);
        $this->session->set_flashdata('msg', '<div class="alert alert-success"><i class="fa fa-check"></i> '.$cnt.' entr'.($cnt===1?'y':'ies').' posted to journal successfully.</div>');
        redirect('accounts/create_voucher');
    }

    public function clear_draft() {
        $user    = $this->session->userdata('admin');
        $user_id = $user['id'];
        $voucher = $this->accounts_model->get_voucher($user_id);
        if ($voucher) {
            $this->accounts_model->delete_voucher($voucher->id);
        }
        $this->session->set_flashdata('msg', '<div class="alert alert-info">Draft cleared.</div>');
        redirect('accounts/create_voucher');
    }

    // Function to fetch journal entry details
    public function get_journal_entry_details($id) {
        $data['journal_entry'] = $this->accounts_model->get_journal_entry($id);
        $data['journal_entry_details'] = $this->accounts_model->get_journal_entry_details($id);
        $grouped_entries = [];
        foreach ($data['journal_entry_details'] as $entry) {
            $debit_account_id = $entry['debit_account_id'];
            if (!isset($grouped_entries[$debit_account_id])) {
                $grouped_entries[$debit_account_id] = [
                    'debit_account_id' => $entry['debit_account_id'],
                    'debit_account_name' => $entry['debit_account_name'],
                    'debit_account_number' => $entry['debit_account_number'],
                    'total_debit_amount' => 0, // Initialize total debit amount
                    'entries' => []
                ];
            }
            // Add the debit amount to the total
            $grouped_entries[$debit_account_id]['total_debit_amount'] += $entry['debit_amount'];
            $grouped_entries[$debit_account_id]['entries'][] = $entry;
        }

        // Pass grouped data to view
        $data['grouped_entries'] = $grouped_entries;
        echo json_encode($data);
    }

    // Function to display journal entries
    public function journal_entries() {

        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/journal_entries');
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        // Define static start and end dates
//        $start_date = '2024-01-01'; // Example start date
//        $end_date = '2024-12-31';   // Example end date
//        if($start_date && $end_date) {
        // Get the start and end dates from the request
        if (empty($start_date) && empty($end_date)) {
            // Get the current month's start and end dates
            $start_date = date('Y-m-01'); // First day of the current month
            $end_date = date('Y-m-t');   // Last day of the current month
        }
        $data['journal_entries'] = $this->accounts_model->get_journal_entries_range($start_date, $end_date);
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $this->load->view('layout/header', $data);
        $this->load->view('account/journal_entries_view', $data);
        $this->load->view('layout/footer', $data);
    }
    public function delete_journal_entry($id) {

        $this->accounts_model->delete_journal($id);
        redirect('accounts/journal_entries');
    }

    public function trial_balance()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/trial_balance');
        $user = $this->session->userdata('admin');
        $user_id=$user['id'];
        $accounts        = $this->accounts_model->get();

        // Get the start and end dates from the request
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        // Define static start and end dates
//        $start_date = '2024-01-01'; // Example start date
//        $end_date = '2024-12-31';   // Example end date
        if($start_date && $end_date) {
            // Fetch the opening balances for all accounts
            $opening_balances = $this->accounts_model->get_opening_balances($start_date);

            // Fetch the journal entries within the date range
            $journal_entries = $this->accounts_model->get_entries_by_date_range($start_date, $end_date);

            // Process the journal entries to create the trial balance
            $trial_balance = $this->process_trial_balance($accounts, $journal_entries, $opening_balances);

            // Pass the data to the view

        }else{
            $trial_balance = [];

        }
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['trial_balance'] = $trial_balance;
//        print_r($user_id);die();
//        print_r($voucher->voucher_date);die();
        $this->load->view('layout/header', $data);
        $this->load->view('account/trial_balance_view', $data);
        $this->load->view('layout/footer', $data);
    }
    public function trial_balance_new()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/trial_balance_new');

        $date_range  = $this->input->post('date_range') ?: 'this_month';
        $custom_from = $this->input->post('custom_from') ?: date('Y-m-01');
        $custom_to   = $this->input->post('custom_to')   ?: date('Y-m-t');
        $run         = (bool) $this->input->post('run_report');

        switch ($date_range) {
            case 'this_month':
                $start_date = date('Y-m-01');
                $end_date   = date('Y-m-t');
                break;
            case 'last_month':
                $start_date = date('Y-m-01', strtotime('first day of last month'));
                $end_date   = date('Y-m-t',  strtotime('last day of last month'));
                break;
            case 'this_quarter':
                $m = date('n');
                $q_start = ceil($m / 3) * 3 - 2;
                $q_end   = ceil($m / 3) * 3;
                $start_date = date('Y-' . str_pad($q_start, 2, '0', STR_PAD_LEFT) . '-01');
                $end_date   = date('Y-' . str_pad($q_end,   2, '0', STR_PAD_LEFT) . '-t');
                break;
            case 'this_year':
                $start_date = date('Y-01-01');
                $end_date   = date('Y-12-31');
                break;
            case 'last_year':
                $y = date('Y') - 1;
                $start_date = $y . '-01-01';
                $end_date   = $y . '-12-31';
                break;
            case 'custom':
                $start_date = $custom_from;
                $end_date   = $custom_to;
                break;
            default:
                $start_date = date('Y-m-01');
                $end_date   = date('Y-m-t');
        }

        if ($run) {
            $accounts        = $this->accounts_model->get();
            $opening_balances = $this->accounts_model->get_opening_balances($start_date);
            $journal_entries  = $this->accounts_model->get_entries_by_date_range($start_date, $end_date);
            $trial_balance    = $this->process_trial_balance($accounts, $journal_entries, $opening_balances);
        } else {
            $trial_balance = [];
        }

        $data['trial_balance'] = $trial_balance;
        $data['date_range']    = $date_range;
        $data['start_date']    = $start_date;
        $data['end_date']      = $end_date;
        $data['run']           = $run;
        $data['school_name']   = $this->setting_model->getCurrentSchoolName();

        $this->load->view('layout/header', $data);
        $this->load->view('account/trial_balance_new_view', $data);
        $this->load->view('layout/footer', $data);
    }

    public function report_by_group()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/report_by_group');

        $account_types_raw = $this->accounts_model->get_account_type();

        $start_date   = $this->input->post('start_date');
        $end_date     = $this->input->post('end_date');
        $account_type = $this->input->post('account_type'); // '' means All

        $trial_balance = [];

        if ($start_date && $end_date) {
            // Determine which types to process
            if ($account_type && $account_type !== 'all') {
                $types_to_run = [$account_type];
            } else {
                $types_to_run = array_column($account_types_raw, 'account_type');
            }

            foreach ($types_to_run as $type) {
                $account_ids = $this->accounts_model->get_account_ids_by_type($type);
                if (empty($account_ids)) continue;

                $accounts         = $this->accounts_model->get_accounts_by_ids($account_ids);
                $opening_balances = $this->accounts_model->get_opening_balances_acounts($account_ids, $start_date);
                $journal_entries  = $this->accounts_model->get_entries_by_date_range_acounts($account_ids, $start_date, $end_date);
                $rows             = $this->process_trial_balance($accounts, $journal_entries, $opening_balances);

                foreach ($rows as $row) {
                    $row['account_type_label'] = $type;
                    $trial_balance[] = $row;
                }
            }
        }

        $data['start_date']    = $start_date;
        $data['end_date']      = $end_date;
        $data['account_type']  = $account_type;
        $data['account_types'] = $account_types_raw;
        $data['trial_balance'] = $trial_balance;
        $data['school_name']   = $this->setting_model->getCurrentSchoolName();

        $this->load->view('layout/header', $data);
        $this->load->view('account/report_by_group', $data);
        $this->load->view('layout/footer', $data);
    }
    public function report_by_account()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/report_by_account');
        $user = $this->session->userdata('admin');
        $user_id=$user['id'];
        $all_accounts        = $this->accounts_model->get();

        // Get the start and end dates from the request
        $start_date = $this->input->post('start_date');
        $end_date = $this->input->post('end_date');
        $account_id = $this->input->post('account_id');
        // Define static start and end dates
//        $start_date = '2024-01-01'; // Example start date
//        $end_date = '2024-12-31';   // Example end date
//        print_r($account_id);die();

        if($start_date && $end_date && $account_id) {
            // Fetch the opening balances for all accounts
            $account_ids = [$account_id];
//            print_r($account_ids);die();
            $accounts        = $this->accounts_model->get_accounts_by_ids($account_ids);

            $opening_balances = $this->accounts_model->get_opening_balances_acounts($account_ids,$start_date);
//            print_r($opening_balances);
//            die();
            // Fetch the journal entries within the date range
            $journal_entries = $this->accounts_model->get_entries_by_date_range_acounts_details($account_ids,$start_date, $end_date);

            // Process the journal entries to create the trial balance
            $trial_balance = $this->process_trial_balance_details($accounts, $journal_entries, $opening_balances);
//            print_r('accounts=');
//            print_r($accounts);
//            echo('<br>');
//            print_r('opening_balances=');
//            print_r($opening_balances);
//            echo('<br>');
//
//            print_r('journal_entries=');
//            print_r($journal_entries);
//            print_r($trial_balance);
//            die();
            // Pass the data to the view

        }else{
            $trial_balance = [];

        }
        // Resolve selected account name for report header
        $selected_account_name = '';
        if ($account_id) {
            foreach ($all_accounts as $acc) {
                if ($acc['id'] == $account_id) {
                    $selected_account_name = $acc['account_name'] . ' (' . $acc['account_number'] . ')';
                    break;
                }
            }
        }

        $data['start_date']            = $start_date;
        $data['end_date']              = $end_date;
        $data['accounts']              = $all_accounts;
        $data['account_id']            = $account_id;
        $data['trial_balance']         = $trial_balance;
        $data['selected_account_name'] = $selected_account_name;
        $data['school_name']           = $this->setting_model->getCurrentSchoolName();

        $this->load->view('layout/header', $data);
        $this->load->view('account/report_by_account', $data);
        $this->load->view('layout/footer', $data);
    }

    public function balance_sheet(){
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/balance_sheet');

        // Resolve "As At" period from POST
        $as_at    = $this->input->post('as_at') ?: 'this_month';
        $custom_date = $this->input->post('custom_date') ?: date('Y-m-d');
        $detailed = (bool) $this->input->post('detailed');
        $run      = (bool) $this->input->post('run_report');

        // Calculate end date based on period selector
        switch ($as_at) {
            case 'this_month':
                $end_date = date('Y-m-t'); break;
            case 'last_month':
                $end_date = date('Y-m-t', strtotime('last day of last month')); break;
            case 'this_quarter':
                $month = date('n');
                $q_end = ceil($month / 3) * 3;
                $end_date = date('Y-' . str_pad($q_end, 2, '0', STR_PAD_LEFT) . '-t'); break;
            case 'this_year':
                $end_date = date('Y-12-31'); break;
            case 'last_year':
                $end_date = (date('Y') - 1) . '-12-31'; break;
            case 'custom':
                $end_date = $custom_date; break;
            default:
                $end_date = date('Y-m-t');
        }
        $start_date = '1900-01-01'; // Balance sheet is cumulative from beginning

        if ($run) {
            $assets      = $this->accounts_model->get_account_balances('Asset',     $start_date, $end_date);
            $liabilities = $this->accounts_model->get_account_balances('Liability', $start_date, $end_date);
            $equity      = $this->accounts_model->get_account_balances('Equity',    $start_date, $end_date);
        } else {
            $assets = $liabilities = $equity = [];
        }

        $data['assets']      = $assets;
        $data['liabilities'] = $liabilities;
        $data['equity']      = $equity;
        $data['as_at']       = $as_at;
        $data['end_date']    = $end_date;
        $data['detailed']    = $detailed;
        $data['run']         = $run;
        $data['school_name'] = $this->setting_model->getCurrentSchoolName();

        $this->load->view('layout/header', $data);
        $this->load->view('account/balance_sheet_view', $data);
        $this->load->view('layout/footer', $data);
    }
    public function print_chart_of_accounts()
    {
        $accounts = $this->accounts_model->get();

        $grouped = array();
        foreach ($accounts as $account) {
            $type  = $account['account_type']  ?: 'Uncategorized';
            $group = $account['account_group'] ?: 'General';
            $grouped[$type][$group][] = $account;
        }
        ksort($grouped);
        foreach ($grouped as $type => &$groups) {
            ksort($groups);
        }
        unset($groups);

        $data['grouped'] = $grouped;
        $this->load->view('account/print_chart_of_accounts', $data);
    }

    public function coa_mapping()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/coa_mapping');

        $this->load->model('Feetype_model');
        $this->load->model('Coa_mapping_model');

        $fee_types = $this->Feetype_model->get();
        $accounts  = $this->accounts_model->get();
        $mappings  = $this->Coa_mapping_model->get_mapping_map();

        $data['fee_types'] = $fee_types;
        $data['accounts']  = $accounts;
        $data['mappings']  = $mappings;

        $this->load->view('layout/header', $data);
        $this->load->view('account/coa_mapping', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save_coa_mapping()
    {
        $this->load->model('Coa_mapping_model');
        $mapping = $this->input->post('mapping');
        if (!is_array($mapping)) {
            $mapping = array();
        }
        $this->Coa_mapping_model->save_batch($mapping);
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">COA Mapping saved successfully!</div>');
        redirect('accounts/coa_mapping');
    }

    public function create_payable()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/create_payable');
        $user = $this->session->userdata('admin');
        $user_id = $user['id'];
        
        // Check if table exists
        if (!$this->db->table_exists('draft_payable_entry')) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left"><strong>Error:</strong> The database table "draft_payable_entry" does not exist. Please run the SQL commands from SQL_COMMANDS.sql file to create the required table.</div>');
            redirect('accounts/all_accounts');
            return;
        }
        
        // Get payable voucher for this user
        $payable = $this->accounts_model->get_payable($user_id);
        $accounts = $this->accounts_model->get();
        
        // Filter expense accounts automatically (smart filtering) for debit
        $expense_accounts = array();
        // Filter liability accounts for credit
        $liability_accounts = array();
        
        foreach ($accounts as $account) {
            $account_type = isset($account['account_type']) ? strtolower(trim($account['account_type'])) : '';
            $account_name = isset($account['account_name']) ? strtolower($account['account_name']) : '';
            
            // Include Expense type accounts for debit
            if ($account_type === 'expense' || stripos($account_type, 'expense') !== false) {
                $expense_accounts[] = $account;
            }
            // Also include accounts with expense-related names for debit
            elseif (stripos($account_name, 'expense') !== false || 
                    stripos($account_name, 'bill') !== false ||
                    stripos($account_name, 'rent') !== false ||
                    stripos($account_name, 'electricity') !== false ||
                    stripos($account_name, 'utility') !== false) {
                $expense_accounts[] = $account;
            }
            
            // Include Liability type accounts for credit
            if ($account_type === 'liability' || stripos($account_type, 'liability') !== false) {
                $liability_accounts[] = $account;
            }
            // Also include accounts with payable-related names for credit
            elseif (stripos($account_name, 'payable') !== false || 
                    stripos($account_name, 'liability') !== false ||
                    stripos($account_name, 'accounts payable') !== false) {
                $liability_accounts[] = $account;
            }
        }
        
        $voucher_number = $this->generate_random_string();
        
        // Ensure a draft payable exists for this user
        if (!$payable) {
            $insert_result = $this->accounts_model->insert_payable(array(
                'user_id' => $user_id,
                'payable_date' => date('Y-m-d'),
                'payable_number' => $voucher_number,
                'payable_narration' => '',
                'payable_entry' => json_encode(array()),
            ));
            
            if ($insert_result) {
                $payable = $this->accounts_model->get_payable($user_id);
            } else {
                // If insert failed, create a dummy object for the view
                $payable = (object)array(
                    'id' => null,
                    'user_id' => $user_id,
                    'payable_date' => date('Y-m-d'),
                    'payable_number' => $voucher_number,
                    'payable_narration' => '',
                    'payable_entry' => json_encode(array()),
                );
            }
        }
        
        $data['expense_accounts'] = $expense_accounts;
        $data['liability_accounts'] = $liability_accounts;
        $data['all_accounts'] = $accounts; // For credit/debit selection during post
        $data['payable'] = $payable;
        $data['payable_number'] = $voucher_number;
        
        $this->load->view('layout/header', $data);
        $this->load->view('account/create_payable', $data);
        $this->load->view('layout/footer', $data);
    }

    public function store_payable()
    {
        $this->form_validation->set_rules('payable_date', 'Payable Date', 'required');
        $this->form_validation->set_rules('payable_narration', 'Narration', 'required');
        
        $user = $this->session->userdata('admin');
        $user_id = $user['id'];
        
        if ($this->form_validation->run() == FALSE) {
            redirect('accounts/create_payable');
        } else {
            // Get entries from form
            $descriptions = $this->input->post('description');
            $amounts = $this->input->post('amount');
            $debit_account_ids = $this->input->post('debit_account_id');
            $credit_account_ids = $this->input->post('credit_account_id');
            $selected = $this->input->post('selected'); // Checkboxes - can be array with numeric keys
            
            $payable_entries = array();
            
            // Store ALL entries (both selected and unselected) so they persist
            if (is_array($descriptions) && is_array($amounts)) {
                foreach ($descriptions as $index => $description) {
                    // Only save entries with valid description and amount
                    if (!empty(trim($description)) && isset($amounts[$index]) && !empty($amounts[$index]) && floatval($amounts[$index]) > 0) {
                        // Check if this entry is selected (checkbox might use index as key)
                        $is_selected = false;
                        if (is_array($selected)) {
                            // Check both numeric index and string index
                            if (isset($selected[$index]) && $selected[$index] == '1') {
                                $is_selected = true;
                            } elseif (isset($selected[(string)$index]) && $selected[(string)$index] == '1') {
                                $is_selected = true;
                            }
                        }
                        
                        $payable_entries[] = array(
                            'description' => trim($description),
                            'amount' => floatval($amounts[$index]),
                            'debit_account_id' => isset($debit_account_ids[$index]) ? intval($debit_account_ids[$index]) : null,
                            'credit_account_id' => isset($credit_account_ids[$index]) ? intval($credit_account_ids[$index]) : null,
                            'selected' => $is_selected
                        );
                    }
                }
            }
            
            // Convert month input (YYYY-MM) to date (first day of month)
            $month_input = $this->input->post('payable_date');
            $payable_date = $month_input . '-01'; // Add day 01 to make it a valid date
            
            // Ensure we have at least an empty array if no entries
            if (empty($payable_entries)) {
                $payable_entries = array();
            }
            
            $data = array(
                'user_id' => $user_id,
                'payable_date' => $payable_date,
                'payable_number' => $this->input->post('payable_number'),
                'payable_narration' => $this->input->post('payable_narration'),
                'payable_entry' => json_encode($payable_entries),
                'updated_at' => date('Y-m-d H:i:s')
            );
            
            $payable = $this->accounts_model->get_payable($user_id);
            if ($payable && isset($payable->id)) {
                $result = $this->accounts_model->update_payable($payable->id, $data);
            } else {
                $result = $this->accounts_model->insert_payable($data);
            }
            
            if ($result) {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Payable voucher saved successfully! ' . count($payable_entries) . ' entries saved.</div>');
            } else {
                $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">Payable voucher saved, but there may have been an issue. Please check your entries.</div>');
            }
            
            redirect('accounts/create_payable');
        }
    }

    public function post_payable()
    {
        $user = $this->session->userdata('admin');
        $user_id = $user['id'];
        
        // Get entries from POST (each entry has its own debit and credit accounts)
        $entries = $this->input->post('entries');
        
        if (empty($entries) || !is_array($entries)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">No entries selected to post!</div>');
            redirect('accounts/create_payable');
        }
        
        // Validate all entries have required accounts
        foreach ($entries as $index => $entry) {
            if (empty($entry['debit_account_id'])) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Please select a debit account for all selected entries!</div>');
                redirect('accounts/create_payable');
            }
            if (empty($entry['credit_account_id'])) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Please select a credit account for all selected entries!</div>');
                redirect('accounts/create_payable');
            }
        }
        
        // Transfer payable entries to journal (each entry with its own accounts)
        $result = $this->accounts_model->transfer_payable_to_journal($user_id, $entries);
        
        if ($result) {
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Payable voucher posted to journal successfully! ' . count($entries) . ' entries posted.</div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Error posting payable voucher to journal. Please try again.</div>');
        }
        
        redirect('accounts/create_payable');
    }
    private function process_trial_balance($accounts, $journal_entries, $opening_balances) {
        /*
         * Accumulate debit and credit totals per account correctly for both entry styles:
         *   Old-style: debit_account_id != credit_account_id, both amounts > 0 on same row.
         *   New-style: debit_account_id = credit_account_id, only one amount > 0 per row.
         * We aggregate the debit side and credit side separately.
         */
        $totals = []; // [account_id => ['dr' => float, 'cr' => float]]
        foreach ($journal_entries as $entry) {
            $dr_id = $entry['debit_account_id'];
            $cr_id = $entry['credit_account_id'];
            $dr    = (float)$entry['debit_amount'];
            $cr    = (float)$entry['credit_amount'];

            if ($dr > 0) {
                if (!isset($totals[$dr_id])) $totals[$dr_id] = ['dr' => 0.0, 'cr' => 0.0];
                $totals[$dr_id]['dr'] += $dr;
            }
            if ($cr > 0) {
                if (!isset($totals[$cr_id])) $totals[$cr_id] = ['dr' => 0.0, 'cr' => 0.0];
                $totals[$cr_id]['cr'] += $cr;
            }
        }

        foreach ($accounts as &$account) {
            $id  = $account['id'];
            $op  = isset($opening_balances[$id]) ? (float)$opening_balances[$id] : 0.0;
            $dr  = isset($totals[$id]) ? $totals[$id]['dr'] : 0.0;
            $cr  = isset($totals[$id]) ? $totals[$id]['cr'] : 0.0;
            $cls = $op + $dr - $cr;

            $account['opening_balance'] = $op;
            $account['debit_balance']   = $dr;
            $account['credit_balance']  = $cr;
            $account['closing_balance'] = $cls;
        }
        unset($account);

        return $accounts;
    }
    private function process_trial_balance_details($accounts, $ledger_rows, $opening_balances) {
        // Build account map
        $accounts_map = [];
        foreach ($accounts as $account) {
            $accounts_map[$account['id']] = $account;
        }

        // Running balance per account (starts at opening)
        $running = [];
        foreach ($accounts_map as $id => $acc) {
            $running[$id] = isset($opening_balances[$id]) ? (float)$opening_balances[$id] : 0.0;
        }

        $result = [];
        foreach ($ledger_rows as $row) {
            $account_id = (int)$row['account_id'];
            if (!isset($accounts_map[$account_id])) continue;

            $account  = $accounts_map[$account_id];
            $opening  = $running[$account_id];
            $is_debit = ($row['side'] === 'Dr');
            $amount   = (float)$row['amount'];

            $debit_amt  = $is_debit ? $amount : 0.0;
            $credit_amt = $is_debit ? 0.0     : $amount;
            $closing    = $opening + $debit_amt - $credit_amt;

            $result[] = [
                'id'              => $account['id'],
                'account_number'  => $account['account_number'],
                'account_name'    => $account['account_name'],
                'voucher_date'    => $row['voucher_date'],
                'voucher_number'  => $row['voucher_number'],
                'voucher_narration'=> $row['voucher_narration'],
                'description'     => $row['description'],
                'account_type'    => $account['account_type'],
                'opening_balance' => $opening,
                'debit_balance'   => $debit_amt,
                'credit_balance'  => $credit_amt,
                'closing_balance' => $closing,
            ];

            $running[$account_id] = $closing;
        }

        return $result;
    }
    private function format_balance($amount) {
        if ($amount >= 0) {
            return number_format($amount, 2) . ' Dr';
        } else {
            return number_format(abs($amount), 2) . ' Cr';
        }
    }
    function generate_random_string() {
        $part1 = strtoupper(substr(md5(rand()), 0, 4));
        $part2 = strtoupper(substr(md5(rand()), 0, 6));
        $part3 = strtoupper(substr(md5(rand()), 0, 3));

        return $part1 . '-' . $part2 . '-' . $part3;
    }

    // ── Journal New ──────────────────────────────────────────────────────────

    public function journal_new_list()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/journal_new_list');

        $data['accounts'] = $this->accounts_model->get();

        $filters = array(
            'account_id' => $this->input->get('account_id'),
            'ref_no'     => $this->input->get('ref_no'),
            'start_date' => $this->input->get('start_date'),
            'end_date'   => $this->input->get('end_date'),
        );
        $data['filters']         = $filters;
        $data['journal_entries'] = $this->accounts_model->get_journal_entries_with_totals($filters);

        $this->load->view('layout/header', $data);
        $this->load->view('account/journal_new_list_view', $data);
        $this->load->view('layout/footer', $data);
    }

    public function journal_new()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/journal_new_list');

        $data['accounts'] = $this->accounts_model->get();

        $this->load->view('layout/header', $data);
        $this->load->view('account/journal_new_view', $data);
        $this->load->view('layout/footer', $data);
    }

    public function fee_collection_report()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/fee_collection_report');

        $this->load->model('Feetype_model');

        $filters = [
            'start_date'   => $this->input->get('start_date') ?: date('Y-m-01'),
            'end_date'     => $this->input->get('end_date')   ?: date('Y-m-d'),
            'class_id'     => $this->input->get('class_id'),
            'fee_type_id'  => $this->input->get('fee_type_id'),
            'admission_no' => $this->input->get('admission_no'),
        ];

        $data['rows']       = $this->accounts_model->get_fee_collection_report($filters);
        $data['classes']    = $this->db->order_by('class')->get('classes')->result_array();
        $data['fee_types']  = $this->Feetype_model->get();
        $data['filters']    = $filters;
        $data['school_name']= $this->setting_model->getCurrentSchoolName();

        $this->load->view('layout/header', $data);
        $this->load->view('account/fee_collection_report', $data);
        $this->load->view('layout/footer', $data);
    }

    public function account_balances()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/account_balances');

        $as_of = $this->input->get('as_of') ?: date('Y-m-d');

        $data['as_of']       = $as_of;
        $data['balances']    = $this->accounts_model->get_all_account_balances($as_of);
        $data['school_name'] = $this->setting_model->getCurrentSchoolName();

        $this->load->view('layout/header', $data);
        $this->load->view('account/account_balances_view', $data);
        $this->load->view('layout/footer', $data);
    }

    public function delete_journal_new($id)
    {
        $this->accounts_model->delete_journal((int)$id);
        $this->db->where('journal_entry_id', (int)$id)->delete('journal_entry_detail');
        $this->session->set_flashdata('msg', '<div class="alert alert-success">Journal deleted successfully.</div>');
        redirect('accounts/journal_new_list');
    }

    public function save_journal_new()
    {
        $user     = $this->session->userdata('admin');
        $user_id  = $user['id'];

        $jn_date  = $this->input->post('jn_date');
        $jn_ref   = $this->input->post('jn_ref');
        $lines    = $this->input->post('lines');   // array of {account_id, description, debit, credit}

        if (empty($jn_date) || empty($lines)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Invalid journal data.</div>');
            redirect('accounts/journal_new');
            return;
        }

        // Filter to lines that have an account selected
        $valid_lines = array();
        foreach ($lines as $line) {
            if (!empty($line['account_id'])) {
                $valid_lines[] = $line;
            }
        }

        if (empty($valid_lines)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Please add at least one journal line.</div>');
            redirect('accounts/journal_new');
            return;
        }

        // Verify debit == credit
        $total_debit  = array_sum(array_column($valid_lines, 'debit'));
        $total_credit = array_sum(array_column($valid_lines, 'credit'));
        if (abs($total_debit - $total_credit) > 0.001) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Debit and Credit totals do not match.</div>');
            redirect('accounts/journal_new');
            return;
        }

        // Insert journal_entry header
        $voucher_number = $this->generate_random_string();
        $journal_data   = array(
            'user_id'          => $user_id,
            'voucher_date'     => $jn_date,
            'voucher_type'     => 'JNL',
            'voucher_number'   => (!empty($jn_ref) ? $jn_ref : $voucher_number),
            'voucher_narration'=> (!empty($jn_ref) ? $jn_ref : $voucher_number),
        );
        $this->accounts_model->insert_journal($journal_data);
        $journal_entry_id = $this->db->insert_id();

        // Insert journal_entry_detail rows
        // Each line carries its own debit or credit; paired lines balance the entry.
        foreach ($valid_lines as $line) {
            $account = $this->accounts_model->get((int)$line['account_id']);
            if (!$account) continue;

            $detail = array(
                'user_id'              => $user_id,
                'journal_entry_id'     => $journal_entry_id,
                'voucher_type'         => 'JNL',
                'voucher_date'         => $jn_date,
                'debit_account_id'     => $line['account_id'],
                'debit_account_name'   => $account->account_name,
                'debit_account_number' => $account->account_number,
                'debit_amount'         => (float)$line['debit'],
                'credit_account_id'    => $line['account_id'],
                'credit_account_name'  => $account->account_name,
                'credit_account_number'=> $account->account_number,
                'credit_amount'        => (float)$line['credit'],
                'description'          => isset($line['description']) ? $line['description'] : '',
            );
            $this->db->insert('journal_entry_detail', $detail);
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Journal saved successfully.</div>');
        redirect('accounts/journal_entries');
    }

    // ── Bank Payments ─────────────────────────────────────────────────────────

    public function bank_payments()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/bank_payments');

        $filters = [
            'bank_id'    => $this->input->get('bank_id'),
            'nominal_id' => $this->input->get('nominal_id'),
            'start_date' => $this->input->get('start_date'),
            'end_date'   => $this->input->get('end_date'),
            'ref_no'     => $this->input->get('ref_no'),
            'details'    => $this->input->get('details'),
        ];

        $data['bank_accounts']    = $this->accounts_model->get_bank_cash_accounts();
        $data['nominal_accounts'] = $this->accounts_model->get();
        $data['payments']         = $this->accounts_model->get_bank_payments($filters);
        $data['filters']          = $filters;
        $data['school_name']      = $this->setting_model->getCurrentSchoolName();

        $this->load->view('layout/header', $data);
        $this->load->view('account/bank_payments_list', $data);
        $this->load->view('layout/footer', $data);
    }

    public function add_bank_payment()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/bank_payments');

        $data['bank_accounts']    = $this->accounts_model->get_bank_cash_accounts();
        $data['nominal_accounts'] = $this->accounts_model->get();

        $this->load->view('layout/header', $data);
        $this->load->view('account/bank_payment_add', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save_bank_payment()
    {
        $user    = $this->session->userdata('admin');
        $user_id = $user['id'];

        $bank_id = (int)$this->input->post('bank_id');
        $lines   = $this->input->post('lines');

        if (!$bank_id) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Please select a Bank/Cash account.</div>');
            redirect('accounts/add_bank_payment');
            return;
        }

        $bank_account = $this->accounts_model->get($bank_id);
        if (!$bank_account) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Invalid bank account.</div>');
            redirect('accounts/add_bank_payment');
            return;
        }

        $valid_lines = [];
        if (is_array($lines)) {
            foreach ($lines as $line) {
                if (!empty($line['nominal_id']) && (float)$line['amount'] > 0) {
                    $valid_lines[] = $line;
                }
            }
        }

        if (empty($valid_lines)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Please add at least one payment line.</div>');
            redirect('accounts/add_bank_payment');
            return;
        }

        foreach ($valid_lines as $line) {
            $nominal = $this->accounts_model->get((int)$line['nominal_id']);
            if (!$nominal) continue;

            $voucher_number = $this->generate_random_string();
            $ref_no = !empty($line['ref_no']) ? $line['ref_no'] : $voucher_number;

            // Insert journal_entry header per line
            $je_data = [
                'user_id'          => $user_id,
                'voucher_date'     => $line['date'],
                'voucher_type'     => 'BPV',
                'voucher_number'   => $ref_no,
                'voucher_narration'=> !empty($line['details']) ? $line['details'] : $ref_no,
            ];
            $this->accounts_model->insert_journal($je_data);
            $je_id = $this->db->insert_id();

            // Accounting entry for Bank Payment:
            // Debit: Nominal Account (expense/asset increases)
            // Credit: Bank/Cash Account (bank decreases)
            $detail = [
                'user_id'              => $user_id,
                'journal_entry_id'     => $je_id,
                'voucher_type'         => 'BPV',
                'voucher_date'         => $line['date'],
                'debit_account_id'     => $nominal->id,
                'debit_account_name'   => $nominal->account_name,
                'debit_account_number' => $nominal->account_number,
                'debit_amount'         => (float)$line['amount'],
                'credit_account_id'    => $bank_account->id,
                'credit_account_name'  => $bank_account->account_name,
                'credit_account_number'=> $bank_account->account_number,
                'credit_amount'        => (float)$line['amount'],
                'description'          => !empty($line['details']) ? $line['details'] : '',
            ];
            $this->db->insert('journal_entry_detail', $detail);
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-success"><i class="fa fa-check"></i> Bank payment(s) saved successfully.</div>');
        redirect('accounts/bank_payments');
    }

    public function delete_bank_payment($je_id)
    {
        $je_id = (int)$je_id;
        // Only delete BPV type entries
        $je = $this->db->where('id', $je_id)->where('voucher_type', 'BPV')->get('journal_entry')->row();
        if ($je) {
            $this->db->where('journal_entry_id', $je_id)->delete('journal_entry_detail');
            $this->db->where('id', $je_id)->delete('journal_entry');
            $this->session->set_flashdata('msg', '<div class="alert alert-success">Payment deleted successfully.</div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Payment not found.</div>');
        }
        redirect('accounts/bank_payments');
    }

    public function get_bank_payment_json($je_id)
    {
        $je_id = (int)$je_id;
        $je  = $this->db->where('id', $je_id)->where('voucher_type', 'BPV')->get('journal_entry')->row_array();
        $det = $this->db->where('journal_entry_id', $je_id)->get('journal_entry_detail')->row_array();
        if (!$je || !$det) {
            echo json_encode(['success' => false]);
            return;
        }
        echo json_encode([
            'success'         => true,
            'je_id'           => $je['id'],
            'voucher_number'  => $je['voucher_number'],
            'voucher_date'    => $je['voucher_date'],
            'voucher_narration'=> $je['voucher_narration'],
            'bank_id'         => $det['credit_account_id'],
            'bank_name'       => $det['credit_account_name'],
            'nominal_id'      => $det['debit_account_id'],
            'nominal_name'    => $det['debit_account_name'],
            'amount'          => $det['debit_amount'],
            'details'         => $det['description'],
        ]);
    }

    // ── Bank Receipts ─────────────────────────────────────────────────────────

    public function bank_receipts()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/bank_receipts');

        $filters = [
            'bank_id'    => $this->input->get('bank_id'),
            'nominal_id' => $this->input->get('nominal_id'),
            'start_date' => $this->input->get('start_date'),
            'end_date'   => $this->input->get('end_date'),
            'ref_no'     => $this->input->get('ref_no'),
            'details'    => $this->input->get('details'),
        ];

        $data['bank_accounts']    = $this->accounts_model->get_bank_cash_accounts();
        $data['nominal_accounts'] = $this->accounts_model->get();
        $data['receipts']         = $this->accounts_model->get_bank_receipts($filters);
        $data['filters']          = $filters;
        $data['school_name']      = $this->setting_model->getCurrentSchoolName();

        $this->load->view('layout/header', $data);
        $this->load->view('account/bank_receipts_list', $data);
        $this->load->view('layout/footer', $data);
    }

    public function add_bank_receipt()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/bank_receipts');

        $data['bank_accounts']    = $this->accounts_model->get_bank_cash_accounts();
        $data['nominal_accounts'] = $this->accounts_model->get();

        $this->load->view('layout/header', $data);
        $this->load->view('account/bank_receipt_add', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save_bank_receipt()
    {
        $user    = $this->session->userdata('admin');
        $user_id = $user['id'];

        $bank_id = (int)$this->input->post('bank_id');
        $lines   = $this->input->post('lines');

        if (!$bank_id) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Please select a Bank/Cash account.</div>');
            redirect('accounts/add_bank_receipt');
            return;
        }

        $bank_account = $this->accounts_model->get($bank_id);
        if (!$bank_account) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Invalid bank account.</div>');
            redirect('accounts/add_bank_receipt');
            return;
        }

        $valid_lines = [];
        if (is_array($lines)) {
            foreach ($lines as $line) {
                if (!empty($line['nominal_id']) && (float)$line['amount'] > 0) {
                    $valid_lines[] = $line;
                }
            }
        }

        if (empty($valid_lines)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Please add at least one receipt line.</div>');
            redirect('accounts/add_bank_receipt');
            return;
        }

        foreach ($valid_lines as $line) {
            $nominal = $this->accounts_model->get((int)$line['nominal_id']);
            if (!$nominal) continue;

            $voucher_number = $this->generate_random_string();
            $ref_no = !empty($line['ref_no']) ? $line['ref_no'] : $voucher_number;

            $je_data = [
                'user_id'          => $user_id,
                'voucher_date'     => $line['date'],
                'voucher_type'     => 'BRV',
                'voucher_number'   => $ref_no,
                'voucher_narration'=> !empty($line['details']) ? $line['details'] : $ref_no,
            ];
            $this->accounts_model->insert_journal($je_data);
            $je_id = $this->db->insert_id();

            // Accounting entry for Bank Receipt:
            // Debit: Bank/Cash Account (funds received into bank)
            // Credit: Nominal Account (income/liability increases)
            $detail = [
                'user_id'              => $user_id,
                'journal_entry_id'     => $je_id,
                'voucher_type'         => 'BRV',
                'voucher_date'         => $line['date'],
                'debit_account_id'     => $bank_account->id,
                'debit_account_name'   => $bank_account->account_name,
                'debit_account_number' => $bank_account->account_number,
                'debit_amount'         => (float)$line['amount'],
                'credit_account_id'    => $nominal->id,
                'credit_account_name'  => $nominal->account_name,
                'credit_account_number'=> $nominal->account_number,
                'credit_amount'        => (float)$line['amount'],
                'description'          => !empty($line['details']) ? $line['details'] : '',
            ];
            $this->db->insert('journal_entry_detail', $detail);
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-success"><i class="fa fa-check"></i> Bank receipt(s) saved successfully.</div>');
        redirect('accounts/bank_receipts');
    }

    public function delete_bank_receipt($je_id)
    {
        $je_id = (int)$je_id;
        $je = $this->db->where('id', $je_id)->where('voucher_type', 'BRV')->get('journal_entry')->row();
        if ($je) {
            $this->db->where('journal_entry_id', $je_id)->delete('journal_entry_detail');
            $this->db->where('id', $je_id)->delete('journal_entry');
            $this->session->set_flashdata('msg', '<div class="alert alert-success">Receipt deleted successfully.</div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Receipt not found.</div>');
        }
        redirect('accounts/bank_receipts');
    }

    public function get_bank_receipt_json($je_id)
    {
        $je_id = (int)$je_id;
        $je  = $this->db->where('id', $je_id)->where('voucher_type', 'BRV')->get('journal_entry')->row_array();
        $det = $this->db->where('journal_entry_id', $je_id)->get('journal_entry_detail')->row_array();
        if (!$je || !$det) {
            echo json_encode(['success' => false]);
            return;
        }
        echo json_encode([
            'success'          => true,
            'je_id'            => $je['id'],
            'voucher_number'   => $je['voucher_number'],
            'voucher_date'     => $je['voucher_date'],
            'voucher_narration'=> $je['voucher_narration'],
            'bank_id'          => $det['debit_account_id'],
            'bank_name'        => $det['debit_account_name'],
            'nominal_id'       => $det['credit_account_id'],
            'nominal_name'     => $det['credit_account_name'],
            'amount'           => $det['debit_amount'],
            'details'          => $det['description'],
        ]);
    }

    // ── Bank Transfers ────────────────────────────────────────────────────────

    public function bank_transfers()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/bank_transfers');

        $filters = [
            'from_bank_id' => $this->input->get('from_bank_id'),
            'to_bank_id'   => $this->input->get('to_bank_id'),
            'start_date'   => $this->input->get('start_date'),
            'end_date'     => $this->input->get('end_date'),
            'ref_no'       => $this->input->get('ref_no'),
            'details'      => $this->input->get('details'),
        ];

        $data['bank_accounts'] = $this->accounts_model->get_bank_cash_accounts();
        $data['transfers']     = $this->accounts_model->get_bank_transfers($filters);
        $data['filters']       = $filters;
        $data['school_name']   = $this->setting_model->getCurrentSchoolName();

        $this->load->view('layout/header', $data);
        $this->load->view('account/bank_transfers_list', $data);
        $this->load->view('layout/footer', $data);
    }

    public function add_bank_transfer()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/bank_transfers');

        $data['bank_accounts'] = $this->accounts_model->get_bank_cash_accounts();

        $this->load->view('layout/header', $data);
        $this->load->view('account/bank_transfer_add', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save_bank_transfer()
    {
        $user    = $this->session->userdata('admin');
        $user_id = $user['id'];
        $lines   = $this->input->post('lines');

        $valid_lines = [];
        if (is_array($lines)) {
            foreach ($lines as $line) {
                if (!empty($line['from_bank_id']) && !empty($line['to_bank_id']) && (float)$line['amount'] > 0) {
                    if ($line['from_bank_id'] == $line['to_bank_id']) continue; // skip same-account
                    $valid_lines[] = $line;
                }
            }
        }

        if (empty($valid_lines)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Please add at least one valid transfer line (From Bank and To Bank must differ).</div>');
            redirect('accounts/add_bank_transfer');
            return;
        }

        foreach ($valid_lines as $line) {
            $from_account = $this->accounts_model->get((int)$line['from_bank_id']);
            $to_account   = $this->accounts_model->get((int)$line['to_bank_id']);
            if (!$from_account || !$to_account) continue;

            $voucher_number = $this->generate_random_string();
            $ref_no  = !empty($line['ref_no'])  ? $line['ref_no']  : $voucher_number;
            $details = !empty($line['details']) ? $line['details'] : $ref_no;

            $je_data = [
                'user_id'          => $user_id,
                'voucher_date'     => $line['date'],
                'voucher_type'     => 'TRF',
                'voucher_number'   => $ref_no,
                'voucher_narration'=> $details,
            ];
            $this->accounts_model->insert_journal($je_data);
            $je_id = $this->db->insert_id();

            // Accounting entry for Bank Transfer:
            // Debit:  To Bank   (receiving account increases)
            // Credit: From Bank (sending account decreases)
            $detail = [
                'user_id'              => $user_id,
                'journal_entry_id'     => $je_id,
                'voucher_type'         => 'TRF',
                'voucher_date'         => $line['date'],
                'debit_account_id'     => $to_account->id,
                'debit_account_name'   => $to_account->account_name,
                'debit_account_number' => $to_account->account_number,
                'debit_amount'         => (float)$line['amount'],
                'credit_account_id'    => $from_account->id,
                'credit_account_name'  => $from_account->account_name,
                'credit_account_number'=> $from_account->account_number,
                'credit_amount'        => (float)$line['amount'],
                'description'          => $details,
            ];
            $this->db->insert('journal_entry_detail', $detail);
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-success"><i class="fa fa-check"></i> Transfer(s) saved successfully.</div>');
        redirect('accounts/bank_transfers');
    }

    public function delete_bank_transfer($je_id)
    {
        $je_id = (int)$je_id;
        $je = $this->db->where('id', $je_id)->where('voucher_type', 'TRF')->get('journal_entry')->row();
        if ($je) {
            $this->db->where('journal_entry_id', $je_id)->delete('journal_entry_detail');
            $this->db->where('id', $je_id)->delete('journal_entry');
            $this->session->set_flashdata('msg', '<div class="alert alert-success">Transfer deleted successfully.</div>');
        } else {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Transfer not found.</div>');
        }
        redirect('accounts/bank_transfers');
    }

    public function get_bank_transfer_json($je_id)
    {
        $je_id = (int)$je_id;
        $je  = $this->db->where('id', $je_id)->where('voucher_type', 'TRF')->get('journal_entry')->row_array();
        $det = $this->db->where('journal_entry_id', $je_id)->get('journal_entry_detail')->row_array();
        if (!$je || !$det) {
            echo json_encode(['success' => false]);
            return;
        }
        echo json_encode([
            'success'          => true,
            'je_id'            => $je['id'],
            'voucher_number'   => $je['voucher_number'],
            'voucher_date'     => $je['voucher_date'],
            'from_bank_id'     => $det['credit_account_id'],
            'from_bank_name'   => $det['credit_account_name'],
            'to_bank_id'       => $det['debit_account_id'],
            'to_bank_name'     => $det['debit_account_name'],
            'amount'           => $det['debit_amount'],
            'details'          => $det['description'],
        ]);
    }

    // ── Reconciliation ────────────────────────────────────────────────────────

    public function reconciliation()
    {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/reconciliation');

        $data['bank_accounts'] = $this->accounts_model->get_bank_cash_accounts();
        $data['school_name']   = $this->setting_model->getCurrentSchoolName();

        $this->load->view('layout/header', $data);
        $this->load->view('account/reconciliation_view', $data);
        $this->load->view('layout/footer', $data);
    }

    public function reconciliation_load_transactions()
    {
        $bank_id        = (int)$this->input->post('bank_id');
        $statement_date = $this->input->post('statement_date');

        if (!$bank_id || !$statement_date) {
            echo json_encode(['success' => false, 'message' => 'Bank and statement date required.']);
            return;
        }

        // All transactions for this bank account up to statement date
        $transactions = $this->accounts_model->get_reconciliation_transactions($bank_id, $statement_date);

        // Opening balance: all movements BEFORE the earliest transaction date loaded
        // (net balance up to statement_date for this bank account — already reconciled items keep running balance)
        $opening = $this->accounts_model->get_reconciliation_opening_balance($bank_id, $statement_date);

        echo json_encode([
            'success'         => true,
            'transactions'    => $transactions,
            'opening_balance' => $opening,
            'csrf_hash'       => $this->security->get_csrf_hash(),
        ]);
    }

    public function reconciliation_save()
    {
        $bank_id          = (int)$this->input->post('bank_id');
        $statement_date   = $this->input->post('statement_date');
        $statement_balance= (float)$this->input->post('statement_balance');
        $reconciled_ids   = $this->input->post('reconciled_ids'); // comma-separated je_ids

        if (!$bank_id || !$statement_date) {
            echo json_encode(['success' => false, 'message' => 'Bank and statement date required.']);
            return;
        }

        $ids = [];
        if (!empty($reconciled_ids)) {
            foreach (explode(',', $reconciled_ids) as $id) {
                $id = (int)trim($id);
                if ($id > 0) $ids[] = $id;
            }
        }

        // Mark selected journal_entry_detail rows as reconciled for this bank
        $this->accounts_model->save_reconciliation($bank_id, $statement_date, $statement_balance, $ids);

        echo json_encode([
            'success'   => true,
            'message'   => 'Reconciliation saved successfully.',
            'csrf_hash' => $this->security->get_csrf_hash(),
        ]);
    }

    // ── Opening Balances ────────────────────────────────────────────────────

    private function _is_ob_admin() {
        $user = $this->session->userdata('admin');
        if (empty($user)) return false;
        // roles is stored as ['Role Name' => role_id]
        $admin_roles = ['Admin', 'Super Admin', 'admin'];
        if (!empty($user['roles']) && is_array($user['roles'])) {
            foreach (array_keys($user['roles']) as $rname) {
                if (in_array(trim($rname), $admin_roles, true)) return true;
            }
        }
        // fallback: old-style role key
        if (!empty($user['role']) && in_array(trim($user['role']), $admin_roles, true)) return true;
        return false;
    }

    private function _ob_admin_only() {
        if (!$this->_is_ob_admin()) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger"><i class="fa fa-ban"></i> Access denied. Only administrators can manage opening balances.</div>');
            redirect('accounts/opening_balance');
            exit;
        }
    }

    public function opening_balance() {
        $this->session->set_userdata('top_menu', 'ChatOfAccount');
        $this->session->set_userdata('sub_menu', 'accounts/opening_balance');

        $accounts      = $this->accounts_model->get();
        $account_types = $this->accounts_model->get_account_type();
        $balances_raw  = $this->accounts_model->get_opening_balances_list();

        // Index balances by account_id for quick lookup
        $balances = [];
        foreach ($balances_raw as $b) {
            $balances[$b['account_id']] = $b;
        }

        // Group accounts same way as COA
        $grouped = [];
        foreach ($accounts as $acc) {
            $type  = $acc['account_type']  ?: 'Uncategorized';
            $group = $acc['account_group'] ?: 'General';
            $sub   = $acc['account_sub_group'] ?: '';
            $grouped[$type][$group][$sub][] = $acc;
        }
        ksort($grouped);
        foreach ($grouped as $t => &$groups) {
            ksort($groups);
            foreach ($groups as $g => &$subs) ksort($subs);
        }
        unset($groups, $subs);

        $is_admin = $this->_is_ob_admin();

        $data['accounts']      = $accounts;
        $data['account_types'] = $account_types;
        $data['grouped']       = $grouped;
        $data['balances']      = $balances;
        $data['is_admin']      = $is_admin;
        $data['school_name']   = $this->setting_model->getCurrentSchoolName();

        $this->load->view('layout/header', $data);
        $this->load->view('account/opening_balance', $data);
        $this->load->view('layout/footer', $data);
    }

    public function save_opening_balance() {
        $this->_ob_admin_only();

        $account_id    = (int)$this->input->post('account_id');
        $debit_amount  = (float)$this->input->post('debit_amount');
        $credit_amount = (float)$this->input->post('credit_amount');
        $opening_date  = $this->input->post('opening_date');
        $financial_year= trim($this->input->post('financial_year'));
        $notes         = $this->input->post('notes');

        if (!$account_id) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Invalid account.</div>');
            redirect('accounts/opening_balance');
            return;
        }

        $user    = $this->session->userdata('admin');
        $user_id = $user['id'];

        $data = [
            'account_id'     => $account_id,
            'debit_amount'   => $debit_amount,
            'credit_amount'  => $credit_amount,
            'opening_date'   => $opening_date ?: date('Y-m-d'),
            'financial_year' => $financial_year ?: date('Y'),
            'notes'          => $notes,
            'created_by'     => $user_id,
            'updated_at'     => date('Y-m-d H:i:s'),
        ];

        $this->accounts_model->save_opening_balance($data);
        $this->session->set_flashdata('msg', '<div class="alert alert-success"><i class="fa fa-check"></i> Opening balance saved successfully.</div>');
        redirect('accounts/opening_balance');
    }

    public function delete_opening_balance($id) {
        $this->_ob_admin_only();
        $this->accounts_model->delete_opening_balance((int)$id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">Opening balance deleted.</div>');
        redirect('accounts/opening_balance');
    }

    public function get_opening_balance_json($account_id) {
        if (!$this->_is_ob_admin()) {
            echo json_encode(['success' => false, 'message' => 'Access denied']);
            return;
        }
        $row = $this->accounts_model->get_opening_balance_by_account((int)$account_id);
        if ($row) {
            echo json_encode(['success' => true] + $row);
        } else {
            echo json_encode(['success' => false]);
        }
    }
}

