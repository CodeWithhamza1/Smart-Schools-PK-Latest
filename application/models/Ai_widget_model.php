<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ai_widget_model extends CI_Model
{
    const EXPIRY_HOURS = 48;

    public function __construct()
    {
        parent::__construct();
        $this->load->model(array('student_model', 'attendance_model', 'studentsession_model', 'class_model', 'section_model', 'subject_model', 'timetable_model', 'staff_model', 'homework_model', 'book_model', 'bookissue_model', 'route_model', 'hostel_model', 'hostelroom_model', 'itemstock_model', 'income_model', 'expense_model', 'onlineexam_model', 'leaverequest_model', 'syllabus_model', 'enquiry_model', 'visitors_model', 'Complaint_Model' => 'complaint_model', 'general_call_model', 'dispatch_model', 'calendar_model', 'content_model', 'notification_model'));
        $this->current_session = $this->setting_model->getCurrentSession();
    }

    public function tool_definitions($question = '')
    {
        return array(array(
            'type' => 'function',
            'function' => array(
                'name' => 'school_report',
                'description' => 'Select one safe school report. Never provide SQL or database table names. Report types: current_session, student_count, students_by_class, student_search, student_profile, attendance_summary, pending_fee_total (ALL pending fees, use for "how much fee is pending"), pending_fee_summary (one month, needs month), pending_fee_students (students who owe, with parent contacts), due_fees, fee_collection (money received between start_date and end_date), student_results, classes, sections, subjects, timetable, staff_search, staff_profile, staff_leave, homework, library_books, library_due, transport_routes, transport_vehicles, hostels, inventory_stock, income, expenses, enquiries, visitors, complaints, phone_calls, dispatch_receive, calendar_events, learning_content, notices, online_exams, syllabus_status.',
                'parameters' => array(
                    'type' => 'object',
                    'properties' => array(
                        'report_type' => array('type' => 'string'),
                        'query' => array('type' => 'string'),
                        'student_id' => array('type' => 'integer'),
                        'admission_no' => array('type' => 'string'),
                        'staff_id' => array('type' => 'integer'),
                        'class_id' => array('type' => 'integer'),
                        'section_id' => array('type' => 'integer'),
                        'exam_id' => array('type' => 'integer'),
                        'start_date' => array('type' => 'string', 'description' => 'YYYY-MM-DD'),
                        'end_date' => array('type' => 'string', 'description' => 'YYYY-MM-DD'),
                        'month' => array('type' => 'integer'),
                        'months' => array('type' => 'integer'),
                        'category' => array('type' => 'string'),
                        'limit' => array('type' => 'integer')
                    ),
                    'required' => array('report_type')
                )
            )
        ));
    }

    /** Tools offered to the AI agent loop: ready-made reports plus schema discovery and read-only SQL. */
    public function agent_tool_definitions()
    {
        $tools = $this->tool_definitions();
        $tools[] = array('type' => 'function', 'function' => array(
            'name' => 'get_database_schema',
            'description' => 'Discover the school database. With no arguments returns every available table name. Pass tables (up to 12) to get their column names, or search to find table names containing a word (for example fee, exam, hostel, transport).',
            'parameters' => array('type' => 'object', 'properties' => array(
                'tables' => array('type' => 'array', 'items' => array('type' => 'string'), 'description' => 'Up to 12 table names whose columns you need.'),
                'search' => array('type' => 'string', 'description' => 'Word to search for in table names.')
            ))
        ));
        $tools[] = array('type' => 'function', 'function' => array(
            'name' => 'read_only_sql',
            'description' => 'Run ONE read-only SELECT statement against ANY table in the school database (JOIN, subqueries, UNION, WHERE, GROUP BY, COUNT/SUM/AVG, date filters, ORDER BY; max 200 rows, default 100). Use real table and column names from get_database_schema. No comments, no semicolons, no writes. Password and secret columns come back as [hidden].',
            'parameters' => array('type' => 'object', 'properties' => array('sql' => array('type' => 'string', 'description' => 'One SELECT statement.')), 'required' => array('sql'))
        ));
        return $tools;
    }

    public function current_session_id() { return $this->current_session; }
    public function current_session_name() { return $this->setting_model->getCurrentSessionName(); }

    private function additional_tool_definitions()
    {
        $text = function ($name, $description, $properties = array(), $required = array()) {
            $parameters = array('type' => 'object', 'properties' => empty($properties) ? new stdClass() : $properties);
            if (!empty($required)) $parameters['required'] = array_values($required);
            return array('type' => 'function', 'function' => array('name' => $name, 'description' => $description, 'parameters' => $parameters));
        };
        $id = array('type' => 'integer'); $date = array('type' => 'string', 'description' => 'YYYY-MM-DD');
        return array(
            $text('count_students', 'Count active students in the current academic session. Use this for questions asking how many students are in the school. Optionally filter by class or section.', array('class_id' => $id, 'section_id' => $id)),
            $text('get_classes', 'List available academic classes.'),
            $text('get_sections', 'List sections for a class.', array('class_id' => $id), array('class_id')),
            $text('get_subjects', 'List available subjects.'),
            $text('get_class_timetable', 'Get the timetable for a class and section.', array('class_id' => $id, 'section_id' => $id), array('class_id', 'section_id')),
            $text('search_staff', 'Search the active staff directory.', array('query' => array('type' => 'string'), 'limit' => array('type' => 'integer', 'minimum' => 1, 'maximum' => 50)), array('query')),
            $text('get_staff_profile', 'Get a limited staff profile.', array('staff_id' => $id), array('staff_id')),
            $text('get_staff_leave_requests', 'List leave requests for a staff member or all staff.', array('staff_id' => $id, 'limit' => array('type' => 'integer', 'minimum' => 1, 'maximum' => 100))),
            $text('get_homework', 'List current-session homework, optionally filtered by class and section.', array('class_id' => $id, 'section_id' => $id, 'limit' => array('type' => 'integer', 'minimum' => 1, 'maximum' => 100))),
            $text('get_library_books', 'List books in the library.', array('limit' => array('type' => 'integer', 'minimum' => 1, 'maximum' => 100))),
            $text('get_library_due_books', 'List library books due for return in a date range.', array('start_date' => $date, 'end_date' => $date), array('start_date', 'end_date')),
            $text('get_transport_routes', 'List configured transport routes.'),
            $text('get_transport_vehicles', 'List configured transport vehicles.'),
            $text('get_hostels', 'List configured hostels and rooms.'),
            $text('get_inventory_stock', 'List current inventory stock.'),
            $text('get_income_report', 'List income records in a date range.', array('start_date' => $date, 'end_date' => $date, 'limit' => array('type' => 'integer', 'minimum' => 1, 'maximum' => 100)), array('start_date', 'end_date')),
            $text('get_expense_report', 'List expense records in a date range.', array('start_date' => $date, 'end_date' => $date, 'limit' => array('type' => 'integer', 'minimum' => 1, 'maximum' => 100)), array('start_date', 'end_date')),
            $text('get_enquiries', 'List admission enquiries in a date range.', array('start_date' => $date, 'end_date' => $date), array('start_date', 'end_date')),
            $text('get_visitors', 'List visitor-book records.'),
            $text('get_complaints', 'List complaint records.'),
            $text('get_phone_calls', 'List phone call log records.'),
            $text('get_dispatch_receive', 'List postal dispatch and receive records.'),
            $text('get_calendar_events', 'List calendar events.'),
            $text('get_learning_content', 'List published learning/content records.', array('category' => array('type' => 'string'))),
            $text('get_notices', 'List published staff notices.'),
            $text('get_online_exams', 'List configured online examinations.'),
            $text('get_syllabus_status', 'Get syllabus status for a class and section.', array('class_id' => $id, 'section_id' => $id), array('class_id', 'section_id')),
            $text('get_database_schema', 'Admin-only discovery of available school database tables and safe column names. Use this before read_only_sql when a report is not covered by another tool.'),
            $text('read_only_sql', 'Admin-only fallback for school reports not covered by a specialized tool. Use real tables and columns from get_database_schema. Generate one simple SELECT query, including JOIN, WHERE, GROUP BY, aggregate totals, and date filters when needed. Never use INSERT, UPDATE, DELETE, DROP, ALTER, TRUNCATE, procedures, comments, or multiple statements. Return SQL only inside the sql argument.', array('sql' => array('type' => 'string', 'description' => 'One read-only SELECT statement.')) , array('sql')),
            $text('read_only_query', 'Run a basic single-table read-only lookup using approved tables and columns. Do not write SQL text; provide structured table, columns, filters, search, ordering, and limit parameters.', array(
                'table' => array('type' => 'string', 'enum' => array_keys($this->read_only_tables())),
                'columns' => array('type' => 'array', 'items' => array('type' => 'string')),
                'filters' => array('type' => 'object', 'additionalProperties' => array('type' => 'string')),
                'search_column' => array('type' => 'string'),
                'search' => array('type' => 'string'),
                'order_by' => array('type' => 'string'),
                'order_direction' => array('type' => 'string', 'enum' => array('ASC', 'DESC')),
                'limit' => array('type' => 'integer', 'minimum' => 1, 'maximum' => 50)
            ), array('table', 'columns'))
        );
    }

    public function new_session($user_id, $title = 'New chat')
    {
        $id = function_exists('uuid_create') && defined('UUID_TYPE_RANDOM') ? uuid_create(UUID_TYPE_RANDOM) : $this->uuid();
        $this->db->insert('ai_widget_sessions', array(
            'session_id' => $id, 'user_id' => (int) $user_id,
            'title' => $this->clean_title($title), 'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ));
        return $id;
    }

    public function get_session($user_id, $session_id, $include_expired = false)
    {
        $this->db->where(array('user_id' => (int) $user_id, 'session_id' => $session_id, 'deleted_at' => null));
        if (!$include_expired) {
            $this->db->where('updated_at >=', date('Y-m-d H:i:s', strtotime('-' . self::EXPIRY_HOURS . ' hours')));
        }
        return $this->db->get('ai_widget_sessions')->row_array();
    }

    public function sessions($user_id)
    {
        $this->db->select('session_id,title,created_at,updated_at')->from('ai_widget_sessions');
        $this->db->where(array('user_id' => (int) $user_id, 'deleted_at' => null));
        $this->db->where('updated_at >=', date('Y-m-d H:i:s', strtotime('-' . self::EXPIRY_HOURS . ' hours')));
        return $this->db->order_by('updated_at', 'DESC')->get()->result_array();
    }

    public function history($user_id, $session_id)
    {
        $this->db->select('role,content,tool_used,created_at')->from('ai_widget_conversations');
        $this->db->where(array('user_id' => (int) $user_id, 'session_id' => $session_id));
        return $this->db->order_by('id', 'ASC')->get()->result_array();
    }

    public function recent_messages($user_id, $session_id, $limit = 10)
    {
        $this->db->select('role,content,tool_used')->from('ai_widget_conversations');
        $this->db->where(array('user_id' => (int) $user_id, 'session_id' => $session_id));
        return array_reverse($this->db->order_by('id', 'DESC')->limit((int) $limit)->get()->result_array());
    }

    public function save_message($user_id, $session_id, $role, $content, $tool_used = null)
    {
        $this->db->insert('ai_widget_conversations', array(
            'user_id' => (int) $user_id, 'session_id' => $session_id,
            'role' => $role, 'content' => substr((string) $content, 0, 20000),
            'tool_used' => $tool_used, 'created_at' => date('Y-m-d H:i:s')
        ));
        $this->db->where(array('user_id' => (int) $user_id, 'session_id' => $session_id));
        $this->db->update('ai_widget_sessions', array('updated_at' => date('Y-m-d H:i:s')));
    }

    public function rename_session_if_new($user_id, $session_id, $content)
    {
        $session = $this->get_session($user_id, $session_id, true);
        if ($session && $session['title'] === 'New chat') {
            $this->db->where(array('user_id' => (int) $user_id, 'session_id' => $session_id));
            $this->db->update('ai_widget_sessions', array('title' => $this->clean_title($content)));
        }
    }

    public function soft_delete($user_id, $session_id)
    {
        $this->db->where(array('user_id' => (int) $user_id, 'session_id' => $session_id));
        return $this->db->update('ai_widget_sessions', array('deleted_at' => date('Y-m-d H:i:s')));
    }

    public function consume_rate_limit($user_id, $max = 15, $window = 600)
    {
        $now = time();
        $row = $this->db->where('user_id', (int) $user_id)->get('ai_widget_usage')->row_array();
        if (!$row || strtotime($row['window_start']) <= $now - $window) {
            $data = array('user_id' => (int) $user_id, 'request_count' => 1, 'window_start' => date('Y-m-d H:i:s'));
            if ($row) $this->db->where('user_id', (int) $user_id)->update('ai_widget_usage', $data);
            else $this->db->insert('ai_widget_usage', $data);
            return array('allowed' => true, 'retry_after' => 0);
        }
        if ((int) $row['request_count'] >= $max) {
            return array('allowed' => false, 'retry_after' => max(1, $window - ($now - strtotime($row['window_start']))));
        }
        $this->db->where('user_id', (int) $user_id)->set('request_count', 'request_count + 1', false)->update('ai_widget_usage');
        return array('allowed' => true, 'retry_after' => 0);
    }

    public function delete_expired()
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . self::EXPIRY_HOURS . ' hours'));
        $expired = $this->db->select('session_id')->where('updated_at <', $cutoff)->get('ai_widget_sessions')->result_array();
        foreach ($expired as $row) {
            $this->db->where('session_id', $row['session_id'])->delete('ai_widget_conversations');
            $this->db->where('session_id', $row['session_id'])->delete('ai_widget_sessions');
        }
        return count($expired);
    }

    public function execute_tool($name, $args, $role, $user_id = 0)
    {
        $allowed = array('count_students' => 'student', 'search_students' => 'student', 'get_student_profile' => 'student', 'get_attendance_summary' => 'attendance', 'get_due_fees' => 'search_due_fees', 'get_student_results' => 'examination', 'get_classes' => 'class', 'get_sections' => 'section', 'get_subjects' => 'subject', 'get_class_timetable' => 'class_timetable', 'search_staff' => 'staff', 'get_staff_profile' => 'staff', 'get_staff_leave_requests' => 'approve_leave_request', 'get_homework' => 'homework', 'get_library_books' => 'book', 'get_library_due_books' => 'book', 'get_transport_routes' => 'routes', 'get_transport_vehicles' => 'routes', 'get_hostels' => 'hostel', 'get_inventory_stock' => 'item_stock', 'get_income_report' => 'search_income', 'get_expense_report' => 'search_expense', 'get_enquiries' => 'admission_enquiry', 'get_visitors' => 'visitor_book', 'get_complaints' => 'complaint', 'get_phone_calls' => 'phone_call_log', 'get_dispatch_receive' => 'postal_dispatch', 'get_calendar_events' => 'calendar_to_do_list', 'get_learning_content' => 'content', 'get_notices' => 'notice_board', 'get_online_exams' => 'online_examination', 'get_syllabus_status' => 'manage_syllabus_status', 'get_database_schema' => 'admin_only', 'read_only_sql' => 'admin_only', 'read_only_query' => 'admin_only');
        if ($name === 'read_only_query' && !$this->is_admin_role($role)) throw new RuntimeException('Basic query access is admin-only');
        if (!isset($allowed[$name]) || !$this->tool_allowed($allowed[$name], $role)) throw new RuntimeException('Tool not permitted');
        switch ($name) {
            case 'get_database_schema': return $this->tool_database_schema($role, $args);
            case 'read_only_sql': return $this->tool_read_only_sql($args, $role);
            case 'count_students': return $this->tool_count_students($args, $role, $user_id);
            case 'search_students': return $this->tool_search_students($args, $role, $user_id);
            case 'get_student_profile': return $this->tool_student_profile($args, $role, $user_id);
            case 'get_attendance_summary': return $this->tool_attendance($args, $role, $user_id);
            case 'get_due_fees': return $this->tool_due_fees($args, $role, $user_id);
            case 'get_student_results': return $this->tool_student_results($args, $role, $user_id);
            case 'get_classes': return $this->cap($this->class_model->get(), 100);
            case 'get_sections': return $this->cap($this->section_model->getClassBySection((int) $args['class_id']), 100);
            case 'get_subjects': return $this->cap($this->subject_model->get(), 200);
            case 'get_class_timetable': return $this->cap($this->timetable_model->get(array('class_id' => (int) $args['class_id'], 'section_id' => (int) $args['section_id'], 'session_id' => $this->current_session)), 100);
            case 'search_staff': return $this->tool_search_staff($args);
            case 'get_staff_profile': return $this->tool_staff_profile($args);
            case 'get_staff_leave_requests': return $this->cap($this->leaverequest_model->staff_leave_request(!empty($args['staff_id']) ? (int) $args['staff_id'] : null), $this->safe_limit(isset($args['limit']) ? $args['limit'] : 100, 100));
            case 'get_homework': return $this->tool_homework($args, $role, $user_id);
            case 'get_library_books': return $this->cap($this->book_model->listbook(), $this->safe_limit(isset($args['limit']) ? $args['limit'] : 100, 100));
            case 'get_library_due_books': return $this->cap($this->bookissue_model->dueforreturn($this->valid_date($args['start_date']), $this->valid_date($args['end_date'])), 100);
            case 'get_transport_routes': return $this->cap($this->route_model->listroute(), 100);
            case 'get_transport_vehicles': return $this->cap($this->route_model->listvehicles(), 100);
            case 'get_hostels': return array('hostels' => $this->cap($this->hostel_model->get(), 100), 'rooms' => $this->cap($this->hostelroom_model->lists(), 200));
            case 'get_inventory_stock': return $this->cap($this->itemstock_model->get_currentstock(), 200);
            case 'get_income_report': return $this->tool_finance_report('income', $args);
            case 'get_expense_report': return $this->tool_finance_report('expense', $args);
            case 'get_enquiries': return $this->tool_date_list($this->enquiry_model->searchEnquiry('', $this->valid_date($args['start_date']), $this->valid_date($args['end_date'])), 100);
            case 'get_visitors': return $this->cap($this->visitors_model->visitors_list(), 100);
            case 'get_complaints': return $this->cap($this->complaint_model->complaint_list(), 100);
            case 'get_phone_calls': return $this->cap($this->general_call_model->call_list(), 100);
            case 'get_dispatch_receive': return array('dispatch' => $this->cap($this->dispatch_model->dispatch_list(), 100), 'receive' => $this->cap($this->dispatch_model->receive_list(), 100));
            case 'get_calendar_events': return $this->cap($this->calendar_model->getEvents(), 100);
            case 'get_learning_content': return $this->cap(!empty($args['category']) ? $this->content_model->getListByCategory(substr($args['category'], 0, 50)) : $this->content_model->get(), 100);
            case 'get_notices': return $this->cap($this->notification_model->get(), 100);
            case 'get_online_exams': return $this->cap($this->onlineexam_model->get(), 100);
            case 'get_syllabus_status': return $this->tool_syllabus_status($args, $role, $user_id);
            case 'read_only_query': return $this->tool_read_only_query($args);
        }
        throw new RuntimeException('Unknown tool');
    }

    public function execute_tool_for_ai($name, $args, $role, $user_id = 0)
    {
        try {
            return $this->execute_tool($name, $args, $role, $user_id);
        } catch (Exception $e) {
            log_message('error', 'AI tool rejected: ' . $name . '; reason=' . $e->getMessage());
            if ($name === 'read_only_sql' && $this->is_fee_month_sql(isset($args['sql']) ? $args['sql'] : '')) {
                $month = $this->fee_month_from_sql($args['sql']);
                if ($month) return array('fallback' => 'pending_fee_month', 'month' => $month, 'total_pending' => $this->pending_fee_month($month, $role, $user_id)['total_pending']);
            }
            return array('tool_error' => substr($e->getMessage(), 0, 600));
        }
    }

    public function execute_school_report($args, $role, $user_id = 0)
    {
        if (!$this->is_admin_role($role)) return array('report_error' => 'This report is admin-only.');
        $type = strtolower(trim((string) (isset($args['report_type']) ? $args['report_type'] : '')));
        $type = str_replace(array('get_', 'search_', 'report_', ' '), array('', '', '', '_'), $type);
        $aliases = array(
            'students_count' => 'student_count', 'count_students' => 'student_count', 'students' => 'student_search',
            'pending_fees' => 'pending_fee_total', 'total_pending_fees' => 'pending_fee_total', 'fee_pending' => 'pending_fee_total', 'all_pending_fees' => 'pending_fee_total', 'fee_summary' => 'pending_fee_summary',
            'class_wise_students' => 'students_by_class', 'students_per_class' => 'students_by_class', 'student_count_by_class' => 'students_by_class', 'fees_due' => 'due_fees',
            'pending_fee_report' => 'pending_fee_students', 'student_pending_fees' => 'pending_fee_students',
            'attendance' => 'attendance_summary', 'results' => 'student_results', 'staff' => 'staff_search',
            'fees_collected' => 'fee_collection', 'fee_collected' => 'fee_collection', 'collection' => 'fee_collection', 'fee_collection_summary' => 'fee_collection', 'income_report' => 'income', 'expense_report' => 'expenses', 'library' => 'library_books'
        );
        if (isset($aliases[$type])) $type = $aliases[$type];
        $args += array('query' => '', 'student_id' => 0, 'admission_no' => '', 'staff_id' => 0, 'class_id' => 0, 'section_id' => 0, 'exam_id' => 0, 'category' => '');
        $args['limit'] = $this->safe_limit(isset($args['limit']) ? $args['limit'] : 25, 50);
        if (empty($args['start_date'])) $args['start_date'] = date('Y-m-d', strtotime('-30 days'));
        if (empty($args['end_date'])) $args['end_date'] = date('Y-m-d');
        if ($type === 'current_session') return array('session' => $this->setting_model->getCurrentSessionName(), 'session_id' => $this->current_session);
        if ($type === 'students_by_class') return $this->students_by_class($role, $user_id);
        if ($type === 'pending_fee_total') return $this->pending_fee_total($role, $user_id);
        if ($type === 'fee_collection') return $this->fee_collection_summary($args['start_date'], $args['end_date'], $role, $user_id);
        if ($type === 'pending_fee_summary') return $this->pending_fee_month(!empty($args['month']) ? (int) $args['month'] : (int) date('n'), $role, $user_id);
        if ($type === 'pending_fee_students') return $this->pending_fee_students(!empty($args['months']) ? (int) $args['months'] : 2, $role, $user_id, $args['limit']);
        $map = array(
            'student_count' => 'count_students', 'student_search' => 'search_students', 'student_profile' => 'get_student_profile',
            'attendance_summary' => 'get_attendance_summary', 'due_fees' => 'get_due_fees', 'student_results' => 'get_student_results',
            'classes' => 'get_classes', 'sections' => 'get_sections', 'subjects' => 'get_subjects', 'timetable' => 'get_class_timetable',
            'staff_search' => 'search_staff', 'staff_profile' => 'get_staff_profile', 'staff_leave' => 'get_staff_leave_requests',
            'homework' => 'get_homework', 'library_books' => 'get_library_books', 'library_due' => 'get_library_due_books',
            'transport_routes' => 'get_transport_routes', 'transport_vehicles' => 'get_transport_vehicles', 'hostels' => 'get_hostels',
            'inventory_stock' => 'get_inventory_stock', 'income' => 'get_income_report', 'expenses' => 'get_expense_report',
            'enquiries' => 'get_enquiries', 'visitors' => 'get_visitors', 'complaints' => 'get_complaints',
            'phone_calls' => 'get_phone_calls', 'dispatch_receive' => 'get_dispatch_receive', 'calendar_events' => 'get_calendar_events',
            'learning_content' => 'get_learning_content', 'notices' => 'get_notices', 'online_exams' => 'get_online_exams',
            'syllabus_status' => 'get_syllabus_status'
        );
        if (!isset($map[$type])) return array('report_error' => 'The requested report type is not supported yet.', 'report_type' => $type);
        try { return $this->execute_tool($map[$type], $args, $role, $user_id); }
        catch (Exception $e) { log_message('error', 'School report failed: type=' . $type . '; reason=' . $e->getMessage()); return array('report_error' => 'The report could not be generated with the supplied filters.', 'report_type' => $type); }
    }

    private function is_fee_month_sql($sql) { return (bool) (preg_match('/\b(fee|fees|payment|collection|balance|due)\b/i', $sql) && $this->fee_month_from_sql($sql)); }
    private function fee_month_from_sql($sql) { $months = array('january'=>1,'february'=>2,'march'=>3,'april'=>4,'may'=>5,'june'=>6,'july'=>7,'august'=>8,'september'=>9,'october'=>10,'november'=>11,'december'=>12); $q = strtolower((string) $sql); foreach ($months as $name => $number) if (strpos($q, $name) !== false) return $number; if (preg_match('/\bclnd_month\s*=\s*[\'\"]?(1[0-2]|[1-9])[\'\"]?/i', $q, $m)) return (int) $m[1]; return 0; }

    private function tool_allowed($privilege, $role)
    {
        if (in_array(strtolower($role), array('admin', 'super admin', 'superadmin'), true)) return true;
        return $this->rbac && $this->rbac->hasPrivilege($privilege, 'can_view');
    }

    private function tool_count_students($a, $role, $user_id)
    {
        $this->db->select('COUNT(DISTINCT s.id) AS total', false)->from('students s');
        $this->db->join('student_session ss', 'ss.student_id=s.id');
        $this->db->where(array('ss.session_id' => $this->current_session, 's.is_active' => 'yes'));
        $this->apply_teacher_scope($role, $user_id, 'ss.class_id', 'ss.section_id');
        if (!empty($a['class_id'])) $this->db->where('ss.class_id', (int) $a['class_id']);
        if (!empty($a['section_id'])) $this->db->where('ss.section_id', (int) $a['section_id']);
        $row = $this->db->get()->row_array();
        return array('total_students' => (int) (isset($row['total']) ? $row['total'] : 0), 'session_id' => $this->current_session);
    }

    private function tool_search_students($a, $role, $user_id)
    {
        $q = trim((string) (isset($a['query']) ? $a['query'] : ''));
        if ($q === '') return array();
        $this->db->select('s.id,s.admission_no,s.roll_no,s.firstname,s.middlename,s.lastname,s.guardian_name,c.class,sec.section')->from('students s');
        $this->db->join('student_session ss', 'ss.student_id=s.id')->join('classes c', 'c.id=ss.class_id')->join('sections sec', 'sec.id=ss.section_id');
        $this->db->where(array('ss.session_id' => $this->current_session, 's.is_active' => 'yes'));
        $this->apply_teacher_scope($role, $user_id, 'ss.class_id', 'ss.section_id');
        if (!empty($a['class_id'])) $this->db->where('ss.class_id', (int) $a['class_id']);
        if (!empty($a['section_id'])) $this->db->where('ss.section_id', (int) $a['section_id']);
        $this->db->group_start()->like('s.firstname', $q)->or_like('s.middlename', $q)->or_like('s.lastname', $q)->or_like('s.admission_no', $q)->or_like('s.guardian_name', $q)->group_end();
        return $this->db->limit($this->safe_limit(isset($a['limit']) ? $a['limit'] : 20, 50))->get()->result_array();
    }

    private function tool_student_profile($a, $role, $user_id)
    {
        $this->db->select('s.id,s.admission_no,s.roll_no,s.firstname,s.middlename,s.lastname,s.dob,s.gender,s.guardian_name,s.guardian_phone,ss.class_id,ss.section_id,c.class,sec.section')->from('students s');
        $this->db->join('student_session ss', 'ss.student_id=s.id')->join('classes c', 'c.id=ss.class_id')->join('sections sec', 'sec.id=ss.section_id');
        $this->db->where(array('ss.session_id' => $this->current_session, 's.is_active' => 'yes'));
        $this->apply_teacher_scope($role, $user_id, 'ss.class_id', 'ss.section_id');
        if (!empty($a['student_id'])) $this->db->where('s.id', (int) $a['student_id']);
        elseif (!empty($a['admission_no'])) $this->db->where('s.admission_no', substr(trim($a['admission_no']), 0, 40));
        else return array();
        return $this->db->get()->row_array() ?: array();
    }

    private function tool_attendance($a, $role, $user_id)
    {
        $start = $this->valid_date(isset($a['start_date']) ? $a['start_date'] : ''); $end = $this->valid_date(isset($a['end_date']) ? $a['end_date'] : '');
        if (!$start || !$end || $start > $end) return array('error' => 'Invalid date range');
        $this->db->select('al.admission_no,al.timestamp,al.status,s.firstname,s.lastname,c.class,sec.section')->from('attendance_logs al');
        $this->db->join('students s', 's.admission_no=al.admission_no', 'left')->join('student_session ss', 'ss.student_id=s.id', 'left')->join('classes c', 'c.id=ss.class_id', 'left')->join('sections sec', 'sec.id=ss.section_id', 'left');
        $this->db->where('DATE(al.timestamp) >=', $start)->where('DATE(al.timestamp) <=', $end)->group_start()->where('ss.session_id', $this->current_session)->or_where('ss.session_id IS NULL', null, false)->group_end();
        $this->apply_teacher_scope($role, $user_id, 'ss.class_id', 'ss.section_id');
        if (!empty($a['class_id'])) $this->db->where('ss.class_id', (int) $a['class_id']); if (!empty($a['section_id'])) $this->db->where('ss.section_id', (int) $a['section_id']);
        return array('start_date' => $start, 'end_date' => $end, 'rows' => $this->db->limit($this->safe_limit(isset($a['limit']) ? $a['limit'] : 100, 100))->get()->result_array());
    }

    public function pending_fee_month($month, $role, $user_id)
    {
        $month = max(1, min(12, (int) $month));
        $this->db->select('sfm.id AS fee_master_id,fg.id AS fee_item_id,fg.amount,fg.clnd_month,sfd.amount_detail')->from('student_fees_master sfm');
        $this->db->join('student_session ss', 'ss.id=sfm.student_session_id')->join('students s', 's.id=ss.student_id')->join('fee_groups_feetype fg', 'fg.fee_session_group_id=sfm.fee_session_group_id', 'inner')->join('student_fees_deposite sfd', 'sfd.student_fees_master_id=sfm.id AND sfd.fee_groups_feetype_id=fg.id', 'left');
        $this->db->where(array('ss.session_id' => $this->current_session, 's.is_active' => 'yes', 'fg.clnd_month' => $month)); $this->apply_teacher_scope($role, $user_id, 'ss.class_id', 'ss.section_id');
        $groups = array(); foreach ($this->db->get()->result_array() as $row) { $key = $row['fee_master_id'] . ':' . $row['fee_item_id']; if (!isset($groups[$key])) $groups[$key] = array('due' => (float) $row['amount'], 'paid' => 0.0, 'discount' => 0.0); if (!empty($row['amount_detail'])) { $details = json_decode($row['amount_detail'], true); if (is_array($details)) foreach ($details as $detail) { if (is_array($detail)) { $groups[$key]['paid'] += (float) (isset($detail['amount']) ? $detail['amount'] : 0); $groups[$key]['discount'] += (float) (isset($detail['amount_discount']) ? $detail['amount_discount'] : 0); } } } }
        $total = 0.0; foreach ($groups as $group) $total += max(0, $group['due'] - $group['paid'] - $group['discount']);
        return array('month' => $month, 'total_pending' => round($total, 2), 'fee_items' => count($groups));
    }

    /** Active students per class (and section) for the current session. */
    public function students_by_class($role, $user_id)
    {
        $this->db->select('c.id AS class_id, c.class, sec.section, COUNT(DISTINCT s.id) AS students', false)->from('students s');
        $this->db->join('student_session ss', 'ss.student_id=s.id')->join('classes c', 'c.id=ss.class_id')->join('sections sec', 'sec.id=ss.section_id');
        $this->db->where(array('ss.session_id' => $this->current_session, 's.is_active' => 'yes'));
        $this->apply_teacher_scope($role, $user_id, 'ss.class_id', 'ss.section_id');
        $rows = $this->db->group_by(array('c.id', 'c.class', 'sec.id', 'sec.section'))->order_by('c.id', 'ASC')->order_by('sec.id', 'ASC')->get()->result_array();
        $classes = array(); $total = 0;
        foreach ($rows as $row) {
            $id = (int) $row['class_id'];
            if (!isset($classes[$id])) $classes[$id] = array('class' => $row['class'], 'students' => 0, 'sections' => array());
            $classes[$id]['students'] += (int) $row['students'];
            $classes[$id]['sections'][] = $row['section'] . ': ' . (int) $row['students'];
            $total += (int) $row['students'];
        }
        $out = array();
        foreach ($classes as $class) $out[] = array('class' => $class['class'], 'students' => $class['students'], 'sections' => implode(', ', $class['sections']));
        return array('total_students' => $total, 'rows' => $out, 'session_id' => $this->current_session);
    }

    /** Everything still owed (due - paid - discount) across all fee items generated for the current session. */
    public function pending_fee_total($role, $user_id)
    {
        $this->db->select('sfm.id AS fee_master_id,fg.id AS fee_item_id,fg.amount,fg.clnd_month,sfd.amount_detail')->from('student_fees_master sfm');
        $this->db->join('student_session ss', 'ss.id=sfm.student_session_id')->join('students s', 's.id=ss.student_id')->join('fee_groups_feetype fg', 'fg.fee_session_group_id=sfm.fee_session_group_id', 'inner')->join('student_fees_deposite sfd', 'sfd.student_fees_master_id=sfm.id AND sfd.fee_groups_feetype_id=fg.id', 'left');
        $this->db->where(array('ss.session_id' => $this->current_session, 's.is_active' => 'yes'));
        $this->apply_teacher_scope($role, $user_id, 'ss.class_id', 'ss.section_id');
        $items = array();
        foreach ($this->db->get()->result_array() as $row) {
            $key = $row['fee_master_id'] . ':' . $row['fee_item_id'];
            if (!isset($items[$key])) $items[$key] = array('month' => (int) $row['clnd_month'], 'due' => (float) $row['amount'], 'paid' => 0.0);
            if (!empty($row['amount_detail'])) {
                $details = json_decode($row['amount_detail'], true);
                if (is_array($details)) foreach ($details as $detail) if (is_array($detail)) $items[$key]['paid'] += (float) (isset($detail['amount']) ? $detail['amount'] : 0) + (float) (isset($detail['amount_discount']) ? $detail['amount_discount'] : 0);
            }
        }
        $total = 0.0; $count = 0; $months = array();
        foreach ($items as $item) {
            $pending = $item['due'] - $item['paid'];
            if ($pending <= 0) continue;
            $total += $pending; $count++;
            $months[$item['month']] = (isset($months[$item['month']]) ? $months[$item['month']] : 0) + $pending;
        }
        ksort($months);
        $by_month = array();
        foreach ($months as $number => $amount) $by_month[] = array('month' => $number >= 1 && $number <= 12 ? date('F', mktime(0, 0, 0, $number, 1)) : 'Other / no month', 'pending' => round($amount, 2));
        return array('total_pending' => round($total, 2), 'fee_items_with_balance' => $count, 'by_month' => $by_month, 'session_id' => $this->current_session);
    }

    public function pending_fee_students($months, $role, $user_id, $limit = 25)
    {
        $months = max(1, min(12, (int) $months)); $month_numbers = array(); $month_names = array();
        for ($i = 1; $i <= $months; $i++) { $time = strtotime('-' . $i . ' month'); $month_numbers[] = (int) date('n', $time); $month_names[] = date('F', $time); }
        $this->db->select('sfm.id AS fee_master_id,fg.id AS fee_item_id,ss.student_id,s.admission_no,s.firstname,s.middlename,s.lastname,s.guardian_name,s.guardian_phone,c.class,sec.section,fg.amount,fg.clnd_month,sfd.amount_detail')->from('student_fees_master sfm');
        $this->db->join('student_session ss', 'ss.id=sfm.student_session_id')->join('students s', 's.id=ss.student_id')->join('classes c', 'c.id=ss.class_id')->join('sections sec', 'sec.id=ss.section_id')->join('fee_groups_feetype fg', 'fg.fee_session_group_id=sfm.fee_session_group_id', 'inner')->join('student_fees_deposite sfd', 'sfd.student_fees_master_id=sfm.id AND sfd.fee_groups_feetype_id=fg.id', 'left');
        $this->db->where(array('ss.session_id' => $this->current_session, 's.is_active' => 'yes'))->where_in('fg.clnd_month', $month_numbers); $this->apply_teacher_scope($role, $user_id, 'ss.class_id', 'ss.section_id');
        $items = array(); foreach ($this->db->get()->result_array() as $row) { $key = $row['fee_master_id'] . ':' . $row['fee_item_id']; if (!isset($items[$key])) $items[$key] = array('student' => $row, 'due' => (float) $row['amount'], 'paid' => 0.0, 'discount' => 0.0); if (!empty($row['amount_detail'])) { $details = json_decode($row['amount_detail'], true); if (is_array($details)) foreach ($details as $detail) if (is_array($detail)) { $items[$key]['paid'] += (float) (isset($detail['amount']) ? $detail['amount'] : 0); $items[$key]['discount'] += (float) (isset($detail['amount_discount']) ? $detail['amount_discount'] : 0); } } }
        $students = array(); foreach ($items as $item) { $pending = max(0, $item['due'] - $item['paid'] - $item['discount']); if ($pending <= 0) continue; $row = $item['student']; $id = (int) $row['student_id']; if (!isset($students[$id])) $students[$id] = array('student_id' => $id, 'admission_no' => $row['admission_no'], 'student_name' => trim($row['firstname'] . ' ' . $row['middlename'] . ' ' . $row['lastname']), 'guardian_name' => $row['guardian_name'], 'guardian_phone' => $row['guardian_phone'], 'class' => $row['class'], 'section' => $row['section'], 'pending_pkr' => 0.0); $students[$id]['pending_pkr'] += $pending; }
        $rows = array_values($students); usort($rows, function ($a, $b) { return $a['pending_pkr'] < $b['pending_pkr'] ? 1 : -1; }); foreach ($rows as &$row) $row['pending_pkr'] = round($row['pending_pkr'], 2); unset($row);
        return array('months' => $month_names, 'total_students' => count($rows), 'rows' => array_slice($rows, 0, $this->safe_limit($limit, 50)));
    }

    private function tool_due_fees($a, $role, $user_id)
    {
        $this->db->select('ss.student_id,s.admission_no,s.firstname,s.middlename,s.lastname,c.class,sec.section,fg.amount,fg.due_date,sfd.amount_detail')->from('student_fees_master sfm');
        $this->db->join('student_session ss', 'ss.id=sfm.student_session_id')->join('students s', 's.id=ss.student_id')->join('classes c', 'c.id=ss.class_id')->join('sections sec', 'sec.id=ss.section_id')->join('fee_groups_feetype fg', 'fg.fee_session_group_id=sfm.fee_session_group_id', 'inner')->join('student_fees_deposite sfd', 'sfd.student_fees_master_id=sfm.id AND sfd.fee_groups_feetype_id=fg.id', 'left');
        $this->db->where(array('ss.session_id' => $this->current_session, 's.is_active' => 'yes')); $this->apply_teacher_scope($role, $user_id, 'ss.class_id', 'ss.section_id'); if (!empty($a['class_id'])) $this->db->where('ss.class_id', (int) $a['class_id']); if (!empty($a['section_id'])) $this->db->where('ss.section_id', (int) $a['section_id']);
        return $this->db->limit($this->safe_limit(isset($a['limit']) ? $a['limit'] : 100, 100))->get()->result_array();
    }

    private function tool_student_results($a, $role, $user_id)
    {
        $this->db->select('er.student_id,es.exam_id,es.date_of_exam,es.full_marks,es.passing_marks,er.get_marks,er.attendence,sub.name as subject,sub.code')->from('exam_results er');
        $this->db->join('exam_schedules es', 'es.id=er.exam_schedule_id')->join('teacher_subjects ts', 'ts.id=es.teacher_subject_id')->join('subjects sub', 'sub.id=ts.subject_id');
        $this->db->join('student_session ss', 'ss.student_id=er.student_id'); $this->db->where(array('er.student_id' => (int) $a['student_id'], 'es.session_id' => $this->current_session, 'ss.session_id' => $this->current_session)); $this->apply_teacher_scope($role, $user_id, 'ss.class_id', 'ss.section_id'); if (!empty($a['exam_id'])) $this->db->where('es.exam_id', (int) $a['exam_id']);
        return $this->db->get()->result_array();
    }

    private function tool_search_staff($a)
    {
        $q = trim(substr((string) (isset($a['query']) ? $a['query'] : ''), 0, 100));
        $this->db->select('staff.id,staff.employee_id,staff.name,staff.surname,staff.contact_no,staff.email,staff_designation.designation,department.department_name as department,roles.name as role')->from('staff');
        $this->db->join('staff_designation', 'staff_designation.id=staff.designation', 'left')->join('department', 'department.id=staff.department', 'left')->join('staff_roles', 'staff_roles.staff_id=staff.id', 'left')->join('roles', 'roles.id=staff_roles.role_id', 'left')->where('staff.is_active', 1);
        if ($q !== '') $this->db->group_start()->like('staff.name', $q)->or_like('staff.surname', $q)->or_like('staff.employee_id', $q)->or_like('staff.email', $q)->group_end();
        return $this->db->order_by('staff.name', 'ASC')->limit($this->safe_limit(isset($a['limit']) ? $a['limit'] : 20, 50))->get()->result_array();
    }

    private function tool_staff_profile($a)
    {
        $id = (int) (isset($a['staff_id']) ? $a['staff_id'] : 0); if ($id < 1) return array();
        $this->db->select('staff.id,staff.employee_id,staff.name,staff.surname,staff.contact_no,staff.email,staff.gender,staff.date_of_joining,staff_designation.designation,department.department_name as department,roles.name as role')->from('staff');
        $this->db->join('staff_designation', 'staff_designation.id=staff.designation', 'left')->join('department', 'department.id=staff.department', 'left')->join('staff_roles', 'staff_roles.staff_id=staff.id', 'left')->join('roles', 'roles.id=staff_roles.role_id', 'left')->where(array('staff.id' => $id, 'staff.is_active' => 1));
        return $this->db->get()->row_array() ?: array();
    }

    private function tool_homework($a, $role, $user_id)
    {
        if (strpos(strtolower($role), 'teacher') !== false && empty($a['class_id'])) return array();
        $class = !empty($a['class_id']) ? (int) $a['class_id'] : null; $section = !empty($a['section_id']) ? (int) $a['section_id'] : null;
        if ($class) { $this->apply_teacher_scope($role, $user_id, 'homework.class_id', 'homework.section_id'); return $this->cap($this->homework_model->search_homework($class, $section, null, null), $this->safe_limit(isset($a['limit']) ? $a['limit'] : 100, 100)); }
        return $this->cap($this->homework_model->get(), $this->safe_limit(isset($a['limit']) ? $a['limit'] : 100, 100));
    }

    private function tool_finance_report($type, $a)
    {
        $start = $this->valid_date(isset($a['start_date']) ? $a['start_date'] : ''); $end = $this->valid_date(isset($a['end_date']) ? $a['end_date'] : ''); if (!$start || !$end || $start > $end) return array('error' => 'Invalid date range');
        $rows = $type === 'income' ? $this->income_model->search2('', $start, $end) : $this->expense_model->search2('', $start, $end);
        return $this->cap($rows, $this->safe_limit(isset($a['limit']) ? $a['limit'] : 100, 100));
    }

    private function tool_date_list($rows, $limit) { return $this->cap($rows, $limit); }

    private function tool_syllabus_status($a, $role, $user_id)
    {
        $class = (int) $a['class_id']; $section = (int) $a['section_id'];
        if (strpos(strtolower($role), 'teacher') !== false && !$this->teacher_can_access($user_id, $class, $section)) return array();
        return $this->cap($this->syllabus_model->getmysubjects($class, $section), 100);
    }

    private function teacher_can_access($user_id, $class_id, $section_id)
    {
        return $this->db->where(array('staff_id' => (int) $user_id, 'class_id' => (int) $class_id, 'section_id' => (int) $section_id, 'session_id' => $this->current_session))->count_all_results('class_teacher') > 0;
    }

    /**
     * Tables hidden from the AI. Everything else in the database can be read. Override with
     * $config['ai_widget_blocked_tables'] (array of names, "*" allowed as a wildcard); an empty array blocks nothing.
     */
    private function blocked_table_names()
    {
        $configured = $this->config->item('ai_widget_blocked_tables');
        if (is_array($configured)) return array_values(array_filter(array_map('strval', $configured)));
        return array('ci_sessions', 'email_config', 'sms_config', 'payment_settings', 'ai_widget_*');
    }

    /** Regex fragment (no delimiters) matching any blocked table name, or null when nothing is blocked. */
    private function blocked_table_fragment()
    {
        $parts = array();
        foreach ($this->blocked_table_names() as $name) $parts[] = str_replace('\*', '[a-z0-9_]*', preg_quote(strtolower($name), '/'));
        return empty($parts) ? null : '(' . implode('|', $parts) . ')';
    }

    private function table_blocked($table)
    {
        $fragment = $this->blocked_table_fragment();
        return $fragment !== null && (bool) preg_match('/^' . $fragment . '$/i', (string) $table);
    }

    /** Columns holding credentials. They are never listed to the AI and are masked in any query result. */
    private function sensitive_column($field)
    {
        return (bool) preg_match('/password|passcode|(^|_)pass(_|$)|token|secret|api[_-]?key|auth[_-]?key|remember[_-]?code|salt|otp[_-]?code|card[_-]?(no|number)|cvv/i', (string) $field);
    }

    private function tool_database_schema($role, $args = array())
    {
        if (!$this->is_admin_role($role)) throw new RuntimeException('Database schema access is admin-only');
        $available = array();
        foreach ($this->db->list_tables() as $table) {
            if (!$this->table_blocked($table)) $available[] = $table;
        }

        $wanted = isset($args['tables']) ? $args['tables'] : array();
        if (is_string($wanted)) $wanted = preg_split('/\s*,\s*/', $wanted);
        $wanted = is_array($wanted) ? array_slice(array_filter(array_map('strtolower', array_map('strval', $wanted))), 0, 12) : array();
        if (!empty($wanted)) {
            $columns = array();
            foreach ($available as $table) {
                if (!in_array(strtolower($table), $wanted, true)) continue;
                $fields = array();
                foreach ($this->db->list_fields($table) as $field) if (!$this->sensitive_column($field)) $fields[] = $field;
                $columns[$table] = array_slice($fields, 0, 120);
            }
            return array('columns' => $columns, 'unknown_tables' => array_values(array_diff($wanted, array_map('strtolower', array_keys($columns)))));
        }

        $search = strtolower(trim((string) (isset($args['search']) ? $args['search'] : '')));
        if ($search !== '') {
            $matches = array_values(array_filter($available, function ($table) use ($search) { return strpos(strtolower($table), $search) !== false; }));
            return array('tables' => array_slice($matches, 0, 60));
        }
        return array('tables' => $available);
    }

    private function tool_read_only_sql($a, $role)
    {
        if (!$this->is_admin_role($role)) throw new RuntimeException('Dynamic SQL access is admin-only');
        $sql = trim((string) (isset($a['sql']) ? $a['sql'] : ''));
        $sql = rtrim($sql, "; \t\r\n");
        if ($sql === '' || strlen($sql) > 6000) throw new RuntimeException('SQL is empty or too long');
        if (!preg_match('/^SELECT\s/i', $sql) || preg_match('/;|--|\/\*|\*\/|#/', $sql)) throw new RuntimeException('Only one SELECT statement is allowed, without comments or semicolons');
        // Writes, DDL and server-level statements. REPLACE( is allowed because it is also a harmless string function.
        if (preg_match('/\b(INSERT|UPDATE|DELETE|DROP|ALTER|TRUNCATE|CREATE|GRANT|REVOKE|CALL|EXEC|EXECUTE|LOAD|HANDLER|SET|USE|SHOW|DESCRIBE|EXPLAIN|OUTFILE|DUMPFILE|FOR\s+UPDATE|LOCK\s+IN\s+SHARE)\b|\bREPLACE\b(?!\s*\()/i', $sql)) throw new RuntimeException('SQL operation is not allowed');
        if (preg_match('/\b(password|passcode|token|secret|api[_-]?key|auth[_-]?key|remember[_-]?code|sleep|benchmark|load_file|extractvalue|updatexml|information_schema|performance_schema|mysql)\b/i', $sql)) throw new RuntimeException('Sensitive SQL is not allowed');
        // Blocked tables are rejected wherever they appear (covers comma joins and subqueries too).
        $fragment = $this->blocked_table_fragment();
        if ($fragment !== null && preg_match('/\b' . $fragment . '\b/i', $sql, $blocked)) throw new RuntimeException('Table ' . $blocked[1] . ' is not available to the assistant');
        $tables = array(); preg_match_all('/\b(?:FROM|JOIN)\s+[`]?([A-Za-z][A-Za-z0-9_]*)[`]?/i', $sql, $matches); foreach ($matches[1] as $table) $tables[strtolower($table)] = true;
        if (empty($tables)) throw new RuntimeException('A table is required');
        foreach (array_keys($tables) as $table) { if (!$this->db->table_exists($table)) throw new RuntimeException('Table ' . $table . ' does not exist. Use get_database_schema to see the real table names'); }
        if (preg_match_all('/\blimit\s+(\d+)/i', $sql, $limits)) { foreach ($limits[1] as $limit) if ((int) $limit > 200) throw new RuntimeException('Maximum 200 rows allowed'); }
        else $sql .= ' LIMIT 100';

        // Run inside a read-only transaction and report SQL errors back to the model so it can correct the query.
        $previous_debug = $this->db->db_debug;
        $this->db->db_debug = false;
        try {
            $this->db->query('START TRANSACTION READ ONLY');
            $query = $this->db->query($sql);
            if (!$query) { $error = $this->db->error(); throw new RuntimeException('SQL error: ' . substr(isset($error['message']) ? $error['message'] : 'query failed', 0, 300)); }
            $rows = $query->result_array();
            foreach ($rows as $index => $row) foreach ($row as $column => $value) if ($this->sensitive_column($column)) $rows[$index][$column] = '[hidden]';
            $result = array('rows' => $rows, 'row_count' => $query->num_rows());
        } catch (RuntimeException $e) {
            $this->finish_read_only();
            $this->db->db_debug = $previous_debug;
            throw $e;
        } catch (Throwable $e) {
            $this->finish_read_only();
            $this->db->db_debug = $previous_debug;
            throw new RuntimeException('SQL error: ' . substr($e->getMessage(), 0, 300));
        }
        $this->finish_read_only();
        $this->db->db_debug = $previous_debug;
        return $result;
    }

    private function finish_read_only()
    {
        try { $this->db->query('ROLLBACK'); } catch (Throwable $e) { log_message('error', 'AI read-only rollback: ' . $e->getMessage()); }
    }

    /** Fee money actually received between two dates (payments are JSON rows inside student_fees_deposite). */
    public function fee_collection_summary($start, $end, $role, $user_id)
    {
        $start = $this->valid_date($start); $end = $this->valid_date($end);
        if (!$start || !$end || $start > $end) return array('error' => 'Invalid date range');
        $this->db->select('sfd.amount_detail')->from('student_fees_deposite sfd');
        $this->db->join('student_fees_master sfm', 'sfm.id=sfd.student_fees_master_id')->join('student_session ss', 'ss.id=sfm.student_session_id');
        $this->db->where('ss.session_id', $this->current_session);
        $this->apply_teacher_scope($role, $user_id, 'ss.class_id', 'ss.section_id');
        $fees = 0.0; $fine = 0.0; $payments = 0; $by_date = array();
        foreach ($this->db->get()->result_array() as $row) {
            $details = json_decode((string) $row['amount_detail'], true);
            if (!is_array($details)) continue;
            foreach ($details as $detail) {
                if (!is_array($detail)) continue;
                $date_value = '';
                foreach (array('date', 'payment_date', 'paid_date', 'deposit_date', 'collection_date', 'created_at', 'date_time', 'datetime') as $key) {
                    if (isset($detail[$key]) && $detail[$key] !== '') { $date_value = $detail[$key]; break; }
                }
                $ts = $date_value !== '' ? strtotime((string) $date_value) : false;
                if ($ts === false) continue;
                $day = date('Y-m-d', $ts);
                if ($day < $start || $day > $end) continue;
                $amount = (float) (isset($detail['amount']) ? $detail['amount'] : 0);
                $line_fine = (float) (isset($detail['amount_fine']) ? $detail['amount_fine'] : 0);
                $fees += $amount; $fine += $line_fine; $payments++;
                $by_date[$day] = (isset($by_date[$day]) ? $by_date[$day] : 0) + $amount + $line_fine;
            }
        }
        krsort($by_date);
        foreach ($by_date as $day => $total) $by_date[$day] = round($total, 2);
        return array('start_date' => $start, 'end_date' => $end, 'fees_collected' => round($fees, 2), 'fines_collected' => round($fine, 2), 'total_collected' => round($fees + $fine, 2), 'payments' => $payments, 'by_date_latest_first' => array_slice($by_date, 0, 31, true));
    }

    private function read_only_tables()
    {
        return array(
            'students' => array('id','admission_no','roll_no','firstname','middlename','lastname','gender','dob','admission_date','is_active'),
            'classes' => array('id','class'),
            'sections' => array('id','section'),
            'subjects' => array('id','name','code','type'),
            'staff' => array('id','employee_id','name','surname','email','contact_no','is_active'),
            'books' => array('id','book_title','book_no','isbn_no','author','qty'),
            'attendance_logs' => array('id','admission_no','timestamp','status'),
            'exams' => array('id','name','session_id'),
            'exam_schedules' => array('id','exam_id','date_of_exam','full_marks','passing_marks','session_id'),
            'onlineexam' => array('id','exam','exam_from','exam_to','is_active','session_id'),
            'income' => array('id','date','name','invoice_no','amount','inc_head_id'),
            'expenses' => array('id','date','name','invoice_no','amount','exp_head_id')
        );
    }

    private function tool_read_only_query($a)
    {
        $tables = $this->read_only_tables(); $table = isset($a['table']) ? (string) $a['table'] : '';
        if (!isset($tables[$table])) throw new RuntimeException('Table is not approved');
        $requested = isset($a['columns']) && is_array($a['columns']) ? $a['columns'] : array();
        $columns = array_values(array_intersect($requested, $tables[$table]));
        if (empty($columns) || count($columns) !== count($requested)) throw new RuntimeException('Column is not approved');
        $this->db->select($columns)->from($table);
        $filters = isset($a['filters']) && is_array($a['filters']) ? $a['filters'] : array();
        foreach ($filters as $column => $value) {
            if (!in_array($column, $tables[$table], true) || is_array($value) || strlen((string) $value) > 100) throw new RuntimeException('Filter is not approved');
            $this->db->where($column, substr((string) $value, 0, 100));
        }
        if (!empty($a['search'])) {
            $search_column = isset($a['search_column']) ? (string) $a['search_column'] : '';
            if (!in_array($search_column, $tables[$table], true)) throw new RuntimeException('Search column is not approved');
            $this->db->like($search_column, substr((string) $a['search'], 0, 100));
        }
        if (!empty($a['order_by'])) {
            $order_by = (string) $a['order_by']; if (!in_array($order_by, $tables[$table], true)) throw new RuntimeException('Order column is not approved');
            $direction = isset($a['order_direction']) && strtoupper($a['order_direction']) === 'DESC' ? 'DESC' : 'ASC'; $this->db->order_by($order_by, $direction);
        }
        return array('table' => $table, 'rows' => $this->db->limit($this->safe_limit(isset($a['limit']) ? $a['limit'] : 50, 50))->get()->result_array());
    }

    private function is_admin_role($role)
    {
        return in_array(strtolower(trim((string) $role)), array('admin', 'super admin', 'superadmin'), true);
    }

    private function safe_limit($value, $max) { $value = (int) $value; return max(1, min($max, $value ?: $max)); }
    private function cap($rows, $limit) { if (is_array($rows)) return array_slice($rows, 0, (int) $limit); return $rows; }
    private function apply_teacher_scope($role, $user_id, $class_field, $section_field)
    {
        $role_name = strtolower(trim((string) $role));
        if (strpos($role_name, 'teacher') === false) return;
        $rows = $this->db->select('class_id,section_id')->from('class_teacher')->where(array('staff_id' => (int) $user_id, 'session_id' => $this->current_session))->get()->result_array();
        if (empty($rows)) { $this->db->where($class_field, -1); return; }
        $this->db->group_start();
        foreach ($rows as $index => $row) {
            if ($index > 0) $this->db->or_group_start(); else $this->db->group_start();
            $this->db->where($class_field, (int) $row['class_id'])->where($section_field, (int) $row['section_id'])->group_end();
        }
        $this->db->group_end();
    }
    private function valid_date($value) { $d = DateTime::createFromFormat('Y-m-d', (string) $value); return $d && $d->format('Y-m-d') === $value ? $value : false; }
    private function clean_title($value) { return substr(trim(preg_replace('/\s+/', ' ', strip_tags((string) $value))), 0, 80) ?: 'New chat'; }
    private function uuid() { return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)); }
}
