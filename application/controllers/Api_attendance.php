<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Public, collector-only API. It intentionally does not use admin sessions/RBAC. */
class Api_attendance extends CI_Controller {
    private $api_key = '';

    public function __construct() {
        parent::__construct();
        $this->load->model('Attendance_model', 'attendance_model');
        $this->api_key = $this->load_realtime_api_key();
    }

    public function health() {
        $enabled = $this->api_key !== '';
        return $this->respond($enabled ? 200 : 503, array('status' => $enabled ? 'ok' : 'disabled', 'service' => 'zkteco-realtime', 'time' => date('c')));
    }

    public function sync() {
        if (strtoupper($this->input->method(TRUE)) !== 'POST') return $this->respond(405, array('status' => 'error', 'message' => 'POST required'));
        if ($this->api_key === '' || !hash_equals($this->api_key, (string) $this->input->get_request_header('X-API-KEY', TRUE))) {
            $this->attendance_model->capture_realtime_audit('error', 'Unauthorized realtime sync request', array(), 401);
            return $this->respond(401, array('status' => 'error', 'message' => 'Unauthorized'));
        }
        $event = json_decode($this->input->raw_input_stream, true);
        $validation = $this->validate_event($event);
        if ($validation !== '') {
            $this->attendance_model->capture_realtime_audit('error', $validation, is_array($event) ? $event : array(), 400);
            return $this->respond(400, array('status' => 'error', 'message' => $validation));
        }
        try {
            $result = $this->attendance_model->save_realtime_attendance_log($event, 30);
            $outcome = isset($result['outcome']) ? $result['outcome'] : 'error';
            $this->attendance_model->capture_realtime_audit($outcome, isset($result['message']) ? $result['message'] : '', $event, $outcome === 'error' ? 500 : 200);
            if ($outcome === 'error') return $this->respond(500, array('status' => 'error', 'message' => $result['message']));
            if ($outcome === 'unmapped') return $this->respond(404, array('status' => 'unmapped', 'message' => $result['message'], 'id' => $result['id']));
            return $this->respond(200, array('status' => $outcome, 'message' => $result['message'], 'id' => isset($result['id']) ? $result['id'] : null));
        } catch (Throwable $e) {
            $this->attendance_model->capture_realtime_audit('error', 'Unexpected realtime sync error: ' . $e->getMessage(), $event, 500);
            return $this->respond(500, array('status' => 'error', 'message' => 'Unable to process attendance event'));
        }
    }

    /** Collector-only, read-only comparison used to identify missing pipeline events. */
    public function reconcile() {
        if (strtoupper($this->input->method(TRUE)) !== 'POST') return $this->respond(405, array('status' => 'error', 'message' => 'POST required'));
        if ($this->api_key === '' || !hash_equals($this->api_key, (string) $this->input->get_request_header('X-API-KEY', TRUE))) return $this->respond(401, array('status' => 'error', 'message' => 'Unauthorized'));
        $payload = json_decode($this->input->raw_input_stream, true);
        $uids = is_array($payload) && isset($payload['sync_uids']) ? $payload['sync_uids'] : null;
        if (!is_array($uids) || count($uids) > 5000) return $this->respond(400, array('status' => 'error', 'message' => 'sync_uids must contain at most 5000 values'));
        $existing = $this->attendance_model->get_existing_realtime_sync_uids($uids);
        return $this->respond(200, array('status' => 'success', 'existing_sync_uids' => $existing));
    }

    private function validate_event($event) {
        if (!is_array($event)) return 'JSON object required';
        foreach (array('id', 'timestamp', 'state', 'device_id', 'sync_uid', 'source') as $field) if (!array_key_exists($field, $event) || $event[$field] === '') return 'Missing required field: ' . $field;
        if (!preg_match('/^[A-Za-z0-9_-]{1,100}$/', (string) $event['id'])) return 'Invalid device user ID';
        if (!is_numeric($event['state'])) return 'Invalid attendance state';
        if (strlen((string) $event['sync_uid']) > 191 || strlen((string) $event['device_id']) > 100 || strlen((string) $event['source']) > 50) return 'Field length is invalid';
        $time = strtotime((string) $event['timestamp']);
        if ($time === false || $time < strtotime('2000-01-01') || $time > strtotime('+1 day')) return 'Invalid attendance timestamp';
        return '';
    }

    /**
     * Shared hosting normally cannot expose process environment variables.
     * Read only the needed value from the project-root .env file as a fallback.
     */
    private function load_realtime_api_key() {
        $key = trim((string) getenv('ZKTECO_REALTIME_API_KEY'));
        if ($key !== '') return $key;

        $env_file = FCPATH . '.env';
        if (!is_readable($env_file)) return '';
        $lines = @file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) return '';
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) continue;
            list($name, $value) = explode('=', $line, 2);
            if (trim($name) !== 'ZKTECO_REALTIME_API_KEY') continue;
            return trim(trim($value), "\"'");
        }
        return '';
    }

    private function respond($status, $payload) {
        return $this->output->set_content_type('application/json')->set_status_header($status)->set_output(json_encode($payload));
    }
}
