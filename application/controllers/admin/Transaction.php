<?php



if (!defined('BASEPATH'))

    exit('No direct script access allowed');



class Transaction extends Admin_Controller
{



    function __construct()
    {

        parent::__construct();

        $this->sch_setting_detail = $this->setting_model->getSetting();
    }



    function searchtransaction()
    {



        $this->session->set_userdata('top_menu', 'Reports');

        $this->session->set_userdata('sub_menu', 'Reports/finance');



        $data['title'] = 'Search Transaction';

        $data['searchlist'] = $this->customlib->get_searchtype();



        // Initialize search_type

        $search_type = '';

        // Check if form was submitted

        if ($this->input->server('REQUEST_METHOD') == 'POST' && $this->input->post('search_type')) {

            $search_type = $this->input->post('search_type');



            // For period filter, pass the date values directly

            if ($search_type == 'period') {

                $date_from_post = $this->input->post('date_from');

                $date_to_post = $this->input->post('date_to');

                $dates = $this->customlib->get_betweendate($search_type, $date_from_post, $date_to_post);
            } else {

                $dates = $this->customlib->get_betweendate($search_type);
            }

            $data['search_type'] = $search_type;
        } else {

            // Default to this_year if no search type specified

            $search_type = 'this_year';

            $dates = $this->customlib->get_betweendate($search_type);

            $data['search_type'] = $search_type;
        }

        $dateformat = $this->customlib->getSchoolDateFormat();



        $date_from = $dates['from_date'];

        $date_to = $dates['to_date'];



        // Add date values to data array for use in views

        $data['date_from'] = $date_from;

        $data['date_to'] = $date_to;



        $data['collection_title'] = $this->lang->line('collection') . " " . $this->lang->line('report') . " " . $this->lang->line('from') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($date_from)) . " To " . date($this->customlib->getSchoolDateFormat(), strtotime($date_to));

