<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Schsettings extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->library('upload');
    }

    private function saveLogoFile($fieldName, $targetDir, $id)
    {
        if (!isset($_FILES[$fieldName]) || empty($_FILES[$fieldName]['name'])) {
            return array('status' => false, 'error' => $this->lang->line('logo_file_is_required'));
        }

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0777, true);
        }

        if (!is_dir($targetDir) || !is_writable($targetDir)) {
            return array('status' => false, 'error' => $this->lang->line('something_went_wrong'));
        }

        $fileInfo  = pathinfo($_FILES[$fieldName]['name']);
        $extension = strtolower($fileInfo['extension']);
        $img_name  = $id . '.' . $extension;

        if (!move_uploaded_file($_FILES[$fieldName]["tmp_name"], rtrim($targetDir, '/\\') . DIRECTORY_SEPARATOR . $img_name)) {
            return array('status' => false, 'error' => $this->lang->line('something_went_wrong'));
        }

        return array('status' => true, 'file_name' => $img_name);
    }

    private function sanitize_post_text($key, $default = '')
    {
        $value = $this->input->post($key, true);
        if ($value === null) {
            return $default;
        }
        if (is_array($value)) {
            return $default;
        }
        return trim((string) $value);
    }

    private function sanitize_post_int($key, $default = 0)
    {
        $value = $this->input->post($key, true);
        if ($value === null || $value === '') {
            return (int) $default;
        }
        if (!is_numeric($value)) {
            return (int) $default;
        }
        return (int) $value;
    }

    private function sanitize_post_enum($key, $allowed = array(), $default = '')
    {
        $value = $this->sanitize_post_text($key, $default);
        return in_array($value, $allowed, true) ? $value : $default;
    }

  
    public function index()
    {

        if (!$this->rbac->hasPrivilege('general_setting', 'can_view')) {
            access_denied();
        }
        $app_ver = $this->config->item('app_ver');
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'schsettings/index');
        $data['title']          = 'Setting List';
        $timezoneList           = $this->customlib->timezone_list();
        $data['title']          = 'School Setting';
        $session_result         = $this->session_model->get();
        $language_result        = $this->language_model->getEnable_languages();
        $data['sessionlist']    = $session_result;
        $month_list             = $this->customlib->getMonthList();
        $days_list              = $this->customlib->getDayList();
        $data['daysList']       = $days_list;
        $data['languagelist']   = $language_result;
        $data['timezoneList']   = $timezoneList;
        $data['monthList']      = $month_list;
        $dateFormat             = $this->customlib->getDateFormat();
        $currency               = $this->customlib->getCurrency();
        $data['dateFormatList'] = $dateFormat;
        $data['currencyList']   = $currency;
        $digit                  = $this->customlib->getDigits();
        $data['digitList']      = $digit;
        $currencyPlace          = $this->customlib->getCurrencyPlace();
        $data['currencyPlace']  = $currencyPlace;
        $data['result']         = $this->setting_model->getSetting();
        $this->setting_model->ensureWhatsappWebSecurityKey($data['result']);
        $data['app_response']   = $this->auth->andapp_validate();
        $this->load->view('layout/header', $data);
        $this->load->view('setting/settingList', $data);
        $this->load->view('layout/footer', $data);
    }

    public function ajax_editlogo()
    {
        $this->form_validation->set_rules('id', $this->lang->line('id'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {
            $data = array(
                'file' => form_error('file'),
            );
            $array = array('success' => false, 'error' => $data);
            echo json_encode($array);
        } else {
            $id = $this->sanitize_post_int('id');
            $upload = $this->saveLogoFile('file', FCPATH . 'uploads/school_content/logo', $id);
            if (!$upload['status']) {
                $array = array('success' => false, 'error' => array('file' => $upload['error']));
                echo json_encode($array);
                return;
            }
            $img_name    = $upload['file_name'];
            $data_record = array('id' => $id, 'image' => $img_name);
            $this->setting_model->add($data_record);
            $array = array('success' => true, 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function ajax_editadmin_smalllogo()
    {

        $this->form_validation->set_rules('id', $this->lang->line('id'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {
            $data = array(
                'file' => form_error('file'),
            );
            $array = array('success' => false, 'error' => $data);
            echo json_encode($array);
        } else {
            $id = $this->sanitize_post_int('id');
            $upload = $this->saveLogoFile('file', FCPATH . 'uploads/school_content/admin_small_logo', $id);
            if (!$upload['status']) {
                $array = array('success' => false, 'error' => array('file' => $upload['error']));
                echo json_encode($array);
                return;
            }
            $img_name    = $upload['file_name'];
            $data_record = array('id' => $id, 'admin_small_logo' => $img_name);
            $this->setting_model->add($data_record);
            $array = array('success' => true, 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function ajax_editadmin_adminlogo()
    {

        $this->form_validation->set_rules('id', $this->lang->line('id'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {
            $data = array(
                'file' => form_error('file'),
            );
            $array = array('success' => false, 'error' => $data);
            echo json_encode($array);
        } else {
            $id = $this->sanitize_post_int('id');
            $upload = $this->saveLogoFile('file', FCPATH . 'uploads/school_content/admin_logo', $id);
            if (!$upload['status']) {
                $array = array('success' => false, 'error' => array('file' => $upload['error']));
                echo json_encode($array);
                return;
            }
            $img_name    = $upload['file_name'];
            $data_record = array('id' => $id, 'admin_logo' => $img_name);
            $this->setting_model->add($data_record);
            $array = array('success' => true, 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function editLogo($id)
    {
        $data['title']       = 'School Logo';
        $setting_result      = $this->setting_model->get();
        $data['settinglist'] = $setting_result;
        $data['id']          = $id;
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('setting/editLogo', $data);
            $this->load->view('layout/footer', $data);
        } else {
            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $upload = $this->saveLogoFile('file', FCPATH . 'uploads/school_content/logo', $id);
                if (!$upload['status']) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger alert-left">' . $upload['error'] . '</div>');
                    redirect('schsettings/index');
                }
                $img_name = $upload['file_name'];
            }
            $data_record = array('id' => $id, 'image' => $img_name);
            $this->setting_model->add($data_record);
            $this->session->set_flashdata('msg', '<div class="alert alert-left">' . $this->lang->line('update_message') . '</div>');
            redirect('schsettings/index');
        }
    }

    public function handle_upload()
    {
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('jpg', 'jpeg', 'png');
            $temp        = explode(".", $_FILES["file"]["name"]);
            $extension   = end($temp);
            if ($_FILES["file"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if ($_FILES["file"]["type"] != 'image/gif' &&
                $_FILES["file"]["type"] != 'image/jpeg' &&
                $_FILES["file"]["type"] != 'image/png') {
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $this->form_validation->set_message('handle_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($_FILES["file"]["size"] > 1048576) {
                $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') . " 1MB");
                return false;
            }
            return true;
        } else {
            $this->form_validation->set_message('handle_upload', $this->lang->line('logo_file_is_required'));
            return false;
        }
    }

    public function view($id)
    {
        $data['title']   = 'Setting List';
        $setting         = $this->setting_model->get($id);
        $data['setting'] = $setting;
        $this->load->view('layout/header', $data);
        $this->load->view('setting/settingShow', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getSchsetting()
    {

        $data = $this->setting_model->getSetting();
        echo json_encode($data);
    }

    public function ajax_schedit()
    {

        if (!$this->rbac->hasPrivilege('general_setting', 'can_edit')) {
            access_denied();
        }
        $auto_staff_id = false;
        $this->form_validation->set_rules('sch_session_id', $this->lang->line('session'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('fee_due_days', $this->lang->line('fees_due_days'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_name', $this->lang->line('school_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_phone', $this->lang->line('phone'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_start_month', $this->lang->line('start_month'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_address', $this->lang->line('address'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_email', $this->lang->line('email'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_lang_id', $this->lang->line('language'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_currency_symbol', $this->lang->line('currency_symbol'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_timezone', $this->lang->line('timezone'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_currency', $this->lang->line('currency'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('currency_place', $this->lang->line('currency_place'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_date_format', $this->lang->line('date_format'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_is_rtl', $this->lang->line('rtl'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('theme', $this->lang->line('theme'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('sch_start_week', $this->lang->line('start_day_of_week'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('attendence_type', $this->lang->line('attendance') . " " . $this->lang->line('type'), 'trim|required|xss_clean');

        $this->form_validation->set_rules('is_duplicate_fees_invoice', $this->lang->line('duplicate') . " " . $this->lang->line('fees') . " " . $this->lang->line('invoice'), 'trim|required|xss_clean');

        if ($this->input->post('adm_auto_insert')) {
//            $this->form_validation->set_rules('adm_prefix', $this->lang->line('admission_no_prefix'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('adm_start_from', $this->lang->line('admission_start_from'), 'trim|integer|required|xss_clean');
//            $this->form_validation->set_rules('adm_no_digit', $this->lang->line('admission_no_digit'), 'trim|integer|required|xss_clean|callback_check_admission_digit');
        }
        if ($this->input->post('staffid_auto_insert')) {

//            $this->form_validation->set_rules('staffid_prefix', $this->lang->line('staff_id_prefix'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('staffid_start_from', $this->lang->line('staff_id_start_from'), 'trim|integer|required|xss_clean');
//            $this->form_validation->set_rules('staffid_no_digit', $this->lang->line('staff_id_digit'), 'trim|integer|required|xss_clean|callback_check_staff_id_digit');
        }

        if ($this->form_validation->run() == false) {
            $data = array(
                'is_student_house'          => form_error('is_student_house'),
                'sch_session_id'            => form_error('sch_session_id'),
                'sch_name'                  => form_error('sch_name'),
                'sch_phone'                 => form_error('sch_phone'),
                'sch_start_month'           => form_error('sch_start_month'),
                'sch_start_week'            => form_error('sch_start_week'),
                'sch_address'               => form_error('sch_address'),
                'sch_email'                 => form_error('sch_email'),
                'sch_lang_id'               => form_error('sch_lang_id'),
                'sch_currency_symbol'       => form_error('sch_currency_symbol'),
                'sch_timezone'              => form_error('sch_timezone'),
                'sch_currency'              => form_error('sch_currency'),
                'currency_place'            => form_error('currency_place'),
                'sch_date_format'           => form_error('sch_date_format'),
                'sch_is_rtl'                => form_error('sch_is_rtl'),
                'theme'                     => form_error('theme'),
                'adm_start_from'            => form_error('adm_start_from'),
                'biometric_device'          => form_error('biometric_device'),
                'biometric'                 => form_error('biometric'),
                'adm_prefix'                => form_error('adm_prefix'),
                'adm_no_digit'              => form_error('adm_no_digit'),
                'staffid_start_from'        => form_error('staffid_start_from'),
                'staffid_prefix'            => form_error('staffid_prefix'),
                'staffid_no_digit'          => form_error('staffid_no_digit'),

                'is_duplicate_fees_invoice' => form_error('is_duplicate_fees_invoice'),
                'attendence_type'           => form_error('attendence_type'),
                'fee_due_days'              => form_error('fee_due_days'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $setting_result = $this->setting_model->getSetting();
            $sch_id         = $this->sanitize_post_int('sch_id');
            $attendence_type = $this->sanitize_post_int('attendence_type');
            $sch_session_id  = $this->sanitize_post_int('sch_session_id');
            $sch_name        = $this->sanitize_post_text('sch_name');
            $sch_phone       = $this->sanitize_post_text('sch_phone');
            $sch_dise_code   = $this->sanitize_post_text('sch_dise_code');
            $sch_start_month = $this->sanitize_post_text('sch_start_month');
            $sch_start_week  = $this->sanitize_post_text('sch_start_week');
            $sch_address     = $this->sanitize_post_text('sch_address');
            $sch_email       = $this->sanitize_post_text('sch_email');
            $sch_lang_id     = $this->sanitize_post_int('sch_lang_id');
            $sch_timezone    = $this->sanitize_post_text('sch_timezone');
            $sch_date_format = $this->sanitize_post_text('sch_date_format');
            $sch_is_rtl      = $this->sanitize_post_text('sch_is_rtl');
            $sch_currency    = $this->sanitize_post_text('sch_currency');
            $currency_symbol = $this->sanitize_post_text('sch_currency_symbol');
            $currency_place  = $this->sanitize_post_text('currency_place');
            $fee_due_days    = $this->sanitize_post_int('fee_due_days');
            $theme           = $this->sanitize_post_text('theme');
            $adm_start_from  = $this->sanitize_post_int('adm_start_from');
            $adm_prefix      = $this->sanitize_post_text('adm_prefix');
            $adm_no_digit    = $this->sanitize_post_int('adm_no_digit');
            $adm_auto_insert = $this->sanitize_post_int('adm_auto_insert');
            $staffid_start_from  = $this->sanitize_post_int('staffid_start_from');
            $staffid_prefix      = $this->sanitize_post_text('staffid_prefix');
            $staffid_no_digit    = $this->sanitize_post_int('staffid_no_digit');
            $staffid_auto_insert = $this->sanitize_post_int('staffid_auto_insert');
            $class_teacher            = $this->sanitize_post_enum('class_teacher', array('yes', 'no'), 'no');
            $biometric_device         = $this->sanitize_post_text('biometric_device');
            $biometric                = $this->sanitize_post_int('biometric');
            $is_duplicate_fees_invoice = $this->sanitize_post_int('is_duplicate_fees_invoice');
            $app_primary_color_code   = $this->sanitize_post_text('app_primary_color_code');
            $app_secondary_color_code = $this->sanitize_post_text('app_secondary_color_code');
            $mobile_api_url           = $this->sanitize_post_text('mobile_api_url');
            $my_question              = $this->sanitize_post_text('my_question');
            $whatsapp_web_security_key = $this->sanitize_post_text('whatsapp_web_security_key');
            if ($whatsapp_web_security_key === '') {
                $whatsapp_web_security_key = $this->setting_model->ensureWhatsappWebSecurityKey($setting_result);
            }

            $data = array(
                'id'                        => $sch_id,
                'attendence_type'           => $attendence_type,
                'session_id'                => $sch_session_id,
                'name'                      => $sch_name,
                'phone'                     => $sch_phone,
                'dise_code'                 => $sch_dise_code,
                'start_month'               => $sch_start_month,
                'start_week'                => $sch_start_week,
                'address'                   => $sch_address,
                'email'                     => $sch_email,
                'lang_id'                   => $sch_lang_id,
                'timezone'                  => $sch_timezone,
                'date_format'               => $sch_date_format,
                'is_rtl'                    => $sch_is_rtl,
                'currency'                  => $sch_currency,
                'currency_symbol'           => $currency_symbol,
                'currency_place'            => $currency_place,
                'fee_due_days'              => $fee_due_days,
                'theme'                     => $theme,
                'adm_start_from'            => $adm_start_from,
                'adm_prefix'                => $adm_prefix,
                'adm_no_digit'              => $adm_no_digit,
                'adm_auto_insert'           => $adm_auto_insert,
                'staffid_start_from'        => $staffid_start_from,
                'staffid_prefix'            => $staffid_prefix,
                'staffid_no_digit'          => $staffid_no_digit,
                'staffid_auto_insert'       => $staffid_auto_insert,
                'class_teacher'             => $class_teacher,
                'biometric_device'          => $biometric_device,
                'biometric'                 => $biometric,
                'is_duplicate_fees_invoice' => $is_duplicate_fees_invoice,
                'app_primary_color_code'    => $app_primary_color_code,
                'app_secondary_color_code'  => $app_secondary_color_code,
                'mobile_api_url'            => $mobile_api_url,
                'my_question'               => $my_question,
                'whatsapp_web_security_key' => $whatsapp_web_security_key,

            );
            $this->session->userdata['admin']['is_rtl'] = $sch_is_rtl;
            $language_result                            = $this->language_model->get($sch_lang_id);
            if ($this->customlib->get_rtl_languages($language_result['short_code'])) {
                $this->session->userdata['admin']['is_rtl'] = 'enabled';
                $data['is_rtl']                             = 'enabled';
            }

            $session_result = $this->session_model->get($sch_session_id);

            $session = array(
                'session_id' => $session_result['id'],
                'session'    => $session_result['session'],
            );
            $this->session->set_userdata('session_array', $session);

            $data['adm_update_status']     = 1;
            $data['staffid_update_status'] = 1;
            if ($adm_auto_insert) {
                if ($setting_result->adm_prefix != $adm_prefix ||
                    $setting_result->adm_start_from != $adm_start_from ||
                    $setting_result->adm_no_digit != $adm_no_digit
                ) {
                    $data['adm_update_status'] = 0;
                }
            }

            if ($staffid_auto_insert) {
                if ($setting_result->staffid_prefix != $staffid_prefix ||
                    $setting_result->staffid_start_from != $staffid_start_from ||
                    $setting_result->staffid_no_digit != $staffid_no_digit
                ) {
                    $data['staffid_update_status'] = 0;
                }
            }

            $data['adm_update_status'];
            $this->setting_model->add($data);
            $this->load->helper('lang');
            $this->session->userdata['admin']['date_format']     = $sch_date_format;
            $this->session->userdata['admin']['currency_symbol'] = $currency_symbol;
            $this->session->userdata['admin']['start_week']      = date("w", strtotime($sch_start_week));
            $this->session->userdata['admin']['timezone']        = $sch_timezone;
            $this->session->userdata['admin']['theme']           = $theme;
            $this->session->userdata['admin']['currency_place']  = $currency_place;
            $session                                             = $this->session->userdata('admin');
            $staff_id                                            = $session['id'];
            $defoultlang                                         = $this->setting_model->get_stafflang($staff_id);

            if ($defoultlang['lang_id'] != 0) {

                set_language($defoultlang['lang_id']);
            } else {
                set_language($sch_lang_id);
            }

            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function ajax_applogo()
    {
        $this->form_validation->set_rules('id', 'ID', 'trim|required|xss_clean');
        $this->form_validation->set_rules('file', 'Image', 'callback_handle_upload');

        if ($this->form_validation->run() == false) {
            $data = array(
                'file' => form_error('file'),
            );
            $array = array('success' => false, 'error' => $data);
            echo json_encode($array);
        } else {

            $id = $this->sanitize_post_int('id');
            $upload = $this->saveLogoFile('file', FCPATH . 'uploads/school_content/logo/app_logo', $id);
            if (!$upload['status']) {
                $array = array('success' => false, 'error' => array('file' => $upload['error']));
                echo json_encode($array);
                return;
            }
            $img_name = $upload['file_name'];

            $data_record = array('id' => $id, 'app_logo' => $img_name);

            $this->setting_model->add($data_record);
            $array = array('success' => true, 'error' => '', 'message' => 'Record Updated Successfully');
            echo json_encode($array);
        }
    }

    public function check_admission_digit()
    {
        $adm_start_from = $this->input->post('adm_start_from');
        $adm_no_digit   = $this->input->post('adm_no_digit');
        if ($adm_no_digit != "") {

            if (strlen($adm_start_from) == $adm_no_digit) {

                return true;
            }
            $this->form_validation->set_message('check_admission_digit', 'Admission no must be ' . $adm_no_digit . ' digit long');
            return false;
        }
        return true;
    }

    public function check_staff_id_digit()
    {
        $adm_start_from   = $this->input->post('staffid_start_from');
        $staffid_no_digit = $this->input->post('staffid_no_digit');
        if ($staffid_no_digit != "") {

            if (strlen($adm_start_from) == $staffid_no_digit) {

                return true;
            }
            $this->form_validation->set_message('check_staff_id_digit', $this->lang->line('staff_id_start_from_must_be') . ' ' . strlen($adm_start_from) . ' ' . $this->lang->line('digit_long'));
            return false;
        }
        return true;
    }

}
