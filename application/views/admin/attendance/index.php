<?php
$attendance_logs = isset($attendance_logs) ? $attendance_logs : array();
$attendance_logs = array_values(array_filter($attendance_logs, function ($log) {
    $is_staff = isset($log['user_type']) && $log['user_type'] === 'staff';
    $is_unmapped = (strpos((string) $log['admission_no'], 'UNMAPPED_') === 0) || (empty($log['firstname']) && empty($log['staff_name']));
    return $is_staff || $is_unmapped;
}));
$unique_staff_ids = array();
foreach ($attendance_logs as $log) {
    if (isset($log['user_type']) && $log['user_type'] === 'staff') {
        $staff_key = !empty($log['staff_id']) ? $log['staff_id'] : (isset($log['employee_id']) ? $log['employee_id'] : '');
        if ($staff_key !== '') {
            $unique_staff_ids[$staff_key] = true;
        }
    }
}
?>
<!-- ZKTeco Attendance Management View -->
<style type="text/css">
    .progress-container {
        display: none;
        margin: 20px 0;
    }
    .progress {
        height: 30px;
        margin-bottom: 10px;
    }
    .progress-bar {
        transition: width 0.5s ease;
        font-size: 14px;
        line-height: 30px;
    }
    .status-message {
        padding: 10px;
        background: #f5f5f5;
        border-radius: 4px;
        margin-bottom: 10px;
    }
    .stats-box {
        padding: 15px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-bottom: 15px;
    }
    .stats-box h4 {
        margin-top: 0;
    }
    .attendance-table {
        margin-top: 20px;
    }
    .btn-import {
        font-size: 16px;
        padding: 10px 20px;
    }
    .device-select {
        margin-bottom: 15px;
    }
</style>

