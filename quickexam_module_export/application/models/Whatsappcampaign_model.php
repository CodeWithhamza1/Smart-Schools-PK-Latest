<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Whatsappcampaign_model extends MY_Model
{

    protected $current_session;
    protected $current_date;
    protected $school_setting;

    public function __construct()
    {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->current_date    = $this->setting_model->getDateYmd();
        $this->school_setting  = $this->setting_model->getSetting();
        if ($this->hasCampaignTables()) {
            $this->ensureExamColumns();
        }
    }

    public function hasCampaignTables()
    {
        return $this->db->table_exists('whatsapp_campaigns') && $this->db->table_exists('whatsapp_campaign_items');
    }

    public function getQueueVersion()
    {
        if (!$this->hasCampaignTables()) {
            return 0;
        }

        $row = $this->db->select_max('id', 'queue_version')->get('whatsapp_campaigns')->row_array();
        return !empty($row['queue_version']) ? (int) $row['queue_version'] : 0;
    }

    protected function ensureAttendanceStatusColumn()
    {
        if ($this->db->field_exists('attendance_status', 'student_attendences')) {
            return;
        }

        $this->load->dbforge();
        $this->dbforge->add_column('student_attendences', array(
            'attendance_status' => array(
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => FALSE,
                'default'    => 'published',
            ),
        ));
    }

    public function getCampaigns($limit = 50)
    {
        if (!$this->hasCampaignTables()) {
            return array();
        }

        $this->db->select('whatsapp_campaigns.*,classes.class as class_name,sections.section as section_name');
        $this->db->from('whatsapp_campaigns');
        $this->db->join('classes', 'classes.id = whatsapp_campaigns.class_id', 'left');
        $this->db->join('sections', 'sections.id = whatsapp_campaigns.section_id', 'left');
        $this->db->order_by('whatsapp_campaigns.id', 'desc');
        $this->db->limit((int) $limit);
        $campaigns = $this->db->get()->result_array();

        if (empty($campaigns)) {
            return array();
        }

        $ids = array_column($campaigns, 'id');
        $summary_map = $this->getCampaignSummaryMap($ids);

        foreach ($campaigns as &$campaign) {
            $summary = isset($summary_map[$campaign['id']]) ? $summary_map[$campaign['id']] : $this->getEmptySummary();
            $campaign = array_merge($campaign, $summary);
        }

        return $campaigns;
    }

    public function getCampaign($id)
    {
        if (!$this->hasCampaignTables()) {
            return array();
        }

        $this->db->select('whatsapp_campaigns.*,classes.class as class_name,sections.section as section_name,staff.name as created_by_name,staff.surname as created_by_surname');
        $this->db->from('whatsapp_campaigns');
        $this->db->join('classes', 'classes.id = whatsapp_campaigns.class_id', 'left');
        $this->db->join('sections', 'sections.id = whatsapp_campaigns.section_id', 'left');
        $this->db->join('staff', 'staff.id = whatsapp_campaigns.created_by', 'left');
        $this->db->where('whatsapp_campaigns.id', $id);
        $campaign = $this->db->get()->row_array();

        if (empty($campaign)) {
            return array();
        }

        $summary = $this->getCampaignSummaryMap(array($id));
        $campaign = array_merge($campaign, isset($summary[$id]) ? $summary[$id] : $this->getEmptySummary());

        return $campaign;
    }

    public function getRecentCampaignItems($campaign_id, $limit = 100)
    {
        if (!$this->hasCampaignTables()) {
            return array();
        }

        $this->db->select('*');
        $this->db->from('whatsapp_campaign_items');
        $this->db->where('campaign_id', $campaign_id);
        $this->db->order_by('id', 'desc');
        $this->db->limit((int) $limit);
        return $this->db->get()->result_array();
    }

    public function getCampaignItemsForCsv($campaign_id, $statuses = array())
    {
        if (!$this->hasCampaignTables()) {
            return array();
        }

        $this->db->select('whatsapp_campaign_items.*,whatsapp_campaigns.title as campaign_title');
        $this->db->from('whatsapp_campaign_items');
        $this->db->join('whatsapp_campaigns', 'whatsapp_campaigns.id = whatsapp_campaign_items.campaign_id');
        $this->db->where('whatsapp_campaign_items.campaign_id', $campaign_id);

        if (!empty($statuses)) {
            $this->db->where_in('whatsapp_campaign_items.status', $statuses);
        }

        $this->db->order_by('whatsapp_campaign_items.id', 'asc');
        return $this->db->get()->result_array();
    }

    public function getCampaignItemCount($campaign_id, $statuses = array())
    {
        if (!$this->hasCampaignTables()) {
            return 0;
        }

        $this->db->from('whatsapp_campaign_items');
        $this->db->where('campaign_id', (int) $campaign_id);

        if (!empty($statuses)) {
            $this->db->where_in('status', $statuses);
        }

        return (int) $this->db->count_all_results();
    }

    public function prepareCampaignForCsv($campaign_id)
    {
        if (!$this->hasCampaignTables()) {
            return $this->queueResult('schema_missing');
        }

        $campaign = $this->getCampaign((int) $campaign_id);
        if (empty($campaign)) {
            return $this->queueResult('not_found');
        }

        $existing_count = $this->getCampaignItemCount((int) $campaign_id);
        if ($existing_count > 0) {
            return $this->queueResult('already_queued', 0, $existing_count, 0, 0, $existing_count);
        }

        if ($campaign['schedule_type'] === 'fee_due_date') {
            $offset_days = (int) $campaign['fee_reminder_offset_days'];
            $run_date = date('Y-m-d', strtotime(date('Y-m-d') . ' +' . $offset_days . ' days'));
        } else {
            $run_date = !empty($campaign['scheduled_at']) ? date('Y-m-d', strtotime($campaign['scheduled_at'])) : $this->current_date;
        }

        return $this->queueCampaign((int) $campaign_id, $run_date);
    }

    public function itemsToCsv($items)
    {
        $rows = array(array(
            'item_id',
            'campaign_id',
            'campaign_title',
            'recipient_name',
            'phone_number',
            'message_text',
            'status',
            'class_name',
            'section_name',
        ));

        foreach ($items as $item) {
            $rows[] = array(
                isset($item['id']) ? $item['id'] : '',
                isset($item['campaign_id']) ? $item['campaign_id'] : '',
                isset($item['campaign_title']) ? $item['campaign_title'] : '',
                isset($item['recipient_name']) ? $item['recipient_name'] : '',
                isset($item['phone_number']) ? $item['phone_number'] : '',
                isset($item['message_text']) ? $item['message_text'] : '',
                isset($item['status']) ? $item['status'] : '',
                isset($item['class_name']) ? $item['class_name'] : '',
                isset($item['section_name']) ? $item['section_name'] : '',
            );
        }

        $handle = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    public function saveCampaign($data)
    {
        if (!$this->hasCampaignTables()) {
            return false;
        }

        $this->db->trans_start();
        $this->db->trans_strict(false);

        if (isset($data['id']) && !empty($data['id'])) {
            $this->db->where('id', $data['id']);
            $this->db->update('whatsapp_campaigns', $data);
            $id = $data['id'];
            $message = UPDATE_RECORD_CONSTANT . " On whatsapp campaigns id " . $id;
            $action = "Update";
            $record_id = $id;
            $this->log($message, $record_id, $action);
        } else {
            $this->db->insert('whatsapp_campaigns', $data);
            $id = $this->db->insert_id();
            $message = INSERT_RECORD_CONSTANT . " On whatsapp campaigns id " . $id;
            $action = "Insert";
            $record_id = $id;
            $this->log($message, $record_id, $action);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return false;
        }

        return $id;
    }

    public function deleteCampaign($campaign_id)
    {
        if (!$this->hasCampaignTables() || (int) $campaign_id <= 0) {
            return false;
        }

        $campaign_id = (int) $campaign_id;
        $this->db->trans_start();
        $this->db->where('campaign_id', $campaign_id)->delete('whatsapp_campaign_items');
        $this->db->where('id', $campaign_id)->delete('whatsapp_campaigns');
        $this->db->trans_complete();

        return $this->db->trans_status() !== false;
    }

    public function processReadyCampaigns()
    {
        if (!$this->hasCampaignTables()) {
            return false;
        }

        $now = date('Y-m-d H:i:s');

        $this->db->select('*');
        $this->db->from('whatsapp_campaigns');
        $this->db->where('schedule_type', 'once');
        $this->db->where('status', 'scheduled');
        $this->db->where('scheduled_at <=', $now);
        $once_campaigns = $this->db->get()->result_array();

        foreach ($once_campaigns as $campaign) {
            $reference_date = !empty($campaign['scheduled_at']) ? date('Y-m-d', strtotime($campaign['scheduled_at'])) : $this->current_date;
            $this->queueCampaign($campaign['id'], $reference_date);
        }

        $today = date('Y-m-d');
        $this->db->select('*');
        $this->db->from('whatsapp_campaigns');
        $this->db->where('schedule_type', 'fee_due_date');
        $this->db->where_in('status', array('scheduled', 'processing'));
        $this->db->group_start();
        $this->db->where('last_processed_date IS NULL', null, false);
        $this->db->or_where('last_processed_date !=', $today);
        $this->db->group_end();
        $recurring_campaigns = $this->db->get()->result_array();

        foreach ($recurring_campaigns as $campaign) {
            $offset_days = (int) $campaign['fee_reminder_offset_days'];
            $reference_date = date('Y-m-d', strtotime($today . ' +' . $offset_days . ' days'));
            $this->queueCampaign($campaign['id'], $reference_date);
            $this->saveCampaign(array(
                'id' => $campaign['id'],
                'last_processed_date' => $today,
                'last_processed_at'   => date('Y-m-d H:i:s'),
                'status'              => 'scheduled',
            ));
        }
    }

    public function queueCampaign($campaign_id, $reference_date = null)
    {
        if (!$this->hasCampaignTables()) {
            return $this->queueResult('schema_missing');
        }

        $campaign = $this->getCampaign($campaign_id);
        if (empty($campaign)) {
            return $this->queueResult('not_found');
        }

        $run_date = empty($reference_date) ? $this->current_date : $reference_date;
        if ($campaign['schedule_type'] === 'once' && $this->campaignAlreadyQueued($campaign_id)) {
            $existing_count = $this->getCampaignItemCount((int) $campaign_id);
            return $this->queueResult('already_queued', 0, $existing_count, 0, 0, $existing_count);
        }

        if ($campaign['schedule_type'] === 'fee_due_date' && $this->campaignRunAlreadyQueued($campaign_id, $run_date)) {
            $existing_count = $this->getCampaignItemCount((int) $campaign_id);
            return $this->queueResult('already_queued', 0, $existing_count, 0, 0, $existing_count);
        }

        $recipients = $this->resolveCampaignRecipients($campaign, $run_date);
        $recipient_count = count($recipients);
        if (empty($recipients)) {
            $this->markCampaignQueueAttempted($campaign_id, $campaign);

            return $this->queueResult('no_recipients');
        }

        $insert_batch = array();
        $missing_phone_count = 0;
        foreach ($recipients as $recipient) {
            $phone_numbers = $this->getRecipientPhoneNumbers($recipient);
            if (empty($phone_numbers)) {
                $missing_phone_count++;
                continue;
            }

            foreach ($phone_numbers as $phone) {
                $recipient['phone_number'] = $phone;
                $insert_batch[] = array(
                    'campaign_id'        => $campaign_id,
                    'queue_run_date'     => $run_date,
                    'student_id'         => $recipient['student_id'],
                    'student_session_id' => $recipient['student_session_id'],
                    'recipient_name'     => isset($recipient['recipient_name']) ? $recipient['recipient_name'] : $recipient['student_name'],
                    'class_name'         => $recipient['class_name'],
                    'section_name'       => $recipient['section_name'],
                    'phone_number'       => $phone,
                    'message_text'       => $this->buildMessage($campaign['message_template'], $recipient),
                    'merge_data'         => json_encode($recipient),
                    'status'             => 'pending',
                    'attempt_count'      => 0,
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s'),
                );
            }
        }

        if (empty($insert_batch)) {
            $this->markCampaignQueueAttempted($campaign_id, $campaign);
            return $this->queueResult('missing_phone', 0, $missing_phone_count, $recipient_count, $missing_phone_count);
        }

        if (!empty($insert_batch)) {
            $this->db->insert_batch('whatsapp_campaign_items', $insert_batch);
        }

        $status = $campaign['schedule_type'] === 'fee_due_date' ? 'scheduled' : 'processing';
        $this->saveCampaign(array(
            'id'                => $campaign_id,
            'status'            => $status,
            'last_processed_at' => date('Y-m-d H:i:s'),
        ));

        return $this->queueResult('queued', count($insert_batch), $missing_phone_count, $recipient_count, $missing_phone_count);
    }

    public function reservePendingItems($worker_id, $limit = 5)
    {
        if (!$this->hasCampaignTables()) {
            return array();
        }

        $this->processReadyCampaigns();
        $worker_id = trim((string) $worker_id);
        if ($worker_id === '') {
            $worker_id = 'default-worker';
        }

        $stale_time = date('Y-m-d H:i:s', strtotime('-15 minutes'));
        $this->db->where('status', 'pending');
        $this->db->where('reserved_by IS NOT NULL', null, false);
        $this->db->where('reserved_at <', $stale_time);
        $this->db->update('whatsapp_campaign_items', array(
            'reserved_by' => null,
            'reserved_at' => null,
            'updated_at'  => date('Y-m-d H:i:s'),
        ));

        $this->db->select('whatsapp_campaign_items.*,whatsapp_campaigns.title as campaign_title');
        $this->db->from('whatsapp_campaign_items');
        $this->db->join('whatsapp_campaigns', 'whatsapp_campaigns.id = whatsapp_campaign_items.campaign_id');
        $this->db->where('whatsapp_campaign_items.status', 'pending');
        $this->db->group_start();
        $this->db->where('whatsapp_campaign_items.reserved_by IS NULL', null, false);
        $this->db->or_where('whatsapp_campaign_items.reserved_by', '');
        $this->db->or_where('whatsapp_campaign_items.reserved_by', $worker_id);
        $this->db->group_end();
        $this->db->order_by('whatsapp_campaign_items.id', 'asc');
        $this->db->limit((int) $limit);
        $items = $this->db->get()->result_array();

        if (empty($items)) {
            return array();
        }

        $ids = array();
        foreach ($items as $item) {
            $ids[] = (int) $item['id'];
        }

        $this->db->where_in('id', $ids);
        $this->db->where('status', 'pending');
        $this->db->update('whatsapp_campaign_items', array(
            'reserved_by' => $worker_id,
            'reserved_at' => date('Y-m-d H:i:s'),
            'updated_at'  => date('Y-m-d H:i:s'),
        ));

        $this->db->select('whatsapp_campaign_items.*,whatsapp_campaigns.title as campaign_title');
        $this->db->from('whatsapp_campaign_items');
        $this->db->join('whatsapp_campaigns', 'whatsapp_campaigns.id = whatsapp_campaign_items.campaign_id');
        $this->db->where_in('whatsapp_campaign_items.id', $ids);
        $this->db->where('whatsapp_campaign_items.reserved_by', $worker_id);
        $reserved = $this->db->get()->result_array();

        foreach ($reserved as $item) {
            $this->refreshCampaignStatus($item['campaign_id']);
        }

        return $reserved;
    }

    public function reportItemResult($item_id, $status, $error_message = '', $delivery_response = '', $worker_id = '')
    {
        if (!$this->hasCampaignTables()) {
            return false;
        }

        $item = $this->db->where('id', $item_id)->get('whatsapp_campaign_items')->row_array();
        if (empty($item)) {
            return false;
        }

        if (!empty($worker_id) && !empty($item['reserved_by']) && $item['reserved_by'] !== $worker_id) {
            return false;
        }

        $next_status = ($status === 'sent') ? 'sent' : (($status === 'pending') ? 'pending' : 'failed');

        $save_data = array(
            'status'             => $next_status,
            'error_message'      => $error_message,
            'extension_response' => $delivery_response,
            'attempt_count'      => ((int) $item['attempt_count']) + 1,
            'reserved_by'        => null,
            'reserved_at'        => null,
            'updated_at'         => date('Y-m-d H:i:s'),
        );

        if ($next_status === 'sent') {
            $save_data['sent_at'] = date('Y-m-d H:i:s');
            $save_data['failed_at'] = null;
        } elseif ($next_status === 'failed') {
            $save_data['failed_at'] = date('Y-m-d H:i:s');
        } else {
            $save_data['failed_at'] = null;
        }

        $this->db->where('id', $item_id);
        $this->db->update('whatsapp_campaign_items', $save_data);
        $this->refreshCampaignStatus($item['campaign_id']);

        return true;
    }

    public function releaseCampaignReservations($campaign_id)
    {
        if (!$this->hasCampaignTables()) {
            return 0;
        }

        $this->db->where('campaign_id', (int) $campaign_id);
        $this->db->where('status', 'pending');
        $this->db->where('reserved_by IS NOT NULL', null, false);
        $this->db->update('whatsapp_campaign_items', array(
            'reserved_by' => null,
            'reserved_at' => null,
            'updated_at'  => date('Y-m-d H:i:s'),
        ));

        $affected = (int) $this->db->affected_rows();
        $this->refreshCampaignStatus((int) $campaign_id);
        return $affected;
    }

    public function retryFailedCampaignItems($campaign_id)
    {
        if (!$this->hasCampaignTables()) {
            return 0;
        }

        $this->db->where('campaign_id', (int) $campaign_id);
        $this->db->where('status', 'failed');
        $this->db->update('whatsapp_campaign_items', array(
            'status'             => 'pending',
            'error_message'      => null,
            'extension_response' => null,
            'reserved_by'        => null,
            'reserved_at'        => null,
            'failed_at'          => null,
            'updated_at'         => date('Y-m-d H:i:s'),
        ));

        $affected = (int) $this->db->affected_rows();
        $this->refreshCampaignStatus((int) $campaign_id);
        return $affected;
    }

    public function getCampaignSummaryMap($campaign_ids)
    {
        if (!$this->hasCampaignTables()) {
            return array();
        }

        if (empty($campaign_ids)) {
            return array();
        }

        $this->db->select("
            campaign_id,
            SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent_count,
            SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed_count,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            COUNT(*) as total_count
        ");
        $this->db->from('whatsapp_campaign_items');
        $this->db->where_in('campaign_id', $campaign_ids);
        $this->db->group_by('campaign_id');
        $summary_rows = $this->db->get()->result_array();

        $summary_map = array();
        foreach ($campaign_ids as $campaign_id) {
            $summary_map[$campaign_id] = $this->getEmptySummary();
        }

        foreach ($summary_rows as $row) {
            $summary_map[$row['campaign_id']] = array(
                'sent_count'    => (int) $row['sent_count'],
                'failed_count'  => (int) $row['failed_count'],
                'pending_count' => (int) $row['pending_count'],
                'total_count'   => (int) $row['total_count'],
            );
        }

        return $summary_map;
    }

    public function verifySecurityKey($key)
    {
        $saved_key = isset($this->school_setting->whatsapp_web_security_key) ? trim((string) $this->school_setting->whatsapp_web_security_key) : '';
        return ($saved_key !== '' && hash_equals($saved_key, trim((string) $key)));
    }

    public function getStudentSelectionPreview($campaign_type, $class_id = null, $section_id = null, $delivery_mode = 'immediate', $fee_offset_days = 0, $scheduled_date = null)
    {
        $campaign_type = trim((string) $campaign_type);
        $delivery_mode = trim((string) $delivery_mode);
        $reference_date = $this->current_date;
        $recipients = array();

        if ($campaign_type === 'fee_message' || $delivery_mode === 'fee_due_date') {
            $due_mode = 'lte';
            if ($delivery_mode === 'fee_due_date') {
                $due_mode = 'exact';
                $reference_date = date('Y-m-d', strtotime($this->current_date . ' +' . (int) $fee_offset_days . ' days'));
            } elseif ($delivery_mode === 'specific_datetime' && !empty($scheduled_date) && strtotime($scheduled_date) !== false) {
                $reference_date = date('Y-m-d', strtotime($scheduled_date));
            }
            $recipients = $this->getFeeDefaulterRecipients($reference_date, $class_id, $section_id, $due_mode);
        } else {
            $recipients = $this->getStudentRecipients($class_id, $section_id);
        }

        $rows = array();
        foreach ($recipients as $recipient) {
            $phone = $this->normalizePhone(isset($recipient['phone_number']) ? $recipient['phone_number'] : '');
            $rows[] = array(
                'student_id'         => isset($recipient['student_id']) ? (int) $recipient['student_id'] : 0,
                'student_session_id' => isset($recipient['student_session_id']) ? (int) $recipient['student_session_id'] : 0,
                'admission_no'       => isset($recipient['admission_no']) ? $recipient['admission_no'] : '',
                'roll_no'            => isset($recipient['roll_no']) ? $recipient['roll_no'] : '',
                'student_name'       => isset($recipient['student_name']) ? $recipient['student_name'] : '',
                'guardian_name'      => isset($recipient['guardian_name']) ? $recipient['guardian_name'] : '',
                'class_name'         => isset($recipient['class_name']) ? $recipient['class_name'] : '',
                'section_name'       => isset($recipient['section_name']) ? $recipient['section_name'] : '',
                'phone_number'       => $phone,
                'has_phone'          => $phone !== '',
                'due_amount'         => $this->formatCurrencyValue(isset($recipient['due_amount']) ? $recipient['due_amount'] : 0),
                'due_date'           => !empty($recipient['due_date']) ? date('d-m-Y', strtotime($recipient['due_date'])) : '',
            );
        }

        return array(
            'reference_date' => $reference_date,
            'count'          => count($rows),
            'rows'           => $rows,
        );
    }

    public function getPendingFeeMobileNotificationRecipients($reference_date, $class_id = null, $section_id = null, $minimum_due_amount = 0)
    {
        if (empty($reference_date) || strtotime($reference_date) === false) {
            $reference_date = $this->current_date;
        }

        $recipients = $this->getFeeDefaulterRecipients(date('Y-m-d', strtotime($reference_date)), $class_id, $section_id, 'lte');
        $minimum_due_amount = (float) $minimum_due_amount;
        $filtered = array();

        foreach ($recipients as $recipient) {
            if ($minimum_due_amount > 0 && (float) $recipient['due_amount'] < $minimum_due_amount) {
                continue;
            }

            $recipient['class'] = isset($recipient['class_name']) ? $recipient['class_name'] : '';
            $recipient['section'] = isset($recipient['section_name']) ? $recipient['section_name'] : '';
            $recipient['fee_amount'] = $this->formatCurrencyValue(isset($recipient['fee_amount']) ? $recipient['fee_amount'] : 0);
            $recipient['due_amount'] = $this->formatCurrencyValue(isset($recipient['due_amount']) ? $recipient['due_amount'] : 0);
            $recipient['due_date'] = !empty($recipient['due_date']) ? date('d-m-Y', strtotime($recipient['due_date'])) : '';
            $filtered[] = $recipient;
        }

        return $filtered;
    }

    public function createManualImmediateCampaign($title, $message_template, $created_by = 0, $class_id = null, $section_id = null)
    {
        if (!$this->hasCampaignTables()) {
            return false;
        }

        $campaign_data = array(
            'title'                    => $title,
            'target_type'              => !empty($class_id) ? 'class_section' : 'all_students',
            'class_id'                 => !empty($class_id) ? (int) $class_id : null,
            'section_id'               => !empty($section_id) ? (int) $section_id : null,
            'schedule_type'            => 'once',
            'scheduled_at'             => date('Y-m-d H:i:s'),
            'fee_reminder_offset_days' => 0,
            'template_type'            => 'general_notice',
            'message_template'         => $message_template,
            'status'                   => 'processing',
            'created_by'               => (int) $created_by,
        );

        $campaign_id = $this->saveCampaign($campaign_data);
        if (!$campaign_id) {
            return false;
        }

        $this->supersedePendingCampaignItems($campaign_id);
        $this->queueCampaign($campaign_id, date('Y-m-d'));
        return $campaign_id;
    }

    public function createPendingFeeImmediateCampaign($reference_date, $class_id, $section_id, $minimum_due_amount, $message_template, $title, $created_by = 0)
    {
        if (!$this->hasCampaignTables()) {
            return false;
        }

        $recipients = $this->getPendingFeeMobileNotificationRecipients($reference_date, $class_id > 0 ? $class_id : null, $section_id > 0 ? $section_id : null, $minimum_due_amount);
        if (empty($recipients)) {
            return false;
        }

        $campaign_id = $this->saveCampaign(array(
            'title'                    => $title,
            'target_type'              => 'fee_defaulters',
            'class_id'                 => $class_id > 0 ? (int) $class_id : null,
            'section_id'               => $section_id > 0 ? (int) $section_id : null,
            'schedule_type'            => 'once',
            'scheduled_at'             => date('Y-m-d H:i:s'),
            'fee_reminder_offset_days' => 0,
            'template_type'            => 'fee_reminder',
            'message_template'         => $message_template,
            'status'                   => 'processing',
            'created_by'               => (int) $created_by,
        ));
        if (!$campaign_id) {
            return false;
        }

        $this->supersedePendingCampaignItems($campaign_id);
        $insert_batch = array();
        foreach ($recipients as $recipient) {
            $phone_numbers = $this->getRecipientPhoneNumbers($recipient);
            if (empty($phone_numbers)) {
                continue;
            }
            foreach ($phone_numbers as $phone) {
                $recipient['phone_number'] = $phone;
                $insert_batch[] = array(
                    'campaign_id'        => $campaign_id,
                    'queue_run_date'     => $reference_date,
                    'student_id'         => $recipient['student_id'],
                    'student_session_id' => $recipient['student_session_id'],
                    'recipient_name'     => !empty($recipient['guardian_name']) ? $recipient['guardian_name'] : $recipient['student_name'],
                    'class_name'         => $recipient['class_name'],
                    'section_name'       => $recipient['section_name'],
                    'phone_number'       => $phone,
                    'message_text'       => $this->buildMessage($message_template, $recipient),
                    'merge_data'         => json_encode($recipient),
                    'status'             => 'pending',
                    'attempt_count'      => 0,
                    'created_at'         => date('Y-m-d H:i:s'),
                    'updated_at'         => date('Y-m-d H:i:s'),
                );
            }
        }

        if (!empty($insert_batch)) {
            if ($this->db->insert_batch('whatsapp_campaign_items', $insert_batch) === false) {
                // Do not leave an apparently-created campaign with no queue
                // items when the queue table schema is incompatible.
                $this->db->where('id', $campaign_id)->delete('whatsapp_campaigns');
                return false;
            }
            $this->saveCampaign(array('id' => $campaign_id, 'last_processed_at' => date('Y-m-d H:i:s')));
        } else {
            // A campaign without a valid phone number cannot be sent.
            $this->db->where('id', $campaign_id)->delete('whatsapp_campaigns');
            return false;
        }

        return $campaign_id;
    }

    public function supersedePendingCampaignItems($campaign_id)
    {
        if (!$this->hasCampaignTables() || (int) $campaign_id <= 0) {
            return 0;
        }

        $campaign_id = (int) $campaign_id;
        $this->db->where('campaign_id !=', $campaign_id);
        $this->db->where('status', 'pending');
        $this->db->delete('whatsapp_campaign_items');
        $removed = (int) $this->db->affected_rows();

        // Remove rows created by the previous supersede behavior as well, so
        // old campaigns no longer show a false failed-message error.
        $this->db->where('campaign_id !=', $campaign_id);
        $this->db->where('status', 'failed');
        $this->db->where('error_message', 'Superseded by a newer WhatsApp campaign.');
        $this->db->delete('whatsapp_campaign_items');

        return $removed + (int) $this->db->affected_rows();
    }

    protected function resolveCampaignRecipients($campaign, $reference_date)
    {
        switch ($campaign['target_type']) {
            case 'all_students':
                return $this->getStudentRecipients($campaign['class_id'], $campaign['section_id']);
            case 'class_section':
                return $this->getStudentRecipients($campaign['class_id'], $campaign['section_id']);
            case 'absent_today':
                return $this->getAbsentRecipients($reference_date, $campaign['class_id'], $campaign['section_id']);
            case 'fee_defaulters':
                $due_mode = ($campaign['schedule_type'] === 'fee_due_date') ? 'exact' : 'lte';
                return $this->getFeeDefaulterRecipients($reference_date, $campaign['class_id'], $campaign['section_id'], $due_mode);
            case 'exam_result':
                return $this->getExamResultRecipients(
                    isset($campaign['exam_id']) ? $campaign['exam_id'] : null,
                    $campaign['class_id'],
                    $campaign['section_id']
                );
            default:
                return array();
        }
    }

    protected function getStudentRecipients($class_id = null, $section_id = null)
    {
        $this->db->select('students.id as student_id,student_session.id as student_session_id,students.admission_no,students.roll_no,students.firstname,students.middlename,students.lastname,students.guardian_name,students.guardian_phone,students.guardian_email,students.mobileno,students.father_phone,students.mother_phone,classes.class as class_name,sections.section as section_name');
        $this->db->from('students');
        $this->db->join('student_session', 'student_session.student_id = students.id');
        $this->db->join('classes', 'classes.id = student_session.class_id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('students.is_active', 'yes');

        if (!empty($class_id)) {
            $this->db->where('student_session.class_id', $class_id);
        }
        if (!empty($section_id)) {
            $this->db->where('student_session.section_id', $section_id);
        }
        $this->db->order_by('students.admission_no', 'asc');

        return $this->formatBaseRecipients($this->db->get()->result_array());
    }

    protected function getAbsentRecipients($attendance_date, $class_id = null, $section_id = null)
    {
        $this->ensureAttendanceStatusColumn();
        $this->db->select('students.id as student_id,student_session.id as student_session_id,students.firstname,students.middlename,students.lastname,students.guardian_name,students.guardian_phone,students.guardian_email,students.mobileno,students.father_phone,students.mother_phone,classes.class as class_name,sections.section as section_name');
        $this->db->from('student_attendences');
        $this->db->join('student_session', 'student_session.id = student_attendences.student_session_id');
        $this->db->join('students', 'students.id = student_session.student_id');
        $this->db->join('classes', 'classes.id = student_session.class_id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->where('student_attendences.date', $attendance_date);
        $this->db->where('student_attendences.attendence_type_id', 4);
        $this->db->where('student_attendences.attendance_status', 'published');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('students.is_active', 'yes');

        if (!empty($class_id)) {
            $this->db->where('student_session.class_id', $class_id);
        }
        if (!empty($section_id)) {
            $this->db->where('student_session.section_id', $section_id);
        }

        return $this->formatBaseRecipients($this->db->get()->result_array(), array('attendance_date' => $attendance_date));
    }

    protected function getFeeDefaulterRecipients($reference_date, $class_id = null, $section_id = null, $due_mode = 'lte')
    {
        $this->db->select('students.id as student_id,student_session.id as student_session_id,students.admission_no,students.roll_no,students.firstname,students.middlename,students.lastname,students.guardian_name,students.guardian_phone,students.guardian_email,students.mobileno,students.father_phone,students.mother_phone,students.app_key,students.parent_app_key,classes.class as class_name,sections.section as section_name,fee_groups_feetype.amount as fee_amount,fee_groups_feetype.due_date,student_fees_deposite.amount_detail');
        $this->db->from('student_fees_master');
        $this->db->join('student_session', 'student_session.id = student_fees_master.student_session_id');
        $this->db->join('students', 'students.id = student_session.student_id');
        $this->db->join('classes', 'classes.id = student_session.class_id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->join('fee_groups_feetype', 'fee_groups_feetype.fee_session_group_id = student_fees_master.fee_session_group_id');
        $this->db->join('student_fees_deposite', 'student_fees_deposite.student_fees_master_id = student_fees_master.id AND student_fees_deposite.fee_groups_feetype_id = fee_groups_feetype.id', 'left');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('students.is_active', 'yes');

        if ($due_mode === 'exact') {
            $this->db->where('fee_groups_feetype.due_date', $reference_date);
        } else {
            $this->db->where('fee_groups_feetype.due_date <=', $reference_date);
        }

        if (!empty($class_id)) {
            $this->db->where('student_session.class_id', $class_id);
        }
        if (!empty($section_id)) {
            $this->db->where('student_session.section_id', $section_id);
        }
        $this->db->order_by('students.admission_no', 'asc');

        $rows = $this->db->get()->result_array();
        if (empty($rows)) {
            return array();
        }

        $grouped = array();
        foreach ($rows as $row) {
            $row_fee_amount = (float) $row['fee_amount'];
            $deposit_amount = $this->getDepositedAmount($row['amount_detail']);
            $due_amount     = $row_fee_amount - $deposit_amount;
            if ($due_amount <= 0) {
                continue;
            }

            $key = $row['student_session_id'];
            if (!isset($grouped[$key])) {
                $grouped[$key] = array(
                    'student_id'         => $row['student_id'],
                    'student_session_id' => $row['student_session_id'],
                    'admission_no'       => isset($row['admission_no']) ? $row['admission_no'] : '',
                    'roll_no'            => isset($row['roll_no']) ? $row['roll_no'] : '',
                    'student_name'       => $this->getStudentFullName($row),
                    'guardian_name'      => $row['guardian_name'],
                    'guardian_phone'     => $row['guardian_phone'],
                    'guardian_email'     => $row['guardian_email'],
                    'mobileno'           => $row['mobileno'],
                    'father_phone'       => isset($row['father_phone']) ? $row['father_phone'] : '',
                    'mother_phone'       => isset($row['mother_phone']) ? $row['mother_phone'] : '',
                    'app_key'            => isset($row['app_key']) ? $row['app_key'] : '',
                    'parent_app_key'     => isset($row['parent_app_key']) ? $row['parent_app_key'] : '',
                    'phone_number'       => $this->pickGuardianFirstPhone($row),
                    'phone_numbers'      => $this->getRecipientPhoneNumbers($row),
                    'class_name'         => $row['class_name'],
                    'section_name'       => $row['section_name'],
                    'fee_amount'         => 0,
                    'due_amount'         => 0,
                    'due_date'           => $row['due_date'],
                    'school_name'        => $this->school_setting->name,
                );
            }

            $grouped[$key]['fee_amount'] += $row_fee_amount;
            $grouped[$key]['due_amount'] += $due_amount;

            if (strtotime($row['due_date']) < strtotime($grouped[$key]['due_date'])) {
                $grouped[$key]['due_date'] = $row['due_date'];
            }
        }

        return array_values($grouped);
    }

    protected function formatBaseRecipients($rows, $extra = array())
    {
        $recipients = array();
        foreach ($rows as $row) {
            $recipients[] = array_merge(array(
                'student_id'         => $row['student_id'],
                'student_session_id' => $row['student_session_id'],
                'admission_no'       => isset($row['admission_no']) ? $row['admission_no'] : '',
                'roll_no'            => isset($row['roll_no']) ? $row['roll_no'] : '',
                'student_name'       => $this->getStudentFullName($row),
                'guardian_name'      => $row['guardian_name'],
                'guardian_phone'     => $row['guardian_phone'],
                'guardian_email'     => isset($row['guardian_email']) ? $row['guardian_email'] : '',
                'mobileno'           => $row['mobileno'],
                'father_phone'       => isset($row['father_phone']) ? $row['father_phone'] : '',
                'mother_phone'       => isset($row['mother_phone']) ? $row['mother_phone'] : '',
                'phone_number'       => $this->pickRecipientPhone($row),
                'class_name'         => $row['class_name'],
                'section_name'       => $row['section_name'],
                'fee_amount'         => 0,
                'due_amount'         => 0,
                'due_date'           => '',
                'school_name'        => $this->school_setting->name,
            ), $extra);
        }

        return $recipients;
    }

    protected function markCampaignQueueAttempted($campaign_id, $campaign)
    {
        $status = ($campaign['schedule_type'] === 'fee_due_date') ? 'scheduled' : 'processing';
        $this->saveCampaign(array(
            'id'                => $campaign_id,
            'status'            => $status,
            'last_processed_at' => date('Y-m-d H:i:s'),
        ));
    }

    protected function pickRecipientPhone($row)
    {
        foreach (array('mobileno', 'guardian_phone', 'father_phone', 'mother_phone') as $field) {
            if (!isset($row[$field])) {
                continue;
            }

            $phone = $this->normalizePhone($row[$field]);
            if ($phone !== '') {
                return $phone;
            }
        }

        return '';
    }

    protected function pickGuardianFirstPhone($row)
    {
        $phone_numbers = $this->getRecipientPhoneNumbers($row);
        if (!empty($phone_numbers)) {
            return $phone_numbers[0];
        }

        return '';
    }

    protected function getRecipientPhoneNumbers($recipient)
    {
        if (isset($recipient['phone_numbers']) && is_array($recipient['phone_numbers'])) {
            return $recipient['phone_numbers'];
        }

        $phone_numbers = array();
        foreach (array('guardian_phone', 'father_phone', 'mother_phone', 'mobileno', 'phone_number') as $field) {
            if (!isset($recipient[$field])) {
                continue;
            }

            foreach (preg_split('/[,;|\/\r\n]+/', (string) $recipient[$field]) as $phone_value) {
                $phone = $this->normalizePhone($phone_value);
                if ($phone !== '' && !in_array($phone, $phone_numbers, true)) {
                    $phone_numbers[] = $phone;
                }
            }
        }

        return $phone_numbers;
    }

    protected function buildMessage($template, $recipient)
    {
        $replacements = array(
            '{student_name}'    => isset($recipient['student_name']) ? $recipient['student_name'] : '',
            '{guardian_name}'   => isset($recipient['guardian_name']) ? $recipient['guardian_name'] : '',
            '{class}'           => isset($recipient['class_name']) ? $recipient['class_name'] : '',
            '{section}'         => isset($recipient['section_name']) ? $recipient['section_name'] : '',
            '{fee_amount}'      => $this->formatCurrencyValue(isset($recipient['fee_amount']) ? $recipient['fee_amount'] : 0),
            '{due_amount}'      => $this->formatCurrencyValue(isset($recipient['due_amount']) ? $recipient['due_amount'] : 0),
            '{due_date}'        => !empty($recipient['due_date']) ? date('d-m-Y', strtotime($recipient['due_date'])) : '',
            '{attendance_date}' => !empty($recipient['attendance_date']) ? date('d-m-Y', strtotime($recipient['attendance_date'])) : '',
            '{school_name}'     => $this->school_setting->name,
            '{phone}'           => isset($recipient['phone_number']) ? $recipient['phone_number'] : '',
            '{exam_name}'       => isset($recipient['exam_name']) ? $recipient['exam_name'] : '',
            '{total_marks}'     => isset($recipient['total_marks']) ? $this->formatCurrencyValue($recipient['total_marks']) : '',
            '{obtained_marks}'  => isset($recipient['obtained_marks']) ? $this->formatCurrencyValue($recipient['obtained_marks']) : '',
            '{percentage}'      => isset($recipient['percentage']) ? $recipient['percentage'] . '%' : '',
        );
        foreach ($replacements as $tag => $value) {
            $replacements['{' . $tag . '}'] = $value;
        }
        return strtr($template, $replacements);
    }

    protected function refreshCampaignStatus($campaign_id)
    {
        $campaign = $this->db->where('id', $campaign_id)->get('whatsapp_campaigns')->row_array();
        if (empty($campaign)) {
            return;
        }

        $summary_map = $this->getCampaignSummaryMap(array($campaign_id));
        $summary = isset($summary_map[$campaign_id]) ? $summary_map[$campaign_id] : $this->getEmptySummary();

        if ($campaign['schedule_type'] === 'fee_due_date') {
            $status = ($summary['pending_count'] > 0) ? 'processing' : 'scheduled';
        } else {
            if ($summary['pending_count'] > 0) {
                $status = 'processing';
            } elseif ($summary['sent_count'] > 0 && $summary['failed_count'] == 0) {
                $status = 'completed';
            } elseif ($summary['sent_count'] > 0 && $summary['failed_count'] > 0) {
                $status = 'partial';
            } elseif ($summary['failed_count'] > 0) {
                $status = 'failed';
            } else {
                $status = $campaign['status'];
            }
        }

        $this->db->where('id', $campaign_id);
        $this->db->update('whatsapp_campaigns', array(
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s'),
        ));
    }

    protected function campaignAlreadyQueued($campaign_id)
    {
        return (bool) $this->db->where('campaign_id', $campaign_id)->count_all_results('whatsapp_campaign_items');
    }

    protected function campaignRunAlreadyQueued($campaign_id, $run_date)
    {
        return (bool) $this->db
            ->where('campaign_id', $campaign_id)
            ->where('queue_run_date', $run_date)
            ->count_all_results('whatsapp_campaign_items');
    }

    protected function getDepositedAmount($amount_detail)
    {
        if (empty($amount_detail)) {
            return 0;
        }

        $fees_array = json_decode($amount_detail);
        if (json_last_error() !== JSON_ERROR_NONE || empty($fees_array)) {
            return 0;
        }

        $deposit_amount = 0;
        foreach ($fees_array as $value) {
            $amount = isset($value->amount) ? (float) $value->amount : 0;
            $discount = isset($value->amount_discount) ? (float) $value->amount_discount : 0;
            $deposit_amount += ($amount + $discount);
        }

        return $deposit_amount;
    }

    protected function getStudentFullName($row)
    {
        return $this->customlib->getFullName(
            isset($row['firstname']) ? $row['firstname'] : '',
            isset($row['middlename']) ? $row['middlename'] : '',
            isset($row['lastname']) ? $row['lastname'] : '',
            $this->school_setting->middlename,
            $this->school_setting->lastname
        );
    }

    protected function normalizePhone($phone)
    {
        $phone = trim((string) $phone);
        if ($phone === '') {
            return '';
        }

        $digits = preg_replace('/[^0-9]/', '', $phone);
        if ($digits === '') {
            return '';
        }

        // Strip 00-style international dialling prefix
        if (substr($digits, 0, 2) === '00') {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 13 && substr($digits, 0, 4) === '9203') {
            // +92 03XXXXXXXXX -> +923XXXXXXXXX
            $digits = '92' . substr($digits, 3);
        } elseif (strlen($digits) === 11 && substr($digits, 0, 2) === '03') {
            // 03XXXXXXXXX -> +923XXXXXXXXX
            $digits = '92' . substr($digits, 1);
        } elseif (strlen($digits) === 10 && substr($digits, 0, 1) === '3') {
            // 3XXXXXXXXX -> +923XXXXXXXXX
            $digits = '92' . $digits;
        } elseif (strlen($digits) === 12 && substr($digits, 0, 3) === '923') {
            // 923XXXXXXXXX already in target form
        } else {
            // One more attempt: strip a leading 0 and re-check
            if (substr($digits, 0, 1) === '0') {
                $digits = substr($digits, 1);
            }
            if (strlen($digits) === 10 && substr($digits, 0, 1) === '3') {
                $digits = '92' . $digits;
            } else {
                return ''; // not a recognisable Pakistani mobile number
            }
        }

        // Final validation: must be exactly 923XXXXXXXXX (12 digits)
        if (strlen($digits) !== 12 || substr($digits, 0, 3) !== '923') {
            return '';
        }

        return '+' . $digits;
    }

    protected function formatCurrencyValue($amount)
    {
        return number_format((float) $amount, 2, '.', '');
    }

    protected function queueResult($reason, $queued = 0, $skipped = 0, $recipient_count = 0, $missing_phone_count = 0, $existing_count = 0)
    {
        return array(
            'queued'              => (int) $queued,
            'skipped'             => (int) $skipped,
            'recipient_count'     => (int) $recipient_count,
            'missing_phone_count' => (int) $missing_phone_count,
            'existing_count'      => (int) $existing_count,
            'reason'              => $reason,
        );
    }

    protected function getEmptySummary()
    {
        return array(
            'sent_count'    => 0,
            'failed_count'  => 0,
            'pending_count' => 0,
            'total_count'   => 0,
        );
    }

    // ---------------------------------------------------------------
    // Exam result recipients
    // ---------------------------------------------------------------

    public function createExamResultCampaign($exam_id, $created_by = 0, $message_template = null)
    {
        if (!$this->hasCampaignTables() || empty($exam_id)) {
            return false;
        }

        $exam = $this->db
            ->select('exam_group_class_batch_exams.id,exam_group_class_batch_exams.exam,exam_group_class_batch_exams.exam_group_id,exam_groups.name as exam_group_name')
            ->from('exam_group_class_batch_exams')
            ->join('exam_groups', 'exam_groups.id = exam_group_class_batch_exams.exam_group_id')
            ->where('exam_group_class_batch_exams.id', (int) $exam_id)
            ->get()
            ->row_array();

        if (empty($exam)) {
            return false;
        }

        $class_section = $this->db
            ->select('student_session.class_id,student_session.section_id,classes.class as class_name,sections.section as section_name')
            ->from('exam_group_class_batch_exam_students')
            ->join('student_session', 'student_session.id = exam_group_class_batch_exam_students.student_session_id')
            ->join('classes', 'classes.id = student_session.class_id')
            ->join('sections', 'sections.id = student_session.section_id')
            ->where('exam_group_class_batch_exam_students.exam_group_class_batch_exam_id', (int) $exam_id)
            ->group_by(array('student_session.class_id', 'student_session.section_id', 'classes.class', 'sections.section'))
            ->limit(1)
            ->get()
            ->row_array();

        $title = 'Exam Result - ' . $exam['exam'];
        if (!empty($class_section['class_name'])) {
            $title .= ' - ' . $class_section['class_name'];
            if (!empty($class_section['section_name'])) {
                $title .= ' / ' . $class_section['section_name'];
            }
        }

        if ($message_template === null || trim((string) $message_template) === '') {
            $message_template = 'Dear {guardian_name}, {student_name} of class {class} has scored {obtained_marks} / {total_marks} ({percentage}%) in {exam_name}. - {school_name}';
        }

        $campaign_data = array(
            'title'                    => $title,
            'target_type'              => 'exam_result',
            'class_id'                 => !empty($class_section['class_id']) ? (int) $class_section['class_id'] : null,
            'section_id'               => !empty($class_section['section_id']) ? (int) $class_section['section_id'] : null,
            'exam_group_id'            => (int) $exam['exam_group_id'],
            'exam_id'                  => (int) $exam_id,
            'schedule_type'            => 'once',
            'scheduled_at'             => date('Y-m-d H:i:s'),
            'fee_reminder_offset_days' => 0,
            'template_type'            => 'exam_result',
            'message_template'         => $message_template,
            'status'                   => 'processing',
            'created_by'               => (int) $created_by,
        );

        $campaign_id = $this->saveCampaign($campaign_data);
        if (!$campaign_id) {
            return false;
        }

        $result = $this->queueCampaign($campaign_id, date('Y-m-d'));
        if ((int) $result['queued'] === 0) {
            return false;
        }

        return $campaign_id;
    }

    protected function getExamResultRecipients($exam_id, $class_id = null, $section_id = null)
    {
        if (empty($exam_id)) {
            return array();
        }

        $class_filter   = !empty($class_id)   ? ' AND student_session.class_id = '   . $this->db->escape((int) $class_id)   : '';
        $section_filter = !empty($section_id) ? ' AND student_session.section_id = ' . $this->db->escape((int) $section_id) : '';

        $sql = "
            SELECT
                egcbes.id                                                    AS exam_group_class_batch_exam_student_id,
                student_session.id                                           AS student_session_id,
                students.id                                                  AS student_id,
                students.firstname, students.middlename, students.lastname,
                students.guardian_name, students.guardian_phone, students.guardian_email,
                students.mobileno,
                COALESCE(students.father_phone, '')                          AS father_phone,
                COALESCE(students.mother_phone, '')                          AS mother_phone,
                classes.class                                                AS class_name,
                sections.section                                             AS section_name,
                egcbe.exam                                                   AS exam_name,
                eg.name                                                      AS exam_group_name,
                (SELECT COALESCE(SUM(subj.max_marks), 0)
                 FROM exam_group_class_batch_exam_subjects subj
                 WHERE subj.exam_group_class_batch_exams_id = egcbe.id)      AS total_marks,
                (SELECT COALESCE(SUM(res.get_marks), 0)
                 FROM exam_group_exam_results res
                 WHERE res.exam_group_class_batch_exam_student_id = egcbes.id) AS obtained_marks
            FROM exam_group_class_batch_exam_students egcbes
            INNER JOIN exam_group_class_batch_exams egcbe
                ON egcbe.id = egcbes.exam_group_class_batch_exam_id
            INNER JOIN exam_groups eg
                ON eg.id = egcbe.exam_group_id
            INNER JOIN student_session
                ON student_session.id = egcbes.student_session_id
            INNER JOIN students
                ON students.id = student_session.student_id
            INNER JOIN classes
                ON classes.id = student_session.class_id
            INNER JOIN sections
                ON sections.id = student_session.section_id
            WHERE egcbes.exam_group_class_batch_exam_id = " . $this->db->escape((int) $exam_id) . "
              AND student_session.session_id = " . $this->db->escape($this->current_session) . "
              AND students.is_active = 'yes'
              " . $class_filter . $section_filter;

        $rows = $this->db->query($sql)->result_array();
        if (empty($rows)) {
            return array();
        }

        $recipients = array();
        foreach ($rows as $row) {
            $total    = (float) $row['total_marks'];
            $obtained = (float) $row['obtained_marks'];
            $pct      = $total > 0 ? round(($obtained / $total) * 100, 2) : 0;

            $base_recipient = array(
                'student_id'         => $row['student_id'],
                'student_session_id' => $row['student_session_id'],
                'student_name'       => $this->getStudentFullName($row),
                'guardian_name'      => $row['guardian_name'],
                'guardian_phone'     => $row['guardian_phone'],
                'guardian_email'     => $row['guardian_email'],
                'mobileno'           => $row['mobileno'],
                'father_phone'       => $row['father_phone'],
                'mother_phone'       => $row['mother_phone'],
                'phone_number'       => $this->pickRecipientPhone($row),
                'class_name'         => $row['class_name'],
                'section_name'       => $row['section_name'],
                'fee_amount'         => 0,
                'due_amount'         => 0,
                'due_date'           => '',
                'school_name'        => $this->school_setting->name,
                'exam_name'          => !empty($row['exam_name']) ? $row['exam_name'] : $row['exam_group_name'],
                'exam_group_name'    => $row['exam_group_name'],
                'total_marks'        => $total,
                'obtained_marks'     => $obtained,
                'percentage'         => $pct,
            );

            $seen_phones = array();
            $phone_candidates = array(
                array(
                    'field' => 'mobileno',
                    'role'  => 'student',
                    'name'  => $base_recipient['student_name'],
                ),
                array(
                    'field' => 'guardian_phone',
                    'role'  => 'guardian',
                    'name'  => !empty($row['guardian_name']) ? $row['guardian_name'] : $base_recipient['student_name'],
                ),
            );

            foreach ($phone_candidates as $candidate) {
                $phone = $this->normalizePhone(isset($row[$candidate['field']]) ? $row[$candidate['field']] : '');
                if ($phone === '' || isset($seen_phones[$phone])) {
                    continue;
                }

                $seen_phones[$phone] = true;
                $recipient = $base_recipient;
                $recipient['phone_number'] = $phone;
                $recipient['recipient_role'] = $candidate['role'];
                $recipient['recipient_name'] = $candidate['name'];
                $recipients[] = $recipient;
            }

            if (empty($seen_phones)) {
                $base_recipient['phone_number'] = '';
                $base_recipient['recipient_role'] = 'missing';
                $base_recipient['recipient_name'] = $base_recipient['student_name'];
                $recipients[] = $base_recipient;
            }
        }

        return $recipients;
    }

    // ---------------------------------------------------------------
    // Auto-create an absence alert campaign from the attendance page
    // ---------------------------------------------------------------

    public function createAbsenceAlertCampaignForStudentSessions($class_id, $section_id, $attendance_date, $student_session_ids, $created_by = 0)
    {
        if (!$this->hasCampaignTables() || empty($student_session_ids)) {
            return false;
        }

        $student_session_ids = array_values(array_unique(array_filter(array_map('intval', (array) $student_session_ids))));
        if (empty($student_session_ids)) {
            return false;
        }

        $this->db->select('students.id as student_id,student_session.id as student_session_id,students.firstname,students.middlename,students.lastname,students.guardian_name,students.guardian_phone,students.guardian_email,students.mobileno,students.father_phone,students.mother_phone,classes.class as class_name,sections.section as section_name');
        $this->db->from('student_session');
        $this->db->join('students', 'students.id = student_session.student_id');
        $this->db->join('classes', 'classes.id = student_session.class_id');
        $this->db->join('sections', 'sections.id = student_session.section_id');
        $this->db->where('student_session.session_id', $this->current_session);
        $this->db->where('students.is_active', 'yes');
        $this->db->where_in('student_session.id', $student_session_ids);

        $recipients = $this->formatBaseRecipients(
            $this->db->get()->result_array(),
            array('attendance_date' => $attendance_date)
        );
        if (empty($recipients)) {
            return false;
        }

        $title = $this->getAbsenceAlertTitle($class_id, $section_id, $attendance_date);
        $message_template = 'Dear {guardian_name}, your child {student_name} of class {class}-{section} was marked absent on {attendance_date}. Please ensure their attendance. - {school_name}';
        $campaign_id = $this->saveCampaign(array(
            'title'                    => $title,
            'target_type'              => 'absent_today',
            'class_id'                 => !empty($class_id)   ? (int) $class_id   : null,
            'section_id'               => !empty($section_id) ? (int) $section_id : null,
            'schedule_type'            => 'once',
            'scheduled_at'             => date('Y-m-d H:i:s'),
            'fee_reminder_offset_days' => 0,
            'template_type'            => 'absence_alert',
            'message_template'         => $message_template,
            'status'                   => 'processing',
            'created_by'               => (int) $created_by,
        ));
        if (!$campaign_id) {
            return false;
        }

        $insert_batch = array();
        foreach ($recipients as $recipient) {
            $phone = $this->normalizePhone($recipient['phone_number']);
            if ($phone === '') {
                continue;
            }

            $recipient['phone_number'] = $phone;
            $insert_batch[] = array(
                'campaign_id'        => $campaign_id,
                'queue_run_date'     => $attendance_date,
                'student_id'         => $recipient['student_id'],
                'student_session_id' => $recipient['student_session_id'],
                'recipient_name'     => isset($recipient['recipient_name']) ? $recipient['recipient_name'] : $recipient['student_name'],
                'class_name'         => $recipient['class_name'],
                'section_name'       => $recipient['section_name'],
                'phone_number'       => $phone,
                'message_text'       => $this->buildMessage($message_template, $recipient),
                'merge_data'         => json_encode($recipient),
                'status'             => 'pending',
                'attempt_count'      => 0,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            );
        }

        if (empty($insert_batch)) {
            $this->saveCampaign(array(
                'id'                => $campaign_id,
                'status'            => 'failed',
                'last_processed_at' => date('Y-m-d H:i:s'),
            ));
            return $campaign_id;
        }

        $this->db->insert_batch('whatsapp_campaign_items', $insert_batch);
        $this->saveCampaign(array(
            'id'                => $campaign_id,
            'last_processed_at' => date('Y-m-d H:i:s'),
        ));
        $this->refreshCampaignStatus($campaign_id);

        return $campaign_id;
    }

    public function createAbsenceAlertCampaign($class_id, $section_id, $attendance_date, $created_by = 0)
    {
        if (!$this->hasCampaignTables()) {
            return false;
        }

        $title = $this->getAbsenceAlertTitle($class_id, $section_id, $attendance_date);

        $campaign_data = array(
            'title'                    => $title,
            'target_type'              => 'absent_today',
            'class_id'                 => !empty($class_id)   ? (int) $class_id   : null,
            'section_id'               => !empty($section_id) ? (int) $section_id : null,
            'schedule_type'            => 'once',
            'scheduled_at'             => date('Y-m-d H:i:s'),
            'fee_reminder_offset_days' => 0,
            'template_type'            => 'absence_alert',
            'message_template'         => 'Dear {guardian_name}, your child {student_name} of class {class}-{section} was marked absent on {attendance_date}. Please ensure their attendance. - {school_name}',
            'status'                   => 'processing',
            'created_by'               => (int) $created_by,
        );

        $campaign_id = $this->saveCampaign($campaign_data);
        if (!$campaign_id) {
            return false;
        }

        $result = $this->queueCampaign($campaign_id, $attendance_date);
        if ((int) $result['queued'] === 0) {
            return false;
        }

        return $campaign_id;
    }

    protected function getAbsenceAlertTitle($class_id, $section_id, $attendance_date)
    {
        $title = 'Absence Alert';

        $class_row = !empty($class_id)
            ? $this->db->where('id', (int) $class_id)->get('classes')->row_array()
            : array();
        if (!empty($class_row['class'])) {
            $title .= ' - ' . $class_row['class'];
        }

        $section_row = !empty($section_id)
            ? $this->db->where('id', (int) $section_id)->get('sections')->row_array()
            : array();
        if (!empty($section_row['section'])) {
            $title .= ' / ' . $section_row['section'];
        }

        return $title . ' (' . $attendance_date . ')';
    }

    // ---------------------------------------------------------------
    // Schema migration helpers (run once, idempotent)
    // ---------------------------------------------------------------

    protected function ensureExamColumns()
    {
        $this->load->dbforge();
        if (!$this->db->field_exists('exam_group_id', 'whatsapp_campaigns')) {
            $this->dbforge->add_column('whatsapp_campaigns', array(
                'exam_group_id' => array(
                    'type'       => 'INT',
                    'null'       => TRUE,
                    'default'    => NULL,
                    'after'      => 'section_id',
                ),
            ));
        }
        if (!$this->db->field_exists('exam_id', 'whatsapp_campaigns')) {
            $this->dbforge->add_column('whatsapp_campaigns', array(
                'exam_id' => array(
                    'type'       => 'INT',
                    'null'       => TRUE,
                    'default'    => NULL,
                    'after'      => 'exam_group_id',
                ),
            ));
        }
    }
}
