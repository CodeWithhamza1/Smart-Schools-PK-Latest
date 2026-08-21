<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Parallel fee navigation workspace.
 *
 * This controller intentionally performs no financial reads or writes. Every
 * action opens the existing canonical fee route so payments, receipts,
 * reporting, notifications and Charts of Accounts vouchers keep using their
 * established controllers and models.
 */
class Feeworkspace extends Admin_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->sch_setting_detail = $this->setting_model->getSetting();
    }

    public function index()
    {
        foreach ($this->workspaceSections() as $key => $section) {
            if (!empty($section['actions'])) {
                redirect('admin/feeworkspace/' . $key);
                return;
            }
        }

        access_denied();
    }

    public function collect()
    {
        $this->renderWorkspace('collect');
    }

    public function generate()
    {
        $this->renderWorkspace('generate');
    }

    public function challans()
    {
        $this->renderWorkspace('challans');
    }

    public function enquiry()
    {
        $this->renderWorkspace('enquiry');
    }

    public function setup()
    {
        $this->renderWorkspace('setup');
    }

    private function renderWorkspace($active_section)
    {
        $sections = $this->workspaceSections();
        if (!isset($sections[$active_section]) || empty($sections[$active_section]['actions'])) {
            access_denied();
            return;
        }

        $this->session->set_userdata('top_menu', 'Fee Workspace');
        $this->session->set_userdata('sub_menu', 'admin/feeworkspace/' . $active_section);

        $data = array(
            'title'          => $sections[$active_section]['title'],
            'active_section' => $active_section,
            'sections'       => $sections,
        );

        $this->load->view('layout/header', $data);
        $this->load->view('admin/feeworkspace/index', $data);
        $this->load->view('layout/footer', $data);
    }

    private function workspaceSections()
    {
        return array(
            'collect' => array(
                'title'       => 'Collect Fees',
                'icon'        => 'fa-credit-card',
                'description' => 'Receive payments through the existing receipt and accounting workflow.',
                'actions'     => $this->allowedActions(array(
                    array(
                        'privilege'  => 'collect_fees',
                        'title'      => 'Student Collection',
                        'icon'       => 'fa-user',
                        'description'=> 'Search one student, collect outstanding fees and print a receipt.',
                        'url'        => 'studentfee',
                    ),
                    array(
                        'privilege'  => 'collect_fees',
                        'title'      => 'Family Collection',
                        'icon'       => 'fa-users',
                        'description'=> 'Collect fees for siblings through the existing family workflow.',
                        'url'        => 'studentfee/barCodeFeeCollectView',
                    ),
                )),
            ),
            'generate' => array(
                'title'       => 'Generate Fees',
                'icon'        => 'fa-bolt',
                'description' => 'Generate missing fees or safely update editable unpaid fee entries.',
                'actions'     => $this->allowedActions(array(
                    array(
                        'privilege'  => 'fees_master',
                        'title'      => 'Generate & Update Fees',
                        'icon'       => 'fa-money',
                        'description'=> 'Open the optimized monthly fee workspace with duplicate and settlement protection.',
                        'url'        => 'admin/feemaster/generateFee',
                    ),
                )),
            ),
            'challans' => array(
                'title'       => 'Challans',
                'icon'        => 'fa-file-text-o',
                'description' => 'Prepare payment slips without recording a payment.',
                'actions'     => $this->allowedActions(array(
                    array(
                        'privilege'  => 'collect_fees',
                        'title'      => 'Student Challan',
                        'icon'       => 'fa-file-o',
                        'description'=> 'Generate and print challans for individual students.',
                        'url'        => 'challanform/loadView',
                    ),
                    array(
                        'privilege'  => 'collect_fees',
                        'title'      => 'Family Challan',
                        'icon'       => 'fa-files-o',
                        'description'=> 'Generate the existing sibling or family challan format.',
                        'url'        => 'FamilyChallanform/loadView',
                    ),
                )),
            ),
            'enquiry' => array(
                'title'       => 'Enquiry & Reports',
                'icon'        => 'fa-bar-chart',
                'description' => 'Find payments and dues, send reminders, and open fee reports.',
                'actions'     => $this->allowedActions(array(
                    array(
                        'privilege'  => 'search_fees_payment',
                        'title'      => 'Payment Search',
                        'icon'       => 'fa-search',
                        'description'=> 'Find collected payments and existing receipts.',
                        'url'        => 'studentfee/searchpayment',
                    ),
                    array(
                        'privilege'  => 'search_due_fees',
                        'title'      => 'Due Fees',
                        'icon'       => 'fa-exclamation-circle',
                        'description'=> 'Search students with outstanding fee balances.',
                        'url'        => 'studentfee/feesearch',
                    ),
                    array(
                        'privilege'  => 'fees_reminder',
                        'title'      => 'Fee Reminders',
                        'icon'       => 'fa-bell-o',
                        'description'=> 'Configure and use the existing fee reminder workflow.',
                        'url'        => 'admin/feereminder/setting',
                    ),
                    array(
                        'privilege'  => 'fees_statement',
                        'title'      => 'Financial Reports',
                        'icon'       => 'fa-line-chart',
                        'description'=> 'Open collection, due-fee, statement and finance reports.',
                        'url'        => 'report/finance',
                    ),
                )),
            ),
            'setup' => array(
                'title'       => 'Fee Setup',
                'icon'        => 'fa-cogs',
                'description' => 'Configure fee definitions and access occasional session tools.',
                'actions'     => $this->allowedActions(array(
                    array(
                        'privilege'  => 'fees_type',
                        'title'      => 'Fee Types',
                        'icon'       => 'fa-tags',
                        'description'=> 'Create and maintain fee items such as Monthly Fee or Transport.',
                        'url'        => 'admin/feetype',
                    ),
                    array(
                        'privilege'  => 'fees_group',
                        'title'      => 'Fee Groups',
                        'icon'       => 'fa-object-group',
                        'description'=> 'Organize fee types into reusable groups.',
                        'url'        => 'admin/feegroup',
                    ),
                    array(
                        'privilege'  => 'fees_master',
                        'title'      => 'Fee Master',
                        'icon'       => 'fa-sitemap',
                        'description'=> 'Connect fee groups, fee types and their default amounts.',
                        'url'        => 'admin/feemaster',
                    ),
                    array(
                        'privilege'  => 'fees_discount',
                        'title'      => 'Discounts',
                        'icon'       => 'fa-percent',
                        'description'=> 'Create and assign student fee concessions.',
                        'url'        => 'admin/feediscount',
                    ),
                    array(
                        'privilege'  => 'fees_carry_forward',
                        'title'      => 'Previous Session Balance',
                        'eyebrow'    => 'Session Transfer',
                        'icon'       => 'fa-exchange',
                        'description'=> 'Transfer reviewed outstanding balances into the current session.',
                        'url'        => 'admin/feesforward',
                    ),
                )),
            ),
        );
    }

    private function allowedActions(array $actions)
    {
        $allowed = array();
        foreach ($actions as $action) {
            if ($this->rbac->hasPrivilege($action['privilege'], 'can_view')) {
                $allowed[] = $action;
            }
        }
        return $allowed;
    }
}

