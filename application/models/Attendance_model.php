<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Attendance Model
 * 
 * Handles all database operations for ZKTeco attendance integration
 * 
 * @package    School Management System
 * @subpackage Models
 * @category   Attendance
 * @author     AI Assistant
 * @version    1.0
 */

class Attendance_model extends MY_Model {
    protected $import_progress_fields = null;
    protected $attendance_log_fields = null;
    protected $zkteco_error_table_checked = false;
    protected $attendance_logs_table_checked = false;
    protected $student_map_table_checked = false;
    protected $staff_map_table_checked = false;
    protected $devices_table_checked = false;
    protected $realtime_audit_table_checked = false;
    protected $last_error = '';
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->current_session = $this->setting_model->getCurrentSession();
        $this->ensure_zkteco_support_tables();
    }

    public function get_last_error() {
        return $this->last_error;
    }

    private function set_last_error($message) {
        $this->last_error = trim((string) $message);
        if ($this->last_error !== '') {
            log_message('error', 'Attendance Model: ' . $this->last_error);
        }
    }

    private function run_db_query_safely($sql) {
        $original_debug = isset($this->db->db_debug) ? $this->db->db_debug : false;
        $this->db->db_debug = false;
        $result = $this->db->query($sql);
        $db_error = $this->db->error();
        $this->db->db_debug = $original_debug;

        if (!$result && !empty($db_error['message'])) {
            $this->set_last_error($db_error['message']);
        }

        return $result;
    }

    private function db_insert_safely($table, $data) {
        $original_debug = isset($this->db->db_debug) ? $this->db->db_debug : false;
        $this->db->db_debug = false;
        $result = $this->db->insert($table, $data);
        $db_error = $this->db->error();
        $this->db->db_debug = $original_debug;

        if (!$result && !empty($db_error['message'])) {
            $this->set_last_error($db_error['message']);
        }

        return $result;
    }

    private function db_update_safely($table, $data) {
        $original_debug = isset($this->db->db_debug) ? $this->db->db_debug : false;
        $this->db->db_debug = false;
        $result = $this->db->update($table, $data);
        $db_error = $this->db->error();
        $this->db->db_debug = $original_debug;

        if (!$result && !empty($db_error['message'])) {
            $this->set_last_error($db_error['message']);
        }

        return $result;
    }
    
    /**
     * Save attendance log to database
     * 
     * @param array $data Attendance data
     * @return int|bool Insert ID or false on failure
     */
    public function save_attendance_log($data) {
        try {
            if (!$this->ensure_attendance_logs_table()) {
                $this->set_last_error('attendance_logs table is missing and could not be created');
                return false;
            }

            // Check if record already exists (avoid duplicates)
            $data = $this->normalize_identity_for_storage($data);
            if (isset($data['admission_no']) && isset($data['timestamp'])) {
                $exists = $this->check_duplicate($data['admission_no'], $data['timestamp']);
                if ($exists) {
                    return false; // Duplicate record
                }
            }

            $fields = $this->get_attendance_log_fields();
            if (!empty($fields)) {
                $data = array_intersect_key($data, array_flip($fields));
            }
            
            $this->db->insert('attendance_logs', $data);
            return $this->db->insert_id();
            
        } catch (Exception $e) {
            log_message('error', 'Attendance Model: Error saving log - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Save multiple attendance logs (batch insert with optimization)
     * 
     * @param array $data_array Array of attendance records
     * @return array Statistics (inserted, duplicates, errors)
     */
    public function save_attendance_logs_batch($data_array) {
        $stats = array(
            'total' => count($data_array),
            'inserted' => 0,
            'duplicates' => 0,
            'errors' => 0
        );
        
        if (empty($data_array)) {
            return $stats;
        }

        if (!$this->ensure_attendance_logs_table()) {
            $stats['errors'] = count($data_array);
            $this->set_last_error('attendance_logs table is missing and could not be created');
            return $stats;
        }
        
        // Filter out records without timestamp first
        $valid_records = array();
        foreach ($data_array as $index => $data) {
            // Validate required fields
            if (empty($data['timestamp'])) {
                $stats['errors']++;
                continue;
            }
            
            // Normalize timestamp
            if (isset($data['timestamp'])) {
                $data['timestamp'] = date('Y-m-d H:i:s', strtotime($data['timestamp']));
            }

            // Normalize identity keys early so duplicate checks match actual stored value.
            $data = $this->normalize_identity_for_storage($data);
            
            $valid_records[] = $data;
        }
        
        if (empty($valid_records)) {
            return $stats;
        }

        // Process in chunks to keep duplicate checks and insert queries small.
        $configured_chunk_size = (int) $this->config->item('zkteco_batch_size');
        $chunk_size = ($configured_chunk_size > 0) ? min(max($configured_chunk_size, 50), 1000) : 300;
        $valid_chunks = array_chunk($valid_records, $chunk_size);

        $attendance_log_fields = $this->get_attendance_log_fields();
        $has_created_at_column = in_array('created_at', $attendance_log_fields, true);

        foreach ($valid_chunks as $records_chunk) {
            $duplicate_keys = $this->check_duplicates_batch($records_chunk);
            $insert_batch = array();

            foreach ($records_chunk as $data) {
                $key = $data['admission_no'] . '|' . $data['timestamp'];

                if (isset($duplicate_keys[$key])) {
                    $stats['duplicates']++;
                    continue;
                }

                // Ensure all required fields are present
                $insert_data = array(
                    'admission_no' => $data['admission_no'],
                    'timestamp' => $data['timestamp'],
                    'status' => isset($data['status']) ? $data['status'] : 'Present',
                    'machine_user_id' => isset($data['machine_user_id']) ? $data['machine_user_id'] : null,
                    'device_id' => isset($data['device_id']) ? $data['device_id'] : null
                );
                if (in_array('staff_id', $attendance_log_fields, true)) {
                    $insert_data['staff_id'] = isset($data['staff_id']) && $data['staff_id'] !== '' ? (int) $data['staff_id'] : null;
                }
                if (in_array('user_type', $attendance_log_fields, true)) {
                    $insert_data['user_type'] = isset($data['user_type']) ? $data['user_type'] : 'student';
                }
                if ($has_created_at_column) {
                    $insert_data['created_at'] = date('Y-m-d H:i:s');
                }

                $insert_batch[] = $insert_data;
            }

            if (empty($insert_batch)) {
                continue;
            }

            try {
                $result = $this->db->insert_batch('attendance_logs', $insert_batch);
                
                if ($result) {
                    $stats['inserted'] += count($insert_batch);
                } else {
                    // If batch insert fails, try individual inserts
                    foreach ($insert_batch as $record) {
                        try {
                            if ($this->db->insert('attendance_logs', $record)) {
                                $stats['inserted']++;
                            } else {
                                $stats['errors']++;
                                log_message('error', 'Attendance Model: Failed to insert record - ' . $this->db->error()['message']);
                            }
                        } catch (Exception $e) {
                            $stats['errors']++;
                            log_message('error', 'Attendance Model: Error inserting record - ' . $e->getMessage());
                        }
                    }
                }
            } catch (Exception $e) {
                log_message('error', 'Attendance Model: Batch insert error - ' . $e->getMessage());
                
                // Fallback to individual inserts
                foreach ($insert_batch as $record) {
                    try {
                        if ($this->db->insert('attendance_logs', $record)) {
                            $stats['inserted']++;
                        } else {
                            $stats['errors']++;
                        }
                    } catch (Exception $e2) {
                        $stats['errors']++;
                    }
                }
            }
        }

        $this->sync_staff_attendance_for_log_records($valid_records);

        // Log summary for debugging/monitoring
        log_message('info', 'Attendance_model::save_attendance_logs_batch summary: '
            . 'total=' . $stats['total']
            . ' inserted=' . $stats['inserted']
            . ' duplicates=' . $stats['duplicates']
            . ' errors=' . $stats['errors']);
        
        return $stats;
    }
    
    /**
     * Batch check for duplicates (more efficient)
     * 
     * @param array $records Array of attendance records
     * @return array Array of duplicate keys
     */
    private function check_duplicates_batch($records) {
        if (empty($records)) {
            return array();
        }

        if (!$this->ensure_attendance_logs_table()) {
            return array();
        }
        
        // Build query to check all records at once
        $conditions = array();
        $params = array();
        
        foreach ($records as $record) {
            $conditions[] = "(admission_no = ? AND timestamp = ?)";
            $params[] = $record['admission_no'];
            $params[] = date('Y-m-d H:i:s', strtotime($record['timestamp']));
        }
        
        if (empty($conditions)) {
            return array();
        }
        
        $sql = "SELECT admission_no, timestamp FROM attendance_logs WHERE " . implode(' OR ', $conditions);
        $query = $this->db->query($sql, $params);
        
        $duplicate_keys = array();
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result_array() as $row) {
                $key = $row['admission_no'] . '|' . $row['timestamp'];
                $duplicate_keys[$key] = true;
            }
        }
        
        return $duplicate_keys;
    }

    /**
     * Build final identity keys used in attendance_logs storage.
     *
     * @param array $data
     * @return array
     */
    private function normalize_identity_for_storage($data) {
        $staff_id = isset($data['staff_id']) ? (int) $data['staff_id'] : 0;
        if ($staff_id > 0) {
            $data['staff_id'] = $staff_id;
            $data['user_type'] = 'staff';
            $data['admission_no'] = 'STAFF_' . $staff_id;
            return $data;
        }

        $admission_no = isset($data['admission_no']) ? trim((string) $data['admission_no']) : '';
        if ($admission_no !== '') {
            $data['admission_no'] = $admission_no;
            $data['staff_id'] = null;
            $data['user_type'] = (strpos($admission_no, 'UNMAPPED_') === 0) ? 'unmapped' : 'student';
            return $data;
        }

        $base_machine_id = isset($data['machine_user_id']) ? trim((string) $data['machine_user_id']) : '';
        if ($base_machine_id === '' && isset($data['uid'])) {
            $base_machine_id = trim((string) $data['uid']);
        }
        if ($base_machine_id === '') {
            $base_machine_id = 'UNKNOWN';
        }

        $data['admission_no'] = 'UNMAPPED_' . substr($base_machine_id, 0, 40);
        $data['staff_id'] = null;
        $data['user_type'] = 'unmapped';
        return $data;
    }
    
    /**
     * Check if attendance record already exists
     * 
     * @param string $admission_no Student admission number
     * @param string $timestamp Attendance timestamp
     * @return bool True if exists, false otherwise
     */
    public function check_duplicate($admission_no, $timestamp) {
        try {
            if (!$this->ensure_attendance_logs_table()) {
                return false;
            }

            // Normalize timestamp
            $normalized_timestamp = date('Y-m-d H:i:s', strtotime($timestamp));
            
            $query = $this->db->select('id')
                              ->from('attendance_logs')
                              ->where('admission_no', $admission_no)
                              ->where('timestamp', $normalized_timestamp)
                              ->limit(1)
                              ->get();
            
            return $query->num_rows() > 0;
        } catch (Exception $e) {
            log_message('error', 'Attendance Model: Error checking duplicate - ' . $e->getMessage());
            return false; // Return false on error to allow insert attempt
        }
    }
    
    /**
     * Get attendance logs by date
     * 
     * @param string $date Date in Y-m-d format
     * @param string $admission_no Optional admission number filter
     * @return array Attendance records
     */
    public function get_attendance_by_date($date, $admission_no = null, $date_field = 'timestamp', $limit = null, $offset = 0) {
        if (!$this->ensure_attendance_logs_table()) {
            return array();
        }

        $filter_column = ($date_field === 'created_at') ? 'al.created_at' : 'al.timestamp';

        $this->db->select('al.*, s.firstname, s.lastname, s.image, s.image as student_image, c.class, sec.section, st.name as staff_name, st.surname as staff_surname, st.employee_id, st.image as staff_image, roles.name as staff_role')
                 ->from('attendance_logs al')
                 ->join('students s', 's.admission_no = al.admission_no', 'left')
                 ->join('student_session ss', 'ss.student_id = s.id', 'left')
                 ->join('classes c', 'c.id = ss.class_id', 'left')
                 ->join('sections sec', 'sec.id = ss.section_id', 'left')
                 ->join('staff st', 'st.id = al.staff_id', 'left')
                 ->join('staff_roles sr', 'sr.staff_id = st.id', 'left')
                 ->join('roles', 'roles.id = sr.role_id', 'left')
                 ->where('DATE(' . $filter_column . ')', $date);
        
        // Include current-session students and also unmapped/non-student logs.
        $this->db->group_start()
                 ->where('ss.session_id', $this->current_session)
                 ->or_where('ss.session_id IS NULL', null, false)
                 ->group_end();
        
        if ($admission_no) {
            $this->db->where('al.admission_no', $admission_no);
        }
        
        $this->db->group_by('al.id');
        $this->db->order_by('al.timestamp', 'DESC');
        if ($limit !== null) {
            $this->db->limit((int) $limit, max(0, (int) $offset));
        }
        
        $query = $this->db->get();
        $logs = $query->result_array();
        return $this->normalize_statuses_from_machine($logs);
    }

    public function count_attendance_by_date($date, $admission_no = null, $date_field = 'timestamp') {
        if (!$this->ensure_attendance_logs_table()) {
            return 0;
        }

        $filter_column = ($date_field === 'created_at') ? 'created_at' : 'timestamp';
        $this->db->from('attendance_logs')
                 ->where('DATE(' . $filter_column . ')', $date);

        if ($admission_no) {
            $this->db->where('admission_no', $admission_no);
        }

        return (int) $this->db->count_all_results();
    }
    
    /**
     * Get today's attendance logs
     * 
     * @return array Attendance records
     */
    public function get_todays_attendance() {
        $today = date('Y-m-d');
        return $this->get_attendance_by_date($today);
    }
    
    /**
     * Get attendance logs by date range
     * 
     * @param string $start_date Start date
     * @param string $end_date End date
     * @param string $admission_no Optional admission number filter
     * @return array Attendance records
     */
    public function get_attendance_by_range($start_date, $end_date, $admission_no = null) {
        if (!$this->ensure_attendance_logs_table()) {
            return array();
        }

        $this->db->select('al.*, s.firstname, s.lastname, s.image, s.image as student_image, c.class, sec.section, st.name as staff_name, st.surname as staff_surname, st.employee_id, st.image as staff_image, roles.name as staff_role')
                 ->from('attendance_logs al')
                 ->join('students s', 's.admission_no = al.admission_no', 'left')
                 ->join('student_session ss', 'ss.student_id = s.id', 'left')
                 ->join('classes c', 'c.id = ss.class_id', 'left')
                 ->join('sections sec', 'sec.id = ss.section_id', 'left')
                 ->join('staff st', 'st.id = al.staff_id', 'left')
                 ->join('staff_roles sr', 'sr.staff_id = st.id', 'left')
                 ->join('roles', 'roles.id = sr.role_id', 'left')
                 ->where('DATE(al.timestamp) >=', $start_date)
                 ->where('DATE(al.timestamp) <=', $end_date);

        $this->db->group_start()
                 ->where('ss.session_id', $this->current_session)
                 ->or_where('ss.session_id IS NULL', null, false)
                 ->group_end();
        
        if ($admission_no) {
            $this->db->where('al.admission_no', $admission_no);
        }
        
        $this->db->order_by('al.timestamp', 'DESC');
        
        $query = $this->db->get();
        $logs = $query->result_array();
        return $this->normalize_statuses_from_machine($logs);
    }
    
    /**
     * Get attendance logs by class and section
     * 
     * @param int $class_id Class ID
     * @param int $section_id Section ID
     * @param string $date Date in Y-m-d format
     * @return array Attendance records
     */
    public function get_attendance_by_class($class_id, $section_id, $date) {
        if (!$this->ensure_attendance_logs_table()) {
            return array();
        }

        $this->db->select('al.*, s.firstname, s.lastname, s.image, s.roll_no')
                 ->from('attendance_logs al')
                 ->join('students s', 's.admission_no = al.admission_no', 'inner')
                 ->join('student_session ss', 'ss.student_id = s.id', 'inner')
                 ->where('ss.class_id', $class_id)
                 ->where('ss.section_id', $section_id)
                 ->where('DATE(al.timestamp)', $date)
                 ->where('ss.session_id', $this->current_session)
                 ->order_by('s.roll_no', 'ASC');
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Build student attendance summary by date range and integrate leave statuses.
     *
     * @param int $class_id
     * @param int|null $section_id
     * @param string $from_date Y-m-d
     * @param string $to_date Y-m-d
     * @param string|null $admission_no
     * @return array
     */
    public function get_student_attendance_report($class_id, $section_id, $from_date, $to_date, $admission_no = null) {
        $result = array(
            'rows' => array(),
            'date_list' => array(),
            'summary' => array(
                'students' => 0,
                'present' => 0,
                'absent' => 0,
                'leave_approved' => 0,
                'leave_pending' => 0,
                'leave_denied' => 0
            )
        );

        if (empty($class_id) || empty($from_date) || empty($to_date)) {
            return $result;
        }

        if (!$this->ensure_attendance_logs_table()) {
            return $result;
        }

        $from_ts = strtotime($from_date . ' 00:00:00');
        $to_ts = strtotime($to_date . ' 00:00:00');
        if ($from_ts === false || $to_ts === false || $from_ts > $to_ts) {
            return $result;
        }

        for ($ts = $from_ts; $ts <= $to_ts; $ts = strtotime('+1 day', $ts)) {
            $result['date_list'][] = date('Y-m-d', $ts);
        }

        $this->db->select('s.id as student_id, s.admission_no, s.roll_no, s.firstname, s.middlename, s.lastname, ss.id as student_session_id, c.class, sec.section')
                 ->from('student_session ss')
                 ->join('students s', 's.id = ss.student_id', 'inner')
                 ->join('classes c', 'c.id = ss.class_id', 'left')
                 ->join('sections sec', 'sec.id = ss.section_id', 'left')
                 ->where('ss.session_id', $this->current_session)
                 ->where('ss.class_id', $class_id)
                 ->where('s.is_active', 'yes')
                 ->order_by('s.roll_no', 'ASC')
                 ->order_by('s.admission_no', 'ASC');

        if (!empty($section_id)) {
            $this->db->where('ss.section_id', $section_id);
        }
        if (!empty($admission_no)) {
            $this->db->where('s.admission_no', $admission_no);
        }

        $students_query = $this->db->get();
        $students = $students_query ? $students_query->result_array() : array();
        if (empty($students)) {
            return $result;
        }

        $admission_list = array();
        $session_id_list = array();
        foreach ($students as $student) {
            if (!empty($student['admission_no'])) {
                $admission_list[] = $student['admission_no'];
            }
            if (!empty($student['student_session_id'])) {
                $session_id_list[] = (int) $student['student_session_id'];
            }
        }

        $attendance_map = array();
        if (!empty($admission_list)) {
            $logs_query = $this->db->select('admission_no, DATE(timestamp) as log_date, MIN(timestamp) as first_punch, MAX(timestamp) as last_punch, COUNT(*) as punch_count')
                                  ->from('attendance_logs')
                                  ->where_in('admission_no', $admission_list)
                                  ->where('DATE(timestamp) >=', $from_date)
                                  ->where('DATE(timestamp) <=', $to_date)
                                  ->group_by('admission_no, DATE(timestamp)')
                                  ->get();
            $logs = $logs_query ? $logs_query->result_array() : array();
            foreach ($logs as $log) {
                $ad = (string) $log['admission_no'];
                $ld = (string) $log['log_date'];
                if (!isset($attendance_map[$ad])) {
                    $attendance_map[$ad] = array();
                }
                $attendance_map[$ad][$ld] = $log;
            }
        }

        $leave_map = array();
        if (!empty($session_id_list)) {
            $leave_query = $this->db->select('student_session_id, from_date, to_date, status')
                                    ->from('student_applyleave')
                                    ->where_in('student_session_id', $session_id_list)
                                    ->where('from_date <=', $to_date)
                                    ->where('to_date >=', $from_date)
                                    ->get();
            $leave_rows = $leave_query ? $leave_query->result_array() : array();

            foreach ($leave_rows as $leave) {
                $session_id = (int) $leave['student_session_id'];
                $leave_from = max(strtotime($from_date . ' 00:00:00'), strtotime($leave['from_date'] . ' 00:00:00'));
                $leave_to = min(strtotime($to_date . ' 00:00:00'), strtotime($leave['to_date'] . ' 00:00:00'));
                if ($leave_from === false || $leave_to === false || $leave_from > $leave_to) {
                    continue;
                }

                $status_raw = strtolower(trim((string) $leave['status']));
                if ($status_raw === '1' || $status_raw === 'approve') {
                    $leave_status = 'approved';
                } elseif ($status_raw === '0' || $status_raw === 'disapprove' || $status_raw === 'denied') {
                    $leave_status = 'denied';
                } else {
                    $leave_status = 'pending';
                }

                for ($day_ts = $leave_from; $day_ts <= $leave_to; $day_ts = strtotime('+1 day', $day_ts)) {
                    $day_key = date('Y-m-d', $day_ts);
                    if (!isset($leave_map[$session_id])) {
                        $leave_map[$session_id] = array();
                    }

                    // Priority: approved > pending > denied.
                    $existing = isset($leave_map[$session_id][$day_key]) ? $leave_map[$session_id][$day_key] : '';
                    $priority = array('denied' => 1, 'pending' => 2, 'approved' => 3);
                    if ($existing === '' || $priority[$leave_status] >= $priority[$existing]) {
                        $leave_map[$session_id][$day_key] = $leave_status;
                    }
                }
            }
        }

        foreach ($students as $student) {
            $student_admission = isset($student['admission_no']) ? (string) $student['admission_no'] : '';
            $student_session_id = isset($student['student_session_id']) ? (int) $student['student_session_id'] : 0;
            $total_days = count($result['date_list']);
            $present_days = 0;
            $approved_leave_days = 0;
            $pending_leave_days = 0;
            $denied_leave_days = 0;
            $absent_days = 0;
            $punch_count_total = 0;

            foreach ($result['date_list'] as $day) {
                $has_log = isset($attendance_map[$student_admission]) && isset($attendance_map[$student_admission][$day]);
                $leave_status = isset($leave_map[$student_session_id]) && isset($leave_map[$student_session_id][$day]) ? $leave_map[$student_session_id][$day] : '';

                if ($has_log) {
                    $present_days++;
                    $punch_count_total += isset($attendance_map[$student_admission][$day]['punch_count']) ? (int) $attendance_map[$student_admission][$day]['punch_count'] : 0;
                    continue;
                }

                if ($leave_status === 'approved') {
                    $approved_leave_days++;
                } elseif ($leave_status === 'pending') {
                    $pending_leave_days++;
                    $absent_days++;
                } elseif ($leave_status === 'denied') {
                    $denied_leave_days++;
                    $absent_days++;
                } else {
                    $absent_days++;
                }
            }

            $effective_days = max($total_days - $approved_leave_days, 0);
            $attendance_percent = ($effective_days > 0) ? round(($present_days / $effective_days) * 100, 2) : 0;

            $result['rows'][] = array(
                'student_id' => (int) $student['student_id'],
                'student_session_id' => $student_session_id,
                'admission_no' => $student_admission,
                'roll_no' => isset($student['roll_no']) ? $student['roll_no'] : '',
                'student_name' => trim($student['firstname'] . ' ' . $student['middlename'] . ' ' . $student['lastname']),
                'class' => isset($student['class']) ? $student['class'] : '',
                'section' => isset($student['section']) ? $student['section'] : '',
                'total_days' => $total_days,
                'effective_days' => $effective_days,
                'present_days' => $present_days,
                'absent_days' => $absent_days,
                'leave_approved_days' => $approved_leave_days,
                'leave_pending_days' => $pending_leave_days,
                'leave_denied_days' => $denied_leave_days,
                'punch_count_total' => $punch_count_total,
                'attendance_percent' => $attendance_percent
            );

            $result['summary']['present'] += $present_days;
            $result['summary']['absent'] += $absent_days;
            $result['summary']['leave_approved'] += $approved_leave_days;
            $result['summary']['leave_pending'] += $pending_leave_days;
            $result['summary']['leave_denied'] += $denied_leave_days;
        }

        $result['summary']['students'] = count($result['rows']);
        return $result;
    }

    /**
     * Save one collector event without changing the legacy batch-import path.
     * The return value is safe to expose as an API response (never contains secrets).
     */
    public function save_realtime_attendance_log($event, $dedupe_window_seconds = 30) {
        if (!$this->ensure_attendance_logs_table()) {
            return array('outcome' => 'error', 'message' => 'Attendance log storage is unavailable');
        }

        $machine_user_id = trim((string) (isset($event['id']) ? $event['id'] : ''));
        $timestamp = date('Y-m-d H:i:s', strtotime((string) $event['timestamp']));
        $sync_uid = trim((string) (isset($event['sync_uid']) ? $event['sync_uid'] : ''));
        $device_id = trim((string) (isset($event['device_id']) ? $event['device_id'] : ''));
        $source = trim((string) (isset($event['source']) ? $event['source'] : 'zkteco_realtime'));
        if ($machine_user_id === '' || $timestamp === '1970-01-01 00:00:00' || $sync_uid === '') {
            return array('outcome' => 'error', 'message' => 'Invalid realtime attendance event');
        }

        $existing = $this->db->select('id, timestamp')->from('attendance_logs')
            ->where('sync_uid', $sync_uid)->limit(1)->get()->row_array();
        if ($existing) {
            return array('outcome' => 'duplicate', 'message' => 'Already synced', 'id' => (int) $existing['id']);
        }

        $student_map = $this->db->select('admission_no')->from('zkteco_student_map')
            ->where('machine_user_id', $machine_user_id)->limit(1)->get()->row_array();
        $staff_map = empty($student_map) ? $this->db->select('staff_id')->from('zkteco_staff_map')
            ->where('machine_user_id', $machine_user_id)->limit(1)->get()->row_array() : null;
        $mapped = !empty($student_map) || !empty($staff_map);

        $identity_where = array('machine_user_id' => $machine_user_id);
        $from = date('Y-m-d H:i:s', strtotime($timestamp . ' -' . max(1, (int) $dedupe_window_seconds) . ' seconds'));
        $to = date('Y-m-d H:i:s', strtotime($timestamp . ' +' . max(1, (int) $dedupe_window_seconds) . ' seconds'));
        $near = $this->db->select('id, timestamp')->from('attendance_logs')->where($identity_where)
            ->where('timestamp >=', $from)->where('timestamp <=', $to)->order_by('timestamp', 'ASC')->limit(1)->get()->row_array();
        if ($near) {
            return array('outcome' => 'near_duplicate', 'message' => 'Duplicate scan ignored', 'id' => (int) $near['id'], 'existing_timestamp' => $near['timestamp']);
        }

        $data = array(
            'timestamp' => $timestamp,
            'status' => isset($event['state']) ? (string) $event['state'] : 'Present',
            'machine_user_id' => $machine_user_id,
            'device_id' => $device_id,
            'sync_uid' => $sync_uid,
            'source' => $source,
            'created_at' => date('Y-m-d H:i:s')
        );
        if (!empty($student_map['admission_no'])) {
            $data['admission_no'] = $student_map['admission_no'];
            $data['user_type'] = 'student';
            $data['staff_id'] = null;
        } elseif (!empty($staff_map['staff_id'])) {
            $data['admission_no'] = 'STAFF_' . (int) $staff_map['staff_id'];
            $data['staff_id'] = (int) $staff_map['staff_id'];
            $data['user_type'] = 'staff';
        } else {
            $data['admission_no'] = 'UNMAPPED_' . substr($machine_user_id, 0, 40);
            $data['staff_id'] = null;
            $data['user_type'] = 'unmapped';
        }
        $data = array_intersect_key($data, array_flip($this->get_attendance_log_fields()));

        if (!$this->db_insert_safely('attendance_logs', $data)) {
            return array('outcome' => 'error', 'message' => $this->get_last_error() ?: 'Unable to save attendance event');
        }
        $id = (int) $this->db->insert_id();
        if (!empty($staff_map['staff_id'])) {
            $this->sync_staff_attendance_for_log_records(array(array('staff_id' => (int) $staff_map['staff_id'], 'timestamp' => $timestamp)));
        }
        return array('outcome' => $mapped ? 'inserted' : 'unmapped', 'message' => $mapped ? 'Attendance synced' : 'Unmapped device user recorded', 'id' => $id);
    }

    /** Store sanitized realtime outcomes separately from legacy import errors. */
    public function capture_realtime_audit($outcome, $message, $event = array(), $http_status = null) {
        $safe_event = array(
            'device_id' => isset($event['device_id']) ? substr((string) $event['device_id'], 0, 100) : '',
            'machine_user_id' => isset($event['id']) ? substr((string) $event['id'], 0, 100) : '',
            'timestamp' => isset($event['timestamp']) ? substr((string) $event['timestamp'], 0, 30) : '',
            'sync_uid' => isset($event['sync_uid']) ? substr((string) $event['sync_uid'], 0, 191) : '',
            'source' => isset($event['source']) ? substr((string) $event['source'], 0, 50) : ''
        );
        log_message($outcome === 'error' ? 'error' : 'info', 'ZKTecoRealtime[' . $outcome . ']: ' . $message . ' | ' . json_encode($safe_event));
        if (!$this->ensure_realtime_audit_table()) return;
        $this->db_insert_safely('zkteco_realtime_audit', array(
            'outcome' => substr((string) $outcome, 0, 50), 'message' => substr((string) $message, 0, 1000),
            'device_id' => $safe_event['device_id'], 'machine_user_id' => $safe_event['machine_user_id'],
            'sync_uid' => $safe_event['sync_uid'], 'http_status' => $http_status, 'created_at' => date('Y-m-d H:i:s')
        ));
    }

    public function get_realtime_diagnostics($inactive_minutes = 15) {
        $this->ensure_realtime_audit_table();
        $last = $this->db->select_max('created_at')->where_in('outcome', array('inserted', 'duplicate', 'near_duplicate', 'unmapped'))->get('zkteco_realtime_audit')->row_array();
        $rows = $this->db->select('outcome, COUNT(*) AS total')->from('zkteco_realtime_audit')->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))->group_by('outcome')->get()->result_array();
        $counts = array(); foreach ($rows as $row) $counts[$row['outcome']] = (int) $row['total'];
        $latest_error = $this->db->order_by('id', 'DESC')->limit(1)->get_where('zkteco_realtime_audit', array('outcome' => 'error'))->row_array();
        $last_at = !empty($last['created_at']) ? $last['created_at'] : null;
        return array('last_sync_at' => $last_at, 'inactive' => !$last_at || strtotime($last_at) < strtotime('-' . max(1, (int) $inactive_minutes) . ' minutes'), 'counts_24h' => $counts, 'latest_error' => $latest_error);
    }

    /** Read-only reconciliation helper for collector-exported sync identifiers. */
    public function get_existing_realtime_sync_uids($sync_uids) {
        if (!$this->ensure_attendance_logs_table() || !is_array($sync_uids)) return array();
        $sync_uids = array_values(array_unique(array_filter($sync_uids, function ($uid) { return is_string($uid) && strlen($uid) <= 191; })));
        if (empty($sync_uids)) return array();
        return array_column($this->db->select('sync_uid')->from('attendance_logs')->where_in('sync_uid', $sync_uids)->get()->result_array(), 'sync_uid');
    }

    /**
     * List active staff roles for the attendance report filter.
     *
     * @return array
     */
    public function get_staff_report_roles() {
        if (!$this->db->table_exists('roles')) {
            return array();
        }

        $this->db->select('roles.id, roles.name as type')
                 ->from('roles')
                 ->where('roles.is_active', 'yes')
                 ->order_by('roles.id', 'ASC');

        $query = $this->db->get();
        return $query ? $query->result_array() : array();
    }

    /**
     * List active staff for the attendance report filter.
     *
     * @return array
     */
    public function get_staff_report_people() {
        $query = $this->db->select('staff.id, staff.employee_id, staff.name, staff.surname, roles.id as role_id, roles.name as staff_role')
                          ->from('staff')
                          ->join('staff_roles sr', 'sr.staff_id = staff.id', 'left')
                          ->join('roles', 'roles.id = sr.role_id', 'left')
                          ->where('staff.is_active', 1)
                          ->order_by('staff.name', 'ASC')
                          ->order_by('staff.surname', 'ASC')
                          ->get();

        return $query ? $query->result_array() : array();
    }

    /**
     * Build staff attendance report from staff attendance rows and staff ZKTeco logs.
     *
     * Missing non-holiday days are treated as Absent. Approved staff leave is
     * shown as Leave when no staff_attendance row already exists for that day.
     *
     * @param int $role_id
     * @param int $staff_id
     * @param string $from_date Y-m-d
     * @param string $to_date Y-m-d
     * @return array
     */
    public function get_staff_attendance_report($role_id, $staff_id, $from_date, $to_date) {
        $result = array(
            'rows' => array(),
            'daily_rows' => array(),
            'date_list' => array(),
            'summary' => array(
                'staff' => 0,
                'working_days' => 0,
                'present' => 0,
                'absent' => 0,
                'late' => 0,
                'leave' => 0,
                'half_day' => 0,
                'holiday' => 0,
            )
        );

        if (empty($from_date) || empty($to_date) || !$this->db->table_exists('staff') || !$this->db->table_exists('staff_attendance')) {
            return $result;
        }

        $from_ts = strtotime($from_date . ' 00:00:00');
        $to_ts = strtotime($to_date . ' 00:00:00');
        if ($from_ts === false || $to_ts === false || $from_ts > $to_ts) {
            return $result;
        }

        for ($ts = $from_ts; $ts <= $to_ts; $ts = strtotime('+1 day', $ts)) {
            $result['date_list'][] = date('Y-m-d', $ts);
        }

        $staff_rows = $this->get_staff_report_staff_rows($role_id, $staff_id);
        if (empty($staff_rows)) {
            return $result;
        }

        $staff_ids = array();
        foreach ($staff_rows as $staff) {
            $staff_ids[] = (int) $staff['id'];
        }

        $attendance_map = $this->get_staff_attendance_map($staff_ids, $from_date, $to_date);
        $log_time_map = $this->get_staff_log_time_map($staff_ids, $from_date, $to_date);
        $leave_map = $this->get_staff_leave_map($staff_ids, $from_date, $to_date);
        $holiday_dates = $this->get_staff_holiday_dates($from_date, $to_date);

        foreach ($staff_rows as $staff) {
            $sid = (int) $staff['id'];
            $row = array(
                'staff_id' => $sid,
                'employee_id' => isset($staff['employee_id']) ? $staff['employee_id'] : '',
                'staff_name' => trim((isset($staff['name']) ? $staff['name'] : '') . ' ' . (isset($staff['surname']) ? $staff['surname'] : '')),
                'staff_role' => isset($staff['staff_role']) ? $staff['staff_role'] : '',
                'total_days' => count($result['date_list']),
                'working_days' => 0,
                'present_days' => 0,
                'absent_days' => 0,
                'late_days' => 0,
                'leave_days' => 0,
                'half_days' => 0,
                'holiday_days' => 0,
                'attendance_percent' => 0,
            );

            foreach ($result['date_list'] as $day) {
                $attendance = isset($attendance_map[$sid][$day]) ? $attendance_map[$sid][$day] : null;
                $log_times = isset($log_time_map[$sid][$day]) ? $log_time_map[$sid][$day] : null;
                $leave_status = isset($leave_map[$sid][$day]) ? $leave_map[$sid][$day] : '';

                if ($attendance) {
                    $status_key = $this->normalize_staff_attendance_status(
                        isset($attendance['staff_attendance_type_id']) ? (int) $attendance['staff_attendance_type_id'] : 0,
                        isset($attendance['att_type']) ? $attendance['att_type'] : '',
                        isset($attendance['key_value']) ? $attendance['key_value'] : ''
                    );
                    $status_label = $this->staff_attendance_status_label($status_key, isset($attendance['att_type']) ? $attendance['att_type'] : '');
                    $check_in = isset($attendance['check_in']) ? $this->format_staff_report_time($attendance['check_in']) : '-';
                    $check_out = isset($attendance['check_out']) ? $this->format_staff_report_time($attendance['check_out']) : '-';
                    if ($log_times) {
                        if ($check_in === '-') {
                            $check_in = isset($log_times['check_in']) ? $this->format_staff_report_time($log_times['check_in']) : '-';
                        }
                        if ($check_out === '-') {
                            $check_out = isset($log_times['check_out']) ? $this->format_staff_report_time($log_times['check_out']) : '-';
                        }
                    }
                    $remark = isset($attendance['remark']) ? $attendance['remark'] : '';
                } elseif ($log_times) {
                    $status_key = 'present';
                    $status_label = 'Present';
                    $check_in = isset($log_times['check_in']) ? $this->format_staff_report_time($log_times['check_in']) : '-';
                    $check_out = isset($log_times['check_out']) ? $this->format_staff_report_time($log_times['check_out']) : '-';
                    $remark = 'ZKTeco attendance log';
                } elseif ($leave_status === 'approve' || $leave_status === 'approved') {
                    $status_key = 'leave';
                    $status_label = 'Leave';
                    $check_in = '-';
                    $check_out = '-';
                    $remark = 'Approved leave';
                } elseif (isset($holiday_dates[$day])) {
                    $status_key = 'holiday';
                    $status_label = 'Holiday';
                    $check_in = '-';
                    $check_out = '-';
                    $remark = 'Holiday';
                } else {
                    $status_key = 'absent';
                    $status_label = 'Absent';
                    $check_in = '-';
                    $check_out = '-';
                    $remark = '';
                }

                $this->add_staff_attendance_status_count($row, $status_key);
                $result['daily_rows'][] = array(
                    'staff_id' => $sid,
                    'employee_id' => $row['employee_id'],
                    'staff_name' => $row['staff_name'],
                    'staff_role' => $row['staff_role'],
                    'date' => $day,
                    'status_key' => $status_key,
                    'status' => $status_label,
                    'check_in' => $check_in,
                    'check_out' => $check_out,
                    'remark' => $remark,
                );
            }

            $row['attendance_percent'] = ($row['working_days'] > 0) ? round(((float) $row['present_days'] / $row['working_days']) * 100, 2) : 0;

            $result['rows'][] = $row;
            $result['summary']['working_days'] += $row['working_days'];
            $result['summary']['present'] += $row['present_days'];
            $result['summary']['absent'] += $row['absent_days'];
            $result['summary']['late'] += $row['late_days'];
            $result['summary']['leave'] += $row['leave_days'];
            $result['summary']['half_day'] += $row['half_days'];
            $result['summary']['holiday'] += $row['holiday_days'];
        }

        $result['summary']['staff'] = count($result['rows']);
        return $result;
    }

    private function get_staff_report_staff_rows($role_id, $staff_id) {
        $role_id = (int) $role_id;
        $staff_id = (int) $staff_id;

        $this->db->select('staff.id, staff.employee_id, staff.name, staff.surname, roles.id as role_id, roles.name as staff_role')
                 ->from('staff')
                 ->join('staff_roles sr', 'sr.staff_id = staff.id', 'left')
                 ->join('roles', 'roles.id = sr.role_id', 'left')
                 ->where('staff.is_active', 1);

        if ($role_id > 0) {
            $this->db->where('roles.id', $role_id);
        }
        if ($staff_id > 0) {
            $this->db->where('staff.id', $staff_id);
        }

        $query = $this->db->order_by('staff.name', 'ASC')
                          ->order_by('staff.surname', 'ASC')
                          ->get();

        return $query ? $query->result_array() : array();
    }

    private function get_staff_attendance_map($staff_ids, $from_date, $to_date) {
        $map = array();
        if (empty($staff_ids)) {
            return $map;
        }

        $fields = $this->db->list_fields('staff_attendance');
        $select = 'sa.id, sa.staff_id, sa.date, sa.staff_attendance_type_id, sa.remark';
        $select .= in_array('check_in', $fields, true) ? ', sa.check_in' : ', NULL as check_in';
        $select .= in_array('check_out', $fields, true) ? ', sa.check_out' : ', NULL as check_out';

        $has_type_table = $this->db->table_exists('staff_attendance_type');
        $type_select = $has_type_table ? ', sat.type as att_type, sat.key_value' : ", '' as att_type, '' as key_value";
        $this->db->select($select . $type_select, false)
                 ->from('staff_attendance sa');
        if ($has_type_table) {
            $this->db->join('staff_attendance_type sat', 'sat.id = sa.staff_attendance_type_id', 'left');
        }
        $query = $this->db->where_in('sa.staff_id', $staff_ids)
                          ->where('sa.date >=', $from_date)
                          ->where('sa.date <=', $to_date)
                          ->order_by('sa.id', 'ASC')
                          ->get();

        $rows = $query ? $query->result_array() : array();
        foreach ($rows as $row) {
            $sid = (int) $row['staff_id'];
            $day = (string) $row['date'];
            if (!isset($map[$sid])) {
                $map[$sid] = array();
            }
            $map[$sid][$day] = $row;
        }

        return $map;
    }

    private function get_staff_log_time_map($staff_ids, $from_date, $to_date) {
        $map = array();
        if (empty($staff_ids) || !$this->ensure_attendance_logs_table()) {
            return $map;
        }

        $query = $this->db->select('staff_id, DATE(timestamp) as attendance_date, MIN(timestamp) as first_punch, MAX(timestamp) as last_punch, COUNT(*) as punch_count', false)
                          ->from('attendance_logs')
                          ->where_in('staff_id', $staff_ids)
                          ->where('user_type', 'staff')
                          ->where('DATE(timestamp) >=', $from_date)
                          ->where('DATE(timestamp) <=', $to_date)
                          ->group_by('staff_id, DATE(timestamp)')
                          ->get();

        $rows = $query ? $query->result_array() : array();
        foreach ($rows as $row) {
            $sid = (int) $row['staff_id'];
            $day = (string) $row['attendance_date'];
            if ($sid <= 0 || $day === '') {
                continue;
            }
            if (!isset($map[$sid])) {
                $map[$sid] = array();
            }

            $punch_count = isset($row['punch_count']) ? (int) $row['punch_count'] : 0;
            $map[$sid][$day] = array(
                'check_in' => isset($row['first_punch']) ? $row['first_punch'] : '',
                'check_out' => ($punch_count > 1 && !empty($row['last_punch'])) ? $row['last_punch'] : '',
                'punch_count' => $punch_count,
            );
        }

        return $map;
    }

    private function get_staff_leave_map($staff_ids, $from_date, $to_date) {
        $map = array();
        if (empty($staff_ids) || !$this->db->table_exists('staff_leave_request')) {
            return $map;
        }

        $query = $this->db->select('staff_id, leave_from, leave_to, status')
                          ->from('staff_leave_request')
                          ->where_in('staff_id', $staff_ids)
                          ->where('leave_from <=', $to_date)
                          ->where('leave_to >=', $from_date)
                          ->get();

        $rows = $query ? $query->result_array() : array();
        foreach ($rows as $leave) {
            $sid = (int) $leave['staff_id'];
            $status = strtolower(trim((string) $leave['status']));
            if ($status !== 'approve' && $status !== 'approved') {
                continue;
            }

            $leave_from = max(strtotime($from_date . ' 00:00:00'), strtotime($leave['leave_from'] . ' 00:00:00'));
            $leave_to = min(strtotime($to_date . ' 00:00:00'), strtotime($leave['leave_to'] . ' 00:00:00'));
            if ($leave_from === false || $leave_to === false || $leave_from > $leave_to) {
                continue;
            }

            for ($ts = $leave_from; $ts <= $leave_to; $ts = strtotime('+1 day', $ts)) {
                if (!isset($map[$sid])) {
                    $map[$sid] = array();
                }
                $map[$sid][date('Y-m-d', $ts)] = 'approve';
            }
        }

        return $map;
    }

    private function get_staff_holiday_dates($from_date, $to_date) {
        $holidays = array();
        if (!$this->db->table_exists('staff_attendance')) {
            return $holidays;
        }

        $this->config->load('payroll', false, true);
        $staff_attendance_config = $this->config->item('staffattendance');
        $holiday_type_id = isset($staff_attendance_config['holiday']) ? (int) $staff_attendance_config['holiday'] : 5;

        $query = $this->db->select('date')
                          ->from('staff_attendance')
                          ->where('staff_attendance_type_id', $holiday_type_id)
                          ->where('date >=', $from_date)
                          ->where('date <=', $to_date)
                          ->group_by('date')
                          ->get();

        $rows = $query ? $query->result_array() : array();
        foreach ($rows as $row) {
            if (!empty($row['date'])) {
                $holidays[(string) $row['date']] = true;
            }
        }

        return $holidays;
    }

    private function normalize_staff_attendance_status($type_id, $type, $key_value) {
        $this->config->load('payroll', false, true);
        $staff_attendance_config = $this->config->item('staffattendance');
        if (!is_array($staff_attendance_config)) {
            $staff_attendance_config = array(
                'present' => 1,
                'half_day' => 4,
                'late' => 2,
                'absent' => 3,
                'holiday' => 5,
            );
        }

        foreach ($staff_attendance_config as $key => $configured_id) {
            if ($type_id > 0 && (int) $configured_id === $type_id) {
                return $key;
            }
        }

        $text = strtolower(trim((string) $type . ' ' . (string) $key_value));
        if (strpos($text, 'holiday') !== false) {
            return 'holiday';
        }
        if (strpos($text, 'half') !== false) {
            return 'half_day';
        }
        if (strpos($text, 'late') !== false) {
            return 'late';
        }
        if (strpos($text, 'leave') !== false) {
            return 'leave';
        }
        if (strpos($text, 'absent') !== false) {
            return 'absent';
        }
        if (strpos($text, 'present') !== false) {
            return 'present';
        }

        return 'present';
    }

    private function staff_attendance_status_label($status_key, $fallback_type = '') {
        $labels = array(
            'present' => 'Present',
            'late' => 'Late',
            'absent' => 'Absent',
            'leave' => 'Leave',
            'half_day' => 'Half Day',
            'holiday' => 'Holiday',
        );

        if (isset($labels[$status_key])) {
            return $labels[$status_key];
        }

        return trim((string) $fallback_type) !== '' ? trim((string) $fallback_type) : 'Present';
    }

    private function add_staff_attendance_status_count(&$row, $status_key) {
        if ($status_key === 'holiday') {
            $row['holiday_days']++;
            return;
        }

        $row['working_days']++;
        if ($status_key === 'present') {
            $row['present_days']++;
        } elseif ($status_key === 'late') {
            $row['present_days']++;
            $row['late_days']++;
        } elseif ($status_key === 'leave') {
            $row['leave_days']++;
        } elseif ($status_key === 'half_day') {
            $row['present_days'] += 0.5;
            $row['absent_days'] += 0.5;
            $row['half_days']++;
        } else {
            $row['absent_days']++;
        }
    }

    private function format_staff_report_time($time) {
        $time = trim((string) $time);
        if ($time === '' || $time === '00:00:00') {
            return '-';
        }

        $timestamp = strtotime($time);
        return $timestamp === false ? $time : date('h:i A', $timestamp);
    }
    
    /**
     * Get student mapping (machine user ID to admission number)
     * 
     * @param string $machine_user_id Optional machine user ID
     * @return array|object Mapping records
     */
    public function get_student_mapping($machine_user_id = null) {
        if (!$this->ensure_student_map_table()) {
            return $machine_user_id ? null : array();
        }

        $this->db->select('*')->from('zkteco_student_map');
        
        if ($machine_user_id) {
            $this->db->where('machine_user_id', $machine_user_id);
            $query = $this->db->get();
            return $query->row();
        }
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    /**
     * Get all student mappings as associative array
     * 
     * @return array Array with machine_user_id as key and admission_no as value
     */
    public function get_student_mapping_array() {
        if (!$this->ensure_student_map_table()) {
            return array();
        }

        $query = $this->db->select('machine_user_id, admission_no')
                          ->from('zkteco_student_map')
                          ->get();
        
        $mapping = array();
        foreach ($query->result_array() as $row) {
            $mapping[$row['machine_user_id']] = $row['admission_no'];
        }
        
        return $mapping;
    }
    
    /**
     * Save or update student mapping
     * 
     * @param array $data Mapping data
     * @return bool Success status
     */
    public function save_student_mapping($data) {
        try {
            $this->last_error = '';

            if (!$this->ensure_student_map_table()) {
                $this->set_last_error('zkteco_student_map table is missing and could not be created');
                return false;
            }

            if ($this->ensure_staff_map_table()) {
                $this->db->delete('zkteco_staff_map', array('machine_user_id' => $data['machine_user_id']));
            }

            // Check if mapping already exists
            $exists = $this->db->select('id')
                               ->from('zkteco_student_map')
                               ->where('machine_user_id', $data['machine_user_id'])
                               ->get()
                               ->row();
            
            if ($exists) {
                // Update existing mapping
                $this->db->where('id', $exists->id);
                $result = $this->db_update_safely('zkteco_student_map', $data);
                if (!$result && $this->last_error === '') {
                    $this->set_last_error('Failed to update zkteco student mapping');
                }
                return $result;
            } else {
                // Insert new mapping
                $result = $this->db_insert_safely('zkteco_student_map', $data);
                if (!$result && $this->last_error === '') {
                    $this->set_last_error('Failed to insert zkteco student mapping');
                }
                return $result;
            }
            
        } catch (Exception $e) {
            log_message('error', 'Attendance Model: Error saving mapping - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Automatically map unmapped logs for a date using multiple mapping rules.
     *
     * @param string $date Y-m-d
     * @return array
     */
    public function auto_map_unmapped_logs_by_date($date) {
        $stats = array(
            'date' => $date,
            'scanned' => 0,
            'mapped' => 0,
            'student_mapped' => 0,
            'staff_mapped' => 0,
            'skipped' => 0,
            'errors' => 0
        );

        if (!$this->ensure_attendance_logs_table() || !$this->ensure_student_map_table() || !$this->ensure_staff_map_table()) {
            $stats['errors']++;
            return $stats;
        }

        try {
            $query = $this->db->select('id, admission_no, machine_user_id, timestamp')
                              ->from('attendance_logs')
                              ->where('DATE(timestamp)', $date)
                              ->group_start()
                              ->where('admission_no IS NULL', null, false)
                              ->or_where('admission_no', '')
                              ->or_like('admission_no', 'UNMAPPED_', 'after')
                              ->group_end()
                              ->order_by('id', 'ASC')
                              ->get();

            $rows = $query ? $query->result_array() : array();
            $stats['scanned'] = count($rows);

            foreach ($rows as $row) {
                try {
                    $machine_key = $this->extract_machine_key($row['machine_user_id'], $row['admission_no']);
                    if ($machine_key === '') {
                        $stats['skipped']++;
                        continue;
                    }

                    $resolved_identity = $this->resolve_identity_for_machine_key($machine_key);
                    if (!$resolved_identity) {
                        $stats['skipped']++;
                        continue;
                    }

                    if ($resolved_identity['type'] === 'staff') {
                        $staff = $this->get_staff_by_id($resolved_identity['staff_id']);
                        $this->save_staff_mapping(array(
                            'machine_user_id' => $machine_key,
                            'staff_id' => $resolved_identity['staff_id'],
                            'staff_name' => $staff ? trim($staff->name . ' ' . $staff->surname) : null
                        ));

                        $updated_count = $this->apply_staff_mapping_to_logs_count($machine_key, (int) $resolved_identity['staff_id']);
                        $updated = ($updated_count !== false);
                    } else {
                        $resolved_admission = $resolved_identity['admission_no'];
                        $student = $this->get_student_by_admission($resolved_admission);
                        $mapping_payload = array(
                            'machine_user_id' => $machine_key,
                            'admission_no' => $resolved_admission,
                            'student_name' => $student ? trim($student->firstname . ' ' . $student->lastname) : null
                        );
                        $this->save_student_mapping($mapping_payload);

                        $updated_count = $this->apply_mapping_to_logs_count($machine_key, $resolved_admission);
                        $updated = ($updated_count !== false);
                    }

                    if ($updated) {
                        $stats['mapped'] += max(1, (int) $updated_count);
                        if ($resolved_identity['type'] === 'staff') {
                            $stats['staff_mapped'] += max(1, (int) $updated_count);
                            $this->sync_staff_attendance_from_logs((int) $resolved_identity['staff_id'], $date);
                        } else {
                            $stats['student_mapped'] += max(1, (int) $updated_count);
                        }
                    } else {
                        $stats['errors']++;
                        $this->capture_zkteco_error(
                            'auto_map_update_failed',
                            'Failed to update attendance_logs row while auto mapping',
                            array('log_id' => $row['id'], 'machine_key' => $machine_key, 'identity' => $resolved_identity)
                        );
                    }
                } catch (Exception $e) {
                    $stats['errors']++;
                    $this->capture_zkteco_error(
                        'auto_map_row_exception',
                        $e->getMessage(),
                        array('log_id' => isset($row['id']) ? $row['id'] : null)
                    );
                }
            }
        } catch (Exception $e) {
            $stats['errors']++;
            $this->capture_zkteco_error('auto_map_query_exception', $e->getMessage(), array('date' => $date));
        }

        return $stats;
    }
    
    /**
     * Delete student mapping
     * 
     * @param int $id Mapping ID
     * @return bool Success status
     */
    public function delete_student_mapping($id) {
        $id = (int) $id;
        if ($id <= 0) {
            $this->set_last_error('Invalid student mapping ID');
            return false;
        }

        if (!$this->ensure_student_map_table()) {
            $this->set_last_error('zkteco_student_map table is missing and could not be created');
            return false;
        }

        $result = $this->db->delete('zkteco_student_map', array('id' => $id));
        if (!$result) {
            $error = $this->db->error();
            $this->set_last_error(!empty($error['message']) ? $error['message'] : 'Failed to delete student mapping');
        }

        return $result;
    }

    /**
     * Delete staff mapping.
     *
     * @param int $id Mapping ID
     * @return bool
     */
    public function delete_staff_mapping($id) {
        $id = (int) $id;
        if ($id <= 0) {
            $this->set_last_error('Invalid staff mapping ID');
            return false;
        }

        if (!$this->ensure_staff_map_table()) {
            $this->set_last_error('zkteco_staff_map table is missing and could not be created');
            return false;
        }

        $result = $this->db->delete('zkteco_staff_map', array('id' => $id));
        if (!$result) {
            $error = $this->db->error();
            $this->set_last_error(!empty($error['message']) ? $error['message'] : 'Failed to delete staff mapping');
        }

        return $result;
    }
    
    /**
     * Get ZKTeco devices
     * 
     * @param int $device_id Optional device ID
     * @param bool $active_only Get only active devices
     * @return array|object Device records
     */
    public function get_devices($device_id = null, $active_only = true) {
        if (!$this->ensure_devices_table()) {
            return $device_id ? null : array();
        }

        $this->db->select('*')->from('zkteco_devices');
        
        if ($active_only) {
            $this->db->where('is_active', 1);
        }
        
        if ($device_id) {
            $this->db->where('id', $device_id);
            $query = $this->db->get();
            return $query->row();
        }
        
        $query = $this->db->get();
        return $query->result_array();
    }
    
    /**
     * Save or update device configuration
     * 
     * @param array $data Device data
     * @return int|bool Device ID or false on failure
     */
    public function save_device($data) {
        try {
            $this->last_error = '';

            if (!$this->ensure_devices_table()) {
                $this->set_last_error('zkteco_devices table is missing and could not be created');
                return false;
            }

            $data = is_array($data) ? $data : array();
            if (empty($data['device_name']) || empty($data['device_ip'])) {
                $this->set_last_error('Device name and device IP are required');
                return false;
            }

            if (isset($data['id']) && $data['id'] > 0) {
                // Update existing device
                $id = $data['id'];
                unset($data['id']);
                $this->db->where('id', $id);
                $result = $this->db_update_safely('zkteco_devices', $data);
                if (!$result && $this->last_error === '') {
                    $this->set_last_error('Failed to update device');
                    return false;
                }
                return $id;
            } else {
                // Insert new device
                unset($data['id']);
                $result = $this->db_insert_safely('zkteco_devices', $data);
                if (!$result && $this->last_error === '') {
                    $this->set_last_error('Failed to insert device');
                    return false;
                }
                return $this->db->insert_id();
            }
        } catch (Exception $e) {
            $this->set_last_error($e->getMessage());
            return false;
        }
    }

    /**
     * Get staff mapping (machine user ID to staff ID).
     *
     * @param string $machine_user_id Optional machine user ID
     * @return array|object|null
     */
    public function get_staff_mapping($machine_user_id = null) {
        if (!$this->ensure_staff_map_table()) {
            return $machine_user_id ? null : array();
        }

        $this->db->select('zkteco_staff_map.*, staff.employee_id, staff.name, staff.surname, roles.name as staff_role')
                 ->from('zkteco_staff_map')
                 ->join('staff', 'staff.id = zkteco_staff_map.staff_id', 'left')
                 ->join('staff_roles sr', 'sr.staff_id = staff.id', 'left')
                 ->join('roles', 'roles.id = sr.role_id', 'left');

        if ($machine_user_id) {
            $this->db->where('zkteco_staff_map.machine_user_id', $machine_user_id);
            $query = $this->db->get();
            return $query ? $query->row() : null;
        }

        $query = $this->db->get();
        return $query ? $query->result_array() : array();
    }

    /**
     * Get all staff mappings as associative array.
     *
     * @return array Array with machine_user_id as key and staff_id as value
     */
    public function get_staff_mapping_array() {
        if (!$this->ensure_staff_map_table()) {
            return array();
        }

        $query = $this->db->select('machine_user_id, staff_id')
                          ->from('zkteco_staff_map')
                          ->get();

        $mapping = array();
        foreach ($query->result_array() as $row) {
            $mapping[$row['machine_user_id']] = (int) $row['staff_id'];
        }

        return $mapping;
    }

    /**
     * Save or update staff mapping.
     *
     * @param array $data Mapping data
     * @return bool
     */
    public function save_staff_mapping($data) {
        try {
            $this->last_error = '';

            if (!$this->ensure_staff_map_table()) {
                $this->set_last_error('zkteco_staff_map table is missing and could not be created');
                return false;
            }

            $payload = array(
                'machine_user_id' => trim((string) $data['machine_user_id']),
                'staff_id' => (int) $data['staff_id'],
                'staff_name' => isset($data['staff_name']) ? $data['staff_name'] : null,
            );

            if ($this->ensure_student_map_table()) {
                $this->db->delete('zkteco_student_map', array('machine_user_id' => $payload['machine_user_id']));
            }

            $exists = $this->db->select('id')
                               ->from('zkteco_staff_map')
                               ->where('machine_user_id', $payload['machine_user_id'])
                               ->get()
                               ->row();

            if ($exists) {
                $this->db->where('id', $exists->id);
                return $this->db_update_safely('zkteco_staff_map', $payload);
            }

            return $this->db_insert_safely('zkteco_staff_map', $payload);
        } catch (Exception $e) {
            log_message('error', 'Attendance Model: Error saving staff mapping - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update device last sync time
     * 
     * @param int $device_id Device ID
     * @return bool Success status
     */
    public function update_device_sync($device_id) {
        if (!$this->ensure_devices_table()) {
            return false;
        }

        $data = array('last_sync' => date('Y-m-d H:i:s'));
        $this->db->where('id', $device_id);
        return $this->db->update('zkteco_devices', $data);
    }
    
    /**
     * Initialize import progress tracking
     * 
     * @param string $session_id Session ID
     * @return bool Success status
     */
    public function init_import_progress($session_id) {
        try {
            if (empty($session_id)) {
                log_message('error', 'Attendance Model: Empty session ID provided');
                return false;
            }

            if (!$this->ensure_import_progress_table()) {
                log_message('error', 'Attendance Model: Failed to ensure zkteco_import_progress table exists');
                return false;
            }

            $fields = $this->get_import_progress_fields();
            if (empty($fields)) {
                log_message('error', 'Attendance Model: Could not read zkteco_import_progress fields');
                return false;
            }

            $now = date('Y-m-d H:i:s');
            $data = array(
                'session_id' => $session_id,
                'progress' => 0,
                'status' => 'Initializing',
                'total_records' => 0,
                'processed_records' => 0,
                'new_records' => 0,
                'duplicate_records' => 0,
                'error_message' => null
            );

            if (in_array('started_at', $fields, true)) {
                $data['started_at'] = $now;
            } elseif (in_array('created_at', $fields, true)) {
                $data['created_at'] = $now;
            }
            if (in_array('updated_at', $fields, true)) {
                $data['updated_at'] = $now;
            }

            $data = array_intersect_key($data, array_flip($fields));
            
            // Delete old progress for this session if exists
            try {
                $this->db->delete('zkteco_import_progress', array('session_id' => $session_id));
            } catch (Exception $e) {
                // Use 'error' level to avoid undefined 'warning' level in CI
                log_message('error', 'Attendance Model: Error deleting old progress - ' . $e->getMessage());
            }
            
            $result = $this->db->insert('zkteco_import_progress', $data);
            
            if (!$result) {
                log_message('error', 'Attendance Model: Failed to initialize progress - ' . print_r($this->db->error(), true));
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Attendance Model: Exception in init_import_progress - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update import progress
     * 
     * @param string $session_id Session ID
     * @param array $data Progress data
     * @return bool Success status
     */
    public function update_import_progress($session_id, $data) {
        try {
            if (empty($session_id)) {
                return false;
            }

            if (!$this->ensure_import_progress_table()) {
                return false;
            }

            $fields = $this->get_import_progress_fields();
            if (empty($fields)) {
                return false;
            }
            
            // Add updated timestamp
            if (in_array('updated_at', $fields, true)) {
                $data['updated_at'] = date('Y-m-d H:i:s');
            }

            $data = array_intersect_key($data, array_flip($fields));
            if (empty($data)) {
                return false;
            }
            
            $this->db->where('session_id', $session_id);
            $result = $this->db->update('zkteco_import_progress', $data);
            
            if (!$result) {
                log_message('error', 'Attendance Model: Failed to update progress - ' . print_r($this->db->error(), true));
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'Attendance Model: Exception in update_import_progress - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get import progress
     * 
     * @param string $session_id Session ID
     * @return object|null Progress data
     */
    public function get_import_progress($session_id) {
        try {
            if (empty($session_id)) {
                return null;
            }

            if (!$this->ensure_import_progress_table()) {
                return null;
            }
            
            $query = $this->db->select('*')
                              ->from('zkteco_import_progress')
                              ->where('session_id', $session_id)
                              ->limit(1)
                              ->get();
            
            if ($query && $query->num_rows() > 0) {
                return $query->row();
            }
            
            return null;
            
        } catch (Exception $e) {
            log_message('error', 'Attendance Model: Exception in get_import_progress - ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Clean up old import progress records (older than 24 hours)
     * 
     * @return bool Success status
     */
    public function cleanup_old_progress() {
        if (!$this->ensure_import_progress_table()) {
            return false;
        }

        $fields = $this->get_import_progress_fields();
        if (empty($fields)) {
            return false;
        }

        $cutoff = date('Y-m-d H:i:s', strtotime('-24 hours'));
        if (in_array('started_at', $fields, true)) {
            $this->db->where('started_at <', $cutoff);
        } elseif (in_array('created_at', $fields, true)) {
            $this->db->where('created_at <', $cutoff);
        } else {
            return false;
        }

        return $this->db->delete('zkteco_import_progress');
    }

    /**
     * Get student by admission number with class/section context.
     *
     * @param string $admission_no
     * @return object|null
     */
    public function get_student_by_admission($admission_no) {
        if (empty($admission_no)) {
            return null;
        }

        $query = $this->db->select('s.admission_no, s.firstname, s.lastname, c.class, sec.section')
                          ->from('students s')
                          ->join('student_session ss', 'ss.student_id = s.id', 'left')
                          ->join('classes c', 'c.id = ss.class_id', 'left')
                          ->join('sections sec', 'sec.id = ss.section_id', 'left')
                          ->where('s.admission_no', $admission_no)
                          ->group_start()
                          ->where('ss.session_id', $this->current_session)
                          ->or_where('ss.session_id IS NULL', null, false)
                          ->group_end()
                          ->limit(1)
                          ->get();

        return $query->num_rows() ? $query->row() : null;
    }

    /**
     * Get staff by local staff ID.
     *
     * @param int $staff_id
     * @return object|null
     */
    public function get_staff_by_id($staff_id) {
        $staff_id = (int) $staff_id;
        if ($staff_id <= 0) {
            return null;
        }

        $query = $this->db->select('staff.id, staff.employee_id, staff.name, staff.surname, roles.name as staff_role')
                          ->from('staff')
                          ->join('staff_roles sr', 'sr.staff_id = staff.id', 'left')
                          ->join('roles', 'roles.id = sr.role_id', 'left')
                          ->where('staff.id', $staff_id)
                          ->where('staff.is_active', 1)
                          ->limit(1)
                          ->get();

        return ($query && $query->num_rows()) ? $query->row() : null;
    }

    /**
     * Update existing attendance rows after machine-user mapping.
     *
     * @param string $machine_user_id
     * @param string $admission_no
     * @return bool
     */
    public function apply_mapping_to_logs($machine_user_id, $admission_no) {
        $updated = $this->apply_mapping_to_logs_count($machine_user_id, $admission_no);
        return ($updated !== false);
    }

    /**
     * Update existing attendance rows after machine-user mapping and return count.
     *
     * @param string $machine_user_id
     * @param string $admission_no
     * @return int|false
     */
    public function apply_mapping_to_logs_count($machine_user_id, $admission_no) {
        if (empty($machine_user_id) || empty($admission_no)) {
            return false;
        }

        $this->db->set('admission_no', $admission_no)
                 ->set('staff_id', null)
                 ->set('user_type', 'student')
                 ->where('machine_user_id', $machine_user_id);
        if (!$this->db->update('attendance_logs')) {
            return false;
        }
        return (int) $this->db->affected_rows();
    }

    /**
     * Update existing attendance rows after staff machine-user mapping and return count.
     *
     * @param string $machine_user_id
     * @param int $staff_id
     * @return int|false
     */
    public function apply_staff_mapping_to_logs_count($machine_user_id, $staff_id) {
        $machine_user_id = trim((string) $machine_user_id);
        $staff_id = (int) $staff_id;
        if ($machine_user_id === '' || $staff_id <= 0) {
            return false;
        }

        $this->db->set('admission_no', 'STAFF_' . $staff_id)
                 ->set('staff_id', $staff_id)
                 ->set('user_type', 'staff')
                 ->where('machine_user_id', $machine_user_id);

        if (!$this->db->update('attendance_logs')) {
            return false;
        }

        $updated = (int) $this->db->affected_rows();
        $this->sync_staff_attendance_from_logs($staff_id);
        return $updated;
    }

    /**
     * List staff for quick mapping dropdown.
     *
     * @return array
     */
    public function get_staff_for_mapping() {
        $query = $this->db->select('staff.id, staff.employee_id, staff.name, staff.surname, roles.name as staff_role')
                          ->from('staff')
                          ->join('staff_roles sr', 'sr.staff_id = staff.id', 'left')
                          ->join('roles', 'roles.id = sr.role_id', 'left')
                          ->where('staff.is_active', 1)
                          ->order_by('staff.name', 'ASC')
                          ->limit(5000)
                          ->get();

        return $query ? $query->result_array() : array();
    }

    private function sync_staff_attendance_for_log_records($records) {
        if (empty($records) || !is_array($records)) {
            return;
        }

        $seen = array();
        foreach ($records as $record) {
            $staff_id = isset($record['staff_id']) ? (int) $record['staff_id'] : 0;
            if ($staff_id <= 0 || empty($record['timestamp'])) {
                continue;
            }

            $date = date('Y-m-d', strtotime($record['timestamp']));
            $key = $staff_id . '|' . $date;
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $this->mark_staff_attendance_present($staff_id, $date);
        }
    }

    public function sync_staff_attendance_from_logs($staff_id, $date = null) {
        $staff_id = (int) $staff_id;
        if ($staff_id <= 0 || !$this->ensure_attendance_logs_table()) {
            return 0;
        }

        $this->db->select('DATE(timestamp) as attendance_date', false)
                 ->from('attendance_logs')
                 ->where('staff_id', $staff_id)
                 ->where('user_type', 'staff')
                 ->group_by('DATE(timestamp)');
        if (!empty($date)) {
            $this->db->where('DATE(timestamp)', $date);
        }

        $query = $this->db->get();
        $rows = $query ? $query->result_array() : array();
        $synced = 0;
        foreach ($rows as $row) {
            if (!empty($row['attendance_date']) && $this->mark_staff_attendance_present($staff_id, $row['attendance_date'])) {
                $synced++;
            }
        }

        return $synced;
    }

    private function mark_staff_attendance_present($staff_id, $date) {
        $staff_id = (int) $staff_id;
        if ($staff_id <= 0 || empty($date) || !$this->db->table_exists('staff_attendance')) {
            return false;
        }

        $present_type_id = 1;
        if ($this->db->table_exists('staff_attendance_type')) {
            $present_type = $this->db->select('id')
                                     ->from('staff_attendance_type')
                                     ->where('is_active', 'yes')
                                     ->group_start()
                                     ->where('id', 1)
                                     ->or_where("LOWER(type) = 'present'", null, false)
                                     ->group_end()
                                     ->order_by('id', 'ASC')
                                     ->limit(1)
                                     ->get()
                                     ->row();
            if ($present_type && !empty($present_type->id)) {
                $present_type_id = (int) $present_type->id;
            }
        }

        $fields = $this->db->list_fields('staff_attendance');
        $existing = $this->db->select('id, staff_attendance_type_id')
                             ->from('staff_attendance')
                             ->where('staff_id', $staff_id)
                             ->where('date', $date)
                             ->limit(1)
                             ->get()
                             ->row();

        $data = array(
            'staff_id' => $staff_id,
            'staff_attendance_type_id' => $present_type_id,
            'date' => $date,
            'remark' => 'ZKTeco auto attendance'
        );
        if (in_array('created_at', $fields, true)) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        $data = array_intersect_key($data, array_flip($fields));

        if ($existing) {
            if ((int) $existing->staff_attendance_type_id === 5) {
                return true;
            }
            $this->db->where('id', $existing->id);
            return $this->db->update('staff_attendance', $data);
        }

        return $this->db->insert('staff_attendance', $data);
    }

    /**
     * List students for quick mapping dropdown.
     *
     * @return array
     */
    public function get_students_for_mapping() {
        $query = $this->db->select('s.admission_no, s.roll_no, s.firstname, s.lastname, c.class, sec.section')
                          ->from('students s')
                          ->join('student_session ss', 'ss.student_id = s.id', 'inner')
                          ->join('classes c', 'c.id = ss.class_id', 'left')
                          ->join('sections sec', 'sec.id = ss.section_id', 'left')
                          ->where('ss.session_id', $this->current_session)
                          ->order_by('s.firstname', 'ASC')
                          ->limit(5000)
                          ->get();

        return $query->result_array();
    }

    /**
     * Capture integration errors in DB and file logs for troubleshooting.
     *
     * @param string $context
     * @param string $message
     * @param array $payload
     * @return void
     */
    public function capture_zkteco_error($context, $message, $payload = array()) {
        $safe_context = substr((string) $context, 0, 100);
        $safe_message = substr((string) $message, 0, 1000);
        $safe_payload = is_array($payload) ? json_encode($payload) : (string) $payload;
        if ($safe_payload === false) {
            $safe_payload = null;
        }

        log_message('error', 'ZKTecoCapture[' . $safe_context . ']: ' . $safe_message . ' | payload=' . $safe_payload);

        try {
            if (!$this->ensure_zkteco_error_table()) {
                return;
            }

            $this->db->insert('zkteco_error_capture', array(
                'context' => $safe_context,
                'error_message' => $safe_message,
                'payload' => $safe_payload,
                'created_at' => date('Y-m-d H:i:s')
            ));
        } catch (Exception $e) {
            log_message('error', 'ZKTecoCapture[db_insert_failed]: ' . $e->getMessage());
        }
    }

    private function ensure_import_progress_table() {
        if ($this->db->table_exists('zkteco_import_progress')) {
            return true;
        }

        $sql = "CREATE TABLE IF NOT EXISTS `zkteco_import_progress` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `session_id` VARCHAR(100) NOT NULL,
            `progress` INT(11) NOT NULL DEFAULT 0,
            `status` VARCHAR(50) NOT NULL DEFAULT 'Initializing',
            `total_records` INT(11) DEFAULT 0,
            `processed_records` INT(11) DEFAULT 0,
            `new_records` INT(11) DEFAULT 0,
            `duplicate_records` INT(11) DEFAULT 0,
            `error_message` TEXT DEFAULT NULL,
            `started_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_session` (`session_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

        $result = $this->db->query($sql);
        if ($result) {
            $this->import_progress_fields = null;
        }

        return $result ? true : false;
    }

    private function get_import_progress_fields() {
        if ($this->import_progress_fields !== null) {
            return $this->import_progress_fields;
        }

        if (!$this->db->table_exists('zkteco_import_progress')) {
            $this->import_progress_fields = array();
            return $this->import_progress_fields;
        }

        $this->import_progress_fields = $this->db->list_fields('zkteco_import_progress');
        return $this->import_progress_fields;
    }

    private function get_attendance_log_fields() {
        if ($this->attendance_log_fields !== null) {
            return $this->attendance_log_fields;
        }

        if (!$this->ensure_attendance_logs_table()) {
            $this->attendance_log_fields = array();
            return $this->attendance_log_fields;
        }

        $this->attendance_log_fields = $this->db->list_fields('attendance_logs');
        return $this->attendance_log_fields;
    }
    
    /**
     * Get attendance statistics for dashboard
     * 
     * @param string $date Date in Y-m-d format
     * @return array Statistics
     */
    public function get_attendance_stats($date = null) {
        if (!$date) {
            $date = date('Y-m-d');
        }

        if (!$this->ensure_attendance_logs_table()) {
            return array(
                'total_records' => 0,
                'unique_students' => 0,
                'status_breakdown' => array(),
                'date' => $date
            );
        }
        
        // Total attendance records for the date
        $total = $this->db->where('DATE(timestamp)', $date)
                          ->count_all_results('attendance_logs');
        
        // Unique students present
        $this->db->select('DISTINCT admission_no');
        $this->db->from('attendance_logs');
        $this->db->where('DATE(timestamp)', $date);
        $unique = $this->db->count_all_results();
        
        // Get status breakdown
        $this->db->select('status, COUNT(*) as count');
        $this->db->from('attendance_logs');
        $this->db->where('DATE(timestamp)', $date);
        $this->db->group_by('status');
        $query = $this->db->get();
        $status_breakdown = $query->result_array();
        
        return array(
            'total_records' => $total,
            'unique_students' => $unique,
            'status_breakdown' => $status_breakdown,
            'date' => $date
        );
    }
    
    /**
     * Get unmapped machine users (users in device but not in mapping table)
     * 
     * @return array Unmapped users
     */
    public function get_unmapped_users($limit = null, $offset = 0) {
        if (!$this->ensure_attendance_logs_table()) {
            return array();
        }

        $this->db->select('machine_user_id, COUNT(*) AS total_logs, MIN(timestamp) AS first_seen, MAX(timestamp) AS last_seen')
                 ->from('attendance_logs')
                 ->where('machine_user_id IS NOT NULL', null, false)
                 ->where("TRIM(machine_user_id) <> ''", null, false)
                 ->group_start()
                 ->where('admission_no IS NULL', null, false)
                 ->or_where('admission_no', '')
                 ->or_like('admission_no', 'UNMAPPED_', 'after')
                 ->group_end()
                 ->group_by('machine_user_id')
                 ->order_by('last_seen', 'DESC');

        if ($limit !== null) {
            $this->db->limit((int) $limit, max(0, (int) $offset));
        }

        $query = $this->db->get();
        return $query ? $query->result_array() : array();
    }

    /**
     * Count distinct unmapped machine users for pagination.
     *
     * @return int
     */
    public function count_unmapped_users() {
        if (!$this->ensure_attendance_logs_table()) {
            return 0;
        }

        $query = $this->db->select('COUNT(DISTINCT machine_user_id) AS total', false)
                          ->from('attendance_logs')
                          ->where('machine_user_id IS NOT NULL', null, false)
                          ->where("TRIM(machine_user_id) <> ''", null, false)
                          ->group_start()
                          ->where('admission_no IS NULL', null, false)
                          ->or_where('admission_no', '')
                          ->or_like('admission_no', 'UNMAPPED_', 'after')
                          ->group_end()
                          ->get();

        if (!$query || !$query->num_rows()) {
            return 0;
        }

        return (int) $query->row()->total;
    }

    /**
     * Save multiple mappings with robust per-row validation and log backfill.
     *
     * @param array $mappings
     * @return array
     */
    public function bulk_save_student_mappings($mappings) {
        $summary = array(
            'total' => is_array($mappings) ? count($mappings) : 0,
            'saved' => 0,
            'updated_logs' => 0,
            'failed' => 0,
            'errors' => array(),
        );

        if (empty($mappings) || !is_array($mappings)) {
            return $summary;
        }

        foreach ($mappings as $idx => $row) {
            $row_no = $idx + 1;
            $machine_user_id = isset($row['machine_user_id']) ? trim((string) $row['machine_user_id']) : '';
            $admission_no = isset($row['admission_no']) ? trim((string) $row['admission_no']) : '';

            if ($machine_user_id === '' || $admission_no === '') {
                $summary['failed']++;
                $summary['errors'][] = 'Row ' . $row_no . ': Missing machine user ID or admission number';
                continue;
            }

            try {
                $student = $this->get_student_by_admission($admission_no);
                if (!$student) {
                    $summary['failed']++;
                    $summary['errors'][] = 'Row ' . $row_no . ': Student not found (' . $admission_no . ')';
                    continue;
                }

                $mapping_data = array(
                    'machine_user_id' => $machine_user_id,
                    'admission_no' => $admission_no,
                    'student_name' => trim($student->firstname . ' ' . $student->lastname)
                );

                $saved = $this->save_student_mapping($mapping_data);
                if (!$saved) {
                    $summary['failed']++;
                    $summary['errors'][] = 'Row ' . $row_no . ': Failed to save mapping for machine user ' . $machine_user_id;
                    continue;
                }

                $updated_count = $this->apply_mapping_to_logs_count($machine_user_id, $admission_no);
                if ($updated_count === false) {
                    $summary['failed']++;
                    $summary['errors'][] = 'Row ' . $row_no . ': Mapping saved but failed to backfill logs for machine user ' . $machine_user_id;
                    continue;
                }

                $summary['saved']++;
                $summary['updated_logs'] += (int) $updated_count;
            } catch (Throwable $e) {
                $summary['failed']++;
                $summary['errors'][] = 'Row ' . $row_no . ': ' . $e->getMessage();
                log_message('error', 'Attendance Model: bulk_save_student_mappings row error - ' . $e->getMessage());
            }
        }

        return $summary;
    }

    /**
     * Save multiple student/staff mappings with log backfill.
     *
     * @param array $mappings
     * @return array
     */
    public function bulk_save_attendance_mappings($mappings) {
        $summary = array(
            'total' => is_array($mappings) ? count($mappings) : 0,
            'saved' => 0,
            'saved_students' => 0,
            'saved_staff' => 0,
            'updated_logs' => 0,
            'failed' => 0,
            'errors' => array(),
        );

        if (empty($mappings) || !is_array($mappings)) {
            return $summary;
        }

        foreach ($mappings as $idx => $row) {
            $row_no = $idx + 1;
            $machine_user_id = isset($row['machine_user_id']) ? trim((string) $row['machine_user_id']) : '';
            $target_type = isset($row['target_type']) ? trim((string) $row['target_type']) : 'student';
            if ($target_type !== 'staff') {
                $target_type = 'student';
            }

            if ($machine_user_id === '') {
                $summary['failed']++;
                $summary['errors'][] = 'Row ' . $row_no . ': Missing machine user ID';
                continue;
            }

            try {
                if ($target_type === 'staff') {
                    $staff_id = isset($row['staff_id']) ? (int) $row['staff_id'] : 0;
                    if ($staff_id <= 0) {
                        $summary['failed']++;
                        $summary['errors'][] = 'Row ' . $row_no . ': Missing staff member';
                        continue;
                    }

                    $staff = $this->get_staff_by_id($staff_id);
                    if (!$staff) {
                        $summary['failed']++;
                        $summary['errors'][] = 'Row ' . $row_no . ': Staff member not found (' . $staff_id . ')';
                        continue;
                    }

                    $saved = $this->save_staff_mapping(array(
                        'machine_user_id' => $machine_user_id,
                        'staff_id' => $staff_id,
                        'staff_name' => trim($staff->name . ' ' . $staff->surname)
                    ));
                    if (!$saved) {
                        $summary['failed']++;
                        $summary['errors'][] = 'Row ' . $row_no . ': Failed to save staff mapping for machine user ' . $machine_user_id;
                        continue;
                    }

                    $updated_count = $this->apply_staff_mapping_to_logs_count($machine_user_id, $staff_id);
                    if ($updated_count === false) {
                        $summary['failed']++;
                        $summary['errors'][] = 'Row ' . $row_no . ': Staff mapping saved but failed to backfill logs for machine user ' . $machine_user_id;
                        continue;
                    }

                    $summary['saved']++;
                    $summary['saved_staff']++;
                    $summary['updated_logs'] += (int) $updated_count;
                    continue;
                }

                $admission_no = isset($row['admission_no']) ? trim((string) $row['admission_no']) : '';
                if ($admission_no === '') {
                    $summary['failed']++;
                    $summary['errors'][] = 'Row ' . $row_no . ': Missing admission number';
                    continue;
                }

                $student = $this->get_student_by_admission($admission_no);
                if (!$student) {
                    $summary['failed']++;
                    $summary['errors'][] = 'Row ' . $row_no . ': Student not found (' . $admission_no . ')';
                    continue;
                }

                $saved = $this->save_student_mapping(array(
                    'machine_user_id' => $machine_user_id,
                    'admission_no' => $admission_no,
                    'student_name' => trim($student->firstname . ' ' . $student->lastname)
                ));
                if (!$saved) {
                    $summary['failed']++;
                    $summary['errors'][] = 'Row ' . $row_no . ': Failed to save mapping for machine user ' . $machine_user_id;
                    continue;
                }

                $updated_count = $this->apply_mapping_to_logs_count($machine_user_id, $admission_no);
                if ($updated_count === false) {
                    $summary['failed']++;
                    $summary['errors'][] = 'Row ' . $row_no . ': Mapping saved but failed to backfill logs for machine user ' . $machine_user_id;
                    continue;
                }

                $summary['saved']++;
                $summary['saved_students']++;
                $summary['updated_logs'] += (int) $updated_count;
            } catch (Throwable $e) {
                $summary['failed']++;
                $summary['errors'][] = 'Row ' . $row_no . ': ' . $e->getMessage();
                log_message('error', 'Attendance Model: bulk_save_attendance_mappings row error - ' . $e->getMessage());
            }
        }

        return $summary;
    }
    
    /**
     * Get students without machine mapping
     * 
     * @return array Students
     */
    public function get_students_without_mapping() {
        if (!$this->ensure_student_map_table()) {
            return array();
        }

        $this->db->select('s.id, s.admission_no, s.firstname, s.lastname, c.class, sec.section')
                 ->from('students s')
                 ->join('student_session ss', 'ss.student_id = s.id', 'inner')
                 ->join('classes c', 'c.id = ss.class_id', 'left')
                 ->join('sections sec', 'sec.id = ss.section_id', 'left')
                 ->where('ss.session_id', $this->current_session)
                 ->where('s.is_active', 'yes')
                 ->where('s.admission_no NOT IN (SELECT admission_no FROM zkteco_student_map)', null, false)
                 ->order_by('s.admission_no', 'ASC');
        
        $query = $this->db->get();
        return $query->result_array();
    }

    /**
     * Normalize status values using machine sequence per day.
     * Keeps explicit Check In/Out values and infers unknown ones.
     *
     * @param array $logs
     * @return array
     */
    private function normalize_statuses_from_machine($logs) {
        if (empty($logs)) {
            return $logs;
        }

        // Group by machine user + date for sequence-based inference.
        $groups = array();
        foreach ($logs as $idx => $row) {
            $machine_user_id = isset($row['machine_user_id']) ? (string) $row['machine_user_id'] : '';
            $date_key = !empty($row['timestamp']) ? date('Y-m-d', strtotime($row['timestamp'])) : 'unknown';
            $group_key = $machine_user_id . '|' . $date_key;
            if (!isset($groups[$group_key])) {
                $groups[$group_key] = array();
            }
            $groups[$group_key][] = $idx;
        }

        foreach ($groups as $group_indexes) {
            usort($group_indexes, function($a, $b) use ($logs) {
                return strtotime($logs[$a]['timestamp']) <=> strtotime($logs[$b]['timestamp']);
            });

            $first_idx = $group_indexes[0];
            $last_idx = $group_indexes[count($group_indexes) - 1];

            foreach ($group_indexes as $idx) {
                $raw_status = isset($logs[$idx]['status']) ? trim((string) $logs[$idx]['status']) : '';
                $normalized = strtoupper(str_replace(array(' ', '/', '-', '_'), '', $raw_status));

                if (in_array($normalized, array('CHECKIN', 'CIN', 'IN'), true)) {
                    $logs[$idx]['status'] = 'Check In';
                    continue;
                }
                if (in_array($normalized, array('CHECKOUT', 'COUT', 'OUT'), true)) {
                    $logs[$idx]['status'] = 'Check Out';
                    continue;
                }
                if (in_array($raw_status, array('Break In', 'Break Out', 'Overtime In', 'Overtime Out'), true)) {
                    continue;
                }

                // Infer unknown statuses from first/last punch of the day.
                if ($idx === $first_idx) {
                    $logs[$idx]['status'] = 'Check In';
                } elseif ($idx === $last_idx) {
                    $logs[$idx]['status'] = 'Check Out';
                } else {
                    $logs[$idx]['status'] = 'Present';
                }
            }
        }

        return $logs;
    }

    private function extract_machine_key($machine_user_id, $admission_no) {
        $machine_user_id = trim((string) $machine_user_id);
        if ($machine_user_id !== '') {
            return $machine_user_id;
        }

        $admission_no = trim((string) $admission_no);
        if (strpos($admission_no, 'UNMAPPED_') === 0) {
            return substr($admission_no, 9);
        }

        return '';
    }

    private function resolve_admission_for_machine_key($machine_key) {
        $machine_key = trim((string) $machine_key);
        if ($machine_key === '') {
            return null;
        }

        if (!$this->ensure_student_map_table()) {
            return null;
        }

        // Rule 1: existing explicit mapping table
        $map = $this->db->select('admission_no')
                        ->from('zkteco_student_map')
                        ->where('machine_user_id', $machine_key)
                        ->limit(1)
                        ->get()
                        ->row();
        if ($map && !empty($map->admission_no)) {
            return $map->admission_no;
        }

        // Rule 2: machine key equals admission number
        $student = $this->db->select('s.admission_no')
                            ->from('students s')
                            ->where('s.admission_no', $machine_key)
                            ->limit(1)
                            ->get()
                            ->row();
        if ($student && !empty($student->admission_no)) {
            return $student->admission_no;
        }

        // Rule 3: machine key equals roll_no in current session
        $student_by_roll = $this->db->select('s.admission_no')
                                    ->from('students s')
                                    ->join('student_session ss', 'ss.student_id = s.id', 'inner')
                                    ->where('ss.session_id', $this->current_session)
                                    ->where('s.roll_no', $machine_key)
                                    ->limit(1)
                                    ->get()
                                    ->row();
        if ($student_by_roll && !empty($student_by_roll->admission_no)) {
            return $student_by_roll->admission_no;
        }

        return null;
    }

    private function resolve_identity_for_machine_key($machine_key) {
        $admission_no = $this->resolve_admission_for_machine_key($machine_key);
        if ($admission_no) {
            return array('type' => 'student', 'admission_no' => $admission_no);
        }

        if (!$this->ensure_staff_map_table()) {
            return null;
        }

        $map = $this->db->select('staff_id')
                        ->from('zkteco_staff_map')
                        ->where('machine_user_id', $machine_key)
                        ->limit(1)
                        ->get()
                        ->row();
        if ($map && (int) $map->staff_id > 0) {
            return array('type' => 'staff', 'staff_id' => (int) $map->staff_id);
        }

        $staff_by_employee = $this->db->select('id')
                                      ->from('staff')
                                      ->where('employee_id', $machine_key)
                                      ->where('is_active', 1)
                                      ->limit(1)
                                      ->get()
                                      ->row();
        if ($staff_by_employee && (int) $staff_by_employee->id > 0) {
            return array('type' => 'staff', 'staff_id' => (int) $staff_by_employee->id);
        }

        if (ctype_digit($machine_key)) {
            $staff_by_id = $this->db->select('id')
                                    ->from('staff')
                                    ->where('id', (int) $machine_key)
                                    ->where('is_active', 1)
                                    ->limit(1)
                                    ->get()
                                    ->row();
            if ($staff_by_id && (int) $staff_by_id->id > 0) {
                return array('type' => 'staff', 'staff_id' => (int) $staff_by_id->id);
            }
        }

        return null;
    }

    private function ensure_zkteco_error_table() {
        if ($this->zkteco_error_table_checked) {
            return true;
        }

        if (!$this->db->table_exists('zkteco_error_capture')) {
            $sql = "CREATE TABLE IF NOT EXISTS `zkteco_error_capture` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `context` VARCHAR(100) NOT NULL,
                `error_message` VARCHAR(1000) NOT NULL,
                `payload` TEXT DEFAULT NULL,
                `created_at` DATETIME NOT NULL,
                PRIMARY KEY (`id`),
                KEY `idx_context` (`context`),
                KEY `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $created = $this->db->query($sql);
            if (!$created) {
                return false;
            }
        }

        $this->zkteco_error_table_checked = true;
        return true;
    }

    private function ensure_zkteco_support_tables() {
        $this->ensure_attendance_logs_table();
        $this->ensure_student_map_table();
        $this->ensure_staff_map_table();
        $this->ensure_devices_table();
        $this->ensure_realtime_audit_table();
    }

    private function ensure_attendance_logs_table() {
        if ($this->attendance_logs_table_checked && $this->db->table_exists('attendance_logs')) {
            return true;
        }

        if (!$this->db->table_exists('attendance_logs')) {
            $sql = "CREATE TABLE IF NOT EXISTS `attendance_logs` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `admission_no` VARCHAR(100) DEFAULT NULL,
                `staff_id` INT(11) DEFAULT NULL,
                `user_type` VARCHAR(20) NOT NULL DEFAULT 'student',
                `timestamp` DATETIME NOT NULL,
                `status` VARCHAR(50) NOT NULL DEFAULT 'Present',
                `machine_user_id` VARCHAR(100) DEFAULT NULL,
                `device_id` VARCHAR(100) DEFAULT NULL,
                `created_at` DATETIME DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_admission_timestamp` (`admission_no`, `timestamp`),
                KEY `idx_timestamp` (`timestamp`),
                KEY `idx_machine_user_id` (`machine_user_id`),
                KEY `idx_staff_id` (`staff_id`),
                KEY `idx_user_type` (`user_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            if (!$this->run_db_query_safely($sql)) {
                return false;
            }
            $this->attendance_log_fields = null;
        }

        $fields = $this->db->list_fields('attendance_logs');
        if (!in_array('staff_id', $fields, true)) {
            $this->run_db_query_safely("ALTER TABLE `attendance_logs` ADD COLUMN `staff_id` INT(11) DEFAULT NULL AFTER `admission_no`");
            $this->run_db_query_safely("ALTER TABLE `attendance_logs` ADD KEY `idx_staff_id` (`staff_id`)");
            $this->attendance_log_fields = null;
        }
        if (!in_array('user_type', $fields, true)) {
            $this->run_db_query_safely("ALTER TABLE `attendance_logs` ADD COLUMN `user_type` VARCHAR(20) NOT NULL DEFAULT 'student' AFTER `staff_id`");
            $this->run_db_query_safely("ALTER TABLE `attendance_logs` ADD KEY `idx_user_type` (`user_type`)");
            $this->attendance_log_fields = null;
        }
        if (!in_array('sync_uid', $fields, true)) {
            $this->run_db_query_safely("ALTER TABLE `attendance_logs` ADD COLUMN `sync_uid` VARCHAR(191) DEFAULT NULL AFTER `device_id`");
            $this->run_db_query_safely("ALTER TABLE `attendance_logs` ADD UNIQUE KEY `uniq_sync_uid` (`sync_uid`)");
            $this->attendance_log_fields = null;
        }
        if (!in_array('source', $fields, true)) {
            $this->run_db_query_safely("ALTER TABLE `attendance_logs` ADD COLUMN `source` VARCHAR(50) NOT NULL DEFAULT 'manual_import' AFTER `sync_uid`");
            $this->attendance_log_fields = null;
        }

        $this->ensure_attendance_realtime_indexes();

        $this->attendance_logs_table_checked = true;
        return true;
    }

    private function ensure_student_map_table() {
        if ($this->student_map_table_checked && $this->db->table_exists('zkteco_student_map')) {
            return true;
        }

        if (!$this->db->table_exists('zkteco_student_map')) {
            $sql = "CREATE TABLE IF NOT EXISTS `zkteco_student_map` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `machine_user_id` VARCHAR(100) NOT NULL,
                `admission_no` VARCHAR(100) NOT NULL,
                `student_name` VARCHAR(255) DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_machine_user` (`machine_user_id`),
                KEY `idx_admission_no` (`admission_no`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            if (!$this->run_db_query_safely($sql)) {
                return false;
            }
        }

        $this->student_map_table_checked = true;
        return true;
    }

    private function ensure_staff_map_table() {
        if ($this->staff_map_table_checked && $this->db->table_exists('zkteco_staff_map')) {
            return true;
        }

        if (!$this->db->table_exists('zkteco_staff_map')) {
            $sql = "CREATE TABLE IF NOT EXISTS `zkteco_staff_map` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `machine_user_id` VARCHAR(100) NOT NULL,
                `staff_id` INT(11) NOT NULL,
                `staff_name` VARCHAR(255) DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `uniq_machine_user` (`machine_user_id`),
                KEY `idx_staff_id` (`staff_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            if (!$this->run_db_query_safely($sql)) {
                return false;
            }
        }

        $this->staff_map_table_checked = true;
        return true;
    }

    private function ensure_devices_table() {
        if ($this->devices_table_checked && $this->db->table_exists('zkteco_devices')) {
            return true;
        }

        if (!$this->db->table_exists('zkteco_devices')) {
            $sql = "CREATE TABLE IF NOT EXISTS `zkteco_devices` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `device_name` VARCHAR(255) NOT NULL,
                `device_ip` VARCHAR(100) NOT NULL,
                `device_port` INT(11) NOT NULL DEFAULT 4370,
                `protocol` VARCHAR(10) NOT NULL DEFAULT 'TCP',
                `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                `last_sync` DATETIME DEFAULT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_active` (`is_active`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";

            if (!$this->run_db_query_safely($sql)) {
                return false;
            }
        }

        $this->devices_table_checked = true;
        return true;
    }

    private function ensure_realtime_audit_table() {
        if ($this->realtime_audit_table_checked && $this->db->table_exists('zkteco_realtime_audit')) return true;
        $sql = "CREATE TABLE IF NOT EXISTS `zkteco_realtime_audit` (
            `id` INT(11) NOT NULL AUTO_INCREMENT, `outcome` VARCHAR(50) NOT NULL, `message` VARCHAR(1000) DEFAULT NULL,
            `device_id` VARCHAR(100) DEFAULT NULL, `machine_user_id` VARCHAR(100) DEFAULT NULL, `sync_uid` VARCHAR(191) DEFAULT NULL,
            `http_status` SMALLINT DEFAULT NULL, `created_at` DATETIME NOT NULL, PRIMARY KEY (`id`),
            KEY `idx_outcome_created` (`outcome`, `created_at`), KEY `idx_device_created` (`device_id`, `created_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $this->realtime_audit_table_checked = $this->run_db_query_safely($sql) ? true : false;
        return $this->realtime_audit_table_checked;
    }

    private function ensure_attendance_realtime_indexes() {
        $indexes = array(
            'uniq_sync_uid' => 'ALTER TABLE `attendance_logs` ADD UNIQUE KEY `uniq_sync_uid` (`sync_uid`)',
            'idx_device_timestamp' => 'ALTER TABLE `attendance_logs` ADD KEY `idx_device_timestamp` (`device_id`, `timestamp`)',
            'idx_machine_timestamp' => 'ALTER TABLE `attendance_logs` ADD KEY `idx_machine_timestamp` (`machine_user_id`, `timestamp`)'
        );
        foreach ($indexes as $name => $sql) {
            $query = $this->db->query("SHOW INDEX FROM `attendance_logs` WHERE Key_name = " . $this->db->escape($name));
            if (!$query || $query->num_rows() === 0) {
                $this->run_db_query_safely($sql);
            }
        }
    }
}

/* End of file Attendance_model.php */
/* Location: ./application/models/Attendance_model.php */

