<?php
$student_mappings = isset($student_mappings) ? $student_mappings : (isset($mappings) ? $mappings : array());
$staff_mappings = isset($staff_mappings) ? $staff_mappings : array();
$students_for_mapping = isset($students_for_mapping) ? $students_for_mapping : array();
$staff_for_mapping = isset($staff_for_mapping) ? $staff_for_mapping : array();
?>
<!-- Manage Attendance Mappings View -->
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-link"></i> Manage Attendance Mappings
            <small>Map ZKTeco Machine Users to Students or Staff</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-plus"></i> Add New Student or Staff Mapping</h3>
                    </div>
                    <div class="box-body">
                        <form id="form-add-mapping">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Map To <span class="text-danger">*</span></label>
                                        <select name="target_type" id="target_type" class="form-control">
                                            <option value="student">Student Mapping</option>
                                            <option value="staff">Staff Mapping</option>
                                        </select>
                                        <span class="help-block">Each machine user can map to one student or staff member.</span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Machine User ID <span class="text-danger">*</span></label>
                                        <input type="text" name="machine_user_id" id="machine_user_id" class="form-control" placeholder="e.g., 1, 2, 3..." required>
                                    </div>
                                </div>
                                <div class="col-md-3 map-target map-target-staff">
                                    <div class="form-group">
                                        <label>Staff <span class="text-danger">*</span></label>
                                        <select name="staff_id" id="staff_id" class="form-control select2">
                                            <option value="">Select Staff</option>
                                            <?php if (!empty($staff_for_mapping)): ?>
                                                <?php foreach ($staff_for_mapping as $staff): ?>
                                                    <?php $staff_name = trim($staff['name'] . ' ' . $staff['surname']); ?>
                                                    <option value="<?php echo (int) $staff['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($staff_name, ENT_QUOTES, 'UTF-8'); ?>"
                                                            data-employee="<?php echo htmlspecialchars((string) $staff['employee_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                                        <?php echo htmlspecialchars($staff['employee_id'] . ' | ' . $staff_name . ' (' . $staff['staff_role'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 map-target map-target-student" style="display:none;">
                                    <div class="form-group">
                                        <label>Student <span class="text-danger">*</span></label>
                                        <select name="admission_no" id="admission_no" class="form-control select2">
                                            <option value="">Select Student</option>
                                            <?php foreach ($students_for_mapping as $student): ?>
                                                <?php $student_name = trim($student['firstname'] . ' ' . $student['lastname']); ?>
                                                <option value="<?php echo html_escape($student['admission_no']); ?>" data-name="<?php echo htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8'); ?>" data-roll="<?php echo html_escape($student['roll_no']); ?>"><?php echo html_escape($student['admission_no'] . ' | ' . $student_name . ' (' . $student['class'] . ' - ' . $student['section'] . ')'); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Selected Name</label>
                                        <input type="text" name="student_name" id="person_name" class="form-control" readonly>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-success btn-block">
                                            <i class="fa fa-save"></i> Save Mapping
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-tasks"></i> Bulk Map Unmapped Machine Users</h3>
                    </div>
                    <div class="box-body">
                        <?php if (!empty($unmapped_users)): ?>
                            <?php
                                $pagination = isset($unmapped_pagination) ? $unmapped_pagination : array('current_page' => 1, 'per_page' => 50, 'total_rows' => count($unmapped_users), 'total_pages' => 1);
                                $current_page = isset($pagination['current_page']) ? (int) $pagination['current_page'] : 1;
                                $per_page = isset($pagination['per_page']) ? (int) $pagination['per_page'] : 50;
                                $total_rows = isset($pagination['total_rows']) ? (int) $pagination['total_rows'] : count($unmapped_users);
                                $total_pages = isset($pagination['total_pages']) ? (int) $pagination['total_pages'] : 1;
                                $start_row = (($current_page - 1) * $per_page) + 1;
                                $end_row = min($start_row + count($unmapped_users) - 1, $total_rows);
                            ?>
                            <p class="text-muted">
                                Map multiple unmapped machine users in one go. Only rows with a selected student or staff member will be submitted.
                                Showing <?php echo $start_row; ?>-<?php echo $end_row; ?> of <?php echo $total_rows; ?> unmapped users.
                            </p>
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered" id="bulk-map-table">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Machine User ID</th>
                                            <th>Unmapped Logs</th>
                                            <th>Last Seen</th>
                                            <th>Map To</th>
                                                <th>Select Student</th>
                                                <th>Select Staff</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $bulkCounter = $start_row; ?>
                                        <?php foreach ($unmapped_users as $row): ?>
                                            <?php $machineKey = isset($row['machine_user_id']) ? (string) $row['machine_user_id'] : ''; ?>
                                            <tr>
                                                <td><?php echo $bulkCounter++; ?></td>
                                                <td class="bulk-machine-id"><?php echo htmlspecialchars($machineKey, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo isset($row['total_logs']) ? (int) $row['total_logs'] : 0; ?></td>
                                                <td><?php echo !empty($row['last_seen']) ? date('d M Y H:i', strtotime($row['last_seen'])) : '-'; ?></td>
                                                <td style="min-width: 120px;">
                                                    <select class="form-control bulk-target-type">
                                                        <option value="student">Student</option>
                                                        <option value="staff">Staff</option>
                                                    </select>
                                                </td>
                                                <td style="min-width: 340px;" class="bulk-student-cell">
                                                    <select class="form-control bulk-student-select select2" disabled><option value="">Skip this row</option></select>
                                                </td>
                                                <td style="min-width: 340px;" class="bulk-staff-cell">
                                                    <select class="form-control bulk-staff-select select2">
                                                        <option value="">Skip this row</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Unmapped pagination" style="margin-top: 10px;">
                                    <ul class="pagination pagination-sm" style="margin: 0;">
                                        <?php $prev_page = max(1, $current_page - 1); ?>
                                        <li class="<?php echo ($current_page <= 1) ? 'disabled' : ''; ?>">
                                            <a href="<?php echo ($current_page <= 1) ? '#' : site_url('admin/attendance/manage_mappings?unmapped_page=' . $prev_page); ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                        <?php
                                            $window_start = max(1, $current_page - 2);
                                            $window_end = min($total_pages, $current_page + 2);
                                            for ($p = $window_start; $p <= $window_end; $p++):
                                        ?>
                                            <li class="<?php echo ($p === $current_page) ? 'active' : ''; ?>">
                                                <a href="<?php echo site_url('admin/attendance/manage_mappings?unmapped_page=' . $p); ?>"><?php echo $p; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <?php $next_page = min($total_pages, $current_page + 1); ?>
                                        <li class="<?php echo ($current_page >= $total_pages) ? 'disabled' : ''; ?>">
                                            <a href="<?php echo ($current_page >= $total_pages) ? '#' : site_url('admin/attendance/manage_mappings?unmapped_page=' . $next_page); ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>

                            <select id="bulk-staff-options-template" style="display:none;">
                                <option value="">Skip this row</option>
                                <?php if (!empty($staff_for_mapping)): ?>
                                    <?php foreach ($staff_for_mapping as $staff): ?>
                                        <?php $staff_name = trim($staff['name'] . ' ' . $staff['surname']); ?>
                                        <option value="<?php echo (int) $staff['id']; ?>"
                                                data-employee="<?php echo htmlspecialchars((string) $staff['employee_id'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($staff['employee_id'] . ' | ' . $staff_name . ' (' . $staff['staff_role'] . ')', ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <select id="bulk-student-options-template" style="display:none;"><option value="">Skip this row</option>
                                <?php foreach ($students_for_mapping as $student): ?><option value="<?php echo html_escape($student['admission_no']); ?>" data-roll="<?php echo html_escape($student['roll_no']); ?>"><?php echo html_escape($student['admission_no'] . ' | ' . trim($student['firstname'] . ' ' . $student['lastname'])); ?></option><?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-warning" id="btn-save-bulk-mapping">
                                <i class="fa fa-save"></i> Save Bulk Mappings
                            </button>
                        <?php else: ?>
                            <div class="alert alert-info" style="margin-bottom:0;">No unmapped machine users found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> Existing Student and Staff Mappings</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover mapping-table" id="student-mappings-table">
                                <thead><tr><th>#</th><th>Machine User ID</th><th>Admission No.</th><th>Student Name</th><th>Created At</th><th>Actions</th></tr></thead>
                                <tbody><?php if (!empty($student_mappings)): ?><?php foreach ($student_mappings as $i => $mapping): ?><tr id="mapping-student-row-<?php echo (int) $mapping['id']; ?>"><td><?php echo $i + 1; ?></td><td><?php echo html_escape($mapping['machine_user_id']); ?></td><td><?php echo html_escape($mapping['admission_no']); ?></td><td><?php echo html_escape($mapping['student_name']); ?></td><td><?php echo !empty($mapping['created_at']) ? date('d M Y', strtotime($mapping['created_at'])) : '-'; ?></td><td><button class="btn btn-danger btn-sm btn-delete" data-type="student" data-id="<?php echo (int) $mapping['id']; ?>">Delete</button></td></tr><?php endforeach; ?><?php else: ?><tr><td colspan="6" class="text-center">No student mappings found</td></tr><?php endif; ?></tbody>
                            </table>
                            <table class="table table-striped table-bordered table-hover mapping-table" id="staff-mappings-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Machine User ID</th>
                                        <th>Employee ID</th>
                                        <th>Staff Name</th>
                                        <th>Role</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($staff_mappings)): ?>
                                        <?php $counter = 1; ?>
                                        <?php foreach ($staff_mappings as $mapping): ?>
                                            <?php $staff_name = !empty($mapping['staff_name']) ? $mapping['staff_name'] : trim($mapping['name'] . ' ' . $mapping['surname']); ?>
                                            <tr id="mapping-staff-row-<?php echo (int) $mapping['id']; ?>">
                                                <td><?php echo $counter++; ?></td>
                                                <td><?php echo htmlspecialchars($mapping['machine_user_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars((string) $mapping['employee_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($staff_name, ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars((string) $mapping['staff_role'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo !empty($mapping['created_at']) ? date('d M Y', strtotime($mapping['created_at'])) : '-'; ?></td>
                                                <td>
                                                    <button class="btn btn-danger btn-sm btn-delete" data-type="staff" data-id="<?php echo (int) $mapping['id']; ?>">
                                                        <i class="fa fa-trash"></i> Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No staff mappings found</td>
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

<script type="text/javascript">
window.addEventListener('error', function(e) {
    if (e.message && e.message.includes('runtime.lastError')) {
        e.preventDefault();
        return false;
    }
}, true);

window.addEventListener('unhandledrejection', function(e) {
    if (e.reason && e.reason.message && e.reason.message.includes('runtime.lastError')) {
        e.preventDefault();
        return false;
    }
});

$(document).ready(function() {
    if ($.fn.select2) {
        $('.select2').select2();
    }

    function getCsrfPayload() {
        var $csrfInput = $('#form-add-mapping').find('input[type="hidden"][name]').first();
        if ($csrfInput.length === 0) {
            return {};
        }
        var payload = {};
        payload[$csrfInput.attr('name')] = $csrfInput.val();
        return payload;
    }

    function updateCsrfFromResponse(response) {
        if (response && response.csrf_token_name && response.csrf_hash) {
            $('input[name="' + response.csrf_token_name + '"]').val(response.csrf_hash);
        }
    }

    function refreshTargetFields() {
        var targetType = $('#target_type').val();
        $('.map-target').hide();
        $('#person_name').val('');
        if (targetType === 'staff') {
            $('.map-target-staff').show();
            $('#admission_no').val('').trigger('change.select2');
        } else {
            $('.map-target-student').show();
            $('#staff_id').val('').trigger('change.select2');
        }
    }

    $('#target_type').on('change', refreshTargetFields);

    $('#admission_no').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        $('#person_name').val(selectedOption.data('name') || '');
    });

    $('#staff_id').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        $('#person_name').val(selectedOption.data('name') || '');
    });

    $('#form-add-mapping').submit(function(e) {
        e.preventDefault();

        var targetType = $('#target_type').val();
        var formData = {
            machine_user_id: $('#machine_user_id').val(),
            target_type: targetType,
            admission_no: targetType === 'student' ? $('#admission_no').val() : '',
            staff_id: targetType === 'staff' ? $('#staff_id').val() : '',
            student_name: $('#person_name').val()
        };
        $.extend(formData, getCsrfPayload());

        $.ajax({
            url: '<?php echo site_url("admin/attendance/save_mapping"); ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                updateCsrfFromResponse(response);
                if (response.status === 'success') {
                    alert(response.message || 'Mapping saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Failed to save mapping: ' + error);
            }
        });
    });

    $(document).on('click', '.btn-delete', function() {
        if (!confirm('Are you sure you want to delete this mapping?')) {
            return;
        }

        var mappingId = $(this).data('id');
        var mappingType = $(this).data('type') || 'student';
        var row = $('#mapping-' + mappingType + '-row-' + mappingId);

        $.ajax({
            url: '<?php echo site_url("admin/attendance/delete_mapping"); ?>',
            type: 'POST',
            data: $.extend({ id: mappingId, target_type: mappingType }, getCsrfPayload()),
            dataType: 'json',
            success: function(response) {
                updateCsrfFromResponse(response);
                if (response.status === 'success') {
                    row.fadeOut(300, function() {
                        $(this).remove();
                    });
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                var message = error || status || 'Request failed';
                if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr && xhr.responseText) {
                    message = xhr.responseText.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
                }
                alert('Failed to delete mapping: ' + message);
            }
        });
    });

    var bulkStudentOptionsHtml = $('#bulk-student-options-template').html() || '<option value="">Skip this row</option>';
    var bulkStaffOptionsHtml = $('#bulk-staff-options-template').html() || '<option value="">Skip this row</option>';

    function getMachineUser($select) {
        return ($select.closest('tr').find('.bulk-machine-id').text() || '').trim().toLowerCase();
    }

    function hydrateBulkStudentSelect($select) {
        if (!$select || !$select.length || $select.data('loaded') === true) {
            return;
        }

        $select.html(bulkStudentOptionsHtml);
        $select.data('loaded', true);

        var machineUser = getMachineUser($select);
        if (!machineUser) {
            return;
        }
        $select.find('option').each(function() {
            var admission = ($(this).val() || '').trim().toLowerCase();
            var roll = (($(this).data('roll') || '') + '').trim().toLowerCase();
            if (admission && (admission === machineUser || roll === machineUser)) {
                $select.val($(this).val());
                return false;
            }
        });
        $select.trigger('change.select2');
    }

    function hydrateBulkStaffSelect($select) {
        if (!$select || !$select.length || $select.data('loaded') === true) {
            return;
        }

        $select.html(bulkStaffOptionsHtml);
        $select.data('loaded', true);

        var machineUser = getMachineUser($select);
        if (!machineUser) {
            return;
        }
        $select.find('option').each(function() {
            var staffId = ($(this).val() || '').trim().toLowerCase();
            var employeeId = (($(this).data('employee') || '') + '').trim().toLowerCase();
            if (staffId && (staffId === machineUser || employeeId === machineUser)) {
                $select.val($(this).val());
                return false;
            }
        });
        $select.trigger('change.select2');
    }

    $(document).on('change', '.bulk-target-type', function() {
        var $row = $(this).closest('tr');
        var targetType = $(this).val();
        if (targetType === 'staff') {
            $row.find('.bulk-student-select').val('').prop('disabled', true).trigger('change.select2');
            $row.find('.bulk-staff-select').prop('disabled', false).trigger('change.select2');
            hydrateBulkStaffSelect($row.find('.bulk-staff-select'));
        } else {
            $row.find('.bulk-staff-select').val('').prop('disabled', true).trigger('change.select2');
            $row.find('.bulk-student-select').prop('disabled', false).trigger('change.select2');
            hydrateBulkStudentSelect($row.find('.bulk-student-select'));
        }
    });

    $(document).on('select2:opening focus', '.bulk-student-select', function() {
        hydrateBulkStudentSelect($(this));
    });

    $(document).on('select2:opening focus', '.bulk-staff-select', function() {
        hydrateBulkStaffSelect($(this));
    });

    $('#btn-save-bulk-mapping').on('click', function() {
        var mappings = [];

        $('#bulk-map-table tbody tr').each(function() {
            var machineUserId = ($(this).find('.bulk-machine-id').text() || '').trim();
            var targetType = ($(this).find('.bulk-target-type').val() || 'staff').trim();
            var admissionNo = ($(this).find('.bulk-student-select').val() || '').trim();
            var staffId = ($(this).find('.bulk-staff-select').val() || '').trim();

            if (machineUserId && targetType === 'staff' && staffId) {
                mappings.push({
                    machine_user_id: machineUserId,
                    target_type: 'staff',
                    staff_id: staffId
                });
            } else if (machineUserId && targetType === 'student' && admissionNo) {
                mappings.push({
                    machine_user_id: machineUserId,
                    target_type: 'student',
                    admission_no: admissionNo
                });
            }
        });

        if (mappings.length === 0) {
            alert('Select at least one student or staff member in the bulk mapping table.');
            return;
        }

        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class=\"fa fa-spinner fa-spin\"></i> Saving...');

        var postData = {
            mappings: JSON.stringify(mappings)
        };
        $.extend(postData, getCsrfPayload());

        $.ajax({
            url: '<?php echo site_url("admin/attendance/save_bulk_mappings"); ?>',
            type: 'POST',
            dataType: 'json',
            data: postData,
            success: function(response) {
                updateCsrfFromResponse(response);
                if (!response || !response.summary) {
                    alert('Unexpected response while bulk mapping.');
                    return;
                }

                var summary = response.summary;
                var msg = 'Bulk mapping result:'
                    + '\nTotal rows: ' + summary.total
                    + '\nSaved mappings: ' + summary.saved
                    + '\nSaved staff: ' + (summary.saved_staff || 0)
                    + '\nBackfilled logs: ' + summary.updated_logs
                    + '\nFailed rows: ' + summary.failed;

                if (summary.errors && summary.errors.length) {
                    msg += '\n\nTop errors:\n- ' + summary.errors.slice(0, 5).join('\n- ');
                }
                alert(msg);

                if (response.status === 'success' || response.status === 'warning') {
                    location.reload();
                }
            },
            error: function(xhr, status, error) {
                var details = (xhr && xhr.responseText) ? '\n' + xhr.responseText : '';
                alert('Failed to save bulk mappings: ' + (error || status) + details);
            },
            complete: function() {
                $btn.prop('disabled', false).html('<i class=\"fa fa-save\"></i> Save Bulk Mappings');
            }
        });
    });

    if ($.fn.DataTable) {
        $('.mapping-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false
        });
    }

    refreshTargetFields();
    $('.bulk-target-type').val('student').trigger('change');
});
</script>
