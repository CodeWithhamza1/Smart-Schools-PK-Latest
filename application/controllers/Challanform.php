<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
class Challanform extends Admin_Controller
{
    function __construct() {
        parent::__construct();
        $this->load->model("Student_model");
        $this->load->model("studentfee_model");
        $this->load->model("class_model");
        $this->load->model("voucher_setting_model");



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
        $this->load->view('studentfee/ChallanForm', $data);
        $this->load->view('layout/footer', $data);
    }

    function getStudents(){
        $class = $this->input->get('CLASS_ID');
        $section = $this->input->get('SECTION_ID');
        $this->load->model("ChallanModel");
        $students = $this->ChallanModel->searchByClassSection($class,$section);
        echo json_encode($students);
    }

    function generateChallanForm(){
        $std = $this->input->post('student');
        $year = $this->input->post('year');
        $month = $this->input->post('month');
        $class_id = $this->input->post('class_id');
        $section_id = $this->input->post('section_id');
        $masterData = array();
        $this->form_validation->set_rules('student', 'Student', 'trim|required|xss_clean');
        $this->form_validation->set_rules('year', 'Year', 'trim|required|xss_clean');
        $this->form_validation->set_rules('month', 'Month', 'trim|required|xss_clean');
        $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');
        $this->form_validation->set_rules('section_id', 'Section', 'trim|required|xss_clean');


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
            }elseif($year<date('Y')){
//            $year = $year-1;
                $current_section_date1 = $year.'-0'.$startMonth1.'-01';
                $current_section1= date('Y-m-01', strtotime($current_section_date1));;
            }
            $query_date = $year.'-'.$month.'-'.'01';
            $startDate = date('Y-m-01', strtotime($query_date));
            $data['school'] = $this->setting_model->getCurrentSchoolName();
            $setting_result = $this->setting_model->get();
            $data['settinglist'] = $setting_result;
            $setting_id = isset($setting_result[0]['id']) ? (int) $setting_result[0]['id'] : 1;
            $voucher_settings = $this->voucher_setting_model->getResolved($setting_id);
            $data['voucher_settings'] = $voucher_settings;
            if($std != 'ALL'){
                $student = $this->student_model->get($std);
                $data['title'] = 'Student Detail';
                $data['student'] = $student;
                $this->load->model("ChallanModel");
                $student_due_fee = $this->ChallanModel->getStudentFees($student['student_session_id'],$month,$year);
                $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($student['student_session_id']);
                $student_pending_fee = $this->ChallanModel->getStudentPreviousFees($student['student_session_id'],$month,$year);
                $data['student_discount_fee'] = $student_discount_fee;
                $data['student_due_fee'] = $student_due_fee;

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
                            } else {
                                $amount_detail = json_decode($each_fee_value->amount_detail);
                                $m = date('m', strtotime($each_fee_value->due_date));
                                $previous_month_date_m = date('m', strtotime($previous_month_date));
                                if ($each_fee_value->due_date >= $current_section1) {
                                    //                if($each_fee_value->name != 'Balance Master')
                                    //                    {
                                    //                                        foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                    //                            if ($amount_detail_value->received_by != 1){
                                    $previous_monthfee += $each_fee_value->amount;
                                    //                            }
                                    //                        }
                                    //                    }
                                }

                                if (is_object($amount_detail)) {
                                    foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                        $y = date('Y', strtotime($amount_detail_value->date));
                                        $m = date('m', strtotime($amount_detail_value->date));
                                        $previous_month_date_m = date('m', strtotime($previous_month_date));

                                        if ($amount_detail_value->date >= $current_section1) {
                                            $previous_deposit += $amount_detail_value->amount;
                                            $previous_fine = $previous_fine + $amount_detail_value->amount_fine;
                                            // if ($amount_detail_value->received_by != 1){
                                            $previous_discount = $previous_discount + $amount_detail_value->amount_discount;
                                            // }
                                        }
                                    }

                                }
                            }
                        }
                    }
                }
                // $data['student_pending_fee'] = ($previous+($previous_monthfee))-($previous_deposit+$previous_discount+$arrear_deposit+$arrear_discount);

                // $data['student_pending_fee'] = ($previous_monthfee-$previous)-($previous_deposit+$previous_discount);
                   // $data['student_pending_fee'] = ($previous_monthfee-$previous)-(($previous_deposit-$arrear_deposit)+$previous_discount);
                $data['student_pending_fee'] = ($previous_monthfee)-($previous_deposit+$previous_discount);
                $data['arrear_deposit'] = $arrear_deposit+$arrear_discount;
