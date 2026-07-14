<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Whatsappweb extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('setting_model');
        $this->load->model('whatsappcampaign_model');
        $this->output->set_content_type('application/json');
    }

    public function validatekey()
    {
        $key = $this->getSecurityKey();
        if (!$this->whatsappcampaign_model->verifySecurityKey($key)) {
            return $this->jsonResponse(array('status' => 0, 'message' => 'Invalid security key'), 403);
        }

        $setting = $this->setting_model->getSetting();
        return $this->jsonResponse(array(
            'status' => 1,
            'message' => 'Connection verified',
            'school_name' => $setting->name,
        ));
    }

    public function pull()
    {
        $key = $this->getSecurityKey();
        if (!$this->whatsappcampaign_model->verifySecurityKey($key)) {
            return $this->jsonResponse(array('status' => 0, 'message' => 'Invalid security key'), 403);
        }

        $worker_id = $this->input->get_post('worker_id', true);
        if (empty($worker_id)) {
            $worker_id = $this->input->get_post('extension_id', true);
        }
        $limit = (int) $this->input->get_post('limit', true);
        if ($limit <= 0 || $limit > 20) {
            $limit = 5;
        }

        $items = $this->whatsappcampaign_model->reservePendingItems($worker_id, $limit);
        return $this->jsonResponse(array(
            'status' => 1,
            'items' => $items,
        ));
    }

    public function pullcsv()
    {
        $key = $this->getSecurityKey();
        if (!$this->whatsappcampaign_model->verifySecurityKey($key)) {
            return $this->jsonResponse(array('status' => 0, 'message' => 'Invalid security key'), 403);
        }

        $worker_id = $this->input->get_post('worker_id', true);
        if (empty($worker_id)) {
            $worker_id = $this->input->get_post('extension_id', true);
        }

        $limit = (int) $this->input->get_post('limit', true);
        if ($limit <= 0 || $limit > 200) {
            $limit = 50;
        }

        $items = $this->whatsappcampaign_model->reservePendingItems($worker_id, $limit);
        $csv = $this->whatsappcampaign_model->itemsToCsv($items);

        $this->output->set_content_type('text/csv');
        $this->output->set_header('Content-Disposition: attachment; filename="smart-schools-whatsapp.csv"');
        $this->output->set_header('X-Smart-Schools-WhatsApp-Rows: ' . count($items));
        $this->output->set_output($csv);
        return;
    }

    public function report()
    {
        $key = $this->getSecurityKey();
        if (!$this->whatsappcampaign_model->verifySecurityKey($key)) {
            return $this->jsonResponse(array('status' => 0, 'message' => 'Invalid security key'), 403);
        }

        $item_id = (int) $this->input->post('item_id');
        $status  = $this->input->post('status', true);
        $error_message = $this->input->post('error_message', true);
        $delivery_response = $this->input->post('delivery_response');
        if ($delivery_response === null) {
            $delivery_response = $this->input->post('extension_response');
        }
        $worker_id = $this->input->post('worker_id', true);
        if (empty($worker_id)) {
            $worker_id = $this->input->post('extension_id', true);
        }

        if ($item_id <= 0 || !in_array($status, array('sent', 'failed', 'pending'), true)) {
            return $this->jsonResponse(array('status' => 0, 'message' => 'Invalid payload'), 422);
        }

        $saved = $this->whatsappcampaign_model->reportItemResult($item_id, $status, $error_message, $delivery_response, $worker_id);
        if (!$saved) {
            return $this->jsonResponse(array('status' => 0, 'message' => 'Unable to update item'), 409);
        }

        return $this->jsonResponse(array('status' => 1, 'message' => 'Updated'));
    }

    protected function getSecurityKey()
    {
        $header_key = $this->input->get_request_header('X-WhatsApp-Key', true);
        if (!empty($header_key)) {
            return $header_key;
        }

        return $this->input->get_post('security_key', true);
    }

    protected function jsonResponse($payload, $http_code = 200)
    {
        $this->output->set_status_header($http_code);
        $this->output->set_output(json_encode($payload));
        return;
    }
}
