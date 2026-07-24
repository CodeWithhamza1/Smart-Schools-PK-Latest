<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * ZKTeco Library for CodeIgniter
 * 
 * This library wraps the ZKLibrary functionality for easy integration
 * with CodeIgniter framework and school management system.
 * 
 * @package    School Management System
 * @subpackage Libraries
 * @category   Attendance
 * @author     AI Assistant
 * @version    1.0
 */

// Include the ZKLibrary from the first valid supported location.
if (!class_exists('ZKLibrary', false)) {
    $zkteco_candidates = array(
        FCPATH . 'Attendance_ZKLIBRARY/zklibrary.php',
        APPPATH . 'third_party/Attendance_ZKLIBRARY/zklibrary.php',
        APPPATH . 'libraries/Attendance_ZKLIBRARY/zklibrary.php',
        dirname(APPPATH) . '/Attendance_ZKLIBRARY/zklibrary.php',
    );

    foreach ($zkteco_candidates as $zkteco_candidate) {
        if (is_file($zkteco_candidate)) {
            require_once $zkteco_candidate;
            break;
        }
    }

    if (!class_exists('ZKLibrary', false)) {
        $message = 'Unable to locate ZKTeco dependency file: zklibrary.php';
        if (function_exists('log_message')) {
            log_message('error', $message . ' | checked=' . implode(', ', $zkteco_candidates));
        }
        if (function_exists('show_error')) {
            show_error($message . '. Upload Attendance_ZKLIBRARY to project root or application/third_party.');
        }
        exit($message);
    }
}

class Zkteco_lib {
    
    protected $CI;
    protected $zk;
    protected $device_ip;
    protected $device_port;
    protected $protocol;
    protected $is_connected = false;
    
    /**
     * Constructor
     * 
     * Initializes the library and loads CodeIgniter instance
     */
    public function __construct($params = array()) {
        $this->CI =& get_instance();
        $this->CI->load->database();
        
        // Load default device configuration from database or config
        if (!empty($params)) {
            $this->device_ip = isset($params['ip']) ? $params['ip'] : null;
            $this->device_port = isset($params['port']) ? $params['port'] : 4370;
            $this->protocol = isset($params['protocol']) ? $params['protocol'] : 'UDP';
        }
        
        log_message('info', 'ZKTeco Library Initialized');
    }
    
