<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Attendance Controller
 * 
 * Handles ZKTeco attendance import and management
 * 
 * @package    School Management System
 * @subpackage Controllers
 * @category   Attendance
 * @author     AI Assistant
 * @version    1.0
 */

class Attendance extends Admin_Controller {
    
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        
        // Load required models and libraries
        $this->loadAttendanceModel();
        $this->loadZktecoLibrary();
        
        // Set session data for menu
        $this->session->set_userdata('top_menu', 'Attendance');
        $this->session->set_userdata('sub_menu', 'admin/attendance');
    }

    private function loadAttendanceModel() {
        $this->invalidatePhpCache(APPPATH . 'models/Attendance_model.php');
        $this->invalidatePhpCache(APPPATH . 'models/attendance_model.php');

        try {
            $this->load->model('Attendance_model', 'attendance_model');
            return;
        } catch (RuntimeException $e) {
            $candidates = array(
                APPPATH . 'models/Attendance_model.php',
                APPPATH . 'models/attendance_model.php',
            );

            foreach ($candidates as $candidate) {
                if (is_file($candidate)) {
                    require_once $candidate;
                    break;
                }
            }

            if (!class_exists('Attendance_model', false) && class_exists('attendance_model', false)) {
                class_alias('attendance_model', 'Attendance_model');
            }

            if (!class_exists('Attendance_model', false)) {
                throw $e;
            }

            $this->attendance_model = new Attendance_model();
        }
    }

    private function loadZktecoLibrary() {
        if (isset($this->zkteco_lib) && is_object($this->zkteco_lib)) {
            return;
        }

        $candidates = array(
            APPPATH . 'libraries/Zkteco_lib.php',
            APPPATH . 'libraries/zkteco_lib.php',
        );

        foreach ($candidates as $candidate) {
            $this->invalidatePhpCache($candidate);
            if (is_file($candidate)) {
                require_once $candidate;
                break;
            }
        }

        if (!class_exists('Zkteco_lib', false) && class_exists('zkteco_lib', false)) {
            class_alias('zkteco_lib', 'Zkteco_lib');
        }

        if (!class_exists('Zkteco_lib', false)) {
            show_error('Unable to load the requested class: Zkteco_lib');
        }

        $this->zkteco_lib = new Zkteco_lib();
    }

    private function invalidatePhpCache($path) {
        if (function_exists('opcache_invalidate') && is_file($path)) {
            @opcache_invalidate($path, true);
        }
    }
    
    /**
     * Main attendance page
     * Shows today's attendance and import button
     */
    public function index() {
        // Check permission
        if (!$this->rbac->hasPrivilege('student_attendance', 'can_view')) {
            access_denied();
        }
        
        $data['title'] = 'ZKTeco Attendance Management';
        $data['title_list'] = 'Attendance Logs';
        
        // Get today's attendance
        $data['attendance_logs'] = $this->attendance_model->get_todays_attendance();
        
        // Get attendance statistics
        $data['stats'] = $this->attendance_model->get_attendance_stats();
        
        // Get devices
        $data['devices'] = $this->attendance_model->get_devices();
        $data['realtime_diagnostics'] = $this->attendance_model->get_realtime_diagnostics();
        
        // Load views
        $this->load->view('layout/header', $data);
        $this->load->view('admin/attendance/index', $data);
        $this->load->view('layout/footer', $data);
    }
    
    /**
     * Start attendance import from ZKTeco device
     * This runs in the background and updates progress
     */
    public function start_import() {
        // Set longer execution time for import
        set_time_limit(300); // 5 minutes
        ini_set('max_execution_time', 300);
        
        // Check permission
        if (!$this->rbac->hasPrivilege('student_attendance', 'can_add')) {
            echo json_encode(array('status' => 'error', 'message' => 'Access denied'));
            return;
        }
        
        $import_session_id = null;
        $device_connected = false;
        
        try {
            // Get device ID from POST
            $device_id = $this->input->post('device_id');
            
            // Generate unique session ID for this import
            $import_session_id = session_id() . '_' . time() . '_' . mt_rand(1000, 9999);
            
            // Initialize progress tracking
            if (!$this->attendance_model->init_import_progress($import_session_id)) {
                throw new Exception('Failed to initialize progress tracking');
            }
            
            // Update progress: Initializing (0-10%)
            $this->update_progress($import_session_id, 5, 'Initializing connection...');
            
            // Load device configuration
            $device_loaded = false;
            if ($device_id) {
                $device_loaded = $this->zkteco_lib->load_device($device_id);
            } else {
                $device_loaded = $this->zkteco_lib->load_device(); // Load default device
            }
            
            if (!$device_loaded) {
                throw new Exception('Failed to load device configuration. Please check device settings.');
            }
            
            $this->update_progress($import_session_id, 10, 'Device configuration loaded');
            
            // Update progress: Connecting to machine (10-30%)
            $this->update_progress($import_session_id, 15, 'Connecting to ZKTeco device...');
            
            // Connect to device with retry
            $connected = $this->zkteco_lib->connect(null, null, null, 3); // 3 retries
            
            if (!$connected) {
                $error_msg = 'Failed to connect to ZKTeco device. Please check:';
                $error_msg .= '<br>1. Device IP address is correct';
                $error_msg .= '<br>2. Device is powered on and connected to network';
                $error_msg .= '<br>3. Firewall allows connection on port 4370';
                $error_msg .= '<br>4. Try changing protocol (UDP/TCP) in device settings';
                
                $this->update_progress($import_session_id, 0, $error_msg, 'error');
                echo json_encode(array(
                    'status' => 'error',
                    'message' => $error_msg,
                    'session_id' => $import_session_id
                ));
                return;
            }
            
            $device_connected = true;
            $this->update_progress($import_session_id, 30, 'Connected to device successfully');
            
            // Update progress: Fetching logs (30-70%)
            $this->update_progress($import_session_id, 35, 'Disabling device...');
            
            // Disable device to prevent new records during sync
            $disable_result = $this->zkteco_lib->disable_device();
            if (!$disable_result) {
                // Use 'error' level to avoid undefined 'warning' level issues in CI logging
                log_message('error', 'ZKTeco: Failed to disable device, continuing anyway');
            }
            
            $this->update_progress($import_session_id, 40, 'Fetching attendance logs...');
            
            // Get attendance logs from device
            $attendance_raw = $this->zkteco_lib->get_attendance();
            
            // Handle empty or invalid results
            if (!is_array($attendance_raw)) {
                $this->zkteco_lib->enable_device();
                $this->zkteco_lib->disconnect();
                $device_connected = false;
                
                $error_msg = 'Failed to retrieve attendance logs from device.';
                $error_msg .= '<br>The device may be empty or there was a communication error.';
                
                $this->update_progress($import_session_id, 0, $error_msg, 'error');
                echo json_encode(array(
                    'status' => 'error',
                    'message' => $error_msg,
                    'session_id' => $import_session_id
                ));
                return;
            }
            
            if (empty($attendance_raw)) {
                $this->zkteco_lib->enable_device();
                $this->zkteco_lib->disconnect();
                $device_connected = false;
                
                $this->update_progress($import_session_id, 100, 'No attendance records found on device', 'warning');
                echo json_encode(array(
                    'status' => 'warning',
                    'message' => 'No attendance records found on device. The device may be empty.',
                    'session_id' => $import_session_id,
                    'stats' => array('total' => 0, 'inserted' => 0, 'duplicates' => 0, 'errors' => 0)
                ));
                return;
            }
            
            $total_records = count($attendance_raw);
            $this->update_progress($import_session_id, 60, "Retrieved {$total_records} records from device", null, $total_records);
            
            // Get student/staff mappings
            $this->update_progress($import_session_id, 65, 'Loading student and staff mappings...');
            $student_map = $this->attendance_model->get_student_mapping_array();
            $staff_map = $this->attendance_model->get_staff_mapping_array();
            
            if (empty($student_map)) {
                log_message('error', 'ZKTeco: No student mappings found');
            } else {
                log_message('info', 'ZKTeco: Loaded ' . count($student_map) . ' student mappings');
            }
            
            // Format attendance data
            $this->update_progress($import_session_id, 70, 'Processing attendance data...');
            $format_stats = array();
            $formatted_attendance = $this->zkteco_lib->format_attendance($attendance_raw, $student_map, $format_stats, $staff_map);
            
            if (empty($formatted_attendance)) {
                $this->zkteco_lib->enable_device();
                $this->zkteco_lib->disconnect();
                $device_connected = false;
                
                $error_msg = 'No valid attendance records to import.';
                $error_msg .= '<br>All records may be unmapped or have invalid data.';
                
                $this->update_progress($import_session_id, 0, $error_msg, 'warning');
                echo json_encode(array(
                    'status' => 'warning',
                    'message' => $error_msg,
                    'session_id' => $import_session_id,
                    'stats' => array('total' => $total_records, 'inserted' => 0, 'duplicates' => 0, 'errors' => $total_records)
                ));
                return;
            }
            
            // Update progress: Saving to database (70-90%) in chunks
            $this->update_progress($import_session_id, 75, 'Saving to database in chunks...');

            $configured_chunk_size = (int) $this->config->item('zkteco_batch_size');
            $save_chunk_size = ($configured_chunk_size > 0) ? min(max($configured_chunk_size, 50), 1000) : 300;
            $save_chunks = array_chunk($formatted_attendance, $save_chunk_size);
            $total_chunks = count($save_chunks);

            $stats = array(
                'total' => count($formatted_attendance),
                'inserted' => 0,
                'duplicates' => 0,
                'errors' => 0,
                'raw_total' => isset($format_stats['raw_total']) ? (int) $format_stats['raw_total'] : $total_records,
                'formatted_total' => isset($format_stats['formatted_total']) ? (int) $format_stats['formatted_total'] : count($formatted_attendance),
                'skipped_total' => isset($format_stats['skipped_total']) ? (int) $format_stats['skipped_total'] : 0,
                'integrity_gap' => 0
            );

            $processed_for_save = 0;
            foreach ($save_chunks as $chunk_index => $save_chunk) {
                $chunk_stats = $this->attendance_model->save_attendance_logs_batch($save_chunk);

                $stats['inserted'] += isset($chunk_stats['inserted']) ? (int) $chunk_stats['inserted'] : 0;
                $stats['duplicates'] += isset($chunk_stats['duplicates']) ? (int) $chunk_stats['duplicates'] : 0;
                $stats['errors'] += isset($chunk_stats['errors']) ? (int) $chunk_stats['errors'] : 0;

                $processed_for_save += count($save_chunk);
                $progress = 75 + (int) round((($chunk_index + 1) / max($total_chunks, 1)) * 15);

                $this->update_progress(
                    $import_session_id,
                    min($progress, 90),
                    'Saving chunk ' . ($chunk_index + 1) . '/' . $total_chunks . '...',
                    null,
                    $stats['total'],
                    $processed_for_save,
                    $stats['inserted'],
                    $stats['duplicates']
                );
            }

            $this->update_progress($import_session_id, 92, 'Finalizing import...');
            $saved_accounted = (int) $stats['inserted'] + (int) $stats['duplicates'] + (int) $stats['errors'];
            $stats['integrity_gap'] = max(0, (int) $stats['formatted_total'] - $saved_accounted);

            if ($stats['integrity_gap'] > 0 || $stats['skipped_total'] > 0) {
                $this->attendance_model->capture_zkteco_error(
                    'import_integrity_check',
                    'Potential missing logs detected during import reconciliation',
                    array(
                        'session_id' => $import_session_id,
                        'raw_total' => $stats['raw_total'],
                        'formatted_total' => $stats['formatted_total'],
                        'skipped_total' => $stats['skipped_total'],
                        'saved_accounted' => $saved_accounted,
                        'integrity_gap' => $stats['integrity_gap']
                    )
                );
            }
            
            // Always try to enable device and disconnect
            if ($device_connected) {
                try {
                    $this->zkteco_lib->enable_device();
                } catch (Exception $e) {
                    log_message('error', 'ZKTeco: Error enabling device - ' . $e->getMessage());
                }
                
                try {
                    $this->zkteco_lib->disconnect();
                    $device_connected = false;
                } catch (Exception $e) {
                    log_message('error', 'ZKTeco: Error disconnecting - ' . $e->getMessage());
                }
            }
            
            // Update device last sync time
            if ($device_id) {
                try {
                    $this->attendance_model->update_device_sync($device_id);
                } catch (Exception $e) {
                    log_message('error', 'ZKTeco: Error updating device sync time - ' . $e->getMessage());
                }
            }
            
            // Update progress: Completed (90-100%)
            $message = "Import completed successfully!";
            $message .= "<br>- Total records: {$stats['total']}";
            $message .= "<br>- New records: {$stats['inserted']}";
            $message .= "<br>- Duplicates skipped: {$stats['duplicates']}";
            if ($stats['errors'] > 0) {
                $message .= "<br>- Errors: {$stats['errors']}";
            }
            if ($stats['skipped_total'] > 0) {
                $message .= "<br>- Skipped invalid device records: {$stats['skipped_total']}";
            }
            if ($stats['integrity_gap'] > 0) {
                $message .= "<br>- Warning: {$stats['integrity_gap']} processed logs were not persisted. Check error capture logs.";
            }
            
            $this->update_progress($import_session_id, 100, $message, null, $stats['total'], $stats['total'], $stats['inserted'], $stats['duplicates']);

            // Log summary for monitoring/debugging
            log_message('info', 'ZKTeco Import Summary | session_id=' . $import_session_id
                . ' | device_id=' . ($device_id ? $device_id : 'default')
                . ' | total=' . $stats['total']
                . ' | inserted=' . $stats['inserted']
                . ' | duplicates=' . $stats['duplicates']
                . ' | errors=' . $stats['errors']);
            
            // Return success response
            echo json_encode(array(
                'status' => 'success',
                'message' => $message,
                'session_id' => $import_session_id,
                'stats' => $stats
            ));
            
        } catch (Exception $e) {
            // Handle errors
            $error_message = 'Import Error: ' . $e->getMessage();
            log_message('error', 'Attendance Import Error: ' . $e->getMessage());
            log_message('error', 'Attendance Import Stack Trace: ' . $e->getTraceAsString());
            
            if ($import_session_id) {
                $this->update_progress($import_session_id, 0, $error_message, 'error');
            }
            
            // Ensure device is enabled and disconnected
            if ($device_connected && $this->zkteco_lib->is_connected()) {
                try {
                    $this->zkteco_lib->enable_device();
                } catch (Exception $e2) {
                    log_message('error', 'ZKTeco: Error enabling device in cleanup - ' . $e2->getMessage());
                }
                
                try {
                    $this->zkteco_lib->disconnect();
                } catch (Exception $e2) {
                    log_message('error', 'ZKTeco: Error disconnecting in cleanup - ' . $e2->getMessage());
                }
            }
            
            echo json_encode(array(
                'status' => 'error',
                'message' => $error_message,
                'session_id' => $import_session_id
            ));
        } catch (Error $e) {
            // Handle fatal errors
            $error_message = 'Fatal Error: ' . $e->getMessage();
            log_message('error', 'Attendance Import Fatal Error: ' . $e->getMessage());
            
            if ($import_session_id) {
                try {
                    $this->update_progress($import_session_id, 0, $error_message, 'error');
                } catch (Exception $e2) {
                    // Ignore
                }
            }
            
            // Cleanup device connection
            if ($device_connected) {
                try {
                    if ($this->zkteco_lib->is_connected()) {
                        $this->zkteco_lib->enable_device();
                        $this->zkteco_lib->disconnect();
                    }
                } catch (Exception $e2) {
                    // Ignore cleanup errors
                }
            }
            
            echo json_encode(array(
                'status' => 'error',
                'message' => $error_message,
                'session_id' => $import_session_id
            ));
        }
    }
    
    /**
     * Import attendance logs from uploaded file (JSON/CSV/TXT).
     */
    public function import_file() {
        $this->output->set_content_type('application/json');
        set_time_limit(900);
        ini_set('max_execution_time', 900);

        if (!$this->rbac->hasPrivilege('student_attendance', 'can_add')) {
            echo json_encode(array('status' => 'error', 'message' => 'Access denied'));
            return;
        }

        try {
            if (!isset($_FILES['attendance_file']) || empty($_FILES['attendance_file']['name'])) {
                echo json_encode(array('status' => 'error', 'message' => 'Please select a file to import'));
                return;
            }

            $file = $_FILES['attendance_file'];
            if (!empty($file['error']) && (int) $file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(array('status' => 'error', 'message' => 'File upload failed. Please try again.'));
                return;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed_extensions = array('json', 'csv', 'txt');
            if (!in_array($ext, $allowed_extensions, true)) {
                echo json_encode(array('status' => 'error', 'message' => 'Invalid file type. Allowed: JSON, CSV, TXT'));
                return;
            }

            if (!is_uploaded_file($file['tmp_name'])) {
                echo json_encode(array('status' => 'error', 'message' => 'Invalid uploaded file'));
                return;
            }

            $configured_max_mb = (int) $this->config->item('zkteco_file_import_max_mb');
            $max_size_mb = ($configured_max_mb > 0) ? min(max($configured_max_mb, 10), 200) : 100;
            $max_size_bytes = $max_size_mb * 1024 * 1024;
            if (!empty($file['size']) && (int) $file['size'] > $max_size_bytes) {
                echo json_encode(array('status' => 'error', 'message' => 'File is too large. Maximum allowed size is ' . $max_size_mb . ' MB.'));
                return;
            }

            $device_id = (int) $this->input->post('device_id');
            if ($device_id > 0) {
                $this->zkteco_lib->load_device($device_id);
            }

            $parsed = $this->parse_uploaded_attendance_file($file['tmp_name'], $ext);
            $attendance_raw = isset($parsed['records']) ? $parsed['records'] : array();
            $meta = isset($parsed['meta']) ? $parsed['meta'] : array();

            if (empty($attendance_raw)) {
                echo json_encode(array(
                    'status' => 'warning',
                    'message' => 'No valid attendance records found in uploaded file.',
                    'stats' => array('total' => 0, 'inserted' => 0, 'duplicates' => 0, 'errors' => 0)
                ));
                return;
            }

            $student_map = $this->attendance_model->get_student_mapping_array();
            $staff_map = $this->attendance_model->get_staff_mapping_array();
            $fallback_device_id = isset($meta['device_ip']) ? trim((string) $meta['device_ip']) : '';
            $configured_raw_chunk = (int) $this->config->item('zkteco_file_import_chunk_size');
            $raw_chunk_size = ($configured_raw_chunk > 0) ? min(max($configured_raw_chunk, 200), 5000) : 1000;
            $raw_chunks = array_chunk($attendance_raw, $raw_chunk_size);

            $stats = array(
                'total' => 0,
                'inserted' => 0,
                'duplicates' => 0,
                'errors' => 0,
                'raw_total' => count($attendance_raw),
                'formatted_total' => 0,
                'skipped_total' => 0
            );

            foreach ($raw_chunks as $raw_chunk) {
                $chunk_format_stats = array();
                $formatted_chunk = $this->zkteco_lib->format_attendance($raw_chunk, $student_map, $chunk_format_stats, $staff_map);

                if (!empty($formatted_chunk) && $fallback_device_id !== '') {
                    foreach ($formatted_chunk as &$row) {
                        if (empty($row['device_id'])) {
                            $row['device_id'] = $fallback_device_id;
                        }
                    }
                    unset($row);
                }

                $stats['formatted_total'] += isset($chunk_format_stats['formatted_total']) ? (int) $chunk_format_stats['formatted_total'] : count($formatted_chunk);
                $stats['skipped_total'] += isset($chunk_format_stats['skipped_total']) ? (int) $chunk_format_stats['skipped_total'] : 0;
                $stats['total'] += count($formatted_chunk);

                if (empty($formatted_chunk)) {
                    continue;
                }

                $chunk_stats = $this->attendance_model->save_attendance_logs_batch($formatted_chunk);
                $stats['inserted'] += isset($chunk_stats['inserted']) ? (int) $chunk_stats['inserted'] : 0;
                $stats['duplicates'] += isset($chunk_stats['duplicates']) ? (int) $chunk_stats['duplicates'] : 0;
                $stats['errors'] += isset($chunk_stats['errors']) ? (int) $chunk_stats['errors'] : 0;
            }

            if ($stats['total'] === 0) {
                echo json_encode(array(
                    'status' => 'warning',
                    'message' => 'No valid attendance records to import after validation.',
                    'stats' => array(
                        'total' => 0,
                        'inserted' => 0,
                        'duplicates' => 0,
                        'errors' => $stats['raw_total'],
                        'raw_total' => $stats['raw_total'],
                        'formatted_total' => 0,
                        'skipped_total' => $stats['skipped_total']
                    )
                ));
                return;
            }

            if ($device_id > 0) {
                $this->attendance_model->update_device_sync($device_id);
            }

            $message = "File import completed successfully!";
            $message .= "<br>- Total records: {$stats['total']}";
            $message .= "<br>- New records: {$stats['inserted']}";
            $message .= "<br>- Duplicates skipped: {$stats['duplicates']}";
            if ($stats['errors'] > 0) {
                $message .= "<br>- Errors: {$stats['errors']}";
            }
            if ($stats['skipped_total'] > 0) {
                $message .= "<br>- Skipped invalid file records: {$stats['skipped_total']}";
            }

            echo json_encode(array(
                'status' => 'success',
                'message' => $message,
                'stats' => $stats
            ));
        } catch (Throwable $e) {
            log_message('error', 'Attendance file import error: ' . $e->getMessage());
            log_message('error', 'Attendance file import stack: ' . $e->getTraceAsString());
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Import failed: ' . $e->getMessage()
            ));
        }
    }

    /**
     * Parse uploaded attendance file into raw records expected by formatter.
     *
     * @param string $file_path
     * @param string $extension
     * @return array
     */
    private function parse_uploaded_attendance_file($file_path, $extension) {
        $extension = strtolower((string) $extension);
        if ($extension === 'json') {
            return $this->parse_uploaded_attendance_json($file_path);
        }

        return $this->parse_uploaded_attendance_delimited($file_path);
    }

    /**
     * Parse JSON attendance export.
     *
     * @param string $file_path
     * @return array
     */
    private function parse_uploaded_attendance_json($file_path) {
        $content = @file_get_contents($file_path);
        if ($content === false) {
            throw new Exception('Unable to read uploaded JSON file');
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            throw new Exception('Invalid JSON format');
        }

        $records_source = array();
        $meta = array();

        if (isset($decoded['records']) && is_array($decoded['records'])) {
            $records_source = $decoded['records'];
        } elseif (isset($decoded['logs']) && is_array($decoded['logs'])) {
            $records_source = $decoded['logs'];
        } elseif (isset($decoded['attendance']) && is_array($decoded['attendance'])) {
            $records_source = $decoded['attendance'];
        } elseif (array_values($decoded) === $decoded) {
            $records_source = $decoded;
        } else {
            $records_source = array();
        }

        if (isset($decoded['device_ip'])) {
            $meta['device_ip'] = trim((string) $decoded['device_ip']);
        } elseif (isset($decoded['device']) && is_array($decoded['device']) && isset($decoded['device']['ip'])) {
            $meta['device_ip'] = trim((string) $decoded['device']['ip']);
        }

        $records = array();
        foreach ($records_source as $row) {
            $normalized = $this->normalize_uploaded_record($row);
            if ($normalized !== null) {
                $records[] = $normalized;
            }
        }

        return array(
            'records' => $records,
            'meta' => $meta
        );
    }

    /**
     * Parse CSV/TXT attendance export.
     *
     * @param string $file_path
     * @return array
     */
    private function parse_uploaded_attendance_delimited($file_path) {
        $lines = @file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            throw new Exception('Unable to read uploaded file');
        }
        if (empty($lines)) {
            return array('records' => array(), 'meta' => array());
        }

        $first_line = (string) $lines[0];
        $delimiters = array(',', '|', "\t", ';');
        $delimiter = ',';
        $max_parts = -1;
        foreach ($delimiters as $candidate) {
            $parts_count = count(str_getcsv($first_line, $candidate));
            if ($parts_count > $max_parts) {
                $max_parts = $parts_count;
                $delimiter = $candidate;
            }
        }

        $first_cols = array_map('trim', str_getcsv($first_line, $delimiter));
        $header_map = array();
        $has_header = false;
        $first_cols_lower = array_map('strtolower', $first_cols);
        if (in_array('timestamp', $first_cols_lower, true)
            || in_array('machine_user_id', $first_cols_lower, true)
            || in_array('user_id', $first_cols_lower, true)
            || in_array('id', $first_cols_lower, true)) {
            $has_header = true;
            foreach ($first_cols_lower as $idx => $name) {
                $header_map[$name] = $idx;
            }
        }

        $records = array();
        foreach ($lines as $line_index => $line) {
            if ($line_index === 0 && $has_header) {
                continue;
            }
            $cols = array_map('trim', str_getcsv((string) $line, $delimiter));
            if (empty($cols)) {
                continue;
            }
            $normalized = $this->normalize_uploaded_record($cols, $header_map);
            if ($normalized !== null) {
                $records[] = $normalized;
            }
        }

        return array('records' => $records, 'meta' => array());
    }

    /**
     * Normalize one uploaded row into [uid, id, state, timestamp].
     *
     * @param mixed $row
     * @param array $header_map
     * @return array|null
     */
    private function normalize_uploaded_record($row, $header_map = array()) {
        $uid = '';
        $machine_id = '';
        $state = 0;
        $timestamp = '';

        if (is_array($row) && array_values($row) !== $row) {
            $uid = isset($row['uid']) ? $row['uid'] : (isset($row['user_uid']) ? $row['user_uid'] : '');
            $machine_id = isset($row['id']) ? $row['id'] : (isset($row['machine_user_id']) ? $row['machine_user_id'] : (isset($row['user_id']) ? $row['user_id'] : ''));
            $state = isset($row['state']) ? $row['state'] : (isset($row['punch']) ? $row['punch'] : (isset($row['status']) ? $row['status'] : 0));
            $timestamp = isset($row['timestamp']) ? $row['timestamp'] : (isset($row['punch_time']) ? $row['punch_time'] : (isset($row['datetime']) ? $row['datetime'] : (isset($row['time']) ? $row['time'] : '')));
        } elseif (is_array($row) && !empty($header_map)) {
            $uid_idx = isset($header_map['uid']) ? $header_map['uid'] : (isset($header_map['user_uid']) ? $header_map['user_uid'] : null);
            $id_idx = isset($header_map['id']) ? $header_map['id'] : (isset($header_map['machine_user_id']) ? $header_map['machine_user_id'] : (isset($header_map['user_id']) ? $header_map['user_id'] : null));
            $state_idx = isset($header_map['state']) ? $header_map['state'] : (isset($header_map['punch']) ? $header_map['punch'] : (isset($header_map['status']) ? $header_map['status'] : null));
            $time_idx = isset($header_map['timestamp']) ? $header_map['timestamp'] : (isset($header_map['punch_time']) ? $header_map['punch_time'] : (isset($header_map['datetime']) ? $header_map['datetime'] : (isset($header_map['time']) ? $header_map['time'] : null)));

            $uid = ($uid_idx !== null && isset($row[$uid_idx])) ? $row[$uid_idx] : '';
            $machine_id = ($id_idx !== null && isset($row[$id_idx])) ? $row[$id_idx] : '';
            $state = ($state_idx !== null && isset($row[$state_idx])) ? $row[$state_idx] : 0;
            $timestamp = ($time_idx !== null && isset($row[$time_idx])) ? $row[$time_idx] : '';
        } elseif (is_array($row)) {
            if (count($row) >= 4) {
                $uid = $row[0];
                $machine_id = $row[1];
                $state = $row[2];
                $timestamp = $row[3];
            } elseif (count($row) >= 3) {
                $uid = $row[0];
                $machine_id = $row[0];
                $state = $row[1];
                $timestamp = $row[2];
            } else {
                return null;
            }
        } else {
            return null;
        }

        $uid = trim((string) $uid);
        $machine_id = trim((string) $machine_id);
        if ($machine_id === '' && $uid !== '') {
            $machine_id = $uid;
        }
        $timestamp = trim((string) $timestamp);
        if ($machine_id === '' || $timestamp === '') {
            return null;
        }

        return array(
            $uid,
            $machine_id,
            $this->normalize_uploaded_state($state),
            $timestamp
        );
    }

    /**
     * Convert text/numeric state from import file into numeric code.
     *
     * @param mixed $state
     * @return int
     */
    private function normalize_uploaded_state($state) {
        if (is_numeric($state)) {
            return (int) $state;
        }

        $text = strtolower(trim((string) $state));
        $map = array(
            'check in' => 0,
            'in' => 0,
            'present' => 0,
            'check out' => 1,
            'out' => 1,
            'break out' => 2,
            'break in' => 3,
            'overtime in' => 4,
            'overtime out' => 5
        );

        return isset($map[$text]) ? $map[$text] : 0;
    }

    /**
     * Get import progress (called via AJAX polling)
     */
    public function get_progress() {
        $session_id = $this->input->get('session_id');
        
        if (!$session_id) {
            echo json_encode(array('status' => 'error', 'message' => 'No session ID provided'));
            return;
        }
        
        $progress = $this->attendance_model->get_import_progress($session_id);
        
        if ($progress) {
            echo json_encode(array(
                'status' => 'success',
                'progress' => $progress->progress,
                'message' => $progress->status,
                'total_records' => $progress->total_records,
                'processed_records' => $progress->processed_records,
                'new_records' => $progress->new_records,
                'duplicate_records' => $progress->duplicate_records,
                'error_message' => $progress->error_message
            ));
        } else {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Progress not found'
            ));
        }
    }
    
    /**
     * Update import progress in database
     * 
     * @param string $session_id Import session ID
     * @param int $progress Progress percentage (0-100)
     * @param string $status Status message
     * @param string $error_type Error type (null, 'error', 'warning')
     * @param int $total Total records
     * @param int $processed Processed records
     * @param int $new_records New records inserted
     * @param int $duplicates Duplicate records
     */
    private function update_progress($session_id, $progress, $status, $error_type = null, $total = 0, $processed = 0, $new_records = 0, $duplicates = 0) {
        $data = array(
            'progress' => $progress,
            'status' => $status
        );
        
        if ($total > 0) {
            $data['total_records'] = $total;
        }
        
        if ($processed > 0) {
            $data['processed_records'] = $processed;
        }
        
        if ($new_records > 0) {
            $data['new_records'] = $new_records;
        }
        
        if ($duplicates > 0) {
            $data['duplicate_records'] = $duplicates;
        }
        
        if ($error_type) {
            $data['error_message'] = $status;
        }
        
        $this->attendance_model->update_import_progress($session_id, $data);
        
        // Small delay to make progress visible
        usleep(100000); // 0.1 second
    }
    
    /**
     * View attendance logs with filters
     */
    public function view_logs() {
        // Check permission
        if (!$this->rbac->hasPrivilege('student_attendance', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('sub_menu', 'admin/attendance/view_logs');
        
        $data['title'] = 'Attendance Logs';
        
        // Get filter parameters
        $date = $this->input->get('date') ?: date('Y-m-d');
        $admission_no = $this->input->get('admission_no');
        $date_mode = $this->input->get('date_mode') === 'created_at' ? 'created_at' : 'timestamp';
        $per_page = (int) $this->input->get('per_page');
        if ($per_page <= 0) {
            $per_page = 200;
        }
        $per_page = min($per_page, 500);
        $current_page = (int) $this->input->get('page');
        if ($current_page < 1) {
            $current_page = 1;
        }
        $offset = ($current_page - 1) * $per_page;
        
        // Get attendance logs
        $has_paged_log_model = method_exists($this->attendance_model, 'count_attendance_by_date');
        $total_logs = $has_paged_log_model
            ? $this->attendance_model->count_attendance_by_date($date, ($admission_no ? $admission_no : null), $date_mode)
            : 0;

        if ($total_logs > 0 && $offset >= $total_logs) {
            $current_page = (int) ceil($total_logs / $per_page);
            $offset = max(0, ($current_page - 1) * $per_page);
        }

        if ($has_paged_log_model) {
            $data['attendance_logs'] = $this->attendance_model->get_attendance_by_date(
                $date,
                ($admission_no ? $admission_no : null),
                $date_mode,
                $per_page,
                $offset
            );
        } elseif ($admission_no) {
            $data['attendance_logs'] = $this->attendance_model->get_attendance_by_date($date, $admission_no);
        } else {
            $data['attendance_logs'] = $this->attendance_model->get_attendance_by_date($date);
        }
        if (!$has_paged_log_model) {
            $total_logs = count($data['attendance_logs']);
            $data['attendance_logs'] = array_slice($data['attendance_logs'], $offset, $per_page);
        }
        $data['students_for_mapping'] = method_exists($this->attendance_model, 'get_students_for_mapping')
            ? $this->attendance_model->get_students_for_mapping()
            : array();
        $data['staff_for_mapping'] = method_exists($this->attendance_model, 'get_staff_for_mapping')
            ? $this->attendance_model->get_staff_for_mapping()
            : array();
        
        $data['selected_date'] = $date;
        $data['selected_admission'] = $admission_no;
        $data['selected_date_mode'] = $date_mode;
        $data['log_pagination'] = array(
            'total_rows' => $total_logs,
            'per_page' => $per_page,
            'current_page' => $current_page,
            'total_pages' => $total_logs > 0 ? (int) ceil($total_logs / $per_page) : 1,
            'offset' => $offset,
        );
        
        // Load views
        $this->load->view('layout/header', $data);
        $this->load->view('admin/attendance/view_logs', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * Staff attendance report.
     */
    public function report() {
        if (!$this->rbac->hasPrivilege('student_attendance', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('sub_menu', 'admin/attendance/report');

        $data['title'] = 'Staff Attendance Report';
        $data['roles'] = $this->attendance_model->get_staff_report_roles();
        $data['staff_list'] = $this->attendance_model->get_staff_report_people();

        $role_id = (int) $this->input->get('role_id');
        $staff_id = (int) $this->input->get('staff_id');
        $from_date = $this->input->get('from_date') ?: date('Y-m-01');
        $to_date = $this->input->get('to_date') ?: date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from_date)) {
            $from_date = date('Y-m-01');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to_date)) {
            $to_date = date('Y-m-d');
        }
        if (strtotime($from_date) > strtotime($to_date)) {
            $tmp = $from_date;
            $from_date = $to_date;
            $to_date = $tmp;
        }

        $data['selected_role_id'] = $role_id;
        $data['selected_staff_id'] = $staff_id;
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
        $data['report_data'] = $this->attendance_model->get_staff_attendance_report(
            $role_id,
            $staff_id,
            $from_date,
            $to_date
        );

        $this->load->view('layout/header', $data);
        $this->load->view('admin/attendance/report', $data);
        $this->load->view('layout/footer', $data);
    }

    /**
     * Student attendance report backed by imported ZKTeco logs.
     * This endpoint is included in the extracted module; the source app's
     * staff-only ZKTeco report remains unchanged.
     */
    public function student_report() {
        if (!$this->rbac->hasPrivilege('student_attendance', 'can_view')) {
            access_denied();
        }

        $this->session->set_userdata('sub_menu', 'admin/attendance/student_report');
        $class_id = (int) $this->input->get('class_id');
        $section_id = (int) $this->input->get('section_id');
        $admission_no = trim((string) $this->input->get('admission_no'));
        $from_date = $this->input->get('from_date') ?: date('Y-m-01');
        $to_date = $this->input->get('to_date') ?: date('Y-m-d');

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from_date)) $from_date = date('Y-m-01');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to_date)) $to_date = date('Y-m-d');
        if (strtotime($from_date) > strtotime($to_date)) {
            $tmp = $from_date; $from_date = $to_date; $to_date = $tmp;
        }

        $data['title'] = 'Student Attendance Report';
        $data['classlist'] = $this->class_model->get();
        $data['section_list'] = $class_id ? $this->section_model->getClassBySection($class_id) : array();
        $data['class_id'] = $class_id;
        $data['section_id'] = $section_id;
        $data['admission_no'] = $admission_no;
        $data['from_date'] = $from_date;
        $data['to_date'] = $to_date;
        $data['report_data'] = $class_id ? $this->attendance_model->get_student_attendance_report(
            $class_id, $section_id, $from_date, $to_date, $admission_no ?: null
        ) : array('rows' => array(), 'date_list' => array(), 'summary' => array(
            'students' => 0, 'present' => 0, 'absent' => 0,
            'leave_approved' => 0, 'leave_pending' => 0, 'leave_denied' => 0
        ));

        $this->load->view('layout/header', $data);
        $this->load->view('admin/attendance/student_report', $data);
        $this->load->view('layout/footer', $data);
    }
    
    /**
     * Manage student and staff mappings
     */
    public function manage_mappings() {
        // Check permission
        if (!$this->rbac->hasPrivilege('student_attendance', 'can_edit')) {
            access_denied();
        }

        $this->session->set_userdata('sub_menu', 'admin/attendance/manage_mappings');
        
        $data['title'] = 'Manage Attendance Mappings';
        
        // Get all mappings
        $data['student_mappings'] = $this->attendance_model->get_student_mapping();
        $data['staff_mappings'] = $this->attendance_model->get_staff_mapping();
        $data['mappings'] = $data['student_mappings'];
        
        // Get students without mapping
        $data['unmapped_students'] = $this->attendance_model->get_students_without_mapping();
        $data['students_for_mapping'] = $this->attendance_model->get_students_for_mapping();
        $data['staff_for_mapping'] = $this->attendance_model->get_staff_for_mapping();

        // Paginate unmapped machine users to keep page fast for large datasets.
        $per_page = 50;
        $current_page = (int) $this->input->get('unmapped_page');
        if ($current_page < 1) {
            $current_page = 1;
        }
        $total_unmapped_users = $this->attendance_model->count_unmapped_users();
        $total_pages = (int) ceil($total_unmapped_users / $per_page);
        if ($total_pages > 0 && $current_page > $total_pages) {
            $current_page = $total_pages;
        }
        $offset = ($current_page - 1) * $per_page;
        if ($offset < 0) {
            $offset = 0;
        }

        $data['unmapped_users'] = $this->attendance_model->get_unmapped_users($per_page, $offset);
        $data['unmapped_pagination'] = array(
            'current_page' => $current_page,
            'per_page' => $per_page,
            'total_rows' => $total_unmapped_users,
            'total_pages' => $total_pages,
        );
        
        // Load views
        $this->load->view('layout/header', $data);
        $this->load->view('admin/attendance/manage_mappings', $data);
        $this->load->view('layout/footer', $data);
    }
    
    /**
     * Save student or staff mapping (AJAX)
     */
    public function save_mapping() {
        $this->output->set_content_type('application/json');
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_hash = $this->security->get_csrf_hash();
        // Check permission
        if (!$this->rbac->hasPrivilege('student_attendance', 'can_add')) {
            echo json_encode(array('status' => 'error', 'message' => 'Access denied', 'csrf_token_name' => $csrf_name, 'csrf_hash' => $csrf_hash));
            return;
        }

        try {
            $machine_user_id = trim((string) $this->input->post('machine_user_id'));
            $target_type = trim((string) $this->input->post('target_type'));
            if ($target_type !== 'staff') {
                $target_type = 'student';
            }
            $admission_no = trim((string) $this->input->post('admission_no'));
            $staff_id = (int) $this->input->post('staff_id');
            $student_name = trim((string) $this->input->post('student_name'));

            if ($machine_user_id === '' || ($target_type === 'student' && $admission_no === '') || ($target_type === 'staff' && $staff_id <= 0)) {
                echo json_encode(array('status' => 'error', 'message' => 'Missing required fields', 'csrf_token_name' => $csrf_name, 'csrf_hash' => $csrf_hash));
                return;
            }

            if ($target_type === 'staff') {
                $staff = $this->attendance_model->get_staff_by_id($staff_id);
                if (!$staff) {
                    echo json_encode(array('status' => 'error', 'message' => 'Staff member not found', 'csrf_token_name' => $csrf_name, 'csrf_hash' => $csrf_hash));
                    return;
                }

                $result = $this->attendance_model->save_staff_mapping(array(
                    'machine_user_id' => $machine_user_id,
                    'staff_id' => $staff_id,
                    'staff_name' => trim($staff->name . ' ' . $staff->surname)
                ));

                if ($result) {
                    $this->attendance_model->apply_staff_mapping_to_logs_count($machine_user_id, $staff_id);
                    echo json_encode(array('status' => 'success', 'message' => 'Staff mapping saved successfully', 'csrf_token_name' => $csrf_name, 'csrf_hash' => $csrf_hash));
                } else {
                    echo json_encode(array('status' => 'error', 'message' => 'Failed to save staff mapping', 'csrf_token_name' => $csrf_name, 'csrf_hash' => $csrf_hash));
                }
                return;
            }

            $data = array(
                'machine_user_id' => $machine_user_id,
                'admission_no' => $admission_no,
                'student_name' => $student_name
            );

            $result = $this->attendance_model->save_student_mapping($data);

            if ($result) {
                $this->attendance_model->apply_mapping_to_logs_count($machine_user_id, $admission_no);
                echo json_encode(array('status' => 'success', 'message' => 'Mapping saved successfully', 'csrf_token_name' => $csrf_name, 'csrf_hash' => $csrf_hash));
            } else {
                echo json_encode(array('status' => 'error', 'message' => 'Failed to save mapping', 'csrf_token_name' => $csrf_name, 'csrf_hash' => $csrf_hash));
            }
        } catch (Exception $e) {
            $this->attendance_model->capture_zkteco_error('save_mapping_exception', $e->getMessage(), array(
                'machine_user_id' => $this->input->post('machine_user_id'),
                'admission_no' => $this->input->post('admission_no'),
                'staff_id' => $this->input->post('staff_id'),
                'target_type' => $this->input->post('target_type')
            ));
            echo json_encode(array('status' => 'error', 'message' => 'Unexpected error while saving mapping', 'csrf_token_name' => $csrf_name, 'csrf_hash' => $csrf_hash));
        }
    }

    /**
     * Save bulk mappings and backfill unmapped logs.
     */
    public function save_bulk_mappings() {
        $this->output->set_content_type('application/json');
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_hash = $this->security->get_csrf_hash();

        if (!$this->rbac->hasPrivilege('student_attendance', 'can_add')) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Access denied',
                'csrf_token_name' => $csrf_name,
                'csrf_hash' => $csrf_hash,
            ));
            return;
        }

        try {
            $mappings_raw = $this->input->post('mappings');
            $mappings = array();

            if (is_string($mappings_raw)) {
                $decoded = json_decode($mappings_raw, true);
                if (is_array($decoded)) {
                    $mappings = $decoded;
                }
            } elseif (is_array($mappings_raw)) {
                $mappings = $mappings_raw;
            }

            if (empty($mappings)) {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'No mappings submitted',
                    'csrf_token_name' => $csrf_name,
                    'csrf_hash' => $csrf_hash,
                ));
                return;
            }

            if (count($mappings) > 1000) {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Too many rows submitted at once. Please submit up to 1000 rows.',
                    'csrf_token_name' => $csrf_name,
                    'csrf_hash' => $csrf_hash,
                ));
                return;
            }

            $summary = method_exists($this->attendance_model, 'bulk_save_attendance_mappings')
                ? $this->attendance_model->bulk_save_attendance_mappings($mappings)
                : $this->bulkSaveAttendanceMappings($mappings);
            $status = ($summary['saved'] > 0 && $summary['failed'] === 0) ? 'success' : (($summary['saved'] > 0) ? 'warning' : 'error');

            echo json_encode(array(
                'status' => $status,
                'message' => 'Bulk mapping completed',
                'summary' => $summary,
                'csrf_token_name' => $csrf_name,
                'csrf_hash' => $csrf_hash,
            ));
        } catch (Throwable $e) {
            $this->attendance_model->capture_zkteco_error('save_bulk_mappings_exception', $e->getMessage(), array(
                'mappings_post_type' => gettype($this->input->post('mappings'))
            ));
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Unexpected error while saving bulk mappings',
                'csrf_token_name' => $csrf_name,
                'csrf_hash' => $csrf_hash,
            ));
        }
    }

    /**
     * Controller-side fallback for older loaded Attendance_model instances.
     *
     * @param array $mappings
     * @return array
     */
    private function bulkSaveAttendanceMappings($mappings) {
        log_message('error', 'Attendance Controller: using controller fallback for bulk student/staff mappings');

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

                    $staff = $this->attendance_model->get_staff_by_id($staff_id);
                    if (!$staff) {
                        $summary['failed']++;
                        $summary['errors'][] = 'Row ' . $row_no . ': Staff member not found (' . $staff_id . ')';
                        continue;
                    }

                    $saved = $this->attendance_model->save_staff_mapping(array(
                        'machine_user_id' => $machine_user_id,
                        'staff_id' => $staff_id,
                        'staff_name' => trim($staff->name . ' ' . $staff->surname)
                    ));
                    if (!$saved) {
                        $summary['failed']++;
                        $summary['errors'][] = 'Row ' . $row_no . ': Failed to save staff mapping for machine user ' . $machine_user_id;
                        continue;
                    }

                    $updated_count = $this->attendance_model->apply_staff_mapping_to_logs_count($machine_user_id, $staff_id);
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

                $student = $this->attendance_model->get_student_by_admission($admission_no);
                if (!$student) {
                    $summary['failed']++;
                    $summary['errors'][] = 'Row ' . $row_no . ': Student not found (' . $admission_no . ')';
                    continue;
                }

                $saved = $this->attendance_model->save_student_mapping(array(
                    'machine_user_id' => $machine_user_id,
                    'admission_no' => $admission_no,
                    'student_name' => trim($student->firstname . ' ' . $student->lastname)
                ));
                if (!$saved) {
                    $summary['failed']++;
                    $summary['errors'][] = 'Row ' . $row_no . ': Failed to save mapping for machine user ' . $machine_user_id;
                    continue;
                }

                $updated_count = $this->attendance_model->apply_mapping_to_logs_count($machine_user_id, $admission_no);
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
                log_message('error', 'Attendance Controller: bulkSaveAttendanceMappings row error - ' . $e->getMessage());
            }
        }

        return $summary;
    }

    /**
     * Save mapping from view logs and backfill existing unmapped attendance rows.
     */
    public function map_unmapped_log() {
        $this->output->set_content_type('application/json');
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_hash = $this->security->get_csrf_hash();

        if (!$this->rbac->hasPrivilege('student_attendance', 'can_edit')) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Access denied',
                'csrf_token_name' => $csrf_name,
                'csrf_hash' => $csrf_hash,
            ));
            return;
        }
        try {
            $machine_user_id = trim((string) $this->input->post('machine_user_id'));
            $target_type = trim((string) $this->input->post('target_type'));
            if ($target_type !== 'staff') {
                $target_type = 'student';
            }
            $admission_no = trim((string) $this->input->post('admission_no'));
            $staff_id = (int) $this->input->post('staff_id');

            if ($machine_user_id === '' || ($target_type === 'student' && $admission_no === '') || ($target_type === 'staff' && $staff_id <= 0)) {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Machine user and selected person are required',
                    'csrf_token_name' => $csrf_name,
                    'csrf_hash' => $csrf_hash,
                ));
                return;
            }

            if ($target_type === 'staff') {
                $staff = $this->attendance_model->get_staff_by_id($staff_id);
                if (!$staff) {
                    echo json_encode(array(
                        'status' => 'error',
                        'message' => 'Staff member not found for selected staff ID',
                        'csrf_token_name' => $csrf_name,
                        'csrf_hash' => $csrf_hash,
                    ));
                    return;
                }

                $saved = $this->attendance_model->save_staff_mapping(array(
                    'machine_user_id' => $machine_user_id,
                    'staff_id' => $staff_id,
                    'staff_name' => trim($staff->name . ' ' . $staff->surname)
                ));
                if (!$saved) {
                    echo json_encode(array(
                        'status' => 'error',
                        'message' => 'Failed to save staff mapping',
                        'csrf_token_name' => $csrf_name,
                        'csrf_hash' => $csrf_hash,
                    ));
                    return;
                }

                $updated = $this->attendance_model->apply_staff_mapping_to_logs_count($machine_user_id, $staff_id);
                if ($updated === false) {
                    echo json_encode(array(
                        'status' => 'error',
                        'message' => 'Mapping saved, but failed to update historical logs',
                        'csrf_token_name' => $csrf_name,
                        'csrf_hash' => $csrf_hash,
                    ));
                    return;
                }

                echo json_encode(array(
                    'status' => 'success',
                    'message' => 'Staff mapping saved and attendance updated successfully',
                    'staff_name' => trim($staff->name . ' ' . $staff->surname),
                    'employee_id' => isset($staff->employee_id) ? $staff->employee_id : '',
                    'staff_role' => isset($staff->staff_role) ? $staff->staff_role : '',
                    'csrf_token_name' => $csrf_name,
                    'csrf_hash' => $csrf_hash,
                ));
                return;
            }

            $student = $this->attendance_model->get_student_by_admission($admission_no);
            if (!$student) {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Student not found for selected admission number',
                    'csrf_token_name' => $csrf_name,
                    'csrf_hash' => $csrf_hash,
                ));
                return;
            }

            $mapping_data = array(
                'machine_user_id' => $machine_user_id,
                'admission_no' => $admission_no,
                'student_name' => trim($student->firstname . ' ' . $student->lastname)
            );

            $saved = $this->attendance_model->save_student_mapping($mapping_data);
            if (!$saved) {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Failed to save mapping',
                    'csrf_token_name' => $csrf_name,
                    'csrf_hash' => $csrf_hash,
                ));
                return;
            }

            $updated = $this->attendance_model->apply_mapping_to_logs($machine_user_id, $admission_no);
            if (!$updated) {
                echo json_encode(array(
                    'status' => 'error',
                    'message' => 'Mapping saved, but failed to update historical logs',
                    'csrf_token_name' => $csrf_name,
                    'csrf_hash' => $csrf_hash,
                ));
                return;
            }

            echo json_encode(array(
                'status' => 'success',
                'message' => 'Mapping saved and logs updated successfully',
                'student_name' => trim($student->firstname . ' ' . $student->lastname),
                'class' => isset($student->class) ? $student->class : '',
                'section' => isset($student->section) ? $student->section : '',
                'csrf_token_name' => $csrf_name,
                'csrf_hash' => $csrf_hash,
            ));
        } catch (Throwable $e) {
            $this->attendance_model->capture_zkteco_error('map_unmapped_log_exception', $e->getMessage(), array(
                'machine_user_id' => $this->input->post('machine_user_id'),
                'admission_no' => $this->input->post('admission_no'),
                'staff_id' => $this->input->post('staff_id'),
                'target_type' => $this->input->post('target_type')
            ));
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Unexpected error while mapping log',
                'csrf_token_name' => $csrf_name,
                'csrf_hash' => $csrf_hash,
            ));
        }
    }

    /**
     * Auto map unmapped logs with advanced matching rules for a specific date.
     */
    public function auto_map_unmapped_logs() {
        if (!$this->rbac->hasPrivilege('student_attendance', 'can_edit')) {
            echo json_encode(array('status' => 'error', 'message' => 'Access denied'));
            return;
        }

        $date = $this->input->post('date') ?: date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            echo json_encode(array('status' => 'error', 'message' => 'Invalid date format'));
            return;
        }

        try {
            $stats = $this->attendance_model->auto_map_unmapped_logs_by_date($date);
            echo json_encode(array(
                'status' => 'success',
                'message' => 'Auto mapping completed',
                'stats' => $stats
            ));
        } catch (Exception $e) {
            $this->attendance_model->capture_zkteco_error('auto_map_unmapped_logs_exception', $e->getMessage(), array('date' => $date));
            echo json_encode(array('status' => 'error', 'message' => 'Auto mapping failed due to an unexpected error'));
        }
    }
    
    /**
     * Delete student or staff mapping (AJAX)
     */
    public function delete_mapping() {
        $this->output->set_content_type('application/json');
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_hash = $this->security->get_csrf_hash();

        // Check permission
        if (!$this->rbac->hasPrivilege('student_attendance', 'can_delete')) {
            echo json_encode(array('status' => 'error', 'message' => 'Access denied', 'csrf_token_name' => $csrf_name, 'csrf_hash' => $csrf_hash));
            return;
        }

        try {
            $id = (int) $this->input->post('id');
            $target_type = trim((string) $this->input->post('target_type'));
            if ($target_type !== 'staff') {
                $target_type = 'student';
            }

            if ($id <= 0) {
                echo json_encode(array('status' => 'error', 'message' => 'Missing mapping ID', 'csrf_token_name' => $csrf_name, 'csrf_hash' => $csrf_hash));
                return;
            }

            if ($target_type === 'staff') {
                $result = method_exists($this->attendance_model, 'delete_staff_mapping')
                    ? $this->attendance_model->delete_staff_mapping($id)
                    : $this->deleteStaffMappingFallback($id);
            } else {
                $result = $this->attendance_model->delete_student_mapping($id);
            }

            if ($result) {
                echo json_encode(array('status' => 'success', 'message' => 'Mapping deleted successfully', 'csrf_token_name' => $csrf_name, 'csrf_hash' => $csrf_hash));
            } else {
                $db_error = $this->db->error();
                $message = 'Failed to delete mapping';
                if (method_exists($this->attendance_model, 'get_last_error') && $this->attendance_model->get_last_error() !== '') {
                    $message .= ': ' . $this->attendance_model->get_last_error();
                } elseif (!empty($db_error['message'])) {
                    $message .= ': ' . $db_error['message'];
                }
                echo json_encode(array('status' => 'error', 'message' => $message, 'csrf_token_name' => $csrf_name, 'csrf_hash' => $csrf_hash));
            }
        } catch (Throwable $e) {
            log_message('error', 'Attendance delete_mapping error: ' . $e->getMessage());
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Delete failed: ' . $e->getMessage(),
                'csrf_token_name' => $csrf_name,
                'csrf_hash' => $csrf_hash,
            ));
        }
    }

    /** Admin-only JSON diagnostics for the realtime collector pipeline. */
    public function realtime_diagnostics() {
        $this->output->set_content_type('application/json');
        if (!$this->rbac->hasPrivilege('student_attendance', 'can_view')) {
            echo json_encode(array('status' => 'error', 'message' => 'Access denied'));
            return;
        }
        echo json_encode(array('status' => 'success', 'data' => $this->attendance_model->get_realtime_diagnostics()));
    }

    private function deleteStaffMappingFallback($id) {
        $id = (int) $id;
        if ($id <= 0) {
            return false;
        }

        if (!$this->db->table_exists('zkteco_staff_map')) {
            return false;
        }

        return $this->db->delete('zkteco_staff_map', array('id' => $id));
    }
    
    /**
     * Manage ZKTeco devices
     */
    public function manage_devices() {
        // Check permission (admin only)
        if (!$this->rbac->hasPrivilege('student_attendance', 'can_edit')) {
            access_denied();
        }

        $this->session->set_userdata('sub_menu', 'admin/attendance/manage_devices');
        
        $data['title'] = 'Manage ZKTeco Devices';
        
        // Get all devices
        $data['devices'] = $this->attendance_model->get_devices(null, false);
        
        // Load views
        $this->load->view('layout/header', $data);
        $this->load->view('admin/attendance/manage_devices', $data);
        $this->load->view('layout/footer', $data);
    }
    
    /**
     * Save device configuration (AJAX)
     */
    public function save_device() {
        $this->output->set_content_type('application/json');
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_hash = $this->security->get_csrf_hash();

        // Check permission
        if (!$this->rbac->hasPrivilege('student_attendance', 'can_edit')) {
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Access denied',
                'csrf_token_name' => $csrf_name,
                'csrf_hash' => $csrf_hash,
            ));
            return;
        }

        try {
            $data = array(
                'id' => $this->input->post('id'),
                'device_name' => $this->input->post('device_name'),
                'device_ip' => $this->input->post('device_ip'),
                'device_port' => $this->input->post('device_port'),
                'protocol' => $this->input->post('protocol'),
                'is_active' => $this->input->post('is_active') ? 1 : 0
            );

            $result = $this->attendance_model->save_device($data);

            if ($result) {
                echo json_encode(array(
                    'status' => 'success',
                    'message' => 'Device saved successfully',
                    'device_id' => $result,
                    'csrf_token_name' => $csrf_name,
                    'csrf_hash' => $csrf_hash,
                ));
            } else {
                $error_message = $this->attendance_model->get_last_error();
                if ($error_message === '') {
                    $error_message = 'Failed to save device';
                }
                echo json_encode(array(
                    'status' => 'error',
                    'message' => $error_message,
                    'csrf_token_name' => $csrf_name,
                    'csrf_hash' => $csrf_hash,
                ));
            }
        } catch (Throwable $e) {
            log_message('error', 'Attendance save_device error: ' . $e->getMessage());
            echo json_encode(array(
                'status' => 'error',
                'message' => $e->getMessage(),
                'csrf_token_name' => $csrf_name,
                'csrf_hash' => $csrf_hash,
            ));
        }
    }
    
    /**
     * Test device connection (AJAX)
     */
    public function test_connection() {
        $this->output->set_content_type('application/json');
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_hash = $this->security->get_csrf_hash();
        $device_ip = $this->input->post('device_ip');
        $device_port = $this->input->post('device_port') ?: 4370;
        $protocol = $this->input->post('protocol') ?: 'TCP'; // Default to TCP to match test.php
        
        if (!$device_ip) {  
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Device IP is required',
                'csrf_token_name' => $csrf_name,
                'csrf_hash' => $csrf_hash,
            ));
            return;
        }
        
        try {
            // Test connection (with retry logic inside library)
            $connected = $this->zkteco_lib->connect($device_ip, $device_port, $protocol);
            
            if ($connected) {
                // Get device info
                $version = $this->zkteco_lib->get_version();
                $time = $this->zkteco_lib->get_time();
                
                $this->zkteco_lib->disconnect();
                
                echo json_encode(array(
                    'status' => 'success',
                    'message' => 'Connection successful',
                    'version' => $version,
                    'device_time' => $time,
                    'csrf_token_name' => $csrf_name,
                    'csrf_hash' => $csrf_hash,
                ));
            } else {
                // More detailed error message
                $error_msg = "Failed to connect to device at {$device_ip}:{$device_port}.\n\n";
                $error_msg .= "Troubleshooting steps:\n";
                $error_msg .= "1. Verify IP address is correct (ping {$device_ip})\n";
                $error_msg .= "2. Check port (default 4370)\n";
                $error_msg .= "3. Try switching protocol (TCP/UDP)\n";
                $error_msg .= "4. Ensure device is powered on\n";
                $error_msg .= "5. Check firewall settings\n";
                $error_msg .= "6. Verify device network settings\n";
                $error_msg .= "7. Some devices require COM key - check device settings\n";
                $error_msg .= "\nNote: Socket connects but device may not respond to handshake.\n";
                $error_msg .= "Check application logs for detailed error messages.";
                
                echo json_encode(array(
                    'status' => 'error',
                    'message' => $error_msg,
                    'csrf_token_name' => $csrf_name,
                    'csrf_hash' => $csrf_hash,
                ));
            }
        } catch (Exception $e) {
            log_message('error', 'Attendance test_connection Exception: ' . $e->getMessage());
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Connection error (Exception): ' . $e->getMessage() . "\n\nCheck application logs for details.",
                'csrf_token_name' => $csrf_name,
                'csrf_hash' => $csrf_hash,
            ));
        } catch (Error $e) {
            log_message('error', 'Attendance test_connection Fatal Error: ' . $e->getMessage());
            echo json_encode(array(
                'status' => 'error',
                'message' => 'Connection error (Fatal): ' . $e->getMessage() . "\n\nCheck application logs for details.",
                'csrf_token_name' => $csrf_name,
                'csrf_hash' => $csrf_hash,
            ));
        }
    }
    
    /**
     * Get latest attendance logs (AJAX for auto-refresh)
     */
    public function get_latest_logs() {
        $date = $this->input->get('date') ?: date('Y-m-d');
        $last_id = $this->input->get('last_id') ?: 0;
        
        // Get logs newer than last_id
        $this->db->select('al.*, s.firstname, s.lastname, c.class, sec.section, st.name as staff_name, st.surname as staff_surname, st.employee_id, roles.name as staff_role')
                 ->from('attendance_logs al')
                 ->join('students s', 's.admission_no = al.admission_no', 'left')
                 ->join('student_session ss', 'ss.student_id = s.id', 'left')
                 ->join('classes c', 'c.id = ss.class_id', 'left')
                 ->join('sections sec', 'sec.id = ss.section_id', 'left')
                 ->join('staff st', 'st.id = al.staff_id', 'left')
                 ->join('staff_roles sr', 'sr.staff_id = st.id', 'left')
                 ->join('roles', 'roles.id = sr.role_id', 'left')
                 ->where('DATE(al.timestamp)', $date)
                 ->where('al.id >', $last_id)
                 ->order_by('al.timestamp', 'DESC');
        
        $query = $this->db->get();
        $logs = $query->result_array();
        
        echo json_encode(array(
            'status' => 'success',
            'logs' => $logs,
            'count' => count($logs)
        ));
    }
    
    /**
     * Export attendance logs to CSV
     */
    public function export_csv() {
        // Check permission
        if (!$this->rbac->hasPrivilege('student_attendance', 'can_view')) {
            access_denied();
        }
        
        $start_date = $this->input->get('start_date') ?: date('Y-m-d');
        $end_date = $this->input->get('end_date') ?: date('Y-m-d');
        
        // Get attendance logs
        $logs = $this->attendance_model->get_attendance_by_range($start_date, $end_date);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="attendance_' . $start_date . '_to_' . $end_date . '.csv"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Write CSV header
        fputcsv($output, array('ID', 'Type', 'Admission/Employee ID', 'Name', 'Class/Role', 'Section', 'Timestamp', 'Status'));
        
        // Write data rows
        foreach ($logs as $log) {
            $is_staff = isset($log['user_type']) && $log['user_type'] === 'staff';
            fputcsv($output, array(
                $log['id'],
                $is_staff ? 'Staff' : 'Student',
                $is_staff ? $log['employee_id'] : $log['admission_no'],
                $is_staff ? trim($log['staff_name'] . ' ' . $log['staff_surname']) : trim($log['firstname'] . ' ' . $log['lastname']),
                $is_staff ? $log['staff_role'] : $log['class'],
                $is_staff ? '' : $log['section'],
                $log['timestamp'],
                $log['status']
            ));
        }
        
        fclose($output);
    }
}

/* End of file Attendance.php */
/* Location: ./application/controllers/admin/Attendance.php */


