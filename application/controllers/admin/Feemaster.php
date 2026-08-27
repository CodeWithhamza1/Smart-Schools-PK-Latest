<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Feemaster extends Admin_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model('fee_generation_model');
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    /**
     * Unified monthly fee profile and generation workspace.
     */
    public function generateFee()
    {
        if (!$this->rbac->hasPrivilege('fees_master', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Fee Workspace');
        $this->session->set_userdata('sub_menu', 'admin/feemaster/generateFee');

        $settings = $this->setting_model->get();
        $fee_due_days = !empty($settings) && isset($settings[0]['fee_due_days']) ? (int) $settings[0]['fee_due_days'] : 0;
        $default_due_date = date('Y-m-d', strtotime('+' . max(0, $fee_due_days) . ' day'));
        $operation_token = bin2hex(random_bytes(16));
        $this->session->set_userdata('fee_generation_operation_token', $operation_token);

        $data = array(
            'title'             => 'Generate Fee',
            'classlist'         => $this->class_model->get(),
            'feetypeList'       => $this->feetype_model->get(),
            'current_session'   => $this->setting_model->getCurrentSession(),
            'current_session_name' => $this->setting_model->getCurrentSessionName(),
            'default_year'      => (int) date('Y', strtotime($default_due_date)),
            'default_month'     => (int) date('n', strtotime($default_due_date)),
            'default_due_date'  => $default_due_date,
            'operation_token'   => $operation_token,
        );

        $this->load->view('layout/header', $data);
        $this->load->view('admin/feemaster/generate_fee', $data);
        $this->load->view('layout/footer', $data);
    }

    public function loadGenerationStudents()
    {
        if (!$this->rbac->hasPrivilege('fees_master', 'can_view')) {
            return $this->jsonResponse(array('status' => 'fail', 'errors' => array('Access denied.')), 403);
        }

        try {
            $payload = $this->validatedGenerationPayload(false, false);
            $students = $this->fee_generation_model->getWorkspaceStudents(
                $payload['class_id'],
                $payload['section_id'],
                $payload['year'],
                $payload['month']
            );
            return $this->jsonResponse(array('status' => 'success', 'students' => $students));
        } catch (Exception $e) {
            return $this->jsonResponse(array('status' => 'fail', 'errors' => array($e->getMessage())), 422);
        }
    }

    public function updateGenerationFees()
    {
        if (!$this->rbac->hasPrivilege('fees_master', 'can_edit')) {
            return $this->jsonResponse(array('status' => 'fail', 'errors' => array('You do not have permission to update fees.')), 403);
        }

        try {
            $payload = $this->validatedGenerationPayload(true, true, 'update');
            $result = $this->fee_generation_model->updateFees(
                $payload,
                $this->customlib->getStaffID(),
                $payload['request_id']
            );
            return $this->jsonResponse($result, $result['status'] === 'success' ? 200 : 422);
        } catch (Exception $e) {
            return $this->jsonResponse(array('status' => 'fail', 'errors' => array($e->getMessage())), 422);
        }
    }

    public function runGeneration()
    {
        if (!$this->rbac->hasPrivilege('fees_master', 'can_add')) {
            return $this->jsonResponse(array('status' => 'fail', 'errors' => array('You do not have permission to generate fees.')), 403);
        }

        try {
            $payload = $this->validatedGenerationPayload(true, true, 'generate');
            $result = $this->fee_generation_model->generateFees(
                $payload,
                $this->customlib->getStaffID(),
                $payload['request_id']
            );
            return $this->jsonResponse($result, $result['status'] === 'success' ? 200 : 422);
        } catch (Exception $e) {
            return $this->jsonResponse(array('status' => 'fail', 'errors' => array($e->getMessage())), 422);
        }
    }

    private function validatedGenerationPayload($require_students, $require_fees, $expected_action = null)
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') {
            throw new Exception('Only POST requests are allowed.');
        }
        $raw = $this->input->post('payload', false);
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            throw new Exception('Invalid fee request.');
        }

        $payload['class_id']  = isset($payload['class_id']) ? (int) $payload['class_id'] : 0;
        $payload['section_id'] = isset($payload['section_id']) ? (int) $payload['section_id'] : 0;
        $payload['year']       = isset($payload['year']) ? (int) $payload['year'] : 0;
        $payload['month']      = isset($payload['month']) ? (int) $payload['month'] : 0;
        $payload['due_date']   = isset($payload['due_date']) ? trim($payload['due_date']) : '';
        $payload['students']   = isset($payload['students']) && is_array($payload['students']) ? $payload['students'] : array();
        $payload['fees']       = isset($payload['fees']) && is_array($payload['fees']) ? $payload['fees'] : array();

        if ($payload['class_id'] <= 0 || $payload['year'] < 2000 || $payload['year'] > 2100 || $payload['month'] < 1 || $payload['month'] > 12) {
            throw new Exception('Select a valid class and billing month.');
        }
        $date = DateTime::createFromFormat('Y-m-d', $payload['due_date']);
        if (!$date || $date->format('Y-m-d') !== $payload['due_date']) {
            throw new Exception('Select a valid due date.');
        }
        if ((int) $date->format('Y') !== $payload['year'] || (int) $date->format('n') !== $payload['month']) {
            throw new Exception('Due date must be inside the selected billing month.');
        }
        if ($require_students && empty($payload['students'])) {
            throw new Exception('Select at least one student.');
        }
        if ($require_fees && empty($payload['fees'])) {
            throw new Exception('Add at least one fee type.');
        }

        if ($require_students || $require_fees) {
            $token = isset($payload['operation_token']) ? $payload['operation_token'] : '';
            $session_token = (string) $this->session->userdata('fee_generation_operation_token');
            if ($session_token === '' || !hash_equals($session_token, (string) $token)) {
                throw new Exception('This fee form has expired. Reload the page and try again.');
            }

            $request_nonce = isset($payload['request_nonce']) ? trim((string) $payload['request_nonce']) : '';

            // Keep pages opened before this fix working. Older JavaScript sent
            // "token-action-timestamp" as request_id; only retain its nonce.
            if (!preg_match('/^[0-9]{10,20}$/', $request_nonce)) {
                $legacy_request_id = isset($payload['request_id']) ? trim((string) $payload['request_id']) : '';
                if (preg_match('/([0-9]{10,20})$/', $legacy_request_id, $legacy_match)) {
                    $request_nonce = $legacy_match[1];
                }
            }

            // A malformed legacy page must not bypass idempotency or block a
            // safe operation. Identical submissions receive the same fallback.
            if (!preg_match('/^[0-9]{10,20}$/', $request_nonce)) {
                $request_nonce = substr(hash('sha256', json_encode($payload)), 0, 20);
            }

            $action_name = $expected_action ? $expected_action : 'fee-operation';
            $payload['request_nonce'] = $request_nonce;
            $payload['request_id'] = 'fee-' . hash('sha256', $session_token . '|' . $action_name . '|' . $request_nonce);
        }

        return $payload;
    }

    private function jsonResponse(array $data, $status_code = 200)
    {
        return $this->output
            ->set_status_header($status_code)
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    function index() {

        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/feemaster');
        $data['title'] = 'Feemaster List';
        $feegroup = $this->feegroup_model->get();
        $data['feegroupList'] = $feegroup;
        $feetype = $this->feetype_model->get();
        $data['feetypeList'] = $feetype;
 
        $feegroup_result = $this->feesessiongroup_model->getFeesByGroup();
        $data['feemasterList'] = $feegroup_result;

        $this->form_validation->set_rules('feetype_id', $this->lang->line('feetype'), 'required');
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required|numeric');

        $this->form_validation->set_rules(
                'fee_groups_id', $this->lang->line('feegroup'), array(
            'required',
            array('check_exists', array($this->feesessiongroup_model, 'valid_check_exists'))
                )
        );

        if(isset($_POST['account_type'] ) && $_POST['account_type'] =='fix'){
            $this->form_validation->set_rules('fine_amount', $this->lang->line('fine') . " " . $this->lang->line('amount'), 'required|numeric');
            $this->form_validation->set_rules('due_date', $this->lang->line('due_date'), 'trim|required|xss_clean');

        }elseif(isset($_POST['account_type']) && ($_POST['account_type']=='percentage')){
            $this->form_validation->set_rules('fine_percentage', $this->lang->line('percentage'), 'required|numeric');
            $this->form_validation->set_rules('fine_amount', $this->lang->line('fine') . " " . $this->lang->line('amount'), 'required|numeric');
             $this->form_validation->set_rules('due_date', $this->lang->line('due_date'), 'trim|required|xss_clean');
        }

        if ($this->form_validation->run() == FALSE) {
            
        } else {


            $insert_array = array(
                'fee_groups_id' => $this->input->post('fee_groups_id'),
                'feetype_id' => $this->input->post('feetype_id'),
                'amount' => $this->input->post('amount'),
                'due_date' => $this->customlib->dateFormatToYYYYMMDD($this->input->post('due_date')),
                'session_id' => $this->setting_model->getCurrentSession(),
                'fine_type' => $this->input->post('account_type'),
                'fine_percentage' => $this->input->post('fine_percentage'),
                'fine_amount' => $this->input->post('fine_amount'),
            );
            /* old code copied
            $insert_array = array(
                'fee_groups_id' => $this->input->post('fee_groups_id'),
                'feetype_id' =>$this->input->post('feetype_id'),
                'amount' => $this->input->post('amount'),
                'due_date' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('due_date'))),
                'clnd_month' =>  date('m', $this->customlib->datetostrtotime($this->input->post('due_date'))),
                'year' =>  date('Y', $this->customlib->datetostrtotime($this->input->post('due_date'))),
                'session_id' => $this->setting_model->getCurrentSession(),
                'fine_type' => $this->input->post('account_type'),
                'fine_percentage' => $this->input->post('fine_percentage'),
                'fine_amount' => $this->input->post('fine_amount'),
            ); */

            $feegroup_result = $this->feesessiongroup_model->add_old($insert_array);
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('success_message') . '</div>');
            redirect('admin/feemaster/index');
        }
 
        $this->load->view('layout/header', $data);
        $this->load->view('admin/feemaster/feemasterList', $data);
        $this->load->view('layout/footer', $data);
    }

    function delete($id) {
        if (!$this->rbac->hasPrivilege('fees_master', 'can_delete')) {
            access_denied();
        }
        $data['title'] = 'Fees Master List';
        $this->feegrouptype_model->remove($id);
        redirect('admin/feemaster/index');
    }

    function deletegrp($id) {
        $data['title'] = 'Fees Master List';
        $this->feesessiongroup_model->remove($id);
        redirect('admin/feemaster');
    }

    function edit($id) {
        if (!$this->rbac->hasPrivilege('fees_master', 'can_edit')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/feemaster');
        $data['id'] = $id;
        $feegroup_type = $this->feegrouptype_model->get($id);
        $data['feegroup_type'] = $feegroup_type;
        $feegroup = $this->feegroup_model->get();
        $data['feegroupList'] = $feegroup;
        $feetype = $this->feetype_model->get();
        $data['feetypeList'] = $feetype;
        $feegroup_result = $this->feesessiongroup_model->getFeesByGroup();
        $data['feemasterList'] = $feegroup_result;
        $this->form_validation->set_rules('feetype_id', $this->lang->line('feetype'), 'required');
        $this->form_validation->set_rules('amount', $this->lang->line('amount'), 'required');
        $this->form_validation->set_rules(
                'fee_groups_id', $this->lang->line('feegroup'), array(
            'required',
            array('check_exists', array($this->feesessiongroup_model, 'valid_check_exists'))
                )
        );

        if(isset($_POST['account_type'] ) && $_POST['account_type'] =='fix'){
            $this->form_validation->set_rules('fine_amount', $this->lang->line('fine') . " " . $this->lang->line('amount'), 'required|numeric');
            $this->form_validation->set_rules('due_date', $this->lang->line('due_date'), 'trim|required|xss_clean');

        }elseif(isset($_POST['account_type']) && ($_POST['account_type']=='percentage')){
            $this->form_validation->set_rules('fine_percentage', $this->lang->line('percentage'), 'required|numeric');
            $this->form_validation->set_rules('fine_amount', $this->lang->line('fine') . " " . $this->lang->line('amount'), 'required|numeric');
             $this->form_validation->set_rules('due_date', $this->lang->line('due_date'), 'trim|required|xss_clean');
        }
        if ($this->form_validation->run() == FALSE) {
            $this->load->view('layout/header', $data);
            $this->load->view('admin/feemaster/feemasterEdit', $data);
            $this->load->view('layout/footer', $data);
        } else {
            $insert_array = array(
                'id' => $this->input->post('id'),
                'feetype_id' => $this->input->post('feetype_id'),
                'due_date' => $this->customlib->dateFormatToYYYYMMDD($this->input->post('due_date')),
                'amount' => $this->input->post('amount'),
                'fine_type' => $this->input->post('account_type'),
                'fine_percentage' => $this->input->post('fine_percentage'),
                'fine_amount' => $this->input->post('fine_amount'),
            );
            
            $feegroup_result = $this->feegrouptype_model->add($insert_array);

            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">' . $this->lang->line('update_message') . '</div>');
            redirect('admin/feemaster/index');
        }
    }

    function assign($id) {
        if (!$this->rbac->hasPrivilege('fees_group_assign', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/feemaster');
        $data['id'] = $id;
        $data['title'] = 'student fees';
        $class = $this->class_model->get();
        $data['classlist'] = $class;
        $feegroup_result = $this->feesessiongroup_model->getFeesByGroup($id);
        $data['feegroupList'] = $feegroup_result;
        $data['adm_auto_insert'] = $this->sch_setting_detail->adm_auto_insert;
        $data['sch_setting'] = $this->sch_setting_detail;

        $genderList = $this->customlib->getGender();
        $data['genderList'] = $genderList;
        $RTEstatusList = $this->customlib->getRteStatus();
        $data['RTEstatusList'] = $RTEstatusList;

        $category = $this->category_model->get();
        $data['categorylist'] = $category;


        if ($this->input->server('REQUEST_METHOD') == 'POST') {

            $data['category_id'] = $this->input->post('category_id');
            $data['gender'] = $this->input->post('gender');
            $data['rte_status'] = $this->input->post('rte');
            $data['class_id'] = $this->input->post('class_id');
            $data['section_id'] = $this->input->post('section_id');

            $resultlist = $this->studentfeemaster_model->searchAssignFeeByClassSection($data['class_id'], $data['section_id'], $id, $data['category_id'], $data['gender'], $data['rte_status']);
            $data['resultlist'] = $resultlist;
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/feemaster/assign', $data);
        $this->load->view('layout/footer', $data);
    }

    function addYearly() {
        redirect('admin/feemaster/generateFee');
        return;
        // if(!$this->rbac->hasPrivilege('fees_master','can_add')){
        //        access_denied();
        //        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/feemaster/addYearly');
        $data['title'] = 'Feemaster List';
        $feegroup = $this->feegroup_model->get();
        $data['feegroupList'] = $feegroup;
        $feetype = $this->feetype_model->get();
        $data['feetypeList'] = $feetype;
        $feegroup_result = $this->feesessiongroup_model->getFeesByGroup();
        $calenderMonth = $this->feesessiongroup_model->getCalenderMonths();
        $data['feemasterList'] = $feegroup_result;
        $data['calenderMonth'] = $calenderMonth;

        $this->form_validation->set_rules('feetype_id', 'FeeType', 'required');
        $this->form_validation->set_rules('amount', 'Amount', 'required');
        $this->form_validation->set_rules('amount', 'Amount', 'required');
        $this->form_validation->set_rules('fee_start_month', 'Start Month', 'required');
        $this->form_validation->set_rules('fee_end_month', 'End Month', 'required');

        $this->form_validation->set_rules(
            'fee_groups_id', 'FeeGroup', array(
                'required',
                array('check_exists', array($this->feesessiongroup_model, 'valid_check_exists2'))
            )
        );
        if ($this->form_validation->run() == FALSE) {

        } else {
            /*$insert_array = array(
                 'fee_groups_id' => $this->input->post('fee_groups_id'),
                 'feetype_id' => $this->input->post('feetype_id'),
                 'amount' => $this->input->post('amount'),
                 'due_date' => date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('due_date'))),
                 'session_id' => $this->setting_model->getCurrentSession()
             );
             $feegroup_result = $this->feesessiongroup_model->add($insert_array);*/
            $startMonth = $this->input->post('fee_start_month');
            $endMonth = $this->input->post('fee_end_month');
            $year =$this->input->post('year');
            $sql ="select CLND_MONTH FROM financial_calender  WHERE CLND_MONTH between $startMonth and $endMonth and CLND_YEAR =$year";
            $query = $this->db->query($sql);
            $months = $query->result();
            //echo json_encode($sql);
            foreach ($months as $clndMonths)
            {
                $dateObj   = DateTime::createFromFormat('!m', $clndMonths->CLND_MONTH);
                $monthName = $dateObj->format('F');
                $insert_array = array(
                    'fee_groups_id' => $this->input->post('fee_groups_id'),
                    'feetype_id' =>$this->input->post('feetype_id'),
                    'amount' => $this->input->post('amount'),
                    'due_date' => $year.'-'.$clndMonths->CLND_MONTH.'-'.'10',
                    'clnd_month' => $clndMonths->CLND_MONTH,
                    'year' => $year,
                    'session_id' => $this->setting_model->getCurrentSession()
                );
                $feegroup_result1 = $this->feesessiongroup_model->add_old($insert_array);
            }
            foreach ($feegroup_result as $feegroup)
            {
                $parentid = $this->feesessiongroup_model->group_exists($this->input->post('fee_groups_id'));
                $feegroup_result = $this->feesessiongroup_model->getFeesByGroup($parentid);
                $group =$feegroup_result[0]->fee_groups_id;
                $result=$this->feesessiongroup_model->getFeeGroup($group);
                $insert_array = array(
                    'student_session_id' => $result[0]->student_id,
                    'fee_session_group_id' => $parentid,
                );
                $student_session_id[] = $result[0]->student_id;
                $student_sesssion_array = isset($student_session_id) ? $student_session_id : array();
                $student_ids[] =  $result[0]->student_id;
                $delete_student = array_diff($student_ids, $student_sesssion_array);
                $inserted_id = $this->studentfeemaster_model->add($insert_array);
                /* if (!empty($delete_student)) {
                     $this->studentfeemaster_model->delete($feegroup->id, $delete_student);
                 }*/
                //print_r($result);

            }
            $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Fees Master added successfully</div>');
            redirect('admin/feemaster/addYearly');
            //echo($feegroup_result);
        }

        $this->load->view('layout/header', $data);
        $this->load->view('admin/feemaster/feemasterListYearly', $data);
        $this->load->view('layout/footer', $data);
        //print_r($student_session_id);
    }
    function genrateFee($feetype_value_id,$clnd_month){
        redirect('admin/feemaster/generateFee');
        return;
        $year = date("Y");
        $date_new = $year."-".$clnd_month."-01";
        $date_new;
        $student_fees=$this->studentfeemaster_model->get_assignFeeRecord($feetype_value_id);
//        print_r($student_fees);
        $staff_record = $this->staff_model->get($this->customlib->getStaffID());
        $date=date("Y/m/d");
        foreach ($student_fees as $student_fees){
            if($student_fees->student_session_id !=""||$student_fees->student_session_id != null) {

                $fee_groups_feetype_id=$student_fees->fee_groups_feetype_id;
                $student_fees_master_id=$student_fees->student_fees_master_id;
                $student_session_id=$student_fees->student_session_id;
                $student_discounts=$this->feediscount_model->getByStudentSessionId($student_session_id);
                $checkFeerec=$this->studentfeemaster_model->checkFeerec($student_fees_master_id,$fee_groups_feetype_id);
                if($checkFeerec==0) {
                    if ($student_discounts) {
                        foreach ($student_discounts as $student_discount) {
                            $student_fees_discount_id = ($student_discount->student_fees_discount_id);
                            $amount_discount = ($student_discount->amount);
                            break;
                        }

                        $json_array = array(
                            'amount' => 0,
                            'date' => $date_new,
                            'amount_discount' => $amount_discount,
                            'amount_fine' => 0,
                            'description' => "",
                            'payment_mode' => "Cash",
                            'received_by' => $staff_record['id'],
                    );
                        $data = array(
                            'student_fees_master_id' => $student_fees_master_id,
                            'fee_groups_feetype_id' => $fee_groups_feetype_id,
                            'amount_detail' => $json_array,
                        );
                        $inserted_id = $this->studentfeemaster_model->fee_deposit($data, "", $student_fees_discount_id);
                        $updateGenrateFeeStatus = $this->studentfeemaster_model->updateGenrateFeeStatus($feetype_value_id);
                    }
                }

            }

        }
        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Fees Genrated successfully</div>');
        redirect('admin/feemaster/addYearly');

    }

    function genrateFeeFeeStructure($feetype_value_id,$clnd_month){
        if (!$this->rbac->hasPrivilege('fees_master', 'can_add')) {
            access_denied();
            return;
        }
//        echo $feetype_value_id . " - " . $clnd_month; die();
        $year = date("Y");
        $date_new = $year."-".$clnd_month."-01";
        $date_new;
        $student_fees=$this->studentfeemaster_model->get_assignFeeRecord($feetype_value_id);
//        print_r($student_fees ); die();
        $staff_record = $this->staff_model->get($this->customlib->getStaffID());
        $date=date("Y/m/d");
        foreach ($student_fees as $student_fees){
            if($student_fees->student_session_id !=""||$student_fees->student_session_id != null) {
                if ($student_fees->code == "Tuition") {

                    $fee_groups_feetype_id = $student_fees->fee_groups_feetype_id;
                    $student_fees_master_id = $student_fees->student_fees_master_id;
                    $student_session_id = $student_fees->student_session_id;
                    $student_discounts = $this->feediscount_model->getByStudentSessionId($student_session_id);

                    $checkFeerec = $this->studentfeemaster_model->checkFeerec($student_fees_master_id, $fee_groups_feetype_id);
                    if ($checkFeerec == 0) {
                        if ($student_discounts) {
                            foreach ($student_discounts as $student_discount) {
                                $student_fees_discount_id = ($student_discount->student_fees_discount_id);
                                $amount_discount = ($student_discount->amount);
                                break;
                            }

                            $json_array = array(
                                'amount' => 0,
                                'date' => $date_new,
                                'amount_discount' => $amount_discount,
                                'amount_fine' => 0,
                                'description' => "",
                                'payment_mode' => "Cash",
                                'received_by' => $staff_record['id'],
                            );
                            $data = array(
                                'student_fees_master_id' => $student_fees_master_id,
                                'fee_groups_feetype_id' => $fee_groups_feetype_id,
                                'amount_detail' => $json_array,
                            );
                            $inserted_id = $this->studentfeemaster_model->fee_deposit($data, "", $student_fees_discount_id);
                            $updateGenrateFeeStatus = $this->studentfeemaster_model->updateGenrateFeeStatus($feetype_value_id);
                        }
                    }

                }
            }

        }
//        $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Fees Genrated successfully</div>');
//        redirect('admin/feemaster/feeStructure');

    }

    function getStudentByClass()
    {
        $class = $this->input->get('class');
        $student_result= $this->student_model->searchByClassSection($class);
        echo json_encode($student_result);
    }
    function feeStructure()
    {
        if (!$this->rbac->hasPrivilege('fees_group_assign', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Fees Collection');
        $this->session->set_userdata('sub_menu', 'admin/feemaster/feeStructure');
        $data['title'] = 'Fees Structure';
        $student_result = $this->student_model->get();

        $class = $this->student_model->getClasses();
        $data['class']=$class;
        $data['studentlist']=$student_result;
        $feegroup = $this->feegroup_model->get();
        $data['feegroupList'] = $feegroup;
        $feetype = $this->feetype_model->get();
        $data['feetypeList'] = $feetype;
        $feegroup_result = $this->feesessiongroup_model->getFeesByGroup();
        $calenderMonth = $this->feesessiongroup_model->getCalenderMonths();
//        print_r($calenderMonth); die();
        $data['calenderMonth'] = $calenderMonth;
        $data['feemasterList'] = $feegroup_result;
        $this->load->view('layout/header', $data);
        $this->load->view('admin/feemaster/feestructure', $data);
        $this->load->view('layout/footer', $data);
        //print_r($class);
    }

    function addFeeStructure()
    {
        if (!$this->rbac->hasPrivilege('fees_master', 'can_add')) {
            access_denied();
            return;
        }
        try {
            // Input validation
            $class_id = $this->input->post('class');
            $section_id = $this->input->post('section'); // Optional section filter
            $student = $this->input->post('student');
            $session = $this->input->post('session_id');
            $totalPackage = $this->input->post('package');
            $method = $this->input->post('method');
            $feetype_id = $this->input->post('feetype_id');
            $amount = $this->input->post('amount');
            $due_date = $this->input->post('due_date');

            // Validate required fields
            if (empty($class_id) || empty($feetype_id) || empty($amount) || empty($due_date)) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Required fields are missing. Please fill all required fields.</div>');
                redirect('admin/feemaster/feeStructure');
                return;
            }

            // Validate arrays
            if (!is_array($feetype_id) || !is_array($amount) || !is_array($due_date)) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Invalid data format. Please try again.</div>');
                redirect('admin/feemaster/feeStructure');
                return;
            }

            // Validate array lengths match
            if (count($feetype_id) !== count($amount) || count($feetype_id) !== count($due_date)) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Fee type, amount, and due date arrays must have the same length.</div>');
                redirect('admin/feemaster/feeStructure');
                return;
            }

            // Validate amounts are numeric (allow empty values for auto-populated fields)
            foreach ($amount as $index => $amt) {
                // Skip validation for empty amounts - they will be auto-populated later
                if (empty($amt)) {
                    continue;
                }
                
                // If amount is provided, it must be numeric and non-negative
                if (!is_numeric($amt) || $amt < 0) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Invalid amount values. Please enter valid numeric amounts.</div>');
                    redirect('admin/feemaster/feeStructure');
                    return;
                }
            }

            // Validate dates
            foreach ($due_date as $date) {
                if (!strtotime($date)) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Invalid due date format. Please enter valid dates.</div>');
                    redirect('admin/feemaster/feeStructure');
                    return;
                }
            }

            // Start database transaction
            $this->db->trans_start();
            if($student == "ALL")
            {
                // Get students by class and optionally by section
                if (!empty($section_id)) {
                    $students = $this->student_model->searchByClassSection($class_id, $section_id);
                } else {
                    $students = $this->feemaster_model->getStudentsByClass($class_id);
                }
                
                if (empty($students)) {
                    $this->db->trans_rollback();
                    $section_msg = !empty($section_id) ? " for the selected class and section" : " for the selected class";
                    $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">No students found' . $section_msg . '.</div>');
                    redirect('admin/feemaster/feeStructure');
                    return;
                }

                $processed_count = 0;
                $error_count = 0;

                foreach ($students as $studentData)
                {
                    try {
                        // Handle different array structures from different methods
                        $student_id = isset($studentData['student_id']) ? $studentData['student_id'] : $studentData['id'];
                        
                        $get_amount = $this->feemaster_model->getFeesFromAdmission($student_id);
                        if (!$get_amount) {
                            log_message('error', "Failed to get fees from admission for student ID: $student_id");
                            $error_count++;
                            continue;
                        }
                        
                        $fee_amount = isset($get_amount['monthly_fee']) ? $get_amount['monthly_fee'] : 0;
                        $admission_amount = isset($get_amount['admission_fee']) ? $get_amount['admission_fee'] : 0;
                        
                        $student_result = $this->feegroup_model->getGroupStudents($student_id);
                        if (empty($student_result)) {
                            log_message('error', "No group students found for student ID: $student_id");
                            $error_count++;
                            continue;
                        }
                        $data = array(
                            'name' => $student_result[0]->firstname . ' ' . $student_result[0]->lastname . '-' . $student_result[0]->class,
                            'description' => '',
                            'is_active' => 1,
                            'student_id' => $student_result[0]->id
                        );
                        
                        $studentID = $this->feegroup_model->getPreviousStudents($student_result[0]->id);
                        if ($studentID && is_array($studentID) && count($studentID) > 0) {
                            $groupID = $studentID['id'];
                        } else {
                            $groupID = $this->feegroup_model->add($data);
                            if (!$groupID) {
                                log_message('error', "Failed to add fee group for student ID: $student_id");
                                $error_count++;
                                continue;
                            }
                        }
                        $inserted_ids = array();
                        for ($i = 0; $i < count($feetype_id); $i++) {
                            // Validate array bounds
                            if (!isset($feetype_id[$i]) || !isset($amount[$i]) || !isset($due_date[$i])) {
                                log_message('error', "Missing data at index $i for student ID: $student_id");
                                continue;
                            }
                            
                            $feeCode = $this->feetype_model->get($feetype_id[$i]);
                            if (!$feeCode) {
                                log_message('error', "Invalid fee type ID: " . $feetype_id[$i] . " for student ID: $student_id");
                                continue;
                            }
                            
                            $feeCode = $feeCode['code'];
                            $FeeAmount = $amount[$i];
                            
                            // Auto-populate amounts for Tuition and Admission fees
                            if ($feeCode == "Tuition") {
                                $FeeAmount = $fee_amount;
                            } elseif ($feeCode == "Admission") {
                                $FeeAmount = $admission_amount;
                            }
                            
                            // If amount is still empty or 0, set a default value
                            if (empty($FeeAmount) || $FeeAmount == 0) {
                                $FeeAmount = 0; // Set to 0 for empty amounts
                            }
                            
                            $time = strtotime($due_date[$i]);
                            if ($time === false) {
                                log_message('error', "Invalid date format: " . $due_date[$i] . " for student ID: $student_id");
                                continue;
                            }
                            
                            $month = date("m", $time);
                            $year = date("Y", $time);
                    $insert_array = array(
                        'fee_groups_id' => $groupID,
                        'feetype_id' => $feetype_id[$i],
                        'amount' => $FeeAmount,
                        'due_date' => $due_date[$i],
                        'session_id' => $this->setting_model->getCurrentSession(),
                        'clnd_month' => $month,
                        'year' => $year,
                        'Session' => $session,
                        'Total_Package' => 0,
                        'Method' => $method,
                    );
                            $inserted_fee = $this->feesessiongroup_model->add_old($insert_array);
                            if ($inserted_fee > 0) {
                                $inserted_ids[] = array(
                                    'inserted_fee' => $inserted_fee,
                                    'month' => $month
                                );
                            }
                        }
                        if (!empty($inserted_ids)) {
                            $feegroup_result = $this->feesessiongroup_model->getFeesByGroup();
                            $parentid = $this->feesessiongroup_model->group_exists($groupID);
                            $feegroup_result = $this->feesessiongroup_model->getFeesByGroup($parentid);
                            
                            if (!empty($feegroup_result)) {
                                $group = $feegroup_result[0]->fee_groups_id;
                                $result = $this->feesessiongroup_model->getFeeGroup($group);
                                
                                if (!empty($result)) {
                                    $insert_array = array(
                                        'student_session_id' => $result[0]->id,
                                        'fee_session_group_id' => $parentid,
                                    );
                                    
                                    $inserted_id = $this->studentfeemaster_model->add($insert_array);
                                    if ($inserted_id) {
                                        for ($i = 0; $i < count($inserted_ids); $i++) {
                                            $this->genrateFeeFeeStructure($inserted_ids[$i]['inserted_fee'], $inserted_ids[$i]['month']);
                                        }
                                        $processed_count++;
                                    } else {
                                        log_message('error', "Failed to add student fee master for student ID: $student_id");
                                        $error_count++;
                                    }
                                } else {
                                    log_message('error', "No fee group found for student ID: $student_id");
                                    $error_count++;
                                }
                            } else {
                                log_message('error', "No fee group result for student ID: $student_id");
                                $error_count++;
                            }
                        }
                        
                    } catch (Exception $e) {
                        log_message('error', "Error processing student ID $student_id: " . $e->getMessage());
                        $error_count++;
                        continue;
                    }
                }
                
                $this->db->trans_complete();
                
                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Database transaction failed. Please try again.</div>');
                    redirect('admin/feemaster/feeStructure');
                    return;
                }
                
                if ($error_count > 0) {
                    $this->session->set_flashdata('msg', '<div class="alert alert-warning text-left">Fee structure processed for ' . $processed_count . ' students. ' . $error_count . ' students had errors. Please check the logs for details.</div>');
                } else {
                    $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Fees Structure added successfully for ' . $processed_count . ' students.</div>');
                }
                
            } else {
                // Single student processing
                $student_result = $this->feegroup_model->getGroupStudents($student);
                if (empty($student_result)) {
                    $this->db->trans_rollback();
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Student not found or invalid student ID.</div>');
                    redirect('admin/feemaster/feeStructure');
                    return;
                }
                $data = array(
                    'name' => $student_result[0]->firstname . ' ' . $student_result[0]->lastname . '-' . $student_result[0]->class,
                    'description' => '',
                    'is_active' => 1,
                    'student_id' => $student_result[0]->id
                );
                
                $studentID = $this->feegroup_model->getPreviousStudents($student_result[0]->id);
                if (count($studentID) > 0) {
                    $groupID = $studentID['id'];
                } else {
                    $groupID = $this->feegroup_model->add($data);
                    if (!$groupID) {
                        $this->db->trans_rollback();
                        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Failed to create fee group for student.</div>');
                        redirect('admin/feemaster/feeStructure');
                        return;
                    }
                }

                $inserted_ids = array();
                for ($i = 0; $i < count($feetype_id); $i++) {
                    if (!isset($feetype_id[$i]) || !isset($amount[$i]) || !isset($due_date[$i])) {
                        log_message('error', "Missing data at index $i for single student processing");
                        continue;
                    }
                    
                    $feeCode = $this->feetype_model->get($feetype_id[$i]);
                    if (!$feeCode) {
                        log_message('error', "Invalid fee type ID: " . $feetype_id[$i]);
                        continue;
                    }
                    
                    $feeCode = $feeCode['code'];
                    $FeeAmount = $amount[$i];
                    
                    $get_amount = $this->feemaster_model->getFeesFromAdmission($student);
                    if ($get_amount) {
                        $fee_amount = isset($get_amount['monthly_fee']) ? $get_amount['monthly_fee'] : 0;
                        $admission_amount = isset($get_amount['admission_fee']) ? $get_amount['admission_fee'] : 0;
                        
                        // Auto-populate amounts for Tuition and Admission fees
                        if ($feeCode == "Tuition") {
                            $FeeAmount = $fee_amount;
                        } elseif ($feeCode == "Admission") {
                            $FeeAmount = $admission_amount;
                        }
                    }
                    
                    // If amount is still empty or 0, set a default value
                    if (empty($FeeAmount) || $FeeAmount == 0) {
                        $FeeAmount = 0; // Set to 0 for empty amounts
                    }
                    
                    $time = strtotime($due_date[$i]);
                    if ($time === false) {
                        log_message('error', "Invalid date format: " . $due_date[$i]);
                        continue;
                    }
                    
                    $month = date("m", $time);
                    $year = date("Y", $time);
                    
                    $insert_array = array(
                        'fee_groups_id' => $groupID,
                        'feetype_id' => $feetype_id[$i],
                        'amount' => $FeeAmount,
                        'due_date' => $due_date[$i],
                        'session_id' => $this->setting_model->getCurrentSession(),
                        'clnd_month' => $month,
                        'year' => $year,
                        'Session' => $session,
                        'Total_Package' => 0,
                        'Method' => $method,
                    );
                    
                    $inserted_fee = $this->feesessiongroup_model->add_old($insert_array);
                    if ($inserted_fee > 0) {
                        $inserted_ids[] = array(
                            'inserted_fee' => $inserted_fee,
                            'month' => $month
                        );
                    }
                }
                if (!empty($inserted_ids)) {
                    $feegroup_result = $this->feesessiongroup_model->getFeesByGroup();
                    $parentid = $this->feesessiongroup_model->group_exists($groupID);
                    $feegroup_result = $this->feesessiongroup_model->getFeesByGroup($parentid);
                    
                    if (!empty($feegroup_result)) {
                        $group = $feegroup_result[0]->fee_groups_id;
                        $result = $this->feesessiongroup_model->getFeeGroup($group);
                        
                        if (!empty($result)) {
                            $insert_array = array(
                                'student_session_id' => $result[0]->id,
                                'fee_session_group_id' => $parentid,
                            );
                            
                            $inserted_id = $this->studentfeemaster_model->add($insert_array);
                            if ($inserted_id) {
                                for ($i = 0; $i < count($inserted_ids); $i++) {
                                    $this->genrateFeeFeeStructure($inserted_ids[$i]['inserted_fee'], $inserted_ids[$i]['month']);
                                }
                            } else {
                                $this->db->trans_rollback();
                                $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Failed to assign fees to student.</div>');
                                redirect('admin/feemaster/feeStructure');
                                return;
                            }
                        } else {
                            $this->db->trans_rollback();
                            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Failed to get fee group information.</div>');
                            redirect('admin/feemaster/feeStructure');
                            return;
                        }
                    } else {
                        $this->db->trans_rollback();
                        $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Failed to get fee group result.</div>');
                        redirect('admin/feemaster/feeStructure');
                        return;
                    }
                }
                
                $this->db->trans_complete();
                
                if ($this->db->trans_status() === FALSE) {
                    $this->db->trans_rollback();
                    $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">Database transaction failed. Please try again.</div>');
                    redirect('admin/feemaster/feeStructure');
                    return;
                }
                
                $section_info = !empty($section_id) ? " for the selected section" : "";
                $this->session->set_flashdata('msg', '<div class="alert alert-success text-left">Fees Structure added successfully' . $section_info . '.</div>');
            }
            
            redirect('admin/feemaster/feeStructure');
            
        } catch (Exception $e) {
            $this->db->trans_rollback();
            log_message('error', 'Fee structure assignment error: ' . $e->getMessage());
            $this->session->set_flashdata('msg', '<div class="alert alert-danger text-left">An error occurred while processing the fee structure. Please try again or contact the administrator.</div>');
            redirect('admin/feemaster/feeStructure');
        }
    }

    function getFeesFromAdmission()
    {
        $student_id = $this->input->post('student_id');
        $fees_code= $this->input->post('fees_code');
        $mod = $this->feemaster_model->getFeesFromAdmission($student_id);
        echo json_encode($mod);
//        return $mod;
    }

    function getSectionsByClass()
    {
        $class_id = $this->input->get('class_id');
        $sections = $this->section_model->getClassBySectionAll($class_id);
        echo json_encode($sections);
    }

    function getStudentByClassSection()
    {
        $class_id = $this->input->get('class_id');
        $section_id = $this->input->get('section_id');
        $students = $this->student_model->searchByClassSection($class_id, $section_id);
        echo json_encode($students);
    }

}

?>
