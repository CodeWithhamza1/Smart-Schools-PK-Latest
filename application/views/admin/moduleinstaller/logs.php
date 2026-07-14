<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-list"></i> Module Installer Logs</h1>
    </section>
    <section class="content">
        <?php if ($this->session->flashdata('msg')) { ?>
            <?php echo $this->session->flashdata('msg'); ?>
        <?php } ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?php echo $module ? html_escape($module['name']) : 'Module'; ?>
                    <?php if ($module) { ?><small><?php echo html_escape($module['module_key']); ?></small><?php } ?>
                </h3>
                <div class="box-tools pull-right">
                    <a class="btn btn-default btn-sm" href="<?php echo site_url('admin/moduleinstaller'); ?>"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
            </div>
            <div class="box-body table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Level</th>
                            <th>Message</th>
                            <th>Context</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)) { ?>
                            <tr><td colspan="4" class="text-muted">No logs found.</td></tr>
                        <?php } ?>
                        <?php foreach ($logs as $log) { ?>
                            <tr>
                                <td><?php echo html_escape($log['created_at']); ?></td>
                                <td><?php echo html_escape($log['level']); ?></td>
                                <td><?php echo html_escape($log['message']); ?></td>
                                <td><code><?php echo html_escape($log['context_json']); ?></code></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
