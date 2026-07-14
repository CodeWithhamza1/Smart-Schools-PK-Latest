<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();

function rbg_fmt($amount) {
    $amount = (float)$amount;
    if ($amount > 0)  return '<span class="dr-val">'.number_format($amount,2).'</span>';
    if ($amount < 0)  return '<span class="cr-val">'.number_format(abs($amount),2).'</span>';
    return '<span class="zero-val">—</span>';
}
function rbg_bal($amount) {
    $amount = (float)$amount;
    if ($amount > 0)  return '<span class="dr-val">'.number_format($amount,2).' Dr</span>';
    if ($amount < 0)  return '<span class="cr-val">'.number_format(abs($amount),2).' Cr</span>';
    return '<span class="zero-val">0.00</span>';
}
function rbg_raw($amount) {
    return number_format((float)$amount, 2);
}

// Group trial_balance rows by account_type_label
$grouped = [];
foreach ($trial_balance as $row) {
    $type = $row['account_type_label'] ?? ($row['account_type'] ?? 'Other');
    $grouped[$type][] = $row;
}
ksort($grouped);

$show_all = ($account_type === '' || $account_type === 'all');
?>
<style>
/* ── Criteria ────────────────────────────────── */
.rbg-criteria {
    background:#fff;
    border-bottom:1px solid #dde3ec;
    padding:18px 26px 20px;
}
.rbg-criteria .form-row {
    display:flex; align-items:flex-end; gap:18px; flex-wrap:wrap;
}
.rbg-criteria .fg { display:flex; flex-direction:column; }
.rbg-criteria label {
    font-size:11px; font-weight:700; color:#555;
    margin-bottom:5px; text-transform:uppercase; letter-spacing:.5px;
}
.rbg-criteria input[type=date],
.rbg-criteria select {
    border:1px solid #c8d0db; border-radius:4px;
    height:36px; padding:0 10px; font-size:13px;
    color:#333; background:#fff;
}
.rbg-criteria input[type=date] { min-width:155px; }
.rbg-criteria select { min-width:220px; }
.btn-run-rbg {
    background:#1a6fc4; color:#fff; border:none;
    border-radius:4px; height:36px; padding:0 22px;
    font-size:13px; font-weight:600; cursor:pointer;
}
.btn-run-rbg:hover { background:#145ca8; }

/* ── Report wrap ──────────────────────────────── */
.rbg-wrap { padding:22px 26px; background:#f0f2f5; min-height:500px; }
.rbg-paper {
    background:#fff; border:1px solid #dde3ec;
    border-radius:4px; padding:28px 32px;
    max-width:1060px; margin:0 auto;
}

/* ── Report header ────────────────────────────── */
.rbg-head { text-align:center; margin-bottom:20px; padding-bottom:14px; border-bottom:2px solid #1a6fc4; }
.rbg-head h2 { margin:0 0 4px; font-size:19px; font-weight:700; color:#1a1a2e; }
.rbg-head .sub { font-size:13px; color:#555; margin:2px 0; }
.rbg-head .period { font-size:12px; color:#999; margin-top:6px; }

/* ── Toolbar ──────────────────────────────────── */
.rbg-toolbar { display:flex; justify-content:flex-end; gap:8px; margin-bottom:12px; }
.rbg-toolbar button {
    background:none; border:1px solid #c8d0db; border-radius:4px;
    padding:5px 14px; font-size:12px; color:#555; cursor:pointer;
    display:inline-flex; align-items:center; gap:5px;
}
.rbg-toolbar button:hover { background:#f5f7fa; color:#222; }

/* ── Section header (one per account type) ───── */
.rbg-type-header {
    background:#1a6fc4; color:#fff;
    font-size:13px; font-weight:700;
    padding:9px 14px;
    margin-top:18px;
    display:flex; justify-content:space-between; align-items:center;
    border-radius:3px 3px 0 0;
}
.rbg-type-header:first-of-type { margin-top:0; }

/* ── Table ────────────────────────────────────── */
table.rbg-table {
    width:100%; border-collapse:collapse; font-size:13px;
    margin-bottom:0;
}
table.rbg-table thead tr { background:#e8edf5; }
table.rbg-table thead th {
    padding:9px 12px; font-weight:700; font-size:11px;
    text-transform:uppercase; letter-spacing:.4px;
    color:#444; border-bottom:2px solid #c8d0db;
    white-space:nowrap;
}
table.rbg-table thead th.num { text-align:right; }
table.rbg-table thead th.w-no  { width:120px; }
table.rbg-table thead th.w-num { width:140px; }

table.rbg-table tbody tr { border-bottom:1px solid #edf0f5; transition:background .1s; }
table.rbg-table tbody tr:hover { background:#f7f9fd; }
table.rbg-table tbody td { padding:8px 12px; color:#333; vertical-align:middle; }
table.rbg-table tbody td.num { text-align:right; font-variant-numeric:tabular-nums; }
table.rbg-table tbody td.acct-name { color:#1a3a5c; font-weight:600; }

/* zero/inactive accounts dimmed */
table.rbg-table tr.zero-row td { color:#bbb; }
table.rbg-table tr.zero-row td.acct-name { color:#ccc; font-weight:400; }

/* subtotal row */
tr.rbg-subtotal td {
    background:#eef3fb; font-weight:700; font-size:13px;
    border-top:2px solid #1a6fc4; border-bottom:2px solid #1a6fc4;
    padding:9px 12px;
}
tr.rbg-subtotal td.num { text-align:right; }

/* grand total */
tr.rbg-grand td {
    background:#1a6fc4; color:#fff; font-weight:700; font-size:13px;
    border-top:3px solid #0d4a8a; padding:10px 12px;
}
tr.rbg-grand td.num { text-align:right; }

/* value colours */
.dr-val   { color:#1a5f1a; }
.cr-val   { color:#b30000; }
.zero-val { color:#bbb; }

/* empty state */
.rbg-empty { text-align:center; padding:50px 20px; color:#aaa; font-size:14px; }

/* print */
@media print {
    .rbg-criteria, .rbg-toolbar, .content-header, .no-print { display:none!important; }
    .rbg-wrap { padding:0; background:#fff; }
    .rbg-paper { border:none; padding:6px; max-width:100%; }
    table.rbg-table thead tr { background:#ddd!important; -webkit-print-color-adjust:exact; }
    .rbg-type-header { background:#333!important; -webkit-print-color-adjust:exact; }
    tr.rbg-grand td { background:#333!important; -webkit-print-color-adjust:exact; }
}
</style>

<div class="content-wrapper" style="min-height:946px; background:#f0f2f5;">
    <section class="content-header no-print">
        <h1><i class="fa fa-bar-chart"></i> Report By Account Type</h1>
    </section>

    <section class="content" style="padding:0;">

        <!-- Criteria -->
        <div class="rbg-criteria no-print">
            <form method="post" action="<?php echo site_url('accounts/report_by_group'); ?>" id="rbg-form">
                <?php echo $this->customlib->getCSRF(); ?>
                <div class="form-row">
                    <div class="fg">
                        <label>Start Date <span style="color:#e74c3c;">*</span></label>
                        <input type="date" name="start_date" id="start_date"
                               value="<?php echo htmlspecialchars($start_date ?: date('Y-m-01')); ?>" required>
                    </div>
                    <div class="fg">
                        <label>End Date <span style="color:#e74c3c;">*</span></label>
                        <input type="date" name="end_date" id="end_date"
                               value="<?php echo htmlspecialchars($end_date ?: date('Y-m-d')); ?>" required>
                    </div>
                    <div class="fg">
                        <label>Account Type</label>
                        <select name="account_type" id="account_type">
                            <option value="all" <?php echo ($account_type === '' || $account_type === 'all') ? 'selected' : ''; ?>>
                                — All Account Types —
                            </option>
                            <?php foreach ($account_types as $at): ?>
                                <option value="<?php echo htmlspecialchars($at['account_type']); ?>"
                                    <?php echo ($account_type === $at['account_type']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($at['account_type']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="fg">
                        <button type="submit" class="btn-run-rbg">
                            <i class="fa fa-search"></i> Get Report
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Report -->
        <div class="rbg-wrap">
            <div class="rbg-paper">

                <?php if (!empty($trial_balance)): ?>
                <div class="rbg-toolbar no-print">
                    <button onclick="window.print()"><i class="fa fa-print"></i> Print</button>
                    <button onclick="exportRbgCSV()"><i class="fa fa-file-excel-o"></i> Export CSV</button>
                </div>
                <?php endif; ?>

                <!-- Report header -->
                <div class="rbg-head">
                    <h2><?php echo htmlspecialchars($school_name ?: 'School'); ?></h2>
                    <p class="sub"><strong>Trial Balance — Report by Account Type</strong></p>
                    <?php if ($account_type && $account_type !== 'all'): ?>
                        <p class="sub">Account Type: <strong><?php echo htmlspecialchars($account_type); ?></strong></p>
                    <?php else: ?>
                        <p class="sub">All Account Types</p>
                    <?php endif; ?>
                    <?php if ($start_date && $end_date): ?>
                        <p class="period">
                            Period: <?php echo date('d M Y', strtotime($start_date)); ?>
                            &ndash; <?php echo date('d M Y', strtotime($end_date)); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (empty($grouped) && !$start_date): ?>
                    <div class="rbg-empty">
                        <i class="fa fa-search fa-2x" style="display:block;margin-bottom:10px;"></i>
                        Select a date range and account type, then click <strong>Get Report</strong>.
                    </div>

                <?php elseif (empty($grouped)): ?>
                    <div class="rbg-empty">
                        <i class="fa fa-inbox fa-2x" style="display:block;margin-bottom:10px;"></i>
                        No accounts found for the selected criteria.
                    </div>

                <?php else:
                    // Grand totals accumulators
                    $grand_op = 0; $grand_dr = 0; $grand_cr = 0; $grand_cl = 0;
                ?>

                <table class="rbg-table" id="rbg-main-table">
                    <?php foreach ($grouped as $type_label => $rows):
                        // Subtotals for this type
                        $sub_op = array_sum(array_column($rows, 'opening_balance'));
                        $sub_dr = array_sum(array_column($rows, 'debit_balance'));
                        $sub_cr = array_sum(array_column($rows, 'credit_balance'));
                        $sub_cl = array_sum(array_column($rows, 'closing_balance'));
                        $grand_op += $sub_op; $grand_dr += $sub_dr;
                        $grand_cr += $sub_cr; $grand_cl += $sub_cl;

                        // Sort: accounts with activity first, then by account_number
                        usort($rows, function($a, $b) {
                            $a_active = ($a['debit_balance'] != 0 || $a['credit_balance'] != 0 || $a['opening_balance'] != 0) ? 0 : 1;
                            $b_active = ($b['debit_balance'] != 0 || $b['credit_balance'] != 0 || $b['opening_balance'] != 0) ? 0 : 1;
                            if ($a_active !== $b_active) return $a_active - $b_active;
                            return strcmp($a['account_number'], $b['account_number']);
                        });
                    ?>

                    <!-- Type section header -->
                    <thead>
                        <tr>
                            <td colspan="6" style="padding:0; border:none;">
                                <div class="rbg-type-header">
                                    <span><i class="fa fa-folder-open-o"></i>&nbsp; <?php echo htmlspecialchars($type_label); ?></span>
                                    <span style="font-size:12px; opacity:.85;">
                                        Net: <?php echo number_format($sub_cl, 2) . (($sub_cl >= 0) ? ' Dr' : ' Cr'); ?>
                                    </span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th class="w-no">Account No.</th>
                            <th>Account Name</th>
                            <th class="num w-num">Opening Balance</th>
                            <th class="num w-num">Debit</th>
                            <th class="num w-num">Credit</th>
                            <th class="num w-num">Closing Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row):
                            $is_zero = ($row['debit_balance'] == 0 && $row['credit_balance'] == 0 && $row['opening_balance'] == 0);
                        ?>
                        <tr class="<?php echo $is_zero ? 'zero-row' : ''; ?>">
                            <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                            <td class="acct-name"><?php echo htmlspecialchars($row['account_name']); ?></td>
                            <td class="num"><?php echo rbg_bal($row['opening_balance']); ?></td>
                            <td class="num"><?php echo $row['debit_balance']  > 0 ? rbg_fmt($row['debit_balance'])  : '<span class="zero-val">—</span>'; ?></td>
                            <td class="num"><?php echo $row['credit_balance'] > 0 ? rbg_fmt($row['credit_balance']) : '<span class="zero-val">—</span>'; ?></td>
                            <td class="num"><?php echo rbg_bal($row['closing_balance']); ?></td>
                        </tr>
                        <?php endforeach; ?>

                        <!-- Subtotal for this type -->
                        <tr class="rbg-subtotal">
                            <td colspan="2">
                                <strong>Sub-Total: <?php echo htmlspecialchars($type_label); ?></strong>
                            </td>
                            <td class="num"><?php echo rbg_bal($sub_op); ?></td>
                            <td class="num"><strong><?php echo $sub_dr > 0 ? rbg_fmt($sub_dr) : '<span class="zero-val">—</span>'; ?></strong></td>
                            <td class="num"><strong><?php echo $sub_cr > 0 ? rbg_fmt($sub_cr) : '<span class="zero-val">—</span>'; ?></strong></td>
                            <td class="num"><?php echo rbg_bal($sub_cl); ?></td>
                        </tr>

                    </tbody>
                    <?php endforeach; ?>

                    <?php if ($show_all || count($grouped) > 1): ?>
                    <!-- Grand total -->
                    <tfoot>
                        <tr class="rbg-grand">
                            <td colspan="2"><strong>GRAND TOTAL</strong></td>
                            <td class="num"><strong><?php echo number_format($grand_op,2) . ($grand_op >= 0 ? ' Dr' : ' Cr'); ?></strong></td>
                            <td class="num"><strong><?php echo number_format($grand_dr,2); ?></strong></td>
                            <td class="num"><strong><?php echo number_format($grand_cr,2); ?></strong></td>
                            <td class="num"><strong><?php echo number_format(abs($grand_cl),2) . ($grand_cl >= 0 ? ' Dr' : ' Cr'); ?></strong></td>
                        </tr>
                    </tfoot>
                    <?php endif; ?>

                </table>

                <?php endif; ?>

            </div><!-- /.rbg-paper -->
        </div><!-- /.rbg-wrap -->

    </section>
</div>

<script>
function exportRbgCSV() {
    var rows = [['Account Type','Account No.','Account Name','Opening Balance','Debit','Credit','Closing Balance']];
    var currentType = '';
    $('#rbg-main-table tbody').each(function() {
        var typeHdr = $(this).prev('thead').find('.rbg-type-header span:first').text().trim();
        if (typeHdr) currentType = typeHdr;
        $(this).find('tr:not(.rbg-subtotal)').each(function() {
            var cells = [];
            cells.push('"' + currentType.replace(/"/g,'""') + '"');
            $(this).find('td').each(function(i) {
                if (i < 6) cells.push('"' + $(this).text().replace(/"/g,'""').trim() + '"');
            });
            if (cells.length >= 7) rows.push(cells);
        });
    });
    var csv = rows.map(function(r){ return r.join(','); }).join('\n');
    var blob = new Blob([csv], {type:'text/csv'});
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'trial_balance_by_type.csv';
    a.click();
}
</script>
