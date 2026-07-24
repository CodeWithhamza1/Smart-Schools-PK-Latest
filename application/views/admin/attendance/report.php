<?php
$roles = isset($roles) ? $roles : array();
$staff_list = isset($staff_list) ? $staff_list : array();
$report_data = isset($report_data) ? $report_data : array('rows' => array(), 'daily_rows' => array(), 'summary' => array());
$summary = isset($report_data['summary']) ? $report_data['summary'] : array();
?>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-bar-chart"></i> Staff Attendance Report
            <small>Staff attendance only</small>
        </h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-filter"></i> Filter Criteria</h3>
                    </div>
                    <div class="box-body">
                        <form method="get" action="<?php echo site_url('admin/attendance/report'); ?>">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Role</label>
                                        <select name="role_id" id="role_id" class="form-control">
                                            <option value="">All Roles</option>
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?php echo (int) $role['id']; ?>" <?php echo ((int) $selected_role_id === (int) $role['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($role['type'], ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Staff</label>
                                        <select name="staff_id" id="staff_id" class="form-control select2">
                                            <option value="">All Staff</option>
                                            <?php foreach ($staff_list as $staff): ?>
                                                <?php $staff_name = trim($staff['name'] . ' ' . $staff['surname']); ?>
                                                <option value="<?php echo (int) $staff['id']; ?>"
                                                        data-role="<?php echo isset($staff['role_id']) ? (int) $staff['role_id'] : 0; ?>"
                                                        <?php echo ((int) $selected_staff_id === (int) $staff['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($staff['employee_id'] . ' | ' . $staff_name . ' (' . (isset($staff['staff_role']) ? $staff['staff_role'] : '') . ')', ENT_QUOTES, 'UTF-8'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>From Date</label>
                                        <input type="date" name="from_date" value="<?php echo htmlspecialchars($from_date, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>To Date</label>
                                        <input type="date" name="to_date" value="<?php echo htmlspecialchars($to_date, ENT_QUOTES, 'UTF-8'); ?>" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fa fa-search"></i> Generate
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <a href="<?php echo site_url('admin/attendance'); ?>" class="btn btn-default btn-block">
                                            <i class="fa fa-arrow-left"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Staff</span>
                        <span class="info-box-number"><?php echo isset($summary['staff']) ? (int) $summary['staff'] : 0; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Present</span>
                        <span class="info-box-number"><?php echo isset($summary['present']) ? (float) $summary['present'] : 0; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-times"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Absent</span>
                        <span class="info-box-number"><?php echo isset($summary['absent']) ? (float) $summary['absent'] : 0; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Late</span>
                        <span class="info-box-number"><?php echo isset($summary['late']) ? (int) $summary['late'] : 0; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-purple"><i class="fa fa-calendar"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Leave</span>
                        <span class="info-box-number"><?php echo isset($summary['leave']) ? (int) $summary['leave'] : 0; ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-4 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-gray"><i class="fa fa-flag"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Holiday</span>
                        <span class="info-box-number"><?php echo isset($summary['holiday']) ? (int) $summary['holiday'] : 0; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa fa-table"></i> Staff Summary
                            <small><?php echo htmlspecialchars($from_date, ENT_QUOTES, 'UTF-8'); ?> to <?php echo htmlspecialchars($to_date, ENT_QUOTES, 'UTF-8'); ?></small>
                        </h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="staff-summary-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Employee ID</th>
                                        <th>Staff Name</th>
                                        <th>Role</th>
                                        <th>Total Days</th>
                                        <th>Working Days</th>
                                        <th>Present Days</th>
                                        <th>Late Entries</th>
                                        <th>Half Day Entries</th>
                                        <th>Absent Days</th>
                                        <th>Leave</th>
                                        <th>Holiday</th>
                                        <th>Attendance %</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($report_data['rows'])): ?>
                                        <?php $i = 1; foreach ($report_data['rows'] as $row): ?>
                                            <tr>
                                                <td><?php echo $i++; ?></td>
                                                <td><?php echo htmlspecialchars((string) $row['employee_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($row['staff_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars((string) $row['staff_role'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo (int) $row['total_days']; ?></td>
                                                <td><?php echo (int) $row['working_days']; ?></td>
                                                <td><?php echo (float) $row['present_days']; ?></td>
                                                <td><?php echo (int) $row['late_days']; ?></td>
                                                <td><?php echo (int) $row['half_days']; ?></td>
                                                <td><?php echo (float) $row['absent_days']; ?></td>
                                                <td><?php echo (int) $row['leave_days']; ?></td>
                                                <td><?php echo (int) $row['holiday_days']; ?></td>
                                                <td><?php echo number_format((float) $row['attendance_percent'], 2); ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="13" class="text-center">No staff records found for selected criteria.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> Daily Staff Attendance Details</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover" id="staff-detail-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Date</th>
                                        <th>Employee ID</th>
                                        <th>Staff Name</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Check In</th>
                                        <th>Check Out</th>
                                        <th>Remark</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($report_data['daily_rows'])): ?>
                                        <?php $i = 1; foreach ($report_data['daily_rows'] as $row): ?>
                                            <?php
                                                $status_class = 'label-default';
                                                if ($row['status_key'] === 'present') {
                                                    $status_class = 'label-success';
                                                } elseif ($row['status_key'] === 'absent') {
                                                    $status_class = 'label-danger';
                                                } elseif ($row['status_key'] === 'late' || $row['status_key'] === 'half_day') {
                                                    $status_class = 'label-warning';
                                                } elseif ($row['status_key'] === 'leave') {
                                                    $status_class = 'label-info';
                                                }
                                            ?>
                                            <tr>
                                                <td><?php echo $i++; ?></td>
                                                <td><?php echo date('d M Y', strtotime($row['date'])); ?></td>
                                                <td><?php echo htmlspecialchars((string) $row['employee_id'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($row['staff_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars((string) $row['staff_role'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><span class="label <?php echo $status_class; ?>"><?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                                <td><?php echo htmlspecialchars($row['check_in'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($row['check_out'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars((string) $row['remark'], ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="9" class="text-center">No staff attendance details found for selected criteria.</td>
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
(function($) {
    var staffOptions = [];

    function cacheStaffOptions() {
        $('#staff_id option').each(function() {
            staffOptions.push({
                value: $(this).attr('value'),
                text: $(this).text(),
                role: $(this).data('role') || '',
                selected: $(this).is(':selected')
            });
        });
    }

    function filterStaffByRole() {
        var roleId = $('#role_id').val();
        var currentStaff = $('#staff_id').val();
        var $staff = $('#staff_id');

        $staff.empty();
        $.each(staffOptions, function(_, option) {
            if (option.value === '' || roleId === '' || String(option.role) === String(roleId)) {
                $('<option>')
                    .val(option.value)
                    .text(option.text)
                    .attr('data-role', option.role)
                    .prop('selected', String(option.value) === String(currentStaff))
                    .appendTo($staff);
            }
        });

        if ($staff.find('option:selected').length === 0) {
            $staff.val('');
        }
        if ($.fn.select2) {
            $staff.trigger('change.select2');
        }
    }

    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2();
        }

        cacheStaffOptions();
        $('#role_id').on('change', filterStaffByRole);
        filterStaffByRole();

        if ($.fn.DataTable) {
            $('#staff-summary-table').DataTable({
                pageLength: 25,
                order: [[12, 'desc']]
            });
            $('#staff-detail-table').DataTable({
                pageLength: 50,
                order: [[1, 'asc'], [3, 'asc']]
            });
        }
    });
})(jQuery);
</script>
