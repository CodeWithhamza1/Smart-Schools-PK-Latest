<style>
    .voucher-settings-grid { display: flex; gap: 20px; align-items: flex-start; }
    .voucher-settings-form { flex: 1 1 52%; min-width: 0; }
    .voucher-preview-panel { flex: 1 1 48%; min-width: 0; position: sticky; top: 10px; }
    .voucher-help { color: #657786; font-size: 12px; margin-top: 5px; }
    .copy-label-row { margin-bottom: 12px; }
    .copy-label-row.is-inactive { opacity: .5; }
    .copy-label-row.is-inactive input { background: #f5f5f5; }
    .voucher-preview-sheet { background: #eef2f5; border: 1px solid #d9e1e7; padding: 14px; overflow-x: auto; }
    .voucher-preview-row { display: grid; gap: 18px 20px; min-width: 430px; }
    .voucher-preview-row.preview-layout-1, .voucher-preview-row.preview-layout-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .voucher-preview-row.preview-layout-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .voucher-preview-copy { min-width: 0; background: #fff; border: 1px solid #98a6b3; padding: 8px; color: #263238; position: relative; }
    .voucher-preview-student { color: #687785; font-size: 8px; margin-top: 4px; text-align: center; }
    .voucher-preview-copy.has-left-cut:before { border-left: 1px dashed #7d8993; color: #44515b; content: "\2702"; font-size: 11px; height: calc(100% + 8px); left: -13px; padding-left: 1px; position: absolute; top: -4px; }
    .voucher-preview-copy.has-top-cut { border-top-style: dashed; }
    .voucher-preview-school { font-size: 12px; font-weight: 700; text-align: center; overflow-wrap: anywhere; }
    .voucher-preview-address, .voucher-preview-phone { font-size: 9px; text-align: center; overflow-wrap: anywhere; }
    .voucher-preview-header-note { min-height: 12px; font-size: 8px; text-align: center; margin-top: 4px; white-space: pre-line; overflow-wrap: anywhere; }
    .voucher-preview-label { background: #273444; color: #fff; text-align: center; font-size: 10px; font-weight: 700; margin: 7px -4px; padding: 3px; overflow-wrap: anywhere; }
    .voucher-preview-lines span { display: block; border-bottom: 1px solid #c7cfd6; height: 14px; }
    .voucher-preview-footer { min-height: 25px; font-size: 8px; margin-top: 7px; white-space: pre-line; overflow-wrap: anywhere; }
    @media (max-width: 1199px) {
        .voucher-settings-grid { display: block; }
        .voucher-preview-panel { position: static; margin-top: 20px; }
    }
    @media (max-width: 767px) {
        .voucher-preview-sheet { margin-left: -10px; margin-right: -10px; padding: 10px; }
    }
</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-file-text-o"></i> Voucher Settings</h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
            <?php echo $this->session->flashdata('msg'); ?>
            <?php if (!empty($save_error)) { ?>
                <div class="alert alert-danger"><?php echo html_escape($save_error); ?></div>
            <?php } ?>
            <?php if (validation_errors()) { ?>
                <div class="alert alert-danger"><?php echo validation_errors(); ?></div>
            <?php } ?>

            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Standard Fee Voucher Layout</h3>
                </div>
                <div class="box-body">
                    <div class="voucher-settings-grid">
                        <div class="voucher-settings-form">
                            <form method="post" action="<?php echo site_url('admin/voucher-settings'); ?>" id="voucher-settings-form">
                                <?php if ($this->config->item('csrf_protection')) { ?>
                                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                                <?php } ?>
                                <div class="form-group">
                                    <label for="copy_count">Number of Copies <small class="req">*</small></label>
                                    <select class="form-control" name="copy_count" id="copy_count" <?php echo !$can_edit ? 'disabled' : ''; ?>>
                                        <?php for ($copy_count = 1; $copy_count <= 3; $copy_count++) { ?>
                                            <option value="<?php echo $copy_count; ?>" <?php echo ((int) $settings['copy_count'] === $copy_count) ? 'selected' : ''; ?>><?php echo $copy_count . ' ' . ($copy_count === 1 ? 'Copy' : 'Copies'); ?></option>
                                        <?php } ?>
                                    </select>
                                    <div class="voucher-help">The selected copies are printed in the order shown below.</div>
                                </div>

                                <?php for ($copy_number = 1; $copy_number <= 3; $copy_number++) { $label_key = 'copy_' . $copy_number . '_label'; ?>
                                    <div class="form-group copy-label-row" data-copy-number="<?php echo $copy_number; ?>">
                                        <label for="<?php echo $label_key; ?>">Copy <?php echo $copy_number; ?> Name <small class="req copy-required">*</small></label>
                                        <input type="text" maxlength="50" class="form-control copy-label-input" id="<?php echo $label_key; ?>" name="<?php echo $label_key; ?>" value="<?php echo html_escape($settings[$label_key]); ?>" <?php echo !$can_edit ? 'disabled' : ''; ?>>
                                    </div>
                                <?php } ?>

                                <div class="form-group">
                                    <label for="header_note">Bank Details / Header Note</label>
                                    <textarea class="form-control" rows="5" maxlength="1000" id="header_note" name="header_note" placeholder="Bank name, account title, account number, IBAN or payment instructions" <?php echo !$can_edit ? 'disabled' : ''; ?>><?php echo html_escape($settings['header_note']); ?></textarea>
                                    <div class="voucher-help">Displayed below the school phone number on every active copy. Leave blank to hide it.</div>
                                </div>

                                <div class="form-group">
                                    <label for="footer_notes">Footer Notes</label>
                                    <textarea class="form-control" rows="6" maxlength="2000" id="footer_notes" name="footer_notes" placeholder="Enter one or more voucher instructions" <?php echo !$can_edit ? 'disabled' : ''; ?>><?php echo html_escape($settings['footer_notes']); ?></textarea>
                                    <div class="voucher-help">Line breaks are preserved. Leave blank to hide the note section.</div>
                                </div>

                                <?php if ($can_edit) { ?>
                                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Voucher Settings</button>
                                <?php } else { ?>
                                    <div class="alert alert-info">You have view-only access to these settings.</div>
                                <?php } ?>
                            </form>
                        </div>

                        <div class="voucher-preview-panel">
                            <label>Responsive Preview</label>
                            <div class="voucher-preview-sheet">
                                <div class="voucher-preview-row" id="voucher-preview-row"></div>
                            </div>
                            <div class="voucher-help">This preview shows placement and copy order. Student fee data remains unchanged.</div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </section>
</div><!-- /.content-wrapper -->

<script>
(function ($) {
    'use strict';

    var schoolName = <?php echo json_encode(isset($school_setting->name) ? $school_setting->name : 'School Name'); ?>;
    var schoolAddress = <?php echo json_encode(isset($school_setting->address) ? $school_setting->address : 'School Address'); ?>;
    var schoolPhone = <?php echo json_encode(isset($school_setting->phone) ? $school_setting->phone : 'Phone'); ?>;

    function refreshVoucherPreview() {
        var count = parseInt($('#copy_count').val(), 10) || 1;
        var headerNote = $('#header_note').val() || '';
        var footerNotes = $('#footer_notes').val() || '';
        var $preview = $('#voucher-preview-row').empty().removeClass('preview-layout-1 preview-layout-2 preview-layout-3').addClass('preview-layout-' + count);
        var studentsPerPage = count === 1 ? 3 : (count === 2 ? 2 : 1);

        $('.copy-label-row').each(function () {
            var copyNumber = parseInt($(this).attr('data-copy-number'), 10);
            var active = copyNumber <= count;
            $(this).toggleClass('is-inactive', !active);
            $(this).find('.copy-required').toggle(active);
            $(this).find('.copy-label-input').attr('required', active);
        });

        for (var studentNumber = 1; studentNumber <= studentsPerPage; studentNumber++) {
            for (var copyNumber = 1; copyNumber <= count; copyNumber++) {
                var label = $('#copy_' + copyNumber + '_label').val() || ('Copy ' + copyNumber);
                var hasLeftCut = (count === 1 && studentNumber > 1) || (count > 1 && copyNumber > 1);
                var hasTopCut = count === 2 && studentNumber > 1;
                var copyClasses = 'voucher-preview-copy' + (hasLeftCut ? ' has-left-cut' : '') + (hasTopCut ? ' has-top-cut' : '');
                var $copy = $('<div>', {'class': copyClasses});
                $('<div>', {'class': 'voucher-preview-school', text: schoolName}).appendTo($copy);
                $('<div>', {'class': 'voucher-preview-address', text: schoolAddress}).appendTo($copy);
                $('<div>', {'class': 'voucher-preview-phone', text: schoolPhone}).appendTo($copy);
                $('<div>', {'class': 'voucher-preview-header-note', text: headerNote}).appendTo($copy);
                $('<div>', {'class': 'voucher-preview-label', text: label}).appendTo($copy);
                $('<div>', {'class': 'voucher-preview-student', text: 'Student ' + studentNumber}).appendTo($copy);
                $('<div>', {'class': 'voucher-preview-lines'}).append('<span></span><span></span><span></span><span></span><span></span>').appendTo($copy);
                $('<div>', {'class': 'voucher-preview-footer', text: footerNotes}).appendTo($copy);
                $preview.append($copy);
            }
        }
    }

    $('#copy_count, .copy-label-input, #header_note, #footer_notes').on('input change', refreshVoucherPreview);
    refreshVoucherPreview();
})(jQuery);
</script>