// print_r($arrear_deposit);
// die();


//                $data['student_pending_fee'] = $student_pending_fee;
                //echo json_encode($data);
                if(count($data['student_due_fee'][0]->fees) > 0 || count($data['student_due_fee'][1]->fees) > 0 || count($data['student_due_fee'][2]->fees) > 0  || count($data['student_due_fee'][3]->fees) > 0 || count($data['student_due_fee'][4]->fees) > 0){
//                    print_r($student_due_fee);
                    $this->load->view('studentfee/challanFormPDF', $data);
                }
                else{
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
            else{
                $data['arrear_depositt'] = 0;
                $this->load->model("ChallanModel");
                $students = $this->ChallanModel->searchByClassSection($class_id,$section_id);
                for($i = 0; $i<count($students); $i++){
                    $student = $this->student_model->get($students[$i]['id']);
                    $data['title'] = 'Student Detail '.$i;
                    $data['student'] = $student;
                    $student_due_fee = $this->ChallanModel->getStudentFees($student['student_session_id'],$month,$year);
                    $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($student['student_session_id']);
                    $student_pending_fee = $this->ChallanModel->getStudentPreviousFees($student['student_session_id'],$month,$year);
                    $data['student_discount_fee'] = $student_discount_fee;
                    $data['student_due_fee'] = $student_due_fee;
                    $data['arrear_depositt'] = 0;
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
                                            $arrear_depositt[$i] = $amount_detail_value->amount; //echo $arrear_depositt[$i];
                                            $arrear_discount += $amount_detail_value->amount_discount;


                                        }

                                    }
                                }
                                else {
                                    $amount_detail = json_decode($each_fee_value->amount_detail);
                                    $m = date('m', strtotime($each_fee_value->due_date));
                                    $previous_month_date_m = date('m', strtotime($previous_month_date));
                                    if ($each_fee_value->due_date >= $current_section1) {
                                        $previous_monthfee += $each_fee_value->amount;
                                    }


                                    if (is_object($amount_detail)) {
                                        foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                            $y = date('Y', strtotime($amount_detail_value->date));
                                            $m = date('m', strtotime($amount_detail_value->date));
                                            $previous_month_date_m = date('m', strtotime($previous_month_date));
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


//                    $data['student_pending_fee'] = ($previous+($previous_monthfee))-($previous_deposit+$previous_discount+$arrear_deposit+$arrear_discount);
//                    $data['student_pending_fee'] = (($previous_monthfee))-($previous_deposit+$previous_discount);
//                      $data['student_pending_fee'] = ($previous_monthfee-$previous)-($previous_deposit+$previous_discount);
//                    $data['student_pending_fee'] = $student_pending_fee;
                    $data['student_pending_fee'] = ($previous_monthfee)-($previous_deposit+$previous_discount);
                    $data['arrear_deposit'] = $arrear_deposit+$arrear_discount;
                    $masterData[$i] = $data;

                }

                $data = array();
                $flag = false;

                $data['masterData'] = $masterData;
                $data['school'] = $this->setting_model->getCurrentSchoolName();
                $data['settinglist'] = $setting_result;
                $data['voucher_settings'] = $voucher_settings;
                for($i=0; $i<count($data['masterData']); $i++){
                    for($j=0; $j<count($data['masterData'][$i]['student_due_fee']); $j++){
                        if($data['masterData'][$i]['student_due_fee'][$j]->fees != '' ||
                            $data['masterData'][$i]['student_due_fee'][$j]->fees != null){

                            $flag = true;
                        }
                    }
                }
                if($flag){
                    //print_r($masterData);
                    //print_r( $data['image']);

                    $this->load->view('studentfee/allChallanFormPDF', $data);
                }
                else{
                    $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Fees not found for the given month!</div>');
                    $data['title'] = 'student fees';
                    $class = $this->class_model->get();
                    $data['classlist'] = $class;
                    $this->load->view('layout/header', $data);
                    $this->load->view('studentfee/studentChallanForm', $data);
                    $this->load->view('layout/footer', $data);
                    //echo json_encode($data);
                }

                //$this->load->view('studentfee/challanFormPDF', $data);
            }

        }

    }
}

?>
