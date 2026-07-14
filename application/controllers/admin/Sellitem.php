<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class Sellitem extends Admin_Controller {

    function __construct() {
        parent::__construct();
        $this->load->helper('form');
    }

    public function index() {
        if (!$this->rbac->hasPrivilege('issue_item', 'can_view')) {
            access_denied();
        }
        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'sellitem/index');
        $data['title'] = 'Add Issue item';
        $data['title_list'] = 'Recent Issue items';
        $this->load->view('layout/header', $data);
        $this->load->view('admin/sellitem/sellitemList', $data);
        $this->load->view('layout/footer', $data);
    }

    public function create() {
        $this->session->set_userdata('top_menu', 'Inventory');
        $this->session->set_userdata('sub_menu', 'sellitem/index');
        $data['title'] = 'Add Issue item';
        $data['title_list'] = 'Recent Issue items';
        $roles = $this->role_model->get();
        $data['roles'] = $roles;

        $itemcategory = $this->itemcategory_model->get();
        $data['itemcatlist'] = $itemcategory;
        $data['staff']['employee_id'] = $this->staff_model->getEmployeeId($this->session->userdata('admin')['id']);
        $data['staff']['name'] = $this->session->userdata('admin')['username'];

        $this->load->view('layout/header', $data);
        $this->load->view('admin/sellitem/sellitemCreate', $data);
        $this->load->view('layout/footer', $data);
    }

    function add() {

//        $this->form_validation->set_rules('account_type', $this->lang->line('account_type'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('issue_to', $this->lang->line('issue_to'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('issue_by', $this->lang->line('issue_by'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('issue_date', $this->lang->line('issue_date'), 'required|trim|xss_clean');

//        $this->form_validation->set_rules('item_category_id', $this->lang->line('item_category'), 'required|trim|xss_clean');
        $this->form_validation->set_rules('item_id', $this->lang->line('item'), 'required|trim|xss_clean');

        $this->form_validation->set_rules('quantity', $this->lang->line('quantity'), 'trim|integer|required|xss_clean|callback_check_available_quantity');

//        if ($this->form_validation->run() == false) {
//            echo "hi"; die();
//            $data = array(
//                'account_type' => form_error('account_type'),
//                'issue_to' => form_error('issue_to'),
//                'issue_by' => form_error('issue_by'),
//                'issue_date' => form_error('issue_date'),
//                'item_category_id' => form_error('item_category_id'),
//                'item_id' => form_error('item_id'),
//                'quantity' => form_error('quantity'),
//            );
//            $array = array('status' => 'fail', 'error' => $data);
//            echo json_encode($array);
//        } else {
            $return_date = "";
            if (($this->input->post('return_date')) != "") {

                $return_date = date('Y-m-d', $this->customlib->datetostrtotime($this->input->post('return_date')));
            }

            $income_head = $this->incomehead_model->getByName("Inventory");
            $studentName = $this->student_model->getStudentName($this->input->post('issue_to'));
            if(!$income_head)
            {
                $data = array(
                    'income_category' => 'Inventory',
                    'description' => ''
                );
                $this->incomehead_model->addFromInventory($data);
            }

            if(isset($income_head['id'])) {
                $incomeHead = $income_head['id'];
                $item_id = $this->input->post('item_id');
                $quantity = $this->input->post('quantity');
                $item_category_id = $this->input->post('item_category_id');
                $totalPrice = $this->input->post('tPrice');
                $date1 = strtr($this->input->post('issue_date'), '/', '-');
                $invoice_no = $this->item_model->getNextInvoiceNo();
                for ($i = 0; $i < count($item_id); $i++) {

                    $data = array(
                        'issue_to' => $this->input->post('issue_to'),
                        'issue_by' => $this->input->post('issue_by'),
                        'issue_date' => date('Y-m-d', strtotime($date1)),
                        'return_date' => $return_date,
                        'note' => $this->input->post('note'),
                        'quantity' => $quantity[$i],
                        'issue_type' => $this->input->post('account_type'),
                        'item_category_id' => $item_category_id{$i},
                        'item_id' => $item_id[$i],
                        'invoice_no' => $invoice_no,
                    );
                    $this->itemissue_model->add($data);
                }

                $data = [
                    'inc_head_id' => $incomeHead,
                    'name'        => $studentName,
                    'date'        => date('Y-m-d', strtotime($date1)),
                    'amount'      => $totalPrice,
                    'invoice_no'  => $invoice_no,
                ];
                $insert_id = $this->income_model->add($data);

                $array = array('status' => 'success', 'error' => '', 'message' => $this->lang->line('success_message'));
            }
            else{
                $array = array('status' => 'fail', 'error' => 'Please add Inventory in income head', 'message' => '');
            }



            echo json_encode($array);
        }
