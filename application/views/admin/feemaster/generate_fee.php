<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<style>
    .generate-fee-page { --fee-navy:#17233c; --fee-blue:#1677b8; --fee-green:#169b62; --fee-orange:#e89a17; --fee-muted:#667085; --fee-border:#e3e8ef; }
    .generate-fee-page .content-header { padding-bottom:4px; }
    .generate-fee-page .page-subtitle { margin:5px 0 0; color:var(--fee-muted); font-size:13px; }
    .generate-fee-page .box { border-radius:7px; border-top:0; box-shadow:0 2px 8px rgba(23,35,60,.08); overflow:hidden; }
    .generate-fee-page .box-header { padding:16px 18px; border-bottom:1px solid var(--fee-border); }
    .generate-fee-page .box-title { font-weight:600; color:var(--fee-navy); }
    .generate-fee-page .step-number { display:inline-flex; width:27px; height:27px; margin-right:8px; border-radius:50%; align-items:center; justify-content:center; background:var(--fee-navy); color:#fff; font-size:13px; font-weight:700; }
    .generate-fee-page .session-pill { display:inline-block; margin-left:8px; padding:4px 9px; border-radius:12px; background:#eef4ff; color:#315b9a; font-size:12px; }
    .generate-fee-page .workflow-guide { display:flex; margin-bottom:16px; border:1px solid var(--fee-border); border-radius:7px; background:#fff; overflow:hidden; }
    .generate-fee-page .workflow-step { flex:1; padding:12px 16px; color:var(--fee-muted); border-right:1px solid var(--fee-border); }
    .generate-fee-page .workflow-step:last-child { border-right:0; }
    .generate-fee-page .workflow-step strong { color:var(--fee-navy); display:block; font-size:13px; }
    .generate-fee-page .workflow-step i { color:var(--fee-blue); margin-right:6px; }
    .generate-fee-page .criteria-row { display:flex; flex-wrap:wrap; gap:14px; align-items:flex-end; }
    .generate-fee-page .criteria-field { flex:1 1 180px; }
    .generate-fee-page .criteria-field label { display:block; color:#344054; font-size:12px; font-weight:600; margin-bottom:6px; }
    .generate-fee-page .form-control { border-color:#d7dde5; border-radius:4px; min-height:34px; }
    .generate-fee-page .load-button { min-width:145px; height:34px; }
    .generate-fee-page .fee-columns { display:flex; gap:10px; margin:0 46px 6px 9px; color:var(--fee-muted); font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.3px; }
    .generate-fee-page .fee-columns span:first-child { flex:2; }
    .generate-fee-page .fee-columns span:last-child { flex:1; }
    .generate-fee-page .fee-type-row { display:flex; gap:10px; align-items:center; margin-bottom:9px; padding:9px; border:1px solid var(--fee-border); border-radius:6px; background:#fafbfc; }
    .generate-fee-page .fee-type-row .fee-type-select { flex:2; }
    .generate-fee-page .fee-type-row .fee-amount { flex:1; }
    .generate-fee-page .fee-help { display:flex; gap:9px; padding:10px 12px; margin-top:10px; background:#f0f7fc; border-radius:5px; color:#46637a; font-size:12px; }
    .generate-fee-page .student-toolbar { display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:10px; margin-bottom:12px; }
    .generate-fee-page .selected-count { display:inline-block; padding:7px 11px; border-radius:16px; background:#eef4ff; color:#315b9a; font-weight:600; }
    .generate-fee-page .student-search { position:relative; width:260px; }
    .generate-fee-page .student-search i { position:absolute; left:11px; top:10px; color:#98a2b3; }
    .generate-fee-page .student-search input { padding-left:31px; }
    .generate-fee-page .status-summary { display:flex; flex-wrap:wrap; gap:8px; margin:0 0 14px; }
    .generate-fee-page .summary-chip { padding:7px 10px; border:1px solid var(--fee-border); border-radius:5px; background:#fff; font-size:12px; color:#475467; }
    .generate-fee-page .summary-chip strong { color:var(--fee-navy); font-size:14px; margin-right:3px; }
    .generate-fee-page .status-badge { display:inline-flex; align-items:center; margin:2px; padding:5px 8px; border-radius:4px; font-size:12px; line-height:1.3; }
    .generate-fee-page .status-badge.locked { background:#fff0f0; color:#a13b3b; border:1px solid #f1caca; }
    .generate-fee-page .status-badge.open { background:#eaf8f1; color:#176b49; border:1px solid #bee6d2; }
    .generate-fee-page .status-badge.missing { background:#fff7e8; color:#8a5a0a; border:1px solid #f2d49a; }
    .generate-fee-page .status-badge i { margin-right:5px; }
    .generate-fee-page .student-ineligible { opacity:.65; }
    .generate-fee-page .student-name { font-weight:600; color:var(--fee-navy); }
    .generate-fee-page .profile-fee { color:#344054; white-space:nowrap; }
    .generate-fee-page .action-panel { position:sticky; bottom:8px; z-index:5; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px; padding:13px 15px; margin-top:14px; border:1px solid var(--fee-border); border-radius:6px; background:#fff; box-shadow:0 3px 12px rgba(23,35,60,.12); }
    .generate-fee-page .action-note { color:var(--fee-muted); font-size:12px; max-width:570px; }
    .generate-fee-page .action-buttons { display:flex; gap:8px; justify-content:flex-end; }
    .generate-fee-page .action-buttons .btn { min-width:145px; }
    .generate-fee-page .loading-overlay { display:none; padding:12px; text-align:center; color:var(--fee-blue); font-weight:600; }
    .generate-fee-page .result-errors { margin:8px 0 0 18px; max-height:180px; overflow-y:auto; }
    .generate-fee-page .table > tbody > tr > td { vertical-align:middle; }
    .generate-fee-page .table > thead > tr > th { border-bottom:1px solid var(--fee-border); background:#f8fafc; color:#475467; font-size:12px; }
    .generate-fee-page .edit-student { color:var(--fee-blue); border-color:#b9d6e8; }
    .generate-fee-page .modal-section { padding:12px; margin-bottom:14px; border:1px solid var(--fee-border); border-radius:6px; }
    .generate-fee-page .modal-section h5 { margin:0 0 4px; color:var(--fee-navy); font-weight:600; }
    .generate-fee-page .modal-section-copy { color:var(--fee-muted); font-size:12px; margin-bottom:12px; }
    .generate-fee-page .modal-protection-note { display:none; padding:9px 11px; margin-bottom:13px; color:#8a4b12; background:#fff7e8; border-left:3px solid var(--fee-orange); }
    .generate-fee-page .override-state { display:block; margin-top:4px; font-size:11px; color:var(--fee-muted); }
    .generate-fee-page .override-state.protected { color:#a13b3b; }
    @media (max-width:767px) {
        .generate-fee-page .workflow-guide { flex-direction:column; }
        .generate-fee-page .workflow-step { border-right:0; border-bottom:1px solid var(--fee-border); }
        .generate-fee-page .fee-type-row { align-items:stretch; flex-direction:column; }
        .generate-fee-page .fee-columns { display:none; }
        .generate-fee-page .student-search { width:100%; }
        .generate-fee-page .action-panel { align-items:stretch; }
        .generate-fee-page .action-buttons { flex-direction:column; }
    }
</style>

<div class="content-wrapper generate-fee-page">
    <section class="content-header">
        <h1><i class="fa fa-money"></i> Generate Fee</h1>
        <p class="page-subtitle">Create new monthly fees or safely update fees that have not received a payment or discount.</p>
    </section>

    <section class="content">
        <div id="fee_result"></div>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><span class="step-number">1</span>Select billing period <span class="session-pill">Session <?php echo html_escape($current_session_name); ?></span></h3>
            </div>
            <div class="box-body">
                <div class="criteria-row">
                    <div class="criteria-field">
                        <label>Class <small class="req">*</small></label>
                        <select id="generation_class" class="form-control select2">
                            <option value="">Select</option>
                            <?php foreach ($classlist as $class) { ?>
                                <option value="<?php echo (int) $class['id']; ?>"><?php echo html_escape($class['class']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="criteria-field">
                        <label>Section</label>
                        <select id="generation_section" class="form-control select2">
                            <option value="">All Sections</option>
                        </select>
                    </div>
                    <div class="criteria-field">
                        <label>Billing Month <small class="req">*</small></label>
                        <input id="generation_month" type="month" class="form-control" value="<?php echo sprintf('%04d-%02d', $default_year, $default_month); ?>">
                    </div>
                    <div class="criteria-field">
                        <label>Due Date <small class="req">*</small></label>
                        <input id="generation_due_date" type="date" class="form-control" value="<?php echo html_escape($default_due_date); ?>">
                    </div>
                    <div>
                        <button id="load_students" type="button" class="btn btn-primary load-button"><i class="fa fa-search"></i> Load Students</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><span class="step-number">2</span>Choose fee types</h3>
                <div class="box-tools pull-right">
                    <button id="add_fee_type" type="button" class="btn btn-info btn-xs"><i class="fa fa-plus"></i> Add Fee Type</button>
                </div>
            </div>
            <div class="box-body">
                <div class="fee-columns"><span>Fee type</span><span>Amount</span></div>
                <div id="fee_type_rows"></div>
            </div>
        </div>

        <div id="student_box" class="box box-success" style="display:none;">
            <div class="box-header with-border">
                <h3 class="box-title"><span class="step-number">3</span>Review students and process fees</h3>
            </div>
            <div class="box-body">
                <div class="student-toolbar">
                    <div><span class="selected-count"><span id="selected_count">0</span> selected</span></div>
                    <div class="student-search">
                        <i class="fa fa-search"></i>
                        <input id="student_search" type="text" class="form-control" placeholder="Search name or admission no.">
                    </div>
                </div>
                <div class="status-summary">
                    <span class="summary-chip"><strong id="summary_students">0</strong> students</span>
                    <span class="summary-chip"><strong id="summary_missing">0</strong> not generated</span>
                    <span class="summary-chip"><strong id="summary_editable">0</strong> editable fee entries</span>
                    <span class="summary-chip"><strong id="summary_protected">0</strong> protected fee entries</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="generation_students_table">
                        <thead>
                        <tr>
                            <th width="35"><input type="checkbox" id="select_all_students" title="Select all eligible students"></th>
                            <th>Admission No.</th>
                            <th>Student</th>
                            <th>Class / Section</th>
                            <th class="text-right">Profile Monthly</th>
                            <th class="text-right">Profile Discount</th>
                            <th>Current Month Fee Status</th>
                            <th width="105">Action</th>
                        </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="loading-overlay" id="fee_loading"><i class="fa fa-spinner fa-spin"></i> Processing fee records...</div>
                <div class="action-panel">
                    <div class="action-buttons">
                        <?php if ($this->rbac->hasPrivilege('fees_master', 'can_edit')) { ?>
                            <button id="update_fees" type="button" class="btn btn-warning"><i class="fa fa-pencil"></i> Update Editable Fees</button>
                        <?php } ?>
                        <?php if ($this->rbac->hasPrivilege('fees_master', 'can_add')) { ?>
                            <button id="generate_fees" type="button" class="btn btn-success"><i class="fa fa-bolt"></i> Generate Missing Fees</button>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="student_fee_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Edit Student Fees - <span id="modal_student_name"></span></h4>
            </div>
            <div class="modal-body">
                <input type="hidden" id="modal_student_session_id">
                <div id="modal_protection_note" class="modal-protection-note"><i class="fa fa-lock"></i> Some current-month fees are protected because a payment or discount was applied. You may change profile defaults for future fees, but protected current fees will not change.</div>
                <div class="modal-section">
                    <h5>Student profile defaults</h5>
                    <div class="modal-section-copy">These values are saved to the student profile and used when future fees are generated.</div>
                    <div class="row">
                        <div class="col-sm-4 form-group">
                            <label>Monthly Fee</label>
                            <input id="modal_monthly_fee" type="number" min="0" step="0.01" class="form-control">
                        </div>
                        <div class="col-sm-4 form-group">
                            <label>Admission Fee</label>
                            <input id="modal_admission_fee" type="number" min="0" step="0.01" class="form-control">
                        </div>
                        <div class="col-sm-4 form-group">
                            <label>Discount</label>
                            <input id="modal_discount_fee" type="number" min="0" step="0.01" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-section">
                    <h5>Selected month fee amounts</h5>
                    <div class="modal-section-copy">Optionally enter a different amount for this student. Protected current-month entries are shown but cannot be changed.</div>
                    <div id="modal_fee_overrides"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" id="save_student_edit" class="btn btn-primary">Apply Edit</button>
            </div>
        </div>
    </div>
</div>

<script>
(function ($) {
    'use strict';

    var baseUrl = <?php echo json_encode(base_url()); ?>;
    var currency = <?php echo json_encode($currency_symbol); ?>;
    var operationToken = <?php echo json_encode($operation_token); ?>;
    var feeTypes = <?php echo json_encode(array_values($feetypeList)); ?>;
    var students = {};
    var feeRowCounter = 0;
    var processing = false;

    function escapeHtml(value) {
        return $('<div>').text(value === null || value === undefined ? '' : value).html();
    }

    function money(value) {
        var number = parseFloat(value);
        return isNaN(number) ? '0.00' : number.toFixed(2);
    }

    function selectedBilling() {
        var parts = ($('#generation_month').val() || '').split('-');
        return {year: parseInt(parts[0], 10) || 0, month: parseInt(parts[1], 10) || 0};
    }

    function feeTypeOptions(selectedId) {
        var options = '<option value="">Select Fee Type</option>';
        $.each(feeTypes, function (_, fee) {
            var selected = String(fee.id) === String(selectedId) ? ' selected' : '';
            options += '<option value="' + parseInt(fee.id, 10) + '" data-code="' + escapeHtml(fee.code) + '"' + selected + '>' + escapeHtml(fee.type) + '</option>';
        });
        return options;
    }

    function addFeeTypeRow(selectedId, amount) {
        feeRowCounter++;
        var row = $('<div class="fee-type-row" data-row="' + feeRowCounter + '">' +
            '<select class="form-control fee-type-select">' + feeTypeOptions(selectedId) + '</select>' +
            '<input type="number" min="0" step="0.01" class="form-control fee-amount" placeholder="Common amount" value="' + escapeHtml(amount || '') + '">' +
            '<button type="button" class="btn btn-danger remove-fee-type"><i class="fa fa-minus"></i></button>' +
            '</div>');
        $('#fee_type_rows').append(row);
        updateFeeAmountState(row);
    }

    function updateFeeAmountState(row) {
        var code = row.find('.fee-type-select option:selected').data('code') || '';
        var automatic = String(code).toLowerCase() === 'tuition' || String(code).toLowerCase() === 'admission';
        row.find('.fee-amount').prop('disabled', automatic).attr('placeholder', automatic ? 'Per student' : 'Common amount');
    }

    function collectFees(showError) {
        var fees = [], seen = {}, invalid = false;
        $('#fee_type_rows .fee-type-row').each(function () {
            var option = $(this).find('.fee-type-select option:selected');
            var id = parseInt(option.val(), 10) || 0;
            var code = option.data('code') || '';
            var amount = $(this).find('.fee-amount').val();
            if (!id || seen[id]) {
                invalid = true;
                return;
            }
            if (String(code).toLowerCase() !== 'tuition' && String(code).toLowerCase() !== 'admission' && (amount === '' || parseFloat(amount) < 0)) {
                invalid = true;
                return;
            }
            seen[id] = true;
            fees.push({feetype_id: id, type: option.text(), code: code, amount: amount === '' ? 0 : amount});
        });
        if ((invalid || !fees.length) && showError) {
            showResult('danger', 'Select at least one unique fee type and enter valid common amounts.');
        }
        return invalid ? [] : fees;
    }

    function loadSections() {
        var classId = $('#generation_class').val();
        $('#generation_section').html('<option value="">All Sections</option>');
        if (!classId) return;
        $.ajax({
            url: baseUrl + 'sections/getByClass',
            type: 'GET',
            dataType: 'json',
            data: {class_id: classId}
        }).done(function (rows) {
            $.each(rows || [], function (_, row) {
                $('#generation_section').append($('<option>', {value: row.section_id, text: row.section}));
            });
        });
    }

    function criteriaPayload() {
        var billing = selectedBilling();
        return {
            class_id: parseInt($('#generation_class').val(), 10) || 0,
            section_id: parseInt($('#generation_section').val(), 10) || 0,
            year: billing.year,
            month: billing.month,
            due_date: $('#generation_due_date').val()
        };
    }

    function fetchStudents(silent) {
        var payload = criteriaPayload();
        return $.ajax({
            url: baseUrl + 'admin/feemaster/loadGenerationStudents',
            type: 'POST',
            dataType: 'json',
            data: {payload: JSON.stringify(payload)}
        }).done(function (response) {
            students = {};
            $('#student_search').val('');
            $.each(response.students || [], function (_, student) {
                student.selected = !!student.eligible;
                student.overrides = {};
                students[String(student.student_session_id)] = student;
            });
            renderStudents();
            $('#student_box').show();
            if (!silent) showResult('success', (response.students || []).length + ' active student(s) loaded.');
        });
    }

    function loadStudents(silent) {
        if (processing) return;
        var payload = criteriaPayload();
        if (!payload.class_id || !payload.year || !payload.month || !payload.due_date) {
            showResult('danger', 'Select class, billing month and due date.');
            return;
        }
        setBusy(true);
        return fetchStudents(silent).fail(showAjaxError).always(function () { setBusy(false); });
    }

    function clearLoadedStudents() {
        if (!Object.keys(students).length) return;
        students = {};
        $('#student_box').hide();
        $('#fee_result').html('<div class="alert alert-info">Billing criteria changed. Click <strong>Load Students</strong> to review the correct records.</div>');
    }

    function statusHtml(student) {
        if (!student.eligible) return '<span class="status-badge missing"><i class="fa fa-ban"></i>Not eligible — admitted after this month</span>';
        if (!student.fees || !student.fees.length) return '<span class="status-badge missing"><i class="fa fa-clock-o"></i>Not generated yet</span>';
        var html = '';
        $.each(student.fees, function (_, fee) {
            var icon = fee.locked ? 'fa-lock' : 'fa-pencil';
            var state = fee.duplicate ? 'Protected duplicate' : (fee.locked ? 'Protected' : 'Editable');
            var title = fee.duplicate ? 'Multiple legacy entries exist; this fee is protected.' : (fee.locked ? 'Payment or discount applied; current amount cannot be changed.' : 'No settlement applied; current amount can be updated.');
            html += '<span class="status-badge ' + (fee.locked ? 'locked' : 'open') + '" title="' + escapeHtml(title) + '"><i class="fa ' + icon + '"></i>' +
                escapeHtml(fee.type) + ': ' + escapeHtml(currency) + money(fee.amount) + ' · ' + state + '</span>';
        });
        return html;
    }

    function renderStudents() {
        var body = $('#generation_students_table tbody').empty();
        var search = $.trim($('#student_search').val() || '').toLowerCase();
        var visible = 0;
        $.each(students, function (id, student) {
            var fullName = $.trim((student.firstname || '') + ' ' + (student.middlename || '') + ' ' + (student.lastname || ''));
            var searchable = (fullName + ' ' + (student.admission_no || '') + ' ' + (student.class || '') + ' ' + (student.section || '')).toLowerCase();
            if (search && searchable.indexOf(search) === -1) return;
            visible++;
            var checked = student.selected ? ' checked' : '';
            var disabled = student.eligible ? '' : ' disabled';
            var rowClass = student.eligible ? '' : ' class="student-ineligible"';
            body.append('<tr data-session-id="' + parseInt(id, 10) + '"' + rowClass + '>' +
                '<td><input type="checkbox" class="student-select"' + checked + disabled + '></td>' +
                '<td>' + escapeHtml(student.admission_no) + '</td>' +
                '<td><span class="student-name">' + escapeHtml(fullName) + '</span></td>' +
                '<td>' + escapeHtml(student.class) + ' / ' + escapeHtml(student.section) + '</td>' +
                '<td class="text-right monthly-value profile-fee">' + escapeHtml(currency) + money(student.monthly_fee) + '</td>' +
                '<td class="text-right discount-value profile-fee">' + escapeHtml(currency) + money(student.discount_fee) + '</td>' +
                '<td>' + statusHtml(student) + '</td>' +
                '<td><button type="button" class="btn btn-default btn-xs edit-student" title="Review and edit"><i class="fa fa-pencil"></i> ' + (student.edited ? 'Edited' : 'Edit') + '</button></td>' +
                '</tr>');
        });
        if (!visible) body.append('<tr><td colspan="8" class="text-center text-muted">No students match your search.</td></tr>');
        updateStatusSummary();
        updateSelectedCount();
    }

    function updateStatusSummary() {
        var studentCount = 0, missing = 0, editable = 0, protectedCount = 0;
        $.each(students, function (_, student) {
            studentCount++;
            if (student.eligible && (!student.fees || !student.fees.length)) missing++;
            $.each(student.fees || [], function (_, fee) {
                if (fee.locked) protectedCount++; else editable++;
            });
        });
        $('#summary_students').text(studentCount);
        $('#summary_missing').text(missing);
        $('#summary_editable').text(editable);
        $('#summary_protected').text(protectedCount);
    }

    function updateSelectedCount() {
        var selected = 0, eligible = 0;
        $.each(students, function (_, student) {
            if (student.eligible) eligible++;
            if (student.selected) selected++;
        });
        $('#selected_count').text(selected);
        $('#select_all_students').prop('checked', eligible > 0 && selected === eligible);
    }

    function existingFee(student, feeTypeId) {
        var match = null;
        $.each(student.fees || [], function (_, fee) {
            if (String(fee.feetype_id) === String(feeTypeId)) match = fee;
        });
        return match;
    }

    function openStudentEdit(id) {
        var student = students[String(id)];
        if (!student) return;
        $('#modal_student_session_id').val(id);
        $('#modal_student_name').text($.trim((student.firstname || '') + ' ' + (student.lastname || '')));
        $('#modal_monthly_fee').val(money(student.monthly_fee));
        $('#modal_admission_fee').val(money(student.admission_fee));
        $('#modal_discount_fee').val(money(student.discount_fee));
        var hasProtectedFee = false;
        $.each(student.fees || [], function (_, fee) { if (fee.locked) hasProtectedFee = true; });
        $('#modal_protection_note').toggle(hasProtectedFee);
        var overrides = $('#modal_fee_overrides').empty();
        $.each(collectFees(false), function (_, fee) {
            var value = student.overrides && student.overrides[String(fee.feetype_id)] !== undefined ? student.overrides[String(fee.feetype_id)] : '';
            var currentFee = existingFee(student, fee.feetype_id);
            var protectedFee = currentFee && currentFee.locked;
            var stateText = protectedFee
                ? '<span class="override-state protected"><i class="fa fa-lock"></i> Current fee is protected and will be skipped.</span>'
                : '<span class="override-state"><i class="fa fa-check-circle"></i> ' + (currentFee ? 'Current fee is editable.' : 'Fee has not been generated yet.') + '</span>';
            overrides.append('<div class="form-group"><label>' + escapeHtml(fee.type) + '</label>' +
                '<input type="number" min="0" step="0.01" class="form-control modal-override" data-feetype-id="' + fee.feetype_id + '" value="' + escapeHtml(value) + '" placeholder="Use default amount"' + (protectedFee ? ' disabled' : '') + '>' + stateText + '</div>');
        });
        $('#student_fee_modal').modal('show');
    }

    function saveStudentEdit() {
        var id = String($('#modal_student_session_id').val());
        var student = students[id];
        var monthly = parseFloat($('#modal_monthly_fee').val());
        var admission = parseFloat($('#modal_admission_fee').val());
        var discount = parseFloat($('#modal_discount_fee').val());
        if (!student || isNaN(monthly) || isNaN(admission) || isNaN(discount) || monthly < 0 || admission < 0 || discount < 0 || discount > monthly) {
            showResult('danger', 'Enter valid non-negative values. Discount cannot exceed monthly fee.');
            return;
        }
        student.monthly_fee = monthly;
        student.admission_fee = admission;
        student.discount_fee = discount;
        student.final_fee = monthly - discount;
        student.overrides = {};
        $('.modal-override').each(function () {
            if ($(this).val() !== '') student.overrides[String($(this).data('feetype-id'))] = $(this).val();
        });
        student.edited = true;
        $('#student_fee_modal').modal('hide');
        renderStudents();
        showResult('info', 'Changes prepared for ' + $.trim((student.firstname || '') + ' ' + (student.lastname || '')) + '. Click Update Editable Fees to save them.');
    }

    function operationPayload(action) {
        var payload = criteriaPayload();
        payload.fees = collectFees(true);
        payload.students = [];
        payload.operation_token = operationToken;
        // The server derives the audit request ID. The browser only supplies a
        // per-click nonce so the session token is never embedded in an ID.
        payload.request_nonce = String(Date.now());
        $.each(students, function (_, student) {
            payload.students.push({
                selected: !!student.selected,
                student_session_id: parseInt(student.student_session_id, 10),
                monthly_fee: student.monthly_fee,
                admission_fee: student.admission_fee,
                discount_fee: student.discount_fee,
                overrides: student.overrides || {}
            });
        });
        return payload;
    }

    function submitOperation(action) {
        if (processing) return;
        var payload = operationPayload(action);
        if (!payload.fees.length) return;
        var selected = $.grep(payload.students, function (student) { return student.selected; }).length;
        if (!selected) {
            showResult('danger', 'Select at least one student.');
            return;
        }
        var label = action === 'update' ? 'update student profiles and eligible unpaid generated fees' : 'generate missing fees';
        if (!window.confirm('This will ' + label + ' for ' + selected + ' student(s). Continue?')) return;

        setBusy(true);
        $.ajax({
            url: baseUrl + 'admin/feemaster/' + (action === 'update' ? 'updateGenerationFees' : 'runGeneration'),
            type: 'POST',
            dataType: 'json',
            data: {payload: JSON.stringify(payload)}
        }).done(function (response) {
            var c = response.counts || {};
            var summary = action === 'update'
                ? 'Profiles updated: ' + (c.updated || 0) + ', fee rows updated: ' + (c.updated_fee_rows || 0) + ', locked: ' + (c.locked || 0) + '.'
                : 'Created: ' + (c.created || 0) + ', already existing: ' + (c.existing || 0) + ', protected duplicates: ' + (c.locked || 0) + ', ineligible: ' + (c.ineligible || 0) + '.';
            showResult('success', summary, response.errors || []);
            fetchStudents(true).fail(showAjaxError).always(function () { setBusy(false); });
        }).fail(function (xhr) {
            showAjaxError(xhr);
            setBusy(false);
        });
    }

    function showResult(type, message, errors) {
        var html = '<div class="alert alert-' + type + ' alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>' + escapeHtml(message);
        if (errors && errors.length) {
            html += '<ul class="result-errors">';
            $.each(errors, function (_, error) { html += '<li>' + escapeHtml(error) + '</li>'; });
            html += '</ul>';
        }
        html += '</div>';
        $('#fee_result').html(html);
        window.scrollTo(0, 0);
    }

    function showAjaxError(xhr) {
        var response = xhr.responseJSON || {};
        var errors = response.errors || ['The fee operation could not be completed.'];
        showResult('danger', errors[0], errors.slice(1));
    }

    function setBusy(busy) {
        processing = busy;
        $('#fee_loading').toggle(busy);
        $('#load_students, #update_fees, #generate_fees, #add_fee_type, #save_student_edit, #select_all_students, #student_search').prop('disabled', busy);
        $('#generation_class, #generation_section, #generation_month, #generation_due_date, .fee-type-select, .remove-fee-type, .edit-student').prop('disabled', busy);
        if (busy) {
            $('.fee-amount').prop('disabled', true);
        } else {
            $('#fee_type_rows .fee-type-row').each(function () { updateFeeAmountState($(this)); });
        }
        $('#generation_students_table .student-select').each(function () {
            var id = String($(this).closest('tr').data('session-id'));
            $(this).prop('disabled', busy || !students[id] || !students[id].eligible);
        });
    }

    $('#generation_class').on('change', function () { clearLoadedStudents(); loadSections(); });
    $('#generation_section, #generation_month, #generation_due_date').on('change', clearLoadedStudents);
    $('#add_fee_type').on('click', function () { addFeeTypeRow('', ''); });
    $('#fee_type_rows').on('change', '.fee-type-select', function () { updateFeeAmountState($(this).closest('.fee-type-row')); });
    $('#fee_type_rows').on('click', '.remove-fee-type', function () {
        if ($('#fee_type_rows .fee-type-row').length > 1) $(this).closest('.fee-type-row').remove();
    });
    $('#load_students').on('click', function () { loadStudents(false); });
    $('#generation_students_table').on('change', '.student-select', function () {
        var id = String($(this).closest('tr').data('session-id'));
        if (students[id]) students[id].selected = this.checked;
        updateSelectedCount();
    });
    $('#select_all_students').on('change', function () {
        var checked = this.checked;
        $.each(students, function (_, student) { student.selected = student.eligible ? checked : false; });
        renderStudents();
    });
    $('#generation_students_table').on('click', '.edit-student', function () { openStudentEdit($(this).closest('tr').data('session-id')); });
    $('#student_search').on('input', renderStudents);
    $('#save_student_edit').on('click', saveStudentEdit);
    $('#update_fees').on('click', function () { submitOperation('update'); });
    $('#generate_fees').on('click', function () { submitOperation('generate'); });

    var tuition = null;
    $.each(feeTypes, function (_, fee) { if (String(fee.code).toLowerCase() === 'tuition' && !tuition) tuition = fee; });
    addFeeTypeRow(tuition ? tuition.id : '', '');
})(jQuery);
</script>
