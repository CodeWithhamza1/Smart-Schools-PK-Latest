<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-plug"></i> Module Installer</h1>
    </section>
    <section class="content">
        <?php if ($this->session->flashdata('msg')) { ?>
            <?php echo $this->session->flashdata('msg'); ?>
        <?php } ?>
        <div class="row">
            <div class="col-md-8">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Installed Modules</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Key</th>
                                    <th>Version</th>
                                    <th>Status</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($modules)) { ?>
                                    <tr><td colspan="5" class="text-muted">No external modules installed.</td></tr>
                                <?php } ?>
                                <?php foreach ($modules as $module) { ?>
                                    <tr>
                                        <td><?php echo html_escape($module['name']); ?></td>
                                        <td><code><?php echo html_escape($module['module_key']); ?></code></td>
                                        <td><?php echo html_escape($module['version']); ?></td>
                                        <td>
                                            <?php if ($module['status'] === 'enabled') { ?>
                                                <span class="label label-success">Enabled</span>
                                            <?php } elseif ($module['status'] === 'disabled') { ?>
                                                <span class="label label-warning">Disabled</span>
                                            <?php } else { ?>
                                                <span class="label label-default">Uninstalled</span>
                                            <?php } ?>
                                        </td>
                                        <td class="text-right">
                                            <a class="btn btn-default btn-xs" href="<?php echo site_url('admin/moduleinstaller/logs/' . rawurlencode($module['module_key'])); ?>"><i class="fa fa-list"></i> Logs</a>
                                            <?php if ($module['status'] !== 'uninstalled') { ?>
                                                <a class="btn btn-success btn-xs" href="<?php echo site_url('admin/moduleinstaller/export/' . rawurlencode($module['module_key'])); ?>"><i class="fa fa-download"></i> Export</a>
                                                <?php if ($module['status'] === 'enabled') { ?>
                                                    <a class="btn btn-warning btn-xs" href="<?php echo site_url('admin/moduleinstaller/disable/' . rawurlencode($module['module_key'])); ?>" onclick="return confirm('Disable this module menu?');"><i class="fa fa-pause"></i> Disable</a>
                                                <?php } else { ?>
                                                    <a class="btn btn-info btn-xs" href="<?php echo site_url('admin/moduleinstaller/enable/' . rawurlencode($module['module_key'])); ?>"><i class="fa fa-play"></i> Enable</a>
                                                <?php } ?>
                                                <form action="<?php echo site_url('admin/moduleinstaller/uninstall/' . rawurlencode($module['module_key'])); ?>" method="post" class="displayinline">
                                                    <?php echo $this->customlib->getCSRF(); ?>
                                                    <button type="submit" class="btn btn-danger btn-xs" onclick="return confirm('Uninstall this module? A backup will be created first.');"><i class="fa fa-trash"></i> Uninstall</button>
                                                </form>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Recent Installer Logs</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Module</th>
                                    <th>Level</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($logs)) { ?>
                                    <tr><td colspan="4" class="text-muted">No installer logs yet.</td></tr>
                                <?php } ?>
                                <?php foreach ($logs as $log) { ?>
                                    <tr>
                                        <td><?php echo html_escape($log['created_at']); ?></td>
                                        <td><code><?php echo html_escape($log['module_key']); ?></code></td>
                                        <td><?php echo html_escape($log['level']); ?></td>
                                        <td><?php echo html_escape($log['message']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Upload Module ZIP</h3>
                    </div>
                    <form action="<?php echo site_url('admin/moduleinstaller/upload'); ?>" method="post" enctype="multipart/form-data">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <div class="box-body">
                            <input class="filestyle form-control" type="file" name="module_package" accept=".zip">
                            <p class="help-block">Package must contain module.json at the ZIP root.</p>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-upload"></i> Preview Package</button>
                        </div>
                    </form>
                </div>
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Safety</h3>
                    </div>
                    <div class="box-body">
                        <p>Install creates a full database backup and backs up overwritten files before applying package changes.</p>
                        <p>Only declared files under approved application folders can be copied.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
