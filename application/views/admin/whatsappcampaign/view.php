<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-whatsapp"></i> <?php echo html_escape($campaign['title']); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if ($this->session->flashdata('msg')) { ?>
                    <?php echo $this->session->flashdata('msg'); ?>
                <?php } ?>
            </div>

            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Campaign Summary</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/whatsappcampaign/queuenow/' . $campaign['id']); ?>" class="btn btn-success btn-sm">Queue Now</a>
                            <a href="<?php echo site_url('admin/whatsappcampaign/releasepending/' . $campaign['id']); ?>" class="btn btn-warning btn-sm">Release Pending</a>
                            <a href="<?php echo site_url('admin/whatsappcampaign/retryfailed/' . $campaign['id']); ?>" class="btn btn-danger btn-sm">Retry Failed</a>
                            <a href="<?php echo site_url('admin/whatsappcampaign/downloadcsv/' . $campaign['id']); ?>" class="btn btn-primary btn-sm">Download CSV</a>
                            <a href="<?php echo site_url('admin/whatsappcampaign'); ?>" class="btn btn-default btn-sm">Back</a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <?php
                            $s = strtolower($campaign['status']);
                            $status_badge = array('sent' => 'success', 'completed' => 'success', 'failed' => 'danger', 'pending' => 'warning', 'processing' => 'info', 'scheduled' => 'default');
                            $status_cls = isset($status_badge[$s]) ? $status_badge[$s] : 'info';
                            ?>
                            <div class="col-md-3"><strong>Status:</strong> <span data-summary="status"><span class="label label-<?php echo $status_cls; ?>"><?php echo ucfirst($campaign['status']); ?></span></span></div>
                            <div class="col-md-3"><strong>Sent:</strong> <span data-summary="sent"><?php echo (int) $campaign['sent_count']; ?></span></div>
                            <div class="col-md-3"><strong>Failed:</strong> <span data-summary="failed"><?php echo (int) $campaign['failed_count']; ?></span></div>
                            <div class="col-md-3"><strong>Pending:</strong> <span data-summary="pending"><?php echo (int) $campaign['pending_count']; ?></span></div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-4"><strong>Recipient Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $campaign['target_type'])); ?></div>
                            <div class="col-md-4"><strong>Schedule Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $campaign['schedule_type'])); ?></div>
                            <div class="col-md-4"><strong>Scheduled At:</strong> <?php echo html_escape($campaign['scheduled_at']); ?></div>
                        </div>
                        <hr>
                        <p><strong>Message Template</strong></p>
                        <div class="well well-sm"><?php echo nl2br(html_escape($campaign['message_template'])); ?></div>
                        <p class="text-muted" style="margin-top:10px;">Pending queue items are delivered by the Smart Schools WhatsApp extension. The extension can pull the CSV feed automatically or use the downloaded CSV from this campaign.</p>
                    </div>
                </div>
            </div>

            <script type="text/javascript">
            (function($) {
                "use strict";
                var campaignId = <?php echo (int) $campaign['id']; ?>;

                function statusBadgeClass(status) {
                    var map = {Sent: 'success', Failed: 'danger', Pending: 'warning', Processing: 'info', Scheduled: 'default', Completed: 'success'};
                    return map[status] || 'info';
                }

                function refreshCampaignStatus() {
                    $.ajax({
                        url: '<?php echo site_url('admin/whatsappcampaign/livestatus'); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {ids: [campaignId]},
                        success: function(response) {
                            if (!response || !response[campaignId]) return;
                            var s = response[campaignId];
                            $('[data-summary="status"]').html('<span class="label label-' + statusBadgeClass(s.status) + '">' + s.status + '</span>');
                            $('[data-summary="sent"]').text(s.sent_count);
                            $('[data-summary="failed"]').text(s.failed_count);
                            $('[data-summary="pending"]').text(s.pending_count);

                            var total = s.total_count || 0;
                            if (total > 0) {
                                var sentPct    = Math.round(s.sent_count    / total * 100);
                                var failedPct  = Math.round(s.failed_count  / total * 100);
                                var pendingPct = Math.max(0, 100 - sentPct - failedPct);
                                $('[data-bar="sent"]').css('width', sentPct + '%').attr('title', s.sent_count + ' Sent').text(sentPct >= 10 ? s.sent_count + ' Sent' : '');
                                $('[data-bar="failed"]').css('width', failedPct + '%').attr('title', s.failed_count + ' Failed').text(failedPct >= 10 ? s.failed_count + ' Failed' : '');
                                $('[data-bar="pending"]').css('width', pendingPct + '%').attr('title', s.pending_count + ' Pending').text(pendingPct >= 10 ? s.pending_count + ' Pending' : '');
                            }
                        }
                    });
                }

                setInterval(refreshCampaignStatus, 5000);
            })(jQuery);
            </script>

            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Message Log</h3>
                        <div class="box-tools pull-right">
                            <?php
                            $failed_count = (int) $campaign['failed_count'];
                            $pending_count = (int) $campaign['pending_count'];
                            ?>
                            <?php if ($failed_count > 0) { ?>
                                <a href="<?php echo site_url('admin/whatsappcampaign/retryfailed/' . $campaign['id']); ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Move <?php echo $failed_count; ?> failed message(s) back to pending so the extension retries them?');">
                                    <i class="fa fa-refresh"></i> Retry <?php echo $failed_count; ?> Failed
                                </a>
                            <?php } ?>
                            <?php if ($pending_count > 0) { ?>
                                <a href="<?php echo site_url('admin/whatsappcampaign/releasepending/' . $campaign['id']); ?>"
                                   class="btn btn-warning btn-sm"
                                   onclick="return confirm('Release <?php echo $pending_count; ?> reserved item(s) so the extension can pick them up again?');">
                                    <i class="fa fa-unlock"></i> Release Pending
                                </a>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="box-body">

                        <?php
                        $total   = (int) $campaign['total_count'];
                        $sent    = (int) $campaign['sent_count'];
                        $failed  = (int) $campaign['failed_count'];
                        $pending = (int) $campaign['pending_count'];
                        if ($total > 0) {
                            $sent_pct    = round($sent    / $total * 100);
                            $failed_pct  = round($failed  / $total * 100);
                            $pending_pct = max(0, 100 - $sent_pct - $failed_pct);
                            ?>
                            <div class="progress" style="height:20px;margin-bottom:10px;">
                                <div class="progress-bar progress-bar-success" data-bar="sent"
                                     style="width:<?php echo $sent_pct; ?>%;" title="<?php echo $sent; ?> Sent">
                                    <?php if ($sent_pct >= 10) { echo $sent . ' Sent'; } ?>
                                </div>
                                <div class="progress-bar progress-bar-danger" data-bar="failed"
                                     style="width:<?php echo $failed_pct; ?>%;" title="<?php echo $failed; ?> Failed">
                                    <?php if ($failed_pct >= 10) { echo $failed . ' Failed'; } ?>
                                </div>
                                <div class="progress-bar progress-bar-warning" data-bar="pending"
                                     style="width:<?php echo $pending_pct; ?>%;" title="<?php echo $pending; ?> Pending">
                                    <?php if ($pending_pct >= 10) { echo $pending . ' Pending'; } ?>
                                </div>
                            </div>
                        <?php } ?>

                        <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover" id="msg-log-table">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Attempts</th>
                                    <th>Sent At</th>
                                    <th>Error / Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($items)) { ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No messages queued yet. Click <strong>Queue Now</strong> to populate.</td>
                                    </tr>
                                <?php } ?>
                                <?php foreach ($items as $item) {
                                    $status = strtolower($item['status']);
                                    $row_class = ($status === 'failed') ? 'danger' : (($status === 'sent') ? 'success' : '');
                                    $badge_class = array('sent' => 'success', 'failed' => 'danger', 'pending' => 'warning', 'processing' => 'info');
                                    $badge = isset($badge_class[$status]) ? $badge_class[$status] : 'default';
                                    ?>
                                    <tr class="<?php echo $row_class; ?>">
                                        <td>
                                            <strong><?php echo html_escape($item['recipient_name']); ?></strong><br>
                                            <small class="text-muted"><?php echo html_escape($item['class_name']); ?><?php if (!empty($item['section_name'])) { echo ' / ' . html_escape($item['section_name']); } ?></small>
                                        </td>
                                        <td><?php echo html_escape($item['phone_number']); ?></td>
                                        <td><span class="label label-<?php echo $badge; ?>"><?php echo ucfirst(html_escape($item['status'])); ?></span></td>
                                        <td class="text-center"><?php echo (int) $item['attempt_count']; ?></td>
                                        <td><?php echo !empty($item['sent_at']) ? html_escape($item['sent_at']) : '<span class="text-muted">—</span>'; ?></td>
                                        <td>
                                            <?php if (!empty($item['error_message'])) { ?>
                                                <span class="text-danger"><i class="fa fa-exclamation-circle"></i> <?php echo html_escape($item['error_message']); ?></span>
                                            <?php } else { ?>
                                                <span class="text-muted">—</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                        </div>
                        <?php if (count($items) >= 100) { ?>
                            <p class="text-muted text-center"><small>Showing first 100 entries. Download the CSV for the full list.</small></p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
