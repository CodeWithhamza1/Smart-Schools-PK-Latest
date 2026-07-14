<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$f = $filters;
?>
<style>
.br-wrap { padding: 20px 24px; background: #f0f2f5; min-height: 600px; }
.br-card { background: #fff; border: 1px solid #dde3ec; border-radius: 4px; overflow: hidden; }

/* Top bar */
.br-topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 13px 18px; border-bottom: 1px solid #dde3ec; flex-wrap: wrap; gap: 10px;
}
.br-topbar h2 { margin: 0; font-size: 16px; font-weight: 700; color: #1a1a2e; }
.br-topbar-right { display: flex; align-items: center; gap: 8px; }

.btn-br-add {
    background: #27ae60; color: #fff; border: none; border-radius: 20px;
    height: 34px; padding: 0 18px; font-size: 13px; font-weight: 600;
    cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
}
.btn-br-add:hover { background: #219150; color: #fff; text-decoration: none; }
.btn-br-icon {
    background: none; border: 1px solid #c8d0db; border-radius: 4px;
    height: 34px; padding: 0 10px; font-size: 13px; color: #555;
    cursor: pointer; display: inline-flex; align-items: center; gap: 5px;
}
.btn-br-icon:hover { background: #f5f7fa; color: #222; }

/* Filter bar */
.br-filter-bar { padding: 14px 18px; border-bottom: 1px solid #eef0f5; background: #fafbfd; }
.br-filter-row { display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; }
.br-fg { display: flex; flex-direction: column; min-width: 160px; flex: 1; }
.br-fg label { font-size: 11px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
.br-fg select, .br-fg input[type=text], .br-fg input[type=date] {
    border: 1px solid #c8d0db; border-radius: 4px; height: 34px;
    padding: 0 10px; font-size: 13px; color: #333; background: #fff; width: 100%;
}
.br-fg select:focus, .br-fg input:focus { border-color: #1a6fc4; outline: none; }
.btn-br-search {
    background: #1a6fc4; color: #fff; border: none; border-radius: 4px;
    height: 34px; padding: 0 18px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap;
}
.btn-br-search:hover { background: #145ca8; }
.btn-br-clear {
    background: #fff; color: #555; border: 1px solid #c8d0db; border-radius: 4px;
    height: 34px; padding: 0 14px; font-size: 13px; cursor: pointer; white-space: nowrap; text-decoration: none;
    display: inline-flex; align-items: center;
}
.btn-br-clear:hover { background: #f5f7fa; color: #333; }

/* Controls */
.br-controls {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 18px; border-bottom: 1px solid #eef0f5;
    background: #fafbfd; flex-wrap: wrap; gap: 8px;
}
.br-show { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #555; }
.br-show select { border: 1px solid #c8d0db; border-radius: 3px; padding: 3px 6px; font-size: 13px; }
.br-search-box { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #555; }
.br-search-box input { border: 1px solid #c8d0db; border-radius: 3px; height: 30px; padding: 0 8px; font-size: 13px; width: 200px; }

/* Table */
table.br-table { width: 100%; border-collapse: collapse; font-size: 13px; }
table.br-table thead tr { background: #1a6fc4; color: #fff; }
table.br-table thead th {
    padding: 10px 12px; font-weight: 600; font-size: 12px;
    text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; border: none;
}
table.br-table thead th.num { text-align: right; }
table.br-table thead th.center { text-align: center; }
table.br-table tbody tr { border-bottom: 1px solid #edf0f5; transition: background .1s; }
table.br-table tbody tr:hover { background: #f7f9fd; }
table.br-table tbody td { padding: 9px 12px; color: #333; vertical-align: middle; }
table.br-table tbody td.num { text-align: right; font-variant-numeric: tabular-nums; font-weight: 600; color: #1a5f1a; }
table.br-table tbody td.center { text-align: center; }
table.br-table tbody td.ref { font-size: 11px; color: #888; font-family: monospace; }
table.br-table tbody td.vno { color: #1a6fc4; font-weight: 600; cursor: pointer; }
table.br-table tbody td.vno:hover { text-decoration: underline; }

/* Totals row */
tr.br-totals td {
    background: #f0fff4; font-weight: 700;
    border-top: 2px solid #27ae60; border-bottom: 2px solid #27ae60;
    padding: 10px 12px;
}
tr.br-totals td.num { text-align: right; }

/* Action dropdown */
.br-action-wrap { position: relative; display: inline-block; }
.btn-br-view {
    background: none; border: 1px solid #c8d0db; border-radius: 4px 0 0 4px;
    padding: 5px 12px; font-size: 12px; color: #1a6fc4; font-weight: 600; cursor: pointer;
}
.btn-br-view:hover { background: #eef4fb; }
.btn-br-caret {
    background: none; border: 1px solid #c8d0db; border-left: none; border-radius: 0 4px 4px 0;
    padding: 5px 8px; font-size: 12px; color: #555; cursor: pointer;
}
.btn-br-caret:hover { background: #f5f7fa; }
.br-dropdown {
    display: none; position: absolute; right: 0; top: 100%; z-index: 999;
    background: #fff; border: 1px solid #dde3ec; border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,.12); min-width: 130px;
}
.br-dropdown.open { display: block; }
.br-dropdown a, .br-dropdown button {
    display: block; width: 100%; text-align: left; padding: 8px 14px;
    font-size: 13px; color: #333; text-decoration: none; background: none; border: none; cursor: pointer;
}
.br-dropdown a:hover, .br-dropdown button:hover { background: #f5f7fa; }
.br-dropdown .danger { color: #c0392b; }

/* Footer */
.br-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 18px; border-top: 1px solid #eef0f5;
    background: #fafbfd; flex-wrap: wrap; gap: 8px; font-size: 12px; color: #666;
}
.br-pagination { display: flex; gap: 4px; }
.br-page-btn {
    background: none; border: 1px solid #c8d0db; border-radius: 3px;
    padding: 4px 9px; font-size: 12px; color: #555; cursor: pointer; min-width: 30px; text-align: center;
}
.br-page-btn:hover { background: #f0f4ff; color: #1a6fc4; border-color: #1a6fc4; }
.br-page-btn.active { background: #1a6fc4; color: #fff; border-color: #1a6fc4; font-weight: 700; }
.br-page-btn:disabled { opacity: .4; cursor: default; }

/* View modal */
.br-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 9999;
    align-items: center; justify-content: center;
}
.br-modal-overlay.open { display: flex; }
.br-modal {
    background: #fff; border-radius: 6px;
    box-shadow: 0 8px 32px rgba(0,0,0,.22);
    width: 640px; max-width: 96vw; overflow: hidden;
}
.br-modal-header {
    background: #27ae60; color: #fff; padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between;
    font-size: 15px; font-weight: 700;
}
.br-modal-header button { background: none; border: none; color: #fff; font-size: 20px; cursor: pointer; }
.br-modal-body { padding: 20px; }
.br-modal-body table { width: 100%; border-collapse: collapse; font-size: 13px; }
.br-modal-body table th { background: #27ae60; color: #fff; padding: 8px 12px; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; }
.br-modal-body table th.r { text-align: right; }
.br-modal-body table td { padding: 8px 12px; border-bottom: 1px solid #eef0f5; }
.br-modal-body table td.r { text-align: right; font-weight: 600; color: #1a5f1a; }
.br-modal-info { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 14px; }
.br-modal-info .mi { font-size: 12px; color: #666; }
.br-modal-info .mi strong { display: block; font-size: 13px; color: #222; }

@media print {
    .br-topbar, .br-filter-bar, .br-controls, .br-footer, .content-header, .no-print { display: none !important; }
    .br-wrap { padding: 0; background: #fff; }
    .br-card { border: none; }
    table.br-table thead tr { background: #333 !important; -webkit-print-color-adjust: exact; }
}
</style>

<div class="content-wrapper" style="min-height: 946px; background: #f0f2f5;">
    <section class="content-header no-print">
        <h1><i class="fa fa-arrow-down"></i> Bank Receipts</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li>Bank</li>
            <li class="active">Bank Receipts</li>
        </ol>
    </section>

    <section class="content" style="padding: 0 10px 10px;">
        <?php if ($this->session->flashdata('msg')): ?>
            <div style="padding: 10px 0 0;"><?php echo $this->session->flashdata('msg'); ?></div>
        <?php endif; ?>

        <div class="br-wrap">
            <div class="br-card">

                <!-- Top bar -->
                <div class="br-topbar no-print">
                    <h2><i class="fa fa-arrow-circle-down" style="color:#27ae60; margin-right:6px;"></i> Bank Receipts</h2>
                    <div class="br-topbar-right">
                        <button class="btn-br-icon" onclick="window.print()" title="Print"><i class="fa fa-print"></i></button>
                        <button class="btn-br-icon" onclick="exportBrCSV()" title="Export CSV"><i class="fa fa-file-excel-o"></i></button>
                        <a href="<?php echo site_url('accounts/add_bank_receipt'); ?>" class="btn-br-add">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </div>
                </div>

                <!-- Filter bar -->
                <div class="br-filter-bar no-print">
                    <form method="get" action="<?php echo site_url('accounts/bank_receipts'); ?>">
                        <div class="br-filter-row">
                            <div class="br-fg">
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
                            <div class="br-fg">
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
                            <div class="br-fg">
                                <label>Ref No.</label>
                                <input type="text" name="ref_no" value="<?php echo htmlspecialchars($f['ref_no'] ?? ''); ?>" placeholder="Ref No.">
                            </div>
                            <div class="br-fg">
                                <label>Details</label>
                                <input type="text" name="details" value="<?php echo htmlspecialchars($f['details'] ?? ''); ?>" placeholder="Details">
                            </div>
                            <div class="br-fg">
                                <label>From Date</label>
                                <input type="date" name="start_date" value="<?php echo htmlspecialchars($f['start_date'] ?? ''); ?>">
                            </div>
                            <div class="br-fg">
                                <label>To Date</label>
                                <input type="date" name="end_date" value="<?php echo htmlspecialchars($f['end_date'] ?? ''); ?>">
                            </div>
                            <div style="display:flex; align-items:flex-end; gap:8px;">
                                <button type="submit" class="btn-br-search"><i class="fa fa-search"></i> Search</button>
                                <a href="<?php echo site_url('accounts/bank_receipts'); ?>" class="btn-br-clear">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Controls -->
                <div class="br-controls no-print">
                    <div class="br-show">
                        Show
                        <select id="br-per-page" onchange="brRender(1)">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="999999">All</option>
                        </select>
                        entries
                    </div>
                    <div class="br-search-box">
                        Search: <input type="text" id="br-search" placeholder="Search…" oninput="brRender(1)">
                    </div>
                </div>

                <!-- Table -->
                <table class="br-table" id="br-table">
                    <thead>
                        <tr>
                            <th class="center" style="width:36px;"><input type="checkbox" id="br-check-all" onclick="brToggleAll(this)"></th>
                            <th style="width:60px;">V. No.</th>
                            <th style="width:100px;">Date</th>
                            <th>Bank</th>
                            <th>Nominal Account</th>
                            <th style="width:120px;">Ref. No.</th>
                            <th>Details</th>
                            <th class="num" style="width:130px;">Amount (<?php echo htmlspecialchars($currency_symbol); ?>)</th>
                            <th class="center" style="width:90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="br-tbody"></tbody>
                    <tfoot>
                        <tr class="br-totals" id="br-totals-row" style="display:none;">
                            <td colspan="7"><strong>Total</strong></td>
                            <td class="num" id="br-total-val"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Footer -->
                <div class="br-footer no-print">
                    <span id="br-info">Showing 0 entries</span>
                    <div class="br-pagination" id="br-pagination"></div>
                </div>

            </div>
        </div>
    </section>
</div>

<!-- View modal -->
<div class="br-modal-overlay no-print" id="br-modal-overlay" onclick="brCloseModalBg(event)">
    <div class="br-modal">
        <div class="br-modal-header">
            <span><i class="fa fa-arrow-circle-down" style="margin-right:7px;"></i> Bank Receipt Detail</span>
            <button onclick="brCloseModal()">&times;</button>
        </div>
        <div class="br-modal-body" id="br-modal-body">
            <p style="text-align:center;color:#aaa;padding:30px;">Loading…</p>
        </div>
    </div>
</div>

<script>
var brData = <?php
    $rows = [];
    $seq  = 0;
    foreach ($receipts as $r) {
        $seq++;
        $rows[] = [
            'seq'           => $seq,
            'id'            => (int)$r['id'],
            'voucher_number'=> $r['voucher_number'],
            'voucher_date'  => $r['voucher_date'],
            'bank_name'     => $r['bank_name'],
            'nominal_name'  => $r['nominal_name'],
            'details'       => $r['details'],
            'amount'        => (float)$r['amount'],
        ];
    }
    echo json_encode($rows);
?>;

var brPage = 1;

function brFmt(n) {
    return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}
function escH(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function brRender(page) {
    brPage = page || 1;
    var perPage = parseInt(document.getElementById('br-per-page').value) || 25;
    var q = (document.getElementById('br-search').value || '').toLowerCase();

    var filtered = brData.filter(function(r) {
        if (!q) return true;
        return (r.bank_name.toLowerCase().indexOf(q) !== -1) ||
               (r.nominal_name.toLowerCase().indexOf(q) !== -1) ||
               (r.voucher_number.toLowerCase().indexOf(q) !== -1) ||
               ((r.details || '').toLowerCase().indexOf(q) !== -1) ||
               (r.voucher_date.indexOf(q) !== -1);
    });

    var total = filtered.length;
    var pages = Math.max(1, Math.ceil(total / perPage));
    if (brPage > pages) brPage = pages;
    var start = (brPage - 1) * perPage;
    var end   = Math.min(start + perPage, total);
    var rows  = filtered.slice(start, end);

    var netTotal = 0;
    filtered.forEach(function(r) { netTotal += r.amount; });

    var html = '';
    rows.forEach(function(r, idx) {
        var d  = new Date(r.voucher_date);
        var ds = !isNaN(d) ? (('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+d.getFullYear()) : r.voucher_date;
        html += '<tr>' +
            '<td class="center"><input type="checkbox" class="br-row-check"></td>' +
            '<td class="vno" onclick="brViewModal(' + r.id + ')">' + (start+idx+1) + '</td>' +
            '<td>' + escH(ds) + '</td>' +
            '<td><strong>' + escH(r.bank_name) + '</strong></td>' +
            '<td>' + escH(r.nominal_name) + '</td>' +
            '<td class="ref">' + escH(r.voucher_number) + '</td>' +
            '<td>' + escH(r.details || '—') + '</td>' +
            '<td class="num">' + brFmt(r.amount) + '</td>' +
            '<td class="center">' +
                '<div class="br-action-wrap">' +
                    '<button class="btn-br-view" onclick="brViewModal(' + r.id + ')">View</button>' +
                    '<button class="btn-br-caret" onclick="brToggleDd(this, event)"><i class="fa fa-chevron-down"></i></button>' +
                    '<div class="br-dropdown">' +
                        '<a href="#" onclick="brViewModal(' + r.id + ');return false;"><i class="fa fa-eye" style="margin-right:6px;"></i>View</a>' +
                        '<a href="#" onclick="brPrintRow(' + r.id + ');return false;"><i class="fa fa-print" style="margin-right:6px;"></i>Print</a>' +
                        '<a href="<?php echo site_url('accounts/delete_bank_receipt'); ?>/' + r.id + '" class="danger" onclick="return confirm(\'Delete this receipt?\');"><i class="fa fa-trash" style="margin-right:6px;"></i>Delete</a>' +
                    '</div>' +
                '</div>' +
            '</td>' +
        '</tr>';
    });

    if (total === 0) {
        html = '<tr><td colspan="9" style="text-align:center;padding:40px;color:#aaa;">No data available in table</td></tr>';
    }
    document.getElementById('br-tbody').innerHTML = html;

    var tr = document.getElementById('br-totals-row');
    document.getElementById('br-total-val').innerHTML = '<strong>' + brFmt(netTotal) + '</strong>';
    tr.style.display = total > 0 ? '' : 'none';

    document.getElementById('br-info').textContent = total === 0
        ? 'Showing 0 to 0 of 0 entries'
        : 'Showing ' + (start+1) + ' to ' + end + ' of ' + total + ' entries';

    var pg = '';
    pg += '<button class="br-page-btn" onclick="brRender('+(brPage-1)+')"'+(brPage<=1?' disabled':'')+'>&#171; Prev</button>';
    var ps = Math.max(1, brPage-2), pe = Math.min(pages, brPage+2);
    if (ps > 1) pg += '<button class="br-page-btn" onclick="brRender(1)">1</button>'+(ps>2?'<span style="padding:4px 2px;color:#aaa;">…</span>':'');
    for (var p = ps; p <= pe; p++) pg += '<button class="br-page-btn'+(p===brPage?' active':'')+'" onclick="brRender('+p+')">'+p+'</button>';
    if (pe < pages) pg += (pe<pages-1?'<span style="padding:4px 2px;color:#aaa;">…</span>':'')+'<button class="br-page-btn" onclick="brRender('+pages+')">'+pages+'</button>';
    pg += '<button class="br-page-btn" onclick="brRender('+(brPage+1)+')"'+(brPage>=pages?' disabled':'')+'>Next &#187;</button>';
    document.getElementById('br-pagination').innerHTML = pg;
}

function brToggleAll(cb) {
    document.querySelectorAll('.br-row-check').forEach(function(c){ c.checked = cb.checked; });
}

function brToggleDd(btn, e) {
    e.stopPropagation();
    document.querySelectorAll('.br-dropdown.open').forEach(function(d){ d.classList.remove('open'); });
    btn.nextElementSibling.classList.toggle('open');
}
document.addEventListener('click', function(){ document.querySelectorAll('.br-dropdown.open').forEach(function(d){ d.classList.remove('open'); }); });

function brViewModal(id) {
    document.getElementById('br-modal-overlay').classList.add('open');
    document.getElementById('br-modal-body').innerHTML = '<p style="text-align:center;color:#aaa;padding:30px;">Loading…</p>';
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?php echo site_url('accounts/get_bank_receipt_json'); ?>/' + id, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var d = JSON.parse(xhr.responseText);
                if (d.success) {
                    var dt = new Date(d.voucher_date);
                    var ds = !isNaN(dt) ? (('0'+dt.getDate()).slice(-2)+'/'+('0'+(dt.getMonth()+1)).slice(-2)+'/'+dt.getFullYear()) : d.voucher_date;
                    document.getElementById('br-modal-body').innerHTML =
                        '<div class="br-modal-info">' +
                            '<div class="mi"><span>V. No.</span><strong>#' + escH(String(d.je_id)) + '</strong></div>' +
                            '<div class="mi"><span>Date</span><strong>' + escH(ds) + '</strong></div>' +
                            '<div class="mi"><span>Ref. No.</span><strong>' + escH(d.voucher_number) + '</strong></div>' +
                            '<div class="mi"><span>Bank</span><strong>' + escH(d.bank_name) + '</strong></div>' +
                        '</div>' +
                        '<table>' +
                            '<thead><tr><th>Nominal Account</th><th>Details</th><th class="r">Amount</th></tr></thead>' +
                            '<tbody><tr><td>' + escH(d.nominal_name) + '</td><td>' + escH(d.details || '—') + '</td><td class="r">' + brFmt(parseFloat(d.amount)) + '</td></tr></tbody>' +
                            '<tfoot><tr><td colspan="2" style="font-weight:700;padding:8px 12px;">Total</td><td class="r" style="font-weight:700;padding:8px 12px;color:#1a5f1a;">' + brFmt(parseFloat(d.amount)) + '</td></tr></tfoot>' +
                        '</table>' +
                        '<div style="margin-top:14px;text-align:right;">' +
                            '<button onclick="brCloseModal()" style="border:1px solid #c8d0db;border-radius:4px;padding:6px 16px;font-size:13px;cursor:pointer;background:#fff;color:#555;margin-right:8px;">Close</button>' +
                            '<a href="<?php echo site_url('accounts/delete_bank_receipt'); ?>/' + d.je_id + '" onclick="return confirm(\'Delete this receipt?\');" style="border:none;border-radius:4px;padding:6px 16px;font-size:13px;cursor:pointer;background:#c0392b;color:#fff;text-decoration:none;">Delete</a>' +
                        '</div>';
                }
            } catch(e) {}
        }
    };
    xhr.send();
}

function brCloseModal() { document.getElementById('br-modal-overlay').classList.remove('open'); }
function brCloseModalBg(e) { if (e.target === document.getElementById('br-modal-overlay')) brCloseModal(); }

function brPrintRow(id) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?php echo site_url('accounts/get_bank_receipt_json'); ?>/' + id, true);
    xhr.onload = function() {
        if (xhr.status !== 200) return;
        try {
            var d = JSON.parse(xhr.responseText);
            if (!d.success) return;
            var dt = new Date(d.voucher_date);
            var ds = !isNaN(dt) ? (('0'+dt.getDate()).slice(-2)+'/'+('0'+(dt.getMonth()+1)).slice(-2)+'/'+dt.getFullYear()) : d.voucher_date;
            printVoucher({
                school:   '<?php echo addslashes($school_name); ?>',
                type:     'Bank Receipt',
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
    var escH = function(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); };
    var fmt  = function(n){ return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,','); };
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
    html += '</style></head><body>';
    html += '<div class="school">'+escH(v.school)+'</div>';
    html += '<div class="vtype">'+escH(v.type)+'</div>';
    html += '<table><thead><tr><th>V.ID</th><th>Date</th><th>Bank</th><th>Ref No</th><th>Nominal Account</th><th>Details</th><th class="r">Amount</th></tr></thead>';
    html += '<tbody><tr><td>'+v.je_id+'</td><td>'+escH(v.date)+'</td><td>'+escH(v.bank)+'</td><td>'+escH(v.ref_no)+'</td><td>'+escH(v.nominal)+'</td><td>'+escH(v.details)+'</td><td class="r">'+fmt(v.amount)+'</td></tr></tbody>';
    html += '<tfoot><tr class="total"><td colspan="6">Total</td><td class="r">'+fmt(v.amount)+'</td></tr></tfoot></table>';
    html += '<div class="sigs">';
    html += '<div class="sig"><div class="line">Prepared By</div></div>';
    html += '<div class="sig"><div class="line">Reviewed By</div></div>';
    html += '<div class="sig"><div class="line">Approved By</div></div>';
    html += '<div class="sig"><div class="line">Received By</div></div>';
    html += '</div>';
    html += '<div style="text-align:right;font-size:10px;color:#aaa;margin-top:30px;">Printed: '+new Date().toLocaleDateString()+'</div>';
    html += '</body></html>';
    var w = window.open('','_blank','width=860,height=620');
    w.document.write(html); w.document.close();
    w.onload = function(){ w.focus(); w.print(); };
}

function exportBrCSV() {
    var q = (document.getElementById('br-search').value || '').toLowerCase();
    var filtered = brData.filter(function(r) {
        if (!q) return true;
        return (r.bank_name.toLowerCase().indexOf(q) !== -1) || (r.nominal_name.toLowerCase().indexOf(q) !== -1) || (r.voucher_number.toLowerCase().indexOf(q) !== -1);
    });
    var rows = [['#','Date','Bank','Nominal Account','Ref No','Details','Amount']];
    filtered.forEach(function(r, idx) {
        rows.push([idx+1, r.voucher_date, '"'+r.bank_name+'"', '"'+r.nominal_name+'"', '"'+r.voucher_number+'"', '"'+(r.details||'')+'"', brFmt(r.amount)]);
    });
    var total = 0; filtered.forEach(function(r){ total += r.amount; });
    rows.push(['','','','','','"Total"', brFmt(total)]);
    var csv = rows.map(function(r){ return r.join(','); }).join('\n');
    var a = document.createElement('a');
    a.href = URL.createObjectURL(new Blob([csv], {type:'text/csv'}));
    a.download = 'bank_receipts.csv';
    a.click();
}

brRender(1);
</script>

