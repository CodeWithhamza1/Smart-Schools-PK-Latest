<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Vouchersettings extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('voucher_setting_model');
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('general_setting', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'vouchersettings/index');

        $school_setting = $this->setting_model->getSetting();
        $setting_id = (!empty($school_setting->id)) ? (int) $school_setting->id : 1;
        $can_edit = $this->rbac->hasPrivilege('general_setting', 'can_edit');
        $settings = $this->voucher_setting_model->getResolved($setting_id);

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            if (!$can_edit) {
                access_denied();
            }

            $copy_count = (int) $this->input->post('copy_count');
            $this->form_validation->set_rules('copy_count', 'Number of copies', 'trim|required|integer|greater_than_equal_to[1]|less_than_equal_to[3]');
            for ($copy_number = 1; $copy_number <= 3; $copy_number++) {
                $rules = 'trim|max_length[50]|xss_clean';
                if ($copy_number <= $copy_count) {
                    $rules .= '|required';
                }
                $this->form_validation->set_rules('copy_' . $copy_number . '_label', 'Copy ' . $copy_number . ' label', $rules);
            }
            $this->form_validation->set_rules('header_note', 'Header bank details', 'trim|max_length[1000]|xss_clean');
            $this->form_validation->set_rules('footer_notes', 'Footer notes', 'trim|max_length[2000]|xss_clean');

            $settings = $this->voucher_setting_model->normalize(array(
                'copy_count'   => $copy_count,
                'copy_1_label' => $this->input->post('copy_1_label', true),
                'copy_2_label' => $this->input->post('copy_2_label', true),
                'copy_3_label' => $this->input->post('copy_3_label', true),
                'header_note'  => $this->input->post('header_note', true),
                'footer_notes' => $this->input->post('footer_notes', true),
            ));

            if ($this->form_validation->run() !== false) {
                $staff_id = $this->customlib->getStaffID();
                if ($this->voucher_setting_model->saveSettings($setting_id, $settings, $staff_id)) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-success">Voucher settings updated successfully.</div>');
                    redirect('admin/voucher-settings');
                }
                $data['save_error'] = 'Voucher settings could not be saved. Please apply the voucher settings database migration and try again.';
            }
        }

        $data['title'] = 'Voucher Settings';
        $data['settings'] = $settings;
        $data['can_edit'] = $can_edit;
        $data['school_setting'] = $school_setting;

        $this->load->view('layout/header', $data);
        $this->load->view('setting/voucherSettings', $data);
        $this->load->view('layout/footer', $data);
    }
}