//    }

    public function check_available_quantity() {
        $item_category_id = $this->input->post('item_category_id');
        $item_id = $this->input->post('item_id');
        $quantity = $this->input->post('quantity');
        if ($quantity != "" && $item_category_id != "" && $item_id != "") {

            $data = $this->item_model->getItemAvailable($item_id);
            $available = ($data['added_stock'] - $data['issued']);

            if ($quantity <= $available) {

                return TRUE;
            }
            $this->form_validation->set_message('check_available_quantity', $this->lang->line('unavailable_quantity') . $quantity);
            return FALSE;
        }
        return TRUE;
    }

    function delete($id) {
        $data['title'] = 'Delete';
        $this->itemissue_model->remove($id);
        redirect('admin/sellitem');
    }

    public function getUser() {

        $usertype = $this->input->post('usertype');


        $result_final = array();
        $result = array();
        if ($usertype != "") {
            $result = $this->staff_model->getEmployeeByRoleID($usertype);
        }

        $result_final = array('usertype' => $usertype, 'result' => $result);
        echo json_encode($result_final);
    }

    public function returnItem() {

        $issue_id = $this->input->post('item_issue_id');

        if ($issue_id != "") {
            $data = array(
                'id' => $issue_id,
                'is_returned' => 0,
                'return_date' => date('Y-m-d')
            );
            $this->itemissue_model->add($data);
        }

        $result_final = array('status' => 'pass', 'message' => $this->lang->line('success_message'));
        echo json_encode($result_final);
    }

     public function getitemlist()
    {

        $m       = $this->itemissue_model->getitemlist();
        $m       = json_decode($m);
        $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
        $dt_data = array();
        if (!empty($m->data)) {
            foreach ($m->data as $key => $value) {
                $editbtn   = '';
                $deletebtn = '';
                $documents = '' ;
             
                if ($value->note == "") { 
                    $condition = "<p class='text text-danger no-print'>".$this->lang->line('no_description')." </p>" ;
                 }else{
                    $condition = "<p class='text text-info no-print' >".$value->note."</p>" ;
                 }

                $title = "<a href='#' data-toggle='popover' class='detail_popover'>".$value->item_name."</a> <div class='fee_detail_popover' style='display: none'> ".$condition ." </div> " ;

                if($value->is_returned == 1){
                    $is_return= "<span class='label label-danger item_remove' data-item=".$value->id." data-category=".$value->item_category." data-item_name=".$value->item_name." data-quantity=".$value->quantity." data-toggle='modal' data-target='#confirm-delete'>".$this->lang->line('click_to_return')."</span>" ;
                }else{
                      $is_return=" <span class='label label-success'>".$this->lang->line('returned')."</span>" ;
                }

             
                if ($this->rbac->hasPrivilege('issue_item', 'can_delete')) {
                    $deletebtn = '';
                    $deletebtn = "<a onclick='return confirm(" . '"' . $this->lang->line('delete_confirm') . '"' . "  )' href='".base_url()."admin/sellitem/delete/".$value->id."' class='btn btn-default btn-xs' data-placement='left' title='" . $this->lang->line('delete') . "' data-toggle='tooltip'><i class='fa fa-remove'></i></a>";
                }
             
                $row       = array();
                $row[]     = $title;
                $row[]     = $value->item_category; 
                if($value->return_date == "0000-00-00"){
                     $return_date="";
                }else{
                 $return_date     = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value->return_date));
                }
                $row[]     = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($value->issue_date)) . " - " . $return_date;
               
                $row[]     =  $value->firstname . " " . $value->lastname . " (" . $value->admission_no . ")";
                $row[]     =  $value->issue_by;
                $row[]     =  $value->quantity ;
                $row[]     =  $is_return ;
                $row[]     =  $deletebtn;
                $dt_data[] = $row;
            }
        }

        $json_data = array(
            "draw"            => intval($m->draw),
            "recordsTotal"    => intval($m->recordsTotal),
            "recordsFiltered" => intval($m->recordsFiltered),
            "data"            => $dt_data,
        );
        echo json_encode($json_data);
    }

    public function getStudents()
    {
        if(!isset($_GET['searchTerm']))
        {
            $students = $this->student_model->getStudentsBySearch();
            foreach ($students as $student)
            {
                $json[] = ['id'=>$student['id'], 'text'=> $student['firstname'] ." ". $student['lastname'] ." (". $student['class'] ."-". $student['section'] . ")"  ];
            }
        }
        else
        {
            $search = $_GET['searchTerm'];
            $students = $this->student_model->getStudentsBySearch($search);
            foreach ($students as $student)
            {
                $json[] = ['id'=>$student['id'], 'text'=> $student['firstname'] ." ". $student['lastname'] ." (". $student['class'] ."-". $student['section'] . ")"  ];
            }
        }
        echo json_encode($json);
    }

    public function getPrice()
    {
        if(isset($_GET['item_id']))
        {
            $id = $_GET['item_id'];
            $price = $this->item_model->getPrice($id);
            echo json_encode($price);
        }
    }

}
