<?php
$can_add = $this->rbac->hasPrivilege('fees_group', 'can_add');
$can_edit = $this->rbac->hasPrivilege('fees_group', 'can_edit');
$show_form = $fee_group_edit_mode ? ($can_add || $can_edit) : $can_add;
$form_action = $fee_group_edit_mode ? site_url('admin/feegroup/edit/' . (int) $id) : site_url('admin/feegroup');
$form_title = $fee_group_edit_mode ? $this->lang->line('edit_fees_group') : $this->lang->line('add_fees_group');
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-object-group"></i> Fee Groups</h1>
        <div class="fee-page-intro">Organize related fee types into clear groups used by the existing fee-generation process.</div>
    </section>
    <section class="content">
        <div class="row">
            <?php if ($show_form) { ?>
                <div class="col-md-4">
                    <div class="box box-primary fee-setup-card">
                        <div class="box-header with-border"><h3 class="box-title"><i class="fa <?php echo $fee_group_edit_mode ? 'fa-pencil' : 'fa-plus'; ?>"></i> <?php echo $form_title; ?></h3></div>
                        <form action="<?php echo $form_action; ?>" method="post" accept-charset="utf-8">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
                                <?php if (isset($error_message)) { ?><div class="alert alert-danger"><?php echo html_escape($error_message); ?></div><?php } ?>
                                <?php echo $this->customlib->getCSRF(); ?>
                                <?php if ($fee_group_edit_mode) { ?><input name="id" type="hidden" value="<?php echo (int) $id; ?>"><?php } ?>
                                <div class="form-group">
                                    <label for="name"><?php echo $this->lang->line('name'); ?> <small class="req">*</small></label>
                                    <input autofocus id="name" name="name" type="text" class="form-control" value="<?php echo html_escape(set_value('name', $fee_group_edit_mode ? $feegroup['name'] : '')); ?>" placeholder="e.g. Monthly Fees">
                                    <span class="text-danger"><?php echo form_error('name'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="description"><?php echo $this->lang->line('description'); ?></label>
                                    <textarea class="form-control" id="description" name="description" rows="4" placeholder="Short description of this fee group"><?php echo html_escape(set_value('description', $fee_group_edit_mode ? $feegroup['description'] : '')); ?></textarea>
                                    <span class="text-danger"><?php echo form_error('description'); ?></span>
                                </div>
                            </div>
                            <div class="box-footer clearfix">
                                <?php if ($fee_group_edit_mode) { ?><a href="<?php echo site_url('admin/feegroup'); ?>" class="btn btn-default"><i class="fa fa-times"></i> Cancel</a><?php } ?>
                                <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-save"></i> <?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } ?>
            <div class="col-md-<?php echo $show_form ? '8' : '12'; ?>">
                <div class="box box-primary fee-list-card">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> <?php echo $this->lang->line('fees_group_list'); ?></h3>
                        <span class="label label-primary pull-right"><?php echo count($feegroupList); ?> groups</span>
                    </div>
                    <div class="box-body table-responsive">
                        <div class="download_label"><?php echo $this->lang->line('fees_group_list'); ?></div>
                        <table class="table table-striped table-hover example">
                            <thead><tr><th><?php echo $this->lang->line('name'); ?></th><th><?php echo $this->lang->line('description'); ?></th><th class="text-right"><?php echo $this->lang->line('action'); ?></th></tr></thead>
                            <tbody>
                            <?php foreach ($feegroupList as $group) { ?>
                                <tr>
                                    <td><strong><?php echo html_escape($group['name']); ?></strong></td>
                                    <td class="text-muted"><?php echo $group['description'] !== '' ? html_escape($group['description']) : '<span class="text-muted">No description</span>'; ?></td>
                                    <td class="text-right"><span class="fee-action-buttons">
                                        <?php if ($can_edit) { ?><a href="<?php echo site_url('admin/feegroup/edit/' . (int) $group['id']); ?>" class="btn btn-default btn-xs" title="<?php echo $this->lang->line('edit'); ?>"><i class="fa fa-pencil"></i></a><?php } ?>
                                        <?php if ($this->rbac->hasPrivilege('fees_group', 'can_delete')) { ?><a href="<?php echo site_url('admin/feegroup/delete/' . (int) $group['id']); ?>" class="btn btn-danger btn-xs" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm'); ?>');"><i class="fa fa-trash"></i></a><?php } ?>
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
