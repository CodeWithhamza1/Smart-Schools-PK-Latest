<?php
$can_add = $this->rbac->hasPrivilege('fees_type', 'can_add');
$can_edit = $this->rbac->hasPrivilege('fees_type', 'can_edit');
$show_form = $fee_type_edit_mode ? ($can_add || $can_edit) : $can_add;
$form_action = $fee_type_edit_mode ? site_url('admin/feetype/edit/' . (int) $id) : site_url('admin/feetype');
$form_title = $fee_type_edit_mode ? $this->lang->line('edit_fees_type') : $this->lang->line('add_fees_type');
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-tags"></i> Fee Types</h1>
        <div class="fee-page-intro">Maintain the fee names and unique codes used when generating student fees.</div>
    </section>
    <section class="content">
        <div class="row">
            <?php if ($show_form) { ?>
                <div class="col-md-4">
                    <div class="box box-primary fee-setup-card">
                        <div class="box-header with-border"><h3 class="box-title"><i class="fa <?php echo $fee_type_edit_mode ? 'fa-pencil' : 'fa-plus'; ?>"></i> <?php echo $form_title; ?></h3></div>
                        <form action="<?php echo $form_action; ?>" method="post" accept-charset="utf-8">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
                                <?php if (isset($error_message)) { ?><div class="alert alert-danger"><?php echo html_escape($error_message); ?></div><?php } ?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                <?php if ($fee_type_edit_mode) { ?><input name="id" type="hidden" value="<?php echo (int) $id; ?>"><?php } ?>
                                <div class="form-group">
                                    <label for="name"><?php echo $this->lang->line('name'); ?> <small class="req">*</small></label>
                                    <input autofocus id="name" name="name" type="text" class="form-control" value="<?php echo html_escape(set_value('name', $fee_type_edit_mode ? $feetype['type'] : '')); ?>" placeholder="e.g. Tuition Fee">
                                    <span class="text-danger"><?php echo form_error('name'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="code"><?php echo $this->lang->line('fees_code'); ?> <small class="req">*</small></label>
                                    <input id="code" name="code" type="text" class="form-control" value="<?php echo html_escape(set_value('code', $fee_type_edit_mode ? $feetype['code'] : '')); ?>" placeholder="e.g. TUITION">
                                    <span class="text-danger"><?php echo form_error('code'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="description"><?php echo $this->lang->line('description'); ?></label>
                                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Short description of this fee type"><?php echo html_escape(set_value('description', $fee_type_edit_mode ? $feetype['description'] : '')); ?></textarea>
                                    <span class="text-danger"><?php echo form_error('description'); ?></span>
                                </div>
                            </div>
                            <div class="box-footer clearfix">
                                <?php if ($fee_type_edit_mode) { ?><a href="<?php echo site_url('admin/feetype'); ?>" class="btn btn-default"><i class="fa fa-times"></i> Cancel</a><?php } ?>
                                <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> <?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } ?>
            <div class="col-md-<?php echo $show_form ? '8' : '12'; ?>">
                <div class="box box-primary fee-list-card">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> <?php echo $this->lang->line('fees_type_list'); ?></h3>
                        <span class="label label-primary pull-right"><?php echo count($feetypeList); ?> types</span>
                    </div>
                    <div class="box-body table-responsive">
                        <div class="download_label"><?php echo $this->lang->line('fees_type_list'); ?></div>
                        <table class="table table-striped table-hover example">
                            <thead><tr><th><?php echo $this->lang->line('name'); ?></th><th><?php echo $this->lang->line('fees_code'); ?></th><th><?php echo $this->lang->line('description'); ?></th><th class="text-right"><?php echo $this->lang->line('action'); ?></th></tr></thead>
                            <tbody>
                            <?php foreach ($feetypeList as $type) { ?>
                                <tr>
                                    <td><strong><?php echo html_escape($type['type']); ?></strong></td>
                                    <td><span class="label label-default"><?php echo html_escape($type['code']); ?></span></td>
                                    <td class="text-muted"><?php echo $type['description'] !== '' ? html_escape($type['description']) : '<span class="text-muted">No description</span>'; ?></td>
                                    <td class="text-right"><span class="fee-action-buttons">
                                        <?php if ($can_edit) { ?><a href="<?php echo site_url('admin/feetype/edit/' . (int) $type['id']); ?>" class="btn btn-default btn-xs" title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i></a><?php } ?>
                                        <?php if ($this->rbac->hasPrivilege('fees_type', 'can_delete')) { ?><a href="<?php echo site_url('admin/feetype/delete/' . (int) $type['id']); ?>" class="btn btn-danger btn-xs" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');"><i class="fa fa-trash"></i></a><?php } ?>
                                    </span></td>
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
