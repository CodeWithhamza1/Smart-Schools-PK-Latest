<?php



if (!defined('BASEPATH')) {

    exit('No direct script access allowed');
}



class Studentfee extends Admin_Controller

{



    public function __construct()

    {

        parent::__construct();

        $this->load->library('smsgateway');

        $this->load->library('mailsmsconf');

        $this->search_type        = $this->config->item('search_type');

        $this->sch_setting_detail = $this->setting_model->getSetting();
    }



    public function index()

    {

        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {

            access_denied();
        }



        $this->session->set_userdata('top_menu', $this->lang->line('fees_collection'));

        $this->session->set_userdata('sub_menu', 'studentfee/index');

        $data['sch_setting'] = $this->sch_setting_detail;

        $data['title']       = 'student fees';

        $class               = $this->class_model->get();

        $data['classlist']   = $class;

        $this->load->view('layout/header', $data);

        $this->load->view('studentfee/studentfeeSearch', $data);

        $this->load->view('layout/footer', $data);
    }



    public function collection_report()

    {

        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {

            access_denied();
        }



        $data['collect_by'] = $this->studentfeemaster_model->get_feesreceived_by();

        $data['searchlist'] = $this->customlib->get_searchtype();

        $data['group_by']   = $this->customlib->get_groupby();

        $feetype = $this->feetype_model->get();

        $data['feetypeList'] = $feetype;

        $this->session->set_userdata('top_menu', 'Reports');

        $this->session->set_userdata('sub_menu', 'Reports/finance');

        $this->session->set_userdata('subsub_menu', 'Reports/finance/collection_report');

        $subtotal = false;

        if (isset($_POST['search_type']) && $_POST['search_type'] != '') {

            $dates               = $this->customlib->get_betweendate($_POST['search_type']);

            $data['search_type'] = $_POST['search_type'];
        } else {

            $dates               = $this->customlib->get_betweendate('this_year');

            $data['search_type'] = '';
        }



        if (isset($_POST['collect_by']) && $_POST['collect_by'] != '') {

            $data['received_by'] = $received_by = $_POST['collect_by'];
        } else {

            $data['received_by'] = $received_by = '';
        }



        if (isset($_POST['feetype_id']) && $_POST['feetype_id'] != '') {

            $feetype_id = $_POST['feetype_id'];
        } else {

            $feetype_id = "";
        }



        if (isset($_POST['group']) && $_POST['group'] != '') {

            $data['group_byid'] = $group = $_POST['group'];

            $subtotal = true;
        } else {

            $data['group_byid'] = $group = '';
        }



        $collect_by          = array();

        $collection          = array();

        $start_date          = date('Y-m-d', strtotime($dates['from_date']));

        $end_date            = date('Y-m-d', strtotime($dates['to_date']));





        $this->form_validation->set_rules('search_type', $this->lang->line('search') . " " . $this->lang->line('type'), 'trim|required|xss_clean');





        if ($this->form_validation->run() == false) {

            $data['results'] = array();
        } else {

            $data['results'] = $this->studentfeemaster_model->getFeeCollectionReport($start_date, $end_date, $feetype_id, $received_by, $group);



            if ($group != '') {



                if ($group == 'class') {



                    $group_by = 'class_id';
                } elseif ($group == 'collection') {



                    $group_by = 'received_by';
                } elseif ($group == 'mode') {



                    $group_by = 'payment_mode';
                }



                foreach ($data['results'] as $key => $value) {



                    $collection[$value[$group_by]][] = $value;
                }
            } else {



                $s = 0;

                foreach ($data['results'] as $key => $value) {



                    $collection[$s++] = array($value);
                }
            }



            $data['results'] = $collection;
        }

        $data['subtotal'] = $subtotal;

        $data['sch_setting'] = $this->sch_setting_detail;

        $this->load->view('layout/header', $data);

        $this->load->view('studentfee/collection_report', $data);

        $this->load->view('layout/footer', $data);
    }



    public function collection_report_dashboard()

    {



        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {

            access_denied();
        }



        $data['collect_by'] = $this->studentfeemaster_model->get_feesreceived_by();



        $data['searchlist'] = $this->customlib->get_searchtype();

        $data['group_by']   = $this->customlib->get_groupby();



        $this->session->set_userdata('top_menu', 'Reports');

        $this->session->set_userdata('sub_menu', 'Reports/finance');

        $this->session->set_userdata('subsub_menu', 'Reports/finance/collection_report');





        $dates               = $this->customlib->get_betweendate('this_month');

        $data['search_type'] = "this_month";



        $data['received_by'] = $received_by = '';



        $data['group_byid'] = $group = "class";





        $collect_by          = array();

        $collection          = array();

        $start_date          = date('Y-m-d', strtotime($dates['from_date']));

        $end_date            = date('Y-m-d', strtotime($dates['to_date']));

        $data['collectlist'] = $this->studentfeemaster_model->getFeeCollectionReport($start_date, $end_date);

        $this->form_validation->set_rules('search_type', $this->lang->line('search') . " " . $this->lang->line('type'), 'trim|required|xss_clean');

        $this->form_validation->set_rules('collect_by', $this->lang->line('collect') . " " . $this->lang->line('by'), 'trim|required|xss_clean');

        $this->form_validation->set_rules('group', $this->lang->line('group') . " " . $this->lang->line('by'), 'trim|required|xss_clean');



        /*if ($this->form_validation->run() == false) {



            $data['results'] = array();



        } else {*/



        $data['results'] = $this->studentfeemaster_model->getFeeCollectionReport($start_date, $end_date, "", $received_by, $group);



        if ($group != '') {



            if ($group == 'class') {



                $group_by = 'class_id';
            } elseif ($group == 'collection') {



                $group_by = 'received_by';
            } elseif ($group == 'mode') {



                $group_by = 'payment_mode';
            }



            foreach ($data['results'] as $key => $value) {



                $collection[$value[$group_by]][] = $value;
            }
        } else {



            $s = 0;

            foreach ($data['results'] as $key => $value) {



                $collection[$s++] = array($value);
            }
        }



        $data['results'] = $collection;



        //        }



        $this->load->view('layout/header', $data);

        $this->load->view('studentfee/collection_report_dashboard', $data);

        $this->load->view('layout/footer', $data);
    }



    public function pdf()

    {

        $this->load->helper('pdf_helper');
    }



    public function search()

