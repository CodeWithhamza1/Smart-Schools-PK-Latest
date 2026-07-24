<?php
$attendance_logs = isset($attendance_logs) ? $attendance_logs : array();
$attendance_logs = array_values(array_filter($attendance_logs, function ($log) {
    $is_staff = isset($log['user_type']) && $log['user_type'] === 'staff';
    $is_unmapped = (strpos((string) $log['admission_no'], 'UNMAPPED_') === 0) || (empty($log['firstname']) && empty($log['staff_name']));
    return $is_staff || $is_unmapped;
}));
$students_for_mapping = array();
?>
<!-- View Attendance Logs with Filters -->
<div class="content-wrapper" style="min-height: 946px;">
    <!-- Content Header -->
    <section class="content-header">
        <h1>
            <i class="fa fa-list"></i> Attendance Logs
            <small>View and Filter Attendance Records</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <!-- Filter Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-filter"></i> Filter Logs</h3>
                    </div>
                    <div class="box-body">
                        <form method="get" action="<?php echo site_url('admin/attendance/view_logs'); ?>">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input type="date" name="date" class="form-control" 
                                               value="<?php echo $selected_date; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Date Type</label>
                                        <select name="date_mode" class="form-control">
                                            <option value="timestamp" <?php echo (isset($selected_date_mode) && $selected_date_mode === 'timestamp') ? 'selected' : ''; ?>>Punch Date</option>
                                            <option value="created_at" <?php echo (isset($selected_date_mode) && $selected_date_mode === 'created_at') ? 'selected' : ''; ?>>Import Date</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Employee/Machine User (Optional)</label>
                                        <input type="text" name="admission_no" class="form-control" 
                                               placeholder="Enter employee or machine user" 
                                               value="<?php echo $selected_admission; ?>">
                                    </div>
                                </div>
                                <input type="hidden" name="per_page" value="<?php echo isset($log_pagination['per_page']) ? (int) $log_pagination['per_page'] : 200; ?>">
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fa fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <a href="<?php echo site_url('admin/attendance/export_csv?start_date=' . $selected_date . '&end_date=' . $selected_date); ?>" 
                                           class="btn btn-success btn-block">
                                            <i class="fa fa-download"></i> Export CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-table"></i> Attendance Records 
                            <small>
                                (showing <?php echo count($attendance_logs); ?> staff/unmapped records)
                            </small>
                        </h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/attendance'); ?>" class="btn btn-sm btn-default">
                                <i class="fa fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php if (!empty($log_pagination) && $log_pagination['total_rows'] > $log_pagination['per_page']): ?>
                            <div class="alert alert-info">
                                Showing page <?php echo (int) $log_pagination['current_page']; ?> of <?php echo (int) $log_pagination['total_pages']; ?>. Use pagination below to move through large log days.
                            </div>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="logs-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Machine User</th>
                                        <th>Type</th>
                                        <th>Employee/Machine ID</th>
                                        <th>Name</th>
                                        <th>Class/Role</th>
                                        <th>Section</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                        <th>Map</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($attendance_logs)): ?>
                                        <?php $counter = isset($log_pagination['offset']) ? ((int) $log_pagination['offset'] + 1) : 1; ?>
                                        <?php foreach ($attendance_logs as $log): ?>
                                            <?php
                                                $is_staff = isset($log['user_type']) && $log['user_type'] === 'staff';
                                                $is_unmapped = (strpos((string)$log['admission_no'], 'UNMAPPED_') === 0) || (empty($log['firstname']) && empty($log['staff_name']));
                                                $person_type = $is_staff ? 'Staff' : 'Unmapped';
                                                $person_id = $is_staff ? (isset($log['employee_id']) ? $log['employee_id'] : $log['staff_id']) : $log['admission_no'];
                                                $person_name = $is_staff ? trim($log['staff_name'] . ' ' . $log['staff_surname']) : trim($log['firstname'] . ' ' . $log['lastname']);
                                                $person_image = $is_staff ? (isset($log['staff_image']) ? $log['staff_image'] : '') : (isset($log['student_image']) ? $log['student_image'] : '');
                                                $class_or_role = $is_staff ? (isset($log['staff_role']) ? $log['staff_role'] : '') : (isset($log['class']) ? $log['class'] : '');
                                                $section_value = $is_staff ? '-' : (isset($log['section']) ? $log['section'] : '');
                                                $status_class = 'label-default';
                                                if ($log['status'] == 'Check In') {
                                                    $status_class = 'label-success';
                                                } elseif ($log['status'] == 'Check Out') {
                                                    $status_class = 'label-danger';
                                                } elseif ($log['status'] == 'Break In' || $log['status'] == 'Break Out') {
                                                    $status_class = 'label-warning';
                                                }
                                            ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td><?php echo !empty($log['machine_user_id']) ? $log['machine_user_id'] : '-'; ?></td>
                                                <td><?php echo $person_type; ?></td>
                                                <td><?php echo $person_id; ?></td>
                                                <td>
                                                    <?php if ($person_image): ?>
                                                        <img src="<?php echo base_url($person_image); ?>" 
                                                             class="img-circle" width="30" height="30">
                                                    <?php endif; ?>
                                                    <?php echo $person_name != '' ? $person_name : 'Unmapped'; ?>
                                                </td>
                                                <td><?php echo !empty($class_or_role) ? $class_or_role : '-'; ?></td>
                                                <td><?php echo !empty($section_value) ? $section_value : '-'; ?></td>
                                                <td><?php echo date('d M Y', strtotime($log['timestamp'])); ?></td>
                                                <td><?php echo date('h:i A', strtotime($log['timestamp'])); ?></td>
                                                <td>
                                                    <span class="label <?php echo $status_class; ?>"><?php echo $log['status']; ?></span>
                                                </td>
                                                <td>
                                                    <?php if ($is_unmapped && !empty($log['machine_user_id'])): ?>
                                                        <button type="button"
                                                                class="btn btn-xs btn-primary btn-map-log"
                                                                data-machine="<?php echo htmlspecialchars($log['machine_user_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                                            <i class="fa fa-link"></i> Map
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">Mapped</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="11" class="text-center">
                                                No attendance records found for the selected <?php echo (isset($selected_date_mode) && $selected_date_mode === 'created_at') ? 'import date' : 'punch date'; ?>.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if (!empty($log_pagination) && $log_pagination['total_pages'] > 1): ?>
                            <?php
                                $base_query = array(
                                    'date' => $selected_date,
                                    'date_mode' => isset($selected_date_mode) ? $selected_date_mode : 'timestamp',
                                    'admission_no' => $selected_admission,
                                    'per_page' => (int) $log_pagination['per_page'],
                                );
                                $current_page = (int) $log_pagination['current_page'];
                                $total_pages = (int) $log_pagination['total_pages'];
                                $start_page = max(1, $current_page - 2);
                                $end_page = min($total_pages, $current_page + 2);
                            ?>
                            <nav aria-label="Attendance log pagination" class="text-center">
                                <ul class="pagination pagination-sm">
                                    <?php
                                        $prev_query = $base_query;
                                        $prev_query['page'] = max(1, $current_page - 1);
                                    ?>
                                    <li class="<?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                        <a href="<?php echo $current_page <= 1 ? '#' : site_url('admin/attendance/view_logs?' . http_build_query($prev_query)); ?>">&laquo;</a>
                                    </li>
                                    <?php for ($page = $start_page; $page <= $end_page; $page++): ?>
                                        <?php $page_query = $base_query; $page_query['page'] = $page; ?>
                                        <li class="<?php echo $page === $current_page ? 'active' : ''; ?>">
                                            <a href="<?php echo site_url('admin/attendance/view_logs?' . http_build_query($page_query)); ?>"><?php echo $page; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    <?php
                                        $next_query = $base_query;
                                        $next_query['page'] = min($total_pages, $current_page + 1);
                                    ?>
                                    <li class="<?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a href="<?php echo $current_page >= $total_pages ? '#' : site_url('admin/attendance/view_logs?' . http_build_query($next_query)); ?>">&raquo;</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Map Modal -->
