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
            $this->output->set_status_header(403)->set_content_type('application/json')->set_output(json_encode(array('error' => true, 'error_code' => 'FORBIDDEN', 'message' => 'This assistant is not available for your account.')));
            exit;
        }
    }

    public function ask()
    {
        $this->cleanup_expired_opportunistically();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->json_error('INVALID_INPUT', 'Invalid request.', 405);
        $input = json_decode($this->input->raw_input_stream, true); if (!is_array($input)) $input = $this->input->post();
        $question = trim((string) (isset($input['message']) ? $input['message'] : '')); $session_id = trim((string) (isset($input['session_id']) ? $input['session_id'] : ''));
        $word_count = $question === '' ? 0 : count(preg_split('/\s+/', $question));
        $max_words = (int) $this->config->item('ai_widget_max_words'); if ($max_words < 1) $max_words = 80;
        if ($question === '' || strlen($question) > 500 || $word_count > $max_words) return $this->json_error('INVALID_INPUT', 'Invalid or empty message.', 422);
        $session = $session_id ? $this->ai_widget_model->get_session($this->user['id'], $session_id) : null;
        if (!$session) { if ($session_id) return $this->json_error('SESSION_EXPIRED', 'This chat has expired and is no longer available.', 410); $session_id = $this->ai_widget_model->new_session($this->user['id'], $question); }
        $rate = $this->ai_widget_model->consume_rate_limit($this->user['id']); if (!$rate['allowed']) return $this->json_error('RATE_LIMIT', 'Please wait before asking another question.', 429, array('retry_after' => $rate['retry_after']));
        $this->ai_widget_model->rename_session_if_new($this->user['id'], $session_id, $question);
        if ($this->is_student_count_question($question)) {
            try {
                $result = $this->ai_widget_model->execute_tool('count_students', array(), $this->primary_role(), (int) $this->user['id']);
                $reply = 'There are ' . (int) $result['total_students'] . ' active students in the current academic session.';
                $this->ai_widget_model->save_message($this->user['id'], $session_id, 'user', $question);
                $this->ai_widget_model->save_message($this->user['id'], $session_id, 'assistant', $reply, 'count_students');
                return $this->respond(array('error' => false, 'session_id' => $session_id, 'reply' => $reply, 'tool_used' => 'count_students'));
            } catch (Exception $e) { log_message('error', 'AI widget count failure: ' . $e->getMessage()); return $this->json_error('SERVER_ERROR', 'I could not read the student count right now.', 500); }
        }
        if ($this->is_pending_fee_month_question($question)) {
            try { $month = $this->question_month($question); $result = $this->ai_widget_model->pending_fee_month($month, $this->primary_role(), (int) $this->user['id']); $reply = 'Pending fees for ' . date('F', mktime(0, 0, 0, $month, 1)) . ': PKR ' . number_format($result['total_pending'], 2) . '.'; $this->ai_widget_model->save_message($this->user['id'], $session_id, 'user', $question); $this->ai_widget_model->save_message($this->user['id'], $session_id, 'assistant', $reply, 'pending_fee_month'); return $this->respond(array('error' => false, 'session_id' => $session_id, 'reply' => $reply, 'tool_used' => 'pending_fee_month', 'widget_version' => '2026-08-10-fee-v2')); } catch (Exception $e) { log_message('error', 'AI fee summary failure: ' . $e->getMessage()); return $this->json_error('FEE_REPORT_ERROR', 'The monthly fee report could not be read. Please check the server log.', 500); }
        }
        if ($this->is_small_talk($question)) {
            $reply = in_array(strtolower(trim($question)), array('thanks', 'thank you', 'thx'), true) ? 'You’re welcome. Ask me whenever you need a school report.' : 'Sure. Ask me about students, attendance, fees, classes, staff, or any other school report.';
            $this->ai_widget_model->save_message($this->user['id'], $session_id, 'user', $question); $this->ai_widget_model->save_message($this->user['id'], $session_id, 'assistant', $reply); return $this->respond(array('error' => false, 'session_id' => $session_id, 'reply' => $reply, 'tool_used' => null));
        }
        $messages = array(array('role' => 'system', 'content' => $this->system_prompt())); foreach ($this->ai_widget_model->recent_messages($this->user['id'], $session_id, $this->config->item('ai_widget_context_messages')) as $m) $messages[] = array('role' => $m['role'], 'content' => $m['content']); $messages[] = array('role' => 'user', 'content' => $question);
        $messages = $this->trim_messages($messages); $tool_used = null;
        try { $response = $this->groq($messages, $this->ai_widget_model->tool_definitions($question)); $loops = 0;
            while (!empty($response['tool_calls']) && $loops++ < 3) { $assistant = array('role' => 'assistant', 'content' => isset($response['content']) ? $response['content'] : null, 'tool_calls' => $response['tool_calls']); $messages[] = $assistant; foreach ($response['tool_calls'] as $call) { $name = $call['function']['name']; $args = json_decode($call['function']['arguments'], true); if (!is_array($args)) throw new RuntimeException('Invalid tool arguments'); $result = $this->ai_widget_model->execute_tool_for_ai($name, $args, $this->primary_role(), (int) $this->user['id']); $tool_used = $name; $tool_content = json_encode($result); $messages[] = array('role' => 'tool', 'tool_call_id' => $call['id'], 'name' => $name, 'content' => substr($tool_content, 0, 6000)); } $response = $this->groq($messages, $this->ai_widget_model->tool_definitions($question)); }
            if (empty($response['tool_calls']) && $tool_used === 'get_database_schema' && $loops < 3) { $messages[] = array('role' => 'assistant', 'content' => isset($response['content']) ? $response['content'] : ''); $messages[] = array('role' => 'user', 'content' => 'Use the schema result and call read_only_sql now for the original question. Do not answer that the tools are unavailable.'); $response = $this->groq($messages, $this->ai_widget_model->tool_definitions($question)); while (!empty($response['tool_calls']) && $loops++ < 3) { $assistant = array('role' => 'assistant', 'content' => isset($response['content']) ? $response['content'] : null, 'tool_calls' => $response['tool_calls']); $messages[] = $assistant; foreach ($response['tool_calls'] as $call) { $name = $call['function']['name']; $args = json_decode($call['function']['arguments'], true); if (!is_array($args)) throw new RuntimeException('Invalid tool arguments'); $result = $this->ai_widget_model->execute_tool_for_ai($name, $args, $this->primary_role(), (int) $this->user['id']); $tool_used = $name; $tool_content = json_encode($result); $messages[] = array('role' => 'tool', 'tool_call_id' => $call['id'], 'name' => $name, 'content' => substr($tool_content, 0, 6000)); } $response = $this->groq($messages, $this->ai_widget_model->tool_definitions($question)); } }
            $reply = trim((string) (isset($response['content']) ? $response['content'] : '')); $max_reply_chars = (int) $this->config->item('ai_widget_max_reply_chars'); if ($max_reply_chars < 1) $max_reply_chars = 4000; $reply = substr($reply, 0, $max_reply_chars); $reply = $this->normalize_currency($reply); if ($reply === '') throw new RuntimeException('Empty provider response');
            $this->ai_widget_model->save_message($this->user['id'], $session_id, 'user', $question); $this->ai_widget_model->save_message($this->user['id'], $session_id, 'assistant', $reply, $tool_used); $this->respond(array('error' => false, 'session_id' => $session_id, 'reply' => $reply, 'tool_used' => $tool_used));
        } catch (Exception $e) { log_message('error', 'AI widget failure: ' . $e->getMessage()); return $this->json_error('SERVER_ERROR', 'Something went wrong on our end. Please try again in a moment.', 500); }
    }

    public function sessions() { $this->cleanup_expired_opportunistically(); $this->respond(array('error' => false, 'sessions' => $this->ai_widget_model->sessions($this->user['id']))); }
    public function history($session_id = '') { $this->cleanup_expired_opportunistically(); if (!$this->ai_widget_model->get_session($this->user['id'], $session_id)) return $this->json_error('SESSION_EXPIRED', 'This chat has expired and is no longer available.', 410); $this->respond(array('error' => false, 'session_id' => $session_id, 'messages' => $this->ai_widget_model->history($this->user['id'], $session_id))); }
    public function new_session() { $this->cleanup_expired_opportunistically(); $this->respond(array('error' => false, 'session_id' => $this->ai_widget_model->new_session($this->user['id']))); }
    public function delete_session($session_id = '') { if (!$this->ai_widget_model->get_session($this->user['id'], $session_id, true)) return $this->json_error('NOT_FOUND', 'That chat could not be found.', 404); $this->ai_widget_model->soft_delete($this->user['id'], $session_id); $this->respond(array('error' => false)); }

    private function groq($messages, $tools) { $key = $this->config->item('groq_api_key'); if (!$key) throw new RuntimeException('Missing Groq key'); $max_completion_tokens = (int) $this->config->item('ai_widget_max_completion_tokens'); if ($max_completion_tokens < 1) $max_completion_tokens = 600; $payload = json_encode(array('model' => $this->config->item('groq_model'), 'messages' => $messages, 'tools' => $tools, 'tool_choice' => 'auto', 'temperature' => 0.1, 'reasoning_effort' => 'low', 'max_completion_tokens' => $max_completion_tokens)); $ch = curl_init($this->config->item('groq_endpoint')); curl_setopt_array($ch, array(CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $key, 'Content-Type: application/json'), CURLOPT_POSTFIELDS => $payload)); $raw = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); $error = curl_error($ch); $errno = curl_errno($ch); curl_close($ch); if ($raw === false || $error || $status >= 400) { $provider_body = substr(preg_replace('/Bearer\s+[^\s]+/i', 'Bearer [redacted]', (string) $raw), 0, 1200); log_message('error', 'Groq HTTP failure: status=' . (int) $status . '; curl_errno=' . (int) $errno . '; curl_error=' . substr((string) $error, 0, 300) . '; response=' . $provider_body); throw new RuntimeException('Groq request failed'); } $json = json_decode($raw, true); if (!isset($json['choices'][0]['message'])) { log_message('error', 'Groq invalid response: ' . substr((string) $raw, 0, 1200)); throw new RuntimeException('Invalid Groq response'); } $msg = $json['choices'][0]['message']; return array('content' => isset($msg['content']) ? $msg['content'] : '', 'tool_calls' => isset($msg['tool_calls']) ? $msg['tool_calls'] : array()); }
    private function trim_messages($messages) { $max = (int) $this->config->item('ai_widget_context_chars'); $total = 0; $out = array($messages[0]); for ($i = count($messages) - 1; $i >= 1; $i--) { $len = strlen(json_encode($messages[$i])); if ($total + $len > $max) break; array_splice($out, 1, 0, array($messages[$i])); $total += $len; } return $out; }
    private function cleanup_expired_opportunistically() { $this->ai_widget_model->delete_expired(); }
    private function is_student_count_question($question) { $q = strtolower(trim($question)); return (bool) (preg_match('/\b(how many|number of|count of|total(?: number)? of)\b.*\b(students?|pupils?)\b/i', $q) || preg_match('/\b(students?|pupils?)\b.*\b(total|count)\b/i', $q)); }
    private function is_pending_fee_month_question($question) { $has_fee = preg_match('/\b(pending|due|outstanding)\b.*\b(fee|fees)\b/i', $question) || preg_match('/\b(fee|fees)\b.*\b(pending|due|outstanding)\b/i', $question); return (bool) ($has_fee && preg_match('/\b(january|february|march|april|may|june|july|august|september|october|november|december|month)\b/i', $question)); }
    private function is_small_talk($question) { $q = strtolower(trim($question)); return in_array($q, array('hi','hello','hey','ok','okay','thanks','thank you','thx','good morning','good afternoon','good evening'), true); }
    private function question_month($question) { $months = array('january'=>1,'february'=>2,'march'=>3,'april'=>4,'may'=>5,'june'=>6,'july'=>7,'august'=>8,'september'=>9,'october'=>10,'november'=>11,'december'=>12); $q = strtolower($question); foreach ($months as $name => $number) if (strpos($q, $name) !== false) return $number; return (int) date('n'); }
    private function normalize_currency($reply) { $reply = preg_replace('/₹\s*([0-9][0-9,]*(?:\.\d+)?)/u', 'PKR $1', $reply); $reply = preg_replace('/\bRs\.?\s*([0-9][0-9,]*(?:\.\d+)?)/i', 'PKR $1', $reply); return $reply; }
    private function system_prompt() { return 'School assistant. For every school-data question, call the most relevant approved read-only tool before answering. Use count_students for student totals and matching report tools for fees, attendance, staff, classes, and other totals. For an unsupported admin report, first call get_database_schema, then use read_only_sql with one simple SELECT. Always format Pakistani currency as PKR, never use the ₹ symbol or other currency symbol. Never say tools are unavailable when an approved tool can answer. Never invent data, write data, expose private fields, or show SQL to the user. Answer briefly, using at most 120 words.'; }
    private function eligible() { $roles = isset($this->user['roles']) && is_array($this->user['roles']) ? array_keys($this->user['roles']) : array(isset($this->user['role']) ? $this->user['role'] : ''); foreach ($roles as $role) { $role = strtolower(trim($role)); if (in_array($role, array('admin', 'super admin', 'superadmin'), true)) return true; } return false; }
    private function primary_role() { $roles = isset($this->user['roles']) && is_array($this->user['roles']) ? array_keys($this->user['roles']) : array(isset($this->user['role']) ? $this->user['role'] : ''); return (string) reset($roles); }
    private function respond($data) { $this->output->set_content_type('application/json')->set_output(json_encode($data)); }
    private function json_error($code, $message, $status, $extra = array()) { $this->output->set_status_header($status); $this->respond(array_merge(array('error' => true, 'error_code' => $code, 'message' => $message), $extra)); return false; }
}