    {

        $search_type = $this->input->post('search_type');

        if ($search_type == "class_search") {

            $this->form_validation->set_rules('class_id', $this->lang->line('class'), 'required|trim|xss_clean');
        } elseif ($search_type == "keyword_search") {

            $this->form_validation->set_rules('search_text', 'keyword --r', 'required|trim|xss_clean');

            $data = array('search_text' => 'dummy');



            $this->form_validation->set_data($data);
        }

        if ($this->form_validation->run() == false) {

            $error = array();

            if ($search_type == "class_search") {

                $error['class_id'] = form_error('class_id');
            } elseif ($search_type == "keyword_search") {

                $error['search_text'] = form_error('search_text');
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



    public function ajaxSearch()

    {

        $class       = $this->input->post('class_id');

        $section     = $this->input->post('section_id');

        $search_text = $this->input->post('search_text');

        $search_type = $this->input->post('search_type');

        if ($search_type == "class_search") {

            $students = $this->student_model->getDatatableByClassSection($class, $section);
        } elseif ($search_type == "keyword_search") {

            $students = $this->student_model->getDatatableByFullTextSearch($search_text);
        }

        $sch_setting = $this->sch_setting_detail;

        $students = json_decode($students);

        $dt_data  = array();

        if (!empty($students->data)) {

            foreach ($students->data as $student_key => $student) {

                $row   = array();

                $row[] = $student->class;

                $row[] = $student->section;

                $row[] = $student->admission_no;

                $row[] = "<a href='" . base_url() . "student/view/" . $student->id . "'>" . $this->customlib->getFullName($student->firstname, $student->middlename, $student->lastname, $sch_setting->middlename, $sch_setting->lastname) . "</a>";

                $row[] = $student->father_name;

                $row[] = $this->customlib->dateformat($student->dob);

                $row[] = $student->guardian_phone;

                $row[] = "<a href=" . site_url('studentfee/addfee/' . $student->student_session_id) . "  class='btn btn-info btn-xs'>" . $this->lang->line('collect_fees') . "</a>";



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



    public function feesearch()

    {

        if (!$this->rbac->hasPrivilege('search_due_fees', 'can_view')) {

            access_denied();
        }



        $this->session->set_userdata('top_menu', 'Fees Collection');

        $this->session->set_userdata('sub_menu', 'studentfee/feesearch');

        $data['title']       = 'student fees';

        $class               = $this->class_model->get();

        $data['classlist']   = $class;

        $data['sch_setting'] = $this->sch_setting_detail;

        $feesessiongroup     = $this->feesessiongroup_model->getFeesByGroup();



        $data['feesessiongrouplist'] = $feesessiongroup;

        $data['fees_group']          = "";

        if (isset($_POST['feegroup_id']) && $_POST['feegroup_id'] != '') {

            $data['fees_group'] = $_POST['feegroup_id'];
        }



        $this->form_validation->set_rules('feegroup_id', $this->lang->line('fee_group'), 'trim|required|xss_clean');



        if ($this->form_validation->run() == false) {

            $this->load->view('layout/header', $data);

            $this->load->view('studentfee/studentSearchFee', $data);

            $this->load->view('layout/footer', $data);
        } else {

            $data['student_due_fee'] = array();

            $feegroup_id             = $this->input->post('feegroup_id');

            $feegroup                = explode("-", $feegroup_id);

            $feegroup_id             = $feegroup[0];

            $fee_groups_feetype_id   = $feegroup[1];

            $class_id                = $this->input->post('class_id');

            $section_id              = $this->input->post('section_id');

            $student_due_fee         = $this->studentfee_model->getDueStudentFees($feegroup_id, $fee_groups_feetype_id, $class_id, $section_id);

            if (!empty($student_due_fee)) {

                foreach ($student_due_fee as $student_due_fee_key => $student_due_fee_value) {

                    $amt_due                                                  = $student_due_fee_value['amount'];

                    $student_due_fee[$student_due_fee_key]['amount_discount'] = 0;

                    $student_due_fee[$student_due_fee_key]['amount_fine']     = 0;

                    $a                                                        = json_decode($student_due_fee_value['amount_detail']);

                    if (!empty($a)) {

                        $amount          = 0;

                        $amount_discount = 0;

                        $amount_fine     = 0;



                        foreach ($a as $a_key => $a_value) {

                            $amount          = $amount + $a_value->amount;

                            $amount_discount = $amount_discount + $a_value->amount_discount;

                            $amount_fine     = $amount_fine + $a_value->amount_fine;
                        }

                        if ($amt_due <= $amount) {

                            unset($student_due_fee[$student_due_fee_key]);
                        } else {



                            $student_due_fee[$student_due_fee_key]['amount_detail']   = $amount;

                            $student_due_fee[$student_due_fee_key]['amount_discount'] = $amount_discount;

                            $student_due_fee[$student_due_fee_key]['amount_fine']     = $amount_fine;
                        }
                    }
                }
            }



            $data['student_due_fee'] = $student_due_fee;

            $this->load->view('layout/header', $data);

            $this->load->view('studentfee/studentSearchFee', $data);

            $this->load->view('layout/footer', $data);
        }
    }



    public function reportbyname()

    {

        if (!$this->rbac->hasPrivilege('fees_statement', 'can_view')) {

            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');

        $this->session->set_userdata('sub_menu', 'Reports/finance');

        $this->session->set_userdata('subsub_menu', 'Reports/finance/reportbyname');

        $data['title']       = 'student fees';

        $data['title']       = 'student fees';

        $class               = $this->class_model->get();

        $data['classlist']   = $class;

        $data['sch_setting'] = $this->sch_setting_detail;



        if ($this->input->server('REQUEST_METHOD') == "GET") {



            $this->load->view('layout/header', $data);

            $this->load->view('studentfee/reportByName', $data);

            $this->load->view('layout/footer', $data);
        } else { {



                $data['student_due_fee'] = array();

                $class_id                = $this->input->post('class_id');

                $section_id              = $this->input->post('section_id');

                $student_id              = $this->input->post('student_id');

                $student_due_fee              = $this->studentfeemaster_model->getStudentFeesByClassSectionStudent($class_id, $section_id, $student_id);

                $data['student_due_fee']      = $student_due_fee;

                $data['class_id']             = $class_id;

                $data['section_id']           = $section_id;

                $data['student_id']           = $student_id;

                $category                     = $this->category_model->get();

                $data['categorylist']         = $category;

                $this->load->view('layout/header', $data);

                $this->load->view('studentfee/reportByName', $data);

                $this->load->view('layout/footer', $data);
            }
        }
    }



    public function reportbyclass()

    {

        $data['title']     = 'student fees';

        $data['title']     = 'student fees';

        $class             = $this->class_model->get();

        $data['classlist'] = $class;

        if ($this->input->server('REQUEST_METHOD') == "GET") {

            $this->load->view('layout/header', $data);

            $this->load->view('studentfee/reportByClass', $data);

            $this->load->view('layout/footer', $data);
        } else {

            $student_fees_array      = array();

            $class_id                = $this->input->post('class_id');

            $section_id              = $this->input->post('section_id');

            $student_result          = $this->student_model->searchByClassSection($class_id, $section_id);

            $data['student_due_fee'] = array();

            if (!empty($student_result)) {

                foreach ($student_result as $key => $student) {

                    $student_array                      = array();

                    $student_array['student_detail']    = $student;

                    $student_session_id                 = $student['student_session_id'];

                    $student_id                         = $student['id'];

                    $student_due_fee                    = $this->studentfee_model->getDueFeeBystudentSection($class_id, $section_id, $student_session_id);

                    $student_array['fee_detail']        = $student_due_fee;

                    $student_fees_array[$student['id']] = $student_array;
                }
            }

            $data['class_id']           = $class_id;

            $data['section_id']         = $section_id;

            $data['student_fees_array'] = $student_fees_array;

            $this->load->view('layout/header', $data);

            $this->load->view('studentfee/reportByClass', $data);

            $this->load->view('layout/footer', $data);
        }
    }



    public function view($id)

    {

        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {

            access_denied();
        }

        $data['title']      = 'studentfee List';

        $studentfee         = $this->studentfee_model->get($id);

        $data['studentfee'] = $studentfee;

        $this->load->view('layout/header', $data);

        $this->load->view('studentfee/studentfeeShow', $data);

        $this->load->view('layout/footer', $data);
    }



    public function deleteFee()

    {



        if (!$this->rbac->hasPrivilege('collect_fees', 'can_delete')) {

            access_denied();
        }

        $invoice_id  = $this->input->post('main_invoice');

        $sub_invoice = $this->input->post('sub_invoice');

        if (!empty($invoice_id)) {

            $this->studentfee_model->remove($invoice_id, $sub_invoice);
        }

        $array = array('status' => 'success', 'result' => 'success');

        echo json_encode($array);
    }



    public function deleteStudentDiscount()

    {



        $discount_id = $this->input->post('discount_id');

        if (!empty($discount_id)) {

            $data = array('id' => $discount_id, 'status' => 'assigned', 'payment_id' => "");

            $this->feediscount_model->updateStudentDiscount($data);
        }

        $array = array('status' => 'success', 'result' => 'success');

        echo json_encode($array);
    }



    public function getcollectfee_new()

    {

        $setting_result      = $this->setting_model->get();

        $data['settinglist'] = $setting_result;

        $record              = $this->input->post('data');

        $record_array        = json_decode($record);



        $fees_array = array();

        foreach ($record_array as $key => $value) {

            $fee_groups_feetype_id = $value->fee_groups_feetype_id;

            $fee_master_id         = $value->fee_master_id;

            $fee_session_group_id  = $value->fee_session_group_id;

            $feeList               = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);

            $fees_array[]          = $feeList;
        }

        $data['feearray'] = $fees_array;

        $result           = array(

            'view' => $this->load->view('studentfee/getcollectfee', $data, true),

        );



        $this->output->set_output(json_encode($result));
    }



    public function getcollectfee()

    {

        $setting_result      = $this->setting_model->get();

        $data['settinglist'] = $setting_result;

        $record              = $this->input->post('data');

        $record_array        = json_decode($record);



        $fees_array = array();

        foreach ($record_array as $key => $value) {

            $fee_groups_feetype_id = $value->fee_groups_feetype_id;

            $fee_master_id         = $value->fee_master_id;

            $student_session_id  = $value->student_session_id;

            $fee_session_group_id  = $value->fee_session_group_id;

            $feeList               = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);

            $fees_array[]          = $feeList;
        }

        $discount_applied = $this->studentfeemaster_model->getAppliedDicount($student_session_id);

        $data['discount_applied_ids'] = $discount_applied;

        $data['student_session_id'] = $student_session_id;

        $data['discount_not_applied'] = $this->getNotAppliedDiscount($student_session_id);

        $data['feearray'] = $fees_array;

        $result           = array(

            'view' => $this->load->view('studentfee/getcollectfee', $data, true),

        );



        $this->output->set_output(json_encode($result));
    }







    public function addfee($id)

    {

        if (!$this->rbac->hasPrivilege('collect_fees', 'can_add')) {

            access_denied();
        }

        $data['sch_setting']  = $this->sch_setting_detail;

        $data['title']        = 'Student Detail';

        $student              = $this->student_model->getByStudentSession($id);

        $data['student']      = $student;

        $student_due_fee      = $this->studentfeemaster_model->getStudentFees($id);

        $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($id);



        $data['student_discount_fee'] = $student_discount_fee;

        $data['student_due_fee']      = $student_due_fee;

        $category                     = $this->category_model->get();

        $data['categorylist']         = $category;

        $class_section                = $this->student_model->getClassSection($student["class_id"]);

        $data["class_section"]        = $class_section;

        $session                      = $this->setting_model->getCurrentSession();

        $studentlistbysection         = $this->student_model->getStudentClassSection($student["class_id"], $session);

        $data["studentlistbysection"] = $studentlistbysection;



        $this->load->view('layout/header', $data);

        $this->load->view('studentfee/studentAddfee', $data);

        $this->load->view('layout/footer', $data);
    }



    public function deleteTransportFee()

    {

        $id = $this->input->post('feeid');

        $this->studenttransportfee_model->remove($id);

        $array = array('status' => 'success', 'result' => 'success');

        echo json_encode($array);
    }



    public function delete($id)

    {

        $data['title'] = 'studentfee List';

        $this->studentfee_model->remove($id);

        redirect('studentfee/index');
    }



    public function create()

    {

        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {

            access_denied();
        }

        $data['title'] = 'Add studentfee';

        $this->form_validation->set_rules('category', $this->lang->line('category'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {

            $this->load->view('layout/header', $data);

            $this->load->view('studentfee/studentfeeCreate', $data);

            $this->load->view('layout/footer', $data);
        } else {

            $data = array(

                'category' => $this->input->post('category'),

            );

            $this->studentfee_model->add($data);

            $this->session->set_flashdata('msg', '<div studentfee="alert alert-success text-center">' . $this->lang->line('success_message') . '</div>');

            redirect('studentfee/index');
        }
    }



    public function edit($id)

    {

        if (!$this->rbac->hasPrivilege('collect_fees', 'can_edit')) {

            access_denied();
        }

        $data['title']      = 'Edit studentfees';

        $data['id']         = $id;

        $studentfee         = $this->studentfee_model->get($id);

        $data['studentfee'] = $studentfee;

        $this->form_validation->set_rules('category', $this->lang->line('category'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {

            $this->load->view('layout/header', $data);

            $this->load->view('studentfee/studentfeeEdit', $data);

            $this->load->view('layout/footer', $data);
        } else {

            $data = array(

                'id'       => $id,

                'category' => $this->input->post('category'),

            );

            $this->studentfee_model->add($data);

            $this->session->set_flashdata('msg', '<div studentfee="alert alert-success text-center">' . $this->lang->line('update_message') . '</div>');

            redirect('studentfee/index');
        }
    }



    public function addstudentfee()

    {



        $this->form_validation->set_rules('student_fees_master_id', $this->lang->line('fee_master'), 'required|trim|xss_clean');

        $this->form_validation->set_rules('fee_groups_feetype_id', $this->lang->line('student'), 'required|trim|xss_clean');

        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|trim|xss_clean|numeric|callback_check_deposit');

        $this->form_validation->set_rules('amount_discount', $this->lang->line('discount'), 'required|numeric|trim|xss_clean');

        $this->form_validation->set_rules('amount_fine', $this->lang->line('fine'), 'required|trim|numeric|xss_clean');

        $this->form_validation->set_rules('payment_mode', $this->lang->line('payment_mode'), 'required|trim|xss_clean');



        if ($this->form_validation->run() == false) {

            $data = array(

                'amount'                 => form_error('amount'),

                'student_fees_master_id' => form_error('student_fees_master_id'),

                'fee_groups_feetype_id'  => form_error('fee_groups_feetype_id'),

                'amount_discount'        => form_error('amount_discount'),

                'amount_fine'            => form_error('amount_fine'),

                'payment_mode'           => form_error('payment_mode'),

            );

            $array = array('status' => 'fail', 'error' => $data);

            echo json_encode($array);
        } else {

            $staff_record = $this->staff_model->get($this->customlib->getStaffID());



            $collected_by             = $this->customlib->getAdminSessionUserName() . "(" . $staff_record['employee_id'] . ")";

            $student_fees_discount_id = $this->input->post('student_fees_discount_id');

            $json_array               = array(

                'amount'          => $this->input->post('amount'),

                'date'            => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('date'))),

                'amount_discount' => $this->input->post('amount_discount'),

                'amount_fine'     => $this->input->post('amount_fine'),

                'description'     => $this->input->post('description'),

                'collected_by'    => $collected_by,

                'payment_mode'    => $this->input->post('payment_mode'),

                'received_by'     => $staff_record['id'],

            );

            $data = array(

                'student_fees_master_id' => $this->input->post('student_fees_master_id'),

                'fee_groups_feetype_id'  => $this->input->post('fee_groups_feetype_id'),

                'amount_detail'          => $json_array,

            );



            $action             = $this->input->post('action');

            $send_to            = $this->input->post('guardian_phone');

            $email              = $this->input->post('guardian_email');

            $parent_app_key     = $this->input->post('parent_app_key');

            $student_session_id = $this->input->post('student_session_id');

            $inserted_id        = $this->studentfeemaster_model->fee_deposit($data, $send_to, $student_fees_discount_id);

            $mailsms_array      = $this->feegrouptype_model->getFeeGroupByID($this->input->post('fee_groups_feetype_id'));

            $print_record       = array();

            if ($action == "print") {

                $receipt_data           = json_decode($inserted_id);

                $data['sch_setting']    = $this->sch_setting_detail;

                $fee_record             = $this->studentfeemaster_model->getFeeByInvoice($receipt_data->invoice_id, $receipt_data->sub_invoice_id);

                $student                = $this->studentsession_model->searchStudentsBySession($student_session_id);

                $data['student']        = $student;

                $data['sub_invoice_id'] = $receipt_data->sub_invoice_id;

                $data['feeList']        = $fee_record;

                $print_record           = $this->load->view('print/printFeesByName', $data, true);
            }

            $mailsms_array->invoice        = $inserted_id;

            $mailsms_array->contact_no     = $send_to;

            $mailsms_array->email          = $email;

            $mailsms_array->parent_app_key = $parent_app_key;



            $this->mailsmsconf->mailsms('fee_submission', $mailsms_array);



            $array = array('status' => 'success', 'error' => '', 'print' => $print_record);

            echo json_encode($array);
        }
    }



    public function printFeesByName()

    {

        $data                   = array('payment' => "0");

        $record                 = $this->input->post('data');

        $invoice_id             = $this->input->post('main_invoice');

        $sub_invoice_id         = $this->input->post('sub_invoice');

        $student_session_id     = $this->input->post('student_session_id');

        $setting_result         = $this->setting_model->get();

        $data['settinglist']    = $setting_result;

        $student                = $this->studentsession_model->searchStudentsBySession($student_session_id);

        $fee_record             = $this->studentfeemaster_model->getFeeByInvoice($invoice_id, $sub_invoice_id);

        $data['student']        = $student;

        $data['sub_invoice_id'] = $sub_invoice_id;

        $data['feeList']        = $fee_record;

        $data['sch_setting']    = $this->sch_setting_detail;

        $this->load->view('print/printFeesByName', $data);
    }



    public function printFeesByGroup()

    {

        $fee_groups_feetype_id = $this->input->post('fee_groups_feetype_id');

        $fee_master_id         = $this->input->post('fee_master_id');

        $fee_session_group_id  = $this->input->post('fee_session_group_id');

        $setting_result        = $this->setting_model->get();

        $data['settinglist']   = $setting_result;

        $fee_list = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);

        $data['feeList']       = $fee_list;
        $data['feearray']      = empty($fee_list) ? array() : array($fee_list);

        $data['sch_setting']   = $this->sch_setting_detail;

        $this->load->view('print/printFeesByGroupArray', $data);
    }



    public function printFeesByGroupArray()

    {

        $data['sch_setting'] = $this->sch_setting_detail;

        //        print_r($data['sch_setting']); die();

        $record              = $this->input->post('data');

        $record_array        = json_decode($record);

        $fees_array          = array();

        foreach ($record_array as $key => $value) {

            $fee_groups_feetype_id = $value->fee_groups_feetype_id;

            $fee_master_id         = $value->fee_master_id;

            $fee_session_group_id  = $value->fee_session_group_id;

            $feeList               = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);

            $fees_array[]          = $feeList;
        }

        $data['feearray'] = $fees_array;

        $this->load->view('print/printFeesByGroupArray', $data);
    }



    public function printFeesByGroupArray2()

    {

        $data['sch_setting'] = $this->sch_setting_detail;

        //        print_r($data['sch_setting']); die();

        $record              = $this->input->post('data');

        $record_array        = json_decode($record);

        $fees_array          = array();

        foreach ($record_array as $key => $value) {

            $fee_groups_feetype_id = $value->fee_groups_feetype_id;

            $fee_master_id         = $value->fee_master_id;

            $fee_session_group_id  = $value->fee_session_group_id;

            $feeList               = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);

            $fees_array[]          = $feeList;
        }

        $data['feearray'] = $fees_array;

        $this->load->view('print/printFeesByGroupArray2', $data);
    }



    public function searchpayment()

    {

        if (!$this->rbac->hasPrivilege('search_fees_payment', 'can_view')) {

            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Fees Collection');

        $this->session->set_userdata('sub_menu', 'studentfee/searchpayment');

        $data['title'] = 'Edit studentfees';



        $this->form_validation->set_rules('paymentid', $this->lang->line('payment_id'), 'trim|required|xss_clean');

        if ($this->form_validation->run() == false) {
        } else {

            $paymentid = $this->input->post('paymentid');

            $invoice   = explode("/", $paymentid);



            if (array_key_exists(0, $invoice) && array_key_exists(1, $invoice)) {

                $invoice_id             = $invoice[0];

                $sub_invoice_id         = $invoice[1];

                $feeList                = $this->studentfeemaster_model->getFeeByInvoice($invoice_id, $sub_invoice_id);

                $data['feeList']        = $feeList;

                $data['sub_invoice_id'] = $sub_invoice_id;
            } else {

                $data['feeList'] = array();
            }
        }

        $data['sch_setting'] = $this->sch_setting_detail;

        $this->load->view('layout/header', $data);

        $this->load->view('studentfee/searchpayment', $data);

        $this->load->view('layout/footer', $data);
    }



    public function addfeegroup()

    {

        $this->form_validation->set_rules('fee_session_groups', $this->lang->line('fee_group'), 'required|trim|xss_clean');



        if ($this->form_validation->run() == false) {

            $data = array(

                'fee_session_groups' => form_error('fee_session_groups'),

            );

            $array = array('status' => 'fail', 'error' => $data);

            echo json_encode($array);
        } else {

            $student_session_id     = $this->input->post('student_session_id');

            $fee_session_groups     = $this->input->post('fee_session_groups');

            $student_sesssion_array = isset($student_session_id) ? $student_session_id : array();

            $student_ids            = $this->input->post('student_ids');

            $delete_student         = array_diff($student_ids, $student_sesssion_array);



            $preserve_record = array();

            if (!empty($student_sesssion_array)) {

                foreach ($student_sesssion_array as $key => $value) {

                    $insert_array = array(

                        'student_session_id'   => $value,

                        'fee_session_group_id' => $fee_session_groups,

                    );

                    $inserted_id = $this->studentfeemaster_model->add($insert_array);



                    $preserve_record[] = $inserted_id;
                }
            }

            if (!empty($delete_student)) {

                $this->studentfeemaster_model->delete($fee_session_groups, $delete_student);
            }



            $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));

            echo json_encode($array);
        }
    }



    public function geBalanceFee()

    {

        $this->form_validation->set_rules('fee_groups_feetype_id', $this->lang->line('fee_groups_feetype_id'), 'required|trim|xss_clean');

        $this->form_validation->set_rules('student_fees_master_id', 'student_fees_master_id', 'required|trim|xss_clean');

        $this->form_validation->set_rules('student_session_id', 'student_session_id', 'required|trim|xss_clean');



        if ($this->form_validation->run() == false) {

            $data = array(

                'fee_groups_feetype_id'  => form_error('fee_groups_feetype_id'),

                'student_fees_master_id' => form_error('student_fees_master_id'),

            );

            $array = array('status' => 'fail', 'error' => $data);

            echo json_encode($array);
        } else {

            $data                   = array();

            $student_session_id     = $this->input->post('student_session_id');

            $fee_groups_feetype_id  = $this->input->post('fee_groups_feetype_id');

            $student_fees_master_id = $this->input->post('student_fees_master_id');

            $remain_amount_object   = $this->getStuFeetypeBalance($fee_groups_feetype_id, $student_fees_master_id);

            $discount_not_applied   = $this->getNotAppliedDiscount($student_session_id);

            $remain_amount          = json_decode($remain_amount_object)->balance;

            $remain_amount_fine     = json_decode($remain_amount_object)->fine_amount;



            $array = array('status' => 'success', 'error' => '', 'balance' => $remain_amount, 'discount_not_applied' => $discount_not_applied, 'remain_amount_fine' => $remain_amount_fine);

            echo json_encode($array);
        }
    }



    public function getStuFeetypeBalance($fee_groups_feetype_id, $student_fees_master_id)

    {

        $data                           = array();

        $data['fee_groups_feetype_id']  = $fee_groups_feetype_id;

        $data['student_fees_master_id'] = $student_fees_master_id;

        $result                         = $this->studentfeemaster_model->studentDeposit($data);

        $amount_balance                 = 0;

        $amount                         = 0;

        $amount_fine                    = 0;

        $amount_discount                = 0;

        $fine_amount                    = 0;

        $fee_fine_amount                = 0;

        $due_amt                        = $result->amount;

        if (strtotime($result->due_date) < strtotime(date('Y-m-d'))) {

            $fee_fine_amount = $result->fine_amount;
        }



        if ($result->is_system) {

            $due_amt = $result->student_fees_master_amount;
        }



        $amount_detail = json_decode($result->amount_detail);

        if (is_object($amount_detail)) {



            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                $amount          = $amount + $amount_detail_value->amount;

                $amount_discount = $amount_discount + $amount_detail_value->amount_discount;

                $amount_fine     = $amount_fine + $amount_detail_value->amount_fine;
            }
        }



        $amount_balance = $due_amt - ($amount + $amount_discount);

        $fine_amount    = abs($amount_fine - $fee_fine_amount);

        $array          = array('status' => 'success', 'error' => '', 'balance' => $amount_balance, 'fine_amount' => $fine_amount);

        return json_encode($array);
    }



    public function check_deposit($amount)

    {

        if (is_numeric($this->input->post('amount')) && is_numeric($this->input->post('amount_discount'))) {

            if ($this->input->post('amount') != "" && $this->input->post('amount_discount') != "") {

                if ($this->input->post('amount') < 0) {

                    $this->form_validation->set_message('check_deposit', $this->lang->line('deposit_amount_can_not_be_less_than_zero'));

                    return false;
                } else {

                    $student_fees_master_id = $this->input->post('student_fees_master_id');

                    $fee_groups_feetype_id  = $this->input->post('fee_groups_feetype_id');

                    $deposit_amount         = $this->input->post('amount') + $this->input->post('amount_discount');

                    $remain_amount          = $this->getStuFeetypeBalance($fee_groups_feetype_id, $student_fees_master_id);

                    $remain_amount          = json_decode($remain_amount)->balance;

                    if ($remain_amount < $deposit_amount) {

                        $this->form_validation->set_message('check_deposit', $this->lang->line('deposit_amount_can_not_be_greater_than_remaining'));

                        return false;
                    } else {

                        return true;
                    }
                }

                return true;
            }
        } elseif (!is_numeric($this->input->post('amount'))) {



            $this->form_validation->set_message('check_deposit', $this->lang->line('amount') . " " . $this->lang->line('field_must_contain_only_numbers'));



            return false;
        } elseif (!is_numeric($this->input->post('amount_discount'))) {



            return true;
        }



        return true;
    }



    public function getNotAppliedDiscount($student_session_id)

    {

        return $this->feediscount_model->getDiscountNotApplied($student_session_id);
    }



    public function addfeegrp()

    {

        $staff_record = $this->session->userdata('admin');

        $this->form_validation->set_error_delimiters('', '');

        $this->form_validation->set_rules('row_counter[]', $this->lang->line('fees_list'), 'required|trim|xss_clean');

        $this->form_validation->set_rules('collected_date', $this->lang->line('date'), 'required|trim|xss_clean');



        if ($this->form_validation->run() == false) {

            $data = array(

                'row_counter'    => form_error('row_counter'),

                'collected_date' => form_error('collected_date'),

            );

            $array = array('status' => 0, 'error' => $data);

            echo json_encode($array);
        } else {

            $collected_array = array();

            $collected_by    = " Collected By: " . $this->customlib->getAdminSessionUserName();



            $send_to            = $this->input->post('guardian_phone');

            $email              = $this->input->post('guardian_email');

            $parent_app_key     = $this->input->post('parent_app_key');

            $student_session_id = $this->input->post('student_session_id');

            $total_row = $this->input->post('row_counter');

            foreach ($total_row as $total_row_key => $total_row_value) {



                $this->input->post('student_fees_master_id_' . $total_row_value);

                $this->input->post('fee_groups_feetype_id_' . $total_row_value);



                $json_array = array(

                    'amount'          => $this->input->post('fee_amount_' . $total_row_value),

                    'date'            => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('collected_date'))),

                    'description'     => $this->input->post('fee_gupcollected_note') . $collected_by,

                    'amount_discount' => 0,

                    'amount_fine'     => $this->input->post('fee_groups_feetype_fine_amount_' . $total_row_value),

                    'payment_mode'    => $this->input->post('payment_mode_fee'),

                    'received_by'     => $staff_record['id'],

                );

                $collected_array[] = array(

                    'student_fees_master_id' => $this->input->post('student_fees_master_id_' . $total_row_value),

                    'fee_groups_feetype_id'  => $this->input->post('fee_groups_feetype_id_' . $total_row_value),

                    'amount_detail'          => $json_array,

                );
            }



            $deposited_fees = $this->studentfeemaster_model->fee_deposit_collections($collected_array);

            $fees_record    = json_decode($deposited_fees);



            foreach ($total_row as $total_row_key => $total_row_value) {

                $mailsms_array                 = $this->feegrouptype_model->getFeeGroupByID($this->input->post('fee_groups_feetype_id_' . $total_row_value));

                $mailsms_array->invoice        = json_encode($fees_record[$total_row_key]);

                $mailsms_array->contact_no     = $send_to;

                $mailsms_array->email          = $email;

                $mailsms_array->parent_app_key = $parent_app_key;

                $this->mailsmsconf->mailsms('fee_submission', $mailsms_array);
            }

            $array = array('status' => 1, 'error' => '');

            echo json_encode($array);
        }
    }

