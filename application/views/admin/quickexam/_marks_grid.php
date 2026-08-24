<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">Enter Marks<?php if (!empty($subject_detail)) { ?> - <?php echo $subject_detail->subject_name; ?><?php } ?></h3>
        <div class="box-tools pull-right">
            <?php if (!empty($exam_subject_list) && count($exam_subject_list) > 1) { ?>
                <select id="grid_subject_selector" class="form-control input-sm" style="display:inline-block; width:220px; margin-right:10px;">
                    <?php foreach ($exam_subject_list as $subject_row) { ?>
                        <option value="<?php echo (int) $subject_row['id']; ?>" <?php echo ((int) $exam_subject_id === (int) $subject_row['id']) ? 'selected' : ''; ?>>
                            <?php echo $subject_row['subject_name']; ?> (<?php echo $subject_row['max_marks']; ?>)
                        </option>
                    <?php } ?>
                </select>
            <?php } ?>
            <button type="button" id="publish_notify" class="btn btn-success btn-sm">
                <i class="fa fa-bell"></i> Publish & Notify
            </button>
        </div>
    </div>
    <form method="post" id="quick_marks_form">
        <?php echo $this->customlib->getCSRF(); ?>
        <input type="hidden" name="exam_subject_id" value="<?php echo (int) $exam_subject_id; ?>">
        <div class="box-body table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Admission No</th>
                        <th>Roll No</th>
                        <th>Student</th>
                        <th>Attendance</th>
                        <th>Marks</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($resultlist)) { ?>
                        <tr>
                            <td colspan="6" class="text-center text-danger"><?php echo $this->lang->line('no_record_found'); ?></td>
                        </tr>
                    <?php } else { ?>
                        <?php foreach ($resultlist as $row) { ?>
                            <?php
                            $exam_student_id = (int) $row['exam_group_class_batch_exam_students_id'];
                            $attendance = $row['exam_group_exam_result_attendance'] != '' ? $row['exam_group_exam_result_attendance'] : 'present';
                            ?>
                            <tr>
                                <td><?php echo $row['admission_no']; ?></td>
                                <td><?php echo $row['exam_roll_no']; ?></td>
                                <td><?php echo $row['firstname'] . " " . $row['lastname']; ?></td>
                                <td>
                                    <input type="hidden" name="exam_group_student_id[]" value="<?php echo $exam_student_id; ?>">
                                    <select class="form-control" name="attendance_<?php echo $exam_student_id; ?>">
                                        <option value="present" <?php echo $attendance == 'present' ? 'selected' : ''; ?>>Present</option>
                                        <option value="absent" <?php echo $attendance == 'absent' ? 'selected' : ''; ?>>Absent</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="number" step="0.01" class="form-control" name="marks_<?php echo $exam_student_id; ?>" value="<?php echo $row['exam_group_exam_result_get_marks']; ?>">
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="note_<?php echo $exam_student_id; ?>" value="<?php echo htmlspecialchars($row['exam_group_exam_result_note']); ?>">
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        <div class="box-footer">
            <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Draft</button>
        </div>
    </form>
</div>
