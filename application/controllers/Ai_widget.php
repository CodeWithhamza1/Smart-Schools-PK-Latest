<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_widget extends Admin_Controller
{
    private $user;
    private $groq_rate = array();

    public function __construct()
    {
        parent::__construct();
        $this->config->load('groq');
        $this->load->model('ai_widget_model');
        $this->load->model('ai_knowledge_model');
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
        $this->ai_widget_model->rename_session_if_new($this->user['id'], $session_id, $question);

        if ($this->is_small_talk($question)) return $this->finish_small_talk($session_id, $question);

        // Questions with a fixed, well-known answer come straight from the database: instant, free and independent of the AI service.
        $quick = $this->quick_report($question);
        if ($quick) return $this->answer_from_report($session_id, $question, $quick);

        // Only questions that really call the AI service count towards the per-user limit (default 20 per 10 minutes).
        $limit = (int) $this->config->item('ai_widget_rate_limit'); if ($limit < 1) $limit = 20;
        $rate = $this->ai_widget_model->consume_rate_limit($this->user['id'], $limit);
        if (!$rate['allowed']) return $this->json_error('RATE_LIMIT', 'Please wait before asking another question.', 429, array('retry_after' => $rate['retry_after']));

        try {
            $answer = $this->run_agent($question, $session_id);
            return $this->finish_report($session_id, $question, $answer['tool'], array(), $answer['reply']);
        } catch (Throwable $e) {
            log_message('error', 'AI widget failure: ' . $e->getMessage());
            if ($e->getMessage() === 'GROQ_RATE_LIMIT') {
                $wait = isset($this->groq_rate['retry_after']) ? (int) $this->groq_rate['retry_after'] : 20;
                $detail = !empty($this->groq_rate['message']) ? ' Groq says: ' . $this->groq_rate['message'] : '';
                return $this->json_error('AI_RATE_LIMIT', 'The AI provider (Groq) is limiting this API key right now, not the assistant. Please try again in about ' . $wait . ' seconds.' . $detail, 429, array('retry_after' => $wait));
            }
            // The AI service is unavailable: still answer whatever can be answered directly from the database.
            try {
                $args = $this->infer_report_args($question);
                if (!empty($args)) return $this->answer_from_report($session_id, $question, $args, true);
                $hits = $this->ai_knowledge_model->search($question, 3, 1500);
                if (!empty($hits)) {
                    $lines = array('The AI service is unavailable, but this is what I found in the app\'s knowledge base:');
                    foreach ($hits as $hit) $lines[] = '- ' . $hit['topic'] . ': ' . $hit['content'];
                    return $this->finish_report($session_id, $question, 'knowledge_base', array(), implode("\n", $lines), array('ai_offline' => true));
                }
            } catch (Throwable $fallback_error) {
                log_message('error', 'AI widget fallback failure: ' . $fallback_error->getMessage());
            }
            return $this->ai_unavailable($e->getMessage());
        }
    }

    /** Re-indexes the app (menu, pages, code, tables, docs) into the knowledge base. */
    public function rebuild_knowledge()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->json_error('INVALID_INPUT', 'Invalid request.', 405);
        try {
            $stats = $this->ai_knowledge_model->refresh_if_stale(true);
            return $this->respond(array('error' => false, 'rebuilt' => $stats, 'knowledge' => $this->ai_knowledge_model->stats()));
        } catch (Throwable $e) {
            log_message('error', 'AI knowledge rebuild failed: ' . $e->getMessage());
            return $this->json_error('SERVER_ERROR', 'The knowledge base could not be rebuilt: ' . substr($e->getMessage(), 0, 200), 500);
        }
    }

    /** Called by the widget when it opens: refreshes a stale index in the background so questions never wait for it. */
    public function knowledge_stats()
    {
        try { $this->ai_knowledge_model->refresh_if_stale(); } catch (Throwable $e) { log_message('error', 'AI knowledge refresh failed: ' . $e->getMessage()); }
        $this->respond(array('error' => false, 'knowledge' => $this->ai_knowledge_model->stats()));
    }

    public function sessions() { $this->cleanup_expired_opportunistically(); $this->respond(array('error' => false, 'sessions' => $this->ai_widget_model->sessions($this->user['id']))); }
    public function history($session_id = '') { $this->cleanup_expired_opportunistically(); if (!$this->ai_widget_model->get_session($this->user['id'], $session_id)) return $this->json_error('SESSION_EXPIRED', 'This chat has expired and is no longer available.', 410); $this->respond(array('error' => false, 'session_id' => $session_id, 'messages' => $this->ai_widget_model->history($this->user['id'], $session_id))); }
    public function new_session() { $this->cleanup_expired_opportunistically(); $this->respond(array('error' => false, 'session_id' => $this->ai_widget_model->new_session($this->user['id']))); }
    public function delete_session($session_id = '') { if (!$this->ai_widget_model->get_session($this->user['id'], $session_id, true)) return $this->json_error('NOT_FOUND', 'That chat could not be found.', 404); $this->ai_widget_model->soft_delete($this->user['id'], $session_id); $this->respond(array('error' => false)); }

    /**
     * Tool-calling loop: the model may call school_report, get_database_schema and read_only_sql
     * several times (reading each result, fixing failed queries) before it writes the final answer.
     */
    private function run_agent($question, $session_id)
    {
        @set_time_limit(120);
        $max_steps = 5;
        $system = $this->agent_prompt();
        try {
            $this->ai_knowledge_model->refresh_if_stale();
            $hits = $this->ai_knowledge_model->search($question, 4, 1200);
            if (!empty($hits)) {
                $system .= "\n\nRelevant knowledge base entries (from the app's source code, database and what admins taught you; cite menu paths exactly):";
                foreach ($hits as $hit) $system .= "\n[#" . $hit['id'] . ' ' . $hit['source'] . '] ' . $hit['topic'] . ': ' . $hit['content'];
            }
        } catch (Throwable $e) {
            log_message('error', 'AI knowledge lookup failed: ' . $e->getMessage());
        }
        $messages = array(array('role' => 'system', 'content' => $system));
        foreach ($this->ai_widget_model->recent_messages($this->user['id'], $session_id, 4) as $message) {
            if (!in_array($message['role'], array('user', 'assistant'), true)) continue;
            $messages[] = array('role' => $message['role'], 'content' => substr((string) $message['content'], 0, 400));
        }
        $messages[] = array('role' => 'user', 'content' => $question);

        $tools = array_merge($this->ai_widget_model->agent_tool_definitions(), $this->knowledge_tools());
        $used = array();
        $working_sql = '';
        for ($step = 1; $step <= $max_steps; $step++) {
            // On the last step no tools are offered, which forces the model to answer with what it has.
            $offered = $step === $max_steps ? array() : $tools;
            try {
                $response = $this->groq($messages, $offered, 700);
            } catch (RuntimeException $e) {
                if ($e->getMessage() !== 'GROQ_TOOL_FAILED') throw $e;
                $response = $this->groq($messages, $offered, 700); // the model produced a malformed tool call; one retry
            }

            if (empty($response['tool_calls'])) {
                $reply = trim((string) $response['content']);
                if ($reply === '') $reply = 'I could not find an answer for that. Please try rephrasing with a class, date or name.';
                if ($working_sql !== '') {
                    try { $this->ai_knowledge_model->learn_query($question, $working_sql, $reply, (int) $this->user['id']); } catch (Throwable $e) { log_message('error', 'AI learn_query failed: ' . $e->getMessage()); }
                }
                return array('reply' => $reply, 'tool' => empty($used) ? null : substr(implode(', ', array_keys($used)), 0, 100));
            }

            $messages[] = array('role' => 'assistant', 'content' => (string) $response['content'], 'tool_calls' => $response['tool_calls']);
            foreach ($response['tool_calls'] as $call) {
                $name = isset($call['function']['name']) ? (string) $call['function']['name'] : '';
                $raw_args = isset($call['function']['arguments']) ? $call['function']['arguments'] : '{}';
                $args = is_array($raw_args) ? $raw_args : json_decode((string) $raw_args, true);
                if (!is_array($args)) $args = array();
                $result = $this->run_tool($name, $args);
                $label = $name === 'school_report' && !empty($args['report_type']) ? $name . ':' . preg_replace('/[^a-z_]/', '', strtolower((string) $args['report_type'])) : $name;
                if (in_array($name, array('school_report', 'get_database_schema', 'read_only_sql', 'search_knowledge', 'save_knowledge', 'forget_knowledge'), true)) $used[$label] = true;
                if ($name === 'read_only_sql' && isset($result['rows']) && !empty($result['rows']) && !empty($args['sql'])) $working_sql = (string) $args['sql'];
                $json = json_encode($result, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_UNICODE);
                if ($json === false) $json = '{"tool_error":"Result could not be encoded"}';
                if (strlen($json) > 3000) $json = substr($json, 0, 3000) . ' ...[truncated: use aggregates or a narrower query]';
                $messages[] = array('role' => 'tool', 'tool_call_id' => isset($call['id']) ? $call['id'] : ('call_' . $step), 'content' => $json);
            }
        }
        return array('reply' => 'That question needed more steps than I can take at once. Please ask for a narrower report.', 'tool' => null);
    }

    private function run_tool($name, $args)
    {
        $role = $this->primary_role();
        $user_id = (int) $this->user['id'];
        if ($name === 'school_report') return $this->ai_widget_model->execute_school_report($args, $role, $user_id);
        if ($name === 'get_database_schema' || $name === 'read_only_sql') return $this->ai_widget_model->execute_tool_for_ai($name, $args, $role, $user_id);
        try {
            if ($name === 'search_knowledge') return array('results' => $this->ai_knowledge_model->search(isset($args['query']) ? (string) $args['query'] : '', 6, 3500));
            if ($name === 'save_knowledge') return $this->ai_knowledge_model->remember(isset($args['topic']) ? $args['topic'] : '', isset($args['content']) ? $args['content'] : '', $user_id);
            if ($name === 'forget_knowledge') return $this->ai_knowledge_model->forget(isset($args['id']) ? (int) $args['id'] : 0);
        } catch (Throwable $e) {
            log_message('error', 'AI knowledge tool failed: ' . $e->getMessage());
            return array('tool_error' => substr($e->getMessage(), 0, 300));
        }
        return array('tool_error' => 'Unknown tool. Use school_report, get_database_schema, read_only_sql, search_knowledge, save_knowledge or forget_knowledge.');
    }

    private function knowledge_tools()
    {
        $tool = function ($name, $description, $properties, $required) {
            return array('type' => 'function', 'function' => array('name' => $name, 'description' => $description, 'parameters' => array('type' => 'object', 'properties' => $properties, 'required' => $required)));
        };
        return array(
            $tool('search_knowledge', 'Search knowledge about this software: sidebar menu paths, pages and their fields, settings, code, tables, docs, taught facts, learned queries.', array('query' => array('type' => 'string')), array('query')),
            $tool('save_knowledge', 'Save a fact the admin explicitly teaches or corrects.', array('topic' => array('type' => 'string'), 'content' => array('type' => 'string')), array('topic', 'content')),
            $tool('forget_knowledge', 'Delete a taught fact or learned query by #id.', array('id' => array('type' => 'integer')), array('id'))
        );
    }

    private function agent_prompt()
    {
        return 'You are the AI assistant inside a school management system (Smart School, MySQL). Today: ' . date('Y-m-d l') . '. Current academic session: ' . $this->ai_widget_model->current_session_name() . ' (session_id ' . $this->ai_widget_model->current_session_id() . '). Money: PKR.' . "\n"
            . 'Answer from tool results and the knowledge entries below only; never invent data. Tools: school_report (ready reports; always use it for fees), get_database_schema + read_only_sql (any table, one SELECT; check columns first; fix and retry on errors), search_knowledge (menus, pages, settings, code, taught facts), save_knowledge (only when the admin says remember/note/correct), forget_knowledge.' . "\n"
            . 'Key tables: students(id, admission_no, firstname, lastname, guardian_name, guardian_phone, is_active yes/no), student_session(student_id, session_id, class_id, section_id), classes(id, class), sections(id, section), staff(id, name, surname, is_active 1/0), sessions(id, session), income/expenses(date, name, amount), attendance_logs(admission_no, timestamp, status). Fee payments are JSON in student_fees_deposite.amount_detail, so use school_report for fees.' . "\n"
            . 'How-to/where/settings questions: give the exact sidebar path, page URL and fields. Never say something does not exist without searching. A table missing from a migration means that migration file must be imported.' . "\n"
            . 'Style: plain text, no markdown or tables, short lines, list items start with "- ", PKR with thousands separators, mention when a list is cut short. Greetings: answer briefly without tools. Use as few tool calls as possible.';
    }

    private function groq($messages, $tools = array(), $min_tokens = 0, $attempt = 0)
    {
        $key = $this->config->item('groq_api_key'); if (!$key) throw new RuntimeException('MISSING_GROQ_KEY');
        $max_tokens = (int) $this->config->item('ai_widget_max_completion_tokens'); if ($max_tokens < 1) $max_tokens = 500; if ($max_tokens < $min_tokens) $max_tokens = $min_tokens;
        $payload_data = array('model' => $this->config->item('groq_model'), 'messages' => $messages, 'temperature' => 0.1, 'reasoning_effort' => 'low', 'max_completion_tokens' => $max_tokens);
        if (!empty($tools)) { $payload_data['tools'] = $tools; $payload_data['tool_choice'] = 'auto'; }
        $payload = json_encode($payload_data);
        $this->pace_groq((int) (strlen($payload) / 4) + $max_tokens);
        $response_headers = array();
        $ch = curl_init($this->config->item('groq_endpoint'));
        curl_setopt_array($ch, array(CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 25, CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_HEADERFUNCTION => function ($handle, $header) use (&$response_headers) { $parts = explode(':', $header, 2); if (count($parts) === 2) $response_headers[strtolower(trim($parts[0]))] = trim($parts[1]); return strlen($header); }, CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $key, 'Content-Type: application/json'), CURLOPT_POSTFIELDS => $payload));
        $raw = curl_exec($ch); $status = curl_getinfo($ch, CURLINFO_HTTP_CODE); $error = curl_error($ch); $errno = curl_errno($ch); curl_close($ch);
        $this->remember_groq_budget($response_headers);
        if ($status === 429) {
            log_message('error', 'Groq rate limit: ' . substr((string) $raw, 0, 800));
            $body = json_decode((string) $raw, true);
            $retry_after = isset($response_headers['retry-after']) ? (float) $response_headers['retry-after'] : 0;
            if ($retry_after <= 0 && isset($body['error']['message']) && preg_match('/try again in ([\d.]+)\s*(ms|s|m)/i', $body['error']['message'], $wait)) $retry_after = strtolower($wait[2]) === 'ms' ? $wait[1] / 1000 : (strtolower($wait[2]) === 'm' ? $wait[1] * 60 : (float) $wait[1]);
            $this->groq_rate = array('retry_after' => (int) ceil($retry_after > 0 ? $retry_after : 20), 'message' => isset($body['error']['message']) ? substr((string) $body['error']['message'], 0, 300) : '');
            // Free-tier limits reset within seconds: wait and retry (twice) instead of failing the question.
            if ($attempt < 2 && $retry_after > 0 && $retry_after <= 20) { usleep((int) ($retry_after * 1000000) + 200000); return $this->groq($messages, $tools, $min_tokens, $attempt + 1); }
            throw new RuntimeException('GROQ_RATE_LIMIT');
        }
        if ($status === 400 && strpos((string) $raw, 'tool_use_failed') !== false) { log_message('error', 'Groq malformed tool call: ' . substr((string) $raw, 0, 600)); throw new RuntimeException('GROQ_TOOL_FAILED'); }
        if ($errno) { log_message('error', 'Groq network failure: curl_errno=' . (int) $errno . '; ' . substr((string) $error, 0, 300)); throw new RuntimeException('GROQ_NETWORK:' . (int) $errno . ' ' . substr((string) $error, 0, 120)); }
        if ($status === 401 || $status === 403) { log_message('error', 'Groq rejected the API key (HTTP ' . (int) $status . '): ' . substr((string) $raw, 0, 300)); throw new RuntimeException('GROQ_AUTH'); }
        if ($raw === false || $error || $status >= 400) { log_message('error', 'Groq HTTP failure: status=' . (int) $status . '; curl_errno=' . (int) $errno . '; curl_error=' . substr((string) $error, 0, 300) . '; response=' . substr((string) $raw, 0, 1200)); throw new RuntimeException('GROQ_REQUEST_FAILED'); }
        $json = json_decode($raw, true);
        if (!isset($json['choices'][0]['message'])) throw new RuntimeException('INVALID_GROQ_RESPONSE');
        $message = $json['choices'][0]['message'];
        return array('content' => isset($message['content']) ? $message['content'] : '', 'tool_calls' => isset($message['tool_calls']) ? $message['tool_calls'] : array());
    }

    /*
     * Groq reports the tokens left in the current minute in response headers. Remember them (shared by all
     * requests) and, when the next call would not fit, wait for the reset instead of triggering a 429.
     */
    private function groq_budget_file() { return APPPATH . 'cache/ai_groq_budget.json'; }

    private function pace_groq($tokens_needed)
    {
        $budget = @json_decode((string) @file_get_contents($this->groq_budget_file()), true);
        if (!is_array($budget) || empty($budget['reset_at'])) return;
        $wait = (float) $budget['reset_at'] - microtime(true);
        if ($wait <= 0 || (int) $budget['remaining'] >= $tokens_needed) return;
        usleep((int) (min(20, $wait) * 1000000));
    }

    private function remember_groq_budget($headers)
    {
        if (!isset($headers['x-ratelimit-remaining-tokens'])) return;
        $reset = isset($headers['x-ratelimit-reset-tokens']) ? $this->parse_duration($headers['x-ratelimit-reset-tokens']) : 0;
        @file_put_contents($this->groq_budget_file(), json_encode(array('remaining' => (int) $headers['x-ratelimit-remaining-tokens'], 'reset_at' => microtime(true) + $reset)), LOCK_EX);
    }

    /** "7.66s", "1m2.5s" or "120ms" in seconds. */
    private function parse_duration($text)
    {
        $seconds = 0.0;
        $units = array('ms' => 0.001, 's' => 1, 'm' => 60, 'h' => 3600);
        if (preg_match_all('/([\d.]+)(ms|s|m|h)/', (string) $text, $parts, PREG_SET_ORDER)) foreach ($parts as $part) $seconds += (float) $part[1] * $units[$part[2]];
        return $seconds;
    }

    private function finish_small_talk($session_id, $question)
    {
        $reply = in_array(strtolower(trim($question)), array('thanks', 'thank you', 'thx'), true) ? 'You are welcome. Ask me whenever you need a school report.' : 'Sure. Ask me about students, attendance, fees, classes, staff, or another school report.';
        return $this->finish_report($session_id, $question, null, array(), $reply);
    }

    private function finish_report($session_id, $question, $tool, $result, $reply, $extra = array())
    {
        $reply = $this->normalize_currency(substr(trim((string) $reply), 0, 4000));
        $this->ai_widget_model->save_message($this->user['id'], $session_id, 'user', $question);
        $this->ai_widget_model->save_message($this->user['id'], $session_id, 'assistant', $reply, $tool);
        return $this->respond(array_merge(array('error' => false, 'session_id' => $session_id, 'reply' => $reply, 'tool_used' => $tool, 'widget_version' => '2026-10-28-agent-v2'), $extra));
    }

    /** Runs one report straight from the database and answers in plain text, without calling the AI service. */
    private function answer_from_report($session_id, $question, $args, $ai_offline = false)
    {
        $type = $args['report_type'];
        $result = $this->ai_widget_model->execute_school_report($args, $this->primary_role(), (int) $this->user['id']);
        return $this->finish_report($session_id, $question, 'school_report:' . $type, $result, $this->format_result_fallback($type, $result), $ai_offline ? array('ai_offline' => true) : array());
    }

    /** Explains why the AI service could not be used, in words an administrator can act on. */
    private function ai_unavailable($reason)
    {
        $reason = (string) $reason;
        $basics = 'I can still answer basic questions without it: student counts (also per class), pending and collected fees, attendance, staff, income and expenses.';
        if ($reason === 'MISSING_GROQ_KEY') return $this->json_error('AI_UNAVAILABLE', 'No Groq API key is configured, so the AI part of the assistant is switched off. ' . $basics . ' An administrator can add a key in application/config/groq.php or the GROQ_API_KEY environment variable.', 503, array('reason' => $reason));
        if ($reason === 'GROQ_AUTH') return $this->json_error('AI_UNAVAILABLE', 'The AI service rejected the Groq API key (invalid or expired). ' . $basics . ' An administrator needs to create a new key at console.groq.com and set it in application/config/groq.php or the GROQ_API_KEY environment variable.', 503, array('reason' => $reason));
        if (strpos($reason, 'GROQ_NETWORK') === 0) return $this->json_error('AI_UNAVAILABLE', 'The server could not reach the AI service (' . substr($reason, 13) . '). ' . $basics . ' Check the internet connection and the SSL certificate bundle (curl.cainfo) in php.ini.', 503, array('reason' => 'GROQ_NETWORK'));
        return $this->json_error('SERVER_ERROR', 'The report could not be generated right now.', 500);
    }

    private function pkr($amount) { return 'PKR ' . number_format((float) $amount, 2); }

    private function range_label($start, $end) { return $start === $end ? 'on ' . $start : 'from ' . $start . ' to ' . $end; }

    private function format_result_fallback($type, $result)
    {
        if (isset($result['report_error'])) return $result['report_error'];
        if (isset($result['error']) && is_string($result['error'])) return $result['error'];

        if ($type === 'current_session' && !empty($result['session'])) return 'The current academic session is ' . $result['session'] . '.';
        if ($type === 'student_count' && isset($result['total_students'])) {
            return 'There are ' . (int) $result['total_students'] . ' active students in the current academic session.';
        }
        if ($type === 'students_by_class' && isset($result['rows'])) {
            if (empty($result['rows'])) return 'No active students were found for the current session.';
            $lines = array('Active students by class (current session):');
            foreach ($result['rows'] as $row) $lines[] = '- ' . $row['class'] . ': ' . (int) $row['students'] . (!empty($row['sections']) ? ' (' . $row['sections'] . ')' : '');
            $lines[] = 'Total: ' . (int) $result['total_students'] . ' students.';
            return implode("\n", $lines);
        }
        if ($type === 'pending_fee_total' && isset($result['total_pending'])) {
            $lines = array('Total pending fees: ' . $this->pkr($result['total_pending']) . ' (' . (int) $result['fee_items_with_balance'] . ' unpaid or part-paid fee items this session).');
            if (!empty($result['by_month'])) {
                $lines[] = 'By month:';
                foreach ($result['by_month'] as $row) $lines[] = '- ' . $row['month'] . ': ' . $this->pkr($row['pending']);
            }
            return implode("\n", $lines);
        }
        if ($type === 'pending_fee_summary' && isset($result['total_pending'])) {
            return 'Pending fees for ' . date('F', mktime(0, 0, 0, (int) $result['month'], 1)) . ': ' . $this->pkr($result['total_pending']) . '.';
        }
        if ($type === 'fee_collection' && isset($result['total_collected'])) {
            $lines = array('Fees collected ' . $this->range_label($result['start_date'], $result['end_date']) . ': ' . $this->pkr($result['total_collected']));
            $lines[] = '- Fees: ' . $this->pkr($result['fees_collected']) . ', fines: ' . $this->pkr($result['fines_collected']) . ', ' . (int) $result['payments'] . ' payment(s).';
            if (!empty($result['by_date_latest_first']) && count($result['by_date_latest_first']) > 1) {
                $lines[] = 'By date:';
                foreach ($result['by_date_latest_first'] as $day => $total) $lines[] = '- ' . $day . ': ' . $this->pkr($total);
            }
            return implode("\n", $lines);
        }
        if (isset($result['total_pending'])) return 'Pending fees: ' . $this->pkr($result['total_pending']) . '.';

        $rows = isset($result['rows']) && is_array($result['rows']) ? $result['rows'] : (array_values($result) === $result ? $result : array($result));
        if (empty($rows)) return 'No matching records were found.';
        $lines = array('Report retrieved ' . count($rows) . ' record(s):');
        foreach (array_slice($rows, 0, 10) as $row) { if (!is_array($row)) continue; $parts = array(); foreach ($row as $key => $value) { if (is_scalar($value) && $value !== '') $parts[] = str_replace('_', ' ', $key) . ': ' . $value; if (count($parts) >= 6) break; } if (!empty($parts)) $lines[] = '- ' . implode(', ', $parts); }
        return implode("\n", $lines);
    }

    /** Start and end date implied by words such as today, yesterday, this week, last month or a month name. */
    private function detect_dates($question, $default = 'month')
    {
        $q = strtolower($question);
        $today = date('Y-m-d');
        if (strpos($q, 'yesterday') !== false) { $d = date('Y-m-d', strtotime('-1 day')); return array($d, $d); }
        if (strpos($q, 'today') !== false) return array($today, $today);
        if (preg_match('/\blast\s+(\d{1,3})\s+days?\b/', $q, $m)) return array(date('Y-m-d', strtotime('-' . max(1, (int) $m[1]) . ' days')), $today);
        if (preg_match('/\blast\s+week\b|\bprevious\s+week\b/', $q)) return array(date('Y-m-d', strtotime('monday last week')), date('Y-m-d', strtotime('sunday last week')));
        if (preg_match('/\bthis\s+week\b/', $q)) return array(date('Y-m-d', strtotime('monday this week')), $today);
        if (preg_match('/\blast\s+month\b|\bprevious\s+month\b/', $q)) return array(date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('first day of last month')));
        if (preg_match('/\bthis\s+year\b/', $q)) return array(date('Y-01-01'), $today);
        foreach (array('january' => 1, 'february' => 2, 'march' => 3, 'april' => 4, 'may' => 5, 'june' => 6, 'july' => 7, 'august' => 8, 'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12) as $name => $number) {
            if (preg_match('/\b' . $name . '\b(?:\s+(20\d{2}))?/', $q, $m)) {
                $year = !empty($m[1]) ? (int) $m[1] : (int) date('Y');
                if (empty($m[1]) && $number > (int) date('n')) $year--; // "december" asked in October means last December
                $first = sprintf('%04d-%02d-01', $year, $number);
                return array($first, date('Y-m-t', strtotime($first)));
            }
        }
        if ($default === 'today') return array($today, $today);
        return array(date('Y-m-01'), $today); // this month so far
    }

    private function wants_pending_fees($q) { return (bool) (preg_match('/\b(fee|fees)\b/', $q) && preg_match('/\b(pending|due|outstanding|unpaid|remaining|receivable|balance|arrears|dues)\b/', $q)); }
    private function wants_fee_collection($q) { return (bool) (preg_match('/\b(fee|fees)\b/', $q) && preg_match('/\b(collect|collected|collection|received|receive|paid|payments?|deposited)\b/', $q) && !$this->wants_pending_fees($q)); }
    private function wants_per_class($q) { return (bool) preg_match('/\b(each|every|per|by)\s+(class|classes|section|sections|grade)\b|\b(class|section|grade)[\s-]?wise\b|\bin\s+(all|every)\s+classes\b|\bclasses?\s+(and\s+)?sections?\b/', $q); }

    /**
     * Questions with one fixed, well-known answer. They are answered straight from the database
     * (instant, no AI tokens, and they keep working when the AI service is down).
     */
    private function quick_report($question)
    {
        $q = strtolower(trim($question));
        if ($q === '' || str_word_count($q) > 14) return null; // long or detailed questions are better handled by the AI
        if (preg_match('/\b(how (do|to|can|should)|where|configure|configuration|settings?|setup|set up|change|edit|enable|disable|create|add|remember|forget|note that)\b/', $q)) return null; // how-to and teaching questions go to the AI

        if (preg_match('/\bsession\b/', $q) && !preg_match('/\b(attendance|class|classes|section|student|students|fee|fees|staff|exam|change|switch|create|add|delete|list|all|previous|old|past|next)\b/', $q) && str_word_count($q) <= 8) return array('report_type' => 'current_session');
        $about_students = (bool) preg_match('/\b(students?|pupils?|children|kids|strength|enrol+ed|enrol+ment)\b/', $q);
        $counting = (bool) preg_match('/\b(how many|number of|count|total|strength|show|list)\b/', $q);
        if ($about_students && $counting && $this->wants_per_class($q)) return array('report_type' => 'students_by_class');
        if ($this->is_student_count_question($q)) return array('report_type' => 'student_count');

        $specific = '/\b(class|classes|section|grade|student|students|name|names|contact|contacts|parent|parents|guardian|who|each|per|by|top|highest|lowest|more than|less than|months?|list|detail|details)\b/';
        if ($this->is_pending_fee_month_question($q)) return array('report_type' => 'pending_fee_summary', 'month' => $this->question_month($q));
        if ($this->wants_pending_fees($q) && !preg_match($specific, $q)) return array('report_type' => 'pending_fee_total');
        if ($this->wants_fee_collection($q) && !preg_match('/\b(class|classes|section|grade|student|students|name|names|who|each|per|by|top)\b/', $q)) {
            $dates = $this->detect_dates($q, 'month');
            return array('report_type' => 'fee_collection', 'start_date' => $dates[0], 'end_date' => $dates[1]);
        }
        return null;
    }

    /** Best-effort report choice used when the AI service is unavailable. */
    private function infer_report_args($question)
    {
        $quick = $this->quick_report($question);
        if ($quick) return $quick;

        $q = strtolower($question);
        // The direct reports cannot filter by class or section, so never answer such a question with unfiltered data.
        if (preg_match('/\b(class|classes|section|sections|grade)\b/', $q) && !$this->wants_per_class($q)) return array();
        $args = array('limit' => 25);
        if ($this->is_pending_fee_month_question($q)) { $args['report_type'] = 'pending_fee_summary'; $args['month'] = $this->question_month($q); return $args; }
        if ($this->wants_pending_fees($q)) {
            if (preg_match('/\b(name|names|contact|contacts|parent|parents|guardian|student|students|who|list)\b/', $q)) { $args['report_type'] = 'pending_fee_students'; if (preg_match('/\b(\d{1,2})\s+months?\b/', $q, $m)) $args['months'] = min(12, (int) $m[1]); return $args; }
            $args['report_type'] = 'pending_fee_total'; return $args;
        }
        if ($this->wants_fee_collection($q)) { $dates = $this->detect_dates($q, 'month'); return array('report_type' => 'fee_collection', 'start_date' => $dates[0], 'end_date' => $dates[1]); }
        if (preg_match('/attendance|absent|present|late/', $q)) { $dates = $this->detect_dates($q, 'today'); $args['report_type'] = 'attendance_summary'; $args['start_date'] = $dates[0]; $args['end_date'] = $dates[1]; return $args; }
        if (preg_match('/staff|teacher|employee/', $q)) { $args['report_type'] = 'staff_search'; $args['query'] = trim(preg_replace('/\b(staff|teachers?|employees?|members?|report|list|show|give|me|all|the|of|my|our|school|who|are|is|what|how|many|total|number|names?)\b/', ' ', $q)); return $args; }
        if ($this->wants_per_class($q) && preg_match('/student|pupil/', $q)) return array('report_type' => 'students_by_class');
        if ($this->is_student_count_question($q)) return array('report_type' => 'student_count');
        if (preg_match('/income|revenue/', $q)) { $dates = $this->detect_dates($q, 'month'); $args['report_type'] = 'income'; $args['start_date'] = $dates[0]; $args['end_date'] = $dates[1]; return $args; }
        if (preg_match('/expense|cost|spending/', $q)) { $dates = $this->detect_dates($q, 'month'); $args['report_type'] = 'expenses'; $args['start_date'] = $dates[0]; $args['end_date'] = $dates[1]; return $args; }
        if (preg_match('/student|pupil|admission/', $q)) {
            $query = trim(preg_replace('/\b(students?|pupils?|admission|report|list|show|give|me|all|the|of|my|our|school|who|are|is|what|how|many|names?)\b/', ' ', $q));
            if ($query !== '') { $args['report_type'] = 'student_search'; $args['query'] = $query; return $args; }
        }
        return array();
    }

    private function cleanup_expired_opportunistically() { $this->ai_widget_model->delete_expired(); }
    private function is_student_count_question($question) { $q = strtolower(trim($question)); if (preg_match('/\b(each|every|per|by|class|classes|section|sections|grade|boys|girls|male|female|new|admitted|left|disabled|inactive|absent|present|pending|fee|fees|staff|teachers?)\b/', $q)) return false; return (bool) (preg_match('/\b(how many|number of|count of|total(?: number)? of)\b.*\b(students?|pupils?)\b/i', $q) || preg_match('/\b(students?|pupils?)\b.*\b(total|count)\b/i', $q)); }
    private function is_pending_fee_month_question($question) { $q = strtolower($question); if (!$this->wants_pending_fees($q) || preg_match('/\b(names?|contacts?|parents?|students?|class|classes|section)\b/', $q)) return false; return (bool) preg_match('/\b(january|february|march|april|may|june|july|august|september|october|november|december|this month|current month|last month|previous month)\b/', $q); }
    private function is_small_talk($question) { return in_array(strtolower(trim($question)), array('hi','hello','hey','ok','okay','thanks','thank you','thx','good morning','good afternoon','good evening'), true); }
    private function question_month($question) { $months = array('january'=>1,'february'=>2,'march'=>3,'april'=>4,'may'=>5,'june'=>6,'july'=>7,'august'=>8,'september'=>9,'october'=>10,'november'=>11,'december'=>12); $q = strtolower($question); foreach ($months as $name => $number) if (strpos($q, $name) !== false) return $number; if (preg_match('/\b(last|previous) month\b/', $q)) return (int) date('n', strtotime('first day of last month')); return (int) date('n'); }
    private function normalize_currency($reply) { $reply = preg_replace('/₹\s*([0-9][0-9,]*(?:\.\d+)?)/u', 'PKR $1', $reply); return preg_replace('/\bRs\.?\s*([0-9][0-9,]*(?:\.\d+)?)/i', 'PKR $1', $reply); }
    private function eligible() { $roles = isset($this->user['roles']) && is_array($this->user['roles']) ? array_keys($this->user['roles']) : array(isset($this->user['role']) ? $this->user['role'] : ''); foreach ($roles as $role) if (in_array(strtolower(trim($role)), array('admin','super admin','superadmin'), true)) return true; return false; }
    private function primary_role() { $roles = isset($this->user['roles']) && is_array($this->user['roles']) ? array_keys($this->user['roles']) : array(isset($this->user['role']) ? $this->user['role'] : ''); return (string) reset($roles); }
    private function respond($data) { $this->output->set_content_type('application/json')->set_output(json_encode($data)); }
    private function json_error($code, $message, $status, $extra = array()) { $this->output->set_status_header($status); $this->respond(array_merge(array('error' => true, 'error_code' => $code, 'message' => $message), $extra)); return false; }
}
