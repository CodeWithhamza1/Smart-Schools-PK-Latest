<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Whatsappcampaign extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('whatsappcampaign_model');
        $this->load->model('examgroup_model');
        $language = $this->customlib->getLanguage();
        $idiom = is_array($language) && isset($language['language']) ? $language['language'] : $language;
        $this->lang->load('app_files/whatsapp_messaging', $idiom);
    }

    public function index()
    {
        if (!$this->rbac->hasPrivilege('whatsapp_messaging', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('top_menu', 'Communicate');
        $this->session->set_userdata('sub_menu', 'admin/whatsappcampaign');

        if ($this->input->server('REQUEST_METHOD') === 'POST') {
            $this->saveCampaign();
            return;
        }

        $data['title'] = 'WhatsApp Campaigns';
        $data['campaigns'] = $this->whatsappcampaign_model->getCampaigns();
        $data['classlist'] = $this->class_model->get();
        $data['examgrouplist'] = $this->db
            ->select('id, name AS exam_group')
            ->from('exam_groups')
            ->order_by('id', 'asc')
            ->get()
            ->result_array();
        $setting = $this->setting_model->getSetting();
        $data['security_key'] = $this->setting_model->ensureWhatsappWebSecurityKey($setting);
        $data['extension_path'] = FCPATH . 'whatsapp-chrome-extension';

        $this->load->view('layout/header', $data);
        $this->load->view('admin/whatsappcampaign/index', $data);
        $this->load->view('layout/footer', $data);
    }

    public function manualsend()
    {
        if (!$this->rbac->hasPrivilege('whatsapp_messaging', 'can_view')) {
            access_denied();
        }

        $this->form_validation->set_rules('manual_title', 'Title', 'trim|required|xss_clean');
        $this->form_validation->set_rules('manual_message_template', 'Message Template', 'trim|required');

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . validation_errors() . '</div>');
            redirect('admin/whatsappcampaign');
        }

        $admin_session = $this->session->userdata('admin');
        $created_by = isset($admin_session['id']) ? (int) $admin_session['id'] : 0;

        $campaign_id = $this->whatsappcampaign_model->createManualImmediateCampaign(
            $this->input->post('manual_title', true),
            $this->input->post('manual_message_template'),
            $created_by,
            (int) $this->input->post('manual_class_id'),
            (int) $this->input->post('manual_section_id')
        );

        if (!$campaign_id) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Unable to create manual campaign.</div>');
            redirect('admin/whatsappcampaign');
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-success">Manual campaign queued. Open the Smart Schools WhatsApp extension on a logged-in WhatsApp Web browser to send pending messages.</div>');
        redirect('admin/whatsappcampaign/view/' . $campaign_id);
    }

    public function view($id)
    {
        if (!$this->rbac->hasPrivilege('whatsapp_messaging', 'can_view')) {
            access_denied();
        }

        $campaign = $this->whatsappcampaign_model->getCampaign((int) $id);
        if (empty($campaign)) {
            show_404();
        }

        $this->session->set_userdata('top_menu', 'Communicate');
        $this->session->set_userdata('sub_menu', 'admin/whatsappcampaign');

        $data['title'] = 'WhatsApp Campaign Details';
        $data['campaign'] = $campaign;
        $data['items'] = $this->whatsappcampaign_model->getRecentCampaignItems((int) $id);

        $this->load->view('layout/header', $data);
        $this->load->view('admin/whatsappcampaign/view', $data);
        $this->load->view('layout/footer', $data);
    }

    public function downloadcsv($id)
    {
        if (!$this->rbac->hasPrivilege('whatsapp_messaging', 'can_view')) {
            access_denied();
        }

        $campaign = $this->whatsappcampaign_model->getCampaign((int) $id);
        if (empty($campaign)) {
            show_404();
        }

        if ($this->whatsappcampaign_model->getCampaignItemCount((int) $id) === 0) {
            $this->whatsappcampaign_model->prepareCampaignForCsv((int) $id);
        }

        $items = $this->whatsappcampaign_model->getCampaignItemsForCsv((int) $id);
        $csv = $this->whatsappcampaign_model->itemsToCsv($items);
        $filename = 'whatsapp_campaign_' . (int) $id . '.csv';

        $this->output->set_content_type('text/csv');
        $this->output->set_header('Content-Disposition: attachment; filename="' . $filename . '"');
        $this->output->set_header('X-Smart-Schools-WhatsApp-Rows: ' . count($items));
        $this->output->set_output($csv);
    }

    public function livestatus()
    {
        if (!$this->rbac->hasPrivilege('whatsapp_messaging', 'can_view')) {
            $this->output->set_content_type('application/json')
                         ->set_status_header(403)
                         ->set_output(json_encode(array('error' => 'Access denied')));
            return;
        }

        $ids = $this->input->post('ids');
        if (!is_array($ids)) {
            $ids = array();
        }

        $payload = array();
        foreach ($ids as $id) {
            $campaign = $this->whatsappcampaign_model->getCampaign((int) $id);
            if (!empty($campaign)) {
                $payload[$id] = array(
                    'status'        => ucfirst($campaign['status']),
                    'total_count'   => (int) $campaign['total_count'],
                    'sent_count'    => (int) $campaign['sent_count'],
                    'failed_count'  => (int) $campaign['failed_count'],
                    'pending_count' => (int) $campaign['pending_count'],
                );
            }
        }

        $this->output->set_content_type('application/json')
                     ->set_output(json_encode($payload));
    }

    public function queuenow($id)
    {
        if (!$this->rbac->hasPrivilege('whatsapp_messaging', 'can_view')) {
            access_denied();
        }

        $campaign = $this->whatsappcampaign_model->getCampaign((int) $id);
        if (empty($campaign)) {
            show_404();
        }

        if ($campaign['schedule_type'] === 'fee_due_date') {
            $offset_days = (int) $campaign['fee_reminder_offset_days'];
            $run_date = date('Y-m-d', strtotime(date('Y-m-d') . ' +' . $offset_days . ' days'));
        } else {
            $run_date = !empty($campaign['scheduled_at']) ? date('Y-m-d', strtotime($campaign['scheduled_at'])) : date('Y-m-d');
        }
        $result = $this->whatsappcampaign_model->queueCampaign((int) $id, $run_date);

        $msg = $this->formatQueueResultMessage($result);
        $alert = ((int) $result['queued'] > 0 || $result['reason'] === 'already_queued') ? 'success' : 'danger';

        $this->session->set_flashdata('msg', '<div class="alert alert-' . $alert . '">' . $msg . '</div>');
        redirect('admin/whatsappcampaign/view/' . (int) $id);
    }

    public function releasepending($id)
    {
        if (!$this->rbac->hasPrivilege('whatsapp_messaging', 'can_view')) {
            access_denied();
        }

        $campaign = $this->whatsappcampaign_model->getCampaign((int) $id);
        if (empty($campaign)) {
            show_404();
        }

        $released = $this->whatsappcampaign_model->releaseCampaignReservations((int) $id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">Released ' . (int) $released . ' reserved pending message(s). The extension can pull them again.</div>');
        redirect('admin/whatsappcampaign/view/' . (int) $id);
    }

    public function retryfailed($id)
    {
        if (!$this->rbac->hasPrivilege('whatsapp_messaging', 'can_view')) {
            access_denied();
        }

        $campaign = $this->whatsappcampaign_model->getCampaign((int) $id);
        if (empty($campaign)) {
            show_404();
        }

        $retried = $this->whatsappcampaign_model->retryFailedCampaignItems((int) $id);
        $this->session->set_flashdata('msg', '<div class="alert alert-success">Moved ' . (int) $retried . ' failed message(s) back to pending.</div>');
        redirect('admin/whatsappcampaign/view/' . (int) $id);
    }

    public function getexamsbygroup()
    {
        if (!$this->rbac->hasPrivilege('whatsapp_messaging', 'can_view')) {
            $this->output->set_content_type('application/json')
                         ->set_status_header(403)
                         ->set_output(json_encode(array()));
            return;
        }

        $group_id = (int) $this->input->get_post('exam_group_id');
        $exams = $this->db
            ->select('id, exam')
            ->where('exam_group_id', $group_id)
            ->order_by('id', 'asc')
            ->get('exam_group_class_batch_exams')
            ->result_array();

        $this->output->set_content_type('application/json')->set_output(json_encode($exams));
    }

    protected function saveCampaign()
    {
        $this->form_validation->set_rules('title', 'Title', 'trim|required|xss_clean');
        $this->form_validation->set_rules('campaign_type', 'Campaign Type', 'trim|required|xss_clean');
        $this->form_validation->set_rules('delivery_mode', 'Delivery Mode', 'trim|required|xss_clean');
        $this->form_validation->set_rules('message_template', 'Message Template', 'trim|required');

        $campaign_type = $this->input->post('campaign_type', true);
        $delivery_mode = $this->input->post('delivery_mode', true);
        $target_type = $this->input->post('target_type', true);
        $template_type = $this->input->post('template_type', true);

        if ($campaign_type === 'fee_message') {
            $target_type = 'fee_defaulters';
            $template_type = 'fee_reminder';
        } elseif ($campaign_type === 'absent_message') {
            $target_type = 'absent_today';
            $template_type = 'absence_alert';
        } elseif ($campaign_type === 'exam_result') {
            $target_type = 'exam_result';
            $template_type = 'exam_result';
        } else {
            if (!in_array($target_type, array('all_students', 'class_section'), true)) {
                $target_type = 'all_students';
            }
            if (empty($template_type)) {
                $template_type = 'general_notice';
            }
        }

        $schedule_type = ($delivery_mode === 'fee_due_date') ? 'fee_due_date' : 'once';

        if ($target_type === 'class_section') {
            $this->form_validation->set_rules('class_id', 'Class', 'trim|required|xss_clean');
        }

        if ($campaign_type === 'exam_result') {
            $this->form_validation->set_rules('exam_group_id', 'Exam Group', 'trim|required|integer');
            $this->form_validation->set_rules('exam_id', 'Exam', 'trim|required|integer');
        }

        if ($delivery_mode === 'specific_datetime') {
            $this->form_validation->set_rules('scheduled_date', 'Scheduled Date', 'trim|required|xss_clean');
            $this->form_validation->set_rules('scheduled_time', 'Scheduled Time', 'trim|required|xss_clean');
        }

        if ($delivery_mode === 'fee_due_date') {
            $this->form_validation->set_rules('fee_reminder_offset_days', 'Fee Reminder Offset Days', 'trim|required|integer|xss_clean');
        }

        if ($this->form_validation->run() === false) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">' . validation_errors() . '</div>');
            redirect('admin/whatsappcampaign');
        }

        if (!in_array($campaign_type, array('custom_message', 'fee_message', 'absent_message', 'exam_result'), true)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Invalid campaign type.</div>');
            redirect('admin/whatsappcampaign');
            return;
        }

        if (!in_array($delivery_mode, array('immediate', 'specific_datetime', 'fee_due_date'), true)) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Invalid delivery mode.</div>');
            redirect('admin/whatsappcampaign');
            return;
        }

        if ($delivery_mode === 'fee_due_date' && $campaign_type !== 'exam_result') {
            $campaign_type = 'fee_message';
            $target_type = 'fee_defaulters';
            $template_type = 'fee_reminder';
        }

        $scheduled_at = null;
        $scheduled_timestamp = null;
        if ($delivery_mode === 'specific_datetime') {
            $scheduled_at = $this->input->post('scheduled_date') . ' ' . $this->input->post('scheduled_time') . ':00';
            $scheduled_timestamp = strtotime($scheduled_at);
            if ($scheduled_timestamp === false) {
                $this->session->set_flashdata('msg', '<div class="alert alert-danger">Invalid scheduled date or time.</div>');
                redirect('admin/whatsappcampaign');
                return;
            }
        } elseif ($delivery_mode === 'immediate') {
            $scheduled_at = date('Y-m-d H:i:s');
            $scheduled_timestamp = time();
        }

        $status = 'scheduled';
        if ($schedule_type === 'once' && $scheduled_timestamp <= time()) {
            $status = 'processing';
        }

        $admin_session = $this->session->userdata('admin');
        $campaign_data = array(
            'title'                    => $this->input->post('title', true),
            'target_type'              => $target_type,
            'class_id'                 => (int) $this->input->post('class_id'),
            'section_id'               => (int) $this->input->post('section_id'),
            'exam_group_id'            => ($campaign_type === 'exam_result') ? (int) $this->input->post('exam_group_id') : null,
            'exam_id'                  => ($campaign_type === 'exam_result') ? (int) $this->input->post('exam_id') : null,
            'schedule_type'            => $schedule_type,
            'scheduled_at'             => $scheduled_at,
            'fee_reminder_offset_days' => (int) $this->input->post('fee_reminder_offset_days'),
            'template_type'            => $template_type,
            'message_template'         => $this->input->post('message_template'),
            'status'                   => $status,
            'created_by'               => isset($admin_session['id']) ? $admin_session['id'] : 0,
        );

        if ($campaign_data['class_id'] <= 0) {
            $campaign_data['class_id'] = null;
        }
        if ($campaign_data['section_id'] <= 0) {
            $campaign_data['section_id'] = null;
        }

        $campaign_id = $this->whatsappcampaign_model->saveCampaign($campaign_data);
        if (!$campaign_id) {
            $this->session->set_flashdata('msg', '<div class="alert alert-danger">Unable to save campaign.</div>');
            redirect('admin/whatsappcampaign');
        }

        if ($schedule_type === 'once' && $scheduled_timestamp <= time()) {
            $queue_result = $this->whatsappcampaign_model->queueCampaign($campaign_id, date('Y-m-d', $scheduled_timestamp));
            if ((int) $queue_result['queued'] === 0) {
                $this->session->set_flashdata('msg', '<div class="alert alert-warning">' . $this->formatQueueResultMessage($queue_result) . '</div>');
                redirect('admin/whatsappcampaign/view/' . (int) $campaign_id);
                return;
            }
        }

        $this->session->set_flashdata('msg', '<div class="alert alert-success">' . $this->lang->line('success_message') . '</div>');
        redirect('admin/whatsappcampaign');
    }

    protected function formatQueueResultMessage($result)
    {
        $reason = isset($result['reason']) ? $result['reason'] : '';
        $queued = isset($result['queued']) ? (int) $result['queued'] : 0;
        $missing_phone = isset($result['missing_phone_count']) ? (int) $result['missing_phone_count'] : 0;
        $recipients = isset($result['recipient_count']) ? (int) $result['recipient_count'] : 0;
        $existing = isset($result['existing_count']) ? (int) $result['existing_count'] : 0;

        if ($queued > 0) {
            $msg = 'Campaign queued. Pending items created: ' . $queued . '.';
            if ($missing_phone > 0) {
                $msg .= ' Skipped ' . $missing_phone . ' recipient(s) without a usable phone number.';
            }
            return $msg;
        }

        if ($reason === 'already_queued') {
            return 'Campaign already has ' . $existing . ' queued message row(s). Use Release Pending if a pull was interrupted, or Retry Failed after fixing extension/browser errors.';
        }

        if ($reason === 'no_recipients') {
            return 'No matching recipients were found for this campaign. Check class/section, attendance date, or fee due date rules.';
        }

        if ($reason === 'missing_phone') {
            return 'Found ' . $recipients . ' recipient(s), but none had a usable phone number. Pakistani mobile numbers are exported as +923XXXXXXXXX.';
        }

        return 'No WhatsApp message rows were created for this campaign.';
    }
}
