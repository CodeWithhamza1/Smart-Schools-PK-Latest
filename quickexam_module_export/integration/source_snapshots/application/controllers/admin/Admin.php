<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
} 

/**
 * @property Studentfeemaster_model $studentfeemaster_model
 */
class Admin extends Admin_Controller
{
    protected $balance_group;

    public function __construct()
    {
        parent::__construct();
        $this->load->model("classteacher_model");
        $this->load->model("quickexam_model");
        $this->load->model("section_model");
        $this->load->model("Staff_model");
        $this->load->model("stuattendence_model");
        $this->load->model("studentfeemaster_model");
        $this->load->config('ci-blog');
        $this->load->library('Enc_lib');
        $this->balance_group = $this->config->item('ci_balance_group');
        $this->sch_setting_detail = $this->setting_model->getSetting();

    }

    public function unauthorized()
    {
        $data = array();
        $this->load->view('layout/header', $data);
        $this->load->view('unauthorized', $data);
        $this->load->view('layout/footer', $data);
    } 


 
    public function dashboard_new()
    {

        $role            = $this->customlib->getStaffRole();
        $role_id         = json_decode($role)->id;
        $data['role_id'] = $role_id;

        $staffid       = $this->customlib->getStaffID();
        $notifications = $this->notification_model->getUnreadStaffNotification($staffid, $role_id);

        $data['notifications'] = $notifications;
        $input                 = $this->setting_model->getCurrentSessionName();

        list($a, $b)  = explode('-', $input);
        $Current_year = $a;
        if (strlen($b) == 2) {
            $Next_year = substr($a, 0, 2) . $b;
        } else {
            $Next_year = $b;
        }
        $data['mysqlVersion'] = $this->setting_model->getMysqlVersion();
        $data['sqlMode']      = $this->setting_model->getSqlMode();
        //========================== Current Attendence ==========================
        $current_date       = date('Y-m-d');
        $data['title']      = 'Dashboard';
        $Current_start_date = date('01');
        $Current_date       = date('d');
        $Current_month      = date('m');
        $month_collection   = 0;
        $month_expense      = 0;
        $total_students     = 0;
        $total_teachers     = 0;
        $ar                 = $this->startmonthandend();
        $year_str_month     = $Current_year . '-' . $ar[0] . '-01';
        $year_end_month     = date("Y-m-t", strtotime($Next_year . '-' . $ar[1] . '-01'));
        $getDepositeAmount  = $this->studentfeemaster_model->getDepositAmountBetweenDate($year_str_month, $year_end_month);
        //======================Current Month Collection ==============================
        $first_day_this_month = date('Y-m-01');
        $month_collection     = $this->getDashboardMonthlyCollection($first_day_this_month, $current_date);
        $data['today_collection_amount'] = $this->getDashboardMonthlyCollection($current_date, $current_date);
        $data['today_collection_url']    = site_url('studentfee/today_collection_report');
        $expense                  = $this->expense_model->getTotalExpenseBwdate($first_day_this_month, $current_date);
        if (!empty($expense)) {
            $month_expense = $month_expense + $expense->amount;
        }

        $data['month_collection'] = $month_collection;
        $data['month_expense']    = $month_expense;

        $tot_students = $this->studentsession_model->getTotalStudentBySession();
        if (!empty($tot_students)) {
            $total_students = $tot_students->total_student;
        }

        $data['total_students'] = $total_students;

        $tot_roles = $this->role_model->get();

        foreach ($tot_roles as $key => $value) {

            $count_roles[$value["name"]] = $this->role_model->count_roles($value["id"]);

        }
        $data["roles"] = $count_roles;

        //======================== get collection by month ==========================
        $start_month = strtotime($year_str_month);
        $start       = strtotime($year_str_month);
        $end         = strtotime($year_end_month);
        $coll_month  = array();
        $s           = array();
        $total_month = array();
        while ($start_month <= $end) {
            $total_month[] = date('M', $start_month);
            $month_start   = date('Y-m-d', $start_month);
            $month_end     = date("Y-m-t", $start_month);
            $return        = $this->whatever($getDepositeAmount, $month_start, $month_end);
            if ($return) {
                $s[] = $return;
            } else {
                $s[] = "0.00";
            }

            $start_month = strtotime("+1 month", $start_month);
        }
        //======================== getexpense by month ==============================
        $ex                  = array();
        $start_session_month = strtotime($year_str_month);
        while ($start_session_month <= $end) {

            $month_start = date('Y-m-d', $start_session_month);
            $month_end   = date("Y-m-t", $start_session_month);

            $expense_monthly = $this->expense_model->getTotalExpenseBwdate($month_start, $month_end);

            if (!empty($expense_monthly)) {
                $amt  = 0;
                $ex[] = $amt + $expense_monthly->amount;
            }

            $start_session_month = strtotime("+1 month", $start_session_month);
        }

        $data['yearly_collection'] = $s;
        $data['yearly_expense']    = $ex;
        $data['total_month']       = $total_month;

        //======================= current month collection /expense ===================
        // hardcoded '01' for first day
        $startdate       = date('m/01/Y');
        $enddate         = date('m/t/Y');
        $start           = strtotime($startdate);
        $end             = strtotime($enddate);
        $currentdate     = $start;
        $month_days      = array();
        $days_collection = array();
        while ($currentdate <= $end) {
            $cur_date          = date('Y-m-d', $currentdate);
            $month_days[]      = date('d', $currentdate);
            $coll_amt          = $this->whatever($getDepositeAmount, $cur_date, $cur_date);
            $days_collection[] = $coll_amt;
            $currentdate       = strtotime('+1 day', $currentdate);
        }
        $data['current_month_days'] = $month_days;
        $data['days_collection']    = $days_collection;

        //======================= current month /expense ==============================
        // hardcoded '01' for first day

        $startdate    = date('m/01/Y');
        $enddate      = date('m/t/Y');
        $start        = strtotime($startdate);
        $end          = strtotime($enddate);
        $currentdate  = $start;
        $days_expense = array();
        while ($currentdate <= $end) {
            $cur_date       = date('Y-m-d', $currentdate);
            $month_days[]   = date('d', $currentdate);
            $currentdate    = strtotime('+1 day', $currentdate);
            $ct             = $this->getExpensebyday($cur_date);
            $days_expense[] = $ct;
        }

        $data['days_expense']        = $days_expense;
        $student_fee_history         = $this->studentfee_model->getTodayStudentFees();
        $data['student_fee_history'] = $student_fee_history;

        $event_colors         = array("#03a9f4", "#c53da9", "#757575", "#8e24aa", "#d81b60", "#7cb342", "#fb8c00", "#fb3b3b");
        $data["event_colors"] = $event_colors;
        $userdata             = $this->customlib->getUserData();
        $data["role"]         = $userdata["user_type"];
        $start_date           = date('Y-m-01');
        $end_date             = date('Y-m-t');
        $student_due_fee      = $this->studentfeemaster_model->getFeesAwaiting($start_date, $end_date);

        $data['fees_awaiting'] = $student_due_fee;
        $previous_balance_data = $this->getPreviousBalanceStudentsData($start_date, $end_date);
        $data['previous_balance_students_count'] = $previous_balance_data['total_students'];
        $data['previous_balance_students_amount'] = $previous_balance_data['total_amount'];
        $data['previous_balance_only_amount'] = isset($previous_balance_data['total_previous_balance_amount']) ? $previous_balance_data['total_previous_balance_amount'] : 0;
        $data['previous_pending_fee_amount'] = isset($previous_balance_data['total_previous_pending_fee_amount']) ? $previous_balance_data['total_previous_pending_fee_amount'] : 0;

        $total_fess    = 0;
        $total_paid    = 0;
        $total_unpaid  = 0;
        $total_partial = 0;

        if (!empty($data['fees_awaiting'])) {

            foreach ($data['fees_awaiting'] as $awaiting_key => $awaiting_value) {

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
                        $amt_                = 0;
                        foreach ($amount_paid_details as $amount_paid_detail_key => $amount_paid_detail_value) {
                            $amt_ = $amt_ + $amount_paid_detail_value->amount;
                        }

                        if (($amt_ + $amount_paid_detail_value->amount_discount) >= $amount_to_be_taken) {
                            $total_paid++;
                        } elseif (($amt_ + $amount_paid_detail_value->amount_discount) < $amount_to_be_taken) {
                            $total_partial++;
                        }
                    } else {
                        $total_unpaid++;
                    }

                }
            }
        }

        $data['incomegraph'] = $this->income_model->getIncomeHeadsData($start_date, $end_date);
        $data['expensegraph'] = $this->expense_model->getExpenseHeadData($start_date, $end_date);
        $enquiry       = $this->admin_model->getAllEnquiryCount($start_date, $end_date);
        $total_counter = $total_paid + $total_unpaid + $total_partial;

        $data['fees_overview'] = array(
            'total_unpaid'     => $total_unpaid,
            'unpaid_progress'  => ($total_counter > 0) ? (($total_unpaid * 100) / $total_counter) : 0,
            'total_paid'       => $total_paid,
            'paid_progress'    => ($total_counter > 0) ? (($total_paid * 100) / $total_counter) : 0,
            'total_partial'    => $total_partial,
            'partial_progress' => ($total_counter > 0) ? (($total_partial * 100) / $total_counter) : 0,
        );

        $total_enquiry = $enquiry['total'];

        if ($total_enquiry > 0) {

            $data['enquiry_overview'] = array(
                'won'              => $enquiry['complete'],
                'won_progress'     => ($enquiry['complete'] * 100) / $total_enquiry,
                'active'           => $enquiry['active'],
                'active_progress'  => ($enquiry['active'] * 100) / $total_enquiry,
                'passive'          => $enquiry['passive'],
                'passive_progress' => ($enquiry['passive'] * 100) / $total_enquiry,
                'dead'             => $enquiry['dead'],
                'dead_progress'    => ($enquiry['dead'] * 100) / $total_enquiry,
                'lost'             => $enquiry['lost'],
                'lost_progress'    => ($enquiry['lost'] * 100) / $total_enquiry,
            );

        } else {

            $data['enquiry_overview'] = array(
                'won'              => 0,
                'won_progress'     => 0,
                'active'           => 0,
                'active_progress'  => 0,
                'passive'          => 0,
                'passive_progress' => 0,
                'dead'             => 0,
                'dead_progress'    => 0,
                'lost'             => 0,
                'lost_progress'    => 0,
            );

        }

        $data['total_paid'] = $total_paid;
        $data['total_fees'] = $total_fess;
        if ($total_fess > 0) {
            $data['fessprogressbar'] = ($total_paid * 100) / $total_fess;
        } else {
            $data['fessprogressbar'] = 0;
        }

        $data['total_enquiry']  = $total_enquiry  = $enquiry['total'];
        $data['total_complete'] = $complete_enquiry = $enquiry['complete'];
        if ($total_enquiry > 0) {
            $data['fenquiryprogressbar'] = ($complete_enquiry * 100) / $total_enquiry;
        } else {
            $data['fenquiryprogressbar'] = 0;
        }

        $bookoverview      = $this->book_model->bookoverview($start_date, $end_date);
        $bookduereport     = $this->bookissue_model->dueforreturn($start_date, $end_date);
        $forreturndata     = $this->bookissue_model->forreturn($start_date, $end_date);
        $dueforreturn      = $bookduereport[0]['total'];
        $forreturn         = $forreturndata[0]['total'];
        $total_qty         = $bookoverview[0]['qty'];
        $total_issued      = $bookoverview[0]['total_issue'];
        $availble          = '0';
        $availble_progress = 0;
        $issued_progress   = 0;
        if ($total_qty > 0) {
            $availble          = $total_qty - $total_issued;
            $availble_progress = ($availble * 100) / $total_qty;
            $issued_progress   = ($total_issued * 100) / $total_qty;
        }
        $data['book_overview'] = array(
            'total'             => $total_qty,
            'total_progress'    => 100,
            'availble'          => $availble,
            'availble_progress' => round($availble_progress, 2),
            'total_issued'      => $total_issued,
            'issued_progress'   => round($issued_progress, 2),
            'dueforreturn'      => $dueforreturn,
            'forreturn'         => $forreturn,
        );

        $Attendence                   = $this->stuattendence_model->getTodayDayAttendance($total_students);
        $data['attendence_data']      = $Attendence;
        $Staffattendence              = $this->Staff_model->getTodayDayAttendance();
        $data['Staffattendence_data'] = $Staffattendence;
        $getTotalStaff                = $this->Staff_model->getTotalStaff();
        $data['getTotalStaff_data']   = $getTotalStaff;
        if ($getTotalStaff > 0) {$percentTotalStaff_data = ($Staffattendence * 100) / ($getTotalStaff);} else { $percentTotalStaff_data = '0';}
        $data['percentTotalStaff_data'] = $percentTotalStaff_data;
        $data['attendance_widget'] = $this->getDashboardAttendanceWidgetData();
        $data['quick_exam_widget'] = $this->quickexam_model->getDashboardWidgetData($this->setting_model->getCurrentSession());
        $data['sch_setting']            = $this->sch_setting_detail;

        // Get pending annual charges for current month
        try {
            $data['pending_annual_charges'] = $this->getPendingAnnualCharges();
        } catch (Exception $e) {
            log_message('error', 'Error getting pending annual charges in dashboard: ' . $e->getMessage());
            $data['pending_annual_charges'] = array(
                'total_students' => 0,
                'total_amount' => 0,
                'total_charges' => 0
            );
        }
        
        // Ensure the variable is always set
        if (!isset($data['pending_annual_charges']) || !is_array($data['pending_annual_charges'])) {
            $data['pending_annual_charges'] = array(
                'total_students' => 0,
                'total_amount' => 0,
                'total_charges' => 0
            );
        }

        if ($data['sch_setting']->attendence_type == 0) {
            $data['std_graphclass'] = "col-lg-3 col-md-6 col-sm-6";
        } else {
            $data['std_graphclass'] = "col-lg-4 col-md-6 col-sm-6";
        }
       
        $this->load->view('layout/header', $data);
        $this->load->view('admin/dashboard', $data);
        $this->load->view('layout/footer', $data);
    }

    public function dashboard()
    {

        $role            = $this->customlib->getStaffRole();
        $role_id         = json_decode($role)->id;
        $data['role_id'] = $role_id;

        $staffid       = $this->customlib->getStaffID();
        $notifications = $this->notification_model->getUnreadStaffNotification($staffid, $role_id);

        $data['notifications'] = $notifications;
        $input                 = $this->setting_model->getCurrentSessionName();
        $classData = $this->admin_model->getClasses();
        $data['classes'] = $classData;
        list($a, $b)  = explode('-', $input);
        $Current_year = $a;
        if (strlen($b) == 2) {
            $Next_year = substr($a, 0, 2) . $b;
        } else {
            $Next_year = $b;
        }
        $data['mysqlVersion'] = $this->setting_model->getMysqlVersion();
        $data['sqlMode']      = $this->setting_model->getSqlMode();
        //========================== Current Attendence ==========================
        $current_date       = date('Y-m-d');
        $data['title']      = 'Dashboard';
        $Current_start_date = date('01');
        $Current_date       = date('d');
        $Current_month      = date('m');
        $month_collection   = 0;
        $month_income       = 0;
        $month_expense      = 0;
        $total_students     = 0;
        $total_teachers     = 0;
        $ar                 = $this->startmonthandend();
        $year_str_month     = $Current_year . '-' . $ar[0] . '-01';
        $year_end_month     = date("Y-m-t", strtotime($Next_year . '-' . $ar[1] . '-01'));
        $getDepositeAmount  = $this->studentfeemaster_model->getDepositAmountBetweenDate($year_str_month, $year_end_month);
        //======================Current Month Collection ==============================
        $first_day_this_month = date('Y-m-01');
        $month_collection     = $this->getDashboardMonthlyCollection($first_day_this_month, $current_date);
        $data['today_collection_amount'] = $this->getDashboardMonthlyCollection($current_date, $current_date);
        $data['today_collection_url']    = site_url('studentfee/today_collection_report');
        $expense                  = $this->expense_model->getTotalExpenseBwdate($first_day_this_month, $current_date);
        $income                  = $this->income_model->getTotalExpenseBwdate($first_day_this_month, $current_date);
        if (!empty($expense)) {
            $month_expense = $month_expense + $expense->amount;
        }
        if (!empty($income)) {
            $month_income = $month_income + $income->amount;
        }

        $data['month_collection'] = $month_collection;
        $data['month_expense']    = $month_expense;
        $data['month_income']    = $month_income;

        $tot_students = $this->studentsession_model->getTotalStudentBySession();
        if (!empty($tot_students)) {
            $total_students = $tot_students->total_student;
        }

        $data['total_students'] = $total_students;

        $tot_roles = $this->role_model->get();

        foreach ($tot_roles as $key => $value) {

            $count_roles[$value["name"]] = $this->role_model->count_roles($value["id"]);

        }
        $data["roles"] = $count_roles;

        //======================== get collection by month ==========================
        $start_month = strtotime($year_str_month);
        $start       = strtotime($year_str_month);
        $end         = strtotime($year_end_month);
        $coll_month  = array();
        $s           = array();
        $total_month = array();
        while ($start_month <= $end) {
            $total_month[] = date('M', $start_month);
            $month_start   = date('Y-m-d', $start_month);
            $month_end     = date("Y-m-t", $start_month);
            $return        = $this->whatever($getDepositeAmount, $month_start, $month_end);
            if ($return) {
                $s[] = $return;
            } else {
                $s[] = "0.00";
            }

            $start_month = strtotime("+1 month", $start_month);
        }
        //======================== getexpense by month ==============================
        $ex                  = array();
        $start_session_month = strtotime($year_str_month);
        while ($start_session_month <= $end) {

            $month_start = date('Y-m-d', $start_session_month);
            $month_end   = date("Y-m-t", $start_session_month);

            $expense_monthly = $this->expense_model->getTotalExpenseBwdate($month_start, $month_end);

            if (!empty($expense_monthly)) {
                $amt  = 0;
                $ex[] = $amt + $expense_monthly->amount;
            }

            $start_session_month = strtotime("+1 month", $start_session_month);
        }

        $data['yearly_collection'] = $s;
        $data['yearly_expense']    = $ex;
        $data['total_month']       = $total_month;

        //======================= current month collection /expense ===================
        // hardcoded '01' for first day
        $startdate       = date('m/01/Y');
        $enddate         = date('m/t/Y');
        $start           = strtotime($startdate);
        $end             = strtotime($enddate);
        $currentdate     = $start;
        $month_days      = array();
        $days_collection = array();
        while ($currentdate <= $end) {
            $cur_date          = date('Y-m-d', $currentdate);
            $month_days[]      = date('d', $currentdate);
            $coll_amt          = $this->whatever($getDepositeAmount, $cur_date, $cur_date);
            $days_collection[] = $coll_amt;
            $currentdate       = strtotime('+1 day', $currentdate);
        }
        $data['current_month_days'] = $month_days;
        $data['days_collection']    = $days_collection;

        //======================= current month /expense ==============================
        // hardcoded '01' for first day

        $startdate    = date('m/01/Y');
        $enddate      = date('m/t/Y');
        $start        = strtotime($startdate);
        $end          = strtotime($enddate);
        $currentdate  = $start;
        $days_expense = array();
        while ($currentdate <= $end) {
            $cur_date       = date('Y-m-d', $currentdate);
            $month_days[]   = date('d', $currentdate);
            $currentdate    = strtotime('+1 day', $currentdate);
            $ct             = $this->getExpensebyday($cur_date);
            $days_expense[] = $ct;
        }

        $data['days_expense']        = $days_expense;
        $student_fee_history         = $this->studentfee_model->getTodayStudentFees();
        $data['student_fee_history'] = $student_fee_history;

        $event_colors         = array("#03a9f4", "#c53da9", "#757575", "#8e24aa", "#d81b60", "#7cb342", "#fb8c00", "#fb3b3b");
        $data["event_colors"] = $event_colors;
        $userdata             = $this->customlib->getUserData();
        $data["role"]         = $userdata["user_type"];
        $start_date           = date('Y-m-01');
        $end_date             = date('Y-m-t');
        $student_due_fee      = $this->studentfeemaster_model->getFeesAwaitingForDashboard($start_date, $end_date);

        $data['fees_awaiting'] = $student_due_fee;
        $previous_balance_data = $this->getPreviousBalanceStudentsData($start_date, $end_date);
        $data['previous_balance_students_count'] = $previous_balance_data['total_students'];
        $data['previous_balance_students_amount'] = $previous_balance_data['total_amount'];
        $data['previous_balance_only_amount'] = isset($previous_balance_data['total_previous_balance_amount']) ? $previous_balance_data['total_previous_balance_amount'] : 0;
        $data['previous_pending_fee_amount'] = isset($previous_balance_data['total_previous_pending_fee_amount']) ? $previous_balance_data['total_previous_pending_fee_amount'] : 0;
//        echo "<pre>"; print_r($student_due_fee); echo "<br>";


        $total_fess    = 0;
        $total_paid    = 0;
        $total_unpaid  = 0;
        $total_partial = 0;

        if (!empty($data['fees_awaiting'])) {

            foreach ($data['fees_awaiting'] as $awaiting_key => $awaiting_value) {

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
                        $amt_                = 0;
                        $discount_           = 0;
                        foreach ($amount_paid_details as $amount_paid_detail_key => $amount_paid_detail_value) {
                            $amt_ = $amt_ + $amount_paid_detail_value->amount;
                            $discount_ = $discount_ + $amount_paid_detail_value->amount_discount;
                        }
                        // echo $amt_ .'+'. $discount_ .'>='. $amount_to_be_taken ."<br>";
                        if (($amt_ + $discount_) >= $amount_to_be_taken) {
                            $total_paid++;
                        } elseif (($amt_ + $amount_paid_detail_value->amount_discount) < $amount_to_be_taken) {
                            $total_partial++;
                        }
                    } else {
                        $total_unpaid++;
                    }

                }
            }
        }

        $data['incomegraph'] = $this->income_model->getIncomeHeadsData($start_date, $end_date);
        $data['expensegraph'] = $this->expense_model->getExpenseHeadData($start_date, $end_date);
        $enquiry       = $this->admin_model->getAllEnquiryCount($start_date, $end_date);
        $total_counter = $total_paid + $total_unpaid + $total_partial;

        $data['fees_overview'] = array(
            'total_unpaid'     => $total_unpaid,
            'unpaid_progress'  => ($total_counter > 0) ? (($total_unpaid * 100) / $total_counter) : 0,
            'total_paid'       => $total_paid,
            'paid_progress'    => ($total_counter > 0) ? (($total_paid * 100) / $total_counter) : 0,
            'total_partial'    => $total_partial,
            'partial_progress' => ($total_counter > 0) ? (($total_partial * 100) / $total_counter) : 0,
        );

        // Calculate current-month receivable only. Previous balances are shown in their own widget.
        $fee_calculation = $this->calculateCompleteFeeBalance($start_date, $end_date);
        
        $data['total_receivable_fee'] = $fee_calculation['total_receivable_fee'];
        // Total Remaining Balance = Total Receivable Fee - Monthly Fees Collection
        $data['total_remaining_balance'] = max(0, $fee_calculation['total_receivable_fee'] - $month_collection);

        $total_enquiry = $enquiry['total'];

        if ($total_enquiry > 0) {

            $data['enquiry_overview'] = array(
                'won'              => $enquiry['complete'],
                'won_progress'     => ($enquiry['complete'] * 100) / $total_enquiry,
                'active'           => $enquiry['active'],
                'active_progress'  => ($enquiry['active'] * 100) / $total_enquiry,
                'passive'          => $enquiry['passive'],
                'passive_progress' => ($enquiry['passive'] * 100) / $total_enquiry,
                'dead'             => $enquiry['dead'],
                'dead_progress'    => ($enquiry['dead'] * 100) / $total_enquiry,
                'lost'             => $enquiry['lost'],
                'lost_progress'    => ($enquiry['lost'] * 100) / $total_enquiry,
            );

        } else {

            $data['enquiry_overview'] = array(
                'won'              => 0,
                'won_progress'     => 0,
                'active'           => 0,
                'active_progress'  => 0,
                'passive'          => 0,
                'passive_progress' => 0,
                'dead'             => 0,
                'dead_progress'    => 0,
                'lost'             => 0,
                'lost_progress'    => 0,
            );

        }

        $data['total_paid'] = $total_paid;
        $data['total_fees'] = $total_fess;
        if ($total_fess > 0) {
            $data['fessprogressbar'] = ($total_paid * 100) / $total_fess;
        } else {
            $data['fessprogressbar'] = 0;
        }

        $data['total_enquiry']  = $total_enquiry  = $enquiry['total'];
        $data['total_complete'] = $complete_enquiry = $enquiry['complete'];
        if ($total_enquiry > 0) {
            $data['fenquiryprogressbar'] = ($complete_enquiry * 100) / $total_enquiry;
        } else {
            $data['fenquiryprogressbar'] = 0;
        }

        $bookoverview      = $this->book_model->bookoverview($start_date, $end_date);
        $bookduereport     = $this->bookissue_model->dueforreturn($start_date, $end_date);
        $forreturndata     = $this->bookissue_model->forreturn($start_date, $end_date);
        $dueforreturn      = $bookduereport[0]['total'];
        $forreturn         = $forreturndata[0]['total'];
        $total_qty         = $bookoverview[0]['qty'];
        $total_issued      = $bookoverview[0]['total_issue'];
        $availble          = '0';
        $availble_progress = 0;
        $issued_progress   = 0;
        if ($total_qty > 0) {
            $availble          = $total_qty - $total_issued;
            $availble_progress = ($availble * 100) / $total_qty;
            $issued_progress   = ($total_issued * 100) / $total_qty;
        }
        $data['book_overview'] = array(
            'total'             => $total_qty,
            'total_progress'    => 100,
            'availble'          => $availble,
            'availble_progress' => round($availble_progress, 2),
            'total_issued'      => $total_issued,
            'issued_progress'   => round($issued_progress, 2),
            'dueforreturn'      => $dueforreturn,
            'forreturn'         => $forreturn,
        );

        $Attendence                   = $this->stuattendence_model->getTodayDayAttendance($total_students);
        $data['attendence_data']      = $Attendence;
        $Staffattendence              = $this->Staff_model->getTodayDayAttendance();
        $data['Staffattendence_data'] = $Staffattendence;
        $getTotalStaff                = $this->Staff_model->getTotalStaff();
        $data['getTotalStaff_data']   = $getTotalStaff;
        if ($getTotalStaff > 0) {$percentTotalStaff_data = ($Staffattendence * 100) / ($getTotalStaff);} else { $percentTotalStaff_data = '0';}
        $data['percentTotalStaff_data'] = $percentTotalStaff_data;
        $data['attendance_widget'] = $this->getDashboardAttendanceWidgetData();
        $data['quick_exam_widget'] = $this->quickexam_model->getDashboardWidgetData($this->setting_model->getCurrentSession());
        $data['sch_setting']            = $this->sch_setting_detail;

        // Get pending annual charges for current month
        try {
            $data['pending_annual_charges'] = $this->getPendingAnnualCharges();
        } catch (Exception $e) {
            log_message('error', 'Error getting pending annual charges in dashboard: ' . $e->getMessage());
            $data['pending_annual_charges'] = array(
                'total_students' => 0,
                'total_amount' => 0,
                'total_charges' => 0
            );
        }
        
        // Ensure the variable is always set
        if (!isset($data['pending_annual_charges']) || !is_array($data['pending_annual_charges'])) {
            $data['pending_annual_charges'] = array(
                'total_students' => 0,
                'total_amount' => 0,
                'total_charges' => 0
            );
        }

        if ($data['sch_setting']->attendence_type == 0) {
            $data['std_graphclass'] = "col-lg-3 col-md-6 col-sm-6";
        } else {
            $data['std_graphclass'] = "col-lg-4 col-md-6 col-sm-6";
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/dashboard', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getUserImage()
    {
        $id     = $this->session->userdata["admin"]["id"];
        $result = $this->staff_model->get($id);
    }

    public function getSession()
    {
        if (!$this->rbac->hasPrivilege('quick_session_change', 'can_view')) {
            access_denied();
        }
        $session             = $this->session_model->getAllSession();
        $data                = array();
        $session_array       = $this->session->has_userdata('session_array');
        $data['sessionData'] = array('session_id' => 0);
        if ($session_array) {
            $data['sessionData'] = $this->session->userdata('session_array');
        } else {
            $setting             = $this->setting_model->get();
            $data['sessionData'] = array('session_id' => $setting[0]['session_id']);
        }
        $data['sessionList'] = $session;
        $this->load->view('admin/partial/_session', $data);
    }

    public function updateSession()
    {
        $session       = $this->input->post('popup_session');
        $session_array = $this->session->has_userdata('session_array');
        if ($session_array) {
            $this->session->unset_userdata('session_array');
        } 
        $session       = $this->session_model->get($session);
        $session_array = array('session_id' => $session['id'], 'session' => $session['session']);
        $this->session->set_userdata('session_array', $session_array);
        echo json_encode(array('status' => 1, 'message' => $this->lang->line('session_changed_successfully')));
    }

    public function updatePurchaseCode()
    {
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|trim|xss_clean');
        $this->form_validation->set_rules('envato_market_purchase_code', 'Purchase Code', 'required|trim|xss_clean');
        if ($this->form_validation->run() == false) {
            $data = array(
                'email'                       => form_error('email'),
                'envato_market_purchase_code' => form_error('envato_market_purchase_code'),
            );
            $array = array('status' => '2', 'error' => $data);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($array));
        } else {
            //==================
            $response = $this->auth->app_update();
        }
    }

    public function backup()
    {
        if (!$this->rbac->hasPrivilege('backup', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'admin/backup');
        $data['title'] = 'Backup History';
        if ($this->input->server('REQUEST_METHOD') == "POST") {
            if ($this->input->post('backup') == "upload") {
                $this->form_validation->set_rules('file', 'Image', 'callback_handle_upload');
                if ($this->form_validation->run() == false) {

                } else {
                    if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
                        $fileInfo  = pathinfo($_FILES["file"]["name"]);
                        $file_name = "db-" . date("Y-m-d_H-i-s") . ".sql";
                        move_uploaded_file($_FILES["file"]["tmp_name"], "./backup/temp_uploaded/" . $file_name);
                        $folder_name  = 'temp_uploaded';
                        $path         = './backup/';
                        $file_restore = $this->load->file($path . $folder_name . '/' . $file_name, true);
                        $file_array   = explode(';', $file_restore);
                        foreach ($file_array as $query) {
                            $trimQuery1 = trim($query);
                            if (!empty($trimQuery1)) {
                                $this->db->query("SET FOREIGN_KEY_CHECKS = 0");
                                $this->db->query($query);
                                $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
                            }
                        }
                        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Backup restored successfully!</div>');
                        redirect('admin/admin/backup');
                    }
                }
            }
            if ($this->input->post('backup') == "backup") {
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Backup created successfully!</div>');
                $this->load->helper('download');
                $this->load->dbutil();
                $version  = $this->customlib->getAppVersion();
                $filename = "db_ver_" . $version . '_' . date("Y-m-d_H-i-s") . ".sql";
                $prefs    = array(
                    'ignore'     => array(),
                    'format'     => 'txt',
                    'filename'   => 'mybackup.sql',
                    'add_drop'   => true,
                    'add_insert' => true,
                    'newline'    => "\n",
                );
                $backup = $this->dbutil->backup($prefs);
                $this->load->helper('file');
                write_file('./backup/database_backup/' . $filename, $backup);
                redirect('admin/admin/backup');
                force_download($filename, $backup);
                $this->session->set_flashdata('feedback', 'Success message for client to see');
                redirect('admin/admin/backup');
            } else if ($this->input->post('backup') == "restore") {
                $folder_name  = 'database_backup';
                $file_name    = $this->input->post('filename');
                $path         = './backup/';
                $filePath     = $path . $folder_name . '/' . $file_name;
                $file_restore = $this->load->file($path . $folder_name . '/' . $file_name, true);
                $db           = (array) get_instance()->db;
                $conn         = mysqli_connect('localhost', $db['username'], $db['password'], $db['database']);

                $sql       = '';
                $error     = false;
                $error_msg = "";
                $result    = mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");

                if (!$result) {
                    $error_msg = "Database failed: " . mysqli_error();
                    $error     = true;
                }
                if (!$error) {
                    if (file_exists($filePath)) {
                        $lines = file($filePath);

                        foreach ($lines as $line) {

                            // Ignoring comments from the SQL script
                            if (substr($line, 0, 2) == '--' || $line == '') {
                                continue;
                            }

                            $sql .= $line;

                            if (substr(trim($line), -1, 1) == ';') {
                                $result = mysqli_query($conn, $sql);
                                if (!$result) {
                                    $error_msg = "Database failed: " . mysqli_error();
                                    $error     = true;
                                    break;
                                }
                                $sql = '';
                            }
                        }
                        if (!$error) {

                            $msg = "Backup restored successfully!";
                        }

                    } // end if file exists

                }

                $result = mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");
                if (!$result) {
                    $error_msg = "Database failed: " . mysqli_error();
                    $error     = true;
                }
                if ($error) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">' . $msg . '</div>');
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $msg . '</div>');
                }

                redirect('admin/admin/backup');
            }
        }
        $dir    = "./backup/database_backup/";
        $result = array();
        $cdir   = scandir($dir);
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    $result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
                } else {
                    $result[] = $value;
                }
            }
        }
        $data['dbfileList']  = $result;
        $setting_result      = $this->setting_model->get();
        $data['settinglist'] = $setting_result;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/backup', $data);
        $this->load->view('layout/footer', $data);
    }

    public function changepass()
    {
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'changepass/index');
        $data['title'] = 'Change Password';
        $this->form_validation->set_rules('current_pass', 'Current password', 'trim|required|xss_clean');
        $this->form_validation->set_rules('new_pass', 'New password', 'trim|required|xss_clean|matches[confirm_pass]');
        $this->form_validation->set_rules('confirm_pass', 'Confirm password', 'trim|required|xss_clean');
        if ($this->form_validation->run() == false) {
            $sessionData            = $this->session->userdata('loggedIn');
            $this->data['id']       = $sessionData['id'];
            $this->data['username'] = $sessionData['username'];
            $this->load->view('layout/header', $data);
            $this->load->view('admin/change_password', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $sessionData = $this->session->userdata('admin');
            $userdata    = $this->customlib->getUserData();
            $data_array  = array(
                'current_pass' => $this->input->post('current_pass'),
                'new_pass'     => md5($this->input->post('new_pass')),
                'user_id'      => $sessionData['id'],
                'user_email'   => $sessionData['email'],
                'user_name'    => $sessionData['username'],
            );
            $newdata = array(
                'id'       => $sessionData['id'],
                'password' => $this->enc_lib->passHashEnc($this->input->post('new_pass')),
            );
            $check = $this->enc_lib->passHashDyc($this->input->post('current_pass'), $userdata["password"]);
            $query1 = $this->admin_model->checkOldPass($data_array);

            if ($query1) {

                if ($check) {
                    $query2 = $this->admin_model->saveNewPass($newdata);
                    if ($query2) {
                        $data['error_message'] = "<div class='alert alert-success'>Password changed successfully</div>";
                        $this->load->view('layout/header', $data);
                        $this->load->view('admin/change_password', $data);
                        $this->load->view('layout/footer', $data);
                    }
                } else {
                    $data['error_message'] = "<div class='alert alert-danger'>Invalid current password</div>";
                    $this->load->view('layout/header', $data);
                    $this->load->view('admin/change_password', $data);
                    $this->load->view('layout/footer', $data);
                }
            } else {

                $data['error_message'] = "<div class='alert alert-danger'>Invalid current password</div>";
                $this->load->view('layout/header', $data);
                $this->load->view('admin/change_password', $data);
                $this->load->view('layout/footer', $data);
            }
        }
    }

    public function pdf_report()
    {
        $data        = array();
        $html        = $this->load->view('reports/students_detail', $data, true);
        $pdfFilePath = "output_pdf_name.pdf";
        $this->load->library('m_pdf');
        $this->m_pdf->pdf->WriteHTML($html);
        $this->m_pdf->pdf->Output($pdfFilePath, "D");
    }

    public function downloadbackup($file)
    {
        $this->load->helper('download');
        $filepath = "./backup/database_backup/" . $file;
        $data     = file_get_contents($filepath);
        $name     = $file;
        force_download($name, $data);
    }

    public function dropbackup($file)
    {
        if (!$this->rbac->hasPrivilege('backup', 'can_delete')) {
            access_denied();
        }
        unlink('./backup/database_backup/' . $file);
        redirect('admin/admin/backup');
    }

    public function search()
    {

        $data['title']           = 'Search';
        $search_text             = $this->input->post('search_text1');
        $data['sch_setting']     = $this->sch_setting_detail;
        $data['search_text']     = trim($this->input->post('search_text1'));
        $userdata                = $this->customlib->getUserData();
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $carray                  = array();
        $class                   = $this->class_model->get();
        $data['classlist']       = $class;
        $data['fields']          = $this->customfield_model->get_custom_fields('students', 1);
        $userdata                = $this->customlib->getUserData();
        $carray                  = array();

        if (!empty($data["classlist"])) {
            foreach ($data["classlist"] as $ckey => $cvalue) {

                $carray[] = $cvalue["id"];
            }
        }

        $resultlist = $this->student_model->searchusersbyFullText($search_text, $carray);

        $data['resultlist'] = $resultlist;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/search', $data);
        $this->load->view('layout/footer', $data);
    }

    public function getCollectionbymonth()
    {
        $result = $this->admin_model->getMonthlyCollection();
        return $result;
    }

    public function getCollectionbyday($date)
    {
        $result = $this->admin_model->getCollectionbyDay($date);
        if ($result[0]['amount'] == "") {
            $return = 0;
        } else {
            $return = $result[0]['amount'];
        }
        return $return;
    }

    public function getExpensebyday($date)
    {
        $result = $this->admin_model->getExpensebyDay($date);
        if ($result[0]['amount'] == "") {
            $return = 0;
        } else {
            $return = $result[0]['amount'];
        }
        return $return;
    }

    public function getExpensebymonth()
    {
        $result = $this->admin_model->getMonthlyExpense();
        return $result;
    }

    public function whatever($feecollection_array, $start_month_date, $end_month_date)
    {
        $return_amount = 0;
        $st_date       = strtotime($start_month_date);
        $ed_date       = strtotime($end_month_date);
        if (!empty($feecollection_array)) {
            while ($st_date <= $ed_date) {
                $date = date('Y-m-d', $st_date);
                foreach ($feecollection_array as $key => $value) {

                    if ($value['date'] == $date) {

                        $return_amount = $return_amount + $value['amount'] + $value['amount_fine'];
                    }
                }
                $st_date = $st_date + 86400;
            }
        } else {

        }

        return $return_amount;
    }

    private function getDashboardMonthlyCollection($start_date, $end_date)
    {
        $month_collection = 0;
        $report_collection = $this->studentfeemaster_model->getFeeCollectionReport($start_date, $end_date);

        if (!empty($report_collection)) {
            foreach ($report_collection as $collection_row) {
                $amount = isset($collection_row['amount']) ? (float) $collection_row['amount'] : 0;
                $fine   = isset($collection_row['amount_fine']) ? (float) $collection_row['amount_fine'] : 0;

                $month_collection += ($amount + $fine);
            }
        }

        return $month_collection;
    }

    private function getDashboardAttendanceWidgetDefaultData()
    {
        return array(
            'date_label' => date('j F'),
            'total_students' => 0,
            'present_students' => 0,
            'absent_students' => 0,
            'present_percentage' => 0,
            'rows' => array(),
        );
    }

    private function getDashboardAttendanceWidgetData()
    {
        $current_session = (int) $this->setting_model->getCurrentSession();
        if ($current_session <= 0) {
            return $this->getDashboardAttendanceWidgetDefaultData();
        }

        $today = date('Y-m-d');
        $query = $this->db->query(
            "SELECT
                classes.id AS class_id,
                classes.class,
                COUNT(DISTINCT students.id) AS total_students,
                COUNT(DISTINCT CASE WHEN student_attendences.attendence_type_id IN (1, 3, 6) THEN students.id END) AS present_students
             FROM classes
             LEFT JOIN student_session
                ON student_session.class_id = classes.id
               AND student_session.session_id = ?
             LEFT JOIN students
                ON students.id = student_session.student_id
               AND students.is_active = 'yes'
             LEFT JOIN student_attendences
                ON student_attendences.student_session_id = student_session.id
               AND DATE(student_attendences.date) = ?
               AND student_attendences.attendance_status = 'published'
             GROUP BY classes.id, classes.class
             HAVING classes.class IS NOT NULL
                AND TRIM(classes.class) <> ''
             ORDER BY classes.id ASC",
            array($current_session, $today)
        );

        $rows = $query->result_array();
        if (empty($rows)) {
            return $this->getDashboardAttendanceWidgetDefaultData();
        }

        $summary_rows = array();
        $total_students = 0;
        $present_students = 0;

        foreach ($rows as $row) {
            $row_total_students = isset($row['total_students']) ? (int) $row['total_students'] : 0;
            $row_present_students = isset($row['present_students']) ? (int) $row['present_students'] : 0;
            $row_absent_students = max(0, $row_total_students - $row_present_students);
            $row_percentage = ($row_total_students > 0) ? round(($row_present_students * 100) / $row_total_students) : 0;
            $class_label = trim($row['class']);
            $class_id = isset($row['class_id']) ? (int) $row['class_id'] : 0;
            $class_sections = ($class_id > 0) ? $this->section_model->getClassBySection($class_id) : array();
            $section_count = is_array($class_sections) ? count($class_sections) : 0;
            $default_section_id = ($section_count === 1 && isset($class_sections[0]['section_id']))
                ? (int) $class_sections[0]['section_id']
                : 0;

            $summary_rows[] = array(
                'class_id' => $class_id,
                'section_id' => $default_section_id,
                'section_count' => $section_count,
                'class_label' => $class_label,
                'total_students' => $row_total_students,
                'present_students' => $row_present_students,
                'absent_students' => $row_absent_students,
                'present_percentage' => $row_percentage,
            );

            $total_students += $row_total_students;
            $present_students += $row_present_students;
        }

        $absent_students = max(0, $total_students - $present_students);
        $present_percentage = ($total_students > 0) ? round(($present_students * 100) / $total_students) : 0;

        return array(
            'date_label' => date('j F', strtotime($today)),
            'total_students' => $total_students,
            'present_students' => $present_students,
            'absent_students' => $absent_students,
            'present_percentage' => $present_percentage,
            'rows' => $summary_rows,
        );
    }

    public function startmonthandend()
    {
        $startmonth = $this->setting_model->getStartMonth();
        if ($startmonth == 1) {
            $endmonth = 12;
        } else {
            $endmonth = $startmonth - 1;
        }
        return array($startmonth, $endmonth);
    }

    /**
     * Calculate dashboard current-month receivable.
     * Previous months and Balance Master are excluded from total_receivable_fee.
     */
    public function calculateCompleteFeeBalance($start_date, $end_date)
    {
        try {
            $grand_total_monthfee = 0;         // Current month fees (monthfee) - matching report
            $grand_total_arrears = 0;          // All arrears (previous) - matching report
            
            // Get current session
            $current_session = $this->setting_model->getCurrentSession();
            
            if (empty($current_session)) {
                log_message('error', 'Current session not available in calculateCompleteFeeBalance');
                return array(
                    'total_receivable_fee' => 0,
                    'total_remaining_balance' => 0
                );
            }
            
            // Calculate session start date using EXACT same logic as fee_summary_report_pdf
            $startMonth = $this->setting_model->getStartMonth();
            $input = $this->setting_model->getCurrentSessionName();
            list($a, $b) = explode('-', $input);
            $Current_year = $a;
            
            if (strlen($b) == 2) {
                $Next_year = substr($a, 0, 2) . $b;
            } else {
                $Next_year = $b;
            }
            
            $year = date('Y', strtotime($start_date));
            $month = date('m', strtotime($start_date));
            
            // Calculate current_section1 using EXACT same logic as report
            if ($year == date('Y') && $year == $Current_year) {
                $current_section_date = $year . '-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01';
                $current_section1 = date('Y-m-01', strtotime($current_section_date));
            } else if ($year == date('Y') && $year > $Current_year) {
                $year_temp = $year - 1;
                $current_section_date = $year_temp . '-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01';
                $current_section1 = date('Y-m-01', strtotime($current_section_date));
            } elseif ($year < date('Y')) {
                $current_section_date = $year . '-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01';
                $current_section1 = date('Y-m-01', strtotime($current_section_date));
            } else {
                $current_section_date = $year . '-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01';
                $current_section1 = date('Y-m-01', strtotime($current_section_date));
            }
            
            log_message('debug', 'Dashboard - start_date: ' . $start_date . ', end_date: ' . $end_date . ', current_section1: ' . $current_section1);
            
            // Get all active students
            $this->load->model('student_model');
            $studentlist = $this->student_model->searchByClassSection(null, null);
            
            if (empty($studentlist)) {
                return array(
                    'total_receivable_fee' => 0,
                    'total_remaining_balance' => 0
                );
            }
            
            foreach ($studentlist as $key => $eachstudent) {
                $student_session_id = $eachstudent['student_session_id'];
                
                // Get ALL student fees (this includes current month, previous months, and Balance Master)
                $student_total_fees = $this->studentfeemaster_model->getStudentFeesMonthly($student_session_id, $start_date, $end_date);
                
                if (!empty($student_total_fees)) {
                    // Variables for current month
                    $current_month_fee = 0;
                    $current_month_paid = 0;
                    $current_month_discount = 0;
                    
                    // Variables for arrears (previous session + previous months)
                    $balance_master = 0;              // Previous session arrears
                    $previous_months_fee = 0;         // Fees from previous months in current session
                    $previous_months_paid = 0;        // Payments on previous months
                    $previous_months_discount = 0;    // Discounts on previous months
                    
                    foreach ($student_total_fees as $student_total_fees_key => $student_total_fees_value) {
                        if (!empty($student_total_fees_value->fees)) {
                            foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {
                                
                                // Use EXACT same logic as fee_summary_report_pdf
                                
                                // 1. BALANCE MASTER (arrears from previous session)
                                if ($each_fee_value->name == 'Balance Master') {
                                    $balance_master += $each_fee_value->amount;
                                }
                                
                                // 2. CURRENT MONTH FEES (due in current month, excluding Balance Master)
                                if ($each_fee_value->due_date >= $start_date && $each_fee_value->due_date <= $end_date) {
                                    if ($each_fee_value->name != 'Balance Master') {
                                        $current_month_fee += $each_fee_value->amount;
                                    }
                                }
                                
                                // 3. PREVIOUS MONTHS FEES (between session start and current month, excluding Balance Master)
                                if ($each_fee_value->due_date >= $current_section1 && $each_fee_value->due_date < $start_date) {
                                    if ($each_fee_value->name != 'Balance Master') {
                                        $previous_months_fee += $each_fee_value->amount;
                                    }
                                }
                                
                                // Process payments - EXACT same logic as report
                                $amount_detail = json_decode($each_fee_value->amount_detail);
                                if (is_object($amount_detail)) {
                                    foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                        
                                        // Current month payments
                                        if ($amount_detail_value->date >= $start_date && $amount_detail_value->date <= $end_date) {
                                            $current_month_paid += $amount_detail_value->amount;
                                            $current_month_discount += $amount_detail_value->amount_discount;
                                        }
                                        
                                        // Previous month payments
                                        if ($amount_detail_value->date >= $current_section1 && $amount_detail_value->date < $start_date) {
                                            $previous_months_paid += $amount_detail_value->amount;
                                            $previous_months_discount += $amount_detail_value->amount_discount;
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    // Calculate arrears using EXACT same formula as report (line 4131)
                    $arrears = ($balance_master + $previous_months_fee) - ($previous_months_paid + $previous_months_discount);
                    if ($arrears < 0) $arrears = 0;
                    
                    // DEBUG: Log per-student calculation - matching report format
                    log_message('debug', '=== DASHBOARD - Student Session ID: ' . $student_session_id . ' ===');
                    log_message('debug', 'Month Fee: ' . $current_month_fee);
                    log_message('debug', 'Previous (Balance Master): ' . $balance_master);
                    log_message('debug', 'Previous Month Fee: ' . $previous_months_fee);
                    log_message('debug', 'Deposit: ' . $current_month_paid);
                    log_message('debug', 'Discount: ' . $current_month_discount);
                    log_message('debug', 'Previous Deposit: ' . $previous_months_paid);
                    log_message('debug', 'Previous Discount: ' . $previous_months_discount);
                    log_message('debug', 'Arrears (previous): ' . $arrears);
                    log_message('debug', 'Total Receivable (current month only): ' . $current_month_fee);
                    
                    // Add to grand totals - matching report calculation
                    $grand_total_monthfee += $current_month_fee;  // monthfee from report
                    $grand_total_arrears += $arrears;             // previous from report
                }
            }
            
            // TOTAL RECEIVABLE FEE = current month fee only.
            // Previous month pending and Balance Master arrears are handled by the Previous Balance widget.
            $total_receivable_fee = $grand_total_monthfee;
            
            // TOTAL REMAINING BALANCE = current-month receivable before dashboard collection adjustment.
            $total_remaining_balance = $total_receivable_fee;
            
            // DEBUG: Log dashboard calculation
            log_message('debug', '=== DASHBOARD CALCULATION DEBUG ===');
            log_message('debug', 'Total Current Month Fee (monthfee): ' . $grand_total_monthfee);
            log_message('debug', 'Total Arrears (previous): ' . $grand_total_arrears);
            log_message('debug', 'Total Receivable Fee (current month only): ' . $total_receivable_fee);
            log_message('debug', 'Total Remaining Balance: ' . $total_remaining_balance);
            log_message('debug', 'Number of Students Processed: ' . count($studentlist));
            
            return array(
                'total_receivable_fee' => $total_receivable_fee,
                'total_remaining_balance' => $total_remaining_balance
            );
            
        } catch (Exception $e) {
            log_message('error', 'Error calculating complete fee balance in dashboard: ' . $e->getMessage());
            return array(
                'total_receivable_fee' => 0,
                'total_remaining_balance' => 0
            );
        }
    }

    public function getPendingAnnualCharges()
    {
        try {
            // Get current session from setting model
            $current_session = $this->setting_model->getCurrentSession();
            
            if (empty($current_session)) {
                log_message('error', 'Current session not available in getPendingAnnualCharges');
                return array(
                    'total_students' => 0,
                    'total_amount' => 0,
                    'total_charges' => 0
                );
            }
            
            $current_month = date('m');
            $current_year = date('Y');
            $startDate = $current_year . '-' . $current_month . '-01';
            $endDate = $current_year . '-' . $current_month . '-31';

            // Use the same logic as the existing pending fee report
            $this->load->model("studentfeemaster_model");
            
            // Get the Annual fee type ID directly from database
            $annual_fee_type_query = $this->db->query("SELECT id FROM feetype WHERE code = 'Annual' LIMIT 1");
            $annual_fee_type_result = $annual_fee_type_query->row();
            $annual_fee_type_id = !empty($annual_fee_type_result) ? $annual_fee_type_result->id : null;
            
            if (empty($annual_fee_type_id)) {
                log_message('error', 'Annual fee type not found');
                return array(
                    'total_students' => 0,
                    'total_amount' => 0,
                    'total_charges' => 0
                );
            }
            
            // Get all students
            $studentlist = $this->student_model->searchByClassSection(null, null);
            
            $total_amount = 0;
            $total_students = 0;
            $total_charges = 0;
            
            if (!empty($studentlist)) {
                foreach ($studentlist as $key => $eachstudent) {
                    $student_session_id = $eachstudent['student_session_id'];
                    
                    // Get student fees for annual charges only (using fee type ID)
                    $student_total_fees = $this->studentfeemaster_model->getStudentFeesMonthly1($student_session_id, $startDate, $endDate, $annual_fee_type_id);
                    $student_previous_fees = $this->studentfeemaster_model->getStudentPreviousFees($student_session_id, $startDate, $endDate, $annual_fee_type_id);
                    
                    if (!empty($student_total_fees) || !empty($student_previous_fees)) {
                        $monthfee = 0;
                        $deposit = 0;
                        $discount = 0;
                        $arrears = 0;
                        $previous_deposit = 0;
                        $previous_discount = 0;
                        $previous_monthfee = 0;
                        
                        // Calculate current month fees
                        if (!empty($student_total_fees)) {
                            foreach ($student_total_fees as $student_total_fees_key => $student_total_fees_value) {
                                if (!empty($student_total_fees_value->fees)) {
                                    foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {
                                        if ($each_fee_value->due_date >= $startDate && $each_fee_value->due_date <= $endDate) {
                                            $monthfee += $each_fee_value->amount;
                                        }
                                        
                                        $amount_detail = json_decode($each_fee_value->amount_detail);
                                        if (is_object($amount_detail)) {
                                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                                $deposit += $amount_detail_value->amount;
                                                $discount += $amount_detail_value->amount_discount;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Calculate previous fees (arrears)
                        if (!empty($student_previous_fees)) {
                            foreach ($student_previous_fees as $student_total_fees_key => $student_total_fees_value) {
                                if (!empty($student_total_fees_value->fees)) {
                                    foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {
                                        if ($each_fee_value->name == 'Balance Master') {
                                            $arrears += $each_fee_value->amount;
                                            
                                            $amount_detail_1 = json_decode($each_fee_value->amount_detail);
                                            if (is_object($amount_detail_1)) {
                                                foreach ($amount_detail_1 as $amount_detail_key => $amount_detail_value) {
                                                    $previous_deposit += $amount_detail_value->amount;
                                                    $previous_discount += $amount_detail_value->amount_discount;
                                                }
                                            }
                                        }
                                        
                                        if ($each_fee_value->due_date < $startDate) {
                                            if ($each_fee_value->name != 'Balance Master') {
                                                $previous_monthfee += $each_fee_value->amount;
                                                
                                                $amount_detail = json_decode($each_fee_value->amount_detail);
                                                if (is_object($amount_detail)) {
                                                    foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                                        $previous_deposit += $amount_detail_value->amount;
                                                        $previous_discount += $amount_detail_value->amount_discount;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                        
                        // Calculate balance using same logic as existing report
                        $balance = $monthfee + $arrears - ($deposit + $discount);
                        
                        if ($balance > 0) {
                            $total_amount += $balance;
                            $total_students++;
                            $total_charges++;
                        }
                    }
                }
            }

            return array(
                'total_students' => $total_students,
                'total_amount' => $total_amount,
                'total_charges' => $total_charges
            );
        } catch (Exception $e) {
            // Log error and return default values
            log_message('error', 'Error in getPendingAnnualCharges: ' . $e->getMessage());
            return array(
                'total_students' => 0,
                'total_amount' => 0,
                'total_charges' => 0
            );
        }
    }

    private function findDashboardBalanceMasterAmount($records, $student_session_id)
    {
        if (empty($records)) {
            return null;
        }

        foreach ($records as $record) {
            if ((int) $record->student_session_id === (int) $student_session_id) {
                $balance_amount = isset($record->amount) ? (float) $record->amount : 0;
                $fees = $this->studentfeemaster_model->getDueFeeByFeeSessionGroup($record->fee_session_group_id, $record->id);

                if (empty($fees)) {
                    return max(0, $balance_amount);
                }

                $total_fee = 0;
                $deposit = 0;
                $discount = 0;
                foreach ($fees as $fee_index => $fee_item) {
                    $fee_amount = isset($fee_item->amount) ? (float) $fee_item->amount : 0;
                    if (isset($record->is_system) && (int) $record->is_system !== 0 && $fee_index === 0) {
                        $fee_amount = $balance_amount;
                    }

                    $total_fee += $fee_amount;
                    $amount_summary = $this->sumDashboardFeeAmountDetail(isset($fee_item->amount_detail) ? $fee_item->amount_detail : '');
                    $deposit += $amount_summary['deposit'];
                    $discount += $amount_summary['discount'];
                }

                return max(0, $total_fee - ($deposit + $discount));
            }
        }

        return null;
    }

    private function sumDashboardFeeAmountDetail($amount_detail)
    {
        $summary = array(
            'deposit'  => 0,
            'discount' => 0,
        );

        $decoded_amount_detail = json_decode((string) $amount_detail);
        if (!is_array($decoded_amount_detail) && !is_object($decoded_amount_detail)) {
            return $summary;
        }

        foreach ($decoded_amount_detail as $amount_detail_value) {
            $summary['deposit'] += isset($amount_detail_value->amount) ? (float) $amount_detail_value->amount : 0;
            $summary['discount'] += isset($amount_detail_value->amount_discount) ? (float) $amount_detail_value->amount_discount : 0;
        }

        return $summary;
    }

    private function calculatePreviousSessionBalanceForDashboard($previous_student_session_id, $include_debug = false)
    {
        $student_total_fees = array();
        if (!empty($previous_student_session_id)) {
            $student_total_fees = $this->studentfeemaster_model->getPreviousStudentFees($previous_student_session_id);
        }

        $debug_data = array(
            'previous_student_session_id' => $previous_student_session_id,
            'master_rows_count'           => is_array($student_total_fees) ? count($student_total_fees) : 0,
            'total_fee'                   => 0,
            'deposit'                     => 0,
            'discount'                    => 0,
            'fee_items'                   => array(),
        );

        $total_fee = 0;
        $deposit = 0;
        $discount = 0;

        if (!empty($student_total_fees)) {
            foreach ($student_total_fees as $student_total_fees_value) {
                if (empty($student_total_fees_value->fees)) {
                    continue;
                }

                foreach ($student_total_fees_value->fees as $each_fee_value) {
                    $fee_amount = isset($each_fee_value->amount) ? (float) $each_fee_value->amount : 0;
                    $total_fee += $fee_amount;

                    $fee_item_debug = array(
                        'name'     => isset($each_fee_value->name) ? (string) $each_fee_value->name : '',
                        'amount'   => $fee_amount,
                        'due_date' => isset($each_fee_value->due_date) ? (string) $each_fee_value->due_date : '',
                        'payments' => array(),
                    );

                    $amount_detail = json_decode((string) (isset($each_fee_value->amount_detail) ? $each_fee_value->amount_detail : ''));
                    if (is_array($amount_detail) || is_object($amount_detail)) {
                        foreach ($amount_detail as $amount_detail_value) {
                            $detail_amount = isset($amount_detail_value->amount) ? (float) $amount_detail_value->amount : 0;
                            $detail_discount = isset($amount_detail_value->amount_discount) ? (float) $amount_detail_value->amount_discount : 0;

                            $deposit += $detail_amount;
                            $discount += $detail_discount;

                            if ($include_debug) {
                                $fee_item_debug['payments'][] = array(
                                    'date'            => isset($amount_detail_value->date) ? (string) $amount_detail_value->date : '',
                                    'amount'          => $detail_amount,
                                    'amount_discount' => $detail_discount,
                                );
                            }
                        }
                    }

                    if ($include_debug) {
                        $debug_data['fee_items'][] = $fee_item_debug;
                    }
                }
            }
        }

        $balance = $total_fee - ($deposit + $discount);
        if ($balance < 0) {
            $balance = 0;
        }

        $result = array(
            'previous_balance' => $balance,
            'pending_months'   => ($balance > 0) ? array('Previous Session' => 'Previous Session') : array(),
        );

        if ($include_debug) {
            $debug_data['total_fee'] = $total_fee;
            $debug_data['deposit'] = $deposit;
            $debug_data['discount'] = $discount;
            $debug_data['previous_balance'] = $balance;
            $result['debug'] = $debug_data;
        }

        return $result;
    }

    private function findPreviousSessionByAdmissionNo($previous_session_id, $admission_no)
    {
        $admission_no = trim((string) $admission_no);
        if (empty($previous_session_id) || $admission_no === '') {
            return 0;
        }

        $row = $this->db
            ->select('student_session.id')
            ->from('student_session')
            ->join('students', 'students.id = student_session.student_id')
            ->where('student_session.session_id', $previous_session_id)
            ->where('students.admission_no', $admission_no)
            ->limit(1)
            ->get()
            ->row();

        if (!empty($row)) {
            return (int) $row->id;
        }

        $normalized_admission_no = $this->normalizeAdmissionNoForDashboard($admission_no);
        if ($normalized_admission_no === '') {
            return 0;
        }

        $normalized_expression = "REPLACE(REPLACE(REPLACE(UPPER(TRIM(students.admission_no)), '-', ''), ' ', ''), '/', '')";
        $row = $this->db
            ->select('student_session.id')
            ->from('student_session')
            ->join('students', 'students.id = student_session.student_id')
            ->where('student_session.session_id', $previous_session_id)
            ->where($normalized_expression . " = " . $this->db->escape($normalized_admission_no), null, false)
            ->limit(1)
            ->get()
            ->row();

        return !empty($row) ? (int) $row->id : 0;
    }

    private function normalizeAdmissionNoForDashboard($admission_no)
    {
        $admission_no = strtoupper(trim((string) $admission_no));
        return str_replace(array('-', ' ', '/'), '', $admission_no);
    }

    private function normalizePersonTokenForDashboard($value)
    {
        $value = strtoupper(trim((string) $value));
        return preg_replace('/[^A-Z0-9]/', '', $value);
    }

    private function getDashboardSessionStartDate($reference_date)
    {
        $startMonth = $this->setting_model->getStartMonth();
        $input = $this->setting_model->getCurrentSessionName();
        list($current_year, $next_year_value) = explode('-', $input);

        if (strlen($next_year_value) == 2) {
            $next_year = substr($current_year, 0, 2) . $next_year_value;
        } else {
            $next_year = $next_year_value;
        }

        $year = date('Y', strtotime($reference_date));

        if ($year == date('Y') && $year == $current_year) {
            $session_start_date = $year . '-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01';
        } elseif ($year == date('Y') && $year > $current_year) {
            $session_start_date = ($year - 1) . '-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01';
        } elseif ($year < date('Y')) {
            $session_start_date = $year . '-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01';
        } else {
            $session_start_date = $year . '-' . str_pad($startMonth, 2, '0', STR_PAD_LEFT) . '-01';
        }

        return date('Y-m-01', strtotime($session_start_date));
    }

    private function findPreviousSessionByNameForDashboard($previous_session_id, $firstname, $middlename, $lastname, $father_name)
    {
        if (empty($previous_session_id)) {
            return 0;
        }

        $student_name = $this->normalizePersonTokenForDashboard(trim($firstname . ' ' . $middlename . ' ' . $lastname));
        $father_name = $this->normalizePersonTokenForDashboard($father_name);

        if ($student_name === '' || $father_name === '') {
            return 0;
        }

        $student_name_expr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(UPPER(CONCAT_WS('', students.firstname, students.middlename, students.lastname)), ' ', ''), '-', ''), '/', ''), '.', ''), ',', '')";
        $father_name_expr = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(UPPER(COALESCE(students.father_name, '')), ' ', ''), '-', ''), '/', ''), '.', ''), ',', '')";
        $row = $this->db->query(
            "SELECT student_session.id
             FROM student_session
             INNER JOIN students ON students.id = student_session.student_id
             WHERE student_session.session_id = ?
               AND {$student_name_expr} = ?
               AND {$father_name_expr} = ?
             LIMIT 1",
            array($previous_session_id, $student_name, $father_name)
        )->row();

        return !empty($row) ? (int) $row->id : 0;
    }

    private function calculateCurrentSessionPreviousPendingFeesForDashboard($student_session_id, $start_date, $end_date)
    {
        $student_previous_fees = $this->studentfeemaster_model->getStudentPreviousFees($student_session_id, $start_date, $end_date);
        $session_start_date = $this->getDashboardSessionStartDate($start_date);
        $pending_amount = 0;
        $pending_months = array();

        if (!empty($student_previous_fees)) {
            foreach ($student_previous_fees as $student_fee_group) {
                if (empty($student_fee_group->fees)) {
                    continue;
                }

                foreach ($student_fee_group->fees as $fee_item) {
                    $due_date = isset($fee_item->due_date) ? (string) $fee_item->due_date : '';
                    $fee_name = isset($fee_item->name) ? trim((string) $fee_item->name) : '';

                    if ($due_date === '' || $fee_name === 'Balance Master') {
                        continue;
                    }

                    if ($due_date < $session_start_date || $due_date >= $start_date) {
                        continue;
                    }

                    $fee_amount = isset($fee_item->amount) ? (float) $fee_item->amount : 0;
                    $amount_summary = $this->sumDashboardFeeAmountDetail(isset($fee_item->amount_detail) ? $fee_item->amount_detail : '');
                    $paid_amount = $amount_summary['deposit'];
                    $discount_amount = $amount_summary['discount'];

                    $fee_balance = $fee_amount - ($paid_amount + $discount_amount);
                    if ($fee_balance <= 0) {
                        continue;
                    }

                    $pending_amount += $fee_balance;
                    $pending_month_key = date('Y-m', strtotime($due_date)) . '-' . $fee_name;
                    $pending_months[$pending_month_key] = date('M Y', strtotime($due_date)) . ' - ' . $fee_name;
                }
            }
        }

        return array(
            'pending_amount' => $pending_amount,
            'pending_months' => array_values($pending_months),
        );
    }

    public function getPreviousBalanceStudentsData($start_date, $end_date)
    {
        try {
            $this->load->model('student_model');
            $studentlist = $this->student_model->searchByClassSection(null, null);
            $students = array();
            $total_amount = 0;
            $total_arrears_amount = 0;
            $total_current_balance_amount = 0;

            if (empty($studentlist)) {
                return array(
                    'total_students' => 0,
                    'total_amount'   => 0,
                    'total_previous_balance_amount' => 0,
                    'total_previous_pending_fee_amount' => 0,
                    'students'       => array(),
                );
            }

            $current_section1 = $this->getDashboardSessionStartDate($start_date);

            foreach ($studentlist as $eachstudent) {
                $student_session_id = isset($eachstudent['student_session_id']) ? (int) $eachstudent['student_session_id'] : 0;
                if ($student_session_id <= 0) {
                    continue;
                }

                $student_total_fees = $this->studentfeemaster_model->getStudentFeesMonthly($student_session_id, $start_date, $end_date);
                if (empty($student_total_fees)) {
                    continue;
                }

                $student_previous_balance = 0;
                $pending_months = array();

                foreach ($student_total_fees as $student_total_fees_value) {
                    if (empty($student_total_fees_value->fees)) {
                        continue;
                    }

                    foreach ($student_total_fees_value->fees as $each_fee_value) {
                        $fee_name = isset($each_fee_value->name) ? (string) $each_fee_value->name : '';
                        $fee_amount = isset($each_fee_value->amount) ? (float) $each_fee_value->amount : 0;
                        $due_date = isset($each_fee_value->due_date) ? (string) $each_fee_value->due_date : '';
                        $is_balance_master = ($fee_name == 'Balance Master');
                        $is_previous_month_fee = (
                            !$is_balance_master
                            && $due_date !== ''
                            && $due_date >= $current_section1
                            && $due_date < $start_date
                        );

                        if (!$is_balance_master && !$is_previous_month_fee) {
                            continue;
                        }

                        $amount_summary = $this->sumDashboardFeeAmountDetail(isset($each_fee_value->amount_detail) ? $each_fee_value->amount_detail : '');
                        $fee_balance = $fee_amount - ($amount_summary['deposit'] + $amount_summary['discount']);
                        if ($fee_balance <= 0) {
                            continue;
                        }

                        $student_previous_balance += $fee_balance;
                        if ($is_balance_master) {
                            $pending_months['balance_master'] = 'Balance Master';
                        } else {
                            $pending_months[date('Y-m', strtotime($due_date)) . '-' . $fee_name] = date('M Y', strtotime($due_date)) . ' - ' . $fee_name;
                        }
                    }
                }

                if ($student_previous_balance <= 0) {
                    continue;
                }

                $total_amount += $student_previous_balance;
                $total_arrears_amount += $student_previous_balance;

                $student_name = trim(
                    (isset($eachstudent['firstname']) ? $eachstudent['firstname'] : '') . ' ' .
                    (isset($eachstudent['middlename']) ? $eachstudent['middlename'] : '') . ' ' .
                    (isset($eachstudent['lastname']) ? $eachstudent['lastname'] : '')
                );
                $students[] = array(
                    'student_session_id' => $student_session_id,
                    'student_id'         => $eachstudent['id'],
                    'student_name'       => $student_name,
                    'admission_no'       => $eachstudent['admission_no'],
                    'class'              => $eachstudent['class'],
                    'section'            => $eachstudent['section'],
                    'previous_balance_amount' => $student_previous_balance,
                    'previous_pending_fee_amount' => 0,
                    'pending_amount'     => $student_previous_balance,
                    'pending_months'     => !empty($pending_months) ? implode(', ', array_values($pending_months)) : '-',
                );
            }

            usort($students, function ($a, $b) {
                $class_compare = strnatcasecmp($a['class'], $b['class']);
                if ($class_compare !== 0) {
                    return $class_compare;
                }

                $section_compare = strnatcasecmp($a['section'], $b['section']);
                if ($section_compare !== 0) {
                    return $section_compare;
                }

                return strnatcasecmp((string) $a['admission_no'], (string) $b['admission_no']);
            });

            return array(
                'total_students' => count($students),
                'total_amount'   => $total_amount,
                'total_previous_balance_amount' => $total_arrears_amount,
                'total_previous_pending_fee_amount' => $total_current_balance_amount,
                'students'       => $students,
            );
        } catch (Exception $e) {
            log_message('error', 'Error in getPreviousBalanceStudentsData: ' . $e->getMessage());
            return array(
                'total_students' => 0,
                'total_amount'   => 0,
                'total_previous_balance_amount' => 0,
                'total_previous_pending_fee_amount' => 0,
                'students'       => array(),
            );
        }
    }

    public function previousBalanceStudentsReport()
    {
        if (!$this->rbac->hasPrivilege('Monthly fees_collection_widget', 'can_view')) {
            access_denied();
        }

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $report_data = $this->getPreviousBalanceStudentsData($start_date, $end_date);

        $data['summary'] = $report_data;
        $data['previous_balance_students'] = $report_data['students'];
        $data['current_month'] = date('F Y');
        $data['currency_symbol'] = $this->customlib->getSchoolCurrencyFormat();
        $data['previous_balance_only_amount'] = isset($report_data['total_previous_balance_amount']) ? $report_data['total_previous_balance_amount'] : 0;
        $data['previous_pending_fee_amount'] = isset($report_data['total_previous_pending_fee_amount']) ? $report_data['total_previous_pending_fee_amount'] : 0;

        $this->load->view('admin/previous_balance_students_report', $data);
    }

    public function debugPreviousBalanceStudentsData()
    {
        if (!$this->rbac->hasPrivilege('Monthly fees_collection_widget', 'can_view')) {
            access_denied();
        }

        $this->load->model('student_model');

        $start_date = date('Y-m-01');
        $end_date = date('Y-m-t');
        $current_session = $this->setting_model->getCurrentSession();
        $previous_session = $this->session_model->getPreSession($current_session);
        $studentlist = $this->student_model->searchByClassSection(null, null);
        $studentlist = is_array($studentlist) ? $studentlist : array();
        $previous_session_student_count = 0;
        $exact_admission_overlap = 0;
        $normalized_admission_overlap = 0;
        $name_father_overlap = 0;

        if (!empty($previous_session)) {
            $previous_session_student_count = (int) $this->db
                ->where('session_id', $previous_session->id)
                ->count_all_results('student_session');

            $exact_overlap_row = $this->db->query(
                "SELECT COUNT(DISTINCT cur.id) AS total
                 FROM student_session cur
                 INNER JOIN students cur_students ON cur_students.id = cur.student_id
                 INNER JOIN student_session prev ON prev.session_id = ?
                 INNER JOIN students prev_students ON prev_students.id = prev.student_id
                    AND TRIM(prev_students.admission_no) = TRIM(cur_students.admission_no)
                 WHERE cur.session_id = ?",
                array($previous_session->id, $current_session)
            )->row();
            $exact_admission_overlap = !empty($exact_overlap_row) ? (int) $exact_overlap_row->total : 0;

            $normalized_overlap_row = $this->db->query(
                "SELECT COUNT(DISTINCT cur.id) AS total
                 FROM student_session cur
                 INNER JOIN students cur_students ON cur_students.id = cur.student_id
                 INNER JOIN student_session prev ON prev.session_id = ?
                 INNER JOIN students prev_students ON prev_students.id = prev.student_id
                    AND REPLACE(REPLACE(REPLACE(UPPER(TRIM(prev_students.admission_no)), '-', ''), ' ', ''), '/', '') =
                        REPLACE(REPLACE(REPLACE(UPPER(TRIM(cur_students.admission_no)), '-', ''), ' ', ''), '/', '')
                WHERE cur.session_id = ?",
                array($previous_session->id, $current_session)
            )->row();
            $normalized_admission_overlap = !empty($normalized_overlap_row) ? (int) $normalized_overlap_row->total : 0;

            $name_overlap_row = $this->db->query(
                "SELECT COUNT(DISTINCT cur.id) AS total
                 FROM student_session cur
                 INNER JOIN students cur_students ON cur_students.id = cur.student_id
                 INNER JOIN student_session prev ON prev.session_id = ?
                 INNER JOIN students prev_students ON prev_students.id = prev.student_id
                    AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(UPPER(CONCAT_WS('', prev_students.firstname, prev_students.middlename, prev_students.lastname)), ' ', ''), '-', ''), '/', ''), '.', ''), ',', '') =
                        REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(UPPER(CONCAT_WS('', cur_students.firstname, cur_students.middlename, cur_students.lastname)), ' ', ''), '-', ''), '/', ''), '.', ''), ',', '')
                    AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(UPPER(COALESCE(prev_students.father_name, '')), ' ', ''), '-', ''), '/', ''), '.', ''), ',', '') =
                        REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(UPPER(COALESCE(cur_students.father_name, '')), ' ', ''), '-', ''), '/', ''), '.', ''), ',', '')
                 WHERE cur.session_id = ?",
                array($previous_session->id, $current_session)
            )->row();
            $name_father_overlap = !empty($name_overlap_row) ? (int) $name_overlap_row->total : 0;
        }

        $class_section_pairs = array();
        $current_students_by_session = array();
        foreach ($studentlist as $student_row) {
            $student_session_id = isset($student_row['student_session_id']) ? (int) $student_row['student_session_id'] : 0;
            $class_id = isset($student_row['class_id']) ? (int) $student_row['class_id'] : 0;
            $section_id = isset($student_row['section_id']) ? (int) $student_row['section_id'] : 0;

            if ($student_session_id > 0) {
                $current_students_by_session[$student_session_id] = $student_row;
            }
            if ($class_id > 0 && $section_id > 0) {
                $class_section_pairs[$class_id . '_' . $section_id] = array(
                    'class_id'   => $class_id,
                    'section_id' => $section_id,
                );
            }
        }

        $paired_students = array();
        $current_student_session_ids = array();
        if (!empty($previous_session)) {
            foreach ($class_section_pairs as $class_section_pair) {
                $pair_students = $this->student_model->getPreviousSessionStudent(
                    $previous_session->id,
                    $class_section_pair['class_id'],
                    $class_section_pair['section_id']
                );

                if (empty($pair_students)) {
                    continue;
                }

                foreach ($pair_students as $pair_student) {
                    $current_student_session_id = isset($pair_student->current_student_session_id) ? (int) $pair_student->current_student_session_id : 0;
                    if ($current_student_session_id <= 0) {
                        continue;
                    }

                    $paired_students[$current_student_session_id] = $pair_student;
                    $current_student_session_ids[$current_student_session_id] = $current_student_session_id;
                }
            }
        }

        $balance_master_records = array();
        if (!empty($current_student_session_ids) && !empty($this->balance_group)) {
            $student_session_array = "(" . implode(",", $current_student_session_ids) . ")";
            $balance_master_records = $this->studentfeemaster_model->getBalanceMasterRecord($this->balance_group, $student_session_array);
        }

        $debug_students = array();
        $max_students = 10;
        $aggregate = array(
            'students_processed'             => 0,
            'students_paired_to_previous'    => count($paired_students),
            'balance_master_records'         => is_array($balance_master_records) ? count($balance_master_records) : 0,
            'students_using_balance_master'  => 0,
            'students_using_previous_fees'   => 0,
            'matched_by_student_id'          => 0,
            'matched_by_admission_no'        => 0,
            'matched_by_name_father'         => 0,
            'without_previous_session_id'    => 0,
            'students_with_previous_balance' => 0,
            'previous_fee_rows_total'        => 0,
        );

        foreach ($paired_students as $student_session_id => $paired_student) {
            $aggregate['students_processed']++;
            $current_student = isset($current_students_by_session[$student_session_id]) ? $current_students_by_session[$student_session_id] : array();
            $resolved_previous_student_session_id = !empty($paired_student->previous_student_session_id)
                ? (int) $paired_student->previous_student_session_id
                : $this->findPreviousSessionByAdmissionNo($previous_session->id, isset($current_student['admission_no']) ? $current_student['admission_no'] : '');
            $name_father_previous_session_id = $this->findPreviousSessionByNameForDashboard(
                $previous_session->id,
                isset($current_student['firstname']) ? $current_student['firstname'] : '',
                isset($current_student['middlename']) ? $current_student['middlename'] : '',
                isset($current_student['lastname']) ? $current_student['lastname'] : '',
                isset($current_student['father_name']) ? $current_student['father_name'] : ''
            );

            if (!empty($paired_student->previous_student_session_id)) {
                $aggregate['matched_by_student_id']++;
            } elseif (!empty($resolved_previous_student_session_id)) {
                $aggregate['matched_by_admission_no']++;
            } elseif (!empty($name_father_previous_session_id)) {
                $aggregate['matched_by_name_father']++;
            } else {
                $aggregate['without_previous_session_id']++;
            }

            $balance_master_amount = $this->findDashboardBalanceMasterAmount($balance_master_records, $student_session_id);
            if ($balance_master_amount !== null) {
                $summary = array(
                    'previous_balance' => $balance_master_amount,
                    'pending_months'   => ($balance_master_amount > 0) ? array('Balance Master' => 'Balance Master') : array(),
                    'debug'            => array(
                        'source'                => 'balance_master',
                        'balance_master_amount' => $balance_master_amount,
                    ),
                );
                $aggregate['students_using_balance_master']++;
            } else {
                $summary = $this->calculatePreviousSessionBalanceForDashboard(
                    $resolved_previous_student_session_id,
                    true
                );
                if (!empty($summary['debug']['master_rows_count'])) {
                    $aggregate['students_using_previous_fees']++;
                    $aggregate['previous_fee_rows_total'] += (int) $summary['debug']['master_rows_count'];
                }
                if (!isset($summary['debug'])) {
                    $summary['debug'] = array();
                }
                $summary['debug']['source'] = 'previous_session_fees';
            }

            if (!empty($summary['previous_balance']) && (float) $summary['previous_balance'] > 0) {
                $aggregate['students_with_previous_balance']++;
            }

            if (count($debug_students) < $max_students) {
                $debug_students[] = array(
                    'student_session_id' => $student_session_id,
                    'student_id'         => isset($current_student['id']) ? $current_student['id'] : '',
                    'admission_no'       => isset($current_student['admission_no']) ? $current_student['admission_no'] : '',
                    'student_name'       => trim(
                        (isset($current_student['firstname']) ? $current_student['firstname'] : '') . ' ' .
                        (isset($current_student['middlename']) ? $current_student['middlename'] : '') . ' ' .
                        (isset($current_student['lastname']) ? $current_student['lastname'] : '')
                    ),
                    'class'              => isset($current_student['class']) ? $current_student['class'] : '',
                    'section'            => isset($current_student['section']) ? $current_student['section'] : '',
                    'previous_student_session_id' => isset($paired_student->previous_student_session_id) ? $paired_student->previous_student_session_id : '',
                    'resolved_previous_student_session_id' => $resolved_previous_student_session_id,
                    'name_father_previous_session_id' => $name_father_previous_session_id,
                    'previous_balance'   => isset($summary['previous_balance']) ? $summary['previous_balance'] : 0,
                    'pending_months'     => isset($summary['pending_months']) ? array_values($summary['pending_months']) : array(),
                    'debug'              => isset($summary['debug']) ? $summary['debug'] : array(),
                );
            }
        }

        $widget_summary = $this->getPreviousBalanceStudentsData($start_date, $end_date);

        header('Content-Type: text/plain; charset=utf-8');
        echo "Previous Balance Dashboard Debug\n";
        echo "================================\n\n";
        echo "Session Name:       " . $this->setting_model->getCurrentSessionName() . "\n";
        echo "Current Session ID: " . $current_session . "\n";
        echo "Previous Session:   " . (!empty($previous_session) ? $previous_session->session : 'N/A') . "\n";
        echo "Current Month Start: " . date('Y-m-01', strtotime($start_date)) . "\n";
        echo "Current Month End:   " . date('Y-m-t', strtotime($end_date)) . "\n";
        echo "Students Found:      " . count($studentlist) . "\n";
        echo "Prev Session Count:  " . $previous_session_student_count . "\n";
        echo "Exact Adm Overlap:   " . $exact_admission_overlap . "\n";
        echo "Norm Adm Overlap:    " . $normalized_admission_overlap . "\n";
        echo "Name+Father Overlap: " . $name_father_overlap . "\n";
        echo "Widget Count:        " . (isset($widget_summary['total_students']) ? $widget_summary['total_students'] : 0) . "\n";
        echo "Widget Amount:       " . (isset($widget_summary['total_amount']) ? $widget_summary['total_amount'] : 0) . "\n";
        echo "Students Processed:  " . $aggregate['students_processed'] . "\n";
        echo "Paired To Prev Sess: " . $aggregate['students_paired_to_previous'] . "\n";
        echo "Balance Masters:     " . $aggregate['balance_master_records'] . "\n";
        echo "Matched By Student:  " . $aggregate['matched_by_student_id'] . "\n";
        echo "Matched By Adm No:   " . $aggregate['matched_by_admission_no'] . "\n";
        echo "Matched By Name+Dad: " . $aggregate['matched_by_name_father'] . "\n";
        echo "Unmatched Previous:  " . $aggregate['without_previous_session_id'] . "\n";
        echo "Using BalanceMaster: " . $aggregate['students_using_balance_master'] . "\n";
        echo "Using Prev Fees:     " . $aggregate['students_using_previous_fees'] . "\n";
        echo "With Prev Balance:   " . $aggregate['students_with_previous_balance'] . "\n";
        echo "Prev Fee Rows Total: " . $aggregate['previous_fee_rows_total'] . "\n";
        echo "Sample Students:     " . count($debug_students) . "\n\n";
        echo "Sample Students\n";
        echo "---------------\n";
        print_r($debug_students);
        exit;
    }

    public function pendingAnnualChargesPDF()
    {
        // Check permission
        if (!$this->rbac->hasPrivilege('pending_annual_charges_widget', 'can_view')) {
            access_denied();
        }
        
        try {
            // Get current session from setting model
            $current_session = $this->setting_model->getCurrentSession();
            
            if (empty($current_session)) {
                log_message('error', 'Current session not available in pendingAnnualChargesPDF');
                $this->session->set_flashdata('msg', '<div class="alert alert-danger">Error: Current session not available</div>');
                redirect('admin/admin/dashboard');
            }
            
            $current_month = date('m');
            $current_year = date('Y');
            $current_date = date('Y-m-d');

            // Get ALL pending annual charges (matching existing report logic with arrear + current)
            $current_date = $current_year . '-' . $current_month . '-20'; // Match existing report logic
            
            $sql = "SELECT 
                        students.admission_no,
                        students.firstname,
                        students.middlename,
                        students.lastname,
                        students.father_name,
                        classes.class,
                        sections.section,
                        fee_groups_feetype.amount,
                        fee_groups_feetype.due_date,
                        fee_groups.name as fee_group_name
                    FROM student_fees_master
                    INNER JOIN fee_session_groups ON fee_session_groups.id = student_fees_master.fee_session_group_id
                    INNER JOIN student_session ON student_session.id = student_fees_master.student_session_id
                    INNER JOIN students ON students.id = student_session.student_id
                    INNER JOIN classes ON classes.id = student_session.class_id
                    INNER JOIN sections ON sections.id = student_session.section_id
                    INNER JOIN fee_groups ON fee_groups.id = fee_session_groups.fee_groups_id
                    INNER JOIN fee_groups_feetype ON fee_groups.id = fee_groups_feetype.fee_groups_id
                    INNER JOIN feetype ON feetype.id = fee_groups_feetype.feetype_id
                    LEFT JOIN student_fees_deposite ON student_fees_deposite.student_fees_master_id = student_fees_master.id 
                        AND student_fees_deposite.fee_groups_feetype_id = fee_groups_feetype.id
                       WHERE feetype.code = 'Annual'
                           AND student_session.session_id = '" . $current_session . "'
                           AND fee_session_groups.session_id = '" . $current_session . "'
                        AND students.is_active = 'yes'
                        AND student_fees_deposite.id IS NULL
                        AND fee_groups_feetype.due_date <= '" . $current_date . "'
                    ORDER BY classes.class, sections.section, students.admission_no";
            
            $query = $this->db->query($sql);
            $data['pending_annual_charges'] = $query->result();
            
            // Get summary data
            $data['summary'] = $this->getPendingAnnualCharges();
            $data['current_month'] = date('F Y');
            $data['currency_symbol'] = $this->customlib->getSchoolCurrencyFormat();
            
            // Generate PDF using alternative method to avoid function conflicts
            $this->generatePDFAlternative($data);
        } catch (Exception $e) {
            // Log error and show error message
            log_message('error', 'Error in pendingAnnualChargesPDF: ' . $e->getMessage());
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Error generating PDF: ' . $e->getMessage() . '</div>');
            redirect('admin/admin/dashboard');
        }
    }

    private function generatePDFAlternative($data)
    {
        try {
            // Generate HTML content with print-friendly styling
            $html = $this->load->view('admin/pending_annual_charges_report', $data, true);
            
            if (empty($html)) {
                throw new Exception('Failed to generate HTML content for report');
            }
            
            // Output the HTML content directly - user can print to PDF from browser
            echo $html;
            exit;
            
        } catch (Exception $e) {
            log_message('error', 'Report generation failed: ' . $e->getMessage());
            throw new Exception('Report generation failed. Please try again.');
        }
    }

    public function handle_upload()
    {
        if (isset($_FILES["file"]) && !empty($_FILES['file']['name'])) {
            $allowedExts = array('sql');
            $temp        = explode(".", $_FILES["file"]["name"]);
            $extension   = end($temp);
            if ($_FILES["file"]["error"] > 0) {
                $error .= "Error opening the file<br />";
            }
            if ($_FILES["file"]["type"] != 'application/octet-stream') {

                $this->form_validation->set_message('handle_upload', 'File type not allowed');
                return false;
            }
            if (!in_array($extension, $allowedExts)) {

                $this->form_validation->set_message('handle_upload', 'Extension not allowed');
                return false;
            }
            if ($_FILES["file"]["size"] > 102400000) {

                $this->form_validation->set_message('handle_upload', 'File size shoud be less than 100 MB');
                return false;
            }
            return true;
        } else {
            $this->form_validation->set_message('handle_upload', 'File field is required');
            return false;
        }
    }

    public function generate_key($length = 12)
    {
        $str        = "";
        $characters = array_merge(range('A', 'Z'), range('a', 'z'), range('0', '9'));
        $max        = count($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $rand = mt_rand(0, $max);
            $str .= $characters[$rand];
        }
        return $str;
    }

    public function addCronsecretkey($id)
    {
        $key = $this->generate_key(25);
        $data = array('cron_secret_key' => $key);
        $this->setting_model->add_cronsecretkey($data, $id);
        redirect('admin/admin/backup');
    }

    public function updateandappCode()
    {
        $this->form_validation->set_rules('app-email', 'Email', 'required|valid_email|trim|xss_clean');
        $this->form_validation->set_rules('app-envato_market_purchase_code', 'Purchase Code', 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'app-email'                       => form_error('app-email'),
                'app-envato_market_purchase_code' => form_error('app-envato_market_purchase_code'),
            );
            $array = array('status' => '2', 'error' => $data);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($array));
        } else {
            //==================
            $response = $this->auth->andapp_update();
        }
    }

    public function filetype()
    {
    
        $data          = array();
        $data['title'] = 'File Type List';
        $this->session->set_userdata('top_menu', 'System Settings');
        $this->session->set_userdata('sub_menu', 'System Settings/filetype');
        $data['filetype'] = $this->filetype_model->get();
        $this->load->view('layout/header', $data);
        $this->load->view('admin/filetype', $data);
        $this->load->view('layout/footer', $data);
    }

    public function addfiletype()
    {
        $this->form_validation->set_rules('file_extension', $this->lang->line('allowed_extension'), 'required|trim|xss_clean|callback_validate_extension');
        $this->form_validation->set_rules('image_extension', $this->lang->line('allowed_extension'), 'required|trim|xss_clean|callback_validate_extension');
        $this->form_validation->set_rules('file_mime', $this->lang->line('allowed_mime_type'), 'required|trim|xss_clean|callback_validate_mime');
        $this->form_validation->set_rules('image_mime', $this->lang->line('allowed_mime_type'), 'required|trim|xss_clean|callback_validate_mime');
        $this->form_validation->set_rules('image_size', $this->lang->line('upload_size_in_bytes'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('file_size', $this->lang->line('upload_size_in_bytes'), 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {
            $data = array(
                'file_extension'  => form_error('file_extension'),
                'file_mime'       => form_error('file_mime'),
                'image_extension' => form_error('image_extension'),
                'image_mime'      => form_error('image_mime'),
                'image_size'      => form_error('image_size'),
                'file_size'       => form_error('file_size'),
            );
            $array = array('status' => 'fail', 'error' => $data);
            echo json_encode($array);
        } else {
            $insert_array = array(
                'file_extension'  => $this->input->post('file_extension'),
                'file_mime'       => $this->input->post('file_mime'),
                'image_extension' => $this->input->post('image_extension'),
                'image_mime'      => $this->input->post('image_mime'),
                'file_size'       => $this->input->post('file_size'),
                'image_size'      => $this->input->post('image_size'),
            );

            $inserted_id = $this->filetype_model->add($insert_array);

            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            echo json_encode($array);
        }
    }

    public function validate_extension($extension)
    {
        if (preg_match('/^([A-Za-z0-9]+)(,\s[A-Za-z0-9]+)*$/', $extension)) {
            return true;
        } else {
            $this->form_validation->set_message('validate_extension', 'The %s field must be like jpg, jpeg');
            return false;
        }
    }

    public function validate_mime($mime)
    {
        if (preg_match('/^([A-Za-z0-9-.+\/]+)(,\s[A-Za-z0-9-.+\/]+)*$/', $mime)) {
            return true;
        } else {
            $this->form_validation->set_message('validate_mime', 'The %s field must be like audio/mp4, video/mp4');
            return false;
        }
    }

      public function updateaddon()
    {
        $this->form_validation->set_rules('app-email', 'Email', 'required|valid_email|trim|xss_clean');
        $this->form_validation->set_rules('app-envato_market_purchase_code', 'Purchase Code', 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {

            $data = array(
                'app-email'                       => form_error('app-email'),
                'app-envato_market_purchase_code' => form_error('app-envato_market_purchase_code'),
            );
            
            $array = array('status' => '2', 'error' => $data);

            return $this->output
                ->set_content_type('application/json')
                ->set_status_header(200)
                ->set_output(json_encode($array));
        } else {
            //==================
            $response = $this->auth->addon_update();
        }
    }

    public function searchvalidation()
    {
        $search_text1       = $this->input->post('search_text1');
        $params      = array('search_text1'=> $search_text1);
        $array       = array('status' => 1, 'error' => '', 'params' => $params);
        echo json_encode($array);
    }


     public function dtstudentlist($search_text)
    {
       if($search_text==="0"){           
            $search_text="";
        }
       $sch_setting    = $this->sch_setting_detail;
       $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $classlist                   = $this->class_model->get();
                $classlist      = $classlist;
                $carray   = array();
                if (!empty($classlist)) {
                    foreach ($classlist as $ckey => $cvalue) {

                        $carray[] = $cvalue["id"];
                    }
                }
        
      
        $resultlist = $this->student_model->searchFullText($search_text, $carray);
        $fields = $this->customfield_model->get_custom_fields('students', 1);
        $students = json_decode($resultlist);
         $dt_data=array();
        if (!empty($students->data)) {
            foreach ($students->data as $student_key => $student) {

                $editbtn='';
                $deletebtn = '';
                $viewbtn='';

                 $viewbtn = "<a href='".base_url()."student/view/".$student->id."'   class='btn btn-default btn-xs'  data-toggle='tooltip' data-placement='left' title='" . $this->lang->line('show') . "'><i class='fa fa-reorder'></i></a>";

                 if ($this->rbac->hasPrivilege('student', 'can_edit')) {
                    $editbtn = "<a href='".base_url()."student/edit/".$student->id."'   class='btn btn-default btn-xs'  data-toggle='tooltip' data-placement='left' title='" . $this->lang->line('edit') . "'><i class='fa fa-pencil'></i></a>";
                }
                if ($this->rbac->hasPrivilege('collect_fees', 'can_add')) {
                    
                    $collectbtn = "<a href='".base_url()."studentfee/addfee/".$student->student_session_id."'   class='btn btn-default btn-xs'  data-toggle='tooltip' data-placement='left' title='" . $this->lang->line('add_fees') . "'><span >".$currency_symbol."</a>";
                }
             
                $row   = array();
                $row[] = $student->admission_no;
                $row[] =  "<a href='".base_url()."student/view/".$student->id."'>".$this->customlib->getFullName($student->firstname,$student->middlename,$student->lastname,$sch_setting->middlename,$sch_setting->lastname)."</a>";              
                  $row[] = $student->class . "(" . $student->section . ")";
                if ($sch_setting->father_name) {
                    $row[]= $student->father_name ;
                }
                
                   $row[]=  $this->customlib->dateformat($student->dob);
              

                $row[] = $student->gender;
                if ($sch_setting->category) {
                    $row[] = $student->category ;
                }
                if ($sch_setting->mobile_no) {
                    $row[] = $student->mobileno ;
                }

                foreach ($fields as $fields_key => $fields_value) {
                   
                    $custom_name = $fields_value->name ;
                   $display_field=$student->$custom_name;
                 if($fields_value->type == "link"){
                     $display_field= "<a href=".$student->$custom_name." target='_blank'>".$student->$custom_name."</a>";

                 }
                 $row[] = $display_field ;  

                }
                $row[] = $viewbtn.''.$editbtn.''.$collectbtn;

                $dt_data[] = $row;
            }

        }
        $json_data = array(
            "draw"            => intval($students->draw),
            "recordsTotal"    => intval($students->recordsTotal),
            "recordsFiltered" => intval($students->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data); 

    }

}