<div class="modal fade" id="mapLogModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><i class="fa fa-link"></i> Map Machine User</h4>
            </div>
            <div class="modal-body">
                <?php echo $this->customlib->getCSRF(); ?>
                <input type="hidden" id="map_machine_user_id" value="">
                <div class="form-group">
                    <label>Machine User ID</label>
                    <input type="text" id="map_machine_user_display" class="form-control" readonly>
                </div>
                <div class="form-group">
                    <label>Map To</label>
                    <select id="map_target_type" class="form-control">
                        <option value="staff">Staff</option>
                    </select>
                </div>
                <div class="form-group map-target map-target-staff" style="display:none;">
                    <label>Select Staff</label>
                    <select id="map_staff_id" class="form-control">
                        <option value="">Select staff</option>
                        <?php if (!empty($staff_for_mapping)): ?>
                            <?php foreach ($staff_for_mapping as $staff): ?>
                                <option value="<?php echo (int) $staff['id']; ?>"
                                        data-employee="<?php echo htmlspecialchars((string)$staff['employee_id'], ENT_QUOTES, 'UTF-8'); ?>"
                                        data-name="<?php echo htmlspecialchars(trim($staff['name'] . ' ' . $staff['surname']), ENT_QUOTES, 'UTF-8'); ?>"
                                        data-role="<?php echo htmlspecialchars((string)$staff['staff_role'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($staff['employee_id'] . ' | ' . $staff['name'] . ' ' . $staff['surname'] . ' (' . $staff['staff_role'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="well well-sm" id="selected-person-info" style="display:none; margin-bottom:0;">
                    <strong>Selected Person:</strong>
                    <div id="selected-person-text"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-save-mapping">
                    <i class="fa fa-save"></i> Save Mapping
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
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
    // Initialize DataTable
    if ($.fn.DataTable) {
        $('#logs-table').DataTable({
            "paging": false,
            "lengthChange": false,
            "searching": true,
            "ordering": true,
            "info": false,
            "autoWidth": false,
            "pageLength": 50,
            "order": [[0, "desc"]]
        });
    }

    // Open mapping modal for unmapped log
    $(document).on('click', '.btn-map-log', function() {
        var machineUserId = $(this).data('machine');
        $('#map_machine_user_id').val(machineUserId);
        $('#map_machine_user_display').val(machineUserId);
        $('#map_target_type').val('staff').trigger('change');
        $('#map_admission_no').val('');
        $('#map_staff_id').val('');
        $('#selected-person-info').hide();
        $('#selected-person-text').text('');

        // Smart pre-selection: machine ID may match staff ID or employee ID.
        var preselected = false;
        $('#map_staff_id option').each(function() {
            var staffId = ($(this).val() || '').trim().toLowerCase();
            var employee = (($(this).data('employee') || '') + '').trim().toLowerCase();
            var machine = (machineUserId || '').toString().trim().toLowerCase();
            if (staffId && (staffId === machine || employee === machine)) {
                $('#map_staff_id').val($(this).val());
                preselected = true;
                return false;
            }
        });

        if (preselected) {
            $('#map_staff_id').trigger('change');
        }
        $('#mapLogModal').modal('show');
    });

    $('#map_target_type').on('change', function() {
        var targetType = $(this).val();
        $('.map-target').hide();
        if (targetType === 'staff') {
            $('.map-target-staff').show();
            $('#map_admission_no').val('');
        } else {
            $('.map-target-student').show();
            $('#map_staff_id').val('');
        }
        $('#selected-person-info').hide();
        $('#selected-person-text').text('');
    });

    // Show selected student context
    $('#map_admission_no').on('change', function() {
        var selected = $(this).find('option:selected');
        var admission = (selected.val() || '').trim();
        if (!admission) {
            $('#selected-person-info').hide();
            $('#selected-person-text').text('');
            return;
        }

        var name = selected.data('name') || '';
        var roll = selected.data('roll') || '-';
        var cls = selected.data('class') || '-';
        var section = selected.data('section') || '-';
        $('#selected-person-text').text(
            admission + ' | Roll: ' + roll + ' | ' + name + ' | Class: ' + cls + ' | Section: ' + section
        );
        $('#selected-person-info').show();
    });

    $('#map_staff_id').on('change', function() {
        var selected = $(this).find('option:selected');
        var staffId = (selected.val() || '').trim();
        if (!staffId) {
            $('#selected-person-info').hide();
            $('#selected-person-text').text('');
            return;
        }

        var name = selected.data('name') || '';
        var employee = selected.data('employee') || '-';
        var role = selected.data('role') || '-';
        $('#selected-person-text').text(employee + ' | ' + name + ' | Role: ' + role);
        $('#selected-person-info').show();
    });

    // Save mapping and backfill logs
    $('#btn-save-mapping').on('click', function() {
        var machineUserId = $('#map_machine_user_id').val();
        var targetType = $('#map_target_type').val();
        var admissionNo = $('#map_admission_no').val();
        var staffId = $('#map_staff_id').val();
        var $csrf = $('.modal-body').find('input[name]').first();
        var csrfName = $csrf.attr('name');
        var csrfHash = $csrf.val();
        var requestData = {
            machine_user_id: machineUserId,
            target_type: targetType,
            admission_no: admissionNo,
            staff_id: staffId
        };
        if (csrfName) {
            requestData[csrfName] = csrfHash;
        }

        if (!machineUserId || (targetType === 'student' && !admissionNo) || (targetType === 'staff' && !staffId)) {
            alert('Please select a staff member for mapping');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        $.ajax({
            url: '<?php echo site_url("admin/attendance/map_unmapped_log"); ?>',
            type: 'POST',
            dataType: 'json',
            data: requestData,
            success: function(response) {
                if (response && response.csrf_token_name && response.csrf_hash) {
                    $('input[name="' + response.csrf_token_name + '"]').val(response.csrf_hash);
                }
                if (response.status === 'success') {
                    $('#mapLogModal').modal('hide');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                var serverText = (xhr && xhr.responseText) ? xhr.responseText : '';
                alert('Failed to save mapping: ' + (error || status) + (serverText ? '\n' + serverText : ''));
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Mapping');
            }
        });
    });

});
</script>

