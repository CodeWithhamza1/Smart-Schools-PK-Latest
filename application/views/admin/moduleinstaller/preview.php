<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-plug"></i> Module Package Preview</h1>
    </section>
    <section class="content">
        <?php $module = $preview['manifest']['module']; ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title"><?php echo html_escape($module['name']); ?> <small><?php echo html_escape($module['version']); ?></small></h3>
                <div class="box-tools pull-right">
                    <span class="label label-info"><?php echo html_escape(ucfirst($preview['action'])); ?></span>
                </div>
            </div>
            <div class="box-body">
                <dl class="dl-horizontal">
                    <dt>Module Key</dt>
                    <dd><code><?php echo html_escape($preview['module_key']); ?></code></dd>
                    <dt>Package Hash</dt>
                    <dd><code><?php echo html_escape($preview['package_hash']); ?></code></dd>
                    <dt>Description</dt>
                    <dd><?php echo html_escape(isset($module['description']) ? $module['description'] : ''); ?></dd>
                    <dt>Author</dt>
                    <dd><?php echo html_escape(isset($module['author']) ? $module['author'] : ''); ?></dd>
                </dl>
            </div>
            <div class="box-footer">
                <form action="<?php echo site_url('admin/moduleinstaller/install/' . $token); ?>" method="post">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <a href="<?php echo site_url('admin/moduleinstaller'); ?>" class="btn btn-default"><i class="fa fa-arrow-left"></i> Cancel</a>
                    <button type="submit" class="btn btn-primary pull-right" onclick="return confirm('Install this module package now? Backups will be created first.');"><i class="fa fa-check"></i> Install Module</button>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Files</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Target</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($preview['files'])) { ?>
                                    <tr><td colspan="2" class="text-muted">No files declared.</td></tr>
                                <?php } ?>
                                <?php foreach ($preview['files'] as $file) { ?>
                                    <tr>
                                        <td><code><?php echo html_escape($file['target']); ?></code></td>
                                        <td><?php echo html_escape($file['status']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">SQL Migrations</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>File</th>
                                    <th>Phase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($preview['sql_files'])) { ?>
                                    <tr><td colspan="2" class="text-muted">No SQL files for this action.</td></tr>
                                <?php } ?>
                                <?php foreach ($preview['sql_files'] as $sql) { ?>
                                    <tr>
                                        <td><code><?php echo html_escape($sql['path']); ?></code></td>
                                        <td><?php echo html_escape($sql['phase']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Menus</h3>
                    </div>
                    <div class="box-body table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th>URL</th>
                                    <th>Permission</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($preview['menus'])) { ?>
                                    <tr><td colspan="3" class="text-muted">No menus declared.</td></tr>
                                <?php } ?>
                                <?php foreach ($preview['menus'] as $menu) { ?>
                                    <tr>
                                        <td><?php echo html_escape($menu['label']); ?></td>
                                        <td><code><?php echo html_escape($menu['url']); ?></code></td>
                                        <td><code><?php echo html_escape(isset($menu['permission']) ? $menu['permission'] : ''); ?></code></td>
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
