<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Student extends Admin_Controller
{

    public $sch_setting_detail = array();

    public function __construct()
    {
        parent::__construct();
        $this->config->load('app-config');
        $this->load->library('smsgateway');
        $this->load->library('mailsmsconf');
        $this->load->library('encoding_lib');
        $this->load->model("classteacher_model");
        $this->load->model(array("timeline_model", "student_edit_field_model"));
        $this->blood_group        = $this->config->item('bloodgroup');
        $this->sch_setting_detail = $this->setting_model->getSetting();
        $this->load->model(array("timeline_model", "student_edit_field_model"));
        $this->role;
    }

    private function getNextAdmissionNo()
    {
        $adm_start_from = (int) $this->sch_setting_detail->adm_start_from;
        $adm_no_digit   = (int) $this->sch_setting_detail->adm_no_digit;
        $adm_prefix     = trim((string) $this->sch_setting_detail->adm_prefix);

        if ($adm_start_from <= 0) {
            $adm_start_from = 1;
        }
        if ($adm_no_digit <= 0) {
            $adm_no_digit = 4;
        }

        $max_sequence = (int) $this->student_model->getMaxAdmissionSequence($adm_prefix);
        if ($max_sequence <= 0) {
            $max_sequence = (int) $this->student_model->getMaxAdmissionSequenceLoose();
        }
        $next_adm_num = max($adm_start_from, $max_sequence + 1);

        $safety_counter = 0;
        do {
            $formatted_no = str_pad((string) $next_adm_num, $adm_no_digit, "0", STR_PAD_LEFT);
            $admission_no = $adm_prefix . $formatted_no;
            $next_adm_num++;
            $safety_counter++;
            if ($safety_counter > 500000) {
                break;
            }
        } while ($this->student_model->check_adm_exists($admission_no));

        return $admission_no;
    }

    public function index()
    {

        $data['title']       = 'Student List';
        $student_result      = $this->student_model->get();
        $data['studentlist'] = $student_result;
        $this->load->view('layout/header', $data);
        $this->load->view('student/studentList', $data);
        $this->load->view('layout/footer', $data);
    }

    public function multiclass()
    {
        if (!$this->rbac->hasPrivilege('multi_class_student', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/multiclass');
        $data['title']       = 'student fees';
        $data['title']       = 'student fees';
        $class               = $this->class_model->get();
        $data['classlist']   = $class;
        $data['sch_setting'] = $this->sch_setting_detail;

        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {

        } else {
            $class                   = $this->class_model->get();
            $data['classlist']       = $class;
            $data['student_due_fee'] = array();
            $class_id                = $this->input->post('class_id');
            $section_id              = $this->input->post('section_id');
            $classes                 = $this->classsection_model->allClassSections();

            $data['classes'] = $classes;

            $students         = $this->studentsession_model->searchMultiStudentByClassSection($class_id, $section_id);
            $data['students'] = $students;
        }
        $this->load->view('layout/header', $data);
        $this->load->view('student/multiclass', $data);
        $this->load->view('layout/footer', $data);
    }

    public function classsectionreport()
    {
        if (!$this->rbac->hasPrivilege('student_report', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/classsectionreport');
        $data['title']              = 'Class & Section Report';
        $data['class_section_list'] = $this->classsection_model->getClassSectionStudentCount();

        $this->load->view('layout/header', $data);
        $this->load->view('reports/classsectionreport', $data);
        $this->load->view('layout/footer', $data);

    }

    public function studentreport()
    {
        if (!$this->rbac->hasPrivilege('student_report', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/student_report');
        $data['title']           = 'student fee';
        $data['title']           = 'student fee';
        $genderList              = $this->customlib->getGender();
        $data['genderList']      = $genderList;
        $RTEstatusList           = $this->customlib->getRteStatus();
        $data['RTEstatusList']   = $RTEstatusList;
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $userdata                = $this->customlib->getUserData();
        $category                = $this->category_model->get();
        $data['categorylist']    = $category;
        $this->load->view('layout/header', $data);
        $this->load->view('student/studentReport', $data);
        $this->load->view('layout/footer', $data);

    }

    public function download($student_id, $doc)
    {
        $this->load->helper('download');
        $filepath = "./uploads/student_documents/$student_id/" . $this->uri->segment(4);
        $data     = file_get_contents($filepath);
        $name     = $this->uri->segment(6);
        force_download($name, $data);
    }

    public function view($id)
    {

        if (!$this->rbac->hasPrivilege('student', 'can_view')) {
            access_denied();
        }

        $data['title']         = 'Student Details';
        $student               = $this->student_model->get($id);
        $gradeList             = $this->grade_model->get();
        $studentSession        = $this->student_model->getStudentSession($id);
        $timeline              = $this->timeline_model->getStudentTimeline($id, $status = '');
        $data["timeline_list"] = $timeline;

        $student_session_id = $studentSession["student_session_id"];

        $student_session         = $studentSession["session"];
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $current_student_session = $this->student_model->get_studentsession($student['student_session_id']);

        $data["session"]              = $current_student_session["session"];
        $student_due_fee              = $this->studentfeemaster_model->getStudentFees($student['student_session_id']);
        $student_discount_fee         = $this->feediscount_model->getStudentFeesDiscount($student['student_session_id']);
        $data['student_discount_fee'] = $student_discount_fee;
        $data['student_due_fee']      = $student_due_fee;
        $siblings                     = $this->student_model->getMySiblings($student['parent_id'], $student['id']);

        $student_doc = $this->student_model->getstudentdoc($id);

        $data['student_doc']    = $student_doc;
        $data['student_doc_id'] = $id;
        $category_list          = $this->category_model->get();
        $data['category_list']  = $category_list;
        $data['gradeList']      = $gradeList;
        $data['student']        = $student;
        $data['siblings']       = $siblings;
        $class_section          = $this->student_model->getClassSection($student["class_id"]);
        $data["class_section"]  = $class_section;
        $session                = $this->setting_model->getCurrentSession();

        $studentlistbysection         = $this->student_model->getStudentClassSection($student["class_id"], $session);
        $data["studentlistbysection"] = $studentlistbysection;

        $data['guardian_credential'] = $this->student_model->guardian_credential($student['parent_id']);

        $data['reason'] = $this->disable_reason_model->get();
        if ($student['is_active'] = 'no') {
            $data['reason_data'] = $this->disable_reason_model->get($student['dis_reason']);
        }
        $data['exam_result'] = $this->examgroupstudent_model->searchStudentExams($student['student_session_id'], true, true);
        $data['exam_grade']  = $this->grade_model->getGradeDetails();

        $this->load->view('layout/header', $data);
        $this->load->view('student/studentShow', $data);
        $this->load->view('layout/footer', $data);
    }

    public function exportformat()
    {
        $this->load->helper('download');
        $filepath = "./backend/import/import_student_sample_file.csv";
        $data     = file_get_contents($filepath);
        $name     = 'import_student_sample_file.csv';

        force_download($name, $data);
    }

    public function delete($id)
    {
        if (!$this->rbac->hasPrivilege('student', 'can_delete')) {
            access_denied();
        }
        $this->student_model->remove($id);
        $this->session->set_flashdata('msg', '<i class="fa fa-check-square-o" aria-hidden="true"></i> ' . $this->lang->line('delete_message') . '');
        redirect('student/search');
    }

    public function doc_delete($id, $student_id)
    {
        $this->student_model->doc_delete($id);
        $this->session->set_flashdata('msg', '<i class="fa fa-check-square-o" aria-hidden="true"></i> ' . $this->lang->line('delete_message') . '');
        redirect('student/view/' . $student_id);
    }

    public function create()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_add')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/create');
        $genderList = $this->customlib->getGender();
        $data['genderList'] = $genderList;
        $data['title'] = 'Add Student';
        $data['title_list'] = 'Recently Added Student';
        $setting_result = $this->setting_model->get();
        //$student_categorize = $setting_result[0]["student_categorize"];
        //$data["student_categorize"] = $student_categorize ;
        $data["student_categorize"] = 'class';
        $session = $this->setting_model->getCurrentSession();
        $student_result = $this->student_model->getRecentRecord();
        $data['adm_auto_insert']    = $this->sch_setting_detail->adm_auto_insert;
        $data['admission_no'] = $this->getNextAdmissionNo();
        $data['studentlist'] = $student_result;
        $class = $this->class_model->get('', $classteacher = 'yes');
        $data['classlist'] = $class;
        $userdata = $this->customlib->getUserData();
        $category = $this->category_model->get();
        $data['categorylist'] = $category;
        $houses = $this->student_model->gethouselist();
        $data['houses'] = $houses;
        $data["bloodgroup"] = $this->blood_group;
        $hostelList = $this->hostel_model->get();
        $data['hostelList'] = $hostelList;
        $vehroute_result = $this->vehroute_model->get();
        $data['vehroutelist'] = $vehroute_result;
        $this->form_validation->set_rules('firstname', 'First Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('guardian_is', 'Guardian', 'trim|required|xss_clean');
        $this->form_validation->set_rules('gender', 'Gender', 'trim|required|xss_clean');
        $this->form_validation->set_rules('dob', 'Date of Birth', 'trim|required|xss_clean');
        $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', 'Section', 'trim|required|xss_clean');
        $this->form_validation->set_rules('rte', 'RTE', 'trim|required|xss_clean');
        $this->form_validation->set_rules('guardian_name', 'Guardian Name', 'trim|required|xss_clean');
        $this->form_validation->set_rules('guardian_phone', 'Guardian Phone', 'trim|required|xss_clean');
        if (!$this->sch_setting_detail->adm_auto_insert) {

            $this->form_validation->set_rules('admission_no', $this->lang->line('admission_no'), 'trim|required|xss_clean|is_unique[students.admission_no]');
        }
        $this->form_validation->set_rules('file', 'Image', 'callback_handle_upload');
        $this->form_validation->set_rules(
            'roll_no', 'Roll No.', array('trim',
                array('check_exists', array($this->student_model, 'valid_student_roll'))
            )
        );
        $feetype = $this->feetype_model->get();
        $data['feetypeList'] = $feetype;


        if ($this->form_validation->run() == FALSE) {

            $this->load->view('layout/header', $data);
            $this->load->view('student/studentCreate', $data);
            $this->load->view('layout/footer', $data);

        } else {

            $class_id = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');

            $fees_discount = $this->input->post('fees_discount');

            $vehroute_id = $this->input->post('vehroute_id');
            $hostel_room_id = $this->input->post('hostel_room_id');
            if (empty($vehroute_id)) {
                $vehroute_id = 0;
            }
            if (empty($hostel_room_id)) {
                $hostel_room_id = 0;
            }
            $data = array(
                // 'admission_no' => $this->input->post('admission_no'),
                'roll_no' => $this->input->post('roll_no'),
                'admission_date' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('admission_date'))),
                'firstname' => $this->input->post('firstname'),
                'lastname' => $this->input->post('lastname'),
                'mobileno' => $this->input->post('mobileno'),
                'rte' => $this->input->post('rte'),
                'email' => $this->input->post('email'),
                'state' => $this->input->post('state'),
                'city' => $this->input->post('city'),
                'guardian_is' => $this->input->post('guardian_is'),
                'pincode' => $this->input->post('pincode'),
                'religion' => $this->input->post('religion'),
                'cast' => $this->input->post('cast'),
                'previous_school' => $this->input->post('previous_school'),
                'dob' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('dob'))),
                'current_address' => $this->input->post('current_address'),
                'permanent_address' => $this->input->post('permanent_address'),
                'image' => 'uploads/student_images/no_image.png',
                'category_id' => $this->input->post('category_id'),
                'adhar_no' => $this->input->post('adhar_no'),
                'samagra_id' => $this->input->post('samagra_id'),
                'bank_account_no' => $this->input->post('bank_account_no'),
                'bank_name' => $this->input->post('bank_name'),
                'ifsc_code' => $this->input->post('ifsc_code'),
                'father_name' => $this->input->post('father_name'),
                'father_phone' => $this->input->post('father_phone'),
                'father_occupation' => $this->input->post('father_occupation'),
                'mother_name' => $this->input->post('mother_name'),
                'mother_phone' => $this->input->post('mother_phone'),
                'mother_occupation' => $this->input->post('mother_occupation'),
                'guardian_occupation' => $this->input->post('guardian_occupation'),
                'guardian_email' => $this->input->post('guardian_email'),
                'gender' => $this->input->post('gender'),
                'guardian_name' => $this->input->post('guardian_name'),
                'guardian_relation' => $this->input->post('guardian_relation'),
                'guardian_phone' => $this->input->post('guardian_phone'),
                'guardian_address' => $this->input->post('guardian_address'),
                'vehroute_id' => $vehroute_id,
                'hostel_room_id' => $hostel_room_id,
                'school_house_id' => $this->input->post('house'),
                'blood_group' => $this->input->post('blood_group'),
                'height' => $this->input->post('height'),
                'weight' => $this->input->post('weight'),
                'note' => $this->input->post('note'),
                'admission_fee' => $this->input->post('admission_fee'),
                'registration_fee' => $this->input->post('registration_fee'),
                'monthly_fee' => $this->input->post('monthly_fee'),
                'discount_fee' => $this->input->post('discount_fee'),
                'final_fee' => $this->input->post('final_fee'),
                'is_active' => 'yes',
                'measurement_date' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('measure_date')))
            );

            if ($this->sch_setting_detail->adm_auto_insert) {
                $admission_no         = $this->getNextAdmissionNo();
                $data['admission_no'] = $admission_no;
            } else {
                $admission_no         = $this->input->post('admission_no');
                $data['admission_no'] = $admission_no;
            }


            $insert_id = $this->student_model->add($data);

            $data_new = array(
                'student_id' => $insert_id,
                'class_id' => $class_id,
                'section_id' => $section_id,
                'session_id' => $session,
                'fees_discount' => $fees_discount
            );
            $student_session_id = $this->student_model->add_student_session_fee_generate($data_new);
            $user_password = $this->role->get_random_password($chars_min = 6, $chars_max = 6, $use_upper_case = false, $include_numbers = true, $include_special_chars = false);
            $sibling_id = $this->input->post('sibling_id');
            $data_student_login = array(
                'username' => $this->student_login_prefix . $insert_id,
                'password' => $user_password,
                'user_id' => $insert_id,
                'role' => 'student'
            );
            $this->user_model->add($data_student_login);


            $data_fees_discount = array (
                'session_id' => $this->Setting_model->getCurrentSession(),
                'name' =>  'Dis-' . $this->input->post('discount_fee'),
                'code' =>  $admission_no,
                'amount' => $this->input->post('discount_fee')
            );
            $fees_discount_id = $this->feediscount_model->add($data_fees_discount);

            $data_student_fees_discount = array(
                'student_session_id' => $student_session_id,
                'fees_discount_id' => $fees_discount_id
            );

            $this->feediscount_model->allotdiscount($data_student_fees_discount);

            if ($sibling_id > 0) {
                $student_sibling = $this->student_model->get($sibling_id);
                $update_student = array(
                    'id' => $insert_id,
                    'parent_id' => $student_sibling['parent_id'],
                );
                $student_sibling = $this->student_model->add($update_student);
            } else {
                $parent_password = $this->role->get_random_password($chars_min = 6, $chars_max = 6, $use_upper_case = false, $include_numbers = true, $include_special_chars = false);
                $temp = $insert_id;
                $data_parent_login = array(
                    'username' => $this->parent_login_prefix . $insert_id,
                    'password' => $parent_password,
                    'user_id' => 0,
                    'role' => 'parent',
                    'childs' => $temp
                );

                $ins_parent_id = $this->user_model->add($data_parent_login);
                $update_student = array(
                    'id' => $insert_id,
                    'parent_id' => $ins_parent_id
                );
                $this->student_model->add($update_student);
            }

            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $fileInfo = pathinfo($_FILES["file"]["name"]);
                $img_name = $insert_id . '.' . $fileInfo['extension'];
                move_uploaded_file($_FILES["file"]["tmp_name"], "./uploads/student_images/" . $img_name);
                $data_img = array('id' => $insert_id, 'image' => 'uploads/student_images/' . $img_name);
                $this->student_model->add($data_img);
            }

            if (isset($_FILES["father_pic"]) && !empty($_FILES['father_pic']['name'])) {
                $fileInfo = pathinfo($_FILES["father_pic"]["name"]);
                $img_name = $insert_id . "father" . '.' . $fileInfo['extension'];
                move_uploaded_file($_FILES["father_pic"]["tmp_name"], "./uploads/student_images/" . $img_name);
                $data_img = array('id' => $insert_id, 'father_pic' => 'uploads/student_images/' . $img_name);
                $this->student_model->add($data_img);
            }
            if (isset($_FILES["mother_pic"]) && !empty($_FILES['mother_pic']['name'])) {
                $fileInfo = pathinfo($_FILES["mother_pic"]["name"]);
                $img_name = $insert_id . "mother" . '.' . $fileInfo['extension'];
                move_uploaded_file($_FILES["mother_pic"]["tmp_name"], "./uploads/student_images/" . $img_name);
                $data_img = array('id' => $insert_id, 'mother_pic' => 'uploads/student_images/' . $img_name);
                $this->student_model->add($data_img);
            }

            if (isset($_FILES["guardian_pic"]) && !empty($_FILES['guardian_pic']['name'])) {
                $fileInfo = pathinfo($_FILES["guardian_pic"]["name"]);
                $img_name = $insert_id . "guardian" . '.' . $fileInfo['extension'];
                move_uploaded_file($_FILES["guardian_pic"]["tmp_name"], "./uploads/student_images/" . $img_name);
                $data_img = array('id' => $insert_id, 'guardian_pic' => 'uploads/student_images/' . $img_name);
                $this->student_model->add($data_img);
            }

            if (isset($_FILES["first_doc"]) && !empty($_FILES['first_doc']['name'])) {
                $uploaddir = './uploads/student_documents/' . $insert_id . '/';
                if (!is_dir($uploaddir) && !mkdir($uploaddir)) {
                    die("Error creating folder $uploaddir");
                }
                $fileInfo = pathinfo($_FILES["first_doc"]["name"]);
                $first_title = $this->input->post('first_title');
                $file_name = $_FILES['first_doc']['name'];
                $exp = explode(' ', $file_name);
                $imp = implode('_',$exp);
                $img_name = $uploaddir .$imp;
                move_uploaded_file($_FILES["first_doc"]["tmp_name"], $img_name);
                $data_img = array('student_id' => $insert_id, 'title' => $first_title, 'doc' =>$imp);
                $this->student_model->adddoc($data_img);
            }
            if (isset($_FILES["second_doc"]) && !empty($_FILES['second_doc']['name'])) {
                $uploaddir = './uploads/student_documents/' . $insert_id . '/';
                if (!is_dir($uploaddir) && !mkdir($uploaddir)) {
                    die("Error creating folder $uploaddir");
                }
                $fileInfo = pathinfo($_FILES["second_doc"]["name"]);
                $second_title = $this->input->post('second_title');
                $file_name = $_FILES['second_doc']['name'];
                $exp = explode(' ', $file_name);
                $imp = implode('_',$exp);
                $img_name = $uploaddir .$imp;
                move_uploaded_file($_FILES["second_doc"]["tmp_name"], $img_name);
                $data_img = array('student_id' => $insert_id, 'title' => $second_title, 'doc' =>$imp);
                $this->student_model->adddoc($data_img);
            }

            if (isset($_FILES["fourth_doc"]) && !empty($_FILES['fourth_doc']['name'])) {
                $uploaddir = './uploads/student_documents/' . $insert_id . '/';
                if (!is_dir($uploaddir) && !mkdir($uploaddir)) {
                    die("Error creating folder $uploaddir");
                }
                $fileInfo = pathinfo($_FILES["fourth_doc"]["name"]);
                $fourth_title = $this->input->post('fourth_title');
                $file_name = $_FILES['fourth_doc']['name'];
                $exp = explode(' ', $file_name);
                $imp = implode('_',$exp);
                $img_name = $uploaddir .$imp;
                move_uploaded_file($_FILES["fourth_doc"]["tmp_name"], $img_name);
                $data_img = array('student_id' => $insert_id, 'title' => $fourth_title, 'doc' =>$imp);
                $this->student_model->adddoc($data_img);
            }
            if (isset($_FILES["fifth_doc"]) && !empty($_FILES['fifth_doc']['name'])) {
                $uploaddir = './uploads/student_documents/' . $insert_id . '/';
                if (!is_dir($uploaddir) && !mkdir($uploaddir)) {
                    die("Error creating folder $uploaddir");
                }
                $fileInfo = pathinfo($_FILES["fifth_doc"]["name"]);
                $fifth_title = $this->input->post('fifth_title');
                $file_name = $_FILES['fifth_doc']['name'];
                $exp = explode(' ', $file_name);
                $imp = implode('_',$exp);
                $img_name = $uploaddir .$imp;

                move_uploaded_file($_FILES["fifth_doc"]["tmp_name"], $img_name);
                $data_img = array('student_id' => $insert_id, 'title' => $fifth_title, 'doc' =>$imp);
                $this->student_model->adddoc($data_img);
            }
            $class = $this->class_model->get($class_id);
            $section = $this->section_model->get($section_id);
            $student = $insert_id;
            $msg = "Dear " . $this->input->post('firstname') . " " . $this->input->post('lastname') .
                ", your admission is confirm in Class: " . $class['class'] .
                ", Section: " . $section['section'] . " for Session: " .  $session  . ", for more detail contact System Admin.";
            //$sender_details = array('student_id' => $insert_id, 'contact_no' => $this->input->post('guardian_phone'), 'email' => $this->input->post('guardian_email'),'notification_to'=>array('app_key'));
            /*$type = "json";
            $id = "rchnisschool";
            $pass = "school688";
            $lang = "English";
            $mask = "NIS-SCHOOL";
            //Data for text message
            $to = $this->input->post('guardian_phone');
            $message = $msg;
            $message = urlencode($message);
            //   // Prepare data for POST request
            $data234 = "id=" . $id . "&pass=" . $pass . "&msg=" . $message . "&to=" . $to . "&lang=" . $lang . "&mask=" . $mask . "&type=" . $type;
            //   // Send the POST request with cURL
            $ch = curl_init('http://www.outreach.pk/api/sendsms.php/sendsms/url');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data234);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            //This is the result from Outreach
            curl_close($ch);*/
            //$this->mailsmsconf->mailsms('student_admission', $sender_details);

            $student_login_detail = array('id' => $insert_id, 'credential_for' => 'student', 'username' => $this->student_login_prefix . $insert_id, 'password' => $user_password, 'contact_no' => $this->input->post('mobileno'), 'email' => $this->input->post('email'),'notification_to'=>array('app_key'));

            //$this->mailsmsconf->mailsms('login_credential', $student_login_detail);


            if ($sibling_id > 0) {

            } else {
                /*$parent_login_detail = array('id' => $insert_id, 'credential_for' => 'parent', 'username' => $this->parent_login_prefix . $insert_id, 'password' => $parent_password, 'contact_no' => $this->input->post('guardian_phone'), 'email' => $this->input->post('guardian_email'),'notification_to'=>array('parent_app_key'));

                $this->mailsmsconf->mailsms('login_credential', $parent_login_detail);*/
            }

            $this->session->set_flashdata('msg', '<div class="alert alert-success">Student added Successfully</div>');
            redirect('student/create');
        }
    }

    public function create_doc()
    {

        $this->form_validation->set_rules('first_title', $this->lang->line('title'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('first_doc', $this->lang->line('document'), 'callback_handle_uploadcreate_doc');

        if ($this->form_validation->run() == false) {
            $msg = array(
                'first_title' => form_error('first_title'),
                'first_doc'   => form_error('first_doc'),
            );
            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {
            $student_id = $this->input->post('student_id');
            if (isset($_FILES["first_doc"]) && !empty($_FILES['first_doc']['name'])) {
                $uploaddir = './uploads/student_documents/' . $student_id . '/';
                if (!is_dir($uploaddir) && !mkdir($uploaddir)) {
                    die("Error creating folder $uploaddir");
                }

                $fileInfo    = pathinfo($_FILES["first_doc"]["name"]);
                $first_title = $this->input->post('first_title');
                $file_name   = $_FILES['first_doc']['name'];
                $exp         = explode(' ', $file_name);
                $imp         = implode('_', $exp);
                $img_name    = $uploaddir . basename($imp);
                move_uploaded_file($_FILES["first_doc"]["tmp_name"], $img_name);
                $data_img = array('student_id' => $student_id, 'title' => $first_title, 'doc' => $imp);
                $this->student_model->adddoc($data_img);

            }

            $msg   = $this->lang->line('success_message');
            $array = array('status' => 'success', 'error' => '', 'message' => $msg);

        }
        echo json_encode($array);

    }

    public function handle_uploadcreate_doc()
    {

        $image_validate = $this->config->item('file_validate');
        $result         = $this->filetype_model->get();
        if (isset($_FILES["first_doc"]) && !empty($_FILES['first_doc']['name'])) {

            $file_type = $_FILES["first_doc"]['type'];
            $file_size = $_FILES["first_doc"]["size"];
            $file_name = $_FILES["first_doc"]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->file_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->file_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mtype = finfo_file($finfo, $_FILES['first_doc']['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mtype, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_uploadcreate_doc', $this->lang->line('file_type_not_allowed'));
                return false;
            }

            if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                $this->form_validation->set_message('handle_uploadcreate_doc', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($file_size > $result->file_size) {
                $this->form_validation->set_message('handle_uploadcreate_doc', $this->lang->line('file_size_shoud_be_less_than') . number_format($image_validate['upload_size'] / 1048576, 2) . " MB");
                return false;
            }

            return true;
        } else {
            $this->form_validation->set_message('handle_uploadcreate_doc', $this->lang->line('the_file_field_is_required'));
            return false;
        }
        return true;
    }

    public function handle_upload()
    {

        $image_validate = $this->config->item('image_validate');
        $result         = $this->filetype_model->get();
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {

            $file_type = $_FILES["file"]['type'];
            $file_size = $_FILES["file"]["size"];
            $file_name = $_FILES["file"]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->image_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->image_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = @getimagesize($_FILES['file']['tmp_name'])) {

                if (!in_array($files['mime'], $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', 'File Type Not Allowed');
                    return false;
                }

                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_upload', 'Extension Not Allowed');
                    return false;
                }
                if ($file_size > $result->image_size) {
                    $this->form_validation->set_message('handle_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($image_validate['upload_size'] / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_upload', "File Type / Extension Error Uploading  Image");
                return false;
            }

            return true;
        }
        return true;
    }

    public function handle_father_upload()
    {

        $image_validate = $this->config->item('image_validate');
        $result         = $this->filetype_model->get();
        if (isset($_FILES["father_pic"]) && !empty($_FILES['father_pic']['name'])) {

            $file_type = $_FILES["father_pic"]['type'];
            $file_size = $_FILES["father_pic"]["size"];
            $file_name = $_FILES["father_pic"]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->image_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->image_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = @getimagesize($_FILES['father_pic']['tmp_name'])) {

                if (!in_array($files['mime'], $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_father_upload', 'File Type Not Allowed');
                    return false;
                }

                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_father_upload', 'Extension Not Allowed');
                    return false;
                }
                if ($file_size > $result->image_size) {
                    $this->form_validation->set_message('handle_father_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($image_validate['upload_size'] / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_father_upload', "File Type / Extension Error Uploading  Image");
                return false;
            }

            return true;
        }
        return true;
    }

    public function handle_mother_upload()
    {

        $image_validate = $this->config->item('image_validate');
        $result         = $this->filetype_model->get();
        if (isset($_FILES["mother_pic"]) && !empty($_FILES['mother_pic']['name'])) {

            $file_type = $_FILES["mother_pic"]['type'];
            $file_size = $_FILES["mother_pic"]["size"];
            $file_name = $_FILES["mother_pic"]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->image_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->image_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = @getimagesize($_FILES['mother_pic']['tmp_name'])) {

                if (!in_array($files['mime'], $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_mother_upload', 'File Type Not Allowed');
                    return false;
                }

                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_mother_upload', 'Extension Not Allowed');
                    return false;
                }
                if ($file_size > $result->image_size) {
                    $this->form_validation->set_message('handle_mother_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($image_validate['upload_size'] / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_mother_upload', "File Type / Extension Error Uploading  Image");
                return false;
            }

            return true;
        }
        return true;
    }

    public function handle_guardian_upload()
    {

        $image_validate = $this->config->item('image_validate');
        $result         = $this->filetype_model->get();
        if (isset($_FILES["guardian_pic"]) && !empty($_FILES['guardian_pic']['name'])) {

            $file_type = $_FILES["guardian_pic"]['type'];
            $file_size = $_FILES["guardian_pic"]["size"];
            $file_name = $_FILES["guardian_pic"]["name"];

            $allowed_extension = array_map('trim', array_map('strtolower', explode(',', $result->image_extension)));
            $allowed_mime_type = array_map('trim', array_map('strtolower', explode(',', $result->image_mime)));
            $ext               = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($files = @getimagesize($_FILES['guardian_pic']['tmp_name'])) {

                if (!in_array($files['mime'], $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_guardian_upload', 'File Type Not Allowed');
                    return false;
                }

                if (!in_array($ext, $allowed_extension) || !in_array($file_type, $allowed_mime_type)) {
                    $this->form_validation->set_message('handle_guardian_upload', 'Extension Not Allowed');
                    return false;
                }
                if ($file_size > $result->image_size) {
                    $this->form_validation->set_message('handle_guardian_upload', $this->lang->line('file_size_shoud_be_less_than') . number_format($image_validate['upload_size'] / 1048576, 2) . " MB");
                    return false;
                }
            } else {
                $this->form_validation->set_message('handle_guardian_upload', "File Type / Extension Error Uploading  Image");
                return false;
            }

            return true;
        }
        return true;
    }

    public function sendpassword()
    {

        $student_login_detail = array('id' => $this->input->post('student_id'), 'credential_for' => 'student', 'username' => $this->input->post('username'), 'password' => $this->input->post('password'), 'contact_no' => $this->input->post('contact_no'), 'email' => $this->input->post('email'));

        $msg = $this->mailsmsconf->mailsms('login_credential', $student_login_detail);
    }

    public function send_parent_password()
    {
        $parent_login_detail = array('id' => $this->input->post('student_id'), 'credential_for' => 'parent', 'username' => $this->input->post('username'), 'password' => $this->input->post('password'), 'contact_no' => $this->input->post('contact_no'), 'email' => $this->input->post('email'));

        $msg = $this->mailsmsconf->mailsms('login_credential', $parent_login_detail);
    }

    public function import()
    {
        if (!$this->rbac->hasPrivilege('import_student', 'can_view')) {
            access_denied();
        }
        $data['title']      = 'Import Student';
        $data['title_list'] = 'Recently Added Student';
        $session            = $this->setting_model->getCurrentSession();
        $class              = $this->class_model->get('', $classteacher = 'yes');
        $data['classlist']  = $class;
        $userdata           = $this->customlib->getUserData();

        $category = $this->category_model->get();

        $fields = array('admission_no', 'roll_no', 'firstname', 'middlename', 'lastname', 'gender', 'dob', 'category_id', 'religion', 'cast', 'mobileno', 'email', 'admission_date', 'blood_group', 'school_house_id', 'height', 'weight', 'measurement_date', 'father_name', 'father_phone', 'father_occupation', 'mother_name', 'mother_phone', 'mother_occupation', 'guardian_is', 'guardian_name', 'guardian_relation', 'guardian_email', 'guardian_phone', 'guardian_occupation', 'guardian_address', 'current_address', 'permanent_address', 'bank_account_no', 'bank_name', 'ifsc_code', 'adhar_no', 'samagra_id', 'rte', 'previous_school', 'note');

        $data["fields"]       = $fields;
        $data['categorylist'] = $category;
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_csv_upload');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('student/import', $data);
            $this->load->view('layout/footer', $data);
        } else {

            $student_categorize = 'class';
            if ($student_categorize == 'class') {
                $section = 0;
            } else if ($student_categorize == 'section') {

                $section = $this->input->post('section_id');
            }
            $class_id   = $this->input->post('class_id');
            $section_id = $this->input->post('section_id');

            $session = $this->setting_model->getCurrentSession();
            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                if ($ext == 'csv') {
                    $file = $_FILES['file']['tmp_name'];
                    $this->load->library('CSVReader');
                    $result = $this->csvreader->parse_file($file);

                    if (!empty($result)) {
                        $rowcount = 0;
                        for ($i = 1; $i <= count($result); $i++) {

                            $student_data[$i] = array();
                            $n                = 0;
                            foreach ($result[$i] as $key => $value) {

                                $student_data[$i][$fields[$n]] = $this->encoding_lib->toUTF8($result[$i][$key]);

                                $student_data[$i]['is_active'] = 'yes';

                                if (date('Y-m-d', strtotime($result[$i]['date_of_birth'])) === $result[$i]['date_of_birth']) {
                                    $student_data[$i]['dob'] = date('Y-m-d', strtotime($result[$i]['date_of_birth']));
                                } else {
                                    $student_data[$i]['dob'] = null;
                                }

                                if (date('Y-m-d', strtotime($result[$i]['measurement_date'])) === $result[$i]['measurement_date']) {
                                    $student_data[$i]['measurement_date'] = date('Y-m-d', strtotime($result[$i]['measurement_date']));
                                } else {
                                    $student_data[$i]['measurement_date'] = '';
                                }

                                if (date('Y-m-d', strtotime($result[$i]['admission_date'])) === $result[$i]['admission_date']) {
                                    $student_data[$i]['admission_date'] = date('Y-m-d', strtotime($result[$i]['admission_date']));
                                } else {
                                    $student_data[$i]['admission_date'] = null;
                                }
                                $n++;
                            }

                            $roll_no                           = $student_data[$i]["roll_no"];
                            $adm_no                            = $student_data[$i]["admission_no"];
                            $mobile_no                         = $student_data[$i]["mobileno"];
                            $email                             = $student_data[$i]["email"];
                            $guardian_phone                    = $student_data[$i]["guardian_phone"];
                            $guardian_email                    = $student_data[$i]["guardian_email"];
                            $data_setting                      = array();
                            $data_setting['id']                = $this->sch_setting_detail->id;
                            $data_setting['adm_auto_insert']   = $this->sch_setting_detail->adm_auto_insert;
                            $data_setting['adm_update_status'] = $this->sch_setting_detail->adm_update_status;
                            //-------------------------

                            if ($this->sch_setting_detail->adm_auto_insert) {
                                if ($this->sch_setting_detail->adm_update_status) {
                                    $last_student         = $this->student_model->lastFourDigitAdmission();
                                    $last_numeric         = 9;
                                    if (!empty($last_student) && !empty($last_student->admission_no)) {
                                        $last_numeric = (int)$last_student->admission_no;
                                        $last_numeric = max($last_numeric, 9);
                                    }
                                    $next_numeric = $last_numeric + 1;
                                    $admission_no = $next_numeric < 10000 ? sprintf("%04d", $next_numeric) : (string)$next_numeric;
                                    $student_data[$i]["admission_no"] = $admission_no;
                                } else {
                                    $start_from   = 10;
                                    $admission_no = sprintf("%04d", $start_from);
                                    $student_data[$i]["admission_no"] = $admission_no;
                                }

                                $admission_no_exists = $this->student_model->check_adm_exists($admission_no);
                                if ($admission_no_exists) {
                                    $insert = "";
                                } else {
                                    $insert_id = $this->student_model->add($student_data[$i], $data_setting);
                                }
                            } else {

                                if ($this->form_validation->is_unique($adm_no, 'students.admission_no')) {
                                    $insert_id = $this->student_model->add($student_data[$i], $data_setting);
                                } else {
                                    $insert_id = "";
                                }
                            }

                            //-------------------------
                            if (!empty($insert_id)) {
                                $data_new = array(
                                    'student_id' => $insert_id,
                                    'class_id'   => $class_id,
                                    'section_id' => $section_id,
                                    'session_id' => $session,
                                );

                                $this->student_model->add_student_session($data_new);
                                $user_password = $this->role->get_random_password($chars_min = 6, $chars_max = 6, $use_upper_case = false, $include_numbers = true, $include_special_chars = false);
                                $sibling_id    = $this->input->post('sibling_id');

                                $data_student_login = array(
                                    'username' => $this->student_login_prefix . $insert_id,
                                    'password' => $user_password,
                                    'user_id'  => $insert_id,
                                    'role'     => 'student',
                                );

                                $this->user_model->add($data_student_login);
                                $parent_password = $this->role->get_random_password($chars_min = 6, $chars_max = 6, $use_upper_case = false, $include_numbers = true, $include_special_chars = false);

                                $temp              = $insert_id;
                                $data_parent_login = array(
                                    'username' => $this->parent_login_prefix . $insert_id,
                                    'password' => $parent_password,
                                    'user_id'  => $insert_id,
                                    'role'     => 'parent',
                                    'childs'   => $temp,
                                );

                                $ins_id         = $this->user_model->add($data_parent_login);
                                $update_student = array(
                                    'id'        => $insert_id,
                                    'parent_id' => $ins_id,
                                );

                                $this->student_model->add($update_student);
                                $sender_details = array('student_id' => $insert_id, 'contact_no' => $guardian_phone, 'email' => $guardian_email);
                                $this->mailsmsconf->mailsms('student_admission', $sender_details);

                                $student_login_detail = array('id' => $insert_id, 'credential_for' => 'student', 'username' => $this->student_login_prefix . $insert_id, 'password' => $user_password, 'contact_no' => $mobile_no, 'email' => $email);
                                $this->mailsmsconf->mailsms('login_credential', $student_login_detail);

                                $parent_login_detail = array('id' => $insert_id, 'credential_for' => 'parent', 'username' => $this->parent_login_prefix . $insert_id, 'password' => $parent_password, 'contact_no' => $guardian_phone, 'email' => $guardian_email);

                                $this->mailsmsconf->mailsms('login_credential', $parent_login_detail);

                                $data['csvData'] = $result;
                                $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">' . $this->lang->line('students_imported_successfully') . '</div>');

                                $rowcount++;
                                $this->session->set_flashdata('msg', '<div class="alert alert-success text-center">Total ' . count($result) . " records found in CSV file. Total " . $rowcount . ' records imported successfully.</div>');

                            } else {

                                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . $this->lang->line('record_already_exists') . '</div>');
                            }
                        }
                    } else {

                        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . $this->lang->line('no_record_found') . '</div>');
                    }
                } else {

                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-center">' . $this->lang->line('please_upload_CSV_file_only') . '</div>');
                }
            }

            redirect('student/import');
        }
    }

    public function handle_csv_upload()
    {
        $error = "";
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('csv');
            $mimes       = array('text/csv',
                'text/plain',
                'application/csv',
                'text/comma-separated-values',
                'application/excel',
                'application/vnd.ms-excel',
                'application/vnd.msexcel',
                'text/anytext',
                'application/octet-stream',
                'application/txt');
            $temp      = explode(".", $_FILES["file"]["name"]);
            $extension = end($temp);
            if ($_FILES["file"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if (!in_array($_FILES['file']['type'], $mimes)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('file_type_not_allowed'));
                return false;
            }
            if (!in_array($extension, $allowedExts)) {
                $error .= "Error opening the file<br />";
                $this->form_validation->set_message('handle_csv_upload', $this->lang->line('extension_not_allowed'));
                return false;
            }
            if ($error == "") {
                return true;
            }
        } else {
            $this->form_validation->set_message('handle_csv_upload', $this->lang->line('please_select_file'));
            return false;
        }
    }

    public function edit($id)
    {
        if (!$this->rbac->hasPrivilege('student', 'can_edit')) {
            access_denied();
        }
        $data['title']   = 'Edit Student';
        $data['id']      = $id;
        $student         = $this->student_model->get($id);
        $genderList      = $this->customlib->getGender();
        $data['student'] = $student;

        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $data['genderList']      = $genderList;
        $session                 = $this->setting_model->getCurrentSession();
        $vehroute_result         = $this->vehroute_model->get();
        $data['vehroutelist']    = $vehroute_result;
        $class                   = $this->class_model->get();
        $setting_result          = $this->setting_model->get();

        $data["student_categorize"] = 'class';
        $data['classlist']          = $class;
        $category                   = $this->category_model->get();
        $data['categorylist']       = $category;
        $hostelList                 = $this->hostel_model->get();
        $data['hostelList']         = $hostelList;
        $houses                     = $this->student_model->gethouselist();
        $data['houses']             = $houses;
        $data["bloodgroup"]         = $this->blood_group;
        $siblings                   = $this->student_model->getMySiblings($student['parent_id'], $student['id']);
        $data['siblings']           = $siblings;
        $data['siblings_counts']    = count($siblings);
        $custom_fields              = $this->customfield_model->getByBelong('students');
        $data['sch_setting']        = $this->sch_setting_detail;

        foreach ($custom_fields as $custom_fields_key => $custom_fields_value) {
            if ($custom_fields_value['validation']) {
                $custom_fields_id   = $custom_fields_value['id'];
                $custom_fields_name = $custom_fields_value['name'];
                $this->form_validation->set_rules("custom_fields[students][" . $custom_fields_id . "]", $custom_fields_name, 'trim|required');
            }
        }

        $this->form_validation->set_rules('firstname', $this->lang->line('first_name'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('dob', $this->lang->line('date_of_birth'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('gender', $this->lang->line('gender'), 'trim|required|xss_clean');

        if ($this->sch_setting_detail->guardian_name) {
            $this->form_validation->set_rules('guardian_name', $this->lang->line('guardian_name'), 'trim|required|xss_clean');
            $this->form_validation->set_rules('guardian_is', $this->lang->line('guardian'), 'trim|required|xss_clean');
        }
        if ($this->sch_setting_detail->guardian_phone) {
            $this->form_validation->set_rules('guardian_phone', $this->lang->line('guardian_phone'), 'trim|required|xss_clean');
        }

        $this->form_validation->set_rules(
            'email', $this->lang->line('email'), array(
                'valid_email',
                array('check_student_email_exists', array($this->student_model, 'check_student_email_exists')),
            )
        );
        $this->form_validation->set_rules('guardian_email', $this->lang->line('guardian_email'), 'trim|valid_email|xss_clean');
        if (!$this->sch_setting_detail->adm_auto_insert) {

            $this->form_validation->set_rules('admission_no', $this->lang->line('admission_no'), array('required', array('check_admission_no_exists', array($this->student_model, 'valid_student_admission_no'))));
        }

        $this->form_validation->set_rules('file', $this->lang->line('image'), 'callback_handle_upload');
        $this->form_validation->set_rules('father_pic', $this->lang->line('image'), 'callback_handle_father_upload');
        $this->form_validation->set_rules('mother_pic', $this->lang->line('image'), 'callback_handle_mother_upload');
        $this->form_validation->set_rules('guardian_pic', $this->lang->line('image'), 'callback_handle_guardian_upload');
        if ($this->form_validation->run() == false) {
            $this->load->view('layout/header', $data);
            $this->load->view('student/studentEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {

            $custom_field_post = $this->input->post("custom_fields[students]");
            if (isset($custom_field_post)) {
                $custom_value_array = array();
                foreach ($custom_field_post as $key => $value) {
                    $check_field_type = $this->input->post("custom_fields[students][" . $key . "]");
                    $field_value      = is_array($check_field_type) ? implode(",", $check_field_type) : $check_field_type;
                    $array_custom     = array(
                        'belong_table_id' => $id,
                        'custom_field_id' => $key,
                        'field_value'     => $field_value,
                    );
                    $custom_value_array[] = $array_custom;
                }
                $this->customfield_model->updateRecord($custom_value_array, $id, 'students');
            }
            $student_id      = $this->input->post('student_id');
            $student         = $this->student_model->get($student_id);
            $sibling_id      = $this->input->post('sibling_id');
            $siblings_counts = $this->input->post('siblings_counts');
            $siblings        = $this->student_model->getMySiblings($student['parent_id'], $student_id);
            $total_siblings  = count($siblings);
            $class_id        = $this->input->post('class_id');
            $section_id      = $this->input->post('section_id');
            $hostel_room_id  = $this->input->post('hostel_room_id');
            $fees_discount   = $this->input->post('fees_discount');
            $vehroute_id     = $this->input->post('vehroute_id');
            if (empty($vehroute_id)) {
                $vehroute_id = 0;
            }
            if (empty($hostel_room_id)) {
                $hostel_room_id = 0;
            }

            $data = array(
                'id'                => $id,
                'firstname'         => $this->input->post('firstname'),
                'rte'               => $this->input->post('rte'),
                'state'             => $this->input->post('state'),
                'city'              => $this->input->post('city'),
                'pincode'           => $this->input->post('pincode'),
                'cast'              => $this->input->post('cast'),
                'previous_school'   => $this->input->post('previous_school'),
                'dob'               => $this->customlib->dateFormatToYYYYMMDD($this->input->post('dob')),
                'current_address'   => $this->input->post('current_address'),
                'permanent_address' => $this->input->post('permanent_address'),
                'adhar_no'          => $this->input->post('adhar_no'),
                'samagra_id'        => $this->input->post('samagra_id'),
                'bank_account_no'   => $this->input->post('bank_account_no'),
                'bank_name'         => $this->input->post('bank_name'),
                'ifsc_code'         => $this->input->post('ifsc_code'),

                'guardian_email'    => $this->input->post('guardian_email'),
                'gender'            => $this->input->post('gender'),
                'guardian_name'     => $this->input->post('guardian_name'),
                'guardian_relation' => $this->input->post('guardian_relation'),
                'guardian_phone'    => $this->input->post('guardian_phone'),
                'guardian_address'  => $this->input->post('guardian_address'),
                'vehroute_id'       => $vehroute_id,
                'hostel_room_id'    => $hostel_room_id,
                'note'              => $this->input->post('note'),
                'is_active'         => 'yes',
            );
            if ($this->sch_setting_detail->guardian_occupation) {
                $data['guardian_occupation'] = $this->input->post('guardian_occupation');
            }
            $house             = $this->input->post('house');
            $blood_group       = $this->input->post('blood_group');
            $measurement_date  = $this->input->post('measure_date');
            $roll_no           = $this->input->post('roll_no');
            $lastname          = $this->input->post('lastname');
            $middlename        = $this->input->post('middlename');
            $category_id       = $this->input->post('category_id');
            $religion          = $this->input->post('religion');
            $mobileno          = $this->input->post('mobileno');
            $email             = $this->input->post('email');
            $admission_date    = $this->input->post('admission_date');
            $height            = $this->input->post('height');
            $weight            = $this->input->post('weight');
            $father_name       = $this->input->post('father_name');
            $father_phone      = $this->input->post('father_phone');
            $father_occupation = $this->input->post('father_occupation');
            $mother_name       = $this->input->post('mother_name');
            $mother_phone      = $this->input->post('mother_phone');
            $mother_occupation = $this->input->post('mother_occupation');

            if ($this->sch_setting_detail->guardian_name) {
                $data['guardian_is'] = $this->input->post('guardian_is');
            }

            if (isset($measurement_date)) {
                $data['measurement_date'] = $this->customlib->dateFormatToYYYYMMDD($this->input->post('measure_date'));
            }

            if (isset($house)) {
                $data['school_house_id'] = $this->input->post('house');
            }
            if (isset($blood_group)) {

                $data['blood_group'] = $this->input->post('blood_group');
            }

            if (isset($roll_no)) {

                $data['roll_no'] = $this->input->post('roll_no');
            }

            if (isset($lastname)) {

                $data['lastname'] = $this->input->post('lastname');
            }

            if (isset($middlename)) {
                $data['middlename'] = $this->input->post('middlename');
            }

            if (isset($category_id)) {

                $data['category_id'] = $this->input->post('category_id');
            }

            if (isset($religion)) {

                $data['religion'] = $this->input->post('religion');
            }

            if (isset($mobileno)) {

                $data['mobileno'] = $this->input->post('mobileno');
            }

            if (isset($email)) {

                $data['email'] = $this->input->post('email');
            }

            if (isset($admission_date)) {

                $data['admission_date'] = $this->customlib->dateFormatToYYYYMMDD($this->input->post('admission_date'));
            }

            if (isset($height)) {

                $data['height'] = $this->input->post('height');
            }

            if (isset($weight)) {

                $data['weight'] = $this->input->post('weight');
            }

            if (isset($father_name)) {

                $data['father_name'] = $this->input->post('father_name');
            }

            if (isset($father_phone)) {

                $data['father_phone'] = $this->input->post('father_phone');
            }

            if (isset($father_occupation)) {

                $data['father_occupation'] = $this->input->post('father_occupation');
            }

            if (isset($mother_name)) {

                $data['mother_name'] = $this->input->post('mother_name');
            }

            if (isset($mother_phone)) {

                $data['mother_phone'] = $this->input->post('mother_phone');
            }

            if (isset($mother_occupation)) {

                $data['mother_occupation'] = $this->input->post('mother_occupation');
            }

            $default_image = array('uploads/student_images/default_female.jpg', 'uploads/student_images/default_male.jpg');
            if (in_array($student['image'], $default_image)) {
                if ($this->input->post('gender') == 'Female') {
                    $data['image'] = 'uploads/student_images/default_female.jpg';
                } else {
                    $data['image'] = 'uploads/student_images/default_male.jpg';
                }
            }

            if (!$this->sch_setting_detail->adm_auto_insert) {

                $data['admission_no'] = $this->input->post('admission_no');
            }
            $this->student_model->add($data);
            $data_new = array(
                'student_id'    => $id,
                'class_id'      => $class_id,
                'section_id'    => $section_id,
                'session_id'    => $session,
                'fees_discount' => $fees_discount,
            );
            $insert_id = $this->student_model->add_student_session($data_new);
            if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                $fileInfo = pathinfo($_FILES["file"]["name"]);
                $img_name = $id . '.' . $fileInfo['extension'];
                move_uploaded_file($_FILES["file"]["tmp_name"], "./uploads/student_images/" . $img_name);
                $data_img = array('id' => $id, 'image' => 'uploads/student_images/' . $img_name);
                $this->student_model->add($data_img);
            }

            if (isset($_FILES["father_pic"]) && !empty($_FILES['father_pic']['name'])) {
                $fileInfo = pathinfo($_FILES["father_pic"]["name"]);
                $img_name = $id . "father" . '.' . $fileInfo['extension'];
                move_uploaded_file($_FILES["father_pic"]["tmp_name"], "./uploads/student_images/" . $img_name);
                $data_img = array('id' => $id, 'father_pic' => 'uploads/student_images/' . $img_name);
                $this->student_model->add($data_img);
            }

            if (isset($_FILES["mother_pic"]) && !empty($_FILES['mother_pic']['name'])) {
                $fileInfo = pathinfo($_FILES["mother_pic"]["name"]);
                $img_name = $id . "mother" . '.' . $fileInfo['extension'];
                move_uploaded_file($_FILES["mother_pic"]["tmp_name"], "./uploads/student_images/" . $img_name);
                $data_img = array('id' => $id, 'mother_pic' => 'uploads/student_images/' . $img_name);
                $this->student_model->add($data_img);
            }

            if (isset($_FILES["guardian_pic"]) && !empty($_FILES['guardian_pic']['name'])) {
                $fileInfo = pathinfo($_FILES["guardian_pic"]["name"]);
                $img_name = $id . "guardian" . '.' . $fileInfo['extension'];
                move_uploaded_file($_FILES["guardian_pic"]["tmp_name"], "./uploads/student_images/" . $img_name);
                $data_img = array('id' => $id, 'guardian_pic' => 'uploads/student_images/' . $img_name);
                $this->student_model->add($data_img);
            }

            if (isset($siblings_counts) && ($total_siblings == $siblings_counts)) {
                //if there is no change in sibling
            } else if (!isset($siblings_counts) && $sibling_id == 0 && $total_siblings > 0) {
                // add for new parent
                $parent_password = $this->role->get_random_password($chars_min = 6, $chars_max = 6, $use_upper_case = false, $include_numbers = true, $include_special_chars = false);

                $data_parent_login = array(
                    'username' => $this->parent_login_prefix . $student_id . "_1",
                    'password' => $parent_password,
                    'user_id'  => "",
                    'role'     => 'parent',
                );

                $update_student = array(
                    'id'        => $student_id,
                    'parent_id' => 0,
                );
                $ins_id = $this->user_model->addNewParent($data_parent_login, $update_student);
            } else if ($sibling_id != 0) {
                //join to student with new parent
                $student_sibling = $this->student_model->get($sibling_id);
                $update_student  = array(
                    'id'        => $student_id,
                    'parent_id' => $student_sibling['parent_id'],
                );
                $student_sibling = $this->student_model->add($update_student);
            } else {

            }

            $this->session->set_flashdata('msg', '<div student="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            redirect('student/search');
        }
    }

    public function bulkdelete()
    {

        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'bulkdelete');
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['fields']          = $this->customfield_model->get_custom_fields('students', 1);
        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $class   = $this->input->post('class_id');
            $section = $this->input->post('section_id');
            $search  = $this->input->post('search');

            $data['searchby']    = "filter";
            $data['class_id']    = $this->input->post('class_id');
            $data['section_id']  = $this->input->post('section_id');
            $data['search_text'] = $this->input->post('search_text');
            $resultlist          = $this->student_model->searchByClassSection($class, $section);
            $data['resultlist']  = $resultlist;
            $title               = $this->classsection_model->getDetailbyClassSection($data['class_id'], $data['section_id']);
            $data['title']       = 'Student Details for ' . $title['class'] . "(" . $title['section'] . ")";
        }
        $this->load->view('layout/header', $data);
        $this->load->view('student/bulkdelete', $data);
        $this->load->view('layout/footer', $data);
    }

    public function search()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/search');
        $data['title']           = 'Student Search';
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['fields']          = $this->customfield_model->get_custom_fields('students', 1);
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;

        $this->load->view('layout/header', $data);
        $this->load->view('student/studentSearch', $data);
        $this->load->view('layout/footer', $data);

    }

    public function ajaxsearch()
    {
        $search_type = $this->input->post('search_type');
        if ($search_type == "search_filter") {
            $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        }

        if ($this->form_validation->run() == false && $search_type == "search_filter") {
            $error = array();
            if ($search_type == "search_filter") {
                $error['class_id'] = form_error('class_id');
            }

            $array = array('status' => 0, 'error' => $error);
            echo json_encode($array);
        } else {
            $search_type = $this->input->post('search_type');
            $search_text = $this->input->post('search_text');
            $class_id    = $this->input->post('class_id');
            $section_id  = $this->input->post('section_id');
            $params      = array('class_id' => $class_id, 'section_id' => $section_id, 'search_type' => $search_type, 'search_text' => $search_text);
            $array       = array('status' => 1, 'error' => '', 'params' => $params);
            echo json_encode($array);
        }
    }

    public function getByClassAndSection()
    {
        $class      = $this->input->get('class_id');
        $section    = $this->input->get('section_id');
        $resultlist = $this->student_model->searchByClassSection($class, $section);
        foreach ($resultlist as $key => $value) {
            $resultlist[$key]['full_name'] = $this->customlib->getFullName($value['firstname'], $value['middlename'], $value['lastname'], $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname);
            # code...
        }
        echo json_encode($resultlist);
    }

    public function getByClassAndSectionExcludeMe()
    {
        $class      = $this->input->get('class_id');
        $section    = $this->input->get('section_id');
        $student_id = $this->input->get('current_student_id');
        $resultlist = $this->student_model->searchByClassSectionWithoutCurrent($class, $section, $student_id);

        foreach ($resultlist as $key => $value) {
            $resultlist[$key]['full_name'] = $this->customlib->getFullName($value['firstname'], $value['middlename'], $value['lastname'], $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname);
            # code...
        }

        echo json_encode($resultlist);
    }

    public function getStudentRecordByID()
    {
        $student_id = $this->input->get('student_id');
        $resultlist = $this->student_model->get($student_id);

        foreach ($resultlist as $key => $value) {

            $resultlist['full_name'] = $this->customlib->getFullName($resultlist['firstname'], $resultlist['middlename'], $resultlist['lastname'], $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname);
        }

        echo json_encode($resultlist);
    }

    public function uploadimage($id)
    {
        $data['title'] = 'Add Image';
        $data['id']    = $id;
        $this->load->view('layout/header', $data);
        $this->load->view('student/uploadimage', $data);
        $this->load->view('layout/footer', $data);
    }

    public function doupload($id)
    {
        $config = array(
            'upload_path'   => "./uploads/student_images/",
            'allowed_types' => "gif|jpg|png|jpeg|df",
            'overwrite'     => true,
        );
        $config['file_name'] = $id . ".jpg";
        $this->upload->initialize($config);
        $this->load->library('upload', $config);
        if ($this->upload->do_upload()) {
            $data        = array('upload_data' => $this->upload->data());
            $upload_data = $this->upload->data();
            $data_record = array('id' => $id, 'image' => $upload_data['file_name']);
            $this->setting_model->add($data_record);

            $this->load->view('upload_success', $data);
        } else {
            $error = array('error' => $this->upload->display_errors());

            $this->load->view('file_view', $error);
        }
    }

    public function getlogindetail()
    {
        if (!$this->rbac->hasPrivilege('student_login_credential_report', 'can_view')) {
            access_denied();
        }
        $student_id   = $this->input->post('student_id');
        $examSchedule = $this->user_model->getStudentLoginDetails($student_id);
        echo json_encode($examSchedule);
    }

    public function getUserLoginDetails()
    {
        $studentid = $this->input->post("student_id");
        $result    = $this->user_model->getUserLoginDetails($studentid);
        echo json_encode($result);
    }

    public function guardianreport()
    {

        if (!$this->rbac->hasPrivilege('guardian_report', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');
        $this->session->set_userdata('sub_menu', 'Reports/student_information');
        $this->session->set_userdata('subsub_menu', 'Reports/student_information/guardian_report');
        $data['title']           = 'Student Guardian Report';
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $userdata                = $this->customlib->getUserData();
        $carray                  = array();

        if (!empty($data["classlist"])) {
            foreach ($data["classlist"] as $ckey => $cvalue) {

                $carray[] = $cvalue["id"];
            }
        }

        $class_id   = $this->input->post("class_id");
        $section_id = $this->input->post("section_id");

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {

            $resultlist         = $this->student_model->studentGuardianDetails($carray);
            $data["resultlist"] = "";
        } else {

            $resultlist         = $this->student_model->searchGuardianDetails($class_id, $section_id);
            $data["resultlist"] = $resultlist;
        }

        $this->load->view("layout/header", $data);
        $this->load->view("student/guardianReport", $data);
        $this->load->view("layout/footer", $data);
    }

    public function disablestudentslist()
    {
        if (!$this->rbac->hasPrivilege('disable_student', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/disablestudentslist');
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $result                  = $this->student_model->getdisableStudent();
        $data["resultlist"]      = array();
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $data['sch_setting']     = $this->sch_setting_detail;
        $userdata                = $this->customlib->getUserData();
        $carray                  = array();
        $reason_list             = array();
        if (!empty($data["classlist"])) {
            foreach ($data["classlist"] as $ckey => $cvalue) {

                $carray[] = $cvalue["id"];
            }
        }

        $button = $this->input->post('search');
        if ($this->input->server('REQUEST_METHOD') == "GET") {

        } else {
            $class       = $this->input->post('class_id');
            $section     = $this->input->post('section_id');
            $search      = $this->input->post('search');
            $search_text = $this->input->post('search_text');
            if (isset($search)) {
                if ($search == 'search_filter') {
                    $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
                    if ($this->form_validation->run() == false) {

                    } else {
                        $data['searchby']   = "filter";
                        $data['class_id']   = $this->input->post('class_id');
                        $data['section_id'] = $this->input->post('section_id');

                        $data['search_text'] = $this->input->post('search_text');
                        $resultlist          = $this->student_model->disablestudentByClassSection($class, $section);
                        $data['resultlist']  = $resultlist;
                        $title               = $this->classsection_model->getDetailbyClassSection($data['class_id'], $data['section_id']);
                        $data['title']       = 'Student Details for ' . $title['class'] . "(" . $title['section'] . ")";
                    }
                } else if ($search == 'search_full') {
                    $data['searchby'] = "text";

                    $data['search_text'] = trim($this->input->post('search_text'));
                    $resultlist          = $this->student_model->disablestudentFullText($search_text);

                    $data['resultlist'] = $resultlist;
                    $data['title']      = 'Search Details: ' . $data['search_text'];
                }
            }
        }

        $disable_reason = $this->disable_reason_model->get();

        foreach ($disable_reason as $key => $value) {
            $id               = $value['id'];
            $reason_list[$id] = $value;
        }

        $data['disable_reason'] = $reason_list;

        $this->load->view("layout/header", $data);
        $this->load->view("student/disablestudents", $data);
        $this->load->view("layout/footer", $data);
    }

    public function disablestudent($id)
    {

        $data = array('is_active' => "no", 'disable_at' => date("Y-m-d"));
        $this->student_model->disableStudent($id, $data);
        redirect("student/view/" . $id);
    }

    public function enablestudent($id)
    {

        $data = array('is_active' => "yes");
        $this->student_model->disableStudent($id, $data);
        echo "0";
    }

    public function savemulticlass()
    {

        $student_id       = '';
        $message          = "";
        $duplicate_record = 0;
        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('student_id', $this->lang->line('student_id'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('row_count[]', 'row_count[]', 'trim|required|xss_clean');

        if ($this->input->server('REQUEST_METHOD') == 'POST') {
            $total_rows = $this->input->post('row_count[]');
            if (!empty($total_rows)) {

                foreach ($total_rows as $key_rowcount => $row_count) {

                    $this->form_validation->set_rules('class_id_' . $row_count, $this->lang->line('class'), 'trim|required|xss_clean');

                    $this->form_validation->set_rules('section_id_' . $row_count, $this->lang->line('section'), 'trim|required|xss_clean');
                }
            }
        }

        if ($this->form_validation->run() == false) {

            $msg = array(
                'student_id'  => form_error('student_id'),
                'row_count[]' => form_error('row_count[]'),
            );

            if ($this->input->server('REQUEST_METHOD') == 'POST') {
                if (!empty($total_rows)) {

                    $total_rows = $this->input->post('row_count[]');
                    foreach ($total_rows as $key_rowcount => $row_count) {

                        $msg['class_id_' . $row_count]   = form_error('class_id_' . $row_count);
                        $msg['section_id_' . $row_count] = form_error('section_id_' . $row_count);
                    }
                }
            }
            if (!empty($msg)) {
                $message = $this->lang->line('something_went_wrong');
            }

            $array = array('status' => '0', 'error' => $msg, 'message' => $message);
        } else {

            $rowcount            = $this->input->post('row_count[]');
            $class_section_array = array();
            $duplicate_array     = array();
            foreach ($rowcount as $key_rowcount => $value_rowcount) {

                $array = array(
                    'class_id'   => $this->input->post('class_id_' . $value_rowcount),
                    'session_id' => $this->setting_model->getCurrentSession(),
                    'student_id' => $this->input->post('student_id'),
                    'section_id' => $this->input->post('section_id_' . $value_rowcount),
                );

                $class_section_array[] = $array;
                $duplicate_array[]     = $this->input->post('class_id_' . $value_rowcount) . "-" . $this->input->post('section_id_' . $value_rowcount);
            }

            foreach (array_count_values($duplicate_array) as $val => $c) {

                if ($c > 1) {
                    $duplicate_record = 1;
                    break;
                }
            }
            if ($duplicate_record) {

                $array = array('status' => 0, 'error' => '', 'message' => $this->lang->line('duplicate_entry'));
            } else {
                $this->studentsession_model->add($class_section_array, $this->input->post('student_id'));

                $array = array('status' => 1, 'error' => '', 'message' => $this->lang->line('success_message'));
            }
        }
        echo json_encode($array);
    }

    public function disable_reason()
    {

        $student_id = '';
        $this->form_validation->set_rules('reason', $this->lang->line('reason'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('disable_date', $this->lang->line('date'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {

            $msg = array(
                'reason' => form_error('reason'),
            );

            $array = array('status' => 'fail', 'error' => $msg, 'message' => '');
        } else {

            $data = array(
                'dis_reason' => $this->input->post('reason'),
                'dis_note'   => $this->input->post('note'),
                'id'         => $this->input->post('student_id'),
                'disable_at' => $this->customlib->dateFormatToYYYYMMDD($this->input->post('disable_date')),
                'is_active'  => 'no',
            );

            $this->student_model->add($data);

            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
        }
        echo json_encode($array);
    }

    public function ajax_delete()
    {

        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('student[]', $this->lang->line('student'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {

            $msg = array(
                'student[]' => form_error('student[]'),
            );
            $array = array('status' => 0, 'error' => $msg, 'message' => '');
        } else {
            $students = $this->input->post('student');

            foreach ($students as $student_key => $student_value) {

            }

            $this->student_model->bulkdelete($students);

            $array = array('status' => 1, 'error' => '', 'message' => $this->lang->line('delete_message'));
        }
        echo json_encode($array);
    }

    public function profilesetting()
    {

        if (!$this->rbac->hasPrivilege('student_profile_update', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'System Settings/profilesetting');
        $data                    = array();
        $data['result']          = $this->setting_model->getSetting();
        $data['fields']          = get_student_editable_fields();
        $data['inserted_fields'] = $this->student_edit_field_model->get();
        $data['result']          = $this->setting_model->getSetting();
        $this->form_validation->set_rules('student_profile_edit', $this->lang->line('student') . " " . $this->lang->line('profile') . " " . $this->lang->line('update'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == true) {
            $data_record = array(
                'id'                   => $this->input->post('sch_id'),
                'student_profile_edit' => $this->input->post('student_profile_edit'),
            );
            $this->setting_model->add($data_record);
            $this->session->set_flashdata('msg', '<div class="alert alert-left">' . $this->lang->line('update_message') . '</div>');
            redirect('student/profilesetting');
        }
        $data['sch_setting_detail'] = $this->sch_setting_detail;
        $this->load->view("layout/header");
        $this->load->view("student/profilesetting", $data);
        $this->load->view("layout/footer");
    }

    public function changeprofilesetting()
    {

        $this->form_validation->set_rules('name', $this->lang->line('student'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('status', $this->lang->line('status'), 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {

            $msg = array(
                'status' => form_error('status'),
                'name'   => form_error('name'),
            );

            $array = array('status' => '0', 'error' => $msg, 'msg' => $this->lang->line('something_went_wrong'));

        } else {
            $insert = array(
                'name'   => $this->input->post('name'),
                'status' => $this->input->post('status'),
            );
            $this->student_edit_field_model->add($insert);
            $array = array('status' => '1', 'error' => '', 'msg' => $this->lang->line('success_message'));
        }

        echo json_encode($array);
    }

    /**
     * This function is used to view bulk mail page
     */
    public function bulkmail()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_view')) {
            access_denied();
        }        
		
		$this->session->set_userdata('top_menu', 'Communicate');
        $this->session->set_userdata('sub_menu', 'bulk_mail');		
        $class                    = $this->class_model->get();
        $data['classlist']        = $class;
        $data['sch_setting']      = $this->sch_setting_detail;
        $data['bulkmailto']       = $this->customlib->bulkmailto();
        $data['notificationtype'] = $this->customlib->bulkmailnotificationtype();
        $data['fields']           = $this->customfield_model->get_custom_fields('students', 1);
        if ($this->input->server('REQUEST_METHOD') == "GET") {
            $this->load->view('layout/header', $data);
            $this->load->view('student/bulkmail', $data);
            $this->load->view('layout/footer', $data);

        } else {
            $class   = $this->input->post('class_id');
            $section = $this->input->post('section_id');

            $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
            if ($this->form_validation->run() == false) {

            } else {
                $data['class_id']   = $this->input->post('class_id');
                $data['section_id'] = $this->input->post('section_id');
                $resultlist         = $this->student_model->searchByClassSection($class, $section);
                $data['resultlist'] = $resultlist;
            }

            $this->load->view('layout/header', $data);
            $this->load->view('student/bulkmail', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    /**
     * This function is used to send bulk mail to student and Parent
     */
    public function sendbulkmail()
    {
    
        $this->form_validation->set_rules('student[]', $this->lang->line('student'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('message_to', $this->lang->line('message_to'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('notification_type', $this->lang->line('notification_type'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
            $msg = array(
                'student[]'         => form_error('student[]'),
                'message_to'        => form_error('message_to'),
                'notification_type' => form_error('notification_type'),
            );
            $array = array('status' => 0, 'error' => $msg, 'message' => '');
        } else {
            $students          = $this->input->post('student');
            $message_to        = $this->input->post('message_to');			
            $notification_type = $this->input->post('notification_type');
			
            foreach ($students as $students_value) {

                $student_detail = $this->user_model->student_information($students_value);
                $parent_detail  = $this->user_model->read_single_child($students_value);
				
				if (($message_to == 1 && $notification_type == 1) || ($message_to == 1 && $notification_type == 3) || ($message_to == 3 && $notification_type == 3)) {
                    $sender_details = array('student_id' => $students_value, 'contact_no' => $student_detail[0]->mobileno, 'email' => $student_detail[0]->email);
                    $this->mailsmsconf->mailsms('student_admission', $sender_details);					
                } 
				
				if (($message_to == 1 && $notification_type == 2)  || ($message_to == 1 && $notification_type == 3) || ($message_to == 3 && $notification_type == 3)) {
                    $student_login_detail = array('id' => $students_value, 'credential_for' => 'student', 'username' => $student_detail[0]->username, 'password' => $student_detail[0]->password, 'contact_no' => $student_detail[0]->mobileno, 'email' => $student_detail[0]->email);
                    $this->mailsmsconf->mailsms('login_credential', $student_login_detail);					
                }			
				
				if (($message_to == 2 && $notification_type == 1) || ($message_to == 2 && $notification_type == 3) || ($message_to == 3 && $notification_type == 3)) {
					$sender_details = array('student_id' => $students_value, 'contact_no' => $student_detail[0]->guardian_phone, 'email' => $student_detail[0]->guardian_email);
                    $this->mailsmsconf->mailsms('student_admission', $sender_details);
                }	
				
				if (($message_to == 2 && $notification_type == 2) || ($message_to == 2 && $notification_type == 3) || ($message_to == 3 && $notification_type == 3)) {
                    $parent_login_detail = array('id' => $students_value, 'credential_for' => 'parent', 'username' => $parent_detail->username, 'password' => $parent_detail->password, 'contact_no' => $student_detail[0]->guardian_phone, 'email' => $student_detail[0]->guardian_email);
                    $this->mailsmsconf->mailsms('login_credential', $parent_login_detail);
				}				
            }

            $array = array('status' => 1, 'error' => '', 'message' => $this->lang->line('message_sent_successfully'));
        }
        echo json_encode($array);
    }

    public function dtstudentlist()
    {
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $class           = $this->input->post('class_id');
        $section         = $this->input->post('section_id');
        $search_text     = $this->input->post('search_text');
        $search_type     = $this->input->post('srch_type');
        $classlist       = $this->class_model->get();
        $classlist       = $classlist;
        $carray          = array();
        if (!empty($classlist)) {
            foreach ($classlist as $ckey => $cvalue) {
                $carray[] = $cvalue["id"];
            }
        }

        $sch_setting = $this->sch_setting_detail;
        if ($search_type == "search_filter") {
            $resultlist = $this->student_model->searchdtByClassSection($class, $section);
        } elseif ($search_type == "search_full") {
            $resultlist = $this->student_model->searchFullText($search_text, $carray);
        }
       
        $students = array();
        $students = json_decode($resultlist);
        $dt_data  = array();
        $fields   = $this->customfield_model->get_custom_fields('students', 1);
        if (!empty($students->data)) {
            foreach ($students->data as $student_key => $student) {

                $editbtn   = '';
                $deletebtn = '';
                $viewbtn   = '';
                $collectbtn   = '';

                $viewbtn = "<a href='" . base_url() . "student/view/" . $student->id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' data-placement='left' title='" . $this->lang->line('show') . "'><i class='fa fa-reorder'></i></a>";

                if ($this->rbac->hasPrivilege('student', 'can_edit')) {
                    $editbtn = "<a href='" . base_url() . "student/edit/" . $student->id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' data-placement='left' title='" . $this->lang->line('edit') . "'><i class='fa fa-pencil'></i></a>";
                }
                if ($this->rbac->hasPrivilege('collect_fees', 'can_add')) {

                    $collectbtn = "<a href='" . base_url() . "studentfee/addfee/" . $student->student_session_id . "'   class='btn btn-default btn-xs'  data-toggle='tooltip' data-placement='left' title='" . $this->lang->line('add_fees') . "'><span >" . $currency_symbol . "</a>";
                }

                $row   = array();
                $row[] = $student->admission_no;
                $row[] = $student->parent_id;
                $row[] = "<a href='" . base_url() . "student/view/" . $student->id . "'>" . $this->customlib->getFullName($student->firstname, $student->middlename, $student->lastname, $sch_setting->middlename, $sch_setting->lastname) . "</a>";
                $row[] = $student->class . "(" . $student->section . ")";
                if ($sch_setting->father_name) {
                    $row[] = $student->father_name;
                }
               
                $row[] = $this->customlib->dateformat($student->dob);          

                $row[] = $student->gender;
                if ($sch_setting->category) {
                    $row[] = $student->category;
                }
                if ($sch_setting->mobile_no) {
                    $row[] = $student->mobileno;
                }

                foreach ($fields as $fields_key => $fields_value) {

                    $custom_name   = $fields_value->name;
                    $display_field = $student->$custom_name;
                    if ($fields_value->type == "link") {
                        $display_field = "<a href=" . $student->$custom_name . " target='_blank'>" . $student->$custom_name . "</a>";

                    }
                    $row[] = $display_field;

                }

                $row[] = $viewbtn . '' . $editbtn . '' . $collectbtn;

                $dt_data[] = $row;
            }

        }
        $sch_setting         = $this->sch_setting_detail;
        $student_detail_view = $this->load->view('student/_searchDetailView', array('sch_setting' => $sch_setting, 'students' => $students), true);
        $json_data           = array(
            "draw"                => intval($students->draw),
            "recordsTotal"        => intval($students->recordsTotal),
            "recordsFiltered"     => intval($students->recordsFiltered),
            "data"                => $dt_data,
            "student_detail_view" => $student_detail_view,
        );

        echo json_encode($json_data);

    }

//datatable function to check search parameter validation
    public function searchvalidation()
    {
        $class_id   = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');

        $srch_type = $this->input->post('search_type');
        $search_text = $this->input->post('search_text');

        if ($srch_type == 'search_filter') {

            $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
            if ($this->form_validation->run() == true) {

                $params = array('srch_type' => $srch_type, 'class_id' => $class_id, 'section_id' => $section_id);
                $array  = array('status' => 1, 'error' => '', 'params' => $params);
                echo json_encode($array);

            } else {

                $error             = array();
                $error['class_id'] = form_error('class_id');
                $array             = array('status' => 0, 'error' => $error);
                echo json_encode($array);
            }
        } else {
            $params = array('srch_type' => 'search_full', 'class_id' => $class_id, 'section_id' => $section_id,'search_text'=>$search_text);
            $array  = array('status' => 1, 'error' => '', 'params' => $params);
            echo json_encode($array);
        }

    }

    /* this function is used to validate student report   */
    public function studentreportvalidation()
    {
        $class_id    = $this->input->post('class_id');
        $section_id  = $this->input->post('section_id');
        $category_id = $this->input->post('category_id');
        $gender      = $this->input->post('gender');
        $rte         = $this->input->post('rte');

        $srch_type = $this->input->post('search_type');

        if ($srch_type == 'search_filter') {

            $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
            if ($this->form_validation->run() == true) {

                $params = array('srch_type' => $srch_type, 'class_id' => $class_id, 'section_id' => $section_id, 'category_id' => $category_id, 'gender' => $gender, 'rte' => $rte);
                $array  = array('status' => 1, 'error' => '', 'params' => $params);
                echo json_encode($array);

            } else {

                $error             = array();
                $error['class_id'] = form_error('class_id');
                $array             = array('status' => 0, 'error' => $error);
                echo json_encode($array);
            }
        } else {
            $params = array('srch_type' => 'search_full', 'class_id' => $class_id, 'section_id' => $section_id);
            $array  = array('status' => 1, 'error' => '', 'params' => $params);
            echo json_encode($array);
        }

    }

    public function dtstudentreportlist()
    {

        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $class           = $this->input->post('class_id');
        $section         = $this->input->post('section_id');
        $category_id     = $this->input->post('category_id');
        $gender          = $this->input->post('gender');
        $rte             = $this->input->post('rte');
        $sch_setting     = $this->sch_setting_detail;

        $result     = $this->student_model->searchdatatableByClassSectionCategoryGenderRte($class, $section, $category_id, $gender, $rte);
        $resultlist = json_decode($result);
        $dt_data    = array();
        if (!empty($resultlist->data)) {
            foreach ($resultlist->data as $resultlist_key => $student) {

                $viewbtn = "<a  href='" . base_url() . "student/view/" . $student->id . "'>" . $this->customlib->getFullName($student->firstname, $student->middlename, $student->lastname, $sch_setting->middlename, $sch_setting->lastname) . "</a>";

                $row   = array();
                $row[] = $student->section;
                $row[] = $student->admission_no;
                $row[] = $viewbtn;
                if ($sch_setting->father_name) {
                    $row[] = $student->father_name;
                }
                if ($student->dob != null && $student->dob != '0000-00-00') {
                    $row[] = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student->dob));
                } else {
                    $row[] = "";
                }
                $row[] = $student->gender;

                if ($sch_setting->category) {
                    $row[] = $student->category;
                }
                if ($sch_setting->mobile_no) {
                    $row[] = $student->mobileno;
                }
                if ($sch_setting->national_identification_no) {
                    $row[] = $student->samagra_id;
                }
                if ($sch_setting->local_identification_no) {
                    $row[] = $student->adhar_no;
                }
                if ($sch_setting->rte) {
                    $row[] = $student->rte;
                }

                $dt_data[] = $row;
            }

        }
        $json_data = array(
            "draw"            => intval($resultlist->draw),
            "recordsTotal"    => intval($resultlist->recordsTotal),
            "recordsFiltered" => intval($resultlist->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    public function getStudentByClassSection()
    {

        $data                 = array();
        $cls_section_id       = $this->input->post('cls_section_id');
        $data['fields']       = $this->customfield_model->get_custom_fields('students', 1);
        $student_list         = $this->student_model->getStudentBy_class_section_id($cls_section_id);
        $data['student_list'] = $student_list;
        $data['sch_setting']  = $this->sch_setting_detail;
        $page                 = $this->load->view('reports/_getStudentByClassSection', $data, true);
        echo json_encode(array('status' => 1, 'page' => $page));
    }

    public function changePasswordView()
    {
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/changePasswordView');
        $data['title'] = 'Change Password';
        $class = $this->class_model->get();
        $data['classlist'] = $class;
        //print_r($class);
        $this->load->view('layout/header', $data);
        $this->load->view('student/changePasswordView', $data);
        $this->load->view('layout/footer', $data);
    }
    public function getCurrentPassword()
    {
        $class_id = $this->input->post("class_id");
        $section_id = $this->input->post("section_id");
        $student_id =$this->input->post("student");
        if($student_id=='ALL'){
            $studentdata = $this->student_model->searchByClassSection($class_id, $section_id);
            foreach ($studentdata as $key => $value) {
                $resultlist = $this->user_model->getUserLoginDetails($value["id"]);
                $parentlist = $this->user_model->getParentLoginDetails($value["id"]);
                if ($resultlist["role"] == "student") {
                    $studentdata[$key]["student_username"] = $resultlist["username"];
                    $studentdata[$key]["student_password"] = $resultlist["password"];
                    $studentdata[$key]["parent_username"] = $parentlist["username"];
                    $studentdata[$key]["parent_password"] = $parentlist["password"];
                }
            }
        }
        else{
            $studentdata = $this->student_model->get($student_id);
            $resultlist = $this->user_model->getUserLoginDetails($student_id);
            $parentlist = $this->user_model->getParentLoginDetails($student_id);
            if ($resultlist["role"] == "student") {
                $studentdata["student_username"] = $resultlist["username"];
                $studentdata["student_password"] = $resultlist["password"];
                $studentdata["parent_username"] = $parentlist["username"];
                $studentdata["parent_password"] = $parentlist["password"];
                $studentdata["childs"] = $parentlist["childs"];
                $studentdata["user_id"] = $parentlist["id"];
                $studentdata["std_id"] = $resultlist["id"];
            }
        }

        $data["resultlist"] = $studentdata;
        $data["student"]=$student_id;
        //echo json_encode($studentdata);
        $this->load->view('layout/header', $data);
        $this->load->view('student/changePassword', $data);
        $this->load->view('layout/footer', $data);
    }

    public function changePassword()
    {
        $password =$this->input->post("parent_password");
        $std_password =$this->input->post("student_password");
        $childs =$this->input->post("childs");
        $user_id =$this->input->post("user_id");
        $std_id =$this->input->post("std_id");
        $data_parent_login = array(
            'id'=>$user_id,
            'password' => $password
        );
        $data_child_login = array(
            'id'=>$std_id,
            'password' => $std_password
        );
        $ins_parent_id = $this->user_model->add($data_parent_login);
        $ins_child_id = $this->user_model->add($data_child_login);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">Password Changed Successfully</div>');
        redirect('student/changePasswordView');
    }
    function changeSection()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/disable');
        $data['title'] = 'Student Disable';
        $class = $this->class_model->get();
        $data['classlist'] = $class;
        $userdata = $this->customlib->getUserData();
        $carray = array();

        if (!empty($data["classlist"])) {
            foreach ($data["classlist"] as $ckey => $cvalue) {

                $carray[] = $cvalue["id"];
            }
        }

        $button = $this->input->post('search');
        if ($this->input->server('REQUEST_METHOD') == "GET") {
            $this->load->view('layout/header', $data);
            $this->load->view('student/changeSection', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $class = $this->input->post('class_id');
            $section = $this->input->post('section_id');
            $search = $this->input->post('search');
            $search_text = $this->input->post('search_text');
            if (isset($search)) {
                if ($search == 'search_filter') {
                    $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');
                    if ($this->form_validation->run() == FALSE) {

                    } else
                    {
                        $data['searchby'] = "filter";
                        $data['class_id'] = $this->input->post('class_id');
                        $data['section_id'] = $this->input->post('section_id');
                        $data['search_text'] = $this->input->post('search_text');
                        $resultlist = $this->student_model->searchByClassSection($class, $section);
                        $data['resultlist'] = $resultlist;
                        $title = $this->classsection_model->getDetailbyClassSection($data['class_id'], $data['section_id']);
                        $data['title'] = 'Student Details for ' . $title['class'] . "(" . $title['section'] . ")";
                    }
                }
                else if ($search == 'search_full') {
                    $data['searchby'] = "text";

                    $data['search_text'] = trim($this->input->post('search_text'));
                    $resultlist = $this->student_model->searchFullText($search_text, $carray);
                    $data['resultlist'] = $resultlist;
                    $data['title'] = 'Search Details: ' . $data['search_text'];
                }
            }
            $this->load->view('layout/header', $data);
            $this->load->view('student/changeSection', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    function disableStudents() {

        if (!$this->rbac->hasPrivilege('student', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/disable');
        $data['title'] = 'Student Disable';
        $class = $this->class_model->get();
        $data['classlist'] = $class;
        $userdata = $this->customlib->getUserData();
        $carray = array();

        if (!empty($data["classlist"])) {
            foreach ($data["classlist"] as $ckey => $cvalue) {

                $carray[] = $cvalue["id"];
            }
        }

        $button = $this->input->post('search');
        if ($this->input->server('REQUEST_METHOD') == "GET") {
            $this->load->view('layout/header', $data);
            $this->load->view('student/disableStudent', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $class = $this->input->post('class_id');
            $section = $this->input->post('section_id');
            $search = $this->input->post('search');
            $search_text = $this->input->post('search_text');
            if (isset($search)) {
                if ($search == 'search_filter') {
                    $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');
                    if ($this->form_validation->run() == FALSE) {

                    } else {
                        $data['searchby'] = "filter";
                        $data['class_id'] = $this->input->post('class_id');
                        $data['section_id'] = $this->input->post('section_id');
                        $data['search_text'] = $this->input->post('search_text');
                        $resultlist = $this->student_model->searchByClassSection($class, $section);
                        $data['resultlist'] = $resultlist;
                        $title = $this->classsection_model->getDetailbyClassSection($data['class_id'], $data['section_id']);
                        $data['title'] = 'Student Details for ' . $title['class'] . "(" . $title['section'] . ")";
                    }
                } else if ($search == 'search_full') {
                    $data['searchby'] = "text";

                    $data['search_text'] = trim($this->input->post('search_text'));
                    $resultlist = $this->student_model->searchFullText($search_text, $carray);
                    $data['resultlist'] = $resultlist;
                    $data['title'] = 'Search Details: ' . $data['search_text'];
                }
            }
            $this->load->view('layout/header', $data);
            $this->load->view('student/disableStudent', $data);
            $this->load->view('layout/footer', $data);
        }
    }

    function disable()
    {
        $id =$this->input->get('STUDENT_ID');
        $data = array('is_active' => "no", 'disable_at' => date("Y-m-d"));
        $this->student_model->disableStudent($id, $data);
        echo json_encode('Success');
    }

    function sectionChange()
    {
        $id =$this->input->get('STUDENT_ID');
        $section =$this->input->get('NEW_SECTION');
        $data = array('section_id' => $section, 'updated_at' => date("Y-m-d"));
        $array = explode(',', $id);
        foreach($array as $value) //loop over values
        {
            $this->student_model->changeSection($value, $data);
        }
        echo json_encode('Success');
    }

    public function admissionReportView()
    {
        $data['title'] = 'Admission Report';
        $class = $this->class_model->get();
        $data['classlist'] = $class;
        $data['startDate']=date('Y-m-01');
        $data['endDate']=date('Y-m-d');
        $this->load->view('layout/header', $data);
        $this->load->view('student/admissionReportView', $data);
        $this->load->view('layout/footer', $data);
    }

    public function admissionReportPDF()
    {
        $class_id = $this->input->post('class_id');
        $startDate = $this->input->post('start_date');
        $endDate = $this->input->post('end_date');
        $result =$this->student_model->getNewAdmissionReport($startDate,$endDate,$class_id);
        $data['result']=$result;
        $data['school'] = $this->setting_model->getCurrentSchoolName();
        $setting_result = $this->setting_model->get();
        $data['class'] = $this->class_model->get($class_id);
        $data['class_id']=$class_id;
        $data['session'] = $this->setting_model->getCurrentSessionName();
        $data['settinglist'] = $setting_result;
        $data['startDate']=$startDate;
        $data['endDate']=$endDate;
//        print_r($result);
        $this->load->view('student/admissionReportPDF', $data);
    }
    public function classStrengthReportView()
    {
        if (!$this->rbac->hasPrivilege('student', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'Reports/student_information/classStrengthReportView');
        $data['title'] = 'Class Strength Report';
        $class = $this->class_model->get();
        $data['classlist'] = $class;

        $this->load->view('layout/header', $data);
        $this->load->view('student/classStrengthReportView', $data);
        $this->load->view('layout/footer', $data);
    }
    function classStrengthReportPDF() {
        $data['school'] = $this->setting_model->getCurrentSchoolName();
        $setting_result = $this->setting_model->get();
        $session_id = $this->setting_model->getCurrentSession();
        $data['settinglist'] = $setting_result;
        $class_id1 = $this->input->post('class_id');
        if($class_id1==null || $class_id1=="" ){
            $class_id = "ALL";
        }else{
            $class_id = $this->input->post('class_id');
        }
        $data['class'] = $this->class_model->get();
        $data['class_id'] = $class_id;

        $strengthlist =$this->student_model->getClassStrengthReport($class_id,$session_id);
        $strengthlistM =$this->student_model->getClassStrengthMReport($class_id,$session_id);
        $strengthlistF =$this->student_model->getClassStrengthFReport($class_id,$session_id);
        $tlist=array();
        $classStrength_Array = array();
        $assignteacherlist = $this->class_model->getClassTeacher();
        $data['assignteacherlist'] = $assignteacherlist;
        foreach ($assignteacherlist as $key => $value) {
            $class_id2 = $value["class_id"];
            $section_id2 = $value["section_id"];
            $tlist[] = $this->classteacher_model->teacherByClassSection($class_id2, $section_id2);
        }
        if (!empty($strengthlist)) {
            foreach ($strengthlist as $key => $eachstudent) {
//                print_r($eachstudent);
                $obj = new stdClass();
                $obj->cnt_sum = $eachstudent['cnt_sum'];
                $obj->class = $eachstudent['class'];
                $obj->section = $eachstudent['section'];
                $c_incharge="";
                $m_std=0;
                $f_std=0;
                foreach ($tlist as $teacher_key => $teacher_value) {
//                        echo($teacher_value[0]['class']);
                    if ($teacher_value[0]['class'] == $eachstudent['class']&& $teacher_value[0]['section'] == $eachstudent['section']) {
                        $c_incharge=$teacher_value[0]['name']." ".$teacher_value[0]['surname'];
                    }
                }foreach ($strengthlistM as $male_std_key => $male_std_value) {
//                        echo($teacher_value[0]['class']);
                    if ($male_std_value['class'] == $eachstudent['class']&& $male_std_value['section'] == $eachstudent['section']) {
                        $m_std+=$male_std_value['cnt_sum'];
                    }
                }foreach ($strengthlistF as $female_std_key => $female_std_value) {
//                        echo($teacher_value[0]['class']);
                    if ($female_std_value['class'] == $eachstudent['class']&& $female_std_value['section'] == $eachstudent['section']) {
                        $f_std+=$female_std_value['cnt_sum'];
                    }
                }
                $obj->m_std =  $m_std;
                $obj->f_std =  $f_std;
                $obj->class_incharge =  $c_incharge;

                $classStrength_Array[] = $obj;
            }
        }
        $data['result'] = $classStrength_Array;
//        print_r($classStrength_Array);
        $this->load->view('student/classStrengthReportPDF',$data);

    }
    function classAttReportPDF() {
        $data['school'] = $this->setting_model->getCurrentSchoolName();
        $setting_result = $this->setting_model->get();
        $session_id = $this->setting_model->getCurrentSession();
        $data['settinglist'] = $setting_result;
        $class_id1 = $this->input->post('class_id');
        if($class_id1==null || $class_id1=="" ){
            $class_id = "ALL";
        }else{
            $class_id = $this->input->post('class_id');
        }
        $data['class'] = $this->class_model->get();
        $data['class_id'] = $class_id;

        $presentlist =$this->student_model->getClassAttendenceReport($class_id,$session_id);
        $strengthlist =$this->student_model->getClassStrengthReport($class_id,$session_id);
        $strengthlistM =$this->student_model->getClassStrengthMReport($class_id,$session_id);
        $strengthlistF =$this->student_model->getClassStrengthFReport($class_id,$session_id);

        if (!empty($strengthlist)) {
            foreach ($strengthlist as $key => $eachstudent) {
//                print_r($eachstudent);
                $obj = new stdClass();

                $m_std=0;
                $f_std=0;
                $present_std=0;

                foreach ($strengthlistM as $male_std_key => $male_std_value) {
//                        echo($teacher_value[0]['class']);
                    if ($male_std_value['class'] == $eachstudent['class']&& $male_std_value['section'] == $eachstudent['section']) {
                        $m_std+=$male_std_value['cnt_sum'];
                    }
                }foreach ($strengthlistF as $female_std_key => $female_std_value) {
//                        echo($teacher_value[0]['class']);
                    if ($female_std_value['class'] == $eachstudent['class']&& $female_std_value['section'] == $eachstudent['section']) {
                        $f_std+=$female_std_value['cnt_sum'];
                    }
                }foreach ($presentlist as $present_std_key => $present_std_value) {
                    if ($present_std_value['classes_id'] == $eachstudent['classes_id']&& $present_std_value['sections_id'] == $eachstudent['sections_id']) {
                        $present_std+=$present_std_value['TOTAL_PRESENT'];
                    }
                }
                $obj->cnt_sum = $eachstudent['cnt_sum'];
                $obj->class = $eachstudent['class'];
                $obj->section = $eachstudent['section'];
                $obj->m_std =  $m_std;
                $obj->f_std =  $f_std;
                $obj->present_std =  $present_std;

                $classStrength_Array[] = $obj;
            }
        }
        $data['result'] = $classStrength_Array;
//        print_r($classStrength_Array);
        $this->load->view('student/classAttReportPDF',$data);

    }
    function classFeeCollectionReportPDF() {
        $data['school'] = $this->setting_model->getCurrentSchoolName();
        $setting_result = $this->setting_model->get();
        $session_id = $this->setting_model->getCurrentSession();
        $data['settinglist'] = $setting_result;
        $class_id1 = $this->input->post('class_id');
        if($class_id1==null || $class_id1=="" ){
            $class_id = "ALL";
        }else{
            $class_id = $this->input->post('class_id');
        }
        $data['class'] = $this->class_model->get();
        $data['class_id'] = $class_id;
        $date1=date('Y-m-1');
        $date=date('Y-m-t');

        $feelist =$this->studentfeemaster_model->getFeesAwaiting($date1,$date);


        $strengthlist =$this->student_model->getClassStrengthReport_old($class_id,$session_id);
        $strengthlistM =$this->student_model->getClassStrengthMReport($class_id,$session_id);
        $strengthlistF =$this->student_model->getClassStrengthFReport($class_id,$session_id);

        $classStrength_Array = array(); // Initialize the array

        if (!empty($strengthlist)) {
            foreach ($strengthlist as $key => $eachstudent) {
                $obj = new stdClass();

                $m_std=0;
                $f_std=0;
                $total_fess    = 0;
                $total_paid    = 0;
                $total_unpaid  = 0;
                $total_partial = 0;
                foreach ($strengthlistM as $male_std_key => $male_std_value) {
//                        echo($teacher_value[0]['class']);
                    if ($male_std_value['class'] == $eachstudent['class']&& $male_std_value['section'] == $eachstudent['section']) {
                        $m_std+=$male_std_value['cnt_sum'];
                    }
                }foreach ($strengthlistF as $female_std_key => $female_std_value) {
//                        echo($teacher_value[0]['class']);
                    if ($female_std_value['class'] == $eachstudent['class']&& $female_std_value['section'] == $eachstudent['section']) {
                        $f_std+=$female_std_value['cnt_sum'];
                    }
                }/*foreach ($presentlist as $present_std_key => $present_std_value) {
                    if ($present_std_value['classes_id'] == $eachstudent['classes_id']&& $present_std_value['sections_id'] == $eachstudent['sections_id']) {
                        $present_std+=$present_std_value['TOTAL_PRESENT'];
                    }
                }*/
                if (!empty($feelist)) {
                    foreach ($feelist as $awaiting_key => $awaiting_value) {
                        // Count fee records for this class-section combination
                        if ($awaiting_value->class_id == $eachstudent['classes_id'] && $awaiting_value->section_id == $eachstudent['sections_id']) {

                            $amount_to_be_taken = 0;
                            if ($awaiting_value->is_system) {
                                if ($awaiting_value->amount > 0) {
                                    $amount_to_be_taken = $awaiting_value->amount;
                                }
                            } elseif ($awaiting_value->is_system == 0) {
                                if ($awaiting_value->fee_amount > 0) {
                                    $amount_to_be_taken = $awaiting_value->fee_amount;
                                }
                            }
                            
                            if ($amount_to_be_taken > 0) {
                                $total_fess++;

                                if (is_string($awaiting_value->amount_detail) && is_array(json_decode($awaiting_value->amount_detail, true)) && (json_last_error() == JSON_ERROR_NONE)) {
                                    $amount_paid_details = (json_decode($awaiting_value->amount_detail));
                                    $amt_ = 0;
                                    foreach ($amount_paid_details as $amount_paid_detail_key => $amount_paid_detail_value) {
                                        $amt_ = $amt_ + $amount_paid_detail_value->amount;
                                    }

                                    if (($amt_ + $amount_paid_detail_value->amount_discount) >= $amount_to_be_taken) {
                                        $total_paid++;
                                    } else {
                                        // Treat partial payments as unpaid to match widget behavior
                                        $total_unpaid++;
                                    }
                                } else {
                                    $total_unpaid++;
                                }
                            }
                        }
                    }
                }
                $obj->cnt_sum = $eachstudent['cnt_sum'];
                $obj->class = $eachstudent['class'];
                $obj->section = $eachstudent['section'];
                $obj->m_std =  $m_std;
                $obj->f_std =  $f_std;
                $obj->total_paid =  $total_paid;
                $obj->total_unpaid =  $total_unpaid;
                $obj->total_partial =  $total_partial;

                $classStrength_Array[] = $obj;
            }
        }
        $data['result'] = $classStrength_Array;
        $this->load->view('student/classFeeCollectionReportPDF',$data);

    }
    

    function studentReportView() {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Student Information');
        $this->session->set_userdata('sub_menu', 'student/studentReportView');
        $data['title'] = 'Student Report';
        $class = $this->class_model->get();
        $data['classlist'] = $class;

//        print_r($feetype);
        $this->load->view('layout/header', $data);
        $this->load->view('student/studentReportView', $data);
        $this->load->view('layout/footer', $data);
    }
    function studentReportPDF()
    {
        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $type = $this->input->post('type');
        $input = $this->setting_model->getCurrentSessionName();
        $student_Array = array();
        $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', 'Section', 'trim|required|xss_clean');
        $this->form_validation->set_rules('type', 'Type', 'trim|required|xss_clean');
        if ($this->form_validation->run() == FALSE) {
            $data['title'] = 'student report';
            $class = $this->class_model->get();
            $data['classlist'] = $class;
            $this->load->view('layout/header', $data);
            $this->load->view('student/studentReportView', $data);
            $this->load->view('layout/footer', $data);
        }
        else{
            $studentlist = $this->student_model->searchByClassSection($class_id,$section_id);
            if (!empty($studentlist)) {
                foreach ($studentlist as $key => $eachstudent) {
                    $obj = new stdClass();
                    $obj->name = $eachstudent['firstname'] . " " . $eachstudent['lastname'];
                    $obj->class = $eachstudent['class'];
                    $obj->section = $eachstudent['section'];
                    $obj->admission_no = $eachstudent['admission_no'];
                    $obj->roll_no = $eachstudent['roll_no'];
                    $obj->father_name = $eachstudent['father_name'];
                    $obj->guardian_phone = $eachstudent['guardian_phone'];
                    $obj->current_address = $eachstudent['current_address'];
                    $student_session_id = $eachstudent['student_session_id'];

                    $student_Array[] = $obj;
                }
            }
            $data['school'] = $this->setting_model->getCurrentSchoolName();
            $data['class'] = $this->class_model->get($class_id);
            $data['section'] = $this->section_model->get($section_id);
            $data['session'] = $this->setting_model->getCurrentSessionName();
            $setting_result = $this->setting_model->get();
            $data['settinglist'] = $setting_result;
            $data['classID']=$class_id;
            $classteacher_id= $this->staff_model->getTeacherId($class_id, $section_id);
            $data['classteacher_name']= $this->staff_model->getClassTeacher($classteacher_id);
            $data['student_due_fee'] = $student_Array;
            $data['type'] = $type;

//            echo json_encode($data);
            if($type==3){
                $this->load->view('student/studentBoardReportPDF', $data);

            }else{
                $this->load->view('student/studentReportPDF', $data);

            }
        }
    }

}
