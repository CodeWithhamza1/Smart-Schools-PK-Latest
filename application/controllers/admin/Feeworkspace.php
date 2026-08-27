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
        $current_session = (int) $this->setting_model->getCurrentSession();
        if ($term === '' && $class_id <= 0) return $this->json(array('status'=>'fail','message'=>'Enter an admission number/name or select a class.'), 422);

        $this->db->select('student_session.id AS student_session_id, students.id AS student_id, students.admission_no, students.firstname, students.middlename, students.lastname, students.guardian_name, students.guardian_phone, classes.class, sections.section')
            ->from('student_session')->join('students', 'students.id = student_session.student_id')
            ->join('classes', 'classes.id = student_session.class_id')->join('sections', 'sections.id = student_session.section_id')
            ->where('student_session.session_id', $current_session)->where('students.is_active', 'yes');
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
        $data['can_reminders'] = $this->rbac->hasPrivilege('fees_reminder','can_view');
        $data['reminders'] = $data['can_reminders'] ? $this->feereminder_model->get() : array();
        $this->render('enquiry', $data);
    }

    public function findPayment()
    {
        $this->requirePrivilege('search_fees_payment','can_view');
        $payment_id=trim((string)$this->input->post('payment_id',true));
        if(!preg_match('/^(\d+)\/(\d+)$/',$payment_id,$m)) return $this->json(array('status'=>'fail','message'=>'Use receipt format invoice/sub-invoice, for example 125/1.'),422);
        $fee=$this->studentfeemaster_model->getFeeByInvoice((int)$m[1],(int)$m[2]);
        if(!$fee) return $this->json(array('status'=>'fail','message'=>'No payment was found for this receipt ID.'),404);
        $details=json_decode($fee->amount_detail,true); $detail=isset($details[$m[2]])?$details[$m[2]]:null;
        if(!$detail) return $this->json(array('status'=>'fail','message'=>'The receipt exists but its payment entry is unavailable.'),404);
        return $this->json(array('status'=>'success','payment'=>array('receipt'=>$payment_id,'student_session_id'=>(int)$fee->student_session_id,'admission_no'=>$fee->admission_no,'student'=>trim($fee->firstname.' '.$fee->middlename.' '.$fee->lastname),'class'=>$fee->class.' / '.$fee->section,'fee'=>$fee->name.' — '.$fee->type,'amount'=>(float)$detail['amount'],'discount'=>(float)$detail['amount_discount'],'fine'=>(float)$detail['amount_fine'],'date'=>$detail['date'],'mode'=>$detail['payment_mode'],'description'=>isset($detail['description'])?$detail['description']:'')));
    }

    public function findDueFees()
    {
        $this->requirePrivilege('search_due_fees','can_view');
        $pair=explode('-',(string)$this->input->post('fee_key',true)); $group_id=isset($pair[0])?(int)$pair[0]:0; $type_id=isset($pair[1])?(int)$pair[1]:0;
        $class_id=(int)$this->input->post('class_id'); $section_id=(int)$this->input->post('section_id');
        $current_session=(int)$this->setting_model->getCurrentSession();
        if($group_id<=0||$type_id<=0) return $this->json(array('status'=>'fail','message'=>'Select a valid fee item.'),422);
        $this->db->select('sfm.id fee_master_id,ss.id student_session_id,s.admission_no,s.firstname,s.middlename,s.lastname,c.class,sec.section,fgt.amount,fgt.due_date,sfd.amount_detail')
            ->from('student_fees_master sfm')->join('student_session ss','ss.id=sfm.student_session_id')->join('students s','s.id=ss.student_id')
            ->join('classes c','c.id=ss.class_id')->join('sections sec','sec.id=ss.section_id')->join('fee_groups_feetype fgt','fgt.id='.$this->db->escape($type_id).' AND fgt.fee_session_group_id=sfm.fee_session_group_id')
            ->join('student_fees_deposite sfd','sfd.student_fees_master_id=sfm.id AND sfd.fee_groups_feetype_id=fgt.id','left')
            ->where('ss.session_id',$current_session)->where('ss.id >',0)->where('s.is_active','yes')->where('sfm.fee_session_group_id',$group_id);
        if($class_id>0)$this->db->where('ss.class_id',$class_id); if($section_id>0)$this->db->where('ss.section_id',$section_id);
        $raw=$this->db->order_by('s.admission_no','ASC')->get()->result_array(); $map=array();
        foreach($raw as $row){$key=$row['fee_master_id'];if(!isset($map[$key])){$row['paid']=0;$row['discount']=0;$map[$key]=$row;}if(!empty($row['amount_detail'])){$a=json_decode($row['amount_detail'],true);if(is_array($a))foreach($a as $d){$map[$key]['paid']+=(float)(isset($d['amount'])?$d['amount']:0);$map[$key]['discount']+=(float)(isset($d['amount_discount'])?$d['amount_discount']:0);}}}
        $rows=array();foreach($map as $row){$row['balance']=max(0,(float)$row['amount']-$row['paid']-$row['discount']);unset($row['amount_detail']);if($row['balance']>0)$rows[]=$row;}
        return $this->json(array('status'=>'success','rows'=>$rows,'count'=>count($rows)));
    }

    public function reportSummary()
    {
        $this->requirePrivilege('fees_statement','can_view');
        $start=$this->validDate($this->input->post('start_date',true)); $end=$this->validDate($this->input->post('end_date',true));
        $current_session=(int)$this->setting_model->getCurrentSession();
        if(!$start||!$end||$start>$end||strtotime($end)-strtotime($start)>86400*366) return $this->json(array('status'=>'fail','message'=>'Select a valid date range of up to one year.'),422);
        $this->db->select('sfd.id,sfd.amount_detail,s.admission_no,s.firstname,s.middlename,s.lastname,c.class,sec.section,fg.name,ft.type')
            ->from('student_fees_deposite sfd')->join('student_fees_master sfm','sfm.id=sfd.student_fees_master_id')->join('student_session ss','ss.id=sfm.student_session_id')
            ->join('students s','s.id=ss.student_id')->join('classes c','c.id=ss.class_id')->join('sections sec','sec.id=ss.section_id')
            ->join('fee_groups_feetype fgt','fgt.id=sfd.fee_groups_feetype_id')->join('fee_groups fg','fg.id=fgt.fee_groups_id')->join('feetype ft','ft.id=fgt.feetype_id')
            ->where('ss.session_id',$current_session);
        $deposits=$this->db->get()->result_array();$rows=array();$totals=array('amount'=>0,'discount'=>0,'fine'=>0);
        foreach($deposits as $deposit){$items=json_decode($deposit['amount_detail'],true);if(!is_array($items))continue;foreach($items as $inv=>$d){$date=isset($d['date'])?$d['date']:'';if($date<$start||$date>$end)continue;$row=array('receipt'=>$deposit['id'].'/'.$inv,'date'=>$date,'admission_no'=>$deposit['admission_no'],'student'=>trim($deposit['firstname'].' '.$deposit['middlename'].' '.$deposit['lastname']),'class'=>$deposit['class'].' / '.$deposit['section'],'fee'=>$deposit['name'].' — '.$deposit['type'],'mode'=>isset($d['payment_mode'])?$d['payment_mode']:'','amount'=>(float)(isset($d['amount'])?$d['amount']:0),'discount'=>(float)(isset($d['amount_discount'])?$d['amount_discount']:0),'fine'=>(float)(isset($d['amount_fine'])?$d['amount_fine']:0));$rows[]=$row;$totals['amount']+=$row['amount'];$totals['discount']+=$row['discount'];$totals['fine']+=$row['fine'];}}
        usort($rows,function($a,$b){return strcmp($b['date'],$a['date']);});return $this->json(array('status'=>'success','rows'=>$rows,'totals'=>$totals,'count'=>count($rows)));
    }

    public function saveReminders()
    {
        $this->requirePrivilege('fees_reminder','can_edit'); $ids=$this->input->post('ids');$active=$this->input->post('active');$days=$this->input->post('days');
        if(!is_array($ids))return $this->json(array('status'=>'fail','message'=>'No reminder settings were supplied.'),422);$valid=array();foreach($this->feereminder_model->get() as $r)$valid[(int)$r->id]=true;$updates=array();
        foreach($ids as $id){$id=(int)$id;if(!isset($valid[$id]))continue;$day=isset($days[$id])?(int)$days[$id]:0;if($day<0||$day>365)return $this->json(array('status'=>'fail','message'=>'Reminder days must be between 0 and 365.'),422);$updates[]=array('id'=>$id,'is_active'=>isset($active[$id])?1:0,'day'=>$day);}
        if(!$this->feereminder_model->updatebatch($updates))return $this->json(array('status'=>'fail','message'=>'Reminder settings were not saved.'),500);return $this->json(array('status'=>'success','message'=>'Reminder settings saved.'));
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
        $data['can_fee_structure'] = $this->rbac->hasPrivilege('fees_group_assign', 'can_view');
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

    public function discountStudents()
    {
        $this->requirePrivilege('fees_discount_assign','can_view');$discount_id=(int)$this->input->post('discount_id');$class_id=(int)$this->input->post('class_id');$section_id=(int)$this->input->post('section_id');
        if($discount_id<=0||$class_id<=0)return $this->json(array('status'=>'fail','message'=>'Select a discount and class.'),422);
        if(!$this->feediscount_model->get($discount_id))return $this->json(array('status'=>'fail','message'=>'Discount not found.'),404);
        $rows=$this->feediscount_model->searchAssignFeeByClassSection($class_id,$section_id?:null,$discount_id);
        return $this->json(array('status'=>'success','students'=>$rows));
    }

    public function saveDiscountStudents()
    {
        $this->requirePrivilege('fees_discount_assign','can_view');$discount_id=(int)$this->input->post('discount_id');$class_id=(int)$this->input->post('class_id');$section_id=(int)$this->input->post('section_id');$selected=$this->input->post('student_session_ids');if(!is_array($selected))$selected=array();$selected=array_values(array_unique(array_map('intval',$selected)));
        if($discount_id<=0||$class_id<=0||!$this->feediscount_model->get($discount_id))return $this->json(array('status'=>'fail','message'=>'Invalid discount selection.'),422);
        $scope=$this->feediscount_model->searchAssignFeeByClassSection($class_id,$section_id?:null,$discount_id);$allowed=array();$previous=array();foreach($scope as $row){$sid=(int)$row['student_session_id'];$allowed[$sid]=true;if((int)$row['student_fees_discount_id']>0)$previous[]=$sid;}
        foreach($selected as $sid)if(!isset($allowed[$sid]))return $this->json(array('status'=>'fail','message'=>'A selected student is outside the current class/section.'),422);
        $this->db->trans_begin();$missing=array_values(array_diff($selected,$previous));if($missing){$insert=array();foreach($missing as $sid)$insert[]=array('student_session_id'=>$sid,'fees_discount_id'=>$discount_id);foreach(array_chunk($insert,200) as $chunk)$this->db->insert_batch('student_fees_discounts',$chunk);}$remove=array_values(array_diff($previous,$selected));if($remove)$this->feediscount_model->deletedisstd($discount_id,$remove);
        if($this->db->trans_status()===false){$this->db->trans_rollback();return $this->json(array('status'=>'fail','message'=>'Discount assignments were rolled back.'),500);}$this->db->trans_commit();return $this->json(array('status'=>'success','message'=>'Discount assignments saved.','selected'=>count($selected),'removed'=>count($remove)));
    }

    public function previousBalances()
    {
        $this->requirePrivilege('fees_carry_forward','can_view');$class_id=(int)$this->input->post('class_id');$section_id=(int)$this->input->post('section_id');if($class_id<=0||$section_id<=0)return $this->json(array('status'=>'fail','message'=>'Select a class and section.'),422);
        try{$result=$this->calculatePreviousBalances($class_id,$section_id);return $this->json(array('status'=>'success','students'=>$result['students'],'previous_session'=>$result['previous_session'],'is_update'=>$result['is_update']));}catch(Exception $e){return $this->json(array('status'=>'fail','message'=>$e->getMessage()),422);}
    }

    public function savePreviousBalances()
    {
        // The legacy carry-forward controller authorizes this operation with
        // can_view; preserve that established RBAC contract in the new UI.
        $this->requirePrivilege('fees_carry_forward','can_view');$class_id=(int)$this->input->post('class_id');$section_id=(int)$this->input->post('section_id');$due_date=$this->validDate($this->input->post('due_date',true));$amounts=$this->input->post('amounts');if(!is_array($amounts))$amounts=array();
        if(!$due_date)return $this->json(array('status'=>'fail','message'=>'Select a valid due date.'),422);
        try{$result=$this->calculatePreviousBalances($class_id,$section_id);$allowed=array();foreach($result['students'] as $s)$allowed[(int)$s['student_session_id']]=true;$data=array();foreach($amounts as $sid=>$amount){$sid=(int)$sid;if(!isset($allowed[$sid])||!is_numeric($amount)||(float)$amount<0)return $this->json(array('status'=>'fail','message'=>'Invalid student or balance amount.'),422);$data[]=array('student_session_id'=>$sid,'amount'=>(float)$amount,'is_system'=>1,'fee_session_group_id'=>0);}
            if(!$data)return $this->json(array('status'=>'fail','message'=>'Select at least one student balance.'),422);if(!$this->studentfeemaster_model->addPreviousBal($data,$due_date))return $this->json(array('status'=>'fail','message'=>'Balance transfer was rolled back.'),500);return $this->json(array('status'=>'success','message'=>'Previous balances transferred successfully.','updated'=>count($data)));
        }catch(Exception $e){return $this->json(array('status'=>'fail','message'=>$e->getMessage()),422);}
    }

    private function calculatePreviousBalances($class_id,$section_id)
    {
        if($class_id<=0||$section_id<=0)throw new Exception('Select a class and section.');$this->load->config('ci-blog');$current=$this->setting_model->getCurrentSession();$previous=$this->session_model->getPreSession($current);if(!$previous)throw new Exception('No previous academic session is configured.');
        $list=$this->student_model->getPreviousSessionStudent($previous->id,$class_id,$section_id);$students=array();$current_ids=array();foreach($list as $s){$students[(int)$s->current_student_session_id]=array('student_session_id'=>(int)$s->current_student_session_id,'previous_student_session_id'=>(int)$s->previous_student_session_id,'admission_no'=>$s->admission_no,'name'=>trim($s->firstname.' '.$s->middlename.' '.$s->lastname),'father_name'=>$s->father_name,'balance'=>0);$current_ids[]=(int)$s->current_student_session_id;}
        $existing=array();if($current_ids){$group=$this->config->item('ci_balance_group');$this->db->select('sfm.student_session_id,sfm.amount')->from('student_fees_master sfm')->join('fee_session_groups fsg','fsg.id=sfm.fee_session_group_id')->join('fee_groups fg','fg.id=fsg.fee_groups_id')->where('fsg.session_id',$current)->where('fg.name',$group)->where_in('sfm.student_session_id',$current_ids);foreach($this->db->get()->result_array() as $r)$existing[(int)$r['student_session_id']]=(float)$r['amount'];}
        if($existing){foreach($students as $sid=>&$s)$s['balance']=isset($existing[$sid])?$existing[$sid]:0;unset($s);}else{
            // Bulk-load all prior-session fee rows and settlements once. This
            // replaces the legacy per-student/per-fee query loop while keeping
            // its admission-date and system-balance rules.
            $previous_ids=array();$current_by_previous=array();foreach($students as $current_sid=>$s){$previous_ids[]=$s['previous_student_session_id'];$current_by_previous[$s['previous_student_session_id']]=$current_sid;}
            if($previous_ids){$this->db->select('sfm.id fee_master_id,sfm.student_session_id,sfm.is_system,sfm.amount system_amount,fgt.id fee_row_id,fgt.amount,sfd.amount_detail')
                ->from('student_fees_master sfm')->join('student_session ss','ss.id=sfm.student_session_id')->join('students st','st.id=ss.student_id')
                ->join('fee_groups_feetype fgt','fgt.fee_session_group_id=sfm.fee_session_group_id AND fgt.due_date >= st.admission_date')
                ->join('student_fees_deposite sfd','sfd.student_fees_master_id=sfm.id AND sfd.fee_groups_feetype_id=fgt.id','left')->where_in('sfm.student_session_id',$previous_ids);
                $fee_map=array();foreach($this->db->get()->result_array() as $row){$key=$row['fee_master_id'].':'.$row['fee_row_id'];if(!isset($fee_map[$key]))$fee_map[$key]=array('previous_sid'=>(int)$row['student_session_id'],'amount'=>(int)$row['is_system']!==0?(float)$row['system_amount']:(float)$row['amount'],'paid'=>0,'discount'=>0);$details=json_decode($row['amount_detail'],true);if(is_array($details))foreach($details as $d){$fee_map[$key]['paid']+=(float)(isset($d['amount'])?$d['amount']:0);$fee_map[$key]['discount']+=(float)(isset($d['amount_discount'])?$d['amount_discount']:0);}}
                foreach($fee_map as $fee){$current_sid=$current_by_previous[$fee['previous_sid']];$students[$current_sid]['balance']+=max(0,$fee['amount']-$fee['paid']-$fee['discount']);}}
            }
        return array('students'=>array_values($students),'previous_session'=>isset($previous->session)?$previous->session:$previous->id,'is_update'=>!empty($existing));
    }

    private function validDate($value){$value=trim((string)$value);$d=DateTime::createFromFormat('Y-m-d',$value);return $d&&$d->format('Y-m-d')===$value?$value:false;}

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
        foreach (array('fees_type','fees_group','fees_master','fees_discount','fees_carry_forward','fees_group_assign') as $p) if ($this->rbac->hasPrivilege($p,'can_view')) return true;
        return false;
    }
    private function baseData($active,$title) { $this->session->set_userdata('top_menu','Fee Workspace'); $this->session->set_userdata('sub_menu','admin/feeworkspace/'.$active); return array('title'=>$title,'active_section'=>$active); }
    private function render($view,array $data) { $this->load->view('layout/header',$data); $this->load->view('admin/feeworkspace/'.$view,$data); $this->load->view('layout/footer',$data); }
    private function requirePrivilege($privilege,$action) { if (!$this->rbac->hasPrivilege($privilege,$action)) { access_denied(); exit; } }
    private function json(array $payload,$status=200) { return $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($payload)); }
}
