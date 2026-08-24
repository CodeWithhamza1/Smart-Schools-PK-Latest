<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-check-square-o"></i> Quick Examination</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_create" data-toggle="tab"><i class="fa fa-plus-circle"></i> Create Test</a></li>
                        <li><a href="#tab_entry" data-toggle="tab"><i class="fa fa-edit"></i> Enter Obtained Marks</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_create">
                            <form id="quick_test_form" method="post">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Test Title <small class="req">*</small></label>
                                            <input type="text" name="test_title" class="form-control" placeholder="e.g. Unit Test 1">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('class'); ?> <small class="req">*</small></label>
                                            <select name="class_id" id="class_id" class="form-control">
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                <?php foreach ($classlist as $class) { ?>
                                                    <option value="<?php echo $class['id']; ?>"><?php echo $class['class']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('section'); ?> <small class="req">*</small></label>
                                            <select name="section_id" id="section_id" class="form-control">
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('date'); ?> <small class="req">*</small></label>
                                            <input type="text" name="test_date" class="form-control date">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Max Marks <small class="req">*</small></label>
                                            <input type="number" step="0.01" name="max_marks" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <label>Pass</label>
                                            <input type="number" step="0.01" name="pass_marks" class="form-control" value="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('subject'); ?> <small class="req">*</small></label>
                                            <select name="subject_id" id="subject_id" class="form-control">
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                <?php foreach ($subjectlist as $subject) { ?>
                                                    <option value="<?php echo $subject['id']; ?>"><?php echo $subject['name']; ?></option>
                                                <?php } ?>
                                            </select>
                                            <p class="help-block" style="margin-bottom:0;">Default mode: single subject test.</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group" style="margin-top:26px;">
                                            <label>
                                                <input type="checkbox" id="separate_subject_entry" name="separate_subject_entry" value="1">
                                                Optional: Enter every subject marks separately
                                            </label>
                                        </div>
                                    </div>
                                    <div class="col-md-4" id="subject_ids_wrap" style="display:none;">
                                        <div class="form-group">
                                            <label>Select Subjects</label>
                                            <select name="subject_ids[]" id="subject_ids" class="form-control" multiple>
                                                <?php foreach ($subjectlist as $subject) { ?>
                                                    <option value="<?php echo $subject['id']; ?>"><?php echo $subject['name']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <a href="#quick_advanced" data-toggle="collapse"><i class="fa fa-cog"></i> More Options</a>
                                    </div>
                                </div>
                                <div class="row collapse" id="quick_advanced" style="margin-top:10px;">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Start Time</label>
                                            <input type="text" name="test_time" class="form-control" placeholder="09:00 AM">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Duration (minutes)</label>
                                            <input type="number" name="duration" class="form-control" min="0" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('description'); ?></label>
                                            <input type="text" name="description" class="form-control" placeholder="Optional note for this test">
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <button type="reset" class="btn btn-default"><i class="fa fa-refresh"></i> Reset</button>
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Create & Start Entry</button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane" id="tab_entry">
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
        </div>

        <div class="row" id="marks_area" style="display:none;">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                <input type="text" id="student_search" class="form-control" placeholder="Search student by name/admission/roll">
                            </div>
                            <div class="col-md-8 text-right">
                                <button type="button" id="mark_all_present" class="btn btn-default btn-sm"><i class="fa fa-check"></i> Mark All Present</button>
                                <button type="button" id="mark_all_absent" class="btn btn-default btn-sm"><i class="fa fa-times"></i> Mark All Absent</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="marks_container"></div>
            </div>
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
            $('#marks_container').prepend('<div class="alert alert-' + cls + '">' + msg + '</div>');
        }

        function getSectionByClass(class_id, selected) {
            if (class_id === '') {
                $('#section_id').html('<option value=""><?php echo $this->lang->line('select'); ?></option>');
                return;
            }
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id},
                dataType: "json",
                success: function(data) {
                    var options = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                    $.each(data, function(i, obj) {
                        var sel = (selected && selected == obj.section_id) ? "selected" : "";
                        options += "<option value='" + obj.section_id + "' " + sel + ">" + obj.section + "</option>";
                    });
                    $('#section_id').html(options);
                }
            });
        }

        function loadSubjects(examId, targetSelector) {
            $(targetSelector).html('<option value="">Select</option>');
            if (!examId) return;
            $.ajax({
                url: base_url + "admin/quickexam/getsubjects",
                type: "GET",
                dataType: "json",
                data: {exam_id: examId},
                success: function(res) {
                    if (res.status) {
                        var options = '<option value="">Select</option>';
                        $.each(res.subjects, function(i, row) {
                            options += "<option value='" + row.id + "'>" + row.subject_name + " (" + row.max_marks + ")</option>";
                        });
                        $(targetSelector).html(options);
                    }
                }
            });
        }

        function loadGrid(examId, subjectId) {
            $.ajax({
                url: base_url + "admin/quickexam/loadmarksgrid",
                type: "GET",
                dataType: "json",
                data: {exam_id: examId, exam_subject_id: subjectId},
                success: function(res) {
                    if (res.status) {
                        currentExamId = examId;
                        $('#marks_area').show();
                        $('#marks_container').html(res.grid);
                        $('html, body').animate({scrollTop: $('#marks_area').offset().top - 80}, 250);
                    } else {
                        alert(res.message ? res.message : "Unable to load marks grid.");
                    }
                }
            });
        }

        $(document).on('change', '#class_id', function() {
            getSectionByClass($(this).val(), 0);
        });

        $(document).on('change', '#separate_subject_entry', function() {
            if ($(this).is(':checked')) {
                $('#subject_ids_wrap').show();
                $('#subject_id').prop('disabled', true);
            } else {
                $('#subject_ids_wrap').hide();
                $('#subject_id').prop('disabled', false);
            }
        });

        $(document).on('submit', '#quick_test_form', function(e) {
            e.preventDefault();
            var $form = $(this);
            $.ajax({
                url: base_url + "admin/quickexam/createtest",
                type: "POST",
                data: $form.serialize(),
                dataType: "json",
                beforeSend: function() {
                    $form.find("button[type=submit]").prop('disabled', true);
                },
                success: function(res) {
                    if (res.status) {
                        currentExamId = res.exam_id;
                        $('#entry_exam_id').val(res.exam_id).trigger('change');
                        loadGrid(res.exam_id, res.exam_subject_id);
                        showMsg("Test created. Start entering obtained marks below.", 'success');
                    } else {
                        var msg = res.message ? res.message : "Unable to create test. Please review form values.";
                        alert(msg);
                    }
                },
                complete: function() {
                    $form.find("button[type=submit]").prop('disabled', false);
                }
            });
        });

        $(document).on('change', '#entry_exam_id', function() {
            currentExamId = $(this).val();
            loadSubjects(currentExamId, '#entry_exam_subject_id');
        });

        $(document).on('click', '#load_entry_grid', function() {
            var examId = $('#entry_exam_id').val();
            var subjectId = $('#entry_exam_subject_id').val();
            if (!examId || !subjectId) {
                alert("Please select test and subject.");
                return;
            }
            loadGrid(examId, subjectId);
        });

        $(document).on('change', '#grid_subject_selector', function() {
            var subjectId = $(this).val();
            if (!currentExamId || !subjectId) return;
            $('#entry_exam_subject_id').val(subjectId);
            loadGrid(currentExamId, subjectId);
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

        $(document).on('click', '#mark_all_present', function() {
            $('#marks_container select[name^="attendance_"]').val('present');
        });

        $(document).on('click', '#mark_all_absent', function() {
            $('#marks_container select[name^="attendance_"]').val('absent');
        });

        $(document).on('keyup', '#student_search', function() {
            var term = $(this).val().toLowerCase();
            $('#quick_marks_form tbody tr').each(function() {
                var txt = $(this).text().toLowerCase();
                $(this).toggle(txt.indexOf(term) > -1);
            });
        });

        var mode = new URLSearchParams(window.location.search).get('mode');
        if (mode === 'entry') {
            $('.nav-tabs a[href="#tab_entry"]').tab('show');
        }
    })();
</script>

