<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_widget extends Admin_Controller
{
    private $user;

    public function __construct()
    {
        parent::__construct();
        $this->config->load('groq');
        $this->load->model('ai_widget_model');
        $this->user = $this->session->userdata('admin');
        if (!$this->eligible()) {
            $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(array(
                'error' => true, 'error_code' => 'FORBIDDEN', 'message' => 'This assistant is not available for your account.'
            )));
            exit;
        }
    }

    public function ask()
    {
        $this->cleanup_expired_opportunistically();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->json_error('INVALID_INPUT', 'Invalid request.', 405);
        $input = json_decode($this->input->raw_input_stream, true);
        if (!is_array($input)) $input = $this->input->post();
        $question = trim((string) (isset($input['message']) ? $input['message'] : ''));
        $session_id = trim((string) (isset($input['session_id']) ? $input['session_id'] : ''));
        $max_words = (int) $this->config->item('ai_widget_max_words'); if ($max_words < 1) $max_words = 80;
        $word_count = $question === '' ? 0 : count(preg_split('/\s+/', $question));
        if ($question === '' || strlen($question) > 500 || $word_count > $max_words) return $this->json_error('INVALID_INPUT', 'Invalid or empty message.', 422);

        $session = $session_id ? $this->ai_widget_model->get_session($this->user['id'], $session_id) : null;
        if (!$session) {
            if ($session_id) return $this->json_error('SESSION_EXPIRED', 'This chat has expired and is no longer available.', 410);
            $session_id = $this->ai_widget_model->new_session($this->user['id'], $question);
        }
        $rate = $this->ai_widget_model->consume_rate_limit($this->user['id']);
        if (!$rate['allowed']) return $this->json_error('RATE_LIMIT', 'Please wait before asking another question.', 429, array('retry_after' => $rate['retry_after']));
        $this->ai_widget_model->rename_session_if_new($this->user['id'], $session_id, $question);

        if ($this->is_small_talk($question)) return $this->finish_small_talk($session_id, $question);
        if ($this->is_student_count_question($question)) {
            $result = $this->ai_widget_model->execute_school_report(array('report_type' => 'student_count'), $this->primary_role(), (int) $this->user['id']);
            return $this->finish_report($session_id, $question, 'student_count', $result, 'There are ' . (int) $result['total_students'] . ' active students in the current academic session.');
        }
        if ($this->is_pending_fee_month_question($question)) {
            $month = $this->question_month($question);
            $result = $this->ai_widget_model->execute_school_report(array('report_type' => 'pending_fee_summary', 'month' => $month), $this->primary_role(), (int) $this->user['id']);
            $reply = 'Pending fees for ' . date('F', mktime(0, 0, 0, $month, 1)) . ': PKR ' . number_format((float) $result['total_pending'], 2) . '.';
            return $this->finish_report($session_id, $question, 'pending_fee_summary', $result, $reply);
        }

        try {
            $messages = array(array('role' => 'system', 'content' => $this->system_prompt()));
            foreach ($this->ai_widget_model->recent_messages($this->user['id'], $session_id, 4) as $message) {
                $messages[] = array('role' => $message['role'], 'content' => $message['content']);
            }
            $messages[] = array('role' => 'user', 'content' => $question);
            $selection = $this->groq($this->trim_messages($messages), $this->ai_widget_model->tool_definitions($question));
            if (empty($selection['tool_calls'])) {
                $args = $this->infer_report_args($question);
                if (empty($args)) { $reply = trim((string) $selection['content']); if ($reply === '') $reply = 'Please ask for a specific school report or record.'; return $this->finish_report($session_id, $question, null, array(), $reply); }
            } else {
                $call = $selection['tool_calls'][0];
                if (!isset($call['function']['name']) || $call['function']['name'] !== 'school_report') throw new RuntimeException('INVALID_TOOL');
                $args = json_decode($call['function']['arguments'], true);
                if (!is_array($args)) $args = $this->infer_report_args($question);
                if (empty($args)) throw new RuntimeException('INVALID_TOOL_ARGS');
            }
            $report_type = isset($args['report_type']) ? strtolower(trim($args['report_type'])) : 'school_report';
            $result = $this->ai_widget_model->execute_school_report($args, $this->primary_role(), (int) $this->user['id']);
            $fallback = $this->format_result_fallback($report_type, $result);

            try {
                $result_json = json_encode($result);
                if (strlen($result_json) > 6000) $result_json = substr($result_json, 0, 6000) . ' [truncated]';
                $format_messages = array(
                    array('role' => 'system', 'content' => 'Format the verified school report data into a concise answer. Use PKR for money. Do not invent values, SQL, or missing records. Use short lines for lists and mention when results are limited.'),
                    array('role' => 'user', 'content' => "Question: " . $question . "\nVerified report data: " . $result_json)
                );
                $formatted = $this->groq($format_messages, array());
                $reply = trim((string) $formatted['content']);
                if ($reply === '') $reply = $fallback;
            } catch (Exception $format_error) {
                log_message('error', 'AI report formatting fallback: ' . $format_error->getMessage());
                $reply = $fallback;
            }
            return $this->finish_report($session_id, $question, $report_type, $result, $reply);
        } catch (Exception $e) {
            log_message('error', 'AI widget failure: ' . $e->getMessage());
            if ($e->getMessage() === 'GROQ_RATE_LIMIT') return $this->json_error('RATE_LIMIT', 'The AI service is busy. Please wait a few seconds and try again.', 429, array('retry_after' => 10));
            return $this->json_error('SERVER_ERROR', 'The report could not be generated right now.', 500);
        }
    }

    public function sessions() { $this->cleanup_expired_opportunistically(); $this->respond(array('error' => false, 'sessions' => $this->ai_widget_model->sessions($this->user['id']))); }
    public function history($session_id = '') { $this->cleanup_expired_opportunistically(); if (!$this->ai_widget_model->get_session($this->user['id'], $session_id)) return $this->json_error('SESSION_EXPIRED', 'This chat has expired and is no longer available.', 410); $this->respond(array('error' => false, 'session_id' => $session_id, 'messages' => $this->ai_widget_model->history($this->user['id'], $session_id))); }
    public function new_session() { $this->cleanup_expired_opportunistically(); $this->respond(array('error' => false, 'session_id' => $this->ai_widget_model->new_session($this->user['id']))); }
    public function delete_session($session_id = '') { if (!$this->ai_widget_model->get_session($this->user['id'], $session_id, true)) return $this->json_error('NOT_FOUND', 'That chat could not be found.', 404); $this->ai_widget_model->soft_delete($this->user['id'], $session_id); $this->respond(array('error' => false)); }

    private function groq($messages, $tools = array())
    {
        $key = $this->config->item('groq_api_key'); if (!$key) throw new RuntimeException('MISSING_GROQ_KEY');
        $max_tokens = (int) $this->config->item('ai_widget_max_completion_tokens'); if ($max_tokens < 1) $max_tokens = 500;
        $payload_data = array('model' => $this->config->item('groq_model'), 'messages' => $messages, 'temperature' => 0.1, 'reasoning_effort' => 'low', 'max_completion_tokens' => $max_tokens);
        if (!empty($tools)) { $payload_data['tools'] = $tools; $payload_data['tool_choice'] = 'auto'; }
        $payload = json_encode($payload_data);
        $ch = curl_init($this->config->item('groq_endpoint'));
        curl_setopt_array($ch, array(CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $key, 'Content-Type: application/json'), CURLOPT_POSTFIELDS => $payload));
        $raw = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); $error = curl_error($ch); $errno = curl_errno($ch); curl_close($ch);
        if ($status === 429) { log_message('error', 'Groq rate limit: ' . substr((string) $raw, 0, 800)); throw new RuntimeException('GROQ_RATE_LIMIT'); }
        if ($raw === false || $error || $status >= 400) { log_message('error', 'Groq HTTP failure: status=' . (int) $status . '; curl_errno=' . (int) $errno . '; curl_error=' . substr((string) $error, 0, 300) . '; response=' . substr((string) $raw, 0, 1200)); throw new RuntimeException('GROQ_REQUEST_FAILED'); }
        $json = json_decode($raw, true);
        if (!isset($json['choices'][0]['message'])) throw new RuntimeException('INVALID_GROQ_RESPONSE');
        $message = $json['choices'][0]['message'];
        return array('content' => isset($message['content']) ? $message['content'] : '', 'tool_calls' => isset($message['tool_calls']) ? $message['tool_calls'] : array());
    }

    private function finish_small_talk($session_id, $question)
    {
        $reply = in_array(strtolower(trim($question)), array('thanks', 'thank you', 'thx'), true) ? 'You are welcome. Ask me whenever you need a school report.' : 'Sure. Ask me about students, attendance, fees, classes, staff, or another school report.';
        return $this->finish_report($session_id, $question, null, array(), $reply);
    }

    private function finish_report($session_id, $question, $tool, $result, $reply)
    {
        $reply = $this->normalize_currency(substr(trim((string) $reply), 0, 4000));
        $this->ai_widget_model->save_message($this->user['id'], $session_id, 'user', $question);
        $this->ai_widget_model->save_message($this->user['id'], $session_id, 'assistant', $reply, $tool);
        return $this->respond(array('error' => false, 'session_id' => $session_id, 'reply' => $reply, 'tool_used' => $tool, 'widget_version' => '2026-08-10-report-v3'));
    }

    private function format_result_fallback($type, $result)
    {
        if (isset($result['total_students']) && $type === 'student_count') return 'There are ' . (int) $result['total_students'] . ' active students in the current academic session.';
        if (isset($result['total_pending'])) return 'Pending fees: PKR ' . number_format((float) $result['total_pending'], 2) . '.';
        if (isset($result['report_error'])) return $result['report_error'];
        $rows = isset($result['rows']) && is_array($result['rows']) ? $result['rows'] : (array_values($result) === $result ? $result : array($result));
        if (empty($rows)) return 'No matching records were found.';
        $lines = array('Report retrieved ' . count($rows) . ' record(s):');
        foreach (array_slice($rows, 0, 10) as $row) { if (!is_array($row)) continue; $parts = array(); foreach ($row as $key => $value) { if (is_scalar($value) && $value !== '') $parts[] = str_replace('_', ' ', $key) . ': ' . $value; if (count($parts) >= 6) break; } if (!empty($parts)) $lines[] = '- ' . implode(', ', $parts); }
        return implode("\n", $lines);
    }

    private function infer_report_args($question)
    {
        $q = strtolower($question); $args = array('limit' => 25);
        if (preg_match('/fee|fees|due|pending|balance/', $q) && preg_match('/name|contact|parent|guardian|student/', $q)) { $args['report_type'] = 'pending_fee_students'; if (preg_match('/\b(\d{1,2})\s+months?\b/', $q, $m)) $args['months'] = min(12, (int) $m[1]); return $args; }
        if (preg_match('/attendance|absent|present|late/', $q)) { $args['report_type'] = 'attendance_summary'; $args['start_date'] = date('Y-m-d'); $args['end_date'] = date('Y-m-d'); return $args; }
        if (preg_match('/staff|teacher|employee/', $q)) { $args['report_type'] = 'staff_search'; $args['query'] = trim(preg_replace('/staff|teacher|employee|report|list|show|give|me/',' ', $q)); return $args; }
        if (preg_match('/student|pupil|admission/', $q)) { $args['report_type'] = 'student_search'; $args['query'] = trim(preg_replace('/student|pupil|admission|report|list|show|give|me/',' ', $q)); return $args; }
        if (preg_match('/income|revenue/', $q)) { $args['report_type'] = 'income'; return $args; }
        if (preg_match('/expense|cost|spending/', $q)) { $args['report_type'] = 'expenses'; return $args; }
        return array();
    }

    private function trim_messages($messages) { $max = 6000; $total = 0; $out = array($messages[0]); for ($i = count($messages) - 1; $i >= 1; $i--) { $len = strlen(json_encode($messages[$i])); if ($total + $len > $max) break; array_splice($out, 1, 0, array($messages[$i])); $total += $len; } return $out; }
    private function cleanup_expired_opportunistically() { $this->ai_widget_model->delete_expired(); }
    private function is_student_count_question($question) { $q = strtolower(trim($question)); return (bool) (preg_match('/\b(how many|number of|count of|total(?: number)? of)\b.*\b(students?|pupils?)\b/i', $q) || preg_match('/\b(students?|pupils?)\b.*\b(total|count)\b/i', $q)); }
    private function is_pending_fee_month_question($question) { $has_fee = preg_match('/\b(pending|due|outstanding)\b.*\b(fee|fees)\b/i', $question) || preg_match('/\b(fee|fees)\b.*\b(pending|due|outstanding)\b/i', $question); return (bool) ($has_fee && preg_match('/\b(january|february|march|april|may|june|july|august|september|october|november|december)\b/i', $question) && !preg_match('/\b(names?|contacts?|parents?|students?)\b/i', $question)); }
    private function is_small_talk($question) { return in_array(strtolower(trim($question)), array('hi','hello','hey','ok','okay','thanks','thank you','thx','good morning','good afternoon','good evening'), true); }
    private function question_month($question) { $months = array('january'=>1,'february'=>2,'march'=>3,'april'=>4,'may'=>5,'june'=>6,'july'=>7,'august'=>8,'september'=>9,'october'=>10,'november'=>11,'december'=>12); $q = strtolower($question); foreach ($months as $name => $number) if (strpos($q, $name) !== false) return $number; return (int) date('n'); }
    private function normalize_currency($reply) { $reply = preg_replace('/₹\s*([0-9][0-9,]*(?:\.\d+)?)/u', 'PKR $1', $reply); return preg_replace('/\bRs\.?\s*([0-9][0-9,]*(?:\.\d+)?)/i', 'PKR $1', $reply); }
    private function system_prompt() { return 'You select one school_report tool for school-data requests. Never generate SQL or table names. For pending-fee names and parent contacts use pending_fee_students. For a monthly total use pending_fee_summary. For greetings or general conversation, answer briefly without a tool. Use only supplied filters and current date ' . date('Y-m-d') . '.'; }
    private function eligible() { $roles = isset($this->user['roles']) && is_array($this->user['roles']) ? array_keys($this->user['roles']) : array(isset($this->user['role']) ? $this->user['role'] : ''); foreach ($roles as $role) if (in_array(strtolower(trim($role)), array('admin','super admin','superadmin'), true)) return true; return false; }
    private function primary_role() { $roles = isset($this->user['roles']) && is_array($this->user['roles']) ? array_keys($this->user['roles']) : array(isset($this->user['role']) ? $this->user['role'] : ''); return (string) reset($roles); }
    private function respond($data) { $this->output->set_content_type('application/json')->set_output(json_encode($data)); }
    private function json_error($code, $message, $status, $extra = array()) { $this->output->set_status_header($status); $this->respond(array_merge(array('error' => true, 'error_code' => $code, 'message' => $message), $extra)); return false; }
}
