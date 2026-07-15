<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Stuattendence extends Admin_Controller {

    function __construct() {
        parent::__construct();

        $this->config->load("mailsms");
        $this->load->library('mailsmsconf');
        $this->config_attendance = $this->config->item('attendence');
        $this->load->model("classteacher_model");
        $this->load->model("whatsappcampaign_model");
         $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    function index() {
        if (!$this->rbac->hasPrivilege('student_attendance', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Attendance');
        $this->session->set_userdata('sub_menu', 'stuattendence/index');
        $data['title'] = 'Add Fees Type';
        $data['title_list'] = 'Fees Type List';
        $sch_setting = $this->setting_model->getSchoolDetail();
        $data['sch_setting'] = $this->sch_setting_detail;
        $class = $this->class_model->get('', $classteacher = 'yes');
        $userdata = $this->customlib->getUserData();
        $carray = array();

        if (!empty($data["classlist"])) {
            foreach ($data["classlist"] as $ckey => $cvalue) {

                $carray[] = $cvalue["id"];
            }
        }

        $userdata = $this->customlib->getUserData();
        $role_id = $userdata["role_id"];
        if (isset($role_id) && ($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
            if ($userdata["class_teacher"] == 'yes') {
                $carray = array();
                $class = array();
                $class = $this->teacher_model->get_daywiseattendanceclass($userdata["id"]);
            }
        }

        $data['classlist'] = $class;
        $requested_class_id = $this->input->post('class_id');
        if ($requested_class_id === null || $requested_class_id === '') {
            $requested_class_id = $this->input->get('class_id');
        }

        $requested_section_id = $this->input->post('section_id');
        if ($requested_section_id === null || $requested_section_id === '') {
            $requested_section_id = $this->input->get('section_id');
        }

        $requested_date = $this->input->post('date');
        if ($requested_date === null || $requested_date === '') {
            $requested_date = $this->input->get('date');
        }

        $data['class_id'] = $requested_class_id;
        $data['section_id'] = $requested_section_id;
        $data['date'] = $requested_date;
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $is_dashboard_autoload = ($this->input->get('autoload') == '1');
        $has_dashboard_filters = !empty($requested_class_id) && !empty($requested_section_id) && !empty($requested_date);

        if ($this->form_validation->run() == FALSE && !($is_dashboard_autoload && $has_dashboard_filters)) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/stuattendence/attendenceList', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $class = !empty($requested_class_id) ? $requested_class_id : $this->input->post('class_id');
            $section = !empty($requested_section_id) ? $requested_section_id : $this->input->post('section_id');
            $date = !empty($requested_date) ? $requested_date : $this->input->post('date');
            $student_list = $this->stuattendence_model->get();
            $data['studentlist'] = $student_list;
            $data['class_id'] = $class;
            $data['section_id'] = $section;
            $data['date'] = $date;
            $search = $this->input->post('search');
            $holiday = $this->input->post('holiday');
            if ($search == "saveattendence") {
                $session_ary = $this->input->post('student_session');
                $absent_student_list = array();
                $absent_type_ids = $this->getAbsentAttendanceTypeIds();
                $attendance_date = date('Y-m-d', $this->customlib->datetostrtotime($date));
                foreach ($session_ary as $key => $value) {
                    $checkForUpdate = $this->input->post('attendendence_id' . $value);
                    if ($checkForUpdate != 0) {
                        $previous_attendance = $this->stuattendence_model->get($checkForUpdate);
                        $previous_attendance_type_id = isset($previous_attendance['attendence_type_id']) ? (int) $previous_attendance['attendence_type_id'] : 0;
                        if (isset($holiday)) {
                            $arr = array(
                                'id' => $checkForUpdate,
                                'student_session_id' => $value,
                                'attendence_type_id' => 5,
                                'remark' => $this->input->post("remark" . $value),
                                'date' => $attendance_date
                            );
                        } else {
                            $arr = array(
                                'id' => $checkForUpdate,
                                'student_session_id' => $value,
                                'attendence_type_id' => $this->input->post('attendencetype' . $value),
                                'remark' => $this->input->post("remark" . $value),
                                'date' => $attendance_date
                            );
                        }
                        $insert_id = $this->stuattendence_model->add($arr);
                        if (!isset($holiday)
                            && in_array((int) $arr['attendence_type_id'], $absent_type_ids, true)
                            && !in_array($previous_attendance_type_id, $absent_type_ids, true)) {
                            $absent_student_list[] = $value;
                        }
                    } else {
                        if (isset($holiday)) {
                            $arr = array(
                                'student_session_id' => $value,
                                'attendence_type_id' => 5,
                                'remark' => $this->input->post("remark" . $value),
                                'date' => $attendance_date
                            );
                        } else {


                            $arr = array(
                                'student_session_id' => $value,
                                'attendence_type_id' => $this->input->post('attendencetype' . $value),
                                'remark' => $this->input->post("remark" . $value),
                                'date' => $attendance_date
                            );
                        }
                        $insert_id = $this->stuattendence_model->add($arr);
                        if (!isset($holiday)
                            && in_array((int) $arr['attendence_type_id'], $absent_type_ids, true)) {
                            $absent_student_list[] = $value;
                        }
                    }
                }
                if (!empty($absent_student_list)) {
                    $absent_student_list = array_values(array_unique($absent_student_list));
                    $this->mailsmsconf->mailsms('absent_attendence', $absent_student_list, $date);
                    $admin_session = $this->session->userdata('admin');
                    $created_by = isset($admin_session['id']) ? (int) $admin_session['id'] : 0;
                    $whatsapp_campaign_id = $this->whatsappcampaign_model->createAbsenceAlertCampaignForStudentSessions($class, $section, $attendance_date, $absent_student_list, $created_by);
                    if (!$whatsapp_campaign_id) {
                        log_message('error', 'Unable to create WhatsApp absence campaign for attendance date ' . $attendance_date . ', class ' . $class . ', section ' . $section . ', absent student_session_ids: ' . implode(',', $absent_student_list));
                    }
                }

                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
                redirect('admin/stuattendence/index', 'refresh');
            }
            $attendencetypes = $this->attendencetype_model->get();
            $data['attendencetypeslist'] = $attendencetypes;
            $resultlist = $this->stuattendence_model->searchAttendenceClassSection($class, $section, date('Y-m-d', $this->customlib->datetostrtotime($date)));
            $data['resultlist'] = $resultlist;
            $this->load->view('layout/header', $data);
            $this->load->view('admin/stuattendence/attendenceList', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    private function getAbsentAttendanceTypeIds() {
        $absent_type_ids = array();
        $attendencetypes = $this->attendencetype_model->get();

        foreach ($attendencetypes as $type) {
            $type_name = isset($type['type']) ? strtolower(trim($type['type'])) : '';
            $key_value = isset($type['key_value']) ? strtolower(trim($type['key_value'])) : '';

            if ($type_name === 'absent' || $key_value === 'a') {
                $absent_type_ids[] = (int) $type['id'];
            }
        }

        if (empty($absent_type_ids) && isset($this->config_attendance['absent'])) {
            $absent_type_ids[] = (int) $this->config_attendance['absent'];
        }

        return array_values(array_unique($absent_type_ids));
    }

    function attendencereport() {

        if (!$this->rbac->hasPrivilege('attendance_by_date', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Attendance');
        $this->session->set_userdata('sub_menu', 'stuattendence/attendenceReport');
        $data['title'] = 'Add Fees Type';
        $data['title_list'] = 'Fees Type List';
        $class = $this->class_model->get();
        $userdata = $this->customlib->getUserData();

        $role_id = $userdata["role_id"];


        if (isset($role_id) && ($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
            if ($userdata["class_teacher"] == 'yes') {
                $carray = array();
                $class = array();
                $class = $this->teacher_model->get_daywiseattendanceclass($userdata["id"]);
            }
        }
        $data['classlist'] = $class;
        $data['class_id'] = "";
        $data['section_id'] = "";
        $data['date'] = "";
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('date', $this->lang->line('date'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == FALSE) {

            $this->load->view('layout/header', $data);
            $this->load->view('admin/stuattendence/attendencereport', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $class = $this->input->post('class_id');
            $section = $this->input->post('section_id');
            $date = $this->input->post('date');
            $student_list = $this->stuattendence_model->get();
            $data['studentlist'] = $student_list;
            $data['class_id'] = $class;
            $data['section_id'] = $section;
            $data['date'] = $date;
            $search = $this->input->post('search');
            if ($search == "saveattendence") {
                $session_ary = $this->input->post('student_session');
                foreach ($session_ary as $key => $value) {
                    $checkForUpdate = $this->input->post('attendendence_id' . $value);
                    if ($checkForUpdate != 0) {
                        $arr = array(
                            'id' => $checkForUpdate,
                            'student_session_id' => $value,
                            'attendence_type_id' => $this->input->post('attendencetype' . $value),
                            'date' => date('Y-m-d', $this->customlib->datetostrtotime($date))
                        );
                        $insert_id = $this->stuattendence_model->add($arr);
                    } else {
                        $arr = array(
                            'student_session_id' => $value,
                            'attendence_type_id' => $this->input->post('attendencetype' . $value),
                            'date' => date('Y-m-d', $this->customlib->datetostrtotime($date))
                        );
                        $insert_id = $this->stuattendence_model->add($arr);
                    }
                }
            }
            $attendencetypes = $this->attendencetype_model->get();
            $data['attendencetypeslist'] = $attendencetypes;
            $resultlist = $this->stuattendence_model->searchAttendenceClassSectionPrepare($class, $section, date('Y-m-d', $this->customlib->datetostrtotime($date)));

            $data['resultlist'] = $resultlist;
            $data['sch_setting'] =$this->sch_setting_detail;
            $this->load->view('layout/header', $data);
            $this->load->view('admin/stuattendence/attendencereport', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    function classattendencereport() {

        if (!$this->rbac->hasPrivilege('attendance_report', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/attendance');
        $this->session->set_userdata('subsub_menu', 'Reports/attendance/attendance_report');
        $attendencetypes = $this->attendencetype_model->getAttType();
        $data['attendencetypeslist'] = $attendencetypes;
        $data['title'] = 'Add Fees Type';
        $data['title_list'] = 'Fees Type List';
        $class = $this->class_model->get();
        $userdata = $this->customlib->getUserData();

        $role_id = $userdata["role_id"];


        if (isset($role_id) && ($userdata["role_id"] == 2) && ($userdata["class_teacher"] == "yes")) {
            if ($userdata["class_teacher"] == 'yes') {
                $carray = array();
                $class = array();
                $class = $this->teacher_model->get_daywiseattendanceclass($userdata["id"]);
            }
        }
        $data['classlist'] = $class;
        $userdata = $this->customlib->getUserData();

        $data['monthlist'] = $this->customlib->getMonthDropdown();
        $data['yearlist'] = $this->stuattendence_model->attendanceYearCount();
        $data['class_id'] = "";
        $data['section_id'] = "";
        $data['date'] = "";
        $data['month_selected'] = "";
        $data['year_selected'] = "";
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('month', $this->lang->line('month'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/stuattendence/classattendencereport', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $resultlist = array();
            $class = $this->input->post('class_id');
            $section = $this->input->post('section_id');
            $month = $this->input->post('month');
            $data['class_id'] = $class;
            $data['section_id'] = $section;
            $data['month_selected'] = $month;
            $studentlist = $this->student_model->searchByClassSection($class, $section);
            $session_current = $this->setting_model->getCurrentSessionName();
            $startMonth = $this->setting_model->getStartMonth();
            $centenary = substr($session_current, 0, 2); //2017-18 to 2017
            $year_first_substring = substr($session_current, 2, 2); //2017-18 to 2017
            $year_second_substring = substr($session_current, 5, 2); //2017-18 to 18
            $month_number = date("m", strtotime($month));
            $year = $this->input->post('year');
            $data['year_selected'] = $year;
            if (!empty($year)) {

                $year = $this->input->post("year");
            } else {

                if ($month_number >= $startMonth && $month_number <= 12) {
                    $year = $centenary . $year_first_substring;
                } else {
                    $year = $centenary . $year_second_substring;
                }
            }


            $num_of_days = cal_days_in_month(CAL_GREGORIAN, $month_number, $year);
            $attr_result = array();
            $attendence_array = array();
            $student_result = array();
            $data['no_of_days'] = $num_of_days;
            $date_result = array();
            for ($i = 1; $i <= $num_of_days; $i++) {
                $att_date = $year . "-" . $month_number . "-" . sprintf("%02d", $i);
                $attendence_array[] = $att_date;

                $res = $this->stuattendence_model->searchAttendenceReport($class, $section, $att_date);
                $student_result = $res;
                $s = array();
                foreach ($res as $result_k => $result_v) {
                    $s[$result_v['student_session_id']] = $result_v;
                }
                $date_result[$att_date] = $s;
            }

            $monthAttendance = array();
            foreach ($res as $result_k => $result_v) {


                $date = $year . "-" . $month;
                $newdate = date('Y-m-d', strtotime($date));
                $monthAttendance[] = $this->monthAttendance($newdate, 1, $result_v['student_session_id']);
            }
            $data['monthAttendance'] = $monthAttendance;

            $data['resultlist'] = $date_result;
            $data['attendence_array'] = $attendence_array;
            $data['student_array'] = $student_result;

            $this->load->view('layout/header', $data);
            $this->load->view('admin/stuattendence/classattendencereport', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    function monthAttendance($st_month, $no_of_months, $student_id) {

        $record = array();

        $r = array();
        $month = date('m', strtotime($st_month));
        $year = date('Y', strtotime($st_month));

        foreach ($this->config_attendance as $att_key => $att_value) {

            $s = $this->stuattendence_model->count_attendance_obj($month, $year, $student_id, $att_value);


            $attendance_key = $att_key;


            $r[$attendance_key] = $s;
        }

        $record[$student_id] = $r;

        return $record;
    }

}

?>