        $data['income_title'] = $this->lang->line('income') . " " . $this->lang->line('report') . " " . $this->lang->line('from') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($date_from)) . " To " . date($this->customlib->getSchoolDateFormat(), strtotime($date_to));

        $data['expense_title'] = $this->lang->line('expense') . " " . $this->lang->line('report') . " " . $this->lang->line('from') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($date_from)) . " To " . date($this->customlib->getSchoolDateFormat(), strtotime($date_to));

        $data['payroll_title'] = $this->lang->line('payroll') . " " . $this->lang->line('report') . " " . $this->lang->line('from') . " " . date($this->customlib->getSchoolDateFormat(), strtotime($date_from)) . " To " . date($this->customlib->getSchoolDateFormat(), strtotime($date_to));

        $date_from = date('Y-m-d', strtotime($date_from));

        $date_to = date('Y-m-d', strtotime($date_to));

        $expenseList = $this->expense_model->search2("", $date_from, $date_to);



        $result = $this->payroll_model->getbetweenpayrollReport($date_from, $date_to);



        $incomeList = $this->income_model->search2("", $date_from, $date_to);

        $feeList = $this->studentfeemaster_model->getFeeBetweenDate($date_from, $date_to);

        $data['expenseList'] = $expenseList;

        $data['incomeList'] = $incomeList;

        $data['feeList'] = $feeList;

        $data['payrollList'] = $result;







        $this->load->view('layout/header', $data);

        $this->load->view('admin/transaction/searchtransaction', $data);

        $this->load->view('layout/footer', $data);
    }



    function studentacademicreport()
    {



        if (!$this->rbac->hasPrivilege('balance_fees_report', 'can_view')) {

            access_denied();
        }



        $this->session->set_userdata('top_menu', 'Reports');

        $this->session->set_userdata('sub_menu', 'Reports/finance');

        $this->session->set_userdata('subsub_menu', 'Reports/finance/studentacademicreport');

        $data['title'] = 'student fee';

        $data['title'] = 'student fee';

        $class = $this->class_model->get();

        $data['sch_setting'] = $this->sch_setting_detail;

        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;

        $data['classlist'] = $class;

        $class_id = $this->input->post('class_id');

        $section_id = $this->input->post('section_id');

        $feetype = $this->input->post('feetype');

        $feetype_arr = $this->input->post('feetype_arr');

        $data['section_list'] = $this->section_model->getClassBySection($this->input->post('class_id'));

        $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'trim|required|xss_clean');

        $this->form_validation->set_rules('section_id', $this->lang->line('section'), 'trim|required|xss_clean');





        if ($this->form_validation->run() == false) {

            $data['student_due_fee'] = array();

            $data['resultarray'] = array();



            $data['class_id'] = "";

            $data['section_id'] = "";

            $data['feetype'] = "";

            $data['feetype_arr'] = array();
        } else {

            $student_Array = array();



            $section = array();



            $classlist = $this->student_model->getAllClassSection($class_id, $section_id);



            foreach ($classlist as $key => $value) {

                $classid = $value['class_id'];

                $sectionid = $value['section_id'];



                $studentlist = $this->student_model->reportClassSection($classid, $sectionid);



                $student_Array = array();

                if (!empty($studentlist)) {

                    foreach ($studentlist as $key => $eachstudent) {

                        $obj = new stdClass();

                        $obj->name = $this->customlib->getFullName($eachstudent['firstname'], $eachstudent['middlename'], $eachstudent['lastname'], $this->sch_setting_detail->middlename, $this->sch_setting_detail->lastname);

                        $obj->class = $eachstudent['class'];

                        $obj->section = $eachstudent['section'];

                        $obj->admission_no = $eachstudent['admission_no'];

                        $obj->roll_no = $eachstudent['roll_no'];

                        $obj->father_name = $eachstudent['father_name'];

                        $student_session_id = $eachstudent['student_session_id'];

                        $student_total_fees = $this->studentfeemaster_model->getStudentFees($student_session_id);



                        if (!empty($student_total_fees)) {

                            $totalfee = 0;

                            $deposit = 0;

                            $discount = 0;

                            $balance = 0;

                            $fine = 0;

                            foreach ($student_total_fees as $student_total_fees_key => $student_total_fees_value) {



                                if (!empty($student_total_fees_value->fees)) {

                                    foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {

                                        $totalfee = $totalfee + $each_fee_value->amount;



                                        $amount_detail = json_decode($each_fee_value->amount_detail);



                                        if (is_object($amount_detail)) {

                                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                                $deposit = $deposit + $amount_detail_value->amount;

                                                $fine = $fine + $amount_detail_value->amount_fine;

                                                $discount = $discount + $amount_detail_value->amount_discount;
                                            }
                                        }
                                    }
                                }
                            }



                            $obj->totalfee = $totalfee;

                            $obj->payment_mode = "N/A";

                            $obj->deposit = $deposit;

                            $obj->fine = $fine;

                            $obj->discount = $discount;

                            $obj->balance = $totalfee - ($deposit + $discount);
                        } else {



                            $obj->totalfee = 0;

                            $obj->payment_mode = 0;

                            $obj->deposit = 0;

                            $obj->fine = 0;

                            $obj->balance = 0;

                            $obj->discount = 0;
                        }



                        if ($obj->balance > 0) {

                            $student_Array[] = $obj;
                        }
                    }
                }



                $classlistdata[$value['class_id']][] = array('class_name' => $value['class'], 'section' => $value['section_id'], 'section_name' => $value['section'], 'result' => $student_Array);
            }







            $data['student_due_fee'] = $student_Array;

            $data['resultarray'] = $classlistdata;



            $data['class_id'] = $class_id;

            $data['section_id'] = $section_id;

            $data['feetype'] = $feetype;

            $data['feetype_arr'] = $feetype_arr;
        }



        $this->load->view('layout/header', $data);

        $this->load->view('admin/transaction/studentAcademicReport', $data);

        $this->load->view('layout/footer', $data);
    }

    function studentacademicreportMonthly()
    {

        if (!$this->rbac->hasPrivilege('balance_fees_report', 'can_view')) {

            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');

        $this->session->set_userdata('sub_menu', 'transaction/studentacademicreportMonthly');

        $data['title'] = 'student fee';

        $data['title'] = 'student fee';

        $class = $this->class_model->get();

        $data['classlist'] = $class;

        $data['startDate'] = date('Y-m-01');

        $data['endDate'] = date('Y-m-d');

        if ($this->input->server('REQUEST_METHOD') == "GET") {

            $this->load->view('layout/header', $data);

            $this->load->view('admin/transaction/studentAcademicReportMonthly', $data);

            $this->load->view('layout/footer', $data);
        } else {

            $this->form_validation->set_rules('section_id', 'Section', 'trim|required|xss_clean');

            $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');

            if ($this->form_validation->run() == FALSE) {

                $this->load->view('layout/header', $data);

                $this->load->view('admin/transaction/studentAcademicReportMonthly', $data);

                $this->load->view('layout/footer', $data);
            } else {

                $class_id = $this->input->post('class_id');

                $section_id = $this->input->post('section_id');

                $startDate = $this->input->post('start_date');

                $endDate = $this->input->post('end_date');

                $feetype = $this->input->post('feetype');

                $feetype_arr = $this->input->post('feetype_arr');

                $student_Array = array();

                $studentlist = $this->student_model->searchByClassSection($class_id, $section_id);

                if (!empty($studentlist)) {

                    foreach ($studentlist as $key => $eachstudent) {

                        $obj = new stdClass();

                        $obj->name = $eachstudent['firstname'] . " " . $eachstudent['lastname'];

                        $obj->class = $eachstudent['class'];

                        $obj->section = $eachstudent['section'];

                        $obj->admission_no = $eachstudent['admission_no'];

                        $obj->roll_no = $eachstudent['roll_no'];

                        $obj->father_name = $eachstudent['father_name'];

                        $student_session_id = $eachstudent['student_session_id'];

                        $student_total_fees = $this->studentfeemaster_model->getStudentFeesMonthly($student_session_id, $startDate, $endDate);

                        //print_r($student_total_fees);

                        if (!empty($student_total_fees)) {



                            $totalfee = 0;

                            $monthfee = 0;

                            $deposit = 0;

                            $discount = 0;

                            $balance = 0;

                            $fine = 0;

                            $previous_monthfee = 0;

                            $previous_deposit = 0;

                            $previous_discount = 0;

                            $previous_fine = 0;

                            $previous = 0;

                            $code = '';

                            foreach ($student_total_fees as $student_total_fees_key => $student_total_fees_value) {

                                if (!empty($student_total_fees_value->fees)) {

                                    foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {

                                        $totalfee = $totalfee + $each_fee_value->amount;

                                        //$monthfee = $each_fee_value->amount;

                                        if ($each_fee_value->name == 'Balance Master') {

                                            $previous += $each_fee_value->amount;

                                            $code = $each_fee_value->name;
                                        }

                                        $amount_detail = json_decode($each_fee_value->amount_detail);

                                        if ($each_fee_value->due_date >= $startDate && $each_fee_value->due_date <= $endDate) {

                                            $monthfee += $each_fee_value->amount;
                                        }

                                        if (is_object($amount_detail)) {

                                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                                //print_r($amount_detail);

                                                if ($amount_detail_value->date >= $startDate && $amount_detail_value->date <= $endDate) {

                                                    $deposit += $amount_detail_value->amount;

                                                    $fine = $fine + $amount_detail_value->amount_fine;

                                                    $discount = $discount + $amount_detail_value->amount_discount;
                                                }
                                            }
                                        }

                                        if ($each_fee_value->due_date < $startDate) {

                                            $previous_monthfee += $each_fee_value->amount;
                                        }

                                        if (is_object($amount_detail)) {

                                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                                if ($amount_detail_value->date < $startDate) {

                                                    $previous_deposit += $amount_detail_value->amount;

                                                    $previous_fine = $previous_fine + $amount_detail_value->amount_fine;

                                                    $previous_discount = $previous_discount + $amount_detail_value->amount_discount;
                                                }
                                            }
                                        }
                                    }
                                }
                            }

                            $previous_balance = $previous_monthfee + $previous_fine - ($previous_deposit + $previous_discount);

                            /* echo "fee:".$previous_monthfee."<br>";

                             echo "fine:".$previous_fine."<br>";

                             echo "discount:".$previous_discount."<br>";

                             echo "paid:".$previous_deposit."<br>";*/

                            $obj->totalfee = $monthfee;

                            $obj->payment_mode = "N/A";

                            $obj->deposit = $deposit;

                            $obj->fine = $fine;

                            $obj->discount = $discount;

                            $obj->previous = $previous + $previous_balance;

                            $obj->code = $code;

                            $obj->balance = $monthfee + $previous + $fine - ($deposit + $discount) + $previous_balance;
                        } else {



                            $obj->totalfee = "N/A";

                            $obj->payment_mode = "N/A";

                            $obj->deposit = "N/A";

                            $obj->fine = "N/A";

                            $obj->balance = "N/A";

                            $obj->discount = "N/A";

                            $obj->code = "N/A";
                            $obj->previous = "N/A";
                        }

                        $student_Array[] = $obj;
                    }
                }

                $data['student_due_fee'] = $student_Array;

                // exit();

                $data['class_id'] = $class_id;

                $data['section_id'] = $section_id;

                $data['feetype'] = $feetype;

                $data['feetype_arr'] = $feetype_arr;

                $data['startDate'] = $startDate;

                $data['endDate'] = $endDate;

                $this->load->view('layout/header', $data);

                $this->load->view('admin/transaction/studentAcademicReportMonthly', $data);

                $this->load->view('layout/footer', $data);
            }
        }
    }



    function transactionReportView()

    {

        $this->session->set_userdata('top_menu', 'Reports');

        $this->session->set_userdata('sub_menu', 'transaction/transactionReportView');

        $data['startDate'] = date('Y-m-01');

        $data['endDate'] = date('Y-m-d');

        $this->load->view('layout/header', $data);

        $this->load->view('admin/transaction/transactionReportView', $data);

        $this->load->view('layout/footer', $data);
    }



    function transactionReportPDF()

    {

        $date_from = $this->input->post('startDate');

        $date_to = $this->input->post('endDate');

        $expenseList = $this->expense_model->search2("", $date_from, $date_to);

        $incomeList = $this->income_model->search2("", $date_from, $date_to);

        $feeList = $this->studentfeemaster_model->getFeeBetweenDate($date_from, $date_to);

        $data['expenseList'] = $expenseList;

        $data['incomeList'] = $incomeList;

        $data['feeList'] = $feeList;

        $data['startDate'] = $date_from;

        $data['endDate'] = $date_to;

        $data['school'] = $this->setting_model->getCurrentSchoolName();

        $month = date("F", strtotime($date_from));

        $year = date('Y', strtotime($date_from));

        $salary = $this->payroll_model->getpayrollReport($month, $year, 'select');

        $data['salary'] = $salary;

        $this->load->view('admin/transaction/transactionReportPDF', $data);
    }



    function studentFeeReport()

    {

        if (!$this->rbac->hasPrivilege('balance_fees_report', 'can_view')) {

            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Fees Collection');

        $this->session->set_userdata('sub_menu', 'transaction/studentFeeReport');

        $data['title'] = 'student fee';

        $data['title'] = 'student fee';

        $class = $this->class_model->get();

        $data['classlist'] = $class;



        if ($this->input->server('REQUEST_METHOD') == "GET") {

            $this->load->view('layout/header', $data);

            $this->load->view('admin/transaction/studentFeeReport', $data);

            $this->load->view('layout/footer', $data);
        } else {

            $this->form_validation->set_rules('section_id', 'Section', 'trim|required|xss_clean');

            $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');

            if ($this->form_validation->run() == FALSE) {

                $this->load->view('layout/header', $data);

                $this->load->view('admin/transaction/studentFeeReport', $data);

                $this->load->view('layout/footer', $data);
            } else {

                $class_id = $this->input->post('class_id');

                $section_id = $this->input->post('section_id');

                $feetype = $this->input->post('feetype');

                $feetype_arr = $this->input->post('feetype_arr');

                $startDate = date('Y-m-01');

                $endDate = date('Y-m-d');

                $student_Array = array();

                $studentlist = $this->student_model->searchByClassSection($class_id, $section_id);

                if (!empty($studentlist)) {

                    foreach ($studentlist as $key => $eachstudent) {

                        $obj = new stdClass();

                        $obj->name = $eachstudent['firstname'] . " " . $eachstudent['lastname'];

                        $obj->class = $eachstudent['class'];

                        $obj->section = $eachstudent['section'];

                        $obj->admission_no = $eachstudent['admission_no'];

                        $obj->roll_no = $eachstudent['roll_no'];

                        $obj->father_name = $eachstudent['father_name'];

                        $student_session_id = $eachstudent['student_session_id'];

                        $student_total_fees = $this->studentfeemaster_model->getStudentFeesMonthly($student_session_id, $startDate, $endDate);

                        if (!empty($student_total_fees)) {

                            $totalfee = 0;

                            $deposit = 0;

                            $discount = 0;

                            $balance = 0;

                            $fine = 0;

                            foreach ($student_total_fees as $student_total_fees_key => $student_total_fees_value) {

                                if (!empty($student_total_fees_value->fees)) {

                                    foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {

                                        $totalfee =  $each_fee_value->amount;

                                        $amount_detail = json_decode($each_fee_value->amount_detail);

                                        if (is_object($amount_detail)) {

                                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                                $deposit = $deposit + $amount_detail_value->amount;

                                                $fine = $fine + $amount_detail_value->amount_fine;

                                                $discount = $discount + $amount_detail_value->amount_discount;
                                            }
                                        }
                                    }
                                }
                            }

                            $obj->totalfee = $totalfee;

                            $obj->payment_mode = "N/A";

                            $obj->deposit = $deposit;

                            $obj->fine = $fine;

                            $obj->discount = $discount;

                            $obj->balance = $totalfee - ($deposit + $discount);
                        } else {



                            $obj->totalfee = "N/A";

                            $obj->payment_mode = "N/A";

                            $obj->deposit = "N/A";

                            $obj->fine = "N/A";

                            $obj->balance = "N/A";

                            $obj->discount = "N/A";
                        }

                        $student_Array[] = $obj;
                    }
                }





                $data['student_due_fee'] = $student_Array;



                // exit();

                $data['class_id'] = $class_id;

                $data['section_id'] = $section_id;

                $data['feetype'] = $feetype;

                $data['feetype_arr'] = $feetype_arr;

                //print_r($student_Array);

                $this->load->view('layout/header', $data);

                $this->load->view('admin/transaction/studentFeeReport', $data);

                $this->load->view('layout/footer', $data);
            }
        }
    }



    function dayTransactionReportView()

    {

        $this->session->set_userdata('top_menu', 'Reports');

        $this->session->set_userdata('sub_menu', 'transaction/dayTransactionReportView');

        $data['startDate'] = date('Y-m-d');

        $data['endDate'] = date('Y-m-d');

        $this->load->view('layout/header', $data);

        $this->load->view('admin/transaction/dayTransactionReportView', $data);

        $this->load->view('layout/footer', $data);
    }



    function dayTransactionReportPDF()

    {

        $date_from = $this->input->post('startDate');

        $expenseList = $this->expense_model->search2("", $date_from, $date_from);

        $incomeList = $this->income_model->search2("", $date_from, $date_from);

        $PreviousIncomeList = $this->income_model->searchByDate($date_from);

        $feeList = $this->studentfeemaster_model->getFeeBetweenDate($date_from, $date_from);

        $PreviousFeeList = $this->studentfeemaster_model->getPreviousBetweenDate($date_from);

        $data['expenseList'] = $expenseList;

        $data['incomeList'] = $incomeList;

        $data['feeList'] = $feeList;

        $data['PreviousIncomeList'] = $PreviousIncomeList;

        $data['PreviousFeeList'] = $PreviousFeeList;

        $data['startDate'] = $date_from;

        $data['school'] = $this->setting_model->getCurrentSchoolName();

        $month = date("F", strtotime($date_from));

        $year = date('Y', strtotime($date_from));
        $data['school'] = $this->setting_model->getCurrentSchoolName();

        $setting_result = $this->setting_model->get();

        $data['settinglist'] = $setting_result;

        $salary = $this->payroll_model->getpayrollReportByDate($date_from);

        $data['salary'] = $salary;

        //echo json_encode($PreviousFeeList);

        $this->load->view('admin/transaction/dayTransactionReportPDF', $data);
    }



    function daily_transaction_report_view()

    {

        $this->session->set_userdata('top_menu', 'Reports');

        $this->session->set_userdata('sub_menu', 'transaction/daily_transaction_report_view');

        $data['startDate'] = date('Y-m-d');

        $data['endDate'] = date('Y-m-d');

        $this->load->view('layout/header', $data);

        $this->load->view('admin/transaction/dailyTransactionReportView', $data);

        $this->load->view('layout/footer', $data);
    }



    function daily_transaction_report()

    {

        $date_from = $this->input->post('date_from');

        $date_to = $this->input->post('date_to');

        $feeList = $this->studentfeemaster_model->getFeeBetweenDate($date_from, $date_to);

        $data['feeList'] = $feeList;

        $data['startDate'] = $date_from;

        $data['endDate'] = $date_to;

        $data['school'] = $this->setting_model->getCurrentSchoolName();

        $month = date("F", strtotime($date_from));

        $year = date('Y', strtotime($date_from));
        $data['school'] = $this->setting_model->getCurrentSchoolName();

        $setting_result = $this->setting_model->get();

        $data['settinglist'] = $setting_result;

        //        echo json_encode($feeList);

        $this->load->view('admin/transaction/dailyTransactionReportPDF', $data);
    }



    function transactionReportPDFDownload()

    {

        try {

            $date_from = $this->input->post('startDate');

            $date_to = $this->input->post('endDate');



            // Validate dates

            if (empty($date_from) || empty($date_to)) {

                show_error('Invalid date parameters', 400);

                return;
            }



            // Get data

            $expenseList = $this->expense_model->search2("", $date_from, $date_to);

            $incomeList = $this->income_model->search2("", $date_from, $date_to);

            $feeList = $this->studentfeemaster_model->getFeeBetweenDate($date_from, $date_to);

            $data['expenseList'] = $expenseList;

            $data['incomeList'] = $incomeList;

            $data['feeList'] = $feeList;

            $data['startDate'] = $date_from;

            $data['endDate'] = $date_to;

            $data['school'] = $this->setting_model->getCurrentSchoolName();



            $month = date("F", strtotime($date_from));

            $year = date('Y', strtotime($date_from));

            $salary = $this->payroll_model->getpayrollReport($month, $year, 'select');

            $data['salary'] = $salary;



            // Load mPDF library

            $this->load->library('Pdf');



            // Generate filename

            $filename = 'Transaction_Report_' . date('Y-m-d', strtotime($date_from)) . '_to_' . date('Y-m-d', strtotime($date_to)) . '.pdf';



            // Get HTML content - use simplified view for better mPDF compatibility

            $html = $this->load->view('admin/transaction/transactionReportPDFSimple', $data, TRUE);



            // Create PDF

            $this->Pdf->loadHtml($html);

            $this->Pdf->setPaper('A4', 'portrait');

            $this->Pdf->render();



            // Output PDF

            $this->Pdf->stream($filename, array("Attachment" => 1));
        } catch (Exception $e) {

            // Log the error

            log_message('error', 'PDF Generation Error: ' . $e->getMessage());



            // Try fallback method using existing mPDF library

            try {

                $this->transactionReportPDFDownloadFallback($date_from, $date_to, $data);
            } catch (Exception $e2) {

                log_message('error', 'PDF Fallback Error: ' . $e2->getMessage());

                show_error('PDF generation failed. Please try again or contact administrator.', 500);
            }
        }
    }



    function transactionReportPDFDownloadFallback($date_from, $date_to, $data)

    {

        // Use existing mPDF library as fallback

        $this->load->library('m_pdf');



        // Generate filename

        $filename = 'Transaction_Report_' . date('Y-m-d', strtotime($date_from)) . '_to_' . date('Y-m-d', strtotime($date_to)) . '.pdf';



        // Get HTML content

        $html = $this->load->view('admin/transaction/transactionReportPDFSimple', $data, TRUE);



        // Create PDF using existing library

        $this->m_pdf->pdf->WriteHTML($html);

        $this->m_pdf->pdf->Output($filename, "D");
    }
}
