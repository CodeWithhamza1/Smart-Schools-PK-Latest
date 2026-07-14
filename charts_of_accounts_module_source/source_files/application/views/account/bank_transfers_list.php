<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$f = $filters;
?>
<style>
.bt-wrap { padding: 20px 24px; background: #f0f2f5; min-height: 600px; }
.bt-card { background: #fff; border: 1px solid #dde3ec; border-radius: 4px; overflow: hidden; }

.bt-topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 13px 18px; border-bottom: 1px solid #dde3ec; flex-wrap: wrap; gap: 10px;
}
.bt-topbar h2 { margin: 0; font-size: 16px; font-weight: 700; color: #1a1a2e; }
.bt-topbar-right { display: flex; align-items: center; gap: 8px; }

.btn-bt-add {
    background: #27ae60; color: #fff; border: none; border-radius: 20px;
    height: 34px; padding: 0 18px; font-size: 13px; font-weight: 600;
    cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
}
.btn-bt-add:hover { background: #219150; color: #fff; text-decoration: none; }
.btn-bt-icon {
    background: none; border: 1px solid #c8d0db; border-radius: 4px;
    height: 34px; padding: 0 10px; font-size: 13px; color: #555;
    cursor: pointer; display: inline-flex; align-items: center; gap: 5px;
}
.btn-bt-icon:hover { background: #f5f7fa; }

.bt-filter-bar { padding: 14px 18px; border-bottom: 1px solid #eef0f5; background: #fafbfd; }
.bt-filter-row { display: flex; align-items: flex-end; gap: 12px; flex-wrap: wrap; }
.bt-fg { display: flex; flex-direction: column; min-width: 160px; flex: 1; }
.bt-fg label { font-size: 11px; font-weight: 700; color: #555; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; }
.bt-fg select, .bt-fg input[type=text], .bt-fg input[type=date] {
    border: 1px solid #c8d0db; border-radius: 4px; height: 34px;
    padding: 0 10px; font-size: 13px; color: #333; background: #fff; width: 100%;
}
.bt-fg select:focus, .bt-fg input:focus { border-color: #1a6fc4; outline: none; }
.btn-bt-search {
    background: #1a6fc4; color: #fff; border: none; border-radius: 4px;
    height: 34px; padding: 0 18px; font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap;
}
.btn-bt-search:hover { background: #145ca8; }
.btn-bt-clear {
    background: #fff; color: #555; border: 1px solid #c8d0db; border-radius: 4px;
    height: 34px; padding: 0 14px; font-size: 13px; cursor: pointer; white-space: nowrap;
    text-decoration: none; display: inline-flex; align-items: center;
}
.btn-bt-clear:hover { background: #f5f7fa; color: #333; }

.bt-controls {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 18px; border-bottom: 1px solid #eef0f5;
    background: #fafbfd; flex-wrap: wrap; gap: 8px;
}
.bt-show { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #555; }
.bt-show select { border: 1px solid #c8d0db; border-radius: 3px; padding: 3px 6px; font-size: 13px; }
.bt-search-box { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #555; }
.bt-search-box input { border: 1px solid #c8d0db; border-radius: 3px; height: 30px; padding: 0 8px; font-size: 13px; width: 200px; }

table.bt-table { width: 100%; border-collapse: collapse; font-size: 13px; }
table.bt-table thead tr { background: #1a6fc4; color: #fff; }
table.bt-table thead th {
    padding: 10px 12px; font-weight: 600; font-size: 12px;
    text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; border: none;
}
table.bt-table thead th.num  { text-align: right; }
table.bt-table thead th.center { text-align: center; }
table.bt-table tbody tr { border-bottom: 1px solid #edf0f5; transition: background .1s; }
table.bt-table tbody tr:hover { background: #f7f9fd; }
table.bt-table tbody td { padding: 9px 12px; color: #333; vertical-align: middle; }
table.bt-table tbody td.num { text-align: right; font-variant-numeric: tabular-nums; font-weight: 600; color: #1a3a5c; }
table.bt-table tbody td.center { text-align: center; }
table.bt-table tbody td.ref { font-size: 11px; color: #888; font-family: monospace; }
table.bt-table tbody td.vno { color: #1a6fc4; font-weight: 600; cursor: pointer; }
table.bt-table tbody td.vno:hover { text-decoration: underline; }

tr.bt-totals td {
    background: #f0f4ff; font-weight: 700;
    border-top: 2px solid #1a6fc4; border-bottom: 2px solid #1a6fc4; padding: 10px 12px;
}
tr.bt-totals td.num { text-align: right; }

.bt-action-wrap { position: relative; display: inline-block; }
.btn-bt-view {
    background: none; border: 1px solid #c8d0db; border-radius: 4px 0 0 4px;
    padding: 5px 12px; font-size: 12px; color: #1a6fc4; font-weight: 600; cursor: pointer;
}
.btn-bt-view:hover { background: #eef4fb; }
.btn-bt-caret {
    background: none; border: 1px solid #c8d0db; border-left: none; border-radius: 0 4px 4px 0;
    padding: 5px 8px; font-size: 12px; color: #555; cursor: pointer;
}
.btn-bt-caret:hover { background: #f5f7fa; }
.bt-dropdown {
    display: none; position: absolute; right: 0; top: 100%; z-index: 999;
    background: #fff; border: 1px solid #dde3ec; border-radius: 4px;
    box-shadow: 0 4px 12px rgba(0,0,0,.12); min-width: 130px;
}
.bt-dropdown.open { display: block; }
.bt-dropdown a, .bt-dropdown button {
    display: block; width: 100%; text-align: left; padding: 8px 14px;
    font-size: 13px; color: #333; text-decoration: none; background: none; border: none; cursor: pointer;
}
.bt-dropdown a:hover, .bt-dropdown button:hover { background: #f5f7fa; }
.bt-dropdown .danger { color: #c0392b; }

.bt-footer {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 18px; border-top: 1px solid #eef0f5;
    background: #fafbfd; flex-wrap: wrap; gap: 8px; font-size: 12px; color: #666;
}
.bt-pagination { display: flex; gap: 4px; }
.bt-page-btn {
    background: none; border: 1px solid #c8d0db; border-radius: 3px;
    padding: 4px 9px; font-size: 12px; color: #555; cursor: pointer; min-width: 30px; text-align: center;
}
.bt-page-btn:hover { background: #f0f4ff; color: #1a6fc4; border-color: #1a6fc4; }
.bt-page-btn.active { background: #1a6fc4; color: #fff; border-color: #1a6fc4; font-weight: 700; }
.bt-page-btn:disabled { opacity: .4; cursor: default; }

.bt-modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(0,0,0,.45); z-index: 9999;
    align-items: center; justify-content: center;
}
.bt-modal-overlay.open { display: flex; }
.bt-modal {
    background: #fff; border-radius: 6px;
    box-shadow: 0 8px 32px rgba(0,0,0,.22);
    width: 620px; max-width: 96vw; overflow: hidden;
}
.bt-modal-header {
    background: #8e44ad; color: #fff; padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between;
    font-size: 15px; font-weight: 700;
}
.bt-modal-header button { background: none; border: none; color: #fff; font-size: 20px; cursor: pointer; }
.bt-modal-body { padding: 20px; }
.bt-modal-body table { width: 100%; border-collapse: collapse; font-size: 13px; }
.bt-modal-body table th { background: #8e44ad; color: #fff; padding: 8px 12px; font-size: 12px; text-transform: uppercase; letter-spacing: .4px; }
.bt-modal-body table th.r { text-align: right; }
.bt-modal-body table td { padding: 8px 12px; border-bottom: 1px solid #eef0f5; }
.bt-modal-body table td.r { text-align: right; font-weight: 600; }
.bt-modal-info { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 14px; }
.bt-modal-info .mi { font-size: 12px; color: #666; }
.bt-modal-info .mi strong { display: block; font-size: 13px; color: #222; }

/* Transfer arrow badge */
.trf-arrow { display: inline-flex; align-items: center; gap: 6px; }
.trf-arrow .arr { color: #8e44ad; font-size: 14px; }

@media print {
    .bt-topbar, .bt-filter-bar, .bt-controls, .bt-footer, .content-header, .no-print { display: none !important; }
    .bt-wrap { padding: 0; background: #fff; }
    .bt-card { border: none; }
    table.bt-table thead tr { background: #333 !important; -webkit-print-color-adjust: exact; }
}
</style>

<div class="content-wrapper" style="min-height: 946px; background: #f0f2f5;">
    <section class="content-header no-print">
        <h1><i class="fa fa-exchange"></i> Transfers</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li>Bank</li>
            <li class="active">Transfers</li>
        </ol>
    </section>

    <section class="content" style="padding: 0 10px 10px;">
        <?php if ($this->session->flashdata('msg')): ?>
            <div style="padding: 10px 0 0;"><?php echo $this->session->flashdata('msg'); ?></div>
        <?php endif; ?>

        <div class="bt-wrap">
            <div class="bt-card">

                <!-- Top bar -->
                <div class="bt-topbar no-print">
                    <h2><i class="fa fa-exchange" style="color:#8e44ad; margin-right:6px;"></i> Transfers</h2>
                    <div class="bt-topbar-right">
                        <button class="btn-bt-icon" onclick="window.print()" title="Print"><i class="fa fa-print"></i></button>
                        <button class="btn-bt-icon" onclick="exportBtCSV()" title="Export CSV"><i class="fa fa-file-excel-o"></i></button>
                        <a href="<?php echo site_url('accounts/add_bank_transfer'); ?>" class="btn-bt-add">
                            <i class="fa fa-plus"></i> Add New
                        </a>
                    </div>
                </div>

                <!-- Filter bar -->
                <div class="bt-filter-bar no-print">
                    <form method="get" action="<?php echo site_url('accounts/bank_transfers'); ?>">
                        <div class="bt-filter-row">
                            <div class="bt-fg">
                                <label>From Bank</label>
                                <select name="from_bank_id">
                                    <option value="">Select</option>
                                    <?php foreach ($bank_accounts as $ba): ?>
                                        <option value="<?php echo $ba['id']; ?>" <?php echo ($f['from_bank_id'] == $ba['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($ba['account_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="bt-fg">
                                <label>To Bank</label>
                                <select name="to_bank_id">
                                    <option value="">Select</option>
                                    <?php foreach ($bank_accounts as $ba): ?>
                                        <option value="<?php echo $ba['id']; ?>" <?php echo ($f['to_bank_id'] == $ba['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($ba['account_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="bt-fg">
                                <label>Ref No.</label>
                                <input type="text" name="ref_no" value="<?php echo htmlspecialchars($f['ref_no'] ?? ''); ?>" placeholder="Ref No.">
                            </div>
                            <div class="bt-fg">
                                <label>From Date</label>
                                <input type="date" name="start_date" value="<?php echo htmlspecialchars($f['start_date'] ?? ''); ?>">
                            </div>
                            <div class="bt-fg">
                                <label>To Date</label>
                                <input type="date" name="end_date" value="<?php echo htmlspecialchars($f['end_date'] ?? ''); ?>">
                            </div>
                            <div style="display:flex; align-items:flex-end; gap:8px;">
                                <button type="submit" class="btn-bt-search"><i class="fa fa-search"></i> Search</button>
                                <a href="<?php echo site_url('accounts/bank_transfers'); ?>" class="btn-bt-clear">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Controls -->
                <div class="bt-controls no-print">
                    <div class="bt-show">
                        Show
                        <select id="bt-per-page" onchange="btRender(1)">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="999999">All</option>
                        </select>
                        entries
                    </div>
                    <div class="bt-search-box">
                        Search: <input type="text" id="bt-search" placeholder="Search…" oninput="btRender(1)">
                    </div>
                </div>

                <!-- Table -->
                <table class="bt-table">
                    <thead>
                        <tr>
                            <th class="center" style="width:36px;"><input type="checkbox" id="bt-check-all" onclick="btToggleAll(this)"></th>
                            <th style="width:60px;">V. No.</th>
                            <th style="width:100px;">Date</th>
                            <th>From Bank</th>
                            <th>To Bank</th>
                            <th style="width:120px;">Ref. No.</th>
                            <th>Details</th>
                            <th class="num" style="width:130px;">From Amount</th>
                            <th class="num" style="width:130px;">To Amount</th>
                            <th class="center" style="width:90px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="bt-tbody"></tbody>
                    <tfoot>
                        <tr class="bt-totals" id="bt-totals-row" style="display:none;">
                            <td colspan="7"><strong>Total</strong></td>
                            <td class="num" id="bt-total-val"></td>
                            <td class="num" id="bt-total-val2"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Footer -->
                <div class="bt-footer no-print">
                    <span id="bt-info">Showing 0 entries</span>
                    <div class="bt-pagination" id="bt-pagination"></div>
                </div>

            </div>
        </div>
    </section>
</div>

<!-- View modal -->
<div class="bt-modal-overlay no-print" id="bt-modal-overlay" onclick="btCloseModalBg(event)">
    <div class="bt-modal">
        <div class="bt-modal-header">
            <span><i class="fa fa-exchange" style="margin-right:7px;"></i> Transfer Detail</span>
            <button onclick="btCloseModal()">&times;</button>
        </div>
        <div class="bt-modal-body" id="bt-modal-body">
            <p style="text-align:center;color:#aaa;padding:30px;">Loading…</p>
        </div>
    </div>
</div>

<script>
var btData = <?php
    $rows = [];
    $seq  = 0;
    foreach ($transfers as $t) {
        $seq++;
        $rows[] = [
            'seq'            => $seq,
            'id'             => (int)$t['id'],
            'voucher_number' => $t['voucher_number'],
            'voucher_date'   => $t['voucher_date'],
            'from_bank_name' => $t['from_bank_name'],
            'to_bank_name'   => $t['to_bank_name'],
            'details'        => $t['details'],
            'amount'         => (float)$t['amount'],
        ];
    }
    echo json_encode($rows);
?>;

var btPage = 1;

function btFmt(n) { return n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }
function escH(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

function fmtDate(str) {
    var d = new Date(str);
    return !isNaN(d) ? ('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+d.getFullYear() : str;
}

function btRender(page) {
    btPage = page || 1;
    var perPage = parseInt(document.getElementById('bt-per-page').value) || 25;
    var q = (document.getElementById('bt-search').value || '').toLowerCase();

    var filtered = btData.filter(function(r) {
        if (!q) return true;
        return (r.from_bank_name.toLowerCase().indexOf(q) !== -1) ||
               (r.to_bank_name.toLowerCase().indexOf(q) !== -1) ||
               (r.voucher_number.toLowerCase().indexOf(q) !== -1) ||
               ((r.details || '').toLowerCase().indexOf(q) !== -1) ||
               (r.voucher_date.indexOf(q) !== -1);
    });

    var total = filtered.length;
    var pages = Math.max(1, Math.ceil(total / perPage));
    if (btPage > pages) btPage = pages;
    var start = (btPage - 1) * perPage;
    var end   = Math.min(start + perPage, total);
    var rows  = filtered.slice(start, end);

    var netTotal = 0;
    filtered.forEach(function(r) { netTotal += r.amount; });

    var html = '';
    rows.forEach(function(r, idx) {
        html += '<tr>' +
            '<td class="center"><input type="checkbox" class="bt-row-check"></td>' +
            '<td class="vno" onclick="btViewModal(' + r.id + ')">' + (start+idx+1) + '</td>' +
            '<td>' + escH(fmtDate(r.voucher_date)) + '</td>' +
            '<td><strong>' + escH(r.from_bank_name) + '</strong></td>' +
            '<td><strong>' + escH(r.to_bank_name) + '</strong></td>' +
            '<td class="ref">' + escH(r.voucher_number) + '</td>' +
            '<td>' + escH(r.details || '—') + '</td>' +
            '<td class="num">' + btFmt(r.amount) + '</td>' +
            '<td class="num">' + btFmt(r.amount) + '</td>' +
            '<td class="center">' +
                '<div class="bt-action-wrap">' +
                    '<button class="btn-bt-view" onclick="btViewModal(' + r.id + ')">View</button>' +
                    '<button class="btn-bt-caret" onclick="btToggleDd(this,event)"><i class="fa fa-chevron-down"></i></button>' +
                    '<div class="bt-dropdown">' +
                        '<a href="#" onclick="btViewModal(' + r.id + ');return false;"><i class="fa fa-eye" style="margin-right:6px;"></i>View</a>' +
                        '<a href="#" onclick="btPrintRow(' + r.id + ');return false;"><i class="fa fa-print" style="margin-right:6px;"></i>Print</a>' +
                        '<a href="<?php echo site_url('accounts/delete_bank_transfer'); ?>/' + r.id + '" class="danger" onclick="return confirm(\'Delete this transfer?\');"><i class="fa fa-trash" style="margin-right:6px;"></i>Delete</a>' +
                    '</div>' +
                '</div>' +
            '</td>' +
        '</tr>';
    });

    if (total === 0) html = '<tr><td colspan="10" style="text-align:center;padding:40px;color:#aaa;">No data available in table</td></tr>';

    document.getElementById('bt-tbody').innerHTML = html;

    var tr = document.getElementById('bt-totals-row');
    var tv = '<strong>' + btFmt(netTotal) + '</strong>';
    document.getElementById('bt-total-val').innerHTML  = tv;
    document.getElementById('bt-total-val2').innerHTML = tv;
    tr.style.display = total > 0 ? '' : 'none';

    document.getElementById('bt-info').textContent = total === 0
        ? 'Showing 0 to 0 of 0 entries'
        : 'Showing ' + (start+1) + ' to ' + end + ' of ' + total + ' entries';

    var pg = '';
    pg += '<button class="bt-page-btn" onclick="btRender('+(btPage-1)+')"'+(btPage<=1?' disabled':'')+'>&#171; Prev</button>';
    var ps = Math.max(1,btPage-2), pe = Math.min(pages,btPage+2);
    if (ps>1) pg += '<button class="bt-page-btn" onclick="btRender(1)">1</button>'+(ps>2?'<span style="padding:4px 2px;color:#aaa;">…</span>':'');
    for (var p=ps; p<=pe; p++) pg += '<button class="bt-page-btn'+(p===btPage?' active':'')+'" onclick="btRender('+p+')">'+p+'</button>';
    if (pe<pages) pg += (pe<pages-1?'<span style="padding:4px 2px;color:#aaa;">…</span>':'')+'<button class="bt-page-btn" onclick="btRender('+pages+')">'+pages+'</button>';
    pg += '<button class="bt-page-btn" onclick="btRender('+(btPage+1)+')"'+(btPage>=pages?' disabled':'')+'>Next &#187;</button>';
    document.getElementById('bt-pagination').innerHTML = pg;
}

function btToggleAll(cb) { document.querySelectorAll('.bt-row-check').forEach(function(c){ c.checked = cb.checked; }); }

function btToggleDd(btn, e) {
    e.stopPropagation();
    document.querySelectorAll('.bt-dropdown.open').forEach(function(d){ d.classList.remove('open'); });
    btn.nextElementSibling.classList.toggle('open');
}
document.addEventListener('click', function(){ document.querySelectorAll('.bt-dropdown.open').forEach(function(d){ d.classList.remove('open'); }); });

function btViewModal(id) {
    document.getElementById('bt-modal-overlay').classList.add('open');
    document.getElementById('bt-modal-body').innerHTML = '<p style="text-align:center;color:#aaa;padding:30px;">Loading…</p>';
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?php echo site_url('accounts/get_bank_transfer_json'); ?>/' + id, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var d = JSON.parse(xhr.responseText);
                if (d.success) {
                    document.getElementById('bt-modal-body').innerHTML =
                        '<div class="bt-modal-info">' +
                            '<div class="mi"><span>V. No.</span><strong>#' + escH(String(d.je_id)) + '</strong></div>' +
                            '<div class="mi"><span>Date</span><strong>' + escH(fmtDate(d.voucher_date)) + '</strong></div>' +
                            '<div class="mi"><span>Ref. No.</span><strong>' + escH(d.voucher_number) + '</strong></div>' +
                        '</div>' +
                        '<table>' +
                            '<thead><tr><th>From Bank</th><th>To Bank</th><th>Details</th><th class="r">Amount</th></tr></thead>' +
                            '<tbody><tr>' +
                                '<td><strong>' + escH(d.from_bank_name) + '</strong></td>' +
                                '<td><strong>' + escH(d.to_bank_name) + '</strong></td>' +
                                '<td>' + escH(d.details || '—') + '</td>' +
                                '<td class="r">' + btFmt(parseFloat(d.amount)) + '</td>' +
                            '</tr></tbody>' +
                            '<tfoot><tr><td colspan="3" style="font-weight:700;padding:8px 12px;">Total</td><td class="r" style="font-weight:700;padding:8px 12px;">' + btFmt(parseFloat(d.amount)) + '</td></tr></tfoot>' +
                        '</table>' +
                        '<div style="margin-top:14px;text-align:right;">' +
                            '<button onclick="btCloseModal()" style="border:1px solid #c8d0db;border-radius:4px;padding:6px 16px;font-size:13px;cursor:pointer;background:#fff;color:#555;margin-right:8px;">Close</button>' +
                            '<a href="<?php echo site_url('accounts/delete_bank_transfer'); ?>/' + d.je_id + '" onclick="return confirm(\'Delete this transfer?\');" style="border:none;border-radius:4px;padding:6px 16px;font-size:13px;cursor:pointer;background:#c0392b;color:#fff;text-decoration:none;">Delete</a>' +
                        '</div>';
                }
            } catch(e) {}
        }
    };
    xhr.send();
}

function btCloseModal() { document.getElementById('bt-modal-overlay').classList.remove('open'); }
function btCloseModalBg(e) { if (e.target === document.getElementById('bt-modal-overlay')) btCloseModal(); }
function btPrintRow(id) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?php echo site_url('accounts/get_bank_transfer_json'); ?>/' + id, true);
    xhr.onload = function() {
        if (xhr.status !== 200) return;
        try {
            var d = JSON.parse(xhr.responseText);
            if (!d.success) return;
            var dt = new Date(d.voucher_date);
            var ds = !isNaN(dt) ? (('0'+dt.getDate()).slice(-2)+'/'+('0'+(dt.getMonth()+1)).slice(-2)+'/'+dt.getFullYear()) : d.voucher_date;
            var escH = function(s){ return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); };
            var fmt  = function(n){ return parseFloat(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,','); };
            var html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>Transfer Voucher</title>';
            html += '<style>';
            html += '*{margin:0;padding:0;box-sizing:border-box;}';
            html += 'body{font-family:Arial,sans-serif;font-size:12px;color:#222;padding:30px 40px;}';
            html += '.school{text-align:center;font-size:20px;font-weight:700;color:#8e44ad;margin-bottom:4px;}';
            html += '.vtype{text-align:center;font-size:15px;font-weight:600;margin-bottom:18px;border-bottom:2px solid #8e44ad;padding-bottom:8px;}';
            html += 'table{width:100%;border-collapse:collapse;font-size:12px;margin-bottom:20px;}';
            html += 'thead tr{background:#8e44ad;color:#fff;}';
            html += 'th{padding:7px 10px;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.4px;}';
            html += 'th.r,td.r{text-align:right;}';
            html += 'td{padding:7px 10px;border-bottom:1px solid #dde3ec;}';
            html += 'tr.total td{border-top:2px solid #8e44ad;font-weight:700;background:#f9f0ff;}';
            html += '.sigs{display:flex;justify-content:space-between;margin-top:50px;}';
            html += '.sig{text-align:center;min-width:120px;}';
            html += '.sig .line{border-top:1px solid #333;padding-top:6px;font-size:11px;font-weight:700;}';
            html += '</style></head><body>';
            html += '<div class="school">'+escH('<?php echo addslashes($school_name); ?>')+'</div>';
            html += '<div class="vtype">Bank Transfer</div>';
            html += '<table><thead><tr><th>V.ID</th><th>Date</th><th>From Bank</th><th>To Bank</th><th>Ref No</th><th>Details</th><th class="r">Amount</th></tr></thead>';
            html += '<tbody><tr><td>'+d.je_id+'</td><td>'+escH(ds)+'</td><td>'+escH(d.from_bank_name)+'</td><td>'+escH(d.to_bank_name)+'</td><td>'+escH(d.voucher_number)+'</td><td>'+escH(d.details||'—')+'</td><td class="r">'+fmt(d.amount)+'</td></tr></tbody>';
            html += '<tfoot><tr class="total"><td colspan="6">Total</td><td class="r">'+fmt(d.amount)+'</td></tr></tfoot></table>';
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
        } catch(e) {}
    };
    xhr.send();
}

function exportBtCSV() {
    var q = (document.getElementById('bt-search').value || '').toLowerCase();
    var filtered = btData.filter(function(r) {
        if (!q) return true;
        return (r.from_bank_name.toLowerCase().indexOf(q)!==-1)||(r.to_bank_name.toLowerCase().indexOf(q)!==-1)||(r.voucher_number.toLowerCase().indexOf(q)!==-1);
    });
    var rows = [['#','Date','From Bank','To Bank','Ref No','Details','Amount']];
    filtered.forEach(function(r,i){ rows.push([i+1, r.voucher_date, '"'+r.from_bank_name+'"', '"'+r.to_bank_name+'"', '"'+r.voucher_number+'"', '"'+(r.details||'')+'"', btFmt(r.amount)]); });
    var total=0; filtered.forEach(function(r){ total+=r.amount; });
    rows.push(['','','','','','"Total"',btFmt(total)]);
    var csv=rows.map(function(r){ return r.join(','); }).join('\n');
    var a=document.createElement('a');
    a.href=URL.createObjectURL(new Blob([csv],{type:'text/csv'}));
    a.download='transfers.csv'; a.click();
}

btRender(1);
</script>
