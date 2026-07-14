<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$months = ['','January','February','March','April','May','June','July','August','September','October','November','December'];

function fcr_period($row, $months) {
    $m = (int)($row['clnd_month'] ?? 0);
    $y = $row['fee_year'] ?? '';
    if ($m && $y) return ($months[$m] ?? $m).' '.$y;
    if (!empty($row['fee_session'])) return $row['fee_session'];
    return '—';
}
?>
<style>
/* ===== Fee Collection Report ===== */
.fcr-wrap { padding: 20px 24px; background: #f0f2f5; min-height: 600px; }
.fcr-card { background: #fff; border: 1px solid #dde3ec; border-radius: 4px; overflow: hidden; margin-bottom: 16px; }

.fcr-header {
    padding: 13px 20px; border-bottom: 1px solid #dde3ec;
    display: flex; align-items: center; justify-content: space-between;
}
.fcr-header h2 { margin: 0; font-size: 16px; font-weight: 700; color: #1a1a2e; }

/* Filter bar */
.fcr-filter-body { padding: 14px 20px; display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
.fcr-field { display: flex; flex-direction: column; min-width: 0; }
.fcr-field label { font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px; }
.fcr-field input, .fcr-field select {
    border: 1px solid #c8d0db; border-radius: 4px; height: 34px;
    padding: 0 8px; font-size: 13px; color: #333; background: #fff;
}
.fcr-field input:focus, .fcr-field select:focus { border-color: #1a6fc4; outline: none; }
.fcr-field-sm  { flex: 0 0 110px; }
.fcr-field-md  { flex: 0 0 140px; }
.fcr-field-lg  { flex: 1 1 160px; }

.btn-fcr-filter {
    background: #1a6fc4; color: #fff; border: none; border-radius: 4px;
    height: 34px; padding: 0 20px; font-size: 13px; font-weight: 600; cursor: pointer;
    white-space: nowrap; align-self: flex-end;
}
.btn-fcr-filter:hover { background: #155da0; }
.btn-fcr-reset {
    background: #fff; color: #555; border: 1px solid #c8d0db; border-radius: 4px;
    height: 34px; padding: 0 14px; font-size: 13px; cursor: pointer; align-self: flex-end;
    text-decoration: none; display: inline-flex; align-items: center;
}
.btn-fcr-reset:hover { background: #f5f7fa; color: #333; text-decoration: none; }

/* Top controls */
.fcr-topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 20px; border-top: 1px solid #eef0f5;
}
.fcr-topbar-left { display: flex; gap: 8px; align-items: center; font-size: 13px; color: #555; }
.fcr-topbar-left select { border: 1px solid #c8d0db; border-radius: 4px; height: 30px; padding: 0 6px; font-size: 13px; }
.fcr-topbar-right { display: flex; gap: 8px; }

.btn-fcr-export {
    background: #27ae60; color: #fff; border: none; border-radius: 20px;
    padding: 6px 16px; font-size: 12px; font-weight: 600; cursor: pointer;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-fcr-export:hover { background: #219150; }
.btn-fcr-print {
    background: #1a6fc4; color: #fff; border: none; border-radius: 20px;
    padding: 6px 16px; font-size: 12px; font-weight: 600; cursor: pointer;
    display: inline-flex; align-items: center; gap: 5px;
}
.btn-fcr-print:hover { background: #155da0; }

/* Search */
.fcr-search input {
    border: 1px solid #c8d0db; border-radius: 20px; height: 30px;
    padding: 0 12px; font-size: 13px; width: 200px;
}
.fcr-search input:focus { border-color: #1a6fc4; outline: none; }

/* Table */
.fcr-table-wrap { padding: 0 20px 16px; overflow-x: auto; }
table.fcr-table { width: 100%; border-collapse: collapse; font-size: 13px; }
table.fcr-table thead tr { background: #1a6fc4; color: #fff; }
table.fcr-table thead th {
    padding: 9px 10px; font-weight: 600; font-size: 12px;
    text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; border: none;
}
table.fcr-table thead th.num { text-align: right; }
table.fcr-table tbody tr { border-bottom: 1px solid #eef0f5; }
table.fcr-table tbody tr:hover { background: #f5f9ff; }
table.fcr-table tbody td { padding: 7px 10px; vertical-align: middle; }
table.fcr-table tbody td.num { text-align: right; font-weight: 600; }
table.fcr-table tbody td.mono { font-family: monospace; font-size: 12px; }

.fcr-badge {
    display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 700;
    background: #e8f5e9; color: #27ae60;
}
.fcr-badge.cash  { background: #e8f5e9; color: #27ae60; }
.fcr-badge.bank  { background: #e3f2fd; color: #1a6fc4; }
.fcr-badge.cheq  { background: #fff8e1; color: #f39c12; }
.fcr-badge.onl   { background: #f3e5f5; color: #8e44ad; }

/* Empty */
.fcr-empty { padding: 32px; text-align: center; color: #aaa; font-size: 13px; }

/* Summary footer */
.fcr-summary {
    display: flex; justify-content: flex-end; gap: 32px;
    padding: 10px 20px; background: #f0f7ff; border-top: 2px solid #1a6fc4;
    font-size: 13px; font-weight: 700; color: #1a3a5c;
}
.fcr-summary span { color: #888; font-weight: 400; }

/* Pagination */
.fcr-pagination { padding: 10px 20px; display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: #666; }
.fcr-pages { display: flex; gap: 4px; }
.fcr-pages button {
    border: 1px solid #c8d0db; background: #fff; border-radius: 4px;
    width: 30px; height: 28px; font-size: 12px; cursor: pointer;
}
.fcr-pages button.active { background: #1a6fc4; color: #fff; border-color: #1a6fc4; }
.fcr-pages button:hover:not(.active) { background: #eef4fb; }
</style>

<div class="content-wrapper" style="min-height: 946px; background: #f0f2f5;">
    <section class="content-header">
        <h1><i class="fa fa-file-text-o"></i> Collection Report</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Collection Report</li>
        </ol>
    </section>

    <section class="content" style="padding: 0 10px 10px;">
        <?php if ($this->session->flashdata('msg')): ?>
            <div style="padding: 10px 0 0;"><?php echo $this->session->flashdata('msg'); ?></div>
        <?php endif; ?>

        <div class="fcr-wrap">

            <!-- Filter Card -->
            <div class="fcr-card">
                <div class="fcr-header">
                    <h2><i class="fa fa-filter" style="color:#1a6fc4; margin-right:6px;"></i> Filters</h2>
                </div>
                <form method="get" action="<?php echo site_url('accounts/fee_collection_report'); ?>">
                    <div class="fcr-filter-body">
                        <div class="fcr-field fcr-field-md">
                            <label>From Date</label>
                            <input type="date" name="start_date" value="<?php echo htmlspecialchars($filters['start_date']); ?>">
                        </div>
                        <div class="fcr-field fcr-field-md">
                            <label>To Date</label>
                            <input type="date" name="end_date" value="<?php echo htmlspecialchars($filters['end_date']); ?>">
                        </div>
                        <div class="fcr-field fcr-field-md">
                            <label>Class</label>
                            <select name="class_id">
                                <option value="">All Classes</option>
                                <?php foreach ($classes as $cl): ?>
                                    <option value="<?php echo $cl['id']; ?>" <?php echo ($filters['class_id'] == $cl['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cl['class']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="fcr-field fcr-field-md">
                            <label>Fee Type</label>
                            <select name="fee_type_id">
                                <option value="">All Types</option>
                                <?php foreach ($fee_types as $ft): ?>
                                    <option value="<?php echo $ft['id']; ?>" <?php echo ($filters['fee_type_id'] == $ft['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($ft['type']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="fcr-field fcr-field-md">
                            <label>Admission No.</label>
                            <input type="text" name="admission_no" value="<?php echo htmlspecialchars($filters['admission_no'] ?? ''); ?>" placeholder="e.g. 00417">
                        </div>
                        <button type="submit" class="btn-fcr-filter"><i class="fa fa-search"></i> Search</button>
                        <a href="<?php echo site_url('accounts/fee_collection_report'); ?>" class="btn-fcr-reset"><i class="fa fa-refresh"></i> Reset</a>
                    </div>
                </form>
            </div>

            <!-- Results Card -->
            <div class="fcr-card">
                <div class="fcr-header">
                    <h2><i class="fa fa-list-alt" style="color:#1a6fc4; margin-right:6px;"></i>
                        Collection Report
                        <span style="font-size:12px; font-weight:400; color:#888; margin-left:8px;">
                            <?php echo date('d M Y', strtotime($filters['start_date'])); ?> — <?php echo date('d M Y', strtotime($filters['end_date'])); ?>
                        </span>
                    </h2>
                </div>

                <div class="fcr-topbar">
                    <div class="fcr-topbar-left">
                        Show
                        <select id="fcr-per-page" onchange="fcrChangePage(1)">
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="999999">All</option>
                        </select>
                        entries
                        &nbsp;|&nbsp;
                        <div class="fcr-search">
                            <input type="text" id="fcr-search" placeholder="Search name, class, type…" oninput="fcrChangePage(1)">
                        </div>
                    </div>
                    <div class="fcr-topbar-right">
                        <button class="btn-fcr-export" onclick="fcrExportCSV()"><i class="fa fa-download"></i> Export CSV</button>
                        <button class="btn-fcr-print"  onclick="fcrPrint()"><i class="fa fa-print"></i> Print</button>
                    </div>
                </div>

                <?php
                // Build data array for JS
                $jsRows = [];
                foreach ($rows as $r) {
                    $period       = fcr_period($r, $months);
                    $student_name = trim(preg_replace('/\s+/', ' ', $r['student_name']));
                    $bank_label   = ($r['bank_account'] && $r['bank_account'] !== '—')
                                    ? $r['bank_account'].' ('.$r['bank_account_no'].')'
                                    : '—';
                    $jsRows[] = [
                        'payment_date' => date('d M Y', strtotime($r['payment_date'])),
                        'adm_no'       => htmlspecialchars($r['admission_no']),
                        'student'      => htmlspecialchars($student_name),
                        'class'        => htmlspecialchars($r['class']),
                        'fee_type'     => htmlspecialchars($r['fee_type']),
                        'period'       => htmlspecialchars($period),
                        'bank'         => htmlspecialchars($bank_label),
                        'amount'       => (float)$r['amount'],
                        'mode'         => htmlspecialchars($r['mode']),
                    ];
                }
                ?>

                <div class="fcr-table-wrap">
                    <table class="fcr-table" id="fcr-table">
                        <thead>
                            <tr>
                                <th style="width:36px;">#</th>
                                <th style="width:100px;">Date</th>
                                <th style="width:80px;">Adm. No.</th>
                                <th>Student Name</th>
                                <th style="width:60px;">Class</th>
                                <th style="width:120px;">Fee Type</th>
                                <th style="width:110px;">Period</th>
                                <th>Bank / Cash Account</th>
                                <th style="width:80px;">Mode</th>
                                <th class="num" style="width:110px;">Amount (<?php echo htmlspecialchars($currency_symbol); ?>)</th>
                            </tr>
                        </thead>
                        <tbody id="fcr-tbody"></tbody>
                    </table>
                </div>

                <div class="fcr-summary" id="fcr-summary">
                    <div><span>Showing:</span> <span id="fcr-showing">0</span> records</div>
                    <div><span>Total:</span> <?php echo htmlspecialchars($currency_symbol); ?> <span id="fcr-total">0.00</span></div>
                </div>

                <div class="fcr-pagination">
                    <div id="fcr-page-info"></div>
                    <div class="fcr-pages" id="fcr-pages"></div>
                </div>

            </div><!-- /results card -->
        </div>
    </section>
</div>

<script>
var fcrAllRows = <?php echo json_encode($jsRows); ?>;
var fcrPage = 1;

function fcrModeBadge(mode) {
    var m = (mode || '').toLowerCase();
    var cls = 'cash';
    if (m === 'cheque') cls = 'cheq';
    else if (m === 'transfer' || m === 'online') cls = m === 'online' ? 'onl' : 'bank';
    else if (m === 'bank') cls = 'bank';
    return '<span class="fcr-badge ' + cls + '">' + mode + '</span>';
}

function fcrFmt(n) {
    return parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function fcrFiltered() {
    var q = (document.getElementById('fcr-search').value || '').toLowerCase();
    if (!q) return fcrAllRows;
    return fcrAllRows.filter(function(r) {
        return (r.student + r.adm_no + r.class + r.fee_type + r.period + r.bank + r.mode).toLowerCase().indexOf(q) !== -1;
    });
}

function fcrChangePage(p) {
    fcrPage = p || fcrPage;
    var filtered = fcrFiltered();
    var perPage  = parseInt(document.getElementById('fcr-per-page').value) || 25;
    var total    = filtered.length;
    var pages    = Math.max(1, Math.ceil(total / perPage));
    if (fcrPage > pages) fcrPage = pages;

    var start = (fcrPage - 1) * perPage;
    var slice = filtered.slice(start, start + perPage);

    var tbody = document.getElementById('fcr-tbody');
    var html  = '';
    var subTotal = 0;
    if (slice.length === 0) {
        html = '<tr><td colspan="10" class="fcr-empty"><i class="fa fa-inbox" style="font-size:24px;display:block;margin-bottom:6px;color:#ccc;"></i>No records found.</td></tr>';
    } else {
        slice.forEach(function(r, i) {
            subTotal += parseFloat(r.amount) || 0;
            html += '<tr>';
            html += '<td style="color:#aaa;">' + (start + i + 1) + '</td>';
            html += '<td class="mono">' + r.payment_date + '</td>';
            html += '<td class="mono">' + r.adm_no + '</td>';
            html += '<td><strong>' + r.student + '</strong></td>';
            html += '<td style="text-align:center;"><span style="background:#e8f0fe;color:#1a6fc4;border-radius:10px;padding:2px 8px;font-size:11px;font-weight:700;">' + r.class + '</span></td>';
            html += '<td>' + r.fee_type + '</td>';
            html += '<td style="color:#666;">' + r.period + '</td>';
            html += '<td style="font-size:12px;color:#555;">' + r.bank + '</td>';
            html += '<td>' + fcrModeBadge(r.mode) + '</td>';
            html += '<td class="num">' + fcrFmt(r.amount) + '</td>';
            html += '</tr>';
        });
    }
    tbody.innerHTML = html;

    // Summary
    var grandTotal = 0;
    filtered.forEach(function(r){ grandTotal += parseFloat(r.amount) || 0; });
    document.getElementById('fcr-showing').textContent = total;
    document.getElementById('fcr-total').textContent   = fcrFmt(grandTotal);

    // Page info
    document.getElementById('fcr-page-info').textContent =
        total === 0 ? '' : 'Showing ' + (start+1) + '–' + Math.min(start+perPage, total) + ' of ' + total;

    // Pagination buttons
    var pHtml = '';
    if (pages > 1) {
        pHtml += '<button onclick="fcrChangePage(' + Math.max(1, fcrPage-1) + ')">&laquo;</button>';
        var lo = Math.max(1, fcrPage-2), hi = Math.min(pages, fcrPage+2);
        if (lo > 1) pHtml += '<button onclick="fcrChangePage(1)">1</button>' + (lo > 2 ? '<button disabled>…</button>' : '');
        for (var p = lo; p <= hi; p++) {
            pHtml += '<button onclick="fcrChangePage('+p+')"' + (p===fcrPage?' class="active"':'') + '>'+p+'</button>';
        }
        if (hi < pages) pHtml += (hi < pages-1 ? '<button disabled>…</button>' : '') + '<button onclick="fcrChangePage('+pages+')">'+pages+'</button>';
        pHtml += '<button onclick="fcrChangePage(' + Math.min(pages, fcrPage+1) + ')">&raquo;</button>';
    }
    document.getElementById('fcr-pages').innerHTML = pHtml;
}

function fcrExportCSV() {
    var filtered = fcrFiltered();
    var headers  = ['#','Date','Adm No','Student Name','Class','Fee Type','Period','Bank/Cash Account','Mode','Amount'];
    var lines    = [headers.join(',')];
    filtered.forEach(function(r, i) {
        lines.push([
            i+1,
            '"'+r.payment_date+'"',
            '"'+r.adm_no+'"',
            '"'+r.student+'"',
            '"'+r.class+'"',
            '"'+r.fee_type+'"',
            '"'+r.period+'"',
            '"'+r.bank+'"',
            '"'+r.mode+'"',
            r.amount
        ].join(','));
    });
    var blob = new Blob([lines.join('\n')], {type:'text/csv'});
    var a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'fee_collection_report.csv';
    a.click();
}

function fcrPrint() {
    var filtered = fcrFiltered();
    var school   = <?php echo json_encode($school_name); ?>;
    var fromDate = <?php echo json_encode(date('d M Y', strtotime($filters['start_date']))); ?>;
    var toDate   = <?php echo json_encode(date('d M Y', strtotime($filters['end_date']))); ?>;
    var grandTotal = 0;
    filtered.forEach(function(r){ grandTotal += parseFloat(r.amount)||0; });

    var html = '<!DOCTYPE html><html><head><title>Fee Collection Report</title>';
    html += '<style>';
    html += 'body{font-family:Arial,sans-serif;font-size:12px;margin:20px;}';
    html += 'h2{margin:0;font-size:16px;}';
    html += '.hdr{text-align:center;margin-bottom:14px;border-bottom:2px solid #1a6fc4;padding-bottom:8px;}';
    html += '.meta{font-size:11px;color:#555;margin-top:4px;}';
    html += 'table{width:100%;border-collapse:collapse;font-size:11px;}';
    html += 'thead tr{background:#1a6fc4;color:#fff;}';
    html += 'th,td{padding:5px 7px;border:1px solid #ddd;text-align:left;}';
    html += 'th{font-size:10px;text-transform:uppercase;}';
    html += '.num{text-align:right;}';
    html += '.tot{background:#f0f7ff;font-weight:700;}';
    html += 'tbody tr:nth-child(even){background:#f9fbff;}';
    html += '</style></head><body>';
    html += '<div class="hdr"><h2>' + school + '</h2>';
    html += '<div class="meta">Collection Report &nbsp;|&nbsp; Period: ' + fromDate + ' — ' + toDate + '</div></div>';
    html += '<table><thead><tr>';
    html += '<th>#</th><th>Date</th><th>Adm No</th><th>Student Name</th>';
    html += '<th>Class</th><th>Fee Type</th><th>Period</th><th>Bank/Cash</th><th>Mode</th><th class="num">Amount</th>';
    html += '</tr></thead><tbody>';
    filtered.forEach(function(r,i){
        html += '<tr>';
        html += '<td>'+(i+1)+'</td>';
        html += '<td>'+r.payment_date+'</td>';
        html += '<td>'+r.adm_no+'</td>';
        html += '<td><strong>'+r.student+'</strong></td>';
        html += '<td style="text-align:center;">'+r.class+'</td>';
        html += '<td>'+r.fee_type+'</td>';
        html += '<td>'+r.period+'</td>';
        html += '<td style="font-size:10px;">'+r.bank+'</td>';
        html += '<td>'+r.mode+'</td>';
        html += '<td class="num">'+parseFloat(r.amount).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',')+'</td>';
        html += '</tr>';
    });
    html += '<tr class="tot"><td colspan="9">Total ('+filtered.length+' records)</td>';
    html += '<td class="num">'+grandTotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',')+'</td></tr>';
    html += '</tbody></table>';
    html += '<div style="margin-top:30px;display:flex;justify-content:space-between;font-size:11px;">';
    html += '<div style="text-align:center;">_______________<br>Prepared By</div>';
    html += '<div style="text-align:center;">_______________<br>Reviewed By</div>';
    html += '<div style="text-align:center;">_______________<br>Approved By</div>';
    html += '</div>';
    html += '</body></html>';

    var w = window.open('','_blank','width=1000,height=700');
    w.document.write(html);
    w.document.close();
    w.onload = function(){ w.focus(); w.print(); };
}

// Init
fcrChangePage(1);
</script>
