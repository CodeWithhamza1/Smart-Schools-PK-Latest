<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();

// Helper: net balance for an account object
function bs_net($account) {
    return floatval($account->opening_balance) + floatval($account->total_debit) - floatval($account->total_credit);
}

// Group accounts by account_group
function bs_group($accounts) {
    $groups = [];
    foreach ($accounts as $acc) {
        $g = $acc->account_group ?: 'General';
        $groups[$g][] = $acc;
    }
    ksort($groups);
    return $groups;
}

$as_at_labels = [
    'this_month'   => 'This Month',
    'last_month'   => 'Last Month',
    'this_quarter' => 'This Quarter',
    'this_year'    => 'This Year',
    'last_year'    => 'Last Year',
    'custom'       => 'Custom',
];

// Totals
$total_assets      = array_sum(array_map('bs_net', $assets));
$total_liabilities = array_sum(array_map('bs_net', $liabilities));
$total_equity      = array_sum(array_map('bs_net', $equity));
$total_liab_equity = $total_liabilities + $total_equity;
?>
<style>
.bs-wrap { background:#f4f6f9; min-height:100vh; }
.bs-criteria-panel {
    background:#fff;
    border-bottom:1px solid #e0e4ea;
    padding:0;
}
.bs-criteria-toggle {
    display:flex; align-items:center; justify-content:space-between;
    padding:14px 22px;
    cursor:pointer;
    user-select:none;
}
.bs-criteria-toggle h4 { margin:0; font-size:15px; font-weight:600; color:#333; }
.bs-criteria-body { padding:16px 22px 20px; border-top:1px solid #e8ecf0; }
.bs-settings-btn { color:#666; font-size:20px; cursor:pointer; }

.bs-run-area { background:#fff; border-bottom:2px solid #e0e4ea; padding:0 22px 18px; }

.bs-report-area { padding:28px 32px; }
.bs-report-paper {
    background:#fff;
    border:1px solid #e0e4ea;
    border-radius:4px;
    padding:32px 36px;
    max-width:860px;
    margin:0 auto;
}

/* Report header */
.bs-report-title { text-align:center; margin-bottom:24px; }
.bs-report-title h2 { font-size:22px; font-weight:700; margin:0 0 4px; color:#1a1a1a; }
.bs-report-title p  { margin:2px 0; color:#555; font-size:13px; }
.bs-toolbar { text-align:right; margin-bottom:12px; }
.bs-toolbar a { margin-left:8px; color:#555; font-size:18px; text-decoration:none; }
.bs-toolbar a:hover { color:#333; }

/* Section */
.bs-section-title {
    font-size:15px; font-weight:700; color:#1a1a1a;
    border-bottom:2px solid #333;
    padding-bottom:5px; margin:24px 0 10px;
}
.bs-group-title {
    display:flex; align-items:center; justify-content:space-between;
    font-size:14px; font-weight:700; color:#1a1a1a;
    background:#f7f9fc;
    padding:7px 10px;
    border-top:1px solid #dce2ea;
    border-bottom:1px solid #dce2ea;
    cursor:pointer;
}
.bs-group-title .bs-toggle-icon { font-size:12px; color:#888; }
.bs-group-amount { font-size:13px; font-weight:700; }

.bs-account-row {
    display:flex; justify-content:space-between;
    padding:5px 10px 5px 26px;
    font-size:13px; color:#333;
    border-bottom:1px solid #f0f0f0;
}
.bs-account-row:last-child { border-bottom:none; }
.bs-account-name { flex:1; text-decoration:underline; color:#1a3a5c; font-weight:600; }
.bs-account-amt  { min-width:110px; text-align:right; }
.bs-account-amt2 { min-width:110px; text-align:right; }

.bs-col-headers {
    display:flex; justify-content:flex-end;
    font-size:12px; font-weight:700; color:#555;
    padding:4px 10px;
    border-bottom:1px solid #ccc;
    margin-top:6px;
}
.bs-col-hdr { min-width:110px; text-align:right; }

.bs-total-row {
    display:flex; justify-content:space-between;
    padding:8px 10px;
    font-size:13px; font-weight:700;
    border-top:2px solid #333;
    margin-top:4px;
}
.bs-total-label { flex:1; }
.bs-total-amt { min-width:110px; text-align:right; font-weight:700; }

.bs-grand-total {
    display:flex; justify-content:space-between;
    padding:8px 10px;
    font-size:13px; font-weight:700;
    border-top:2px solid #333;
    border-bottom:2px solid #333;
    margin-top:8px;
}

/* Form controls */
.form-inline-row { display:flex; align-items:center; gap:24px; flex-wrap:wrap; margin-bottom:14px; }
.form-inline-row .form-group { margin:0; display:flex; align-items:center; gap:8px; }
.form-inline-row label { margin:0; font-weight:600; white-space:nowrap; }
.form-inline-row select, .form-inline-row input[type=date] {
    border:1px solid #ccc; border-radius:4px; padding:5px 10px; font-size:13px; min-width:160px;
}
.btn-run { background:#2e7d4f; color:#fff; border:none; border-radius:20px; padding:9px 26px; font-size:14px; font-weight:600; cursor:pointer; }
.btn-run:hover { background:#235f3b; }

@media print {
    .bs-criteria-panel, .bs-run-area, .bs-toolbar, .no-print { display:none !important; }
    .bs-report-area { padding:0; }
    .bs-report-paper { border:none; padding:10px; box-shadow:none; }
    .content-header { display:none; }
    .bs-wrap { background:#fff; }
}
</style>

<div class="content-wrapper bs-wrap">
    <section class="content-header no-print">
        <h1><i class="fa fa-balance-scale"></i> Balance Sheet</h1>
    </section>

    <section class="content">
        <!-- Criteria Panel -->
        <div class="bs-criteria-panel no-print">
            <div class="bs-criteria-toggle" onclick="toggleCriteria()">
                <h4><i class="fa fa-chevron-down" id="criteria-icon"></i>&nbsp; Report Criteria</h4>
                <span class="bs-settings-btn"><i class="fa fa-cog"></i> Settings</span>
            </div>
            <div class="bs-criteria-body" id="criteria-body">
                <form method="post" action="<?php echo site_url('accounts/balance_sheet'); ?>" id="bs-form">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <input type="hidden" name="run_report" value="1">
                    <div class="form-inline-row">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="detailed" value="1" <?php echo $detailed ? 'checked' : ''; ?>>
                                &nbsp;Detailed Balance Sheet
                            </label>
                        </div>
                        <div class="form-group">
                            <label>As At</label>
                            <select name="as_at" id="as_at_sel" onchange="toggleCustomDate(this.value)">
                                <?php foreach ($as_at_labels as $val => $label): ?>
                                <option value="<?php echo $val; ?>" <?php echo ($as_at == $val) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" id="custom-date-wrap" style="<?php echo ($as_at == 'custom') ? '' : 'display:none'; ?>">
                            <label>Date</label>
                            <input type="date" name="custom_date" value="<?php echo htmlspecialchars($end_date); ?>">
                        </div>
                        <?php if ($as_at != 'custom'): ?>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="text" value="<?php echo date('d/m/Y', strtotime($end_date)); ?>" readonly style="border:1px solid #ccc;border-radius:4px;padding:5px 10px;font-size:13px;background:#f5f5f5;min-width:130px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    <button type="submit" class="btn-run">Run Report</button>
                </form>
            </div>
        </div>

        <!-- Report Area -->
        <?php if ($run): ?>
        <div class="bs-report-area">
            <div class="bs-toolbar no-print">
                <a href="javascript:window.print()" title="Print"><i class="fa fa-print"></i></a>
            </div>

            <div class="bs-report-paper">
                <div class="bs-report-title">
                    <h2><?php echo htmlspecialchars($school_name); ?></h2>
                    <p><strong>Balance Sheet</strong></p>
                    <p>Date as at: <?php echo date('d/m/Y', strtotime($end_date)); ?></p>
                </div>

                <!-- Column headers -->
                <div class="bs-col-headers">
                    <span class="bs-col-hdr"><?php echo $currency_symbol; ?>.</span>
                    <span class="bs-col-hdr"><?php echo $currency_symbol; ?>.</span>
                    <span class="bs-col-hdr"><?php echo $currency_symbol; ?>.</span>
                </div>

                <!-- ========== EQUITY & LIABILITIES ========== -->
                <div class="bs-section-title">Equity and Liabilities</div>

                <?php
                // Combined: Equity first (Capital & Reserves), then Liabilities
                $eq_groups   = bs_group($equity);
                $liab_groups = bs_group($liabilities);
                $all_eq_liab = array_merge($eq_groups, $liab_groups);
                $liab_section_total = 0;
                foreach ($all_eq_liab as $group_name => $group_accounts):
                    $group_total = array_sum(array_map('bs_net', $group_accounts));
                    $liab_section_total += $group_total;
                    $gid = 'g_' . preg_replace('/\W/', '_', $group_name);
                ?>
                <div class="bs-group-title" onclick="toggleGroup('<?php echo $gid; ?>')">
                    <span>
                        <i class="fa fa-chevron-up bs-toggle-icon" id="icon_<?php echo $gid; ?>"></i>
                        &nbsp; <?php echo htmlspecialchars($group_name); ?>
                    </span>
                    <span class="bs-group-amount"><?php echo number_format($group_total, 2); ?></span>
                </div>
                <div id="<?php echo $gid; ?>">
                <?php if ($detailed): foreach ($group_accounts as $acc):
                    $net = bs_net($acc);
                ?>
                    <div class="bs-account-row">
                        <span class="bs-account-name"><?php echo htmlspecialchars($acc->account_name); ?></span>
                        <span class="bs-account-amt"><?php echo number_format($net, 2); ?></span>
                        <span class="bs-account-amt2"></span>
                    </div>
                <?php endforeach; endif; ?>
                </div>
                <?php endforeach; ?>

                <div class="bs-total-row">
                    <span class="bs-total-label">Total Liabilities</span>
                    <span class="bs-total-amt"><strong><?php echo number_format($total_liab_equity, 2); ?></strong></span>
                </div>

                <!-- ========== ASSETS ========== -->
                <div class="bs-section-title" style="margin-top:32px;">Assets</div>

                <?php
                $asset_groups       = bs_group($assets);
                $asset_section_total = 0;
                foreach ($asset_groups as $group_name => $group_accounts):
                    $group_total = array_sum(array_map('bs_net', $group_accounts));
                    $asset_section_total += $group_total;
                    $gid = 'ga_' . preg_replace('/\W/', '_', $group_name);
                ?>
                <div class="bs-group-title" onclick="toggleGroup('<?php echo $gid; ?>')">
                    <span>
                        <i class="fa fa-chevron-up bs-toggle-icon" id="icon_<?php echo $gid; ?>"></i>
                        &nbsp; <?php echo htmlspecialchars($group_name); ?>
                    </span>
                    <span class="bs-group-amount"><?php echo number_format($group_total, 2); ?></span>
                </div>
                <div id="<?php echo $gid; ?>">
                <?php if ($detailed): foreach ($group_accounts as $acc):
                    $net = bs_net($acc);
                ?>
                    <div class="bs-account-row">
                        <span class="bs-account-name"><?php echo htmlspecialchars($acc->account_name); ?></span>
                        <span class="bs-account-amt"><?php echo number_format($net, 2); ?></span>
                        <span class="bs-account-amt2"></span>
                    </div>
                <?php endforeach; endif; ?>
                </div>
                <?php endforeach; ?>

                <div class="bs-total-row">
                    <span class="bs-total-label">Total Assets</span>
                    <span class="bs-total-amt"><strong><?php echo number_format($total_assets, 2); ?></strong></span>
                </div>

            </div><!-- /.bs-report-paper -->
        </div><!-- /.bs-report-area -->
        <?php endif; ?>

    </section>
</div>

<script>
function toggleCriteria() {
    var body = document.getElementById('criteria-body');
    var icon = document.getElementById('criteria-icon');
    if (body.style.display === 'none') {
        body.style.display = '';
        icon.className = 'fa fa-chevron-down';
    } else {
        body.style.display = 'none';
        icon.className = 'fa fa-chevron-right';
    }
}

function toggleCustomDate(val) {
    document.getElementById('custom-date-wrap').style.display = (val === 'custom') ? '' : 'none';
}

function toggleGroup(id) {
    var el   = document.getElementById(id);
    var icon = document.getElementById('icon_' + id);
    if (!el) return;
    if (el.style.display === 'none') {
        el.style.display = '';
        if (icon) icon.className = 'fa fa-chevron-up bs-toggle-icon';
    } else {
        el.style.display = 'none';
        if (icon) icon.className = 'fa fa-chevron-down bs-toggle-icon';
    }
}
</script>