<div class="content-wrapper" style="min-height: 946px;">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            <i class="fa fa-clock-o"></i> <?php echo $this->lang->line('attendance'); ?> 
            <small>ZKTeco Integration</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <?php $realtime = isset($realtime_diagnostics) ? $realtime_diagnostics : array(); ?>
        <div class="row">
            <div class="col-md-12">
                <div class="alert <?php echo !empty($realtime['inactive']) ? 'alert-warning' : 'alert-success'; ?>" style="margin-bottom:15px;">
                    <i class="fa fa-heartbeat"></i> <strong>Real-time Sync:</strong>
                    <?php if (!empty($realtime['last_sync_at'])): ?>
                        Last received <?php echo htmlspecialchars($realtime['last_sync_at'], ENT_QUOTES, 'UTF-8'); ?>
                    <?php else: ?>
                        No real-time scan received yet.
                    <?php endif; ?>
                    <a class="pull-right" href="<?php echo site_url('admin/attendance/realtime_diagnostics'); ?>" target="_blank">View diagnostics</a>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Import Section -->
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-download"></i> Import Attendance from ZKTeco Device</h3>
                    </div>
                    <div class="box-body">
                        <!-- Device Selection -->
                        <div class="device-select">
                            <label>Select Device:</label>
                            <select id="device_id" class="form-control" style="width: 300px; display: inline-block;">
                                <?php if (!empty($devices)): ?>
                                    <?php foreach ($devices as $device): ?>
                                        <option value="<?php echo $device['id']; ?>">
                                            <?php echo $device['device_name']; ?> (<?php echo $device['device_ip']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="">No devices configured</option>
                                <?php endif; ?>
                            </select>
                            <a href="<?php echo site_url('admin/attendance/manage_devices'); ?>" class="btn btn-sm btn-default">
                                <i class="fa fa-cog"></i> Manage Devices
                            </a>
                        </div>

                        <!-- Import Button -->
                        <button id="btn-import" class="btn btn-success btn-import" disabled title="Import from machine is disabled">
                            <i class="fa fa-download"></i> Import From Machine
                        </button>

                        <div style="margin-top: 15px;">
                            <input type="file" id="attendance-file" accept=".json,.csv,.txt" style="display:none;">
                            <button id="btn-import-file" class="btn btn-success btn-import" type="button" <?php echo empty($devices) ? 'disabled' : ''; ?>>
                                <i class="fa fa-download"></i> Import From File
                            </button>
                        </div>

                        <!-- Progress Container -->
                        <div id="progress-container" class="progress-container">
                            <div class="status-message">
                                <strong>Status:</strong> <span id="status-text">Initializing...</span>
                            </div>
                            <div class="progress">
                                <div id="progress-bar" class="progress-bar progress-bar-striped active" role="progressbar" 
                                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">
                                    0%
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="info-box bg-aqua">
                                        <span class="info-box-icon"><i class="fa fa-list"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Total Records</span>
                                            <span class="info-box-number" id="total-records">0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-green">
                                        <span class="info-box-icon"><i class="fa fa-check"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">New Records</span>
                                            <span class="info-box-number" id="new-records">0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-yellow">
                                        <span class="info-box-icon"><i class="fa fa-copy"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Duplicates</span>
                                            <span class="info-box-number" id="duplicate-records">0</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="info-box bg-gray">
                                        <span class="info-box-icon"><i class="fa fa-cog"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Processed</span>
                                            <span class="info-box-number" id="processed-records">0</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Row -->
        <div class="row">
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-list"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Records Today</span>
                        <span class="info-box-number"><?php echo isset($stats['total_records']) ? $stats['total_records'] : 0; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Unique Staff</span>
                        <span class="info-box-number"><?php echo count($unique_staff_ids); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-calendar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Date</span>
                        <span class="info-box-number"><?php echo date('d M Y'); ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-sm-6 col-xs-12">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-cog"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Active Devices</span>
                        <span class="info-box-number"><?php echo count($devices); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Logs Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-table"></i> Today's Attendance Logs</h3>
                        <div class="box-tools pull-right">
                            <button id="btn-refresh" class="btn btn-sm btn-default">
                                <i class="fa fa-refresh"></i> Refresh
                            </button>
                            <a href="<?php echo site_url('admin/attendance/view_logs'); ?>" class="btn btn-sm btn-primary">
                                <i class="fa fa-search"></i> View All Logs
                            </a>
                            <a href="<?php echo site_url('admin/attendance/report'); ?>" class="btn btn-sm btn-info">
                                <i class="fa fa-bar-chart"></i> Attendance Report
                            </a>
                            <a href="<?php echo site_url('admin/attendance/student_report'); ?>" class="btn btn-sm btn-success">
                                <i class="fa fa-users"></i> Student Report
                            </a>
                            <a href="<?php echo site_url('admin/attendance/manage_mappings'); ?>" class="btn btn-sm btn-warning">
                                <i class="fa fa-link"></i> Manage Mappings
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive attendance-table">
                            <table id="attendance-table" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Type</th>
                                        <th>Employee/Machine ID</th>
                                        <th>Name</th>
                                        <th>Class/Role</th>
                                        <th>Section</th>
                                        <th>Timestamp</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="attendance-tbody">
                                    <?php if (!empty($attendance_logs)): ?>
                                        <?php $counter = 1; ?>
                                        <?php foreach ($attendance_logs as $log): ?>
                                            <?php
                                                $is_staff = isset($log['user_type']) && $log['user_type'] === 'staff';
                                                $person_type = $is_staff ? 'Staff' : 'Unmapped';
                                                $person_id = $is_staff ? (isset($log['employee_id']) ? $log['employee_id'] : $log['staff_id']) : $log['admission_no'];
                                                $person_name = $is_staff ? trim($log['staff_name'] . ' ' . $log['staff_surname']) : trim($log['firstname'] . ' ' . $log['lastname']);
                                                $person_image = $is_staff ? (isset($log['staff_image']) ? $log['staff_image'] : '') : (isset($log['image']) ? $log['image'] : '');
                                                $class_or_role = $is_staff ? (isset($log['staff_role']) ? $log['staff_role'] : '') : (isset($log['class']) ? $log['class'] : '');
                                                $section_value = $is_staff ? '-' : (isset($log['section']) ? $log['section'] : '');
                                            ?>
                                            <tr data-id="<?php echo $log['id']; ?>">
                                                <td><?php echo $counter++; ?></td>
                                                <td><?php echo $person_type; ?></td>
                                                <td><?php echo $person_id; ?></td>
                                                <td>
                                                    <?php if ($person_image): ?>
                                                        <img src="<?php echo base_url($person_image); ?>" class="img-circle" width="30" height="30">
                                                    <?php endif; ?>
                                                    <?php echo $person_name; ?>
                                                </td>
                                                <td><?php echo $class_or_role; ?></td>
                                                <td><?php echo $section_value; ?></td>
                                                <td><?php echo date('d M Y h:i A', strtotime($log['timestamp'])); ?></td>
                                                <td>
                                                    <span class="label label-success"><?php echo $log['status']; ?></span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No attendance records found for today</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- JavaScript for Import Functionality -->
<script type="text/javascript">
// Suppress Chrome extension errors (harmless but annoying)
window.addEventListener('error', function(e) {
    if (e.message && e.message.includes('runtime.lastError')) {
        e.preventDefault();
        return false;
    }
}, true);

// Suppress unhandled promise rejections from extensions
window.addEventListener('unhandledrejection', function(e) {
    if (e.reason && e.reason.message && e.reason.message.includes('runtime.lastError')) {
        e.preventDefault();
        return false;
    }
});

$(document).ready(function() {
    var importSessionId = null;
    var progressInterval = null;
    var lastLogId = <?php echo !empty($attendance_logs) ? $attendance_logs[0]['id'] : 0; ?>;

    // Import button click handler
    $('#btn-import').click(function() {
        return false;

        var deviceId = $('#device_id').val();
        
        if (!deviceId) {
            alert('Please select a device');
            return;
        }

        // Disable button and show progress
        $(this).prop('disabled', true);
        $('#progress-container').slideDown();
        resetProgress();

        // Start import via AJAX
        $.ajax({
            url: '<?php echo site_url("admin/attendance/start_import"); ?>',
            type: 'POST',
            data: { device_id: deviceId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' || response.status === 'warning') {
                    importSessionId = response.session_id;
                    
                    // Start polling for progress
                    progressInterval = setInterval(pollProgress, 500);
                } else {
                    showError(response.message);
                    $('#btn-import').prop('disabled', true);
                }
            },
            error: function(xhr, status, error) {
                showError('Failed to start import: ' + error);
                $('#btn-import').prop('disabled', true);
            }
        });
    });

    // Import from file click handler (single-button flow)
    $('#btn-import-file').click(function() {
        if ($(this).prop('disabled')) {
            return;
        }
        $('#attendance-file').trigger('click');
    });

    $('#attendance-file').on('change', function() {
        var fileInput = $('#attendance-file')[0];
        var selectedFile = fileInput && fileInput.files ? fileInput.files[0] : null;
        var deviceId = $('#device_id').val();

        if (!selectedFile) {
            return;
        }

        var formData = new FormData();
        formData.append('attendance_file', selectedFile);
        if (deviceId) {
            formData.append('device_id', deviceId);
        }

        $('#btn-import-file').prop('disabled', true);
        $('#btn-import').prop('disabled', true);
        $('#progress-container').slideDown();
        resetProgress();
        $('#status-text').text('Uploading and processing file...');

        $.ajax({
            url: '<?php echo site_url("admin/attendance/import_file"); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                var stats = response.stats || {};
                updateProgress({
                    progress: 100,
                    message: response.message || 'Completed',
                    total_records: stats.total || 0,
                    processed_records: (stats.inserted || 0) + (stats.duplicates || 0) + (stats.errors || 0),
                    new_records: stats.inserted || 0,
                    duplicate_records: stats.duplicates || 0
                });

                if (response.status === 'success' || response.status === 'warning') {
                    showSuccess(response.message || 'File import completed');
                    setTimeout(function() {
                        refreshAttendanceTable();
                    }, 1200);
                } else {
                    showError(response.message || 'File import failed');
                }
            },
            error: function(xhr, status, error) {
                showError('Failed to import file: ' + error);
            },
            complete: function() {
                $('#btn-import-file').prop('disabled', false);
                $('#btn-import').prop('disabled', true);
                $('#attendance-file').val('');
            }
        });
    });

    // Poll progress function
    function pollProgress() {
        if (!importSessionId) return;

        $.ajax({
            url: '<?php echo site_url("admin/attendance/get_progress"); ?>',
            type: 'GET',
            data: { session_id: importSessionId },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    updateProgress(response);

                    // Stop polling if complete
                    if (response.progress >= 100) {
                        clearInterval(progressInterval);
                        $('#btn-import').prop('disabled', true);
                        
                        // Show success message
                        showSuccess(response.message);
                        
                        // Refresh attendance table after 2 seconds
                        setTimeout(function() {
                            refreshAttendanceTable();
                        }, 2000);
                    }
                }
            }
        });
    }

    // Update progress UI
    function updateProgress(data) {
        var progress = data.progress || 0;
        
        $('#progress-bar').css('width', progress + '%')
                         .attr('aria-valuenow', progress)
                         .text(progress + '%');
        
        $('#status-text').text(data.message || 'Processing...');
        $('#total-records').text(data.total_records || 0);
        $('#processed-records').text(data.processed_records || 0);
        $('#new-records').text(data.new_records || 0);
        $('#duplicate-records').text(data.duplicate_records || 0);

        // Update progress bar color based on progress
        $('#progress-bar').removeClass('progress-bar-info progress-bar-success progress-bar-warning');
        if (progress < 30) {
            $('#progress-bar').addClass('progress-bar-info');
        } else if (progress < 90) {
            $('#progress-bar').addClass('progress-bar-warning');
        } else {
            $('#progress-bar').addClass('progress-bar-success');
        }
    }

    // Reset progress
    function resetProgress() {
        $('#progress-bar').css('width', '0%')
                         .attr('aria-valuenow', 0)
                         .text('0%')
                         .removeClass('progress-bar-success progress-bar-warning')
                         .addClass('progress-bar-info progress-bar-striped active');
        
        $('#status-text').text('Initializing...');
        $('#total-records').text('0');
        $('#processed-records').text('0');
        $('#new-records').text('0');
        $('#duplicate-records').text('0');
    }

    // Show success message
    function showSuccess(message) {
        $('#status-text').html('<i class="fa fa-check-circle text-success"></i> ' + message);
        $('#progress-bar').removeClass('active');
    }

    // Show error message
    function showError(message) {
        $('#status-text').html('<i class="fa fa-exclamation-circle text-danger"></i> ' + message);
        $('#progress-bar').removeClass('active progress-bar-striped')
                         .addClass('progress-bar-danger');
        $('#progress-container').slideDown();
    }

    // Refresh attendance table
    $('#btn-refresh').click(function() {
        refreshAttendanceTable();
    });

    function refreshAttendanceTable() {
        location.reload();
    }

    // Auto-refresh for new logs every 30 seconds
    setInterval(function() {
        checkNewLogs();
    }, 30000);

    function checkNewLogs() {
        $.ajax({
            url: '<?php echo site_url("admin/attendance/get_latest_logs"); ?>',
            type: 'GET',
            data: { 
                date: '<?php echo date("Y-m-d"); ?>',
                last_id: lastLogId
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.count > 0) {
                    // Prepend new logs to table
                    var tbody = $('#attendance-tbody');
                    var currentRows = tbody.find('tr').length;
                    
                    $.each(response.logs, function(index, log) {
                        var isStaff = log.user_type === 'staff';
                        var isUnmapped = ((log.admission_no || '').indexOf('UNMAPPED_') === 0) || (!log.firstname && !log.staff_name);
                        if (!isStaff && !isUnmapped) {
                            return true;
                        }
                        var personType = isStaff ? 'Staff' : 'Unmapped';
                        var personId = isStaff ? (log.employee_id || log.staff_id || '') : (log.admission_no || '');
                        var personName = isStaff ? ((log.staff_name || '') + ' ' + (log.staff_surname || '')) : ((log.firstname || '') + ' ' + (log.lastname || ''));
                        var classOrRole = isStaff ? (log.staff_role || '') : (log.class || '');
                        var sectionValue = isStaff ? '-' : (log.section || '');
                        var row = '<tr data-id="' + log.id + '" class="new-row">' +
                            '<td>' + (currentRows + index + 1) + '</td>' +
                            '<td>' + personType + '</td>' +
                            '<td>' + personId + '</td>' +
                            '<td>' + personName + '</td>' +
                            '<td>' + classOrRole + '</td>' +
                            '<td>' + sectionValue + '</td>' +
                            '<td>' + formatDateTime(log.timestamp) + '</td>' +
                            '<td><span class="label label-success">' + log.status + '</span></td>' +
                            '</tr>';
                        
                        tbody.prepend(row);
                        lastLogId = Math.max(lastLogId, log.id);
                    });

                    // Highlight new rows
                    $('.new-row').css('background-color', '#d4edda').delay(3000).queue(function() {
                        $(this).css('background-color', '').removeClass('new-row').dequeue();
                    });
                }
            }
        });
    }

    function formatDateTime(datetime) {
        var date = new Date(datetime);
        return date.toLocaleString('en-US', { 
            day: '2-digit', 
            month: 'short', 
            year: 'numeric', 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
    }

    // Initialize DataTable if available
    if ($.fn.DataTable) {
        $('#attendance-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "pageLength": 25,
            "order": [[0, "desc"]]
        });
    }
});
</script>