    public function addfeegrp_old()

    {

        $staff_record = $this->session->userdata('admin');

        $this->form_validation->set_error_delimiters('', '');

        $this->form_validation->set_rules('row_counter[]', 'Fees List', 'required|trim|xss_clean');

        $this->form_validation->set_rules('fee_amount_1', 'Amount', 'required|trim|xss_clean|callback_check_deposit');



        $this->form_validation->set_rules('collected_date', 'Date', 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {

            $data = array(

                'amount' => form_error('amount'),

                'row_counter' => form_error('row_counter'),

                'collected_date' => form_error('collected_date'),

            );

            $array = array('status' => 0, 'error' => $data);

            echo json_encode($array);
        } else {

            $collected_array = array();

            $fees_array = array();

            $collected_by = " Collected By: " . $this->customlib->getAdminSessionUserName();

            $total_row = $this->input->post('row_counter');

            $deposit_amount = 0;



            foreach ($total_row as $total_row_key => $total_row_value) {

                $deposit_amount += $this->input->post('fee_amount_' . $total_row_value);



                $this->input->post('student_fees_master_id_' . $total_row_value);

                $this->input->post('fee_groups_feetype_id_' . $total_row_value);

                $fee_amount = $this->input->post('fee_amount_' . $total_row_value);

                $fee_discount = $this->input->post('fee_discount_' . $total_row_value);

                $json_array = array(

                    'amount' => $this->input->post('fee_amount_' . $total_row_value),

                    'date' => date('Y-m-d'),

                    'description' => $this->input->post('fee_gupcollected_note') . $collected_by,

                    'amount_discount' => $this->input->post('fee_discount_' . $total_row_value),

                    'amount_fine' => 0,

                    'payment_mode' => $this->input->post('payment_mode_fee'),

                    'received_by' => $staff_record['id'],

                );

                if ($fee_amount > 0 || $fee_discount > 0) {



                    $collected_array[] = array(

                        'student_fees_discount_id' => $this->input->post('student_fees_discount_id_' . $total_row_value),

                        'student_fees_master_id' => $this->input->post('student_fees_master_id_' . $total_row_value),

                        'fee_groups_feetype_id' => $this->input->post('fee_groups_feetype_id_' . $total_row_value),

                        'amount_detail' => $json_array,

                    );
                }

                $student_fees_master_id = $this->input->post('student_fees_master_id_' . $total_row_value);
            }

            $inserted_id = $this->studentfeemaster_model->fee_deposit_collections($collected_array);

            $array = array('status' => 1, 'error' => '');

            $student_detail = $this->studentfeemaster_model->getStudentDetailByStudentSession($student_fees_master_id);

            $send_to = $student_detail->guardian_phone;

            $student_session_id = $student_detail->student_session_id;

            $email = $student_detail->guardian_email;

            $std_name = $student_detail->firstname . " " . $student_detail->lastname;

            $admission_no = $student_detail->admission_no;

            $invoice_amount = $deposit_amount;

            $sender_details = array('student_session_id' => $student_session_id, 'admission_no' => $admission_no, 'std_name' => $std_name, 'invoice_amount' => $invoice_amount, 'contact_no' => $send_to, 'email' => $email, 'notification_to' => array('parent_app_key' => $student_detail->parent_app_key));

            $this->mailsmsconf->mailsms_grup('fee_submission', $sender_details);

            echo json_encode($array);

            /* foreach ($total_row as $total_row_key => $total_row_value1) {

             $fee_groups_feetype_id = $this->input->post('fee_groups_feetype_id_' . $total_row_value1);

             $fee_master_id = $this->input->post('student_fees_master_id_' . $total_row_value1);

             $fee_session_group_id = $this->input->post('fee_session_group_id' . $total_row_value1);

                 $feeList = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);

                 $fees_array[] = $feeList;

             }

             $setting_result = $this->setting_model->get();

             $data['settinglist'] = $setting_result;

             $data['feearray'] = $fees_array;*/

            //            if($inserted_id) {

            //            $this->printFeesByGroupArray1($total_row);

            //         }

        }
    }



    public function reportdailycollection()

    {

        if (!$this->rbac->hasPrivilege('fees_statement', 'can_view')) {

            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');

        $this->session->set_userdata('sub_menu', 'Reports/finance');

        $this->session->set_userdata('subsub_menu', 'Reports/finance/reportdailycollection');

        $data          = array();

        $data['title'] = 'Daily Collection Report';

        $this->form_validation->set_rules('date_from', $this->lang->line('date_from'), 'trim|required|xss_clean');

        $this->form_validation->set_rules('date_to', $this->lang->line('date_to'), 'trim|required|xss_clean');



        if ($this->form_validation->run() == true) {



            $date_from          = $this->input->post('date_from');

            $date_to            = $this->input->post('date_to');

            $formated_date_from = strtotime($this->customlib->dateFormatToYYYYMMDD($date_from));

            $formated_date_to   = strtotime($this->customlib->dateFormatToYYYYMMDD($date_to));

            $st_fees            = $this->studentfeemaster_model->getCurrentSessionStudentFees();

            $fees_data = array();



            for ($i = $formated_date_from; $i <= $formated_date_to; $i += 86400) {

                $fees_data[$i]['amt'] = 0;

                $fees_data[$i]['count'] = 0;

                $fees_data[$i]['student_fees_deposite_ids'] = array();
            }



            if (!empty($st_fees)) {

                foreach ($st_fees as $fee_key => $fee_value) {

                    if (isJSON($fee_value->amount_detail)) {



                        $fees_details = (json_decode($fee_value->amount_detail));

                        if (!empty($fees_details)) {

                            foreach ($fees_details as $fees_detail_key => $fees_detail_value) {

                                $date = strtotime($fees_detail_value->date);

                                if ($date >= $formated_date_from && $date <= $formated_date_to) {

                                    if (array_key_exists($date, $fees_data)) {

                                        $fees_data[$date]['amt'] += $fees_detail_value->amount + $fees_detail_value->amount_fine;

                                        $fees_data[$date]['count'] += 1;

                                        $fees_data[$date]['student_fees_deposite_ids'][] = $fee_value->student_fees_deposite_id;
                                    } else {

                                        $fees_data[$date]['amt'] = $fees_detail_value->amount + $fees_detail_value->amount_fine;

                                        $fees_data[$date]['count'] = 1;

                                        $fees_data[$date]['student_fees_deposite_ids'][] = $fee_value->student_fees_deposite_id;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $data['fees_data'] = $fees_data;
        }



        $this->load->view('layout/header', $data);

        $this->load->view('reports/reportdailycollection', $data);

        $this->load->view('layout/footer', $data);
    }



    public function reportduefees()

    {

        if (!$this->rbac->hasPrivilege('fees_statement', 'can_view')) {

            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');

        $this->session->set_userdata('sub_menu', 'Reports/finance');

        $this->session->set_userdata('subsub_menu', 'Reports/finance/reportduefees');

        $data                = array();

        $data['title']       = 'student fees';

        $class               = $this->class_model->get();

        $data['classlist']   = $class;

        $data['sch_setting'] = $this->sch_setting_detail;

        if ($this->input->server('REQUEST_METHOD') == "POST") {

            $date               = date('Y-m-d');

            $class_id           = $this->input->post('class_id');

            $section_id         = $this->input->post('section_id');

            $data['class_id']   = $class_id;

            $data['section_id'] = $section_id;

            $fees_dues          = $this->studentfeemaster_model->getStudentDueFeeTypesByDate($date, $class_id, $section_id);

            $students_list      = array();



            if (!empty($fees_dues)) {

                foreach ($fees_dues as $fee_due_key => $fee_due_value) {

                    $amount_paid = 0;



                    if (isJSON($fee_due_value->amount_detail)) {

                        $student_fees_array = json_decode($fee_due_value->amount_detail);

                        foreach ($student_fees_array as $fee_paid_key => $fee_paid_value) {

                            $amount_paid += ($fee_paid_value->amount + $fee_paid_value->amount_discount);
                        }
                    }

                    if ($amount_paid < $fee_due_value->fee_amount) {

                        $students_list[$fee_due_value->student_session_id]['admission_no']             = $fee_due_value->admission_no;

                        $students_list[$fee_due_value->student_session_id]['roll_no']                  = $fee_due_value->roll_no;

                        $students_list[$fee_due_value->student_session_id]['admission_date']           = $fee_due_value->admission_date;

                        $students_list[$fee_due_value->student_session_id]['firstname']                = $fee_due_value->firstname;

                        $students_list[$fee_due_value->student_session_id]['middlename']               = $fee_due_value->middlename;

                        $students_list[$fee_due_value->student_session_id]['lastname']                 = $fee_due_value->lastname;

                        $students_list[$fee_due_value->student_session_id]['father_name']              = $fee_due_value->father_name;

                        $students_list[$fee_due_value->student_session_id]['image']                    = $fee_due_value->image;

                        $students_list[$fee_due_value->student_session_id]['mobileno']                 = $fee_due_value->mobileno;

                        $students_list[$fee_due_value->student_session_id]['email']                    = $fee_due_value->email;

                        $students_list[$fee_due_value->student_session_id]['state']                    = $fee_due_value->state;

                        $students_list[$fee_due_value->student_session_id]['city']                     = $fee_due_value->city;

                        $students_list[$fee_due_value->student_session_id]['pincode']                  = $fee_due_value->pincode;

                        $students_list[$fee_due_value->student_session_id]['class']                    = $fee_due_value->class;

                        $students_list[$fee_due_value->student_session_id]['section']                  = $fee_due_value->section;

                        $students_list[$fee_due_value->student_session_id]['fee_groups_feetype_ids'][] = $fee_due_value->fee_groups_feetype_id;
                    }
                }
            }



            if (!empty($students_list)) {

                foreach ($students_list as $student_key => $student_value) {

                    $students_list[$student_key]['fees_list'] = $this->studentfeemaster_model->studentDepositByFeeGroupFeeTypeArray($student_key, $student_value['fee_groups_feetype_ids']);
                }
            }



            $data['student_due_fee'] = $students_list;
        }



        $this->load->view('layout/header', $data);

        $this->load->view('reports/reportduefees', $data);

        $this->load->view('layout/footer', $data);
    }



    public function printreportduefees()

    {



        $data                = array();

        $data['title']       = 'student fees';

        $class               = $this->class_model->get();

        $data['classlist']   = $class;

        $data['sch_setting'] = $this->sch_setting_detail;

        $date                = date('Y-m-d');

        $class_id            = $this->input->post('class_id');

        $section_id          = $this->input->post('section_id');

        $data['class_id']    = $class_id;

        $data['section_id']  = $section_id;

        $fees_dues           = $this->studentfeemaster_model->getStudentDueFeeTypesByDate($date, $class_id, $section_id);

        $students_list       = array();



        if (!empty($fees_dues)) {

            foreach ($fees_dues as $fee_due_key => $fee_due_value) {

                $amount_paid = 0;



                if (isJSON($fee_due_value->amount_detail)) {

                    $student_fees_array = json_decode($fee_due_value->amount_detail);

                    foreach ($student_fees_array as $fee_paid_key => $fee_paid_value) {

                        $amount_paid += ($fee_paid_value->amount + $fee_paid_value->amount_discount);
                    }
                }

                if ($amount_paid < $fee_due_value->fee_amount) {

                    $students_list[$fee_due_value->student_session_id]['admission_no']             = $fee_due_value->admission_no;

                    $students_list[$fee_due_value->student_session_id]['roll_no']                  = $fee_due_value->roll_no;

                    $students_list[$fee_due_value->student_session_id]['admission_date']           = $fee_due_value->admission_date;

                    $students_list[$fee_due_value->student_session_id]['firstname']                = $fee_due_value->firstname;

                    $students_list[$fee_due_value->student_session_id]['middlename']               = $fee_due_value->middlename;

                    $students_list[$fee_due_value->student_session_id]['lastname']                 = $fee_due_value->lastname;

                    $students_list[$fee_due_value->student_session_id]['father_name']              = $fee_due_value->father_name;

                    $students_list[$fee_due_value->student_session_id]['image']                    = $fee_due_value->image;

                    $students_list[$fee_due_value->student_session_id]['mobileno']                 = $fee_due_value->mobileno;

                    $students_list[$fee_due_value->student_session_id]['email']                    = $fee_due_value->email;

                    $students_list[$fee_due_value->student_session_id]['state']                    = $fee_due_value->state;

                    $students_list[$fee_due_value->student_session_id]['city']                     = $fee_due_value->city;

                    $students_list[$fee_due_value->student_session_id]['pincode']                  = $fee_due_value->pincode;

                    $students_list[$fee_due_value->student_session_id]['class']                    = $fee_due_value->class;

                    $students_list[$fee_due_value->student_session_id]['section']                  = $fee_due_value->section;

                    $students_list[$fee_due_value->student_session_id]['fee_groups_feetype_ids'][] = $fee_due_value->fee_groups_feetype_id;
                }
            }
        }



        if (!empty($students_list)) {

            foreach ($students_list as $student_key => $student_value) {

                $students_list[$student_key]['fees_list'] = $this->studentfeemaster_model->studentDepositByFeeGroupFeeTypeArray($student_key, $student_value['fee_groups_feetype_ids']);
            }
        }

        $data['student_due_fee'] = $students_list;

        $page                    = $this->load->view('reports/_printreportduefees', $data, true);

        echo json_encode(array('status' => 1, 'page' => $page));
    }



    public function feeCollectionStudentDeposit()

    {



        $data = array();

        $date = $this->input->post('date');

        $fees_id = $this->input->post('fees_id');

        $fees_id_array = explode(',', $fees_id);

        $fees_list = $this->studentfeemaster_model->getFeesDepositeByIdArray($fees_id_array);

        $data['student_list'] = $fees_list;

        $data['date'] = $date;

        $data['sch_setting']  = $this->sch_setting_detail;

        $page = $this->load->view('reports/_feeCollectionStudentDeposit', $data, true);

        echo json_encode(array('status' => 1, 'page' => $page));
    }



    public function addfeegrp1()

    {

        $staff_record = $this->session->userdata('admin');

        $this->form_validation->set_error_delimiters('', '');

        $this->form_validation->set_rules('row_counter[]', 'Fees List', 'required|trim|xss_clean');

        $this->form_validation->set_rules('collected_date', 'Date', 'required|trim|xss_clean');

        if ($this->form_validation->run() == false) {

            $data = array(

                'row_counter' => form_error('row_counter'),

                'collected_date' => form_error('collected_date'),

            );

            $array = array('status' => 0, 'error' => $data);

            echo json_encode($array);
        } else {

            $collected_array = array();

            $fees_array = array();

            $collected_by = " Collected By: " . $this->customlib->getAdminSessionUserName();

            $total_row = $this->input->post('row_counter');

            $student_session_id = $this->input->post('student_session_id');

            $deposit_amount = 0;



            foreach ($total_row as $total_row_key => $total_row_value) {

                $deposit_amount += $this->input->post('fee_amount_' . $total_row_value);



                $this->input->post('student_fees_master_id_' . $total_row_value);

                $this->input->post('fee_groups_feetype_id_' . $total_row_value);

                $fee_amount = $this->input->post('fee_amount_' . $total_row_value);

                $fee_discount = $this->input->post('fee_discount_' . $total_row_value);

                $json_array = array(

                    'amount' => $this->input->post('fee_amount_' . $total_row_value),

                    'date' => date('Y-m-d'),

                    'description' => $this->input->post('fee_gupcollected_note') . $collected_by,

                    'amount_discount' => $this->input->post('fee_discount_' . $total_row_value),

                    'amount_fine' => 0,

                    'payment_mode' => $this->input->post('payment_mode_fee'),

                    'received_by' => $staff_record['id'],

                );

                if ($fee_amount > 0 || $fee_discount > 0) {



                    $collected_array[] = array(

                        'student_fees_discount_id' => $this->input->post('student_fees_discount_id_' . $total_row_value),

                        'student_fees_master_id' => $this->input->post('student_fees_master_id_' . $total_row_value),

                        'fee_groups_feetype_id' => $this->input->post('fee_groups_feetype_id_' . $total_row_value),

                        'amount_detail' => $json_array,

                    );
                }

                $student_fees_master_id = $this->input->post('student_fees_master_id_' . $total_row_value);
            }

            $inserted_id = $this->studentfeemaster_model->fee_deposit_collections($collected_array);



            $student_detail = $this->studentfeemaster_model->getStudentDetailByStudentSession($student_fees_master_id);

            $send_to = $student_detail->guardian_phone;

            $email = $student_detail->guardian_email;

            $std_name = $student_detail->firstname . " " . $student_detail->lastname;

            $admission_no = $student_detail->admission_no;

            $invoice_amount = $deposit_amount;

            $sender_details = array('student_session_id' => $student_session_id, 'admission_no' => $admission_no, 'std_name' => $std_name, 'invoice_amount' => $invoice_amount, 'contact_no' => $send_to, 'email' => $email, 'notification_to' => array('parent_app_key' => $student_detail->parent_app_key));

            $this->mailsmsconf->mailsms_grup('fee_submission', $sender_details);

            $this->printFeesByGroupArray1($total_row, $student_session_id);
        }
    }



    public function addfeegrp2()

    {



        $staff_record = $this->session->userdata('admin');



        $this->form_validation->set_error_delimiters('', '');

        $this->form_validation->set_rules('row_counter[]', 'Fees List', 'required|trim|xss_clean');

        $this->form_validation->set_rules('collected_date', 'Date', 'required|trim|xss_clean');

        $this->form_validation->set_rules('fee_amount_1', 'Amount', 'required|trim|xss_clean|callback_check_deposit');



        // if ($this->form_validation->run() == false) {

        //     $data = array(

        //         'row_counter'    => form_error('row_counter'),

        //         'collected_date' => form_error('collected_date'),

        //     );

        //     $array = array('status' => 0, 'error' => $data);

        //     echo json_encode($array);

        // } else {

        $collected_array = array();

        $collected_by    = " Collected By: " . $this->customlib->getAdminSessionUserName();



        $total_row = $this->input->post('row_counter');

        $deposit_amount = 0;



        foreach ($total_row as $total_row_key => $total_row_value) {

            $deposit_amount += $this->input->post('fee_amount_' . $total_row_value);



            $this->input->post('student_fees_master_id_' . $total_row_value);

            $this->input->post('fee_groups_feetype_id_' . $total_row_value);

            $fee_amount = $this->input->post('fee_amount_' . $total_row_value);

            $fee_discount = $this->input->post('fee_discount_' . $total_row_value);

            $json_array = array(

                'amount'          => $this->input->post('fee_amount_' . $total_row_value),

                'date'            => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('collected_date'))),

                'description'     => $this->input->post('fee_gupcollected_note') . $collected_by,

                'amount_discount' => $this->input->post('fee_discount_' . $total_row_value),

                'amount_fine'     => 0,

                'payment_mode'    => $this->input->post('payment_mode_fee'),

                'received_by'     => $staff_record['id'],

            );

            if ($fee_amount > 0 || $fee_discount > 0) {



                $collected_array[] = array(

                    'student_fees_discount_id' => $this->input->post('student_fees_discount_id_' . $total_row_value),

                    'student_fees_master_id' => $this->input->post('student_fees_master_id_' . $total_row_value),

                    'fee_groups_feetype_id' => $this->input->post('fee_groups_feetype_id_' . $total_row_value),

                    'amount_detail' => $json_array,

                );
            }

            $student_fees_master_id = $this->input->post('student_fees_master_id_' . $total_row_value);
        }



        $inserted_id = $this->studentfeemaster_model->fee_deposit_collections_old($collected_array);



        /*$student_detail = $this->studentfeemaster_model->getStudentDetailByStudentSession($student_fees_master_id);

            $send_to = $student_detail->guardian_phone;

            $student_session_id = $student_detail->student_session_id;

            $email = $student_detail->guardian_email;

            $std_name = $student_detail->firstname." ".$student_detail->lastname;

            $admission_no = $student_detail->admission_no;

            $invoice_amount=$deposit_amount;

            $sender_details = array('student_session_id' => $student_session_id,'admission_no' => $admission_no,'std_name' => $std_name,'invoice_amount' => $invoice_amount, 'contact_no' => $send_to, 'email' => $email,'notification_to'=>array('parent_app_key'=>$student_detail->parent_app_key));

            $this->mailsmsconf->mailsms_grup('fee_submission', $sender_details);*/

        $array       = array('status' => 1, 'error' => '');

        echo json_encode($array);

        //           return $this->barCodeFeeCollectView();

        // }

    }





    function barCodeFeeCollectView()

    {

        $data['title'] = 'Fee Collection';

        $data['title_list'] = 'Fee Collection';

        $this->session->set_userdata('top_menu', 'Fees Collection');

        $this->session->set_userdata('sub_menu', 'studentfee/barCodeFeeCollectView');

        $this->load->view('layout/header', $data);

        $this->load->view('studentfee/feeCollect');

        $this->load->view('layout/footer', $data);
    }



    function getStudentList()

    {

        $admission_no = $this->input->get('admission_no');

        $resultlist = $this->student_model->getStudentByAdmissionNo($admission_no);

        if ($resultlist) {

            $id = $resultlist[0]['id'];

            echo json_encode($id);
        } else {

            echo json_encode('fail');
        }



        //return route('unreconcil', ['id' => $resultlist[0]['id']]);

    }



    function feeSummaryReportView()

    {

        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {

            access_denied();
        }

        $data['collect_by'] = $this->studentfeemaster_model->get_feesreceived_by();

        $this->session->set_userdata('top_menu', 'Fee Summary Report');

        $this->session->set_userdata('sub_menu', 'studentfee/feeSummaryReportView');

        $data['title'] = 'Fee Summary Report';

        $data['startDate'] = date('Y-m-01');

        $data['endDate'] = date('Y-m-d');

        $feetype = $this->feetype_model->get();

        $data['feetype'] = $feetype;

        //        print_r($data['collect_by']);

        $this->load->view('layout/header', $data);

        $this->load->view('studentfee/feeSummaryReportView', $data);

        $this->load->view('layout/footer', $data);
    }

    function assigned_fee_report_view()

    {

        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {

            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');

        $this->session->set_userdata('sub_menu', 'studentfee/assigned_fee_report_view');

        $data['title'] = 'Assigned Fee Report';



        $class = $this->class_model->get();

        $feegroup = $this->feegroup_model->get();

        $data['feegroupList'] = $feegroup;

        $data['classlist'] = $class;

        $this->load->view('layout/header', $data);

        $this->load->view('studentfee/feeAssignedReportView', $data);

        $this->load->view('layout/footer', $data);
    }

    function assigned_fee_groupwise_report()

    {

        $fee_groups = $this->input->post('fee_groups_id');

        $arr = explode("|", $fee_groups, 2);

        $fee_groups_id = $arr[0];

        $fee_groups_name = $arr[1];



        $data['school'] = $this->setting_model->getCurrentSchoolName();

        $data['session'] = $this->setting_model->getCurrentSessionName();

        $setting_result = $this->setting_model->get();

        $data['settinglist'] = $setting_result;

        $data['fee_groups'] = $fee_groups_name;



        $studentlist = $this->student_model->searchByClassSection();

        $stdArray = array();

        if (!empty($studentlist)) {

            foreach ($studentlist as $key => $eachstudent) {



                $student_session_id = $eachstudent['student_session_id'];

                $student_total_fees = $this->studentfeemaster_model->getStudentAssignedFees($student_session_id, $fee_groups_id);

                if (!empty($student_total_fees)) {

                    $obj = new stdClass();

                    $obj->name = $eachstudent['firstname'] . " " . $eachstudent['lastname'];

                    $obj->class = $eachstudent['class'];

                    $obj->section = $eachstudent['section'];

                    $obj->admission_no = $eachstudent['admission_no'];

                    foreach ($student_total_fees as $key => $eachgroup) {

                        $fee_group = $eachgroup->name;
                    }

                    $obj->student_fees_group = $fee_group;

                    $stdArray[] = $obj;
                }
            }
        }



        usort(

            $stdArray,

            function ($a, $b) {

                $da = $a->section;

                $db = $b->section;

                return $da > $db; // use "<" to reverse sort

            }
        );

        usort(

            $stdArray,

            function ($a, $b) {

                $da = $a->class;

                $db = $b->class;

                return $da > $db; // use "<" to reverse sort

            }
        );

        $data['report'] = $stdArray;

        //        print_r($stdArray );

        $this->load->view('studentfee/feeAssignedGroupWisePDF', $data);
    }

    function assigned_fee_classwise_report()

    {

        $class_id = $this->input->post('class_id');

        $section_id = $this->input->post('section_id');

        $clases = $this->class_model->get($class_id);

        $sections = $this->section_model->get($section_id);

        $data['class'] = $clases['class'];

        $data['section'] = $sections['section'];

        $data['school'] = $this->setting_model->getCurrentSchoolName();

        $data['session'] = $this->setting_model->getCurrentSessionName();

        $setting_result = $this->setting_model->get();

        $data['settinglist'] = $setting_result;

        $studentlist = $this->student_model->searchByClassSection($class_id, $section_id);

        $stdArray = array();

        if (!empty($studentlist)) {

            foreach ($studentlist as $key => $eachstudent) {



                $student_session_id = $eachstudent['student_session_id'];

                $student_total_fees = $this->studentfeemaster_model->getStudentAssignedFees($student_session_id);



                $obj = new stdClass();

                $obj->name = $eachstudent['firstname'] . " " . $eachstudent['lastname'];

                $obj->class = $eachstudent['class'];

                $obj->section = $eachstudent['section'];

                $obj->admission_no = $eachstudent['admission_no'];

                $fee_group = "";

                foreach ($student_total_fees as $key => $eachgroup) {

                    $fee_group .= ", " . $eachgroup->name;
                }

                $str = substr($fee_group, 1);

                $obj->student_fees_group = $str;

                $stdArray[] = $obj;
            }
        }

        $data['report'] = $stdArray;



        //        print_r($stdArray );

        $this->load->view('studentfee/feeAssignedClassWisePDF', $data);
    }



    function feeSummaryReport()

    {

        $date = $this->input->post('date');



        $collect_by = $this->input->post('collect_by');

        $fee_type = $this->input->post('fee_type');

        //        $collect_by = 'all';

        //        print_r($getDepositeAmount = $this->studentfeemaster_model->getDepositAmountBetweenDate($date, $date));

        $type = $this->feetype_model->get();

        //$students = $this->student_model->get();

        if ($fee_type == "all") {

            $report = $this->studentfeemaster_model->getFeeSummaryReport($date);

            $students = $this->studentfeemaster_model->getFeeSummaryStudents($date);

            $class = $this->studentfeemaster_model->getFeeSummaryClasses($date);

            $sections = $this->studentfeemaster_model->getFeeSummarySections($date);
        } else {

            $report = $this->studentfeemaster_model->getFeeSummaryReport($date, null, $fee_type);

            $students = $this->studentfeemaster_model->getFeeSummaryStudents($date, $fee_type);

            $class = $this->studentfeemaster_model->getFeeSummaryClasses($date, $fee_type);

            $sections = $this->studentfeemaster_model->getFeeSummarySections($date, $fee_type);
        }



        $data['class'] = $class;

        $data['sections'] = $sections;

        $data['students'] = $students;

        $data['type'] = $type;

        $data['user'] = $collect_by;

        $data['report'] = $report;

        $data['school'] = $this->setting_model->getCurrentSchoolName();

        $data['session'] = $this->setting_model->getCurrentSessionName();

        $data['date'] = $date;

        $setting_result = $this->setting_model->get();

        $data['settinglist'] = $setting_result;

        $criteria = $this->input->post('criteria');

        $data['criteria'] = $criteria;

        if ($criteria != "All") {

            //            echo json_encode($class);

            $this->load->view('studentfee/feeSummaryReportStudentWisePDF', $data);
        } else {

            //            print_r($report );

            $this->load->view('studentfee/feeSummaryReportPDF', $data);
        }
    }

    function feeSummaryReportDateWise()

    {

        $start_date = $this->input->post('start_date');

        $end_date = $this->input->post('end_date');

        $collect_by = $this->input->post('collect_by');

        $fee_type = $this->input->post('fee_type');



        $type = $this->feetype_model->get();

        if ($fee_type != "all") {

            $report = $this->studentfeemaster_model->getFeeSummaryReport($start_date, $end_date, $fee_type);
        } else {

            $report = $this->studentfeemaster_model->getFeeSummaryReport($start_date, $end_date);
        }



        $dates = $this->studentfeemaster_model->getFeeSummaryReportDates($start_date, $end_date);



        $data['user'] = $collect_by;

        $data['type'] = $type;

        $data['dates'] = $dates;

        $data['report'] = $report;

        $data['school'] = $this->setting_model->getCurrentSchoolName();

        $data['session'] = $this->setting_model->getCurrentSessionName();

        $data['start_date'] = $start_date;

        $data['end_date'] = $end_date;

        $setting_result = $this->setting_model->get();

        $data['settinglist'] = $setting_result;

        //        print_r($dates );

        $this->load->view('studentfee/feeSummaryReportDateWisePDF', $data);
    }



    function sendMail()

    {

        $date_from = date('Y-m-d');

        $expenseList = $this->expense_model->search("", $date_from, $date_from);

        $incomeList = $this->income_model->search("", $date_from, $date_from);

        $feeList = $this->studentfeemaster_model->getFeeBetweenDate($date_from, $date_from);

        $salary = $this->payroll_model->getpayrollReportByDate($date_from);

        $totalFee = 0;

        foreach ($feeList as $list) {

            $totalFee += $list['amount'] + $list['amount_discount'] + $list['amount_fine'];
        }

        $totalSalary = 0;

        foreach ($salary as $list) {

            $totalSalary += $list['net_salary'];
        }

        $totalExpense = 0;

        foreach ($expenseList as $list) {

            $totalExpense += $list['amount'];
        }

        $totalIncome = 0;

        foreach ($incomeList as $list) {

            $totalIncome += $list['amount'];
        }

        $date = date('d-F-Y', strtotime(date('Y-m-d')));

        $this->email->set_newline("\r\n");

        $this->email->from('info@smartschools.pk', 'Admin Smart School');

        $this->email->to('imranyusuf671@gmail.com');

        $this->email->subject("Daily Transaction Report For $date");

        $this->email->message("

            <div style=\"background-color: #f9f9f9;\" align=\"center\"><br />

            		  <table style=\"font-family: OpenSans,sans-serif; color: #666666;\" border=\"0\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" bgcolor=\"#FFFFFF\">

					    <tbody>

					      <tr>

					        <td width=\"600\" valign=\"top\" bgcolor=\"#FFFFFF\"><br>

					          <table style=\"font-family:OpenSans,sans-serif; color: #666666; font-size: 10px; padding: 15px;\" border=\"0\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" align=\"left\">

					            <tbody>

					              <tr>

					                <td valign=\"top\"><table border=\"0\" align=\"left\" cellpadding=\"0\" cellspacing=\"0\" style=\"font-family:OpenSans,sans-serif; color: #666666; font-size: 10px; width:100%;\">

					                    <tbody>

					                      <tr>

					                        <td><p style=\"color: #262626; font-size: 28px; margin-top:0px;\"><strong>Dear Principal</strong></p><br><br>

					                          <p style=\"color:#262626; font-size:20px; line-height:32px;font-weight:500;\">Thank you for using Smart School Management System<br>Your Transaction Data for $date is<br>

					                            Your Fee Collection is : <b>$totalFee</b><br>

					                            Your Income is : <b>$totalIncome</b><br>

					                            Your Salary Expenses are : <b>$totalSalary</b><br>

					                            Your Other Daily Expenses are:<b> $totalExpense<b></p>

					                          <p style=\"color:#262626; font-size:20px; line-height:32px;font-weight:500;margin-bottom:30px;\">Best Regard,<br />

					                            Smart School Admin.</p></td>

					                      </tr>

					                    </tbody>

					                  </table></td>

					              </tr>



					            </tbody>

					          </table></td>

					      </tr>

					      <tr>

					        <td style=\"color: #262626; padding: 20px 0; font-size: 20px; border-top:5px solid #52bfd3;\" colspan=\"2\" align=\"center\" bgcolor=\"#ffffff\">Copyright @2020 Smart School.</td>

					      </tr>

					    </tbody>

					  </table>

					</div>");

        if ($this->email->send()) {

            echo 'Success';
        } else {

            echo $this->email->print_debugger();
        }
    }





    function overDueNoticeView()

    {

        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {

            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Fees Collection');

        $this->session->set_userdata('sub_menu', 'studentfee/overDueNoticeView');

        $data['title'] = 'student fees';

        $class = $this->class_model->get();

        $data['classlist'] = $class;

        //print_r($class);

        $this->load->view('layout/header', $data);

        $this->load->view('studentfee/overdueNoticeView', $data);

        $this->load->view('layout/footer', $data);
    }



    function overDueNoticeView11()

    {

        if (!$this->rbac->hasPrivilege('collect_fees', 'can_view')) {

            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Fees Collection');

        $this->session->set_userdata('sub_menu', 'studentfee/overDueNoticeView');

        $data['title'] = 'student fees';

        $class = $this->class_model->get();

        $data['classlist'] = $class;

        //print_r($class);

        $this->load->view('layout/header', $data);

        $this->load->view('studentfee/overdueNoticeView11', $data);

        $this->load->view('layout/footer', $data);
    }





    function overDueNoticePDF()

    {

        $std = $this->input->post('student');

        $year = $this->input->post('year');

        $month = $this->input->post('month');

        $class_id = $this->input->post('class_id');

        $section_id = $this->input->post('section_id');

        $masterData = array();

        $this->form_validation->set_rules('year', 'Year', 'trim|required|xss_clean');

        $this->form_validation->set_rules('month', 'Month', 'trim|required|xss_clean');

        $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');

        //$this->form_validation->set_rules('section_id', 'Section', 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {

            $data['title'] = 'student fees';

            $class = $this->class_model->get();

            $data['classlist'] = $class;

            $this->load->view('layout/header', $data);

            $this->load->view('studentfee/overDueNoticeView', $data);

            $this->load->view('layout/footer', $data);
        } else {

            $this->load->model("ChallanModel");

            if ($class_id == 'ALL') {

                $students = $this->ChallanModel->searchByClassSection(null, null);
            } else {

                $students = $this->ChallanModel->searchByClassSection($class_id, $section_id);
            }

            for ($i = 0; $i < count($students); $i++) {

                $student = $this->student_model->get($students[$i]['id']);

                $data['title'] = 'Student Detail ' . $i;

                $data['student'] = $student;

                $student_due_fee = $this->ChallanModel->getStudentFees($student['student_session_id'], $month, $year);

                $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($student['student_session_id']);

                $student_pending_fee = $this->ChallanModel->getStudentPreviousFees($student['student_session_id'], $month, $year);

                $std_balance = $this->getPendingBalance($student['student_session_id'], $month);



                $data['std_arrears'] = $std_balance;

                $data['student_discount_fee'] = $student_discount_fee;

                $data['student_due_fee'] = $student_due_fee;

                $data['student_pending_fee'] = $student_pending_fee;

                $masterData[$i] = $data;
            }

            $data = array();

            $flag = false;

            $data['masterData'] = $masterData;

            for ($i = 0; $i < count($data['masterData']); $i++) {

                for ($j = 0; $j < count($data['masterData'][$i]['student_due_fee']); $j++) {

                    if (
                        $data['masterData'][$i]['student_due_fee'][$j]->fees != '' ||

                        $data['masterData'][$i]['student_due_fee'][$j]->fees != null

                    ) {

                        $flag = true;
                    }
                }
            }

            if ($flag) {

                //echo json_encode($masterData);

                $data['school'] = $this->setting_model->getCurrentSchoolName();

                $data['class'] = $this->class_model->get($class_id);

                $data['section'] = $this->section_model->get($section_id);

                $data['session'] = $this->setting_model->getCurrentSessionName();

                $data['year'] = $year;

                $data['month'] = $month;

                $data['classID'] = $class_id;

                $setting_result = $this->setting_model->get();

                $data['settinglist'] = $setting_result;

                //echo json_encode($masterData);

                $this->load->view('studentfee/overdueNoticePDF', $data);
            } else {

                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Fees not found for the given month!</div>');

                $data['title'] = 'student fees';

                $class = $this->class_model->get();

                $data['classlist'] = $class;

                $this->load->view('layout/header', $data);

                $this->load->view('studentfee/overdueNoticeView', $data);

                $this->load->view('layout/footer', $data);

                //echo json_encode($data);

            }

            //$this->load->view('studentfee/challanFormPDF', $data);

        }
    }



    function overDueNoticePDF11()

    {

        $std = $this->input->post('student');

        $year = $this->input->post('year');

        $month = $this->input->post('month');

        $class_id = $this->input->post('class_id');

        $section_id = $this->input->post('section_id');

        $masterData = array();

        $this->form_validation->set_rules('year', 'Year', 'trim|required|xss_clean');

        $this->form_validation->set_rules('month', 'Month', 'trim|required|xss_clean');

        $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');

        //$this->form_validation->set_rules('section_id', 'Section', 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {

            $data['title'] = 'student fees';

            $class = $this->class_model->get();

            $data['classlist'] = $class;

            $this->load->view('layout/header', $data);

            $this->load->view('studentfee/overDueNoticeView', $data);

            $this->load->view('layout/footer', $data);
        } else {

            $this->load->model("ChallanModel");

            if ($class_id == 'ALL') {

                $students = $this->ChallanModel->searchByClassSection(null, null);
            } else {

                $students = $this->ChallanModel->searchByClassSection($class_id, $section_id);
            }



            for ($i = 0; $i < count($students); $i++) {

                $student = $this->student_model->get($students[$i]['id']);

                $data['title'] = 'Student Detail ' . $i;

                $data['student'] = $student;

                $student_due_fee = $this->ChallanModel->getStudentFees($student['student_session_id'], $month, $year);

                $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($student['student_session_id']);

                $student_pending_fee = $this->ChallanModel->getStudentPreviousFees($student['student_session_id'], $month, $year);

                $std_balance = $this->getPendingBalance($student['student_session_id'], $month);



                $data['std_arrears'] = $std_balance;

                $data['student_discount_fee'] = $student_discount_fee;

                $data['student_due_fee'] = $student_due_fee;

                $data['student_pending_fee'] = $student_pending_fee;

                $masterData[$i] = $data;
            }



            $data = array();

            $flag = false;

            $data['masterData'] = $masterData;

            // for ($i = 0; $i < count($data['masterData']); $i++) {

            //     for ($j = 0; $j < count($data['masterData'][$i]['student_due_fee']); $j++) {

            //         if ($data['masterData'][$i]['student_due_fee'][$j]->fees != '' ||

            //             $data['masterData'][$i]['student_due_fee'][$j]->fees != null

            //         ) {

            //             $flag = true;

            //         }

            //     }

            // }

            // if ($flag) {

            //echo json_encode($masterData);

            $data['school'] = $this->setting_model->getCurrentSchoolName();

            $data['class'] = $this->class_model->get($class_id);

            $data['section'] = $this->section_model->get($section_id);

            $data['session'] = $this->setting_model->getCurrentSessionName();

            $data['year'] = $year;

            $data['month'] = $month;

            $data['classID'] = $class_id;

            $setting_result = $this->setting_model->get();

            $data['settinglist'] = $setting_result;

            //echo json_encode($masterData);

            $this->load->view('studentfee/overdueNoticePDF11', $data);

            // } else {

            //     $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Fees not found for the given month!</div>');

            //     $data['title'] = 'student fees';

            //     $class = $this->class_model->get();

            //     $data['classlist'] = $class;

            //     $this->load->view('layout/header', $data);

            //     $this->load->view('studentfee/overdueNoticeView11', $data);

            //     $this->load->view('layout/footer', $data);

            //     //echo json_encode($data);

            // }

            //$this->load->view('studentfee/challanFormPDF', $data);

        }
    }

    public function getPendingBalance($student_session_id, $month)
    {

        $student_total_fees = $this->studentfeemaster_model->getStudentAllFees($student_session_id, $month);

        if (!empty($student_total_fees)) {

            $monthfee = 0;

            $fine = 0;

            $deposit = 0;

            $discount = 0;

            foreach ($student_total_fees as $student_total_fees_key => $student_total_fees_value) {

                if (!empty($student_total_fees_value->fees)) {

                    foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {



                        $amount_detail = json_decode($each_fee_value->amount_detail);

                        $monthfee += $each_fee_value->amount;

                        if (is_object($amount_detail)) {

                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                $deposit += $amount_detail_value->amount;

                                $fine = $fine + $amount_detail_value->amount_fine;

                                $discount = $discount + $amount_detail_value->amount_discount;
                            }
                        }
                    }
                }
            }

            $balance = $monthfee - ($deposit + $discount);
        }

        //        echo json_encode($student_total_fees);

        return $balance;
    }

    public function getPendingBalanceByFeeType($student_session_id, $month)
    {

        $student_total_fees = $this->studentfeemaster_model->getStudentAllFees($student_session_id, $month);

        if (!empty($student_total_fees)) {

            $monthfee = 0;

            $fine = 0;

            $deposit = 0;

            $discount = 0;

            $tuition_fee = 0;
            $board_fee = 0;
            $admission_fee = 0;
            $transport_fee = 0;
            $paper_fee = 0;
            $enrol_fee = 0;
            $arear_fee = 0;

            $c_tuition_fee = 0;
            $c_board_fee = 0;
            $c_admission_fee = 0;
            $c_transport_fee = 0;
            $c_paper_fee = 0;
            $c_enrol_fee = 0;

            $tuition_deposit = 0;
            $board_deposit = 0;
            $admission_deposit = 0;
            $transport_deposit = 0;
            $paper_deposit = 0;
            $enrol_deposit = 0;
            $arear_deposit = 0;

            $tuition_discount = 0;
            $board_discount = 0;
            $admission_discount = 0;
            $transport_discount = 0;
            $paper_discount = 0;
            $enrol_discount = 0;
            $arear_discount = 0;



            foreach ($student_total_fees as $student_total_fees_key => $student_total_fees_value) {

                if (!empty($student_total_fees_value->fees)) {

                    foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {



                        $amount_detail = json_decode($each_fee_value->amount_detail);

                        $due_date = $each_fee_value->due_date;

                        $due_month = date('m', strtotime($due_date));



                        $monthfee += $each_fee_value->amount;

                        if ($each_fee_value->code == "Tuition") {

                            if ($due_month == $month) {

                                $c_tuition_fee += $each_fee_value->amount;
                            } else {

                                $tuition_fee += $each_fee_value->amount;
                            }
                        }
                        if ($each_fee_value->code == "BOARDADMISSIONFEE") {

                            if ($due_month == $month) {

                                $c_board_fee += $each_fee_value->amount;
                            } else {

                                $board_fee += $each_fee_value->amount;
                            }
                        }

                        if ($each_fee_value->code == "Admission") {

                            if ($due_month == $month) {

                                $c_admission_fee += $each_fee_value->amount;
                            } else {

                                $admission_fee += $each_fee_value->amount;
                            }
                        }

                        if ($each_fee_value->code == "Transport") {

                            if ($due_month == $month) {

                                $c_transport_fee += $each_fee_value->amount;
                            } else {

                                $transport_fee += $each_fee_value->amount;
                            }
                        }



                        if ($each_fee_value->code == "PAPERFUND") {

                            if ($due_month == $month) {

                                $c_paper_fee += $each_fee_value->amount;
                            } else {

                                $paper_fee += $each_fee_value->amount;
                            }
                        }

                        if ($each_fee_value->code == "ENROLMENTFEE") {

                            if ($due_month == $month) {

                                $c_enrol_fee += $each_fee_value->amount;
                            } else {

                                $enrol_fee += $each_fee_value->amount;
                            }
                        }

                        if ($each_fee_value->code == "PreviousSessionBalance" || $each_fee_value->code == "Arrears") {

                            $arear_fee += $each_fee_value->amount;
                        }

                        if (is_object($amount_detail)) {

                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                $deposit += $amount_detail_value->amount;

                                $fine = $fine + $amount_detail_value->amount_fine;

                                $discount = $discount + $amount_detail_value->amount_discount;
                            }

                            if ($each_fee_value->code == "Tuition" && $due_month != $month) {

                                foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                    $tuition_deposit += $amount_detail_value->amount;

                                    $tuition_discount +=  $amount_detail_value->amount_discount;
                                }
                            }

                            if ($each_fee_value->code == "BOARDADMISSIONFEE" && $due_month != $month) {

                                foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                    $board_deposit += $amount_detail_value->amount;

                                    $board_discount +=  $amount_detail_value->amount_discount;
                                }
                            }

                            if ($each_fee_value->code == "Admission" && $due_month != $month) {

                                foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                    $admission_deposit += $amount_detail_value->amount;

                                    $admission_discount += $amount_detail_value->amount_discount;
                                }
                            }

                            if ($each_fee_value->code == "Transport" && $due_month != $month) {

                                foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                    $transport_deposit += $amount_detail_value->amount;

                                    $transport_discount += $amount_detail_value->amount_discount;
                                }
                            }



                            if ($each_fee_value->code == "PAPERFUND" && $due_month != $month) {

                                foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                    $paper_deposit += $amount_detail_value->amount;

                                    $paper_discount += $amount_detail_value->amount_discount;
                                }
                            }

                            if ($each_fee_value->code == "ENROLMENTFEE" && $due_month != $month) {

                                foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                    $enrol_deposit += $amount_detail_value->amount;

                                    $enrol_discount += $amount_detail_value->amount_discount;
                                }
                            }

                            if ($each_fee_value->code == "PreviousSessionBalance" || $each_fee_value->code == "Arrears") {

                                foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                    $arear_deposit += $amount_detail_value->amount;

                                    $arear_discount += $amount_detail_value->amount_discount;
                                }
                            }
                        }
                    }
                }
            }

            $balance = $monthfee - ($deposit + $discount);

            $tuition_balance = $tuition_fee - ($tuition_deposit + $tuition_discount);

            $board_balance = $board_fee - ($board_deposit + $board_discount);

            $admission_balance = $admission_fee - ($admission_deposit + $admission_discount);

            $transport_balance = $transport_fee - ($transport_deposit + $transport_discount);

            $paper_balance = $paper_fee - ($paper_deposit + $paper_discount);

            $enrol_balance = $enrol_fee - ($enrol_deposit + $enrol_discount);

            $arrear_balance = $arear_fee - ($arear_deposit + $arear_discount);

            $return_array = array(

                "balance" => $balance,

                "tuition_balance" => $tuition_balance,

                "tuition_curent" => $c_tuition_fee,

                "board_balance" => $board_balance,

                "board_curent" => $c_board_fee,

                "admission_balance" => $admission_balance,

                "admission_curent" => $c_admission_fee,

                "transport_balance" => $transport_balance,

                "transport_curent" => $c_transport_fee,

                "paper_balance" => $paper_balance,

                "paper_curent" => $c_paper_fee,

                "enrol_balance" => $enrol_balance,

                "enrol_curent" => $c_enrol_fee,

                "arrear_balance " => $arrear_balance

            );
        }

        //        echo json_encode($return_array);

        return $return_array;
    }



    function fee_summary_report_view()

    {

        $this->session->set_userdata('top_menu', 'Fees Collection');

        $this->session->set_userdata('sub_menu', 'studentfee/defaulterFeeSearch');

        $data['title'] = 'student fees';

        $class = $this->class_model->get();

        $data['classlist'] = $class;

        $data['startDate'] = date('Y-m-01');

        $data['endDate'] = date('Y-m-d');

        $this->load->view('layout/header', $data);

        $this->load->view('studentfee/classFeeSummaryReportView', $data);

        $this->load->view('layout/footer', $data);
    }



    function fee_summary_report_pdf()

    {

        $input = $this->setting_model->getCurrentSessionName();



        list($a, $b) = explode('-', $input);

        $Current_year = $a;

        if (strlen($b) == 2) {

            $Next_year = substr($a, 0, 2) . $b;
        } else {

            $Next_year = $b;
        }

        //         $Current_year;

        //        echo $Next_year."<br>";



        $class_id = $this->input->post('class_id');

        $year = $this->input->post('year');

        $year1 = $this->input->post('year');



        $month = $this->input->post('month');

        $previous_month = $month - 1;

        $previous_month_date = $year . '-0' . $previous_month . '-' . '01';

        $startMonth = $this->setting_model->getStartMonth();

        $query_date = $year . '-' . $month . '-' . '01';

        $startDate = date('Y-m-01', strtotime($query_date));

        $endDate = date('Y-m-t', strtotime($query_date));

        if ($year == date('Y') && $year == $Current_year) {

            $current_section_date = $year . '-0' . $startMonth . '-01';

            $current_section1 = date('Y-m-01', strtotime($current_section_date));;
        } else if ($year == date('Y') && $year > $Current_year) {

            $year = $year - 1;

            $current_section_date = $year . '-0' . $startMonth . '-01';

            $current_section1 = date('Y-m-01', strtotime($current_section_date));;
        } elseif ($year < date('Y')) {

            //            $year = $year-1;

            $current_section_date = $year . '-0' . $startMonth . '-01';

            $current_section1 = date('Y-m-01', strtotime($current_section_date));;
        }

        $student_Array = array();

        if ($class_id == 'ALL' || $class_id == 'ALLSECTION') {

            $studentlist = $this->student_model->searchByClassSection_old();
        }



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



                //                print_r($student_total_fees);

                if (!empty($student_total_fees)) {

                    $totalfee = 0;

                    $monthfee = 0;

                    $deposit = 0;

                    $discount = 0;

                    $balance = 0;

                    $previous_monthfee = 0;

                    $previous_deposit = 0;

                    $previous_discount = 0;

                    $previous_fine = 0;

                    $previous_balance = 0;

                    $fine = 0;

                    $previous = 0;

                    $code = '';

                    foreach ($student_total_fees as $student_total_fees_key => $student_total_fees_value) {

                        if (!empty($student_total_fees_value->fees)) {

                            foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {

                                //$totalfee = $totalfee + $each_fee_value->amount;

                                //$monthfee = $each_fee_value->amount;

                                if ($each_fee_value->name == 'Balance Master') {

                                    $previous += $each_fee_value->amount;

                                    $code = $each_fee_value->name;
                                }

                                $amount_detail = json_decode($each_fee_value->amount_detail);

                                //                                print_r($amount_detail);

                                if ($each_fee_value->due_date >= $startDate && $each_fee_value->due_date <= $endDate) {

                                    if ($each_fee_value->name != 'Balance Master') {

                                        $monthfee += $each_fee_value->amount;
                                    }
                                }

                                $m = date('m', strtotime($each_fee_value->due_date));

                                $previous_month_date_m = date('m', strtotime($previous_month_date));

                                if ($each_fee_value->due_date >= $current_section1 && $each_fee_value->due_date < $startDate) {



                                    if ($each_fee_value->name != 'Balance Master') {

                                        $previous_monthfee += $each_fee_value->amount;
                                    }
                                }

                                // echo "<pre/>";

                                // var_dump($amount_detail);



                                if (is_object($amount_detail)) {

                                    foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                        //print_r($amount_detail);

                                        if ($amount_detail_value->date >= $startDate && $amount_detail_value->date <= $endDate) {

                                            $deposit += $amount_detail_value->amount;

                                            $fine = $fine + $amount_detail_value->amount_fine;

                                            $discount = $discount + $amount_detail_value->amount_discount;
                                        }

                                        $y = date('Y', strtotime($amount_detail_value->date));

                                        $m = date('m', strtotime($amount_detail_value->date));

                                        $previous_month_date_m = date('m', strtotime($previous_month_date));

                                        if ($amount_detail_value->date >= $current_section1 && $amount_detail_value->date < $startDate) {

                                            $previous_deposit += $amount_detail_value->amount;

                                            $previous_fine = $previous_fine + $amount_detail_value->amount_fine;

                                            $previous_discount = $previous_discount + $amount_detail_value->amount_discount;
                                        }



                                        /* else{

                                             $deposit="N/A";

                                             $fine="N/A";

                                             $discount="N/A";

                                         }*/
                                    }
                                }
                            }
                        }
                    }

                    $obj->totalfee = $monthfee;

                    $obj->payment_mode = "N/A";

                    $obj->deposit = $deposit;

                    $obj->fine = $fine;

                    $obj->discount = $discount;

                    $obj->previous_deposit = $previous_deposit;

                    $obj->previous_monthfee = $previous_monthfee;

                    $obj->previous_fine = $previous_fine;

                    $obj->previous_discount = $previous_discount;

                    $obj->previous = ($previous + $previous_monthfee) - ($previous_deposit + $previous_discount);

                    $obj->code = $code;

                    $obj->balance = $monthfee + $previous - ($deposit + $discount);

                    $obj->previous_balance = $previous_monthfee - ($previous_deposit + $previous_discount);
                    
                    // DEBUG: Log per-student calculation
                    log_message('debug', '=== FEE SUMMARY REPORT - Student: ' . $obj->admission_no . ' ===');
                    log_message('debug', 'Month Fee: ' . $monthfee);
                    log_message('debug', 'Previous (Balance Master): ' . $previous);
                    log_message('debug', 'Previous Month Fee: ' . $previous_monthfee);
                    log_message('debug', 'Deposit: ' . $deposit);
                    log_message('debug', 'Discount: ' . $discount);
                    log_message('debug', 'Previous Deposit: ' . $previous_deposit);
                    log_message('debug', 'Previous Discount: ' . $previous_discount);
                    log_message('debug', 'Arrears (previous): ' . $obj->previous);
                    log_message('debug', 'Balance: ' . $obj->balance);
                    log_message('debug', 'Total Receivable (monthfee + previous): ' . ($monthfee + $obj->previous));
                } else {



                    $obj->totalfee = "N/A";

                    $obj->payment_mode = "N/A";

                    $obj->deposit = "N/A";

                    $obj->fine = "N/A";

                    $obj->previous_monthfee = "N/A";

                    $obj->balance = "N/A";

                    $obj->previous_deposit = "N/A";

                    $obj->previous_fine = "N/A";

                    $obj->previous_discount = "N/A";

                    $obj->discount = "N/A";

                    $obj->code = "N/A";

                    $obj->previous = "N/A";

                    $obj->previous_balance = "N/A";
                }

                $student_Array[] = $obj;
            }
        }
        
        // DEBUG: Calculate grand totals for comparison
        $report_total_current = 0;
        $report_total_arrears = 0;
        $report_total_receivable = 0;
        foreach ($student_Array as $student) {
            if (is_numeric($student->totalfee)) {
                $report_total_current += $student->totalfee;
            }
            if (is_numeric($student->previous)) {
                $report_total_arrears += $student->previous;
            }
            if (is_numeric($student->totalfee) && is_numeric($student->previous)) {
                $report_total_receivable += ($student->totalfee + $student->previous);
            }
        }
        
        log_message('debug', '=== FEE SUMMARY REPORT GRAND TOTALS ===');
        log_message('debug', 'Total Current Month Fees (monthfee): ' . $report_total_current);
        log_message('debug', 'Total Arrears (previous): ' . $report_total_arrears);
        log_message('debug', 'Total Receivable (monthfee + previous): ' . $report_total_receivable);
        log_message('debug', 'Number of Students: ' . count($student_Array));

        $data['year'] = $year1;

        $data['month'] = $month;

        $data['student_due_fee'] = $student_Array;

        $data['class'] = $this->class_model->get();

        $data['class_section'] = $this->class_model->getClassSection();

        $data['section'] = $this->section_model->get();

        $data['school'] = $this->setting_model->getCurrentSchoolName();

        $setting_result = $this->setting_model->get();

        $data['settinglist'] = $setting_result;

        $newAdmission = $this->studentfee_model->getNewAdmissionFeeSummary($month, $year1);

        $data['newAdmission'] = $newAdmission;

        if ($class_id == 'ALL') {

            $this->load->view('studentfee/classFeeSummaryReportPDF', $data);
        } else {

            $this->load->view('studentfee/classFeeSummaryReportSectionWisePDF', $data);
        }

        //echo json_encode($newAdmission);

    }



    function fee_summary_report_pdf_new()

    {

        $input = $this->setting_model->getCurrentSessionName();



        list($a, $b) = explode('-', $input);

        $Current_year = $a;

        if (strlen($b) == 2) {

            $Next_year = substr($a, 0, 2) . $b;
        } else {

            $Next_year = $b;
        }

        //         $Current_year;

        //        echo $Next_year."<br>";



        $class_id = $this->input->post('class_id');

        $year = $this->input->post('year');

        $month = $this->input->post('month');

        $previous_month = $month - 1;

        $previous_month_date = $year . '-0' . $previous_month . '-' . '01';

        $startMonth = $this->setting_model->getStartMonth();

        $query_date = $year . '-' . $month . '-' . '01';

        $startDate = date('Y-m-01', strtotime($query_date));

        $endDate = date('Y-m-t', strtotime($query_date));

        if ($year == date('Y') && $year == $Current_year) {

            $current_section_date = $year . '-0' . $startMonth . '-01';

            $current_section1 = date('Y-m-01', strtotime($current_section_date));;
        } else if ($year == date('Y') && $year > $Current_year) {

            $year = $year - 1;

            $current_section_date = $year . '-0' . $startMonth . '-01';

            $current_section1 = date('Y-m-01', strtotime($current_section_date));;
        } elseif ($year < date('Y')) {

            //            $year = $year-1;

            $current_section_date = $year . '-0' . $startMonth . '-01';

            $current_section1 = date('Y-m-01', strtotime($current_section_date));;
        }

        $student_Array = array();

        if ($class_id == 'ALL') {

            $studentlist = $this->student_model->searchByClassSection();
        }

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

                //                print_r($student_total_fees);

                if (!empty($student_total_fees)) {

                    $totalfee = 0;

                    $monthfee = 0;

                    $deposit = 0;

                    $discount = 0;

                    $balance = 0;

                    $previous_monthfee = 0;

                    $previous_deposit = 0;

                    $previous_discount = 0;

                    $previous_fine = 0;

                    $previous_balance = 0;

                    $fine = 0;

                    $previous = 0;

                    $code = '';

                    foreach ($student_total_fees as $student_total_fees_key => $student_total_fees_value) {

                        if (!empty($student_total_fees_value->fees)) {

                            foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {

                                //$totalfee = $totalfee + $each_fee_value->amount;

                                //$monthfee = $each_fee_value->amount;

                                if ($each_fee_value->name == 'Balance Master') {

                                    $previous += $each_fee_value->amount;

                                    $code = $each_fee_value->name;
                                }

                                $amount_detail = json_decode($each_fee_value->amount_detail);

                                //                                print_r($amount_detail);

                                if ($each_fee_value->due_date >= $startDate && $each_fee_value->due_date <= $endDate) {

                                    if ($each_fee_value->name != 'Balance Master') {

                                        $monthfee += $each_fee_value->amount;
                                    }
                                }

                                $m = date('m', strtotime($each_fee_value->due_date));

                                $previous_month_date_m = date('m', strtotime($previous_month_date));

                                if ($each_fee_value->due_date >= $current_section1 && $each_fee_value->due_date < $startDate) {



                                    if ($each_fee_value->name != 'Balance Master') {

                                        $previous_monthfee += $each_fee_value->amount;
                                    }
                                }

                                // echo "<pre/>";

                                // var_dump($amount_detail);



                                if (is_object($amount_detail)) {

                                    foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                        //print_r($amount_detail);

                                        if ($amount_detail_value->date >= $startDate && $amount_detail_value->date <= $endDate) {

                                            $deposit += $amount_detail_value->amount;

                                            $fine = $fine + $amount_detail_value->amount_fine;

                                            $discount = $discount + $amount_detail_value->amount_discount;
                                        }

                                        $y = date('Y', strtotime($amount_detail_value->date));

                                        $m = date('m', strtotime($amount_detail_value->date));

                                        $previous_month_date_m = date('m', strtotime($previous_month_date));

                                        if ($amount_detail_value->date >= $current_section1 && $amount_detail_value->date < $startDate) {

                                            $previous_deposit += $amount_detail_value->amount;

                                            $previous_fine = $previous_fine + $amount_detail_value->amount_fine;

                                            $previous_discount = $previous_discount + $amount_detail_value->amount_discount;
                                        }



                                        /* else{

                                             $deposit="N/A";

                                             $fine="N/A";

                                             $discount="N/A";

                                         }*/
                                    }
                                }
                            }
                        }
                    }

                    $obj->totalfee = $monthfee;

                    $obj->payment_mode = "N/A";

                    $obj->deposit = $deposit;

                    $obj->fine = $fine;

                    $obj->discount = $discount;

                    $obj->previous_deposit = $previous_deposit;

                    $obj->previous_monthfee = $previous_monthfee;

                    $obj->previous_fine = $previous_fine;

                    $obj->previous_discount = $previous_discount;

                    $obj->previous = ($previous + ($previous_monthfee + $previous_fine)) - $previous_deposit;

                    $obj->code = $code;

                    $obj->balance = $monthfee + $previous - ($deposit + $discount);

                    $obj->previous_balance = $previous_monthfee - ($previous_deposit + $previous_discount);
                } else {



                    $obj->totalfee = "N/A";

                    $obj->payment_mode = "N/A";

                    $obj->deposit = "N/A";

                    $obj->fine = "N/A";

                    $obj->previous_monthfee = "N/A";

                    $obj->balance = "N/A";

                    $obj->previous_deposit = "N/A";

                    $obj->previous_fine = "N/A";

                    $obj->previous_discount = "N/A";

                    $obj->discount = "N/A";

                    $obj->code = "N/A";

                    $obj->previous = "N/A";

                    $obj->previous_balance = "N/A";
                }

                $student_Array[] = $obj;
            }
        }

        $data['year'] = $year;

        $data['month'] = $month;

        $data['student_due_fee'] = $student_Array;

        $data['class'] = $this->class_model->get();

        $data['school'] = $this->setting_model->getCurrentSchoolName();

        $setting_result = $this->setting_model->get();

        $data['settinglist'] = $setting_result;

        $newAdmission = $this->studentfee_model->getNewAdmissionFeeSummary($month, $year);

        $data['newAdmission'] = $newAdmission;

        $this->load->view('studentfee/classFeeSummaryReportPDF', $data);

        //        echo json_encode($student_Array);

    }



    /*

     *  for barcode single student

    function addfee1($id)

    {



        if (!$this->rbac->hasPrivilege('collect_fees', 'can_add')) {

            access_denied();

        }

        $data['title'] = 'Student Detail';

        $student = $this->student_model->get($id);

        $data['student'] = $student;

        $role = $this->customlib->getStaffRole();

        $data['role_id'] = json_decode($role)->id;

        $student_due_fee = $this->studentfeemaster_model->getStudentFees1($student['student_session_id']);

        $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($student['student_session_id']);

        $data['student_discount_fee'] = $student_discount_fee;

        $data['student_due_fee'] = $student_due_fee;

        $category = $this->category_model->get();

        $data['categorylist'] = $category;

        $class_section = $this->student_model->getClassSection($student["class_id"]);

        $data["class_section"] = $class_section;

        $session = $this->setting_model->getCurrentSession();

        $studentlistbysection = $this->student_model->getStudentClassSection($student["class_id"],$session);

        $data["studentlistbysection"] = $studentlistbysection;

        //echo json_encode($student_due_fee);

        $this->load->view('layout/header', $data);

        $this->load->view('studentfee/studentAddFee1', $data);

        $this->load->view('layout/footer', $data);

    }

    */



    // for barcode siblings

    function addfee1($id)

    {



        if (!$this->rbac->hasPrivilege('collect_fees', 'can_add')) {

            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Fees Collection');

        $this->session->set_userdata('sub_menu', 'studentfee/barCodeFeeCollectView');

        $data['title'] = 'Student Detail';

        $student = $this->student_model->get($id);

        $siblingDetail = $this->student_model->read_siblings_students($student['parent_id']);

        if (count($siblingDetail) > 1) {

            $student_due_fee = array();

            $student_discount_fee = array();

            $class_section = array();

            $studentData = array();

            for ($i = 0; $i < count($siblingDetail); $i++) {

                $student = $this->student_model->get($siblingDetail[$i]->id);

                $studentData[] = $student;

                $student_due_fee[] = $this->studentfeemaster_model->getStudentFees1($student['student_session_id']);

                $student_discount_fee[] = $this->feediscount_model->getStudentFeesDiscount($student['student_session_id']);

                $class_section[] = $this->student_model->getClassSection($student["class_id"]);
            }

            $data['student'] = $studentData;

            $data['student_discount_fee'] = $student_discount_fee;

            $data['student_due_fee'] = $student_due_fee;

            $category = $this->category_model->get();

            $data['categorylist'] = $category;

            $data["class_section"] = $class_section;

            $session = $this->setting_model->getCurrentSession();

            $studentlistbysection = $this->student_model->getStudentClassSection($student["class_id"], $session);

            $data["studentlistbysection"] = $studentlistbysection;

            //echo json_encode($data);

            $this->load->view('layout/header', $data);

            $this->load->view('studentfee/studentAddFee1', $data);

            $this->load->view('layout/footer', $data);
        } else {

            $student = $this->student_model->get($id);

            $data['student'] = $student;

            $student_due_fee = $this->studentfeemaster_model->getStudentFees1($student['student_session_id']);

            $student_discount_fee = $this->feediscount_model->getStudentFeesDiscount($student['student_session_id']);

            $data['student_discount_fee'] = $student_discount_fee;

            $data['student_due_fee'] = $student_due_fee;

            $category = $this->category_model->get();

            $data['categorylist'] = $category;

            $class_section = $this->student_model->getClassSection($student["class_id"]);

            $data["class_section"] = $class_section;

            $session = $this->setting_model->getCurrentSession();

            $studentlistbysection = $this->student_model->getStudentClassSection($student["class_id"], $session);

            $data["studentlistbysection"] = $studentlistbysection;

            //echo json_encode($student_due_fee);

            $this->load->view('layout/header', $data);

            $this->load->view('studentfee/studentAddFee2', $data);

            $this->load->view('layout/footer', $data);
        }
    }



    function pendingFeeReportView()
    {

        if (!$this->rbac->hasPrivilege('pending_fee_report', 'can_view')) {

            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Reports');

        $this->session->set_userdata('sub_menu', 'studentfee/pendingFeeReportView');

        $data['title'] = 'student fees';

        $class = $this->class_model->get();

        $feetype = $this->feetype_model->get();

        $data['classlist'] = $class;

        $data['feetype'] = $feetype;

        //        print_r($feetype);

        $this->load->view('layout/header', $data);

        $this->load->view('studentfee/pendingFeeReportView', $data);

        $this->load->view('layout/footer', $data);
    }



    function pendingFeeReport()

    {

        $std = $this->input->post('student');

        $year1 = $this->input->post('year');

        $year = $this->input->post('year');

        $month = $this->input->post('month');

        $fee_type = $this->input->post('fee_type');

        $query_date = $year . '-' . $month . '-' . '01';

        $startDate = date('Y-m-01', strtotime($query_date));

        $endDate = date('Y-m-t', strtotime($query_date));

        $class_id = $this->input->post('class_id');

        $section_id = $this->input->post('section_id');

        $input = $this->setting_model->getCurrentSessionName();

        $startMonth = $this->setting_model->getStartMonth();

        list($a, $b) = explode('-', $input);

        $Current_year = $a;

        if (strlen($b) == 2) {

            $Next_year = substr($a, 0, 2) . $b;
        } else {

            $Next_year = $b;
        }

        $previous_month = $month - 1;

        $previous_month_date = $year . '-0' . $previous_month . '-' . '01';

        if ($year == date('Y') && $year == $Current_year) {

            $current_section_date = $year . '-0' . $startMonth . '-01';

            $current_section1 = date('Y-m-01', strtotime($current_section_date));;
        } else if ($year == date('Y') && $year > $Current_year) {

            $year = $year - 1;

            $current_section_date = $year . '-0' . $startMonth . '-01';

            $current_section1 = date('Y-m-01', strtotime($current_section_date));;
        } elseif ($year < date('Y')) {

            //            $year = $year-1;

            $current_section_date = $year . '-0' . $startMonth . '-01';

            $current_section1 = date('Y-m-01', strtotime($current_section_date));;
        }

        $masterData = array();

        $student_Array = array();

        $this->form_validation->set_rules('year', 'Year', 'trim|required|xss_clean');

        $this->form_validation->set_rules('month', 'Month', 'trim|required|xss_clean');

        $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');

        //$this->form_validation->set_rules('section_id', 'Section', 'trim|required|xss_clean');

        if ($this->form_validation->run() == FALSE) {

            $data['title'] = 'student fees';

            $class = $this->class_model->get();

            $data['classlist'] = $class;

            $this->load->view('layout/header', $data);

            $this->load->view('studentfee/pendingFeeReportView', $data);

            $this->load->view('layout/footer', $data);
        } else {

            $this->load->model("ChallanModel");

            if ($class_id == 'ALL' || $class_id == 'family') {

                $studentlist = $this->student_model->searchByClassSection(null, $section_id);
            } else {

                $studentlist = $this->student_model->searchByClassSection($class_id, $section_id);
            }

            if (!empty($studentlist)) {

                foreach ($studentlist as $key => $eachstudent) {

                    $obj = new stdClass();

                    $obj->name = $eachstudent['firstname'] . " " . $eachstudent['lastname'];

                    $obj->class = $eachstudent['class'];

                    $obj->section = $eachstudent['section'];

                    $obj->admission_no = $eachstudent['admission_no'];

                    $obj->roll_no = $eachstudent['roll_no'];

                    $obj->father_name = $eachstudent['father_name'];

                    $obj->parent_id = $eachstudent['parent_id'];

                    $obj->guardian_phone = $eachstudent['guardian_phone'];

                    $student_session_id = $eachstudent['student_session_id'];

                    if ($fee_type != "all") {

                        $student_total_fees = $this->studentfeemaster_model->getStudentFeesMonthly1($student_session_id, $startDate, $endDate, $fee_type);

                        $student_previous_fees = $this->studentfeemaster_model->getStudentPreviousFees($student_session_id, $startDate, $endDate, $fee_type);
                    } else {

                        $student_total_fees = $this->studentfeemaster_model->getStudentFeesMonthly1($student_session_id, $startDate, $endDate);

                        $student_previous_fees = $this->studentfeemaster_model->getStudentPreviousFees($student_session_id, $startDate, $endDate);
                    }

                    //                print_r($student_previous_fees); die();

                    //                    
?><!-- <pre> --><?php //print_r($student_previous_fees); die();

                    if (!empty($student_total_fees)) {

                        $totalfee = 0;

                        $monthfee = 0;

                        $fine = 0;

                        $deposit = 0;

                        $discount = 0;

                        $balance = 0;

                        $previous_monthfee = 0;

                        $previous_deposit = 0;

                        $previous_discount = 0;

                        $previous_fine = 0;

                        $previous_balance = 0;

                        $previous_monthfee_name = 0;



                        $previous = 0;

                        $arrears = 0;

                        $arrears_deposit = 0;

                        $arrears_discount = 0;

                        $arrears_fine = 0;

                        $code = '';

                        foreach ($student_total_fees as $student_total_fees_key => $student_total_fees_value) {

                            if (!empty($student_total_fees_value->fees)) {

                                foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {

                                    //$totalfee = $totalfee + $each_fee_value->amount;

                                    //$monthfee = $each_fee_value->amount;

                                    /* if ($each_fee_value->name == 'Balance Master') {

                                         $previous += $each_fee_value->amount;

                                         $code = $each_fee_value->name;

                                     }*/

                                    $amount_detail = json_decode($each_fee_value->amount_detail);

                                    //                                print_r($amount_detail);

                                    if ($each_fee_value->due_date >= $startDate && $each_fee_value->due_date <= $endDate) {

                                        //                                        if ($each_fee_value->name != 'Balance Master') {

                                        //                                        print_r($each_fee_value);

                                        $monthfee += $each_fee_value->amount;
                                    }

                                    //                                    }



                                    if (is_object($amount_detail)) {

                                        foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                            //print_r($amount_detail);

                                            //                                            if ($amount_detail_value->date >= $startDate /*&& $amount_detail_value->date <= $endDate*/) {

                                            $deposit += $amount_detail_value->amount;

                                            $fine = $fine + $amount_detail_value->amount_fine;

                                            $discount = $discount + $amount_detail_value->amount_discount;

                                            //                                            }



                                        }
                                    }
                                }
                            }
                        }

                        if (!empty($student_previous_fees)) {

                            //                            print_r($student_previous_fees); die();

                            foreach ($student_previous_fees as $student_total_fees_key => $student_total_fees_value) {

                                if (!empty($student_total_fees_value->fees)) {

                                    foreach ($student_total_fees_value->fees as $each_fee_key => $each_fee_value) {

                                        //$totalfee = $totalfee + $each_fee_value->amount;

                                        //$monthfee = $each_fee_value->amount;

                                        if ($each_fee_value->name == 'Balance Master') {



                                            $previous += $each_fee_value->amount;

                                            $code = $each_fee_value->name;

                                            $amount_detail_1 = json_decode($each_fee_value->amount_detail);



                                            if (is_object($amount_detail_1)) {

                                                foreach ($amount_detail_1 as $amount_detail_key => $amount_detail_value) {

                                                    //print_r($amount_detail);



                                                    if ($amount_detail_value->date >= $current_section1) {

                                                        $arrears_deposit += $amount_detail_value->amount;

                                                        $arrears_fine = $arrears_fine + $amount_detail_value->amount_fine;

                                                        $arrears_discount = $arrears_discount + $amount_detail_value->amount_discount;
                                                    }
                                                }
                                            }

                                            $arrears = $previous - ($arrears_deposit + $arrears_discount);
                                        }



                                        //                                    }



                                        $m = date('m', strtotime($each_fee_value->due_date));

                                        $previous_month_date_m = date('m', strtotime($previous_month_date));

                                        if ($each_fee_value->due_date >= $current_section1 && $each_fee_value->due_date < $startDate) {



                                            if ($each_fee_value->name != 'Balance Master') {

                                                $month1 = date("m", strtotime($each_fee_value->due_date));

                                                $dateObj   = DateTime::createFromFormat('!m', $month1);

                                                $monthName = $dateObj->format('F');





                                                //                                                
                ?><!-- <pre> --><?php //print_r($each_fee_value);

                                                $previous_monthfee += $each_fee_value->amount;

                                                $amount_detail = json_decode($each_fee_value->amount_detail);



                                                //                                                if(!is_object($amount_detail)){

                                                $previous_monthfee_name = $monthName . " : " . $each_fee_value->amount;

                                                //                                                }





                                                if (is_object($amount_detail)) {

                                                    foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                                        //print_r($amount_detail);



                                                        $y = date('Y', strtotime($amount_detail_value->date));

                                                        $m = date('m', strtotime($amount_detail_value->date));

                                                        $previous_month_date_m = date('m', strtotime($previous_month_date));

                                                        if ($amount_detail_value->date >= $current_section1) {

                                                            $previous_deposit += $amount_detail_value->amount;

                                                            $previous_fine = $previous_fine + $amount_detail_value->amount_fine;

                                                            $previous_discount = $previous_discount + $amount_detail_value->amount_discount;

                                                            $previous_monthfee_name = $monthName . " : " . ($each_fee_value->amount - $amount_detail_value->amount - $amount_detail_value->amount_discount);
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        //                                         echo "<pre/>";

                                        //                                         var_dump($previous_monthfee_name);





                                    }
                                }
                            }

                            $obj->previous_deposit = $previous_deposit;

                            $obj->previous_monthfee = $previous_monthfee;

                            $obj->previous_fine = $previous_fine;

                            $obj->previous_monthfee_name = $previous_monthfee_name;

                            $obj->previous_discount = $previous_discount;

                            $obj->previous = ($arrears + ($previous_monthfee)) - ($previous_deposit + $previous_discount);

                            $obj->previous_month = $previous_month_date_m;

                            $obj->previous_balance = ($previous_monthfee) - ($previous_deposit + $previous_discount);
                        } else {

                            $obj->previous_deposit = "N/A";

                            $obj->previous_monthfee = "N/A";

                            $obj->previous_fine = "N/A";

                            $obj->previous_discount = "N/A";

                            $obj->previous = "N/A";

                            $obj->previous_balance = "N/A";
                        }

                        $obj->totalfee = $monthfee;

                        $obj->payment_mode = "N/A";

                        $obj->deposit = $deposit;

                        $obj->fine = $fine;

                        $obj->discount = $discount;



                        $obj->code = $code;

                        $obj->balance = $monthfee + $arrears - ($deposit + $discount);
                    } else {



                        $obj->totalfee = "N/A";

                        $obj->payment_mode = "N/A";

                        $obj->deposit = "N/A";

                        $obj->fine = "N/A";

                        $obj->previous_monthfee = "N/A";

                        $obj->balance = "N/A";

                        $obj->previous_deposit = "N/A";

                        $obj->previous_fine = "N/A";

                        $obj->previous_discount = "N/A";

                        $obj->discount = "N/A";

                        $obj->code = "N/A";

                        $obj->previous = "N/A";

                        $obj->previous_balance = "N/A";
                    }

                    $student_Array[] = $obj;
                }
            }

            $data['school'] = $this->setting_model->getCurrentSchoolName();

            if ($class_id == 'ALL') {

                $data['class'] = $this->class_model->get();

                $data['section'] = $this->section_model->get();
            } elseif ($class_id == 'family') {

                $data['families'] = $this->student_model->getfamily();
            } else {

                $data['class'] = $this->class_model->get($class_id);

                $data['section'] = $this->section_model->get($section_id);
            }





            $data['session'] = $this->setting_model->getCurrentSessionName();

            $setting_result = $this->setting_model->get();

            $data['settinglist'] = $setting_result;

            $data['year'] = $year1;

            $data['month'] = $month;

            $data['classID'] = $class_id;

            $data['student_due_fee'] = $student_Array;

            //            echo json_encode($student_Array);

            //            $this->load->view('studentfee/pendingFeeReportPDF', $data);



            // for sadiq memorial

            //            echo json_encode($data);



            if ($class_id == 'ALL') {

                $this->load->view('studentfee/pendingFeeReportAllPDF', $data);
            } elseif ($class_id == 'family') {

                $this->load->view('studentfee/pendingFeeReportFamilyPDF', $data);

                //                echo json_encode($data);

            } else {

                $this->load->view('studentfee/pendingFeeReportPDF', $data);
            }

            //print_r($student_total_fees);

            //$this->load->view('studentfee/challanFormPDF', $data);

        }
    }



    public function getcollectfeeSibling()

    {

        $setting_result      = $this->setting_model->get();

        $data['settinglist'] = $setting_result;

        $role = $this->customlib->getStaffRole();

        $data['role_id'] = json_decode($role)->id;

        $record              = $this->input->post('data');



        $record_array        = json_decode($record);



        $fees_array = array();

        $student_session_ids1 = "";

        foreach ($record_array as $key => $value) {

            $fee_groups_feetype_id = $value->fee_groups_feetype_id;

            $fee_master_id         = $value->fee_master_id;

            $student_session_ids1  .= "," . $value->student_session_id;

            $fee_session_group_id  = $value->fee_session_group_id;

            $feeList               = $this->studentfeemaster_model->getDueFeeByFeeSessionGroupFeetype($fee_session_group_id, $fee_master_id, $fee_groups_feetype_id);

            $fees_array[]          = $feeList;
        }

        $student_session_ids = substr($student_session_ids1, 1);

        $discount_applied = $this->studentfeemaster_model->getAppliedDicountSiblings($student_session_ids);

        // echo "<pre>"; print_r($discount_applied); die();



        $data['discount_applied_ids'] = $discount_applied;

        $data['feearray'] = $fees_array;

        $data['student_session_id'] = $student_session_ids;

        $data['discount_not_applied'] = $this->getNotAppliedDiscountSiblings($student_session_ids);

        $result           = array(

            'view' => $this->load->view('studentfee/getcollectfeesiblings', $data, true),

        );



        $this->output->set_output(json_encode($result));
    }



    public function getNotAppliedDiscountSiblings($student_session_ids)

    {

        return $this->feediscount_model->getDiscountNotAppliedSiblings($student_session_ids);
    }
}
