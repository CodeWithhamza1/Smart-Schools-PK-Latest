<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();

$date_range_labels = [
    'this_month'   => 'This Month',
    'last_month'   => 'Last Month',
    'this_quarter' => 'This Quarter',
    'this_year'    => 'This Year',
    'last_year'    => 'Last Year',
    'custom'       => 'Custom',
];
?>
<style>
.tb-wrap { background:#f4f6f9; min-height:100vh; }

/* Criteria panel */
.tb-criteria-panel { background:#fff; border-bottom:1px solid #e0e4ea; }
.tb-criteria-toggle {
    display:flex; align-items:center; justify-content:space-between;
    padding:14px 22px; cursor:pointer; user-select:none;
}
.tb-criteria-toggle h4 { margin:0; font-size:15px; font-weight:600; color:#333; }
.tb-criteria-body { padding:16px 22px 22px; border-top:1px solid #e8ecf0; }
.tb-settings-btn { color:#666; font-size:20px; }

/* Inline form */
.form-inline-row { display:flex; align-items:flex-end; gap:20px; flex-wrap:wrap; margin-bottom:16px; }
.form-inline-row .fg { display:flex; flex-direction:column; gap:4px; }
.form-inline-row label { font-size:12px; font-weight:600; color:#444; margin:0; }
.form-inline-row select,
.form-inline-row input[type=date] {
    border:1px solid #ccc; border-radius:4px; padding:6px 10px;
    font-size:13px; min-width:180px; background:#fff;
}
.form-inline-row input[readonly] { background:#f5f5f5; color:#555; }
.btn-run {
    background:#2e7d4f; color:#fff; border:none; border-radius:20px;
    padding:9px 26px; font-size:14px; font-weight:600; cursor:pointer;
}
.btn-run:hover { background:#235f3b; }

/* Report area */
.tb-report-area { padding:24px 32px; }
.tb-toolbar { text-align:right; margin-bottom:10px; }
.tb-toolbar a { margin-left:10px; color:#555; font-size:18px; text-decoration:none; }
.tb-toolbar a:hover { color:#222; }

.tb-paper {
    background:#fff; border:1px solid #e0e4ea; border-radius:4px;
    padding:28px 32px; max-width:900px; margin:0 auto;
}

/* Report title */
.tb-title { text-align:center; margin-bottom:20px; }
.tb-title h2 { font-size:20px; font-weight:700; margin:0 0 4px; color:#111; }

/* Date labels */
.tb-dates { font-size:13px; color:#333; margin-bottom:16px; line-height:1.7; }
.tb-dates strong { font-weight:700; }

/* DataTable overrides */
table.tb-table { width:100% !important; border-collapse:collapse; font-size:13px; }
table.tb-table thead tr th {
    border-top:2px solid #333;
    border-bottom:1px solid #999;
    padding:8px 12px;
    font-weight:700;
    color:#111;
    background:#fff;
    white-space:nowrap;
}
table.tb-table tbody tr td {
    padding:7px 12px;
    border-bottom:1px solid #eee;
    color:#222;
}
table.tb-table tbody tr:last-child td { border-bottom:none; }

/* Total row */
.tb-total-table { margin-top:0; }
.tb-total-row td {
    border-top:2px solid #333 !important;
    border-bottom:2px solid #333 !important;
    font-weight:700 !important;
    padding:8px 12px !important;
    background:#fff !important;
}
.tb-total-label { color:#2a6e99 !important; }

.col-account { width:55%; }
.col-debit   { width:22%; text-align:right !important; }
.col-credit  { width:23%; text-align:right !important; }

/* DataTables search */
.dataTables_filter label { font-size:13px; }
.dataTables_filter input { border:1px solid #ccc; border-radius:3px; padding:4px 8px; font-size:13px; }
.dataTables_info, .dataTables_paginate { display:none !important; }

@media print {
    .tb-criteria-panel, .tb-toolbar, .no-print,
    .dataTables_filter, .dataTables_length { display:none !important; }
    .tb-wrap { background:#fff; }
    .tb-report-area { padding:0; }
    .tb-paper { border:none; padding:8px; box-shadow:none; }
    .content-header { display:none; }
}
</style>

<div class="content-wrapper tb-wrap">
    <section class="content-header no-print">
        <h1><i class="fa fa-list-alt"></i> Trial Balance New</h1>
    </section>

    <section class="content">

        <!-- ── Criteria Panel ── -->
        <div class="tb-criteria-panel no-print">
            <div class="tb-criteria-toggle" onclick="toggleCriteria()">
                <h4><i class="fa fa-chevron-down" id="tb-crit-icon"></i>&nbsp; Report Criteria</h4>
                <span class="tb-settings-btn"><i class="fa fa-cog"></i> Settings</span>
            </div>
            <div class="tb-criteria-body" id="tb-crit-body">
                <form method="post" action="<?php echo site_url('accounts/trial_balance_new'); ?>" id="tb-form">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <input type="hidden" name="run_report" value="1">

                    <div class="form-inline-row">
                        <div class="fg">
                            <label>Date Range</label>
                            <select name="date_range" id="tb-range-sel" onchange="onRangeChange(this.value)">
                                <?php foreach ($date_range_labels as $val => $lbl): ?>
                                <option value="<?php echo $val; ?>" <?php echo ($date_range == $val) ? 'selected' : ''; ?>><?php echo $lbl; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="fg" id="tb-date-from-wrap">
                            <label>Date From</label>
                            <?php if ($date_range === 'custom'): ?>
                            <input type="date" name="custom_from" value="<?php echo htmlspecialchars($start_date); ?>">
                            <?php else: ?>
                            <input type="text" value="<?php echo date('d/m/Y', strtotime($start_date)); ?>" readonly>
                            <?php endif; ?>
                        </div>

                        <div class="fg" id="tb-date-to-wrap">
                            <label>Date To</label>
                            <?php if ($date_range === 'custom'): ?>
                            <input type="date" name="custom_to" value="<?php echo htmlspecialchars($end_date); ?>">
                            <?php else: ?>
                            <input type="text" value="<?php echo date('d/m/Y', strtotime($end_date)); ?>" readonly>
                            <?php endif; ?>
                        </div>
                    </div>

                    <button type="submit" class="btn-run">Run Report</button>
                </form>
            </div>
        </div>

        <!-- ── Report ── -->
        <?php if ($run): ?>
        <div class="tb-report-area">
            <div class="tb-toolbar no-print">
                <a href="javascript:window.print()" title="Print"><i class="fa fa-print"></i></a>
                <a href="javascript:exportExcel()" title="Export Excel"><i class="fa fa-file-excel-o" style="color:#217346;"></i></a>
            </div>

            <div class="tb-paper">
                <div class="tb-title">
                    <h2>Trial Balance</h2>
                </div>

                <div class="tb-dates">
                    <strong>Date From:</strong> <?php echo date('d/m/Y', strtotime($start_date)); ?><br>
                    <strong>Date To:</strong> <?php echo date('d/m/Y', strtotime($end_date)); ?>
                </div>

                <?php
                $total_debit  = 0;
                $total_credit = 0;
                $visible_rows = [];
                foreach ($trial_balance as $row) {
                    $d = floatval($row['debit_balance']);
                    $c = floatval($row['credit_balance']);
                    if ($d == 0 && $c == 0) continue;
                    $total_debit  += $d;
                    $total_credit += $c;
                    $visible_rows[] = $row;
                }
                ?>

                <table id="tb-datatable" class="tb-table">
                    <thead>
                        <tr>
                            <th class="col-account">Account</th>
                            <th class="col-debit">Debit</th>
                            <th class="col-credit">Credit</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($visible_rows as $row):
                        $debit  = floatval($row['debit_balance']);
                        $credit = floatval($row['credit_balance']);
                    ?>
                        <tr>
                            <td class="col-account"><?php echo htmlspecialchars($row['account_name']); ?></td>
                            <td class="col-debit"><?php echo $debit  > 0 ? number_format($debit,  2) : ''; ?></td>
                            <td class="col-credit"><?php echo $credit > 0 ? number_format($credit, 2) : ''; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Total row outside DataTable so it is always visible -->
                <table class="tb-table tb-total-table">
                    <tbody>
                        <tr class="tb-total-row">
                            <td class="col-account tb-total-label">Total</td>
                            <td class="col-debit"><?php echo number_format($total_debit,  2); ?></td>
                            <td class="col-credit"><?php echo number_format($total_credit, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div><!-- /.tb-paper -->
        </div><!-- /.tb-report-area -->
        <?php endif; ?>

    </section>
</div>

<script>
function toggleCriteria() {
    var body = document.getElementById('tb-crit-body');
    var icon = document.getElementById('tb-crit-icon');
    body.style.display = (body.style.display === 'none') ? '' : 'none';
    icon.className     = (body.style.display === 'none') ? 'fa fa-chevron-right' : 'fa fa-chevron-down';
}

function onRangeChange(val) {
    // Reload page to recalculate dates — submit with run_report=0
    var form = document.getElementById('tb-form');
    var hidden = form.querySelector('input[name=run_report]');
    hidden.value = '0';
    form.submit();
}

function exportExcel() {
    var table = document.getElementById('tb-datatable');
    if (!table) return;
    var html = '<table>' + table.outerHTML + '</table>';
    var blob = new Blob([html], {type: 'application/vnd.ms-excel'});
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'trial_balance.xls';
    a.click();
}

<?php if ($run): ?>
$(document).ready(function() {
    $('#tb-datatable').DataTable({
        paging: false,
        ordering: true,
        info: false,
        searching: true,
        columnDefs: [
            { orderable: false, targets: 0 }
        ],
        dom: '<"tb-search-row"f>t',
        language: { search: 'Search:' }
    });
});
<?php endif; ?>
</script>
