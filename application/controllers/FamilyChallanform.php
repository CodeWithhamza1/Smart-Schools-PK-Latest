<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
class FamilyChallanform extends Admin_Controller
{
    function __construct() {
        parent::__construct();
        $this->load->model("Student_model");
        $this->load->model("studentfee_model");
        $this->load->model("class_model");



    }

    function loadView() {
        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'challanform/loadView');
        $data['title'] = 'student fees';
        $class = $this->class_model->get();
        $data['classlist'] = $class;
        //print_r($class);
        $this->load->view('layout/header', $data);
        $this->load->view('studentfee/familyChallanForm', $data);
        $this->load->view('layout/footer', $data);
    }

    function getStudents(){
        $class = $this->input->get('CLASS_ID');
        $section = $this->input->get('SECTION_ID');
        $this->load->model("FamilyChallanModel");
        $students = $this->FamilyChallanModel->searchByClassSection($class,$section);
        echo json_encode($students);
    }

    function getAllStudents(){
        $this->load->model("FamilyChallanModel");
        $students = $this->FamilyChallanModel->getAllStudentsName();
        echo json_encode($students);
    }

    function generateChallanForm(){
        $std = $this->input->post('student');
        $year = $this->input->post('year');
        $month = $this->input->post('month');
        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $masterData = array();
//        $this->form_validation->set_rules('student', 'Student', 'trim|required|xss_clean');
        $this->form_validation->set_rules('year', 'Year', 'trim|required|xss_clean');
        $this->form_validation->set_rules('month', 'Month', 'trim|required|xss_clean');
//        $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');
//        $this->form_validation->set_rules('section_id', 'Section', 'trim|required|xss_clean');


        if ($this->form_validation->run() == FALSE) {
            $data['title'] = 'student fees';
            $class = $this->class_model->get();
            $data['classlist'] = $class;
            $this->load->view('layout/header', $data);
            $this->load->view('studentfee/ChallanForm', $data);
            $this->load->view('layout/footer', $data);
        }
        else{
            $input1 = $this->setting_model->getCurrentSessionName();
            $startMonth1 = $this->setting_model->getStartMonth();
            list($a, $b) = explode('-', $input1);
            $Current_year1 = $a;
            if (strlen($b) == 2) {
                $Next_year = substr($a, 0, 2) . $b;
            } else {
                $Next_year = $b;
            }
            $previous_month1 = $month-1;
            $previous_month_date = $year.'-0'.$previous_month1.'-'.'01';
            if($year==date('Y')&&$year==$Current_year1){
                $current_section_date1 = $year.'-0'.$startMonth1.'-01';
                $current_section1= date('Y-m-01', strtotime($current_section_date1));;
            }else if($year==date('Y')&&$year>$Current_year1){
                $year1 = $year-1;
                $current_section_date1 = $year1.'-0'.$startMonth1.'-01';
                $current_section1= date('Y-m-01', strtotime($current_section_date1));;
            }else if($year<date('Y')){
//            $year = $year-1;
                $current_section_date1 = $year.'-0'.$startMonth1.'-01';
                $current_section1= date('Y-m-01', strtotime($current_section_date1));;
            }
            $query_date = $year.'-'.$month.'-'.'01';
            $startDate = date('Y-m-10', strtotime($query_date));
            $data['school'] = $this->setting_model->getCurrentSchoolName();
            $setting_result = $this->setting_model->get();
            $data['settinglist'] = $setting_result;
            /*if($std != 'ALL') {
                $student = $this->student_model->get($std);
                $siblingDetail = $this->student_model->read_siblings_students($student['parent_id']);
                $data['title'] = 'Student Detail';
                $this->load->model("FamilyChallanModel");
                if (count($siblingDetail) > 1) {
                    for ($i = 0; $i < count($siblingDetail); $i++) {
                        $student = $this->student_model->get($siblingDetail[$i]->id);
                        $data['student'] = $student;
                        $student_due_fee = $this->FamilyChallanModel->getStudentFees($student['student_session_id'], $month, $year);
                        $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($student['student_session_id']);
                        $student_pending_fee = $this->FamilyChallanModel->getStudentPreviousFees($student['student_session_id'], $month, $year);
                        $data['student_discount_fee'] = $student_discount_fee;
                        $data['student_due_fee'] = $student_due_fee;
                        $data['student_pending_fee'] = $student_pending_fee;
                        $masterData[$i] = $data;
                    }
                    $data1 = array();
                    $flag1 = false;
                    $data['masterData'] = $masterData;
                    for ($i = 0; $i < count($data1['masterData']); $i++) {
                        for ($j = 0; $j < count($data1['masterData'][$i]['student_due_fee']); $j++) {
                            if ($data1['masterData'][$i]['student_due_fee'][$j]->fees != '' ||
                                $data1['masterData'][$i]['student_due_fee'][$j]->fees != null
                            ) {
                                $flag1 = true;
                            }
                        }
                    }
echo json_encode($data);
//                    $this->load->view('studentfee/siblingChallanFormPDF', $data);

                }
                else {
                    $data['title'] = 'Student Detail';
                    $data['student'] = $student;
                    $this->load->model("FamilyChallanModel");
                    $student_due_fee = $this->FamilyChallanModel->getStudentFees($student['student_session_id'], $month, $year);
                    $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($student['student_session_id']);
                    $student_pending_fee = $this->FamilyChallanModel->getStudentPreviousFees($student['student_session_id'], $month, $year);
                    $data['student_discount_fee'] = $student_discount_fee;
                    $data['student_due_fee'] = $student_due_fee;
                    $data['student_pending_fee'] = $student_pending_fee;
                    //echo json_encode($data);
                    if (count($data['student_due_fee'][0]->fees) > 0 || count($data['student_due_fee'][1]->fees) > 0 || count($data['student_due_fee'][2]->fees) > 0 || count($data['student_due_fee'][3]->fees) > 0 || count($data['student_due_fee'][4]->fees) > 0) {
                        //print_r($student_due_fee);
                        $this->load->view('studentfee/challanFormPDF', $data);
//                    $this->load->view('studentfee/challanFormEducatorQcPDF', $data);
                    } else {
                        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Fees not found for the given month!</div>');
                        $data['title'] = 'student fees';
                        $class = $this->class_model->get();
                        $data['classlist'] = $class;
                        $this->load->view('layout/header', $data);
                        $this->load->view('studentfee/ChallanForm', $data);
                        $this->load->view('layout/footer', $data);
                        //echo json_encode($data);
                    }
                }
            }
            else{*/
                $this->load->model("FamilyChallanModel");
                if($class_id=="" && $section_id==""){
//                    echo "all record";
                   $students = $this->FamilyChallanModel->searchByFamily();
                }else{
//                    echo "class record";

                    $students = $this->FamilyChallanModel->searchByClassSection($class_id,$section_id);
                }


                for($i = 0; $i<count($students); $i++){
//                foreach ($students1 as $key => $students){
//                     $student = $this->student_model->get($students['id']);
                     $student = $this->student_model->get($students[$i]['id']);
                    $siblingData = array();
//                    echo $student['student_session_id']."<br>";

                    $siblingDetail = $this->student_model->read_siblings_students($student['parent_id']);
                    $data['title'] = 'Student Detail ';
                    if (count($siblingDetail) > 1) {
                        for ($j = 0; $j < count($siblingDetail); $j++) {
                            $student = $this->student_model->get($siblingDetail[$j]->id);
                            $data['student'] = $student;
                            if($student['student_session_id'] != null ||$student['student_session_id'] !="") {
                                $student_due_fee = $this->FamilyChallanModel->getStudentFees($student['student_session_id'], $month, $year);
                                $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($student['student_session_id']);
                                $student_pending_fee = $this->FamilyChallanModel->getStudentPreviousFees($student['student_session_id'], $month, $year);
                                $data['student_discount_fee'] = $student_discount_fee;
                                $data['student_due_fee'] = $student_due_fee;
//                                $data['student_pending_fee'] = $student_pending_fee;
                                $previous_monthfee = 0;
                                $previous_deposit = 0;
                                $previous_discount = 0;
                                $previous_fine = 0;
                                $previous = 0;
                                $arrear_deposit=0;
                                $arrear_discount=0;
                                foreach ($student_pending_fee as $student_total_fees_key => $student_total_fees_value) {
                                    if (!empty($student_total_fees_value->fees)) {
                                        foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {
                                            if ($each_fee_value->name == 'Balance Master') {
                                                $previous += $each_fee_value->amount;
                                                $amount_detail = json_decode($each_fee_value->amount_detail);
                                                if (is_object($amount_detail)) {
                                                    foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                                        $arrear_deposit += $amount_detail_value->amount;
                                                        $arrear_discount += $amount_detail_value->amount_discount;
                                                    }
                                                }
                                            }
                                            if ($each_fee_value->name != 'Balance Master') {
                                                $amount_detail = json_decode($each_fee_value->amount_detail);
                                                $m = date('m', strtotime($each_fee_value->due_date));
                                                $previous_month_date_m = date('m', strtotime($previous_month_date));
                                                if ($each_fee_value->due_date >= $current_section1 && $each_fee_value->due_date < $startDate) {
                                                    $previous_monthfee += $each_fee_value->amount;
                                                }
                                                if (is_object($amount_detail)) {
                                                    foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                                        if ($amount_detail_value->date >= $current_section1) {
                                                            $previous_deposit += $amount_detail_value->amount;
                                                            $previous_fine = $previous_fine + $amount_detail_value->amount_fine;
                                                            $previous_discount = $previous_discount + $amount_detail_value->amount_discount;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                // echo "1. (".$previous."+(".$previous_monthfee."))-(".$previous_deposit."+".$previous_discount."+".$arrear_deposit."+".$arrear_discount.")<br>"; 
                                $data['student_pending_fee'] = ($previous+($previous_monthfee))-($previous_deposit+$arrear_deposit+$arrear_discount);

//                            $data['student_pending_fee'] = (($previous_monthfee))-($previous_deposit+$previous_discount);

                            }
//                    $masterData[$i] = $data;
                            $siblingData['sibling'][$j] = $data;
                        }
                    $masterData[]=$siblingData;
                    }else{
                        $data['student'] = $student;
//                        echo json_encode($student);
                        if($student['student_session_id'] != null ||$student['student_session_id'] !="") {

                            $student_due_fee = $this->FamilyChallanModel->getStudentFees($student['student_session_id'], $month, $year);
                            $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($student['student_session_id']);
                            $student_pending_fee = $this->FamilyChallanModel->getStudentPreviousFees($student['student_session_id'], $month, $year);
                            $data['student_discount_fee'] = $student_discount_fee;
                            $data['student_due_fee'] = $student_due_fee;
//                            $data['student_pending_fee'] = $student_pending_fee;
                            $previous_monthfee = 0;
                            $previous_deposit = 0;
                            $previous_discount = 0;
                            $previous_fine = 0;
                            $previous = 0;
                            $arrear_deposit=0;
                            $arrear_discount=0;
                            foreach ($student_pending_fee as $student_total_fees_key => $student_total_fees_value) {
                                if (!empty($student_total_fees_value->fees)) {
                                    foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {
                                        if ($each_fee_value->name == 'Balance Master') {
                                            $previous += $each_fee_value->amount;
                                            $amount_detail = json_decode($each_fee_value->amount_detail);
                                            if (is_object($amount_detail)) {
                                                foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                                    $arrear_deposit += $amount_detail_value->amount;
                                                    $arrear_discount += $amount_detail_value->amount_discount;
                                                }
                                            }
                                        }
                                        if ($each_fee_value->name != 'Balance Master') {
                                            $amount_detail = json_decode($each_fee_value->amount_detail);
                                            $m = date('m', strtotime($each_fee_value->due_date));
                                            $previous_month_date_m = date('m', strtotime($previous_month_date));
                                            if ($each_fee_value->due_date >= $current_section1 && $each_fee_value->due_date < $startDate) {
                                                $previous_monthfee += $each_fee_value->amount;
                                            }
                                            if (is_object($amount_detail)) {
                                                foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                                    if ($amount_detail_value->date >= $current_section1) {
                                                        $previous_deposit += $amount_detail_value->amount;
                                                        $previous_fine = $previous_fine + $amount_detail_value->amount_fine;
                                                        $previous_discount = $previous_discount + $amount_detail_value->amount_discount;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            $data['student_pending_fee'] = ($previous+($previous_monthfee))-($previous_deposit+$arrear_deposit+$arrear_discount);
                            // echo "2. (".$previous."+(".$previous_monthfee."))-(".$previous_deposit."+".$previous_discount."+".$arrear_deposit."+".$arrear_discount.")<br>"; 
//                            $data['student_pending_fee'] = (($previous_monthfee))-($previous_deposit+$previous_discount);

                        }
//                    $masterData[] = $data;
                    $masterData[$i] = $data;
                    }
                }
                $data = array();
                $flag = false;
                $data['masterData'] = $masterData;
                $data['school'] = $this->setting_model->getCurrentSchoolName();
                $data['settinglist'] = $setting_result;
                for($i=0; $i<count($data['masterData']); $i++){
                    for($j=0; $j<count($data['masterData'][$i]['student_due_fee']); $j++){
                        if($data['masterData'][$i]['student_due_fee'][$j]->fees != '' ||
                            $data['masterData'][$i]['student_due_fee'][$j]->fees != null){
                            $flag = true;
                        }
                    }

                }
                // die();
//                if($flag){
                    //print_r($masterData);
                   //print_r( $data['image']);
//                    echo json_encode($data);
//                    $this->load->view('studentfee/allChallanFormPDF', $data);
                    $this->load->view('studentfee/allChallanFormSiblingPDF', $data);
//                    $this->load->view('studentfee/allChallanFormEducatorQcPDF', $data);
               /* }
                else{
                    $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Fees not found for the given month!</div>');
                    $data['title'] = 'student fees';
                    $class = $this->class_model->get();
                    $data['classlist'] = $class;
                    $this->load->view('layout/header', $data);
                    $this->load->view('studentfee/studentChallanForm', $data);
                    $this->load->view('layout/footer', $data);
                    //echo json_encode($data);
                }*/

                //$this->load->view('studentfee/challanFormPDF', $data);
//            }

        }

    }
}

?>
