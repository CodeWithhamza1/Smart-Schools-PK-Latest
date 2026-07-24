<?php
$report = isset($report_data) ? $report_data : array('rows' => array(), 'date_list' => array(), 'summary' => array());
$summary = isset($report['summary']) ? $report['summary'] : array();
$rows = isset($report['rows']) ? $report['rows'] : array();
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-bar-chart"></i> Student Attendance Report</h1>
    </section>
    <section class="content">
        <div class="box box-primary">
            <div class="box-header with-border"><h3 class="box-title">ZKTeco Student Attendance</h3></div>
            <div class="box-body">
                <form method="get" action="<?php echo site_url('admin/attendance/student_report'); ?>">
                    <div class="row">
                        <div class="col-md-3"><label>Class</label><select name="class_id" class="form-control" required>
                            <option value="">Select Class</option>
                            <?php foreach ((array) $classlist as $class): ?><option value="<?php echo (int) $class['id']; ?>" <?php echo ((int) $class_id === (int) $class['id']) ? 'selected' : ''; ?>><?php echo html_escape($class['class']); ?></option><?php endforeach; ?>
                        </select></div>
                        <div class="col-md-3"><label>Section</label><select name="section_id" class="form-control"><option value="0">All Sections</option><?php foreach ((array) $section_list as $section): ?><option value="<?php echo (int) $section['section_id']; ?>" <?php echo ((int) $section_id === (int) $section['section_id']) ? 'selected' : ''; ?>><?php echo html_escape($section['section']); ?></option><?php endforeach; ?></select></div>
                        <div class="col-md-2"><label>Admission No.</label><input type="text" name="admission_no" class="form-control" value="<?php echo html_escape($admission_no); ?>"></div>
                        <div class="col-md-2"><label>From</label><input type="date" name="from_date" class="form-control" value="<?php echo html_escape($from_date); ?>"></div>
                        <div class="col-md-2"><label>To</label><input type="date" name="to_date" class="form-control" value="<?php echo html_escape($to_date); ?>"></div>
                    </div>
                    <button class="btn btn-primary" style="margin-top:15px"><i class="fa fa-search"></i> Generate Report</button>
                    <a class="btn btn-default" style="margin-top:15px" href="<?php echo site_url('admin/attendance'); ?>">Back</a>
                </form>
            </div>
        </div>
        <?php if ($class_id): ?>
        <div class="row">
            <div class="col-md-3"><div class="small-box bg-green"><div class="inner"><h3><?php echo (int) ($summary['present'] ?? 0); ?></h3><p>Present Days</p></div></div></div>
            <div class="col-md-3"><div class="small-box bg-red"><div class="inner"><h3><?php echo (int) ($summary['absent'] ?? 0); ?></h3><p>Absent Days</p></div></div></div>
            <div class="col-md-3"><div class="small-box bg-yellow"><div class="inner"><h3><?php echo (int) ($summary['leave_approved'] ?? 0); ?></h3><p>Approved Leave Days</p></div></div></div>
            <div class="col-md-3"><div class="small-box bg-aqua"><div class="inner"><h3><?php echo (int) count($rows); ?></h3><p>Students</p></div></div></div>
        </div>
        <div class="box box-info"><div class="box-body table-responsive"><table class="table table-bordered table-striped" id="student-zkteco-report">
            <thead><tr><th>#</th><th>Admission No.</th><th>Roll No.</th><th>Student</th><th>Class</th><th>Section</th><th>Present</th><th>Absent</th><th>Approved Leave</th><th>Punches</th><th>Attendance %</th></tr></thead>
            <tbody><?php foreach ($rows as $i => $row): ?><tr><td><?php echo $i + 1; ?></td><td><?php echo html_escape($row['admission_no']); ?></td><td><?php echo html_escape($row['roll_no']); ?></td><td><?php echo html_escape($row['student_name']); ?></td><td><?php echo html_escape($row['class']); ?></td><td><?php echo html_escape($row['section']); ?></td><td><?php echo (int) $row['present_days']; ?></td><td><?php echo (int) $row['absent_days']; ?></td><td><?php echo (int) $row['leave_approved_days']; ?></td><td><?php echo (int) $row['punch_count_total']; ?></td><td><?php echo number_format((float) $row['attendance_percent'], 2); ?>%</td></tr><?php endforeach; ?></tbody>
        </table></div></div>
        <?php endif; ?>
    </section>
</div>
