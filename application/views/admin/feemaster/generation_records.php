<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-history"></i> Fee Generation Records</h1>
    </section>
    <section class="content">
        <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
                <li><a href="<?php echo site_url('admin/feemaster/feeStructure'); ?>">Generate Fee</a></li>
                <li class="active"><a href="#records">Generation Records</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="records">
                    <div class="alert alert-info"><i class="fa fa-lock"></i> This is a read-only audit copy. Changing or deleting records here is not allowed and does not affect vouchers, ledgers, collections, or reports.</div>
                    <?php if (!$records_available) { ?>
                        <div class="alert alert-warning">The audit table is not installed yet. Apply <code>application/migrations/002_optimize_fee_generation.sql</code> to enable immutable generation records.</div>
                    <?php } else { ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered example">
                                <thead><tr><th>Date/Time</th><th>Action</th><th>Period</th><th>Class / Section IDs</th><th>Staff ID</th><th>Status</th><th>Result</th><th>Record ID</th></tr></thead>
                                <tbody>
                                <?php foreach ($records as $record) { $counts = $record['counts']; ?>
                                    <tr>
                                        <td><?php echo html_escape($record['created_at']); ?></td>
                                        <td><?php echo html_escape(ucwords(str_replace('_', ' ', $record['action']))); ?></td>
                                        <td><?php echo html_escape(date('F Y', mktime(0, 0, 0, (int) $record['fee_month'], 1, (int) $record['fee_year']))); ?></td>
                                        <td><?php echo (int) $record['class_id']; ?> / <?php echo (int) $record['section_id']; ?></td>
                                        <td><?php echo (int) $record['staff_id']; ?></td>
                                        <td><span class="label label-<?php echo $record['status'] === 'completed' ? 'success' : ($record['status'] === 'failed' ? 'danger' : 'warning'); ?>"><?php echo html_escape($record['status']); ?></span></td>
                                        <td>Students: <?php echo (int) $record['student_count']; ?>, Fee types: <?php echo (int) $record['fee_type_count']; ?>, Created: <?php echo isset($counts['created']) ? (int) $counts['created'] : 0; ?>, Existing: <?php echo isset($counts['existing']) ? (int) $counts['existing'] : 0; ?>, Due dates: <?php echo isset($counts['updated_fee_rows']) ? (int) $counts['updated_fee_rows'] : 0; ?></td>
                                        <td><code><?php echo (int) $record['id']; ?></code></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>