    /**
     * Load device configuration from database
     * 
     * @param int $device_id Device ID from zkteco_devices table
     * @return bool Success status
     */
    public function load_device($device_id = null) {
        try {
            $query = $this->CI->db->select('device_ip, device_port, protocol')
                                  ->from('zkteco_devices')
                                  ->where('is_active', 1);
            
            if ($device_id) {
                $query->where('id', $device_id);
            }
            
            $device = $query->get()->row();
            
            if ($device) {
                $this->device_ip = $device->device_ip;
                $this->device_port = $device->device_port;
                $this->protocol = $device->protocol;
                return true;
            }
            
            log_message('error', 'ZKTeco: No active device found in database');
            return false;
            
        } catch (Exception $e) {
            log_message('error', 'ZKTeco: Error loading device - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Connect to ZKTeco device with retry logic
     * 
     * @param string $ip Device IP address (optional if already set)
     * @param int $port Device port (optional if already set)
     * @param string $protocol Connection protocol (UDP/TCP)
     * @param int $max_retries Maximum connection retry attempts
     * @return bool Connection status
     */
    public function connect($ip = null, $port = null, $protocol = null, $max_retries = 3) {
        try {
            // Use provided parameters or fall back to stored values
            $ip = $ip ?: $this->device_ip;
            $port = $port ?: $this->device_port;
            $protocol = $protocol ?: $this->protocol;
            
            if (!$ip) {
                log_message('error', 'ZKTeco: Device IP not provided');
                return false;
            }
            
            if (!$port) {
                $port = 4370; // Default port
            }
            
            if (!$protocol) {
                // Default to TCP to match ZKTeco test script and common device config
                $protocol = 'TCP';
            }

            // Normalize protocol string
            $protocol = strtoupper($protocol) === 'TCP' ? 'TCP' : 'UDP';

            log_message('info', "ZKTeco: Preparing connection to {$ip}:{$port} using protocol {$protocol}");
            
            // Clean up any existing connection
            if ($this->zk && $this->is_connected) {
                try {
                    $this->disconnect();
                } catch (Exception $e) {
                    // Ignore cleanup errors
                }
            }
            
            // Log connection attempt details
            log_message('info', "ZKTeco: Preparing connection to {$ip}:{$port} using protocol {$protocol}");
            
            // Retry connection logic
            $attempt = 0;
            $connected = false;
            $last_error = '';
            $original_protocol = $protocol; // Store original protocol for fallback
            
            while ($attempt < $max_retries && !$connected) {
                $attempt++;
                
                try {
                    // IMPORTANT: For TCP, ZKLibrary constructor calls socket_connect() immediately
                    // This can throw an exception if the device is unreachable
                    try {
                        // Initialize ZKLibrary (TCP will try to connect in constructor)
                        $this->zk = new ZKLibrary($ip, $port, $protocol);
                    } catch (Exception $constructor_exception) {
                        // TCP connection in constructor failed
                        $last_error = $constructor_exception->getMessage();
                        $socket_errno = socket_last_error();
                        $socket_error = $socket_errno ? socket_strerror($socket_errno) : 'Unknown socket error';
                        log_message('error', "ZKTeco: Connection attempt {$attempt} - Constructor failed: {$last_error} (Socket Error #{$socket_errno}: {$socket_error})");
                        
                        // Try alternate protocol on first constructor failure
                        if ($attempt == 1 && $protocol == $original_protocol) {
                            $fallback_protocol = ($protocol == 'TCP') ? 'UDP' : 'TCP';
                            log_message('info', "ZKTeco: {$protocol} constructor failed, trying {$fallback_protocol} as fallback...");
                            $protocol = $fallback_protocol;
                            $this->zk = null;
                            if ($attempt < $max_retries) {
                                usleep(1000000); // 1 second delay
                            }
                            continue; // Retry with alternate protocol
                        }
                        
                        // Clean up and wait before retry
                        if ($this->zk) {
                            try {
                                if (method_exists($this->zk, 'disconnect')) {
                                    $this->zk->disconnect();
                                }
                            } catch (Exception $e) {
                                // Ignore
                            }
                            unset($this->zk);
                            $this->zk = null;
                        }
                        
                        if ($attempt < $max_retries) {
                            usleep(1000000); // 1 second delay for TCP connection failures
                        }
                        continue;
                    } catch (Error $constructor_error) {
                        // Fatal error in constructor
                        $last_error = $constructor_error->getMessage();
                        log_message('error', "ZKTeco: Connection attempt {$attempt} - Constructor fatal error: {$last_error}");
                        
                        if ($this->zk) {
                            try {
                                unset($this->zk);
                            } catch (Exception $e2) {
                                // Ignore
                            }
                            $this->zk = null;
                        }
                        
                        if ($attempt < $max_retries) {
                            usleep(1000000);
                        }
                        continue;
                    }
                    
                    // Set timeout for connection (only for UDP, TCP timeout is set in constructor)
                    if ($protocol == 'UDP' && method_exists($this->zk, 'setTimeout')) {
                        $this->zk->setTimeout(10, 0); // 10 seconds to match zklibrary.php
                    }
                    
                    // Attempt connection (for UDP, this sends handshake; for TCP, socket is already connected)
                    $connected = $this->zk->connect();
                    
                    if (!$connected && $protocol == 'TCP') {
                        // Some devices do not ACK connect consistently on TCP but still answer commands.
                        // Probe with lightweight read commands before marking connection as failed.
                        if ($this->probe_tcp_connection()) {
                            $connected = true;
                            log_message('info', "ZKTeco: TCP handshake probe succeeded for {$ip}:{$port} on attempt {$attempt}");
                        }
                    }

                    if ($connected) {
                        $this->is_connected = true;
                        log_message('info', "ZKTeco: Successfully connected to device at {$ip}:{$port} using {$protocol} on attempt {$attempt}");
                        return true;
                    } else {
                        $last_error = "Device did not respond to handshake";
                        log_message('error', "ZKTeco: Connection attempt {$attempt} failed for {$ip}:{$port} using {$protocol} - {$last_error}");
                        
                        // Try alternate protocol on first handshake failure
                        if ($attempt == 1 && $protocol == $original_protocol) {
                            $fallback_protocol = ($protocol == 'TCP') ? 'UDP' : 'TCP';
                            log_message('info', "ZKTeco: {$protocol} handshake failed, trying {$fallback_protocol} as fallback...");
                            
                            // Clean up TCP connection
                            if ($this->zk) {
                                try {
                                    if (method_exists($this->zk, 'disconnect')) {
                                        $this->zk->disconnect();
                                    }
                                } catch (Exception $e) {
                                    // Ignore
                                }
                                try {
                                    unset($this->zk);
                                } catch (Exception $e) {
                                    // Ignore
                                }
                                $this->zk = null;
                            }
                            
                            // Switch protocol and retry
                            $protocol = $fallback_protocol;
                            usleep(500000); // 0.5 second delay
                            continue; // Retry with alternate protocol
                        }
                        
                        // Clean up failed connection
                        if ($this->zk) {
                            try {
                                if (method_exists($this->zk, 'disconnect')) {
                                    $this->zk->disconnect();
                                }
                            } catch (Exception $e) {
                                // Ignore
                            }
                            try {
                                unset($this->zk);
                            } catch (Exception $e) {
                                // Ignore
                            }
                            $this->zk = null;
                        }
                        
                        // Wait before retry (except on last attempt)
                        if ($attempt < $max_retries) {
                            usleep(1000000); // 1 second delay
                        }
                    }
                    
                } catch (Exception $e) {
                    $last_error = $e->getMessage();
                    $socket_errno = socket_last_error();
                    $socket_error = $socket_errno ? socket_strerror($socket_errno) : 'Unknown socket error';
                    log_message('error', "ZKTeco: Connection attempt {$attempt} exception - {$last_error} (Socket Error #{$socket_errno}: {$socket_error})");
                    
                    // Clean up on exception
                    if ($this->zk) {
                        try {
                            if (method_exists($this->zk, 'disconnect')) {
                                $this->zk->disconnect();
                            }
                        } catch (Exception $e2) {
                            // Ignore
                        }
                        try {
                            unset($this->zk);
                        } catch (Exception $e2) {
                            // Ignore
                        }
                        $this->zk = null;
                    }
                    
                    if ($attempt < $max_retries) {
                        usleep(1000000); // 1 second delay before retry
                    }
                } catch (Error $e) {
                    $last_error = $e->getMessage();
                    log_message('error', "ZKTeco: Connection attempt {$attempt} fatal error - {$last_error}");
                    
                    if ($this->zk) {
                        try {
                            unset($this->zk);
                        } catch (Exception $e2) {
                            // Ignore
                        }
                        $this->zk = null;
                    }
                    
                    if ($attempt < $max_retries) {
                        usleep(1000000);
                    }
                }
            }
            
            // Log final failure with details
            log_message('error', "ZKTeco: Failed to connect to device at {$ip}:{$port} using {$protocol} after {$max_retries} attempts. Last error: {$last_error}");
            
            log_message('error', "ZKTeco: Failed to connect to device at {$ip}:{$port} after {$max_retries} attempts");
            return false;
            
        } catch (Exception $e) {
            log_message('error', 'ZKTeco: Connection error - ' . $e->getMessage());
            $this->is_connected = false;
            $this->zk = null;
            return false;
        } catch (Error $e) {
            log_message('error', 'ZKTeco: Fatal connection error - ' . $e->getMessage());
            $this->is_connected = false;
            $this->zk = null;
            return false;
        }
    }
    
    /**
     * Disconnect from ZKTeco device with proper cleanup
     * 
     * @return bool Disconnect status
     */
    public function disconnect() {
        try {
            if ($this->is_connected && $this->zk) {
                // Try to enable device before disconnecting
                try {
                    if (method_exists($this->zk, 'enableDevice')) {
                        $this->zk->enableDevice();
                    }
                } catch (Exception $e) {
                    log_message('error', 'ZKTeco: Error enabling device before disconnect - ' . $e->getMessage());
                }
                
                // Disconnect
                try {
                    if (method_exists($this->zk, 'disconnect')) {
                        $this->zk->disconnect();
                    }
                } catch (Exception $e) {
                    log_message('error', 'ZKTeco: Error during disconnect - ' . $e->getMessage());
                }
                
                $this->is_connected = false;
                $this->zk = null;
                log_message('info', 'ZKTeco: Device disconnected');
                return true;
            }
            
            // Clean up even if not marked as connected
            if ($this->zk) {
                try {
                    unset($this->zk);
                } catch (Exception $e) {
                    // Ignore
                }
                $this->zk = null;
            }
            
            $this->is_connected = false;
            return true;
            
        } catch (Exception $e) {
            log_message('error', 'ZKTeco: Disconnect error - ' . $e->getMessage());
            $this->is_connected = false;
            $this->zk = null;
            return false;
        } catch (Error $e) {
            log_message('error', 'ZKTeco: Fatal disconnect error - ' . $e->getMessage());
            $this->is_connected = false;
            $this->zk = null;
            return false;
        }
    }
    
    /**
     * Check if device is connected
     * 
     * @return bool Connection status
     */
    public function is_connected() {
        return $this->is_connected;
    }
    
    /**
     * Disable device (prevent new attendance records during sync)
     * 
     * @return bool Success status
     */
    public function disable_device() {
        try {
            if ($this->is_connected && $this->zk) {
                return $this->zk->disableDevice();
            }
            return false;
        } catch (Exception $e) {
            log_message('error', 'ZKTeco: Error disabling device - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Enable device (allow new attendance records)
     * 
     * @return bool Success status
     */
    public function enable_device() {
        try {
            if ($this->is_connected && $this->zk) {
                return $this->zk->enableDevice();
            }
            return false;
        } catch (Exception $e) {
            log_message('error', 'ZKTeco: Error enabling device - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all users from device
     * 
     * @return array|bool Array of users or false on failure
     */
    public function get_users() {
        try {
            if (!$this->is_connected) {
                log_message('error', 'ZKTeco: Not connected to device');
                return false;
            }
            
            $users = $this->zk->getUser();
            
            if ($users === false) {
                log_message('error', 'ZKTeco: Failed to retrieve users');
                return false;
            }
            
            log_message('info', 'ZKTeco: Retrieved ' . count($users) . ' users from device');
            return $users;
            
        } catch (Exception $e) {
            log_message('error', 'ZKTeco: Error getting users - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get attendance logs from device with error handling
     * 
     * @return array|bool Array of attendance records or false on failure
     */
    public function get_attendance() {
        try {
            if (!$this->is_connected) {
                log_message('error', 'ZKTeco: Not connected to device');
                return false;
            }
            
            if (!$this->zk) {
                log_message('error', 'ZKTeco: ZKLibrary object not initialized');
                return false;
            }
            
            // Get attendance logs from device
            $attendance = $this->zk->getAttendance();
            
            // Handle different return types
            if ($attendance === false || $attendance === null) {
                log_message('error', 'ZKTeco: Device returned false/null for attendance');
                return array(); // Return empty array instead of false
            }
            
            // Ensure it's an array
            if (!is_array($attendance)) {
                log_message('error', 'ZKTeco: Attendance data is not an array, type: ' . gettype($attendance));
                return array();
            }
            
            // Validate attendance record structure
            $valid_attendance = array();
            foreach ($attendance as $index => $record) {
                // Check if record has required structure [uid, id, state, timestamp]
                if (is_array($record) && count($record) >= 4) {
                    // Validate timestamp format
                    if (isset($record[3]) && !empty($record[3])) {
                        $valid_attendance[] = $record;
                    } else {
                        log_message('debug', "ZKTeco: Skipping invalid record at index {$index} - missing timestamp");
                    }
                } else {
                    log_message('debug', "ZKTeco: Skipping invalid record at index {$index} - invalid structure");
                }
            }
            
            $count = count($valid_attendance);
            log_message('info', "ZKTeco: Retrieved {$count} valid attendance records from device (total: " . count($attendance) . ")");
            
            return $valid_attendance;
            
        } catch (Exception $e) {
            log_message('error', 'ZKTeco: Error getting attendance - ' . $e->getMessage());
            log_message('error', 'ZKTeco: Stack trace - ' . $e->getTraceAsString());
            return array(); // Return empty array instead of false
        } catch (Error $e) {
            log_message('error', 'ZKTeco: Fatal error getting attendance - ' . $e->getMessage());
            return array();
        }
    }
    
    /**
     * Clear attendance logs from device
     * 
     * @return bool Success status
     */
    public function clear_attendance() {
        try {
            if (!$this->is_connected) {
                log_message('error', 'ZKTeco: Not connected to device');
                return false;
            }
            
            $result = $this->zk->clearAttendance();
            
            if ($result) {
                log_message('info', 'ZKTeco: Attendance logs cleared from device');
            }
            
            return $result;
            
        } catch (Exception $e) {
            log_message('error', 'ZKTeco: Error clearing attendance - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get device version information
     * 
     * @return string|bool Version string or false on failure
     */
    public function get_version() {
        try {
            if (!$this->is_connected) {
                return false;
            }
            return $this->zk->getVersion();
        } catch (Exception $e) {
            log_message('error', 'ZKTeco: Error getting version - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get device time
     * 
     * @return string|bool Device time or false on failure
     */
    public function get_time() {
        try {
            if (!$this->is_connected) {
                return false;
            }
            return $this->zk->getTime();
        } catch (Exception $e) {
            log_message('error', 'ZKTeco: Error getting time - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Set device time
     * 
     * @param string $time Time in format 'Y-m-d H:i:s'
     * @return bool Success status
     */
    public function set_time($time) {
        try {
            if (!$this->is_connected) {
                return false;
            }
            return $this->zk->setTime($time);
        } catch (Exception $e) {
            log_message('error', 'ZKTeco: Error setting time - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ping device to check connectivity
     * 
     * @param int $timeout Timeout in seconds
     * @return mixed Ping time in ms or 'down' if unreachable
     */
    public function ping($timeout = 1) {
        try {
            if (!$this->device_ip) {
                return 'down';
            }
            
            $zk_temp = new ZKLibrary($this->device_ip, $this->device_port, $this->protocol);
            return $zk_temp->ping($timeout);
            
        } catch (Exception $e) {
            log_message('error', 'ZKTeco: Ping error - ' . $e->getMessage());
            return 'down';
        }
    }
    
    /**
     * Format attendance data for database insertion
     * 
     * @param array $attendance_raw Raw attendance data from device
     * @param array $user_map User ID to admission number mapping
     * @param array $staff_map User ID to staff ID mapping
     * @return array Formatted attendance records
     */
    public function format_attendance($attendance_raw, $user_map = array(), &$format_stats = null, $staff_map = array()) {
        $formatted = array();
        
        if (empty($attendance_raw) || !is_array($attendance_raw)) {
            log_message('error', 'ZKTeco: No attendance data to format');
            return $formatted;
        }
        
        $mapped_count = 0;
        $unmapped_count = 0;
        $skipped_invalid_structure = 0;
        $skipped_missing_fields = 0;
        $skipped_invalid_machine = 0;
        $skipped_invalid_timestamp = 0;
        $max_allowed_ts = strtotime('+1 day');
        $min_allowed_ts = strtotime('2015-01-01 00:00:00');
        $mapping_cache = array();
        $staff_mapping_cache = array();
        if (!empty($user_map) && is_array($user_map)) {
            foreach ($user_map as $machine_key => $admission_value) {
                $mk = trim((string) $machine_key);
                if ($mk !== '') {
                    $mapping_cache[$mk] = trim((string) $admission_value);
                }
            }
        }
        if (!empty($staff_map) && is_array($staff_map)) {
            foreach ($staff_map as $machine_key => $staff_value) {
                $mk = trim((string) $machine_key);
                if ($mk !== '') {
                    $staff_mapping_cache[$mk] = (int) $staff_value;
                }
            }
        }
        
        foreach ($attendance_raw as $index => $record) {
            try {
                // Validate record structure
                if (!is_array($record) || count($record) < 4) {
                    $skipped_invalid_structure++;
                    continue;
                }
                
                // Record format: [uid, id, state, timestamp]
                $uid = isset($record[0]) ? trim((string) $record[0]) : '';
                $machine_id = isset($record[1]) ? trim($record[1]) : '';
                $state = isset($record[2]) ? intval($record[2]) : 0;
                $timestamp = isset($record[3]) ? trim($record[3]) : '';

                // Some devices send blank "id" but valid UID. Use UID as machine ID fallback.
                if ($machine_id === '' && $uid !== '') {
                    $machine_id = $uid;
                }

                // Keep only readable identifier characters; fallback to UID if needed.
                $machine_id = preg_replace('/[^A-Za-z0-9_\\-]/', '', (string) $machine_id);
                if ($machine_id === '' && $uid !== '') {
                    $uid_clean = preg_replace('/[^0-9]/', '', (string) $uid);
                    if ($uid_clean !== '') {
                        $machine_id = $uid_clean;
                    }
                }

                // Validate required fields
                if (empty($machine_id) || empty($timestamp)) {
                    $skipped_missing_fields++;
                    continue;
                }
                
                // Validate timestamp format (should be Y-m-d H:i:s)
                if (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $timestamp)) {
                    // Try to fix common timestamp issues
                    $timestamp = $this->normalize_timestamp($timestamp);
                    if (!$timestamp) {
                        $skipped_invalid_timestamp++;
                        continue; // Skip if can't normalize
                    }
                }

                $parsed_ts = strtotime($timestamp);
                if ($parsed_ts === false || $parsed_ts < $min_allowed_ts || $parsed_ts > $max_allowed_ts) {
                    $skipped_invalid_timestamp++;
                    continue;
                }

                // Reject clearly non-human machine IDs produced by corrupted frames.
                if (!preg_match('/^[A-Za-z0-9_\\-]{1,40}$/', $machine_id)) {
                    $skipped_invalid_machine++;
                    continue;
                }
                
                // Map machine user ID to a student admission number or staff ID.
                $admission_no = null;
                $staff_id = null;
                $user_type = 'unmapped';
                
                // First try provided mapping
                if (isset($mapping_cache[$machine_id])) {
                    $admission_no = $mapping_cache[$machine_id];
                }
                
                // If not found, try database lookup
                if (!$admission_no) {
                    if (array_key_exists($machine_id, $mapping_cache)) {
                        $admission_no = $mapping_cache[$machine_id];
                    } else {
                        try {
                            $map = $this->CI->db->select('admission_no')
                                                ->from('zkteco_student_map')
                                                ->where('machine_user_id', $machine_id)
                                                ->limit(1)
                                                ->get()
                                                ->row();
                            
                            if ($map && !empty($map->admission_no)) {
                                $admission_no = $map->admission_no;
                                $mapping_cache[$machine_id] = $admission_no;
                            } else {
                                // Cache miss to avoid repetitive lookups for same machine ID.
                                $mapping_cache[$machine_id] = '';
                            }
                        } catch (Exception $e) {
                            log_message('error', 'ZKTeco: Error looking up mapping - ' . $e->getMessage());
                            $mapping_cache[$machine_id] = '';
                        }
                    }
                }

                if (!$admission_no) {
                    if (isset($staff_mapping_cache[$machine_id])) {
                        $staff_id = (int) $staff_mapping_cache[$machine_id];
                    } elseif (!array_key_exists($machine_id, $staff_mapping_cache)) {
                        try {
                            $staff_map_row = $this->CI->db->select('staff_id')
                                                          ->from('zkteco_staff_map')
                                                          ->where('machine_user_id', $machine_id)
                                                          ->limit(1)
                                                          ->get()
                                                          ->row();

                            if ($staff_map_row && (int) $staff_map_row->staff_id > 0) {
                                $staff_id = (int) $staff_map_row->staff_id;
                                $staff_mapping_cache[$machine_id] = $staff_id;
                            } else {
                                $staff_mapping_cache[$machine_id] = 0;
                            }
                        } catch (Exception $e) {
                            log_message('error', 'ZKTeco: Error looking up staff mapping - ' . $e->getMessage());
                            $staff_mapping_cache[$machine_id] = 0;
                        }
                    }
                }
                
                // Determine status based on state
                $status = $this->get_status_from_state($state);
                
                if ($staff_id) {
                    $user_type = 'staff';
                    $mapped_count++;
                } elseif ($admission_no) {
                    $user_type = 'student';
                    $mapped_count++;
                } else {
                    $unmapped_count++;
                }
                
                $formatted[] = array(
                    'uid' => $uid,
                    'admission_no' => $admission_no,
                    'staff_id' => $staff_id,
                    'user_type' => $user_type,
                    'machine_user_id' => $machine_id,
                    'timestamp' => date('Y-m-d H:i:s', $parsed_ts),
                    'status' => $status,
                    'device_id' => $this->device_ip
                );
                
            } catch (Exception $e) {
                log_message('error', "ZKTeco: Error formatting record at index {$index} - " . $e->getMessage());
                continue; // Skip this record and continue
            }
        }
        
        $format_stats = array(
            'raw_total' => count($attendance_raw),
            'formatted_total' => count($formatted),
            'mapped' => $mapped_count,
            'unmapped' => $unmapped_count,
            'skipped_invalid_structure' => $skipped_invalid_structure,
            'skipped_missing_fields' => $skipped_missing_fields,
            'skipped_invalid_machine' => $skipped_invalid_machine,
            'skipped_invalid_timestamp' => $skipped_invalid_timestamp,
            'skipped_total' => $skipped_invalid_structure + $skipped_missing_fields + $skipped_invalid_machine + $skipped_invalid_timestamp
        );

        log_message('info', 'ZKTeco: Formatted ' . count($formatted) . ' attendance records'
            . ' | mapped=' . $mapped_count
            . ' | unmapped=' . $unmapped_count
            . ' | skipped_invalid_structure=' . $skipped_invalid_structure
            . ' | skipped_missing_fields=' . $skipped_missing_fields
            . ' | skipped_invalid_machine=' . $skipped_invalid_machine
            . ' | skipped_invalid_timestamp=' . $skipped_invalid_timestamp
            . ' | skipped_total=' . $format_stats['skipped_total']
            . ' | device_ip=' . $this->device_ip);
        return $formatted;
    }
    
    /**
     * Normalize timestamp to standard format
     * 
     * @param string $timestamp Raw timestamp from device
     * @return string|bool Normalized timestamp or false on failure
     */
    private function normalize_timestamp($timestamp) {
        if (empty($timestamp)) {
            return false;
        }
        
        // Try to parse various timestamp formats
        $parsed = strtotime($timestamp);
        
        if ($parsed === false) {
            return false;
        }
        
        return date('Y-m-d H:i:s', $parsed);
    }
    
    /**
     * Convert device state to readable status
     * 
     * @param int $state Device state code
     * @return string Status text
     */
    private function get_status_from_state($state) {
        // ZKTeco state codes
        // 0 = Check In, 1 = Check Out, 2 = Break Out, 3 = Break In, 4 = Overtime In, 5 = Overtime Out
        $status_map = array(
            0 => 'Check In',
            1 => 'Check Out',
            2 => 'Break Out',
            3 => 'Break In',
            4 => 'Overtime In',
            5 => 'Overtime Out'
        );
        
        return isset($status_map[$state]) ? $status_map[$state] : 'Present';
    }

    /**
     * Probe TCP connection when connect handshake does not ACK.
     *
     * @return bool True when device responds to at least one read command.
     */
    private function probe_tcp_connection() {
        if (!$this->zk) {
            return false;
        }

        try {
            $version = $this->zk->getVersion();
            if (is_string($version) && trim($version) !== '') {
                return true;
            }
        } catch (Exception $e) {
            log_message('debug', 'ZKTeco: TCP probe getVersion failed - ' . $e->getMessage());
        } catch (Error $e) {
            log_message('debug', 'ZKTeco: TCP probe getVersion fatal - ' . $e->getMessage());
        }

        try {
            $device_time = $this->zk->getTime();
            if (is_string($device_time) && preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/', $device_time)) {
                return true;
            }
        } catch (Exception $e) {
            log_message('debug', 'ZKTeco: TCP probe getTime failed - ' . $e->getMessage());
        } catch (Error $e) {
            log_message('debug', 'ZKTeco: TCP probe getTime fatal - ' . $e->getMessage());
        }

        return false;
    }
    
    /**
     * Destructor - ensure device is disconnected
     */
    public function __destruct() {
        if ($this->is_connected) {
            $this->disconnect();
        }
    }
}

/* End of file Zkteco_lib.php */
/* Location: ./application/libraries/Zkteco_lib.php */

