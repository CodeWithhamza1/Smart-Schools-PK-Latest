<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-whatsapp"></i> Send Whatsapp</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if ($this->session->flashdata('msg')) { ?>
                    <?php echo $this->session->flashdata('msg'); ?>
                <?php } ?>
            </div>

            <div class="col-md-5">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Create Message Campaign</h3>
                    </div>
                    <form action="<?php echo site_url('admin/whatsappcampaign'); ?>" method="post" id="whatsapp_campaign_form">
                        <div class="box-body">
                            <div class="form-group">
                                <label>Campaign Title <small class="req">*</small></label>
                                <input type="text" name="title" class="form-control" value="<?php echo set_value('title'); ?>" placeholder="April fee reminder">
                            </div>

                            <div class="form-group">
                                <label>Message Type <small class="req">*</small></label>
                                <select name="campaign_type" id="campaign_type" class="form-control">
                                    <option value="custom_message" <?php echo set_select('campaign_type', 'custom_message', true); ?>>Custom Message</option>
                                    <option value="fee_message" <?php echo set_select('campaign_type', 'fee_message'); ?>>Fees Message</option>
                                    <option value="absent_message" <?php echo set_select('campaign_type', 'absent_message'); ?>>Absent Student Message</option>
                                    <option value="exam_result" <?php echo set_select('campaign_type', 'exam_result'); ?>>Exam Result</option>
                                </select>
                            </div>

                            <div class="row exam-result-fields" style="display:none;">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Exam Group <small class="req">*</small></label>
                                        <select name="exam_group_id" id="exam_group_id" class="form-control">
                                            <option value="">-- Select Exam Group --</option>
                                            <?php foreach ($examgrouplist as $eg) { ?>
                                                <option value="<?php echo $eg['id']; ?>" <?php echo set_select('exam_group_id', $eg['id']); ?>><?php echo html_escape($eg['exam_group']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Exam <small class="req">*</small></label>
                                        <select name="exam_id" id="exam_id" class="form-control">
                                            <option value="">-- Select Exam Group first --</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group custom-recipient-field">
                                <label>Recipients <small class="req">*</small></label>
                                <select name="target_type" id="target_type" class="form-control">
                                    <option value="all_students" <?php echo set_select('target_type', 'all_students', true); ?>>All Students</option>
                                    <option value="class_section" <?php echo set_select('target_type', 'class_section'); ?>>Specific Class / Section</option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Class</label>
                                        <select name="class_id" id="class_id" class="form-control">
                                            <option value="">All Classes</option>
                                            <?php foreach ($classlist as $class) { ?>
                                                <option value="<?php echo $class['id']; ?>" <?php echo set_select('class_id', $class['id']); ?>><?php echo $class['class']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Section</label>
                                        <select name="section_id" id="section_id" class="form-control">
                                            <option value="">All Sections</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Delivery <small class="req">*</small></label>
                                <select name="delivery_mode" id="delivery_mode" class="form-control">
                                    <option value="immediate" <?php echo set_select('delivery_mode', 'immediate', true); ?>>Send after validation</option>
                                    <option value="specific_datetime" <?php echo set_select('delivery_mode', 'specific_datetime'); ?>>Specific Date / Time</option>
                                    <option value="fee_due_date" <?php echo set_select('delivery_mode', 'fee_due_date'); ?>>Auto on Fee Due Date</option>
                                </select>
                            </div>

                            <div class="row schedule-once" style="display:none;">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Scheduled Date</label>
                                        <input type="date" name="scheduled_date" class="form-control" value="<?php echo set_value('scheduled_date', date('Y-m-d')); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Scheduled Time</label>
                                        <input type="time" name="scheduled_time" class="form-control" value="<?php echo set_value('scheduled_time', date('H:i')); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group schedule-due-date" style="display:none;">
                                <label>Fee Due Date Offset</label>
                                <input type="number" name="fee_reminder_offset_days" class="form-control" value="<?php echo set_value('fee_reminder_offset_days', 0); ?>">
                                <p class="help-block">Use 0 for exact due date, 1 for one day before, and -1 for one day after.</p>
                            </div>

                            <input type="hidden" name="template_type" id="template_type" value="<?php echo set_value('template_type', 'general_notice'); ?>">

                            <div class="form-group">
                                <label>Message Template <small class="req">*</small></label>
                                <textarea name="message_template" id="message_template" class="form-control" rows="8"><?php echo set_value('message_template', 'Dear {guardian_name}, this is an important message for {student_name} ({class}-{section}) from {school_name}.'); ?></textarea>
                            </div>

                            <div class="form-group">
                                <label>Variables</label>
                                <div class="well well-sm" style="margin-bottom:0;">
                                    <?php
                                    $tags = array('{student_name}', '{guardian_name}', '{class}', '{section}', '{fee_amount}', '{due_amount}', '{due_date}', '{attendance_date}', '{school_name}', '{phone}');
                                    foreach ($tags as $tag) {
                                        ?>
                                        <button type="button" class="btn btn-default btn-xs insert-tag" data-tag="<?php echo $tag; ?>"><?php echo $tag; ?></button>
                                    <?php } ?>
                                </div>
                                <div class="well well-sm exam-result-vars" style="margin-top:5px;margin-bottom:0;display:none;">
                                    <small class="text-muted">Exam variables:</small><br>
                                    <?php
                                    $exam_tags = array('{exam_name}', '{total_marks}', '{obtained_marks}', '{percentage}');
                                    foreach ($exam_tags as $tag) {
                                        ?>
                                        <button type="button" class="btn btn-info btn-xs insert-tag" data-tag="<?php echo $tag; ?>"><?php echo $tag; ?></button>
                                    <?php } ?>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                After validation, the campaign creates queued message rows and a CSV compatible with the Smart Schools WhatsApp extension.
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-success pull-right"><i class="fa fa-check"></i> Save Campaign</button>
                        </div>
                    </form>
                </div>

                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Extension Connection</h3>
                    </div>
                    <div class="box-body">
                        <dl>
                            <dt>Extension folder</dt>
                            <dd><code><?php echo html_escape($extension_path); ?></code></dd>
                            <dt>Base URL</dt>
                            <dd><code><?php echo site_url(); ?></code></dd>
                            <dt>CSV feed endpoint</dt>
                            <dd><code><?php echo site_url('whatsappweb/pullcsv'); ?></code></dd>
                            <dt>Security key</dt>
                            <dd><code><?php echo !empty($security_key) ? html_escape($security_key) : 'Set this in General Settings first.'; ?></code></dd>
                        </dl>
                        <p class="help-block">Load the folder above from Chrome Extensions developer mode. The extension can pull the CSV feed automatically, or you can download a campaign CSV and import it in the extension popup.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-7">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Campaign Queue</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Campaign</th>
                                    <th>Recipients</th>
                                    <th>Sent</th>
                                    <th>Failed</th>
                                    <th>Pending</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($campaigns)) { ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No WhatsApp campaigns created yet.</td>
                                    </tr>
                                <?php } ?>
                                <?php foreach ($campaigns as $campaign) { ?>
                                    <tr data-campaign-id="<?php echo $campaign['id']; ?>">
                                        <td>
                                            <strong><?php echo html_escape($campaign['title']); ?></strong><br>
                                            <small>
                                                <?php echo ucfirst(str_replace('_', ' ', $campaign['template_type'])); ?>
                                                <?php if (!empty($campaign['class_name'])) { ?>
                                                    - <?php echo html_escape($campaign['class_name']); ?><?php echo !empty($campaign['section_name']) ? ' / ' . html_escape($campaign['section_name']) : ''; ?>
                                                <?php } ?>
                                            </small>
                                        </td>
                                        <td class="campaign-total"><?php echo (int) $campaign['total_count']; ?></td>
                                        <td class="campaign-sent"><?php echo (int) $campaign['sent_count']; ?></td>
                                        <td class="campaign-failed"><?php echo (int) $campaign['failed_count']; ?></td>
                                        <td class="campaign-pending"><?php echo (int) $campaign['pending_count']; ?></td>
                                        <td class="campaign-status"><span class="label label-info"><?php echo ucfirst($campaign['status']); ?></span></td>
                                        <td>
                                            <a href="<?php echo site_url('admin/whatsappcampaign/view/' . $campaign['id']); ?>" class="btn btn-default btn-xs">View</a>
                                            <a href="<?php echo site_url('admin/whatsappcampaign/queuenow/' . $campaign['id']); ?>" class="btn btn-success btn-xs">Queue Now</a>
                                            <a href="<?php echo site_url('admin/whatsappcampaign/retryfailed/' . $campaign['id']); ?>" class="btn btn-danger btn-xs">Retry Failed</a>
                                            <a href="<?php echo site_url('admin/whatsappcampaign/downloadcsv/' . $campaign['id']); ?>" class="btn btn-primary btn-xs">CSV</a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
(function($) {
    "use strict";

    var templates = {
        custom_message: {
            type: "general_notice",
            text: "Dear {guardian_name}, this is an important message for {student_name} ({class}-{section}) from {school_name}."
        },
        fee_message: {
            type: "fee_reminder",
            text: "Dear {guardian_name}, {student_name} of class {class} has an outstanding fee of {due_amount}. Due date: {due_date}. Please clear it at the earliest. - {school_name}"
        },
        absent_message: {
            type: "absence_alert",
            text: "Dear {guardian_name}, {student_name} of class {class} was marked absent on {attendance_date}. - {school_name}"
        },
        exam_result: {
            type: "exam_result",
            text: "Dear {guardian_name}, {student_name} of class {class} has scored {obtained_marks} / {total_marks} ({percentage}%) in {exam_name}. - {school_name}"
        }
    };

    function loadSections(targetSelect, classId) {
        $(targetSelect).html('<option value="">All Sections</option>');
        if (classId === '') {
            return;
        }

        $.ajax({
            url: base_url + "sections/getByClass",
            data: {'class_id': classId},
            dataType: 'json',
            success: function(response) {
                $.each(response, function(index, value) {
                    $(targetSelect).append("<option value='" + value.section_id + "'>" + value.section + "</option>");
                });
            },
            error: function() {
                $(targetSelect).html('<option value="">-- Failed to load --</option>');
            }
        });
    }

    function loadExamsByGroup(groupId) {
        var $examSelect = $('#exam_id');
        $examSelect.html('<option value="">-- Loading... --</option>');
        if (!groupId) {
            $examSelect.html('<option value="">-- Select Exam Group first --</option>');
            return;
        }
        $.ajax({
            url: '<?php echo site_url('admin/whatsappcampaign/getexamsbygroup'); ?>',
            data: {exam_group_id: groupId},
            dataType: 'json',
            success: function(response) {
                $examSelect.html('<option value="">-- Select Exam --</option>');
                $.each(response, function(i, exam) {
                    $examSelect.append('<option value="' + exam.id + '">' + exam.exam + '</option>');
                });
                if (response.length === 0) {
                    $examSelect.html('<option value="">-- No exams found --</option>');
                }
            },
            error: function() {
                $examSelect.html('<option value="">-- Failed to load --</option>');
            }
        });
    }

    function statusBadge(status) {
        var map = {
            'Sent': 'success',
            'Failed': 'danger',
            'Pending': 'warning',
            'Processing': 'info',
            'Scheduled': 'default',
            'Completed': 'success'
        };
        var cls = map[status] || 'info';
        return '<span class="label label-' + cls + '">' + status + '</span>';
    }

    function updateCampaignControls(forceTemplate) {
        var campaignType = $('#campaign_type').val();
        var deliveryMode = $('#delivery_mode').val();

        if (deliveryMode === 'fee_due_date' && campaignType !== 'exam_result') {
            campaignType = 'fee_message';
            $('#campaign_type').val(campaignType);
        }

        var isExamResult = (campaignType === 'exam_result');
        var isCustom = (campaignType === 'custom_message');

        $('.custom-recipient-field').toggle(isCustom);
        $('.exam-result-fields').toggle(isExamResult);
        $('.exam-result-vars').toggle(isExamResult);

        if (isExamResult) {
            $('#delivery_mode option[value="fee_due_date"]').prop('disabled', true);
            if (deliveryMode === 'fee_due_date') {
                $('#delivery_mode').val('immediate');
                deliveryMode = 'immediate';
            }
        } else {
            $('#delivery_mode option[value="fee_due_date"]').prop('disabled', false);
        }

        if (deliveryMode === 'specific_datetime') {
            $('.schedule-once').show();
            $('.schedule-due-date').hide();
        } else if (deliveryMode === 'fee_due_date') {
            $('.schedule-once').hide();
            $('.schedule-due-date').show();
        } else {
            $('.schedule-once').hide();
            $('.schedule-due-date').hide();
        }

        if (templates[campaignType]) {
            $('#template_type').val(templates[campaignType].type);
            if (forceTemplate) {
                $('#message_template').val(templates[campaignType].text);
            }
        }
    }

    function refreshStatuses() {
        var ids = [];
        $('tr[data-campaign-id]').each(function() {
            ids.push($(this).data('campaign-id'));
        });

        if (ids.length === 0) {
            return;
        }

        $.ajax({
            url: '<?php echo site_url('admin/whatsappcampaign/livestatus'); ?>',
            type: 'POST',
            dataType: 'json',
            data: {ids: ids},
            success: function(response) {
                $.each(response, function(id, summary) {
                    var row = $('tr[data-campaign-id="' + id + '"]');
                    row.find('.campaign-total').text(summary.total_count || 0);
                    row.find('.campaign-sent').text(summary.sent_count || 0);
                    row.find('.campaign-failed').text(summary.failed_count || 0);
                    row.find('.campaign-pending').text(summary.pending_count || 0);
                    row.find('.campaign-status').html(statusBadge(summary.status));
                });
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    clearInterval(statusInterval);
                }
            }
        });
    }

    $(document).on('change', '#campaign_type', function() {
        updateCampaignControls(true);
    });

    $(document).on('change', '#delivery_mode', function() {
        updateCampaignControls(false);
    });

    $(document).on('change', '#class_id', function() {
        loadSections('#section_id', $(this).val());
    });

    $(document).on('change', '#exam_group_id', function() {
        loadExamsByGroup($(this).val());
    });

    $(document).on('click', '.insert-tag', function() {
        var textarea = document.getElementById('message_template');
        var tag = $(this).data('tag');
        var start = textarea.selectionStart || 0;
        var end = textarea.selectionEnd || 0;
        var value = textarea.value;
        textarea.value = value.substring(0, start) + tag + value.substring(end);
        textarea.focus();
        textarea.selectionStart = textarea.selectionEnd = start + tag.length;
    });

    updateCampaignControls(false);
    refreshStatuses();
    var statusInterval = setInterval(refreshStatuses, 5000);
})(jQuery);
</script>
