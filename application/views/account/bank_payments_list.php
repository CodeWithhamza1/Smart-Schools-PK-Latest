<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$f = $filters;
?>
<style>
.bp-wrap { padding: 20px 24px; background: #f0f2f5; min-height: 600px; }
.bp-card { background: #fff; border: 1px solid #dde3ec; border-radius: 4px; overflow: hidden; }

/* Top bar */
.bp-topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 13px 18px; border-bottom: 1px solid #dde3ec; flex-wrap: wrap; gap: 10px;
}
.bp-topbar h2 { margin: 0; font-size: 16px; font-weight: 700; color: #1a1a2e; }
.bp-topbar-right { display: flex; align-items: center; gap: 8px; }

.btn-bp-add {
    background: #27ae60; color: #fff; border: none; border-radius: 20px;
    height: 34px; padding: 0 18px; font-size: 13px; font-weight: 600;
    cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
}
.btn-bp-add:hover { background: #219150; color: #fff; text-decoration: none; }
.btn-bp-icon {
    background: none; border: 1px solid #c8d0db; border-radius: 4px;
    height: 34px; padding: 0 10px; font-size: 13px; color: #555;
    cursor: pointer; display: inline-flex; align-items: center; gap: 5px;
}
.btn-bp-icon:hover { background: #f5f7fa; color: #222; }

/* Filter bar */
.bp-filter-bar {
    padding: 14px 18px; border-bottom: 1px solid #eef0f5;
    background: #fafbfd;
}
.bp-filter-row { display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; margin-bottom: 10px; }
.bp-filter-row:last-child { margin-bottom: 0; }
.bp-fg { display: flex; flex-direction: column; min-width: 160px; flex: 1; }
.bp-fg label { font-size: 11px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
.bp-fg select, .bp-fg input[type=text], .bp-fg input[type=date] {
    border: 1px solid #c8d0db; border-radius: 4px; height: 34px;
    padding: 0 10px; font-size: 13px; color: #333; background: #fff; width: 100%;
}
.bp-fg select:focus, .bp-fg input:focus { border-color: #1a6fc4; outline: none; }

.btn-bp-search {
    background: #1a6fc4; color: #fff; border: none; border-radius: 4px;
    height: 34px; padding: 0 18px; font-size: 13px; font-weight: 600; cursor: pointer;
    white-space: nowrap;
}
.btn-bp-search:hover { background: #145ca8; }
.btn-bp-clear {
    background: #fff; color: #555; border: 1px solid #c8d0db; border-radius: 4px;
    height: 34px; padding: 0 14px; font-size: 13px; cursor: pointer; white-space: nowrap;
}
.btn-bp-clear:hover { background: #f5f7fa; }

/* Show entries + search */
.bp-controls {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 18px; border-bottom: 1px solid #eef0f5;
    background: #fafbfd; flex-wrap: wrap; gap: 8px;
}
.bp-show { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #555; }
.bp-show select { border: 1px solid #c8d0db; border-radius: 3px; padding: 3px 6px; font-size: 13px; }
.bp-search-box { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #555; }
.bp-search-box input { border: 1px solid #c8d0db; border-radius: 3px; height: 30px; padding: 0 8px; font-size: 13px; width: 200px; }

/* Table */
table.bp-table { width: 100%; border-collapse: collapse; font-size: 13px; }
table.bp-table thead tr { background: #1a6fc4; color: #fff; }
table.bp-table thead th {
    padding: 10px 12px; font-weight: 600; font-size: 12px;
    text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; border: none;
}
table.bp-table thead th.num { text-align: right; }
table.bp-table thead th.center { text-align: center; }
table.bp-table tbody tr { border-bottom: 1px solid #edf0f5; transition: background .1s; }
table.bp-table tbody tr:hover { background: #f7f9fd; }
table.bp-table tbody td { padding: 9px 12px; color: #333; vertical-align: middle; }
table.bp-table tbody td.num { text-align: right; font-variant-numeric: tabular-nums; font-weight: 600; color: #1a3a5c; }
table.bp-table tbody td.center { text-align: center; }
table.bp-table tbody td.ref { font-size: 11px; color: #888; font-family: monospace; }
table.bp-table tbody td.vno { color: #1a6fc4; font-weight: 600; cursor: pointer; }
table.bp-table tbody td.vno:hover { text-decoration: underline; }

/* Totals row */
tr.bp-totals td {
    background: #f0f4ff; font-weight: 700;
    border-top: 2px solid #1a6fc4; border-bottom: 2px solid #1a6fc4;
    padding: 10px 12px;
}
tr.bp-totals td.num { text-align: right; }

/* Action dropdown */
.bp-action-wrap { position: relative; display: inline-block; }
.btn-bp-view {
    background: none; border: 1px solid #c8d0db; border-radius: 4px 0 0 4px;
    padding: 5px 12px; font-size: 12px; color: #1a6fc4; font-weight: 600; cursor: pointer;
}
.btn-bp-view:hover { background: #eef4fb; }
.btn-bp-caret {
    background: none; border: 1px solid #c8d0db; border-left: none; border-radius: 0 4px 4px 0;
    padding: 5px 8px; font-size: 12px; color: #555; cursor: pointer;
}
.btn-bp-caret:hover { background: #f5f7fa; }
.bp-dropdown {
    display: none; position: absolute; right: 0; top: 100%; z-index: 999;
    background: #fff; border: 1px solid #dde3ec; border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,.12); min-width: 130px;
}
.bp-dropdown.open { display: block; }
.bp-dropdown a, .bp-dropdown button {
    display: block; width: 100%; text-align: left; padding: 8px 14px;
    font-size: 13px; color: #333; text-decoration: none; background: none; border: none; cursor: pointer;
}
.bp-dropdown a:hover, .bp-dropdown button:hover { background: #f5f7fa; }
.bp-dropdown .danger { color: #c0392b; }

/* Footer */
.bp-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 18px; border-top: 1px solid #eef0f5;
    background: #fafbfd; flex-wrap: wrap; gap: 8px; font-size: 12px; color: #666;
}
.bp-pagination { display: flex; gap: 4px; }
.bp-page-btn {
    background: none; border: 1px solid #c8d0db; border-radius: 3px;
    padding: 4px 9px; font-size: 12px; color: #555; cursor: pointer; min-width: 30px; text-align: center;
}
.bp-page-btn:hover { background: #f0f4ff; color: #1a6fc4; border-color: #1a6fc4; }
.bp-page-btn.active { background: #1a6fc4; color: #fff; border-color: #1a6fc4; font-weight: 700; }
.bp-page-btn:disabled { opacity: .4; cursor: default; }

/* View modal */
.bp-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 9999;
    align-items: center; justify-content: center;
}
.bp-modal-overlay.open { display: flex; }
.bp-modal {
    background: #fff; border-radius: 6px;
    box-shadow: 0 8px 32px rgba(0,0,0,.22);
    width: 640px; max-width: 96vw; overflow: hidden;
}
.bp-modal-header {
    background: #1a6fc4; color: #fff; padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between;
    font-size: 15px; font-weight: 700;
}
.bp-modal-header button { background: none; border: none; color: #fff; font-size: 20px; cursor: pointer; }
.bp-modal-body { padding: 20px; }
.bp-modal-body table { width: 100%; border-collapse: collapse; font-size: 13px; }
.bp-modal-body table th {
    background: #1a6fc4; color: #fff; padding: 8px 12px;
    font-size: 12px; text-transform: uppercase; letter-spacing: .4px;
}
.bp-modal-body table th.r { text-align: right; }
.bp-modal-body table td { padding: 8px 12px; border-bottom: 1px solid #eef0f5; }
.bp-modal-body table td.r { text-align: right; font-weight: 600; }
.bp-modal-info { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 14px; }
.bp-modal-info .mi { font-size: 12px; color: #666; }
.bp-modal-info .mi strong { display: block; font-size: 13px; color: #222; }

@media print {
    .bp-topbar, .bp-filter-bar, .bp-controls, .bp-footer, .content-header, .no-print { display: none !important; }
    .bp-wrap { padding: 0; background: #fff; }
    .bp-card { border: none; }
    table.bp-table thead tr { background: #333 !important; -webkit-print-color-adjust: exact; }
}
</style>

<div class="content-wrapper" style="min-height: 946px; background: #f0f2f5;">
    <section class="content-header no-print">
        <h1><i class="fa fa-university"></i> Bank Payments</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li>Bank</li>
            <li class="active">Bank Payments</li>
        </ol>
    </section>

    <section class="content" style="padding: 0 10px 10px;">
        <?php if ($this->session->flashdata('msg')): ?>
            <div style="padding: 10px 0 0;"><?php echo $this->session->flashdata('msg'); ?></div>
        <?php endif; ?>

        <div class="bp-wrap">
            <div class="bp-card">

                <!-- Top bar -->
                <div class="bp-topbar no-print">
                    <h2><i class="fa fa-university" style="color:#1a6fc4; margin-right:6px;"></i> Bank Payments</h2>
                    <div class="bp-topbar-right">
                        <button class="btn-bp-icon" onclick="window.print()" title="Print"><i class="fa fa-print"></i></button>
                        <button class="btn-bp-icon" onclick="exportBpCSV()" title="Export CSV"><i class="fa fa-file-excel-o"></i></button>
                        <a href="<?php echo site_url('accounts/add_bank_payment'); ?>" class="btn-bp-add">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </div>
                </div>

                <!-- Filter bar -->
                <div class="bp-filter-bar no-print">
                    <form method="get" action="<?php echo site_url('accounts/bank_payments'); ?>">
                        <div class="bp-filter-row">
                            <div class="bp-fg">
                                <label>Bank</label>
                                <select name="bank_id">
                                    <option value="">Select</option>
                                    <?php foreach ($bank_accounts as $ba): ?>
                                        <option value="<?php echo $ba['id']; ?>" <?php echo ($f['bank_id'] == $ba['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($ba['account_name'] . ' (' . $ba['account_number'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="bp-fg">
                                <label>Nominals</label>
                                <select name="nominal_id">
                                    <option value="">Select</option>
                                    <?php foreach ($nominal_accounts as $na): ?>
                                        <option value="<?php echo $na['id']; ?>" <?php echo ($f['nominal_id'] == $na['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($na['account_name'] . ' (' . $na['account_number'] . ')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="bp-fg">
                                <label>Ref No.</label>
                                <input type="text" name="ref_no" value="<?php echo htmlspecialchars($f['ref_no'] ?? ''); ?>" placeholder="Ref No.">
                            </div>
                            <div class="bp-fg">
                                <label>Details</label>
                                <input type="text" name="details" value="<?php echo htmlspecialchars($f['details'] ?? ''); ?>" placeholder="Details">
                            </div>
                            <div class="bp-fg">
                                <label>From Date</label>
                                <input type="date" name="start_date" value="<?php echo htmlspecialchars($f['start_date'] ?? ''); ?>">
                            </div>
                            <div class="bp-fg">
                                <label>To Date</label>
                                <input type="date" name="end_date" value="<?php echo htmlspecialchars($f['end_date'] ?? ''); ?>">
                            </div>
                            <div style="display:flex;align-items:flex-end;gap:8px;">
                                <button type="submit" class="btn-bp-search"><i class="fa fa-search"></i> Search</button>
                                <a href="<?php echo site_url('accounts/bank_payments'); ?>" class="btn-bp-clear">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Controls -->
                <div class="bp-controls no-print">
                    <div class="bp-show">
                        Show
                        <select id="bp-per-page" onchange="bpRender(1)">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="999999">All</option>
                        </select>
                        entries
                    </div>
                    <div class="bp-search-box">
                        Search: <input type="text" id="bp-search" placeholder="Search…" oninput="bpRender(1)">
                    </div>
                </div>

                <!-- Table -->
                <table class="bp-table" id="bp-table">
                    <thead>
                        <tr>
                            <th class="center" style="width:36px;"><input type="checkbox" id="bp-check-all" onclick="bpToggleAll(this)"></th>
                            <th style="width:60px;">V. No.</th>
                            <th style="width:100px;">Date</th>
                            <th>Bank</th>
                            <th>Nominal Account</th>
                            <th style="width:120px;">Ref. No.</th>
                            <th>Details</th>
                            <th class="num" style="width:120px;">Amount (<?php echo htmlspecialchars($currency_symbol); ?>)</th>
                            <th class="center" style="width:90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="bp-tbody"></tbody>
                    <tfoot>
                        <tr class="bp-totals" id="bp-totals-row" style="display:none;">
                            <td colspan="7"><strong>Total</strong></td>
                            <td class="num" id="bp-total-val"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Footer -->
                <div class="bp-footer no-print">
                    <span id="bp-info">Showing 0 entries</span>
                    <div class="bp-pagination" id="bp-pagination"></div>
                </div>

            </div>
        </div>
    </section>
</div>

<!-- View modal -->
<div class="bp-modal-overlay no-print" id="bp-modal-overlay" onclick="bpCloseModalBg(event)">
    <div class="bp-modal">
        <div class="bp-modal-header">
            <span><i class="fa fa-university" style="margin-right:7px;"></i> Bank Payment Detail</span>
            <button onclick="bpCloseModal()">&times;</button>
        </div>
        <div class="bp-modal-body" id="bp-modal-body">
            <p style="text-align:center;color:#aaa;padding:30px;">Loading…</p>
        </div>
    </div>
</div>

<script>
var bpData = <?php
    $rows = [];
    $seq  = 0;
    foreach ($payments as $p) {
        $seq++;
        $rows[] = [
            'seq'          => $seq,
            'id'           => (int)$p['id'],
            'voucher_number'=> $p['voucher_number'],
            'voucher_date' => $p['voucher_date'],
            'bank_name'    => $p['bank_name'],
            'nominal_name' => $p['nominal_name'],
            'details'      => $p['details'],
            'amount'       => (float)$p['amount'],
        ];
    }
    echo json_encode($rows);
?>;

var bpPage = 1;

function bpFmt(n) {
    return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
function escH(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function bpRender(page) {
    bpPage = page || 1;
    var perPage = parseInt(document.getElementById('bp-per-page').value) || 25;
    var q = (document.getElementById('bp-search').value || '').toLowerCase();

    var filtered = bpData.filter(function(r) {
        if (!q) return true;
        return (r.bank_name.toLowerCase().indexOf(q) !== -1) ||
               (r.nominal_name.toLowerCase().indexOf(q) !== -1) ||
               (r.voucher_number.toLowerCase().indexOf(q) !== -1) ||
               ((r.details || '').toLowerCase().indexOf(q) !== -1) ||
               (r.voucher_date.indexOf(q) !== -1);
    });

    var total = filtered.length;
    var pages = Math.max(1, Math.ceil(total / perPage));
    if (bpPage > pages) bpPage = pages;
    var start = (bpPage - 1) * perPage;
    var end   = Math.min(start + perPage, total);
    var rows  = filtered.slice(start, end);

    var netTotal = 0;
    filtered.forEach(function(r) { netTotal += r.amount; });

    var html = '';
    rows.forEach(function(r, idx) {
        var d = new Date(r.voucher_date);
        var ds = !isNaN(d) ? (('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+d.getFullYear()) : r.voucher_date;
        html += '<tr>' +
            '<td class="center"><input type="checkbox" class="bp-row-check"></td>' +
            '<td class="vno" onclick="bpViewModal(' + r.id + ')">' + (start+idx+1) + '</td>' +
            '<td>' + escH(ds) + '</td>' +
            '<td><strong>' + escH(r.bank_name) + '</strong></td>' +
            '<td>' + escH(r.nominal_name) + '</td>' +
            '<td class="ref">' + escH(r.voucher_number) + '</td>' +
            '<td>' + escH(r.details || '—') + '</td>' +
            '<td class="num">' + bpFmt(r.amount) + '</td>' +
            '<td class="center">' +
                '<div class="bp-action-wrap">' +
                    '<button class="btn-bp-view" onclick="bpViewModal(' + r.id + ')">View</button>' +
                    '<button class="btn-bp-caret" onclick="bpToggleDd(this, event)"><i class="fa fa-chevron-down"></i></button>' +
                    '<div class="bp-dropdown">' +
                        '<a href="#" onclick="bpViewModal(' + r.id + ');return false;"><i class="fa fa-eye" style="margin-right:6px;"></i>View</a>' +
                        '<a href="#" onclick="bpPrintRow(' + r.id + ');return false;"><i class="fa fa-print" style="margin-right:6px;"></i>Print</a>' +
                        '<a href="<?php echo site_url('accounts/delete_bank_payment'); ?>/' + r.id + '" class="danger" onclick="return confirm(\'Delete this payment?\');"><i class="fa fa-trash" style="margin-right:6px;"></i>Delete</a>' +
                    '</div>' +
                '</div>' +
            '</td>' +
        '</tr>';
    });

    if (total === 0) {
        html = '<tr><td colspan="9" style="text-align:center;padding:40px;color:#aaa;">No data available in table</td></tr>';
    }
    document.getElementById('bp-tbody').innerHTML = html;

    var tr = document.getElementById('bp-totals-row');
    document.getElementById('bp-total-val').innerHTML = '<strong>' + bpFmt(netTotal) + '</strong>';
    tr.style.display = total > 0 ? '' : 'none';

    document.getElementById('bp-info').textContent = total === 0
        ? 'Showing 0 to 0 of 0 entries'
        : 'Showing ' + (start+1) + ' to ' + end + ' of ' + total + ' entries';

    // Pagination
    var pg = '';
    pg += '<button class="bp-page-btn" onclick="bpRender('+(bpPage-1)+')"'+(bpPage<=1?' disabled':'')+'>&#171; Prev</button>';
    var ps = Math.max(1, bpPage-2), pe = Math.min(pages, bpPage+2);
    if (ps > 1) pg += '<button class="bp-page-btn" onclick="bpRender(1)">1</button>'+(ps>2?'<span style="padding:4px 2px;color:#aaa;">…</span>':'');
    for (var p = ps; p <= pe; p++) pg += '<button class="bp-page-btn'+(p===bpPage?' active':'')+'" onclick="bpRender('+p+')">'+p+'</button>';
    if (pe < pages) pg += (pe<pages-1?'<span style="padding:4px 2px;color:#aaa;">…</span>':'')+'<button class="bp-page-btn" onclick="bpRender('+pages+')">'+pages+'</button>';
    pg += '<button class="bp-page-btn" onclick="bpRender('+(bpPage+1)+')"'+(bpPage>=pages?' disabled':'')+'>Next &#187;</button>';
    document.getElementById('bp-pagination').innerHTML = pg;
}

function bpToggleAll(cb) {
    document.querySelectorAll('.bp-row-check').forEach(function(c){ c.checked = cb.checked; });
}

function bpToggleDd(btn, e) {
    e.stopPropagation();
    document.querySelectorAll('.bp-dropdown.open').forEach(function(d){ d.classList.remove('open'); });
    var dd = btn.nextElementSibling;
    dd.classList.toggle('open');
}
document.addEventListener('click', function(){ document.querySelectorAll('.bp-dropdown.open').forEach(function(d){ d.classList.remove('open'); }); });

function bpViewModal(id) {
    document.getElementById('bp-modal-overlay').classList.add('open');
    document.getElementById('bp-modal-body').innerHTML = '<p style="text-align:center;color:#aaa;padding:30px;">Loading…</p>';
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?php echo site_url('accounts/get_bank_payment_json'); ?>/' + id, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var d = JSON.parse(xhr.responseText);
                if (d.success) {
                    var dt = new Date(d.voucher_date);
                    var ds = !isNaN(dt) ? (('0'+dt.getDate()).slice(-2)+'/'+('0'+(dt.getMonth()+1)).slice(-2)+'/'+dt.getFullYear()) : d.voucher_date;
                    document.getElementById('bp-modal-body').innerHTML =
                        '<div class="bp-modal-info">' +
                            '<div class="mi"><span>V. No.</span><strong>#' + escH(String(d.je_id)) + '</strong></div>' +
                            '<div class="mi"><span>Date</span><strong>' + escH(ds) + '</strong></div>' +
                            '<div class="mi"><span>Ref. No.</span><strong>' + escH(d.voucher_number) + '</strong></div>' +
                            '<div class="mi"><span>Bank</span><strong>' + escH(d.bank_name) + '</strong></div>' +
                        '</div>' +
                        '<table>' +
                            '<thead><tr><th>Nominal Account</th><th>Details</th><th class="r">Amount</th></tr></thead>' +
                            '<tbody>' +
                                '<tr><td>' + escH(d.nominal_name) + '</td><td>' + escH(d.details || '—') + '</td><td class="r">' + bpFmt(parseFloat(d.amount)) + '</td></tr>' +
                            '</tbody>' +
                            '<tfoot><tr><td colspan="2" style="font-weight:700;padding:8px 12px;">Total</td><td class="r" style="font-weight:700;padding:8px 12px;">' + bpFmt(parseFloat(d.amount)) + '</td></tr></tfoot>' +
                        '</table>' +
                        '<div style="margin-top:14px;text-align:right;">' +
                            '<button onclick="bpCloseModal()" style="border:1px solid #c8d0db;border-radius:4px;padding:6px 16px;font-size:13px;cursor:pointer;background:#fff;color:#555;margin-right:8px;">Close</button>' +
                            '<a href="<?php echo site_url('accounts/delete_bank_payment'); ?>/' + d.je_id + '" onclick="return confirm(\'Delete this payment?\');" style="border:none;border-radius:4px;padding:6px 16px;font-size:13px;cursor:pointer;background:#c0392b;color:#fff;text-decoration:none;">Delete</a>' +
                        '</div>';
                }
            } catch(e) {}
        }
    };
    xhr.send();
}

function bpCloseModal() { document.getElementById('bp-modal-overlay').classList.remove('open'); }
function bpCloseModalBg(e) { if (e.target === document.getElementById('bp-modal-overlay')) bpCloseModal(); }

function bpPrintRow(id) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?php echo site_url('accounts/get_bank_payment_json'); ?>/' + id, true);
    xhr.onload = function() {
        if (xhr.status !== 200) return;
        try {
            var d = JSON.parse(xhr.responseText);
            if (!d.success) return;
            var dt = new Date(d.voucher_date);
            var ds = !isNaN(dt) ? (('0'+dt.getDate()).slice(-2)+'/'+('0'+(dt.getMonth()+1)).slice(-2)+'/'+dt.getFullYear()) : d.voucher_date;
            printVoucher({
                school:   '<?php echo addslashes($school_name); ?>',
                type:     'Bank Payment',
                je_id:    d.je_id,
                date:     ds,
                bank:     d.bank_name,
                ref_no:   d.voucher_number,
                nominal:  d.nominal_name,
                details:  d.details || '—',
                amount:   parseFloat(d.amount),
                currency: '<?php echo addslashes($currency_symbol); ?>'
            });
        } catch(e) {}
    };
    xhr.send();
}

function printVoucher(v) {
    var escHv = function(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); };
    var fmtV  = function(n){ return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,','); };
    var html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>'+v.type+' Voucher</title>';
    html += '<style>';
    html += '*{margin:0;padding:0;box-sizing:border-box;}';
    html += 'body{font-family:Arial,sans-serif;font-size:12px;color:#222;padding:30px 40px;}';
    html += '.school{text-align:center;font-size:20px;font-weight:700;color:#1a6fc4;margin-bottom:4px;}';
    html += '.vtype{text-align:center;font-size:15px;font-weight:600;margin-bottom:18px;border-bottom:2px solid #1a6fc4;padding-bottom:8px;}';
    html += 'table{width:100%;border-collapse:collapse;font-size:12px;margin-bottom:20px;}';
    html += 'thead tr{background:#1a6fc4;color:#fff;}';
    html += 'th{padding:7px 10px;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.4px;}';
    html += 'th.r,td.r{text-align:right;}';
    html += 'td{padding:7px 10px;border-bottom:1px solid #dde3ec;}';
    html += 'tr.total td{border-top:2px solid #1a6fc4;font-weight:700;background:#f0f4ff;}';
    html += '.sigs{display:flex;justify-content:space-between;margin-top:50px;}';
    html += '.sig{text-align:center;min-width:120px;}';
    html += '.sig .line{border-top:1px solid #333;padding-top:6px;font-size:11px;font-weight:700;}';
    html += '.name{font-size:13px;font-weight:700;margin-bottom:2px;}';
    html += '</style></head><body>';
    html += '<div class="school">'+escHv(v.school)+'</div>';
    html += '<div class="vtype">'+escHv(v.type)+'</div>';
    html += '<table><thead><tr><th>V.ID</th><th>Date</th><th>Bank</th><th>Ref No</th><th>Nominal Account</th><th>Details</th><th class="r">Amount</th></tr></thead>';
    html += '<tbody><tr><td>'+v.je_id+'</td><td>'+escHv(v.date)+'</td><td>'+escHv(v.bank)+'</td><td>'+escHv(v.ref_no)+'</td><td>'+escHv(v.nominal)+'</td><td>'+escHv(v.details)+'</td><td class="r">'+fmtV(v.amount)+'</td></tr></tbody>';
    html += '<tfoot><tr class="total"><td colspan="6">Total</td><td class="r">'+fmtV(v.amount)+'</td></tr></tfoot></table>';
    html += '<div class="sigs">';
    html += '<div class="sig"><div class="line">Prepared By</div></div>';
    html += '<div class="sig"><div class="line">Reviewed By</div></div>';
    html += '<div class="sig"><div class="line">Approved By</div></div>';
    html += '<div class="sig"><div class="line">Received By</div></div>';
    html += '</div>';
    html += '<div style="text-align:right;font-size:10px;color:#aaa;margin-top:30px;">Printed: '+new Date().toLocaleDateString()+'</div>';
    html += '</body></html>';
    var w = window.open('','_blank','width=860,height=620');
    w.document.write(html);
    w.document.close();
    w.onload = function(){ w.focus(); w.print(); };
}

function exportBpCSV() {
    var q = (document.getElementById('bp-search').value || '').toLowerCase();
    var filtered = bpData.filter(function(r) {
        if (!q) return true;
        return (r.bank_name.toLowerCase().indexOf(q) !== -1) || (r.nominal_name.toLowerCase().indexOf(q) !== -1) || (r.voucher_number.toLowerCase().indexOf(q) !== -1);
    });
    var rows = [['#','Date','Bank','Nominal Account','Ref No','Details','Amount']];
    filtered.forEach(function(r, idx) {
        rows.push([idx+1, r.voucher_date, '"'+r.bank_name+'"', '"'+r.nominal_name+'"', '"'+r.voucher_number+'"', '"'+(r.details||'')+'"', bpFmt(r.amount)]);
    });
    var total = 0; filtered.forEach(function(r){ total += r.amount; });
    rows.push(['','','','','','"Total"', bpFmt(total)]);
    var csv = rows.map(function(r){ return r.join(','); }).join('\n');
    var a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([csv], {type:'text/csv'}));
    a.download = 'bank_payments.csv';
    a.click();
}

bpRender(1);
</script>

