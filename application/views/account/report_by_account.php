<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();

// Helper: format a raw float as Dr / Cr string
function rba_fmt($amount) {
    $amount = (float)$amount;
    if ($amount >= 0) return number_format($amount, 2) . ' Dr';
    return number_format(abs($amount), 2) . ' Cr';
}
function rba_num($amount) {
    return number_format((float)$amount, 2);
}
?>
<style>
/* ── Criteria panel ─────────────────────────────── */
.rba-criteria {
    background: #fff;
    border-bottom: 1px solid #dde3ec;
    padding: 18px 24px 20px;
}
.rba-criteria .form-row {
    display: flex; align-items: flex-end; gap: 20px; flex-wrap: wrap;
}
.rba-criteria .fg { display: flex; flex-direction: column; }
.rba-criteria label {
    font-size: 12px; font-weight: 700; color: #444;
    margin-bottom: 5px; text-transform: uppercase; letter-spacing: .4px;
}
.rba-criteria input[type=date],
.rba-criteria select {
    border: 1px solid #c8d0db;
    border-radius: 4px; height: 36px;
    padding: 0 10px; font-size: 13px; color: #333;
    background: #fff;
}
.rba-criteria input[type=date] { min-width: 160px; }
.rba-criteria select { min-width: 260px; }
.btn-run-rpt {
    background: #1a6fc4; color: #fff; border: none;
    border-radius: 4px; height: 36px; padding: 0 22px;
    font-size: 13px; font-weight: 600; cursor: pointer;
    white-space: nowrap;
}
.btn-run-rpt:hover { background: #145ca8; }

/* ── Report paper ───────────────────────────────── */
.rba-report-wrap {
    padding: 24px 28px;
    background: #f0f2f5;
    min-height: 500px;
}
.rba-paper {
    background: #fff;
    border: 1px solid #dde3ec;
    border-radius: 4px;
    padding: 30px 36px;
    max-width: 960px;
    margin: 0 auto;
}

/* Header */
.rba-report-head {
    text-align: center;
    margin-bottom: 22px;
    padding-bottom: 14px;
    border-bottom: 2px solid #1a6fc4;
}
.rba-report-head h2 {
    margin: 0 0 4px;
    font-size: 20px;
    font-weight: 700;
    color: #1a1a2e;
}
.rba-report-head .sub { font-size: 13px; color: #555; margin: 2px 0; }
.rba-report-head .period { font-size: 12px; color: #888; margin-top: 6px; }

/* Toolbar */
.rba-toolbar {
    display: flex; justify-content: flex-end; gap: 8px;
    margin-bottom: 14px;
}
.rba-toolbar button, .rba-toolbar a {
    background: none; border: 1px solid #c8d0db;
    border-radius: 4px; padding: 5px 14px;
    font-size: 12px; color: #555; cursor: pointer;
    text-decoration: none; display: inline-flex;
    align-items: center; gap: 5px;
}
.rba-toolbar button:hover, .rba-toolbar a:hover { background: #f5f7fa; color: #222; }

/* Table */
table.rba-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
table.rba-table thead tr {
    background: #1a6fc4;
    color: #fff;
}
table.rba-table thead th {
    padding: 10px 12px;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .4px;
    border: none;
    white-space: nowrap;
}
table.rba-table thead th.num { text-align: right; }

table.rba-table tbody tr {
    border-bottom: 1px solid #edf0f5;
    transition: background .1s;
}
table.rba-table tbody tr:hover { background: #f7f9fd; }
table.rba-table tbody td {
    padding: 9px 12px;
    color: #333;
    vertical-align: middle;
}
table.rba-table tbody td.num { text-align: right; font-variant-numeric: tabular-nums; }
table.rba-table tbody td.num.dr { color: #1a5f1a; }
table.rba-table tbody td.num.cr { color: #b30000; }
table.rba-table tbody td.ref  { font-size: 11px; color: #888; }
table.rba-table tbody td.desc { font-size: 12px; color: #666; font-style: italic; }

/* Opening row */
tr.rba-opening td { background: #eef4ff; font-weight: 600; color: #1a3a5c; }

/* Total row */
tr.rba-total td {
    background: #f0f4ff;
    font-weight: 700;
    font-size: 13px;
    border-top: 2px solid #1a6fc4;
    border-bottom: 2px solid #1a6fc4;
    padding: 10px 12px;
}
tr.rba-total td.num { text-align: right; }

/* Closing balance row */
tr.rba-closing td {
    background: #1a6fc4;
    color: #fff;
    font-weight: 700;
    font-size: 13px;
    padding: 10px 12px;
}
tr.rba-closing td.num { text-align: right; }

/* Empty state */
.rba-empty {
    text-align: center; padding: 50px 20px;
    color: #aaa; font-size: 14px;
}

/* Print */
@media print {
    .rba-criteria, .rba-toolbar, .content-header, .no-print { display: none !important; }
    .rba-report-wrap { padding: 0; background: #fff; }
    .rba-paper { border: none; padding: 10px; max-width: 100%; }
    .rba-report-head { border-bottom-color: #000; }
    table.rba-table thead tr { background: #333 !important; -webkit-print-color-adjust: exact; }
    tr.rba-closing td { background: #333 !important; -webkit-print-color-adjust: exact; }
}
</style>

<div class="content-wrapper" style="min-height:946px; background:#f0f2f5;">
    <section class="content-header no-print">
        <h1><i class="fa fa-book"></i> Report By Account</h1>
    </section>

    <section class="content" style="padding:0;">

        <!-- Criteria panel -->
        <div class="rba-criteria no-print">
            <form method="post" action="<?php echo site_url('accounts/report_by_account'); ?>" id="rba-form">
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
                    <div class="fg" style="flex:1; min-width:220px;">
                        <label>Account <span style="color:#e74c3c;">*</span></label>
                        <select name="account_id" id="account_id" class="select2-rba" required>
                            <option value="">— Select Account —</option>
                            <?php foreach ($accounts as $acc): ?>
                                <option value="<?php echo $acc['id']; ?>"
                                    <?php echo ($account_id == $acc['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($acc['account_name'] . ' (' . $acc['account_number'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="fg">
                        <button type="submit" class="btn-run-rpt">
                            <i class="fa fa-search"></i> Get Report
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Report area -->
        <div class="rba-report-wrap">
            <div class="rba-paper">

                <!-- Toolbar -->
                <?php if (!empty($trial_balance)): ?>
                <div class="rba-toolbar no-print">
                    <button onclick="window.print()"><i class="fa fa-print"></i> Print</button>
                    <button onclick="exportRbaCSV()"><i class="fa fa-file-excel-o"></i> Export</button>
                </div>
                <?php endif; ?>

                <!-- Report header -->
                <div class="rba-report-head">
                    <h2><?php echo htmlspecialchars($school_name ?: 'School'); ?></h2>
                    <p class="sub"><strong>Account Ledger Report</strong></p>
                    <?php if ($selected_account_name): ?>
                        <p class="sub"><?php echo htmlspecialchars($selected_account_name); ?></p>
                    <?php endif; ?>
                    <?php if ($start_date && $end_date): ?>
                        <p class="period">
                            Period: <?php echo date('d M Y', strtotime($start_date)); ?>
                            &ndash; <?php echo date('d M Y', strtotime($end_date)); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (empty($trial_balance) && !$account_id): ?>
                    <div class="rba-empty">
                        <i class="fa fa-search fa-2x" style="display:block;margin-bottom:10px;"></i>
                        Select an account and date range, then click <strong>Get Report</strong>.
                    </div>

                <?php elseif (empty($trial_balance) && $account_id): ?>
                    <div class="rba-empty">
                        <i class="fa fa-inbox fa-2x" style="display:block;margin-bottom:10px;"></i>
                        No transactions found for this account in the selected period.
                    </div>

                <?php else:
                    // Compute totals
                    $total_dr     = array_sum(array_column($trial_balance, 'debit_balance'));
                    $total_cr     = array_sum(array_column($trial_balance, 'credit_balance'));
                    $opening_raw  = $trial_balance[0]['opening_balance'];
                    $closing_raw  = end($trial_balance)['closing_balance'];
                ?>

                <table class="rba-table" id="rba-table">
                    <thead>
                        <tr>
                            <th style="width:100px;">Date</th>
                            <th style="width:110px;">Ref. No.</th>
                            <th>Description / Narration</th>
                            <th class="num" style="width:130px;">Debit</th>
                            <th class="num" style="width:130px;">Credit</th>
                            <th class="num" style="width:140px;">Balance</th>
                        </tr>
                    </thead>
                    <tbody>

                        <!-- Opening balance row -->
                        <tr class="rba-opening">
                            <td><?php echo date('d/m/Y', strtotime($start_date)); ?></td>
                            <td colspan="2"><em>Opening Balance</em></td>
                            <td class="num"></td>
                            <td class="num"></td>
                            <td class="num <?php echo ($opening_raw >= 0) ? 'dr' : 'cr'; ?>">
                                <?php echo rba_fmt($opening_raw); ?>
                            </td>
                        </tr>

                        <!-- Transaction rows -->
                        <?php foreach ($trial_balance as $row): ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($row['voucher_date'])); ?></td>
                            <td class="ref"><?php echo htmlspecialchars($row['voucher_number']); ?></td>
                            <td>
                                <?php
                                $desc = trim($row['description'] ?? '');
                                $narr = trim($row['voucher_narration'] ?? '');
                                echo htmlspecialchars($desc ?: $narr ?: '—');
                                ?>
                            </td>
                            <td class="num <?php echo ($row['debit_balance'] > 0) ? 'dr' : ''; ?>">
                                <?php echo ($row['debit_balance'] > 0) ? rba_num($row['debit_balance']) : '—'; ?>
                            </td>
                            <td class="num <?php echo ($row['credit_balance'] > 0) ? 'cr' : ''; ?>">
                                <?php echo ($row['credit_balance'] > 0) ? rba_num($row['credit_balance']) : '—'; ?>
                            </td>
                            <td class="num <?php echo ($row['closing_balance'] >= 0) ? 'dr' : 'cr'; ?>">
                                <?php echo rba_fmt($row['closing_balance']); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>

                        <!-- Totals row -->
                        <tr class="rba-total">
                            <td colspan="3"><strong>Period Totals</strong></td>
                            <td class="num dr"><strong><?php echo rba_num($total_dr); ?></strong></td>
                            <td class="num cr"><strong><?php echo rba_num($total_cr); ?></strong></td>
                            <td class="num"></td>
                        </tr>

                        <!-- Closing balance row -->
                        <tr class="rba-closing">
                            <td><?php echo date('d/m/Y', strtotime($end_date)); ?></td>
                            <td colspan="2"><strong>Closing Balance</strong></td>
                            <td class="num"></td>
                            <td class="num"></td>
                            <td class="num">
                                <strong><?php echo rba_fmt($closing_raw); ?></strong>
                            </td>
                        </tr>

                    </tbody>
                </table>

                <?php endif; ?>

            </div><!-- /.rba-paper -->
        </div><!-- /.rba-report-wrap -->

    </section>
</div>

<script>
$(document).ready(function() {
    $('.select2-rba').select2({ width: '100%' });
});

function exportRbaCSV() {
    var rows = [['Date','Ref No','Description','Debit','Credit','Balance']];
    $('#rba-table tbody tr').each(function() {
        var cells = [];
        $(this).find('td').each(function() {
            cells.push('"' + $(this).text().replace(/"/g,'""').trim() + '"');
        });
        if (cells.length >= 6) rows.push(cells);
    });
    var csv = rows.map(function(r){ return r.join(','); }).join('\n');
    var blob = new Blob([csv], {type:'text/csv'});
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'account_ledger_<?php echo $account_id; ?>.csv';
    a.click();
}
</script>

