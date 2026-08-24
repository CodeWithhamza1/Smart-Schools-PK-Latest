<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-list-alt"></i> Students Obtained Marks Entry</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Load Test</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/quickexam'); ?>" class="btn btn-default btn-sm">
                                <i class="fa fa-plus"></i> Create New Test
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Select Test <small class="req">*</small></label>
                                    <select id="entry_exam_id" class="form-control">
                                        <option value="">Select</option>
                                        <?php foreach ($quick_exam_list as $exam) { ?>
                                            <option value="<?php echo (int) $exam['id']; ?>">
                                                <?php echo $exam['exam']; ?> - <?php echo $exam['date_from']; ?><?php echo ((int) $exam['is_publish'] === 1) ? ' (Published)' : ''; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>Select Subject <small class="req">*</small></label>
                                    <select id="entry_exam_subject_id" class="form-control">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group" style="margin-top:24px;">
                                    <button type="button" id="load_entry_grid" class="btn btn-primary btn-block">
                                        <i class="fa fa-search"></i> Load
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="entry_marks_area" style="display:none;">
            <div class="col-md-12" id="entry_marks_container"></div>
        </div>
    </section>
</div>

<script type="text/javascript">
    (function() {
        "use strict";
        var base_url = (typeof window.base_url !== "undefined") ? window.base_url : "<?php echo base_url(); ?>";
        var currentExamId = 0;

        function showMsg(msg, type) {
            var cls = type === 'error' ? 'danger' : 'success';
            $('#entry_marks_container').prepend('<div class="alert alert-' + cls + '">' + msg + '</div>');
        }

        $(document).on('change', '#entry_exam_id', function() {
            currentExamId = $(this).val();
            $('#entry_exam_subject_id').html('<option value="">Select</option>');
            if (!currentExamId) {
                return;
            }
            $.ajax({
                url: base_url + "admin/quickexam/getsubjects",
                type: "GET",
                dataType: "json",
                data: {exam_id: currentExamId},
                success: function(res) {
                    if (res.status) {
                        var options = '<option value="">Select</option>';
                        $.each(res.subjects, function(i, row) {
                            options += "<option value='" + row.id + "'>" + row.subject_name + " (" + row.max_marks + ")</option>";
                        });
                        $('#entry_exam_subject_id').html(options);
                    }
                }
            });
        });

        $(document).on('click', '#load_entry_grid', function() {
            var examId = $('#entry_exam_id').val();
            var examSubjectId = $('#entry_exam_subject_id').val();
            if (!examId || !examSubjectId) {
                alert("Please select test and subject.");
                return;
            }
            $.ajax({
                url: base_url + "admin/quickexam/loadmarksgrid",
                type: "GET",
                dataType: "json",
                data: {exam_id: examId, exam_subject_id: examSubjectId},
                success: function(res) {
                    if (res.status) {
                        currentExamId = examId;
                        $('#entry_marks_area').show();
                        $('#entry_marks_container').html(res.grid);
                    } else {
                        alert(res.message ? res.message : "Unable to load marks.");
                    }
                }
            });
        });

        $(document).on('change', '#grid_subject_selector', function() {
            var subjectId = $(this).val();
            if (!currentExamId || !subjectId) {
                return;
            }
            $.ajax({
                url: base_url + "admin/quickexam/loadmarksgrid",
                type: "GET",
                dataType: "json",
                data: {
                    exam_id: currentExamId,
                    exam_subject_id: subjectId
                },
                success: function(res) {
                    if (res.status) {
                        $('#entry_marks_container').html(res.grid);
                        $('#entry_exam_subject_id').val(subjectId);
                    }
                }
            });
        });

        $(document).on('submit', '#quick_marks_form', function(e) {
            e.preventDefault();
            var $form = $(this);
            $.ajax({
                url: base_url + "admin/quickexam/savemarks",
                type: "POST",
                data: $form.serialize(),
                dataType: "json",
                success: function(res) {
                    if (res.status) {
                        showMsg(res.message, 'success');
                    } else {
                        showMsg("Unable to save marks.", 'error');
                    }
                }
            });
        });

        $(document).on('click', '#publish_notify', function() {
            if (!currentExamId) {
                alert("Load test first.");
                return;
            }
            var requestData = $('#quick_marks_form').serializeArray();
            requestData.push({name: 'exam_id', value: currentExamId});
            requestData.push({name: 'send_notification', value: 1});
            $.ajax({
                url: base_url + "admin/quickexam/publish",
                type: "POST",
                data: requestData,
                dataType: "json",
                success: function(res) {
                    if (res.status) {
                        showMsg(res.message ? res.message : "Published and notification sent successfully.", 'success');
                    } else {
                        showMsg(res.message ? res.message : "Unable to publish.", 'error');
                    }
                }
            });
        });
    })();
</script>
