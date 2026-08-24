<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Quickexam extends Admin_Controller
{
    private $sch_current_session = "";

    public function __construct()
    {
        parent::__construct();
        $this->load->model('quickexam_model');
        $this->load->model('whatsappcampaign_model');
        $this->load->library('mailsmsconf');
        $this->sch_current_session = $this->setting_model->getCurrentSession();
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('exam_group', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Examinations');
        $this->session->set_userdata('sub_menu', 'Examinations/quickexam');

        $data['classlist'] = $this->class_model->get();
        $data['subjectlist'] = $this->subject_model->get();
        $data['session_id'] = $this->sch_current_session;
        $data['quick_exam_list'] = $this->quickexam_model->getQuickExams($this->sch_current_session);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/quickexam/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function entries()
    {
        redirect('admin/quickexam?mode=entry');
    }

    public function createtest()
    {
        if (!$this->rbac->hasPrivilege('exam_group', 'can_add')) {
            access_denied();
        }

        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('test_title', 'Test Title', 'trim|required|xss_clean');
        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('test_date', $this->lang->line('date'), 'trim|required|xss_clean');
        $this->form_validation->set_rules('max_marks', 'Max Marks', 'trim|required|numeric|xss_clean');

        if ($this->form_validation->run() == false) {
            $error = array(
                'test_title' => form_error('test_title'),
                'class_id'   => form_error('class_id'),
                'section_id' => form_error('section_id'),
                'test_date'  => form_error('test_date'),
                'max_marks'  => form_error('max_marks'),
            );
            echo json_encode(array('status' => 0, 'error' => $error));
            return;
        }

        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $separate_subject_entry = $this->input->post('separate_subject_entry') == '1';
        $subject_ids = array();
        if ($separate_subject_entry) {
            $subject_ids = $this->input->post('subject_ids');
            if (empty($subject_ids) || !is_array($subject_ids)) {
                echo json_encode(array('status' => 0, 'message' => 'Please select at least one subject for separate subject-wise marks entry.'));
                return;
            }
            $subject_ids = array_unique(array_map('intval', $subject_ids));
            $subject_ids = array_values(array_filter($subject_ids, function ($id) {
                return $id > 0;
            }));
            if (empty($subject_ids)) {
                echo json_encode(array('status' => 0, 'message' => 'Please select valid subjects.'));
                return;
            }
        } else {
            $subject_id = (int) $this->input->post('subject_id');
            if ($subject_id <= 0) {
                echo json_encode(array('status' => 0, 'message' => 'Please select subject.'));
                return;
            }
            $subject_ids[] = $subject_id;
        }
        $test_date = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('test_date')));
        $max_marks = (float) $this->input->post('max_marks');
        $min_marks = $this->input->post('pass_marks') != '' ? (float) $this->input->post('pass_marks') : 0;
        if ($max_marks < 0 || $min_marks < 0 || $min_marks > $max_marks) {
            echo json_encode(array('status' => 0, 'message' => 'Please provide valid marks. Pass marks must be between 0 and max marks.'));
            return;
        }
        $test_time = $this->input->post('test_time');
        $duration = $this->input->post('duration') != '' ? (int) $this->input->post('duration') : 0;

        $students = $this->quickexam_model->getClassStudents($class_id, $section_id, $this->sch_current_session);
        if (empty($students)) {
            echo json_encode(array('status' => 0, 'message' => 'No active students found for selected class and section.'));
            return;
        }

        $exam_group_id = $this->quickexam_model->getOrCreateQuickExamGroup('basic_system');

        $exam = array(
            'exam'            => $this->input->post('test_title'),
            'exam_group_id'   => $exam_group_id,
            'session_id'      => $this->sch_current_session,
            'date_from'       => $test_date,
            'date_to'         => $test_date,
            'description'     => $this->input->post('description'),
            'is_active'       => 1,
            'is_publish'      => 0,
            'use_exam_roll_no'=> 0,
        );

        $exam_subjects = array();
        foreach ($subject_ids as $subject_id) {
            $exam_subjects[] = array(
                'subject_id'   => (int) $subject_id,
                'credit_hours' => 0,
                'date_from'    => $test_date,
                'time_from'    => $test_time,
                'duration'     => $duration,
                'room_no'      => '',
                'max_marks'    => $max_marks,
                'min_marks'    => $min_marks,
            );
        }

        $exam_students = array();
        foreach ($students as $student) {
            $exam_students[] = array(
                'exam_group_class_batch_exam_id' => null,
                'student_id'                     => $student['student_id'],
                'student_session_id'             => $student['student_session_id'],
            );
        }

        $create_payload = array(
            'exam'         => $exam,
            'exam_subjects'=> $exam_subjects,
            'exam_students'=> $exam_students,
        );

        $create = $this->quickexam_model->createQuickTest($create_payload);
        if (!$create['status']) {
            echo json_encode(array('status' => 0, 'message' => 'Unable to create test. Please try again.'));
            return;
        }

        $exam_id = $create['exam_id'];
        $exam_subject_id = (int) $create['exam_subject_id'];
        $exam_subject_list = $this->quickexam_model->getExamSubjects($exam_id);
        $grid_data = $this->examgroupstudent_model->examGroupSubjectResult($exam_subject_id, $class_id, $section_id, $this->sch_current_session);
        $subject_detail = $this->batchsubject_model->getExamSubject($exam_subject_id);
        $view_data = array(
            'resultlist'          => $grid_data,
            'subject_detail'      => $subject_detail,
            'exam_subject_id'     => $exam_subject_id,
            'exam_id'             => $exam_id,
            'exam_subject_list'   => $exam_subject_list,
            'attendence_exam'     => $this->config->item('attendence_exam'),
            'sch_setting'         => $this->setting_model->getSetting(),
        );
        $grid = $this->load->view('admin/quickexam/_marks_grid', $view_data, true);

        echo json_encode(array(
            'status'          => 1,
            'message'         => 'Test created successfully.',
            'exam_id'         => $exam_id,
            'exam_subject_id' => $exam_subject_id,
            'subjects'        => $exam_subject_list,
            'grid'            => $grid,
        ));
    }

    public function getsubjects()
    {
        if (!$this->rbac->hasPrivilege('exam_group', 'can_view')) {
            access_denied();
        }
        $exam_id = (int) ($this->input->post('exam_id') ? $this->input->post('exam_id') : $this->input->get('exam_id'));
        $subjects = $this->quickexam_model->getExamSubjects($exam_id);
        echo json_encode(array('status' => 1, 'subjects' => $subjects));
    }

    public function loadmarksgrid()
    {
        if (!$this->rbac->hasPrivilege('exam_group', 'can_view')) {
            access_denied();
        }

        $exam_id = (int) ($this->input->post('exam_id') ? $this->input->post('exam_id') : $this->input->get('exam_id'));
        $exam_subject_id = (int) ($this->input->post('exam_subject_id') ? $this->input->post('exam_subject_id') : $this->input->get('exam_subject_id'));
        if ($exam_id <= 0 || $exam_subject_id <= 0) {
            echo json_encode(array('status' => 0, 'message' => 'Invalid exam or subject.'));
            return;
        }

        $class_section_session = $this->quickexam_model->getExamClassSectionSession($exam_id);
        if (empty($class_section_session)) {
            echo json_encode(array('status' => 0, 'message' => 'No students found for this exam.'));
            return;
        }

        $grid_data = $this->examgroupstudent_model->examGroupSubjectResult(
            $exam_subject_id,
            $class_section_session['class_id'],
            $class_section_session['section_id'],
            $class_section_session['session_id']
        );
        $subject_detail = $this->batchsubject_model->getExamSubject($exam_subject_id);
        $exam_subject_list = $this->quickexam_model->getExamSubjects($exam_id);
        $view_data = array(
            'resultlist'          => $grid_data,
            'subject_detail'      => $subject_detail,
            'exam_subject_id'     => $exam_subject_id,
            'exam_id'             => $exam_id,
            'exam_subject_list'   => $exam_subject_list,
            'attendence_exam'     => $this->config->item('attendence_exam'),
            'sch_setting'         => $this->setting_model->getSetting(),
        );
        $grid = $this->load->view('admin/quickexam/_marks_grid', $view_data, true);
        echo json_encode(array('status' => 1, 'grid' => $grid));
    }

    public function savemarks()
    {
        if (!$this->rbac->hasPrivilege('exam_group', 'can_edit')) {
            access_denied();
        }

        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('exam_subject_id', 'Subject', 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            echo json_encode(array('status' => 0, 'error' => array('exam_subject_id' => form_error('exam_subject_id'))));
            return;
        }

        $exam_subject_id = (int) $this->input->post('exam_subject_id');
        $rows = $this->buildMarkRows($exam_subject_id);
        $this->examgroupstudent_model->add_result($rows);
        echo json_encode(array('status' => 1, 'message' => $this->lang->line('success_message')));
    }

    public function publish()
    {
        if (!$this->rbac->hasPrivilege('exam_group', 'can_edit')) {
            access_denied();
        }

        $this->form_validation->set_error_delimiters('', '');
        $this->form_validation->set_rules('exam_id', $this->lang->line('exam'), 'required|trim|xss_clean');
        if ($this->form_validation->run() == false) {
            echo json_encode(array('status' => 0, 'error' => array('exam_id' => form_error('exam_id'))));
            return;
        }

        $exam_id = (int) $this->input->post('exam_id');
        $send_notification = $this->input->post('send_notification') == '1';
        $exam = $this->examgroup_model->getExamByID($exam_id);
        if (empty($exam)) {
            echo json_encode(array('status' => 0, 'message' => 'Exam not found.'));
            return;
        }

        // Safety: if mark rows are posted from the UI, persist them before publish.
        $exam_subject_id = (int) $this->input->post('exam_subject_id');
        if ($exam_subject_id > 0) {
            $rows = $this->buildMarkRows($exam_subject_id);
            if (!empty($rows)) {
                $this->examgroupstudent_model->add_result($rows);
            }
        }

        $updated = $this->quickexam_model->publishQuickTest($exam_id);

        if (!$updated) {
            echo json_encode(array('status' => 0, 'message' => 'Unable to publish this test.'));
            return;
        }

        $response_message = 'Results published successfully.';
        $whatsapp_campaign_id = 0;

        if ($send_notification) {
            $exam_data = $exam;
            $exam_students = $this->examgroupstudent_model->searchExamStudentsByExam($exam_id);
            $student_exams = array('exam' => $exam_data, 'exam_result' => $exam_students);
            $this->mailsmsconf->mailsms('exam_result', $student_exams, null, null, true);

            $admin_session = $this->session->userdata('admin');
            $created_by = isset($admin_session['id']) ? (int) $admin_session['id'] : 0;
            $whatsapp_campaign_id = (int) $this->whatsappcampaign_model->createExamResultCampaign($exam_id, $created_by);
            if ($whatsapp_campaign_id > 0) {
                $response_message = 'Results published successfully. WhatsApp campaign #' . $whatsapp_campaign_id . ' has been queued.';
            } else {
                $response_message = 'Results published successfully, but WhatsApp campaign could not be queued. Please check the WhatsApp campaign tables and student phone numbers.';
            }
        }

        echo json_encode(array(
            'status' => 1,
            'message' => $response_message,
            'whatsapp_campaign_id' => $whatsapp_campaign_id,
        ));
    }

    private function buildMarkRows($exam_subject_id)
    {
        $exam_student_ids = $this->input->post('exam_group_student_id');
        $rows = array();
        if (empty($exam_student_ids) || $exam_subject_id <= 0) {
            return $rows;
        }

        foreach ($exam_student_ids as $exam_student_id) {
            $attendance = $this->input->post('attendance_' . $exam_student_id);
            if ($attendance == '') {
                $attendance = 'present';
            }
            $rows[] = array(
                'exam_group_class_batch_exam_subject_id' => $exam_subject_id,
                'exam_group_class_batch_exam_student_id' => $exam_student_id,
                'attendence'                             => $attendance,
                'get_marks'                              => $this->input->post('marks_' . $exam_student_id),
                'note'                                   => $this->input->post('note_' . $exam_student_id),
            );
        }

        return $rows;
    }
}
