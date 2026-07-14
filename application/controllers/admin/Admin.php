    <?php

    if (!defined('BASEPATH')) {
        exit('No direct script access allowed');
    } 

    class Admin extends Admin_Controller
    {
        protected $month_collection_debug = array();
        protected $fee_collection_date_index = null;
        protected $dashboard_cache_ttl       = 180;

        public function __construct()
        {
            parent::__construct();
            $this->load->model("classteacher_model");
            $this->load->model("Staff_model");
            $this->load->library('Enc_lib');
            $this->load->driver('cache', array('adapter' => 'file', 'backup' => 'dummy'));
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
            $first_day_this_month     = date('Y-m-01');
            $month_collection = $this->getMonthlyCollectionAmount($first_day_this_month, $current_date);
            $expense                  = $this->expense_model->getTotalExpenseBwdate($first_day_this_month, $current_date);
            if (!empty($expense)) {
                $month_expense = $month_expense + $expense->amount;
            }

            $data['month_collection'] = $month_collection;
            $data['month_collection_debug'] = $this->month_collection_debug;
            $data['month_expense']    = $month_expense;

            $tot_students = $this->studentsession_model->getTotalStudentBySession();
            if (!empty($tot_students)) {
                $total_students = $tot_students->total_student;
            }

            $data['total_students'] = $total_students;

            $data["roles"] = $this->role_model->getCountByRoles();

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
            //======================== getexpense by month (batch query - reduces 12 calls to 1) ==============================
            $expense_by_month = $this->admin_model->getExpenseByMonthRange($year_str_month, $year_end_month);
            $ex = array();
            $start_session_month = strtotime($year_str_month);
            while ($start_session_month <= $end) {
                $month_start = date('Y-m-01', $start_session_month);
                $ex[] = isset($expense_by_month[$month_start]) ? (float) $expense_by_month[$month_start] : 0;
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

            //======================= current month /expense (batch query - reduces 31 calls to 1) ==============================
            $startdate_str = date('Y-m-01');
            $enddate_str   = date('Y-m-t');
            $expense_by_day = $this->admin_model->getExpenseByDateRange($startdate_str, $enddate_str);
            $days_expense = array();
            $currentdate = strtotime($startdate_str);
            $end_ts      = strtotime($enddate_str);
            while ($currentdate <= $end_ts) {
                $cur_date = date('Y-m-d', $currentdate);
                $days_expense[] = isset($expense_by_day[$cur_date]) ? (float) $expense_by_day[$cur_date] : 0;
                $currentdate = strtotime('+1 day', $currentdate);
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
            $dashboard_cache_key = $this->getDashboardCacheKey($staffid, $role_id);
            $cached_dashboard    = $this->getCachedDashboardPayload($dashboard_cache_key);
            if (is_array($cached_dashboard)) {
                $data = array_merge($cached_dashboard, $data);
                $start_date = date('Y-m-01');
                $end_date   = date('Y-m-t');
                $fee_calculation = $this->calculateCompleteFeeBalance($start_date, $end_date);
                $data['total_receivable_fee'] = isset($fee_calculation['total_receivable_fee']) ? (float) $fee_calculation['total_receivable_fee'] : 0;
                $data['total_remaining_balance'] = isset($fee_calculation['total_remaining_balance']) ? (float) $fee_calculation['total_remaining_balance'] : 0;
                $this->load->view('layout/header', $data);
                $this->load->view('admin/dashboard', $data);
                $this->load->view('layout/footer', $data);
                return;
            }

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
            $first_day_this_month     = date('Y-m-01');
            $month_collection = $this->getMonthlyCollectionAmount($first_day_this_month, $current_date);
            $expense                  = $this->expense_model->getTotalExpenseBwdate($first_day_this_month, $current_date);
            $income                  = $this->income_model->getTotalExpenseBwdate($first_day_this_month, $current_date);
            if (!empty($expense)) {
                $month_expense = $month_expense + $expense->amount;
            }
            if (!empty($income)) {
                $month_income = $month_income + $income->amount;
            }

            $data['month_collection'] = $month_collection;
            $data['month_collection_debug'] = $this->month_collection_debug;
            $data['month_expense']    = $month_expense;
            $data['month_income']    = $month_income;

            $tot_students = $this->studentsession_model->getTotalStudentBySession();
            if (!empty($tot_students)) {
                $total_students = $tot_students->total_student;
            }

            $data['total_students'] = $total_students;

            $data["roles"] = $this->role_model->getCountByRoles();

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
            //======================== getexpense by month (batch query - reduces 12 calls to 1) ==============================
            $expense_by_month = $this->admin_model->getExpenseByMonthRange($year_str_month, $year_end_month);
            $ex = array();
            $start_session_month = strtotime($year_str_month);
            while ($start_session_month <= $end) {
                $month_start = date('Y-m-01', $start_session_month);
                $ex[] = isset($expense_by_month[$month_start]) ? (float) $expense_by_month[$month_start] : 0;
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

            //======================= current month /expense (batch query - reduces 31 calls to 1) ==============================
            $startdate_str = date('Y-m-01');
            $enddate_str   = date('Y-m-t');
            $expense_by_day = $this->admin_model->getExpenseByDateRange($startdate_str, $enddate_str);
            $days_expense = array();
            $currentdate = strtotime($startdate_str);
            $end_ts      = strtotime($enddate_str);
            while ($currentdate <= $end_ts) {
                $cur_date = date('Y-m-d', $currentdate);
                $days_expense[] = isset($expense_by_day[$cur_date]) ? (float) $expense_by_day[$cur_date] : 0;
                $currentdate = strtotime('+1 day', $currentdate);
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

            $fee_calculation = $this->calculateCompleteFeeBalance($start_date, $end_date);
            $data['total_receivable_fee'] = isset($fee_calculation['total_receivable_fee']) ? (float) $fee_calculation['total_receivable_fee'] : 0;
            $data['total_remaining_balance'] = isset($fee_calculation['total_remaining_balance']) ? (float) $fee_calculation['total_remaining_balance'] : 0;

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
            $this->setCachedDashboardPayload($dashboard_cache_key, $data);

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
            $date_index    = $this->buildFeeCollectionDateIndex($feecollection_array);
            if (!empty($date_index) && $st_date !== false && $ed_date !== false) {
                while ($st_date <= $ed_date) {
                    $date = date('Y-m-d', $st_date);
                    if (isset($date_index[$date])) {
                        $return_amount += $date_index[$date];
                    }
                    $st_date = $st_date + 86400;
                }
            }

            return $return_amount;
        }

        private function buildFeeCollectionDateIndex($feecollection_array)
        {
            if (!is_array($feecollection_array) || empty($feecollection_array)) {
                return array();
            }

            if (is_array($this->fee_collection_date_index)) {
                return $this->fee_collection_date_index;
            }

            $this->fee_collection_date_index = array();
            foreach ($feecollection_array as $row) {
                if (!is_array($row) || !isset($row['date'])) {
                    continue;
                }
                $date = (string) $row['date'];
                $amount = isset($row['amount']) ? (float) $row['amount'] : 0;
                $fine   = isset($row['amount_fine']) ? (float) $row['amount_fine'] : 0;
                if (!isset($this->fee_collection_date_index[$date])) {
                    $this->fee_collection_date_index[$date] = 0;
                }
                $this->fee_collection_date_index[$date] += ($amount + $fine);
            }

            return $this->fee_collection_date_index;
        }

        private function getMonthlyCollectionAmount($start_date, $end_date)
        {
            $month_collection = 0;
            $collection_rows  = $this->studentfeemaster_model->getFeeCollectionReport($start_date, $end_date);
            $report_amount    = 0;

            $this->month_collection_debug = array(
                'start_date' => $start_date,
                'end_date' => $end_date,
                'report_rows' => 0,
                'report_amount' => 0,
                'fallback_used' => 0,
                'fallback_rows' => 0,
                'fallback_json_objects' => 0,
                'fallback_unparsed_dates' => 0,
                'fallback_min_date' => '',
                'fallback_max_date' => '',
                'fallback_matched_entries' => 0,
                'fallback_amount' => 0,
                'created_at_fallback_used' => 0,
                'created_at_rows' => 0,
                'created_at_amount' => 0,
                'all_session_fallback_used' => 0,
                'all_session_rows' => 0,
                'all_session_min_date' => '',
                'all_session_max_date' => '',
                'all_session_matched_entries' => 0,
                'all_session_amount' => 0,
            'all_session_created_at_fallback_used' => 0,
            'all_session_created_at_rows' => 0,
            'all_session_created_at_amount' => 0,
            'latest_month_fallback_used' => 0,
            'latest_month_range' => '',
            'latest_month_rows' => 0,
            'latest_month_amount' => 0,
            'latest_json_month_fallback_used' => 0,
            'latest_json_month_range' => '',
            'latest_json_month_matched_entries' => 0,
            'latest_json_month_amount' => 0,
            'final_amount' => 0,
        );

            if (!empty($collection_rows)) {
                foreach ($collection_rows as $row) {
                    $amount      = isset($row['amount']) ? (float) $row['amount'] : 0;
                    $amount_fine = isset($row['amount_fine']) ? (float) $row['amount_fine'] : 0;
                    $row_total = ($amount + $amount_fine);
                    $month_collection += $row_total;
                    $report_amount += $row_total;
                }
            }
            $this->month_collection_debug['report_rows'] = !empty($collection_rows) ? count($collection_rows) : 0;
            $this->month_collection_debug['report_amount'] = $report_amount;

            // Fallback: compute directly from amount_detail JSON for current session.
            if ($month_collection == 0) {
                $current_session = $this->setting_model->getCurrentSession();
                $start_ts = strtotime($start_date);
                $end_ts   = strtotime($end_date);
                $this->month_collection_debug['fallback_used'] = 1;
                $this->month_collection_debug['session_id'] = $current_session;

                $this->db->select('student_fees_deposite.amount_detail');
                $this->db->from('student_fees_deposite');
                $this->db->join('student_fees_master', 'student_fees_master.id = student_fees_deposite.student_fees_master_id');
                $this->db->join('student_session', 'student_session.id = student_fees_master.student_session_id');
                $this->db->where('student_session.session_id', $current_session);
                $query = $this->db->get();
                $rows  = $query->result();
                $this->month_collection_debug['fallback_rows'] = !empty($rows) ? count($rows) : 0;

                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $amount_detail = json_decode($row->amount_detail);
                        if (!is_object($amount_detail)) {
                            continue;
                        }
                        $this->month_collection_debug['fallback_json_objects']++;

                        foreach ($amount_detail as $detail) {
                            $detail_date_value = $this->extractAmountDetailDateValue($detail);
                            if ($detail_date_value === null) {
                                continue;
                            }

                            $entry_ts = $this->parseDateToDayTimestamp($detail_date_value, $start_ts, $end_ts);
                            if ($entry_ts === null || $start_ts === false || $end_ts === false) {
                                $this->month_collection_debug['fallback_unparsed_dates']++;
                                continue;
                            }
                            $entry_ymd = date('Y-m-d', $entry_ts);
                            if ($this->month_collection_debug['fallback_min_date'] === '' || $entry_ymd < $this->month_collection_debug['fallback_min_date']) {
                                $this->month_collection_debug['fallback_min_date'] = $entry_ymd;
                            }
                            if ($this->month_collection_debug['fallback_max_date'] === '' || $entry_ymd > $this->month_collection_debug['fallback_max_date']) {
                                $this->month_collection_debug['fallback_max_date'] = $entry_ymd;
                            }
                            $entry_day = $entry_ts;

                            if ($entry_day !== false && $entry_day >= $start_ts && $entry_day <= $end_ts) {
                                $amount = isset($detail->amount) ? (float) $detail->amount : 0;
                                $fine   = isset($detail->amount_fine) ? (float) $detail->amount_fine : 0;
                                $entry_total = ($amount + $fine);
                                $month_collection += $entry_total;
                                $this->month_collection_debug['fallback_amount'] += $entry_total;
                                $this->month_collection_debug['fallback_matched_entries']++;
                            }
                        }
                    }
                }
            }

            // Second fallback: use deposit row created_at if JSON entry dates are not aligned with collection month.
            if ($month_collection == 0) {
                $current_session = $this->setting_model->getCurrentSession();
                $this->month_collection_debug['created_at_fallback_used'] = 1;

                $this->db->select('student_fees_deposite.amount_detail');
                $this->db->from('student_fees_deposite');
                $this->db->join('student_fees_master', 'student_fees_master.id = student_fees_deposite.student_fees_master_id');
                $this->db->join('student_session', 'student_session.id = student_fees_master.student_session_id');
                $this->db->where('student_session.session_id', $current_session);
                $this->db->where('DATE(student_fees_deposite.created_at) >=', $start_date);
                $this->db->where('DATE(student_fees_deposite.created_at) <=', $end_date);
                $query = $this->db->get();
                $rows  = $query->result();
                $this->month_collection_debug['created_at_rows'] = !empty($rows) ? count($rows) : 0;

                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $amount_detail = json_decode($row->amount_detail);
                        if (!is_object($amount_detail)) {
                            continue;
                        }

                        foreach ($amount_detail as $detail) {
                            $amount = isset($detail->amount) ? (float) $detail->amount : 0;
                            $fine   = isset($detail->amount_fine) ? (float) $detail->amount_fine : 0;
                            $entry_total = ($amount + $fine);
                            $month_collection += $entry_total;
                            $this->month_collection_debug['created_at_amount'] += $entry_total;
                        }
                    }
                }
            }

            // Third fallback: check all sessions (database/session mismatch guard).
            if ($month_collection == 0) {
                $start_ts = strtotime($start_date);
                $end_ts   = strtotime($end_date);
                $this->month_collection_debug['all_session_fallback_used'] = 1;

                $this->db->select('amount_detail');
                $this->db->from('student_fees_deposite');
                $query = $this->db->get();
                $rows  = $query->result();
                $this->month_collection_debug['all_session_rows'] = !empty($rows) ? count($rows) : 0;

                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $amount_detail = json_decode($row->amount_detail);
                        if (!is_object($amount_detail)) {
                            continue;
                        }

                        foreach ($amount_detail as $detail) {
                            $detail_date_value = $this->extractAmountDetailDateValue($detail);
                            if ($detail_date_value === null) {
                                continue;
                            }

                            $entry_ts = $this->parseDateToDayTimestamp($detail_date_value, $start_ts, $end_ts);
                            if ($entry_ts === null || $start_ts === false || $end_ts === false) {
                                continue;
                            }

                            $entry_ymd = date('Y-m-d', $entry_ts);
                            if ($this->month_collection_debug['all_session_min_date'] === '' || $entry_ymd < $this->month_collection_debug['all_session_min_date']) {
                                $this->month_collection_debug['all_session_min_date'] = $entry_ymd;
                            }
                            if ($this->month_collection_debug['all_session_max_date'] === '' || $entry_ymd > $this->month_collection_debug['all_session_max_date']) {
                                $this->month_collection_debug['all_session_max_date'] = $entry_ymd;
                            }

                            if ($entry_ts >= $start_ts && $entry_ts <= $end_ts) {
                                $amount = isset($detail->amount) ? (float) $detail->amount : 0;
                                $fine   = isset($detail->amount_fine) ? (float) $detail->amount_fine : 0;
                                $entry_total = ($amount + $fine);
                                $month_collection += $entry_total;
                                $this->month_collection_debug['all_session_amount'] += $entry_total;
                                $this->month_collection_debug['all_session_matched_entries']++;
                            }
                        }
                    }
                }
            }

            // Fourth fallback: created_at range across all sessions (helps when current session mapping is off).
        if ($month_collection == 0) {
            $this->month_collection_debug['all_session_created_at_fallback_used'] = 1;

                $this->db->select('amount_detail');
                $this->db->from('student_fees_deposite');
                $this->db->where('DATE(created_at) >=', $start_date);
                $this->db->where('DATE(created_at) <=', $end_date);
                $query = $this->db->get();
                $rows  = $query->result();
                $this->month_collection_debug['all_session_created_at_rows'] = !empty($rows) ? count($rows) : 0;

                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $amount_detail = json_decode($row->amount_detail);
                        if (!is_object($amount_detail)) {
                            continue;
                        }

                        foreach ($amount_detail as $detail) {
                            $amount = isset($detail->amount) ? (float) $detail->amount : 0;
                            $fine   = isset($detail->amount_fine) ? (float) $detail->amount_fine : 0;
                            $entry_total = ($amount + $fine);
                            $month_collection += $entry_total;
                            $this->month_collection_debug['all_session_created_at_amount'] += $entry_total;
                        }
                    }
            }
        }

        // Fifth fallback: if selected month has no deposits, use latest month with data from DB.
        if ($month_collection == 0) {
            $this->month_collection_debug['latest_month_fallback_used'] = 1;
            $max_query = $this->db->query("SELECT MAX(created_at) as max_created_at FROM student_fees_deposite");
            $max_row = $max_query->row();

            if (!empty($max_row) && !empty($max_row->max_created_at)) {
                $latest_ts = strtotime($max_row->max_created_at);
                if ($latest_ts !== false) {
                    $latest_start = date('Y-m-01', $latest_ts);
                    $latest_end   = date('Y-m-t', $latest_ts);
                    $this->month_collection_debug['latest_month_range'] = $latest_start . ' to ' . $latest_end;

                    $this->db->select('amount_detail');
                    $this->db->from('student_fees_deposite');
                    $this->db->where('DATE(created_at) >=', $latest_start);
                    $this->db->where('DATE(created_at) <=', $latest_end);
                    $latest_rows_q = $this->db->get();
                    $latest_rows = $latest_rows_q->result();
                    $this->month_collection_debug['latest_month_rows'] = !empty($latest_rows) ? count($latest_rows) : 0;

                    if (!empty($latest_rows)) {
                        foreach ($latest_rows as $latest_row) {
                            $amount_detail = json_decode($latest_row->amount_detail);
                            if (!is_object($amount_detail)) {
                                continue;
                            }

                            foreach ($amount_detail as $detail) {
                                $amount = isset($detail->amount) ? (float) $detail->amount : 0;
                                $fine   = isset($detail->amount_fine) ? (float) $detail->amount_fine : 0;
                                $entry_total = ($amount + $fine);
                                $month_collection += $entry_total;
                                $this->month_collection_debug['latest_month_amount'] += $entry_total;
                            }
                        }
                    }
                }
            }
        }

        // Sixth fallback: use latest month discovered in JSON payment dates (works when created_at is NULL/invalid).
        if ($month_collection == 0 && !empty($this->month_collection_debug['all_session_max_date'])) {
            $latest_json_max_date = $this->month_collection_debug['all_session_max_date'];
            $latest_json_ts = strtotime($latest_json_max_date);

            if ($latest_json_ts !== false) {
                $latest_json_start = date('Y-m-01', $latest_json_ts);
                $latest_json_end   = date('Y-m-t', $latest_json_ts);
                $latest_json_start_ts = strtotime($latest_json_start);
                $latest_json_end_ts   = strtotime($latest_json_end);

                $this->month_collection_debug['latest_json_month_fallback_used'] = 1;
                $this->month_collection_debug['latest_json_month_range'] = $latest_json_start . ' to ' . $latest_json_end;

                $this->db->select('amount_detail');
                $this->db->from('student_fees_deposite');
                $json_rows_q = $this->db->get();
                $json_rows = $json_rows_q->result();

                if (!empty($json_rows)) {
                    foreach ($json_rows as $json_row) {
                        $amount_detail = json_decode($json_row->amount_detail);
                        if (!is_object($amount_detail)) {
                            continue;
                        }

                        foreach ($amount_detail as $detail) {
                            $detail_date_value = $this->extractAmountDetailDateValue($detail);
                            if ($detail_date_value === null) {
                                continue;
                            }

                            $entry_ts = $this->parseDateToDayTimestamp($detail_date_value, $latest_json_start_ts, $latest_json_end_ts);
                            if ($entry_ts === null) {
                                continue;
                            }

                            if ($entry_ts >= $latest_json_start_ts && $entry_ts <= $latest_json_end_ts) {
                                $amount = isset($detail->amount) ? (float) $detail->amount : 0;
                                $fine   = isset($detail->amount_fine) ? (float) $detail->amount_fine : 0;
                                $entry_total = ($amount + $fine);
                                $month_collection += $entry_total;
                                $this->month_collection_debug['latest_json_month_amount'] += $entry_total;
                                $this->month_collection_debug['latest_json_month_matched_entries']++;
                            }
                        }
                    }
                }
            }
        }

        $this->month_collection_debug['final_amount'] = $month_collection;

            return $month_collection;
        }

        private function extractAmountDetailDateValue($detail)
        {
            if (!is_object($detail)) {
                return null;
            }

            $candidate_keys = array(
                'date',
                'payment_date',
                'paid_date',
                'deposit_date',
                'collection_date',
                'created_at',
                'date_time',
                'datetime',
                'timestamp',
                'time',
            );

            foreach ($candidate_keys as $key) {
                if (isset($detail->$key) && $detail->$key !== '') {
                    return $detail->$key;
                }
            }

            return null;
        }

        private function parseDateToDayTimestamp($date_value, $start_ts = null, $end_ts = null)
        {
            if ($date_value === null || $date_value === '') {
                return null;
            }

            $date_str = trim((string) $date_value);
            $candidate_timestamps = array();

            // Support Unix timestamps stored directly (seconds/milliseconds) or .NET /Date(....)/.
            if (preg_match('/^\/Date\((-?\d+)\)\/$/', $date_str, $matches)) {
                $raw_ts = (int) $matches[1];
                $raw_ts = (abs($raw_ts) > 9999999999) ? (int) floor($raw_ts / 1000) : $raw_ts;
                $candidate_timestamps[] = strtotime(date('Y-m-d', $raw_ts));
            } elseif (preg_match('/^-?\d+$/', $date_str)) {
                $raw_ts = (int) $date_str;
                $raw_ts = (abs($raw_ts) > 9999999999) ? (int) floor($raw_ts / 1000) : $raw_ts;
                $candidate_timestamps[] = strtotime(date('Y-m-d', $raw_ts));
            }

            $formats = array(
                'Y-m-d',
                'Y-m-d H:i:s',
                'Y-m-d h:i:s A',
                'd/m/Y',
                'd/m/Y H:i:s',
                'd/m/Y h:i:s A',
                'm/d/Y',
                'm/d/Y H:i:s',
                'm/d/Y h:i:s A',
                'd-m-Y',
                'd-m-Y H:i:s',
                'd-m-Y h:i:s A',
                'm-d-Y',
                'm-d-Y H:i:s',
                'm-d-Y h:i:s A',
                'd/m/y',
                'd/m/y H:i:s',
                'd/m/y h:i:s A',
                'm/d/y',
                'm/d/y H:i:s',
                'm/d/y h:i:s A',
                'd-m-y',
                'd-m-y H:i:s',
                'd-m-y h:i:s A',
                'm-d-y',
                'm-d-y H:i:s',
                'm-d-y h:i:s A',
            );

            foreach ($formats as $format) {
                $dt = DateTime::createFromFormat($format, $date_str);
                if ($dt instanceof DateTime) {
                    $candidate_timestamps[] = strtotime($dt->format('Y-m-d'));
                }
            }

            if (!empty($candidate_timestamps)) {
                $candidate_timestamps = array_values(array_unique($candidate_timestamps));

                // If a range is provided, prefer the candidate that falls inside it.
                if ($start_ts !== null && $end_ts !== null) {
                    foreach ($candidate_timestamps as $candidate_ts) {
                        if ($candidate_ts >= $start_ts && $candidate_ts <= $end_ts) {
                            return $candidate_ts;
                        }
                    }
                }

                return $candidate_timestamps[0];
            }

            $ts = strtotime($date_str);
            if ($ts === false) {
                return null;
            }

            return strtotime(date('Y-m-d', $ts));
        }

        private function getDashboardCacheKey($staff_id, $role_id)
        {
            $session_array = $this->session->userdata('session_array');
            $active_session_id = (!empty($session_array) && isset($session_array['session_id'])) ? $session_array['session_id'] : $this->setting_model->getCurrentSession();
            $date_bucket = date('Y_m_d_H');

            return 'admin_dashboard_' . (int) $active_session_id . '_' . (int) $staff_id . '_' . (int) $role_id . '_' . $date_bucket;
        }

        private function getCachedDashboardPayload($cache_key)
        {
            if (!isset($this->cache)) {
                return false;
            }

            $cached = $this->cache->get($cache_key);
            return is_array($cached) ? $cached : false;
        }

        private function setCachedDashboardPayload($cache_key, $payload)
        {
            if (!isset($this->cache) || !is_array($payload)) {
                return false;
            }

            return $this->cache->save($cache_key, $payload, $this->dashboard_cache_ttl);
        }

        private function calculateReceivableFromFeesAwaiting($fees_awaiting)
        {
            $total_receivable_fee = 0;
            if (!is_array($fees_awaiting) && !is_object($fees_awaiting)) {
                return $total_receivable_fee;
            }

            foreach ($fees_awaiting as $fee_row) {
                $amount_to_be_taken = 0;
                $is_system = isset($fee_row->is_system) ? (int) $fee_row->is_system : 0;
                if ($is_system === 1) {
                    $amount_to_be_taken = isset($fee_row->amount) ? (float) $fee_row->amount : 0;
                } else {
                    $amount_to_be_taken = isset($fee_row->fee_amount) ? (float) $fee_row->fee_amount : 0;
                }

                if ($amount_to_be_taken <= 0) {
                    continue;
                }

                $paid_total = 0;
                $discount_total = 0;
                if (isset($fee_row->amount_detail) && is_string($fee_row->amount_detail)) {
                    $decoded = json_decode($fee_row->amount_detail);
                    if (is_array($decoded) || is_object($decoded)) {
                        foreach ($decoded as $detail) {
                            $paid_total += isset($detail->amount) ? (float) $detail->amount : 0;
                            $discount_total += isset($detail->amount_discount) ? (float) $detail->amount_discount : 0;
                        }
                    }
                }

                $remaining = $amount_to_be_taken - ($paid_total + $discount_total);
                if ($remaining > 0) {
                    $total_receivable_fee += $remaining;
                }
            }

            return $total_receivable_fee;
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
         * Calculate complete fee balance - ALL pending current fees + ALL arrears
         * Includes: Current month pending + Previous months unpaid + Balance Master
         * @param string $start_date Current month start date
         * @param string $end_date Current month end date
         * @return array Array with total_receivable_fee and total_remaining_balance
         */
        public function calculateCompleteFeeBalance($start_date, $end_date)
        {
            try {
                $session_array = $this->session->userdata('session_array');
                $active_session_id = (!empty($session_array) && isset($session_array['session_id'])) ? $session_array['session_id'] : $this->setting_model->getCurrentSession();
                $cache_key = 'dashboard_fee_balance_report_v2_' . (int) $active_session_id . '_' . $start_date . '_' . $end_date;
                $cached = $this->getCachedDashboardPayload($cache_key);
                if (is_array($cached) && isset($cached['total_receivable_fee'])) {
                    return $cached;
                }

                $grand_total_current = 0;
                $grand_total_arrears = 0;
                $grand_total_fine = 0;
                $grand_total_received = 0;
                $grand_total_discount = 0;
                
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
                
                
                // Get active student session IDs only (memory-optimized)
                $this->load->model('student_model');
                $studentlist = $this->student_model->searchByClassSectionMinimal(null, null);
                
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
                        $monthfee = 0;
                        $deposit = 0;
                        $discount = 0;
                        $previous = 0;
                        $previous_monthfee = 0;
                        $previous_deposit = 0;
                        $previous_discount = 0;
                        $fine = 0;
                        
                        foreach ($student_total_fees as $student_total_fees_key => $student_total_fees_value) {
                            if (!empty($student_total_fees_value->fees)) {
                                foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {

                                    if ($each_fee_value->name == 'Balance Master') {
                                        $previous += $each_fee_value->amount;
                                    }

                                    $amount_detail = json_decode($each_fee_value->amount_detail);

                                    if ($each_fee_value->due_date >= $start_date && $each_fee_value->due_date <= $end_date) {
                                        if ($each_fee_value->name != 'Balance Master') {
                                            $monthfee += $each_fee_value->amount;
                                        }
                                    }

                                    if ($each_fee_value->due_date >= $current_section1 && $each_fee_value->due_date < $start_date) {
                                        if ($each_fee_value->name != 'Balance Master') {
                                            $previous_monthfee += $each_fee_value->amount;
                                        }
                                    }

                                    if (is_object($amount_detail)) {
                                        foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                            if ($amount_detail_value->date >= $start_date && $amount_detail_value->date <= $end_date) {
                                                $deposit += $amount_detail_value->amount;
                                                $fine += $amount_detail_value->amount_fine;
                                                $discount += $amount_detail_value->amount_discount;
                                            }

                                            if ($amount_detail_value->date >= $current_section1 && $amount_detail_value->date < $start_date) {
                                                $previous_deposit += $amount_detail_value->amount;
                                                $previous_discount += $amount_detail_value->amount_discount;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        $arrears = ($previous + $previous_monthfee) - ($previous_deposit + $previous_discount);
                        $total = $monthfee + $arrears + $fine;
                        $balance = $total - ($deposit + $discount);

                        $grand_total_current += $monthfee;
                        $grand_total_arrears += $arrears;
                        $grand_total_fine += $fine;
                        $grand_total_received += $deposit;
                        $grand_total_discount += $discount;
                    }
                }

                $total_receivable_fee = $grand_total_current + $grand_total_arrears + $grand_total_fine;
                $total_remaining_balance = $total_receivable_fee - ($grand_total_received + $grand_total_discount);
                
                $result = array(
                    'total_receivable_fee' => $total_receivable_fee,
                    'total_remaining_balance' => $total_remaining_balance
                );
                $this->setCachedDashboardPayload($cache_key, $result);
                return $result;
                
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
                $session_array = $this->session->userdata('session_array');
                $active_session_id = (!empty($session_array) && isset($session_array['session_id'])) ? $session_array['session_id'] : $this->setting_model->getCurrentSession();
                $cache_key = 'dashboard_pending_annual_' . (int) $active_session_id . '_' . date('Y_m');
                $cached = $this->getCachedDashboardPayload($cache_key);
                if (is_array($cached) && isset($cached['total_students'])) {
                    return $cached;
                }

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
                
                // Get student session IDs only (memory-optimized)
                $studentlist = $this->student_model->searchByClassSectionMinimal(null, null);
                
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

                $result = array(
                    'total_students' => $total_students,
                    'total_amount' => $total_amount,
                    'total_charges' => $total_charges
                );
                $this->setCachedDashboardPayload($cache_key, $result);
                return $result;
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

