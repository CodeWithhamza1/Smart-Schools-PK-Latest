<?php
if (!defined('BASEPATH')) { exit('No direct script access allowed'); }

/** Parallel simplified fee workspace; canonical financial save paths stay unchanged. */
class Feeworkspace extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function index() { redirect('admin/feeworkspace/collect'); }

    public function collect()
    {
        $this->requirePrivilege('collect_fees', 'can_view');
        $data = $this->baseData('collect', 'Collect Fees');
        $data['classlist'] = $this->class_model->get();
        $this->render('collect', $data);
    }

    public function searchStudents()
    {
        $this->requirePrivilege('collect_fees', 'can_view');
        $term = trim((string) $this->input->post('term', true));
        $class_id = (int) $this->input->post('class_id');
        $section_id = (int) $this->input->post('section_id');
        if ($term === '' && $class_id <= 0) return $this->json(array('status'=>'fail','message'=>'Enter an admission number/name or select a class.'), 422);

        $this->db->select('student_session.id AS student_session_id, students.id AS student_id, students.admission_no, students.firstname, students.middlename, students.lastname, students.guardian_name, students.guardian_phone, classes.class, sections.section')
            ->from('student_session')->join('students', 'students.id = student_session.student_id')
            ->join('classes', 'classes.id = student_session.class_id')->join('sections', 'sections.id = student_session.section_id')
            ->where('student_session.session_id', $this->setting_model->getCurrentSession())->where('students.is_active', 'yes');
        if ($class_id > 0) $this->db->where('student_session.class_id', $class_id);
        if ($section_id > 0) $this->db->where('student_session.section_id', $section_id);
        if ($term !== '') {
            $this->db->group_start()->like('students.admission_no', $term)->or_like('students.firstname', $term)
                ->or_like('students.middlename', $term)->or_like('students.lastname', $term)
                ->or_like('students.guardian_phone', $term)->group_end();
        }
        $rows = $this->db->order_by('students.firstname', 'ASC')->limit(100)->get()->result_array();
        return $this->json(array('status'=>'success','students'=>$rows));
    }

    public function student($student_session_id = 0)
    {
        $this->requirePrivilege('collect_fees', 'can_add');
        $student_session_id = (int) $student_session_id;
        $student = $this->student_model->getByStudentSession($student_session_id);
        if (empty($student) || (isset($student['session_id']) && (int)$student['session_id'] !== (int)$this->setting_model->getCurrentSession())) { show_404(); return; }
        $data = $this->baseData('collect', 'Collect Fees');
        $data['student'] = $student;
        $data['student_due_fee'] = $this->studentfeemaster_model->getStudentFees($student_session_id);
        $data['student_discount_fee'] = $this->feediscount_model->getStudentFeesDiscount($student_session_id);
        $this->load->model('accounts_model');
        $data['bank_accounts'] = $this->collectionAccounts($this->accounts_model->get());
        $data['sch_setting'] = $this->sch_setting_detail;
        $this->render('student', $data);
    }

    public function generate()
    {
        $this->requirePrivilege('fees_master', 'can_view');
        redirect('admin/feemaster/generateFee');
    }

    public function challans()
    {
        $this->requirePrivilege('collect_fees', 'can_view');
        $data = $this->baseData('challans', 'Challans');
        $data['classlist'] = $this->class_model->get();
        $this->render('challans', $data);
    }

    public function enquiry()
    {
        if (!$this->rbac->hasPrivilege('search_fees_payment','can_view') && !$this->rbac->hasPrivilege('search_due_fees','can_view') && !$this->rbac->hasPrivilege('fees_statement','can_view')) { access_denied(); return; }
        $data = $this->baseData('enquiry', 'Enquiry & Reports');
        $data['classlist'] = $this->class_model->get();
        $data['fee_types'] = $this->feesessiongroup_model->getFeesByGroup();
        $data['can_payment_search'] = $this->rbac->hasPrivilege('search_fees_payment','can_view');
        $data['can_due_search'] = $this->rbac->hasPrivilege('search_due_fees','can_view');
        $data['can_reports'] = $this->rbac->hasPrivilege('fees_statement','can_view');
        $this->render('enquiry', $data);
    }

    public function setup()
    {
        if (!$this->canViewSetup()) { access_denied(); return; }
        $data = $this->baseData('setup', 'Fee Setup');
        $data['fee_types'] = $this->feetype_model->get();
        $data['fee_groups'] = $this->feegroup_model->get();
        $data['fee_masters'] = $this->feesessiongroup_model->getFeesByGroup();
        $data['discounts'] = $this->feediscount_model->get();
        $data['session_name'] = $this->setting_model->getCurrentSessionName();
        $this->render('setup', $data);
    }

    public function saveSetup()
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST') { show_404(); return; }
        $kind = (string)$this->input->post('kind', true);
        try {
            if ($kind === 'type') {
                $this->requirePrivilege('fees_type','can_add');
                $name=trim((string)$this->input->post('name',true)); $code=trim((string)$this->input->post('code',true));
                if ($name==='' || $code==='') throw new Exception('Fee type name and code are required.');
                if (!$this->feetype_model->check_exists($name)) throw new Exception('This fee type already exists.');
                $this->feetype_model->add(array('type'=>$name,'code'=>$code,'description'=>$this->input->post('description',true)));
            } elseif ($kind === 'group') {
                $this->requirePrivilege('fees_group','can_add');
                $name=trim((string)$this->input->post('name',true)); if ($name==='') throw new Exception('Fee group name is required.');
                if (!$this->feegroup_model->check_exists($name)) throw new Exception('This fee group already exists.');
                $this->feegroup_model->add(array('name'=>$name,'description'=>$this->input->post('description',true)));
            } elseif ($kind === 'discount') {
                $this->requirePrivilege('fees_discount','can_add');
                $name=trim((string)$this->input->post('name',true)); $code=trim((string)$this->input->post('code',true)); $amount=$this->input->post('amount');
                if ($name==='' || $code==='' || !is_numeric($amount) || (float)$amount<0) throw new Exception('Enter a name, code and valid discount amount.');
                $this->feediscount_model->add(array('name'=>$name,'code'=>$code,'amount'=>(float)$amount,'description'=>$this->input->post('description',true)));
            } elseif ($kind === 'master') {
                $this->requirePrivilege('fees_master','can_add');
                $group_id=(int)$this->input->post('fee_groups_id'); $type_id=(int)$this->input->post('feetype_id'); $amount=$this->input->post('amount');
                $due_date=trim((string)$this->input->post('due_date',true)); $fine_type=(string)$this->input->post('account_type',true);
                if ($group_id<=0 || $type_id<=0 || !is_numeric($amount) || (float)$amount<0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$due_date)) throw new Exception('Select a group/type and enter valid amount and due date.');
                if ($this->feesessiongroup_model->check_data_exists($group_id,$type_id,0)) throw new Exception('This fee group and type combination already exists in the current session.');
                $fine_percentage=$this->input->post('fine_percentage'); $fine_amount=$this->input->post('fine_amount');
                if (!in_array($fine_type,array('none','percentage','fix'),true)) $fine_type='none';
                if (($fine_type==='percentage' && !is_numeric($fine_percentage)) || ($fine_type!=='none' && !is_numeric($fine_amount))) throw new Exception('Enter valid fine values.');
                $saved=$this->feesessiongroup_model->add_old(array('fee_groups_id'=>$group_id,'feetype_id'=>$type_id,'amount'=>(float)$amount,'due_date'=>$due_date,'session_id'=>$this->setting_model->getCurrentSession(),'fine_type'=>$fine_type,'fine_percentage'=>(float)$fine_percentage,'fine_amount'=>(float)$fine_amount));
                if ($saved===false) throw new Exception('Fee master was not saved.');
            } else throw new Exception('Unsupported setup operation.');
            $this->session->set_flashdata('fee_workspace_success','Saved successfully.');
        } catch (Exception $e) { $this->session->set_flashdata('fee_workspace_error',$e->getMessage()); }
        redirect('admin/feeworkspace/setup?tab='.rawurlencode($kind));
    }

    private function collectionAccounts($accounts)
    {
        $allowed=array();
        foreach ($accounts as $account) {
            $name=strtolower($account['account_name']); $number=isset($account['account_number'])?$account['account_number']:'';
            $cash_or_bank=strpos($name,'bank')!==false || strpos($name,'cash')!==false || substr($number,0,2)==='CA';
            $excluded=strpos($name,'charge')!==false || strpos($name,'profit')!==false || strpos($name,'expense')!==false || strpos($name,'tax')!==false;
            if ($cash_or_bank && !$excluded) $allowed[]=$account;
        }
        return empty($allowed)?$accounts:$allowed;
    }

    private function canViewSetup()
    {
        foreach (array('fees_type','fees_group','fees_master','fees_discount','fees_carry_forward') as $p) if ($this->rbac->hasPrivilege($p,'can_view')) return true;
        return false;
    }
    private function baseData($active,$title) { $this->session->set_userdata('top_menu','Fee Workspace'); $this->session->set_userdata('sub_menu','admin/feeworkspace/'.$active); return array('title'=>$title,'active_section'=>$active); }
    private function render($view,array $data) { $this->load->view('layout/header',$data); $this->load->view('admin/feeworkspace/'.$view,$data); $this->load->view('layout/footer',$data); }
    private function requirePrivilege($privilege,$action) { if (!$this->rbac->hasPrivilege($privilege,$action)) { access_denied(); exit; } }
    private function json(array $payload,$status=200) { return $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($payload)); }
}
