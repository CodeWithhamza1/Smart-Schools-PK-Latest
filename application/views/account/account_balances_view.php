<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>
/* ── Account Balances ───────────────────────────────────────────── */
.ab-wrap {
    padding: 22px 26px;
    background: #f0f2f5;
    min-height: 600px;
}
.ab-card {
    background: #fff;
    border: 1px solid #dde3ec;
    border-radius: 4px;
    overflow: hidden;
}

/* Top bar */
.ab-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 20px;
    border-bottom: 1px solid #dde3ec;
    flex-wrap: wrap;
    gap: 10px;
}
.ab-topbar-left {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}
.ab-title {
    font-size: 16px;
    font-weight: 700;
    color: #1a1a2e;
    margin: 0;
}
.ab-asof-form {
    display: flex;
    align-items: center;
    gap: 8px;
}
.ab-asof-form label {
    font-size: 12px;
    font-weight: 600;
    color: #555;
    white-space: nowrap;
}
.ab-asof-form input[type=date] {
    border: 1px solid #c8d0db;
    border-radius: 4px;
    height: 32px;
    padding: 0 8px;
    font-size: 13px;
    color: #333;
}
.ab-asof-form button {
    background: #1a6fc4;
    color: #fff;
    border: none;
    border-radius: 4px;
    height: 32px;
    padding: 0 14px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}
.ab-asof-form button:hover { background: #145ca8; }

.ab-topbar-right {
    display: flex;
    align-items: center;
    gap: 8px;
}
.btn-ab-new {
    background: #27ae60;
    color: #fff;
    border: none;
    border-radius: 4px;
    height: 34px;
    padding: 0 16px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-ab-new:hover { background: #219150; color: #fff; text-decoration: none; }
.btn-ab-icon {
    background: none;
    border: 1px solid #c8d0db;
    border-radius: 4px;
    height: 34px;
    padding: 0 10px;
    font-size: 13px;
    color: #555;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.btn-ab-icon:hover { background: #f5f7fa; color: #222; }

/* Controls bar */
.ab-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    border-bottom: 1px solid #eef0f5;
    flex-wrap: wrap;
    gap: 8px;
    background: #fafbfd;
}
.ab-show-entries {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #555;
}
.ab-show-entries select {
    border: 1px solid #c8d0db;
    border-radius: 3px;
    padding: 3px 6px;
    font-size: 13px;
    color: #333;
    cursor: pointer;
}
.ab-search {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #555;
}
.ab-search input {
    border: 1px solid #c8d0db;
    border-radius: 3px;
    height: 30px;
    padding: 0 8px;
    font-size: 13px;
    color: #333;
    width: 200px;
}

/* Table */
table.ab-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
table.ab-table thead tr {
    background: #1a6fc4;
    color: #fff;
}
table.ab-table thead th {
    padding: 10px 14px;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .4px;
    white-space: nowrap;
    border: none;
}
table.ab-table thead th.num { text-align: right; }

table.ab-table tbody tr {
    border-bottom: 1px solid #edf0f5;
    transition: background .1s;
}
table.ab-table tbody tr:hover { background: #f7f9fd; }
table.ab-table tbody td {
    padding: 9px 14px;
    color: #333;
    vertical-align: middle;
}
table.ab-table tbody td.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}
table.ab-table tbody td.positive { color: #1a5f1a; }
table.ab-table tbody td.negative { color: #b30000; }
table.ab-table tbody td.zero     { color: #aaa; }

/* Group header row */
tr.ab-group-hdr td {
    background: #eef4ff;
    font-weight: 700;
    font-size: 12px;
    color: #1a3a5c;
    padding: 7px 14px;
    letter-spacing: .3px;
    text-transform: uppercase;
    border-top: 2px solid #c5d8f0;
    border-bottom: 1px solid #c5d8f0;
}

/* Total row */
tr.ab-total td {
    background: #f0f4ff;
    font-weight: 700;
    border-top: 2px solid #1a6fc4;
    border-bottom: 2px solid #1a6fc4;
    padding: 10px 14px;
}
tr.ab-total td.num { text-align: right; }

/* Footer bar */
.ab-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    border-top: 1px solid #eef0f5;
    background: #fafbfd;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 12px;
    color: #666;
}
.ab-pagination {
    display: flex;
    gap: 4px;
}
.ab-page-btn {
    background: none;
    border: 1px solid #c8d0db;
    border-radius: 3px;
    padding: 4px 9px;
    font-size: 12px;
    color: #555;
    cursor: pointer;
    min-width: 30px;
    text-align: center;
}
.ab-page-btn:hover  { background: #f0f4ff; color: #1a6fc4; border-color: #1a6fc4; }
.ab-page-btn.active { background: #1a6fc4; color: #fff; border-color: #1a6fc4; font-weight: 700; }
.ab-page-btn:disabled { opacity: .4; cursor: default; }

@media print {
    .ab-topbar, .ab-controls, .ab-footer, .content-header, .no-print { display: none !important; }
    .ab-wrap { padding: 0; background: #fff; }
    .ab-card { border: none; }
    table.ab-table thead tr { background: #333 !important; -webkit-print-color-adjust: exact; }
    tr.ab-group-hdr td { background: #eee !important; -webkit-print-color-adjust: exact; }
}
</style>

<div class="content-wrapper" style="min-height:946px; background:#f0f2f5;">
    <section class="content-header no-print">
        <h1><i class="fa fa-bank"></i> Account Balances</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?php echo base_url(); ?>accounts/all_accounts">Chart of Accounts</a></li>
            <li class="active">Account Balances</li>
        </ol>
    </section>

    <section class="content" style="padding:0 10px 10px;">
        <div class="ab-wrap">
            <div class="ab-card">

                <!-- Top bar -->
                <div class="ab-topbar">
                    <div class="ab-topbar-left">
                        <p class="ab-title"><i class="fa fa-bank" style="color:#1a6fc4;margin-right:6px;"></i> Account Balances &mdash; Bank &amp; Cash</p>
                        <form method="get" action="<?php echo site_url('accounts/account_balances'); ?>" class="ab-asof-form no-print">
                            <label>As Of:</label>
                            <input type="date" name="as_of" value="<?php echo htmlspecialchars($as_of); ?>">
                            <button type="submit">Go</button>
                        </form>
                    </div>
                    <div class="ab-topbar-right no-print">
                        <button class="btn-ab-icon" onclick="window.print()" title="Print">
                            <i class="fa fa-print"></i>
                        </button>
                        <button class="btn-ab-icon" onclick="exportAbCSV()" title="Export CSV">
                            <i class="fa fa-file-excel-o"></i>
                        </button>
                        <a href="<?php echo base_url(); ?>accounts/all_accounts" class="btn-ab-new">
                            <i class="fa fa-plus"></i> New Account
                        </a>
                    </div>
                </div>

                <!-- Controls -->
                <div class="ab-controls no-print">
                    <div class="ab-show-entries">
                        Show
                        <select id="ab-per-page" onchange="abRender(1)">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="999999">All</option>
                        </select>
                        entries
                    </div>
                    <div class="ab-search">
                        Search: <input type="text" id="ab-search" placeholder="Account name or code…" oninput="abRender(1)">
                    </div>
                </div>

                <!-- Table -->
                <table class="ab-table" id="ab-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Account Name</th>
                            <th>Code</th>
                            <th>Group</th>
                            <th>Reconcile Date</th>
                            <th class="num">Balance (<?php echo htmlspecialchars($currency_symbol); ?>)</th>
                        </tr>
                    </thead>
                    <tbody id="ab-tbody">
                        <!-- populated by JS -->
                    </tbody>
                    <tfoot>
                        <tr class="ab-total" id="ab-total-row" style="display:none;">
                            <td colspan="5"><strong>Total</strong></td>
                            <td class="num" id="ab-total-val"></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Footer / Pagination -->
                <div class="ab-footer no-print">
                    <span id="ab-info">Showing 0 to 0 of 0 entries</span>
                    <div class="ab-pagination" id="ab-pagination"></div>
                </div>

            </div><!-- /.ab-card -->
        </div><!-- /.ab-wrap -->
    </section>
</div>

<script>
var abData = <?php
    $rows = [];
    foreach ($balances as $b) {
        $rows[] = [
            'id'              => (int)$b['id'],
            'account_name'    => $b['account_name'],
            'account_number'  => $b['account_number'],
            'account_type'    => $b['account_type'],
            'account_group'   => $b['account_group'],
            'account_sub_group'=> $b['account_sub_group'],
            'net_balance'     => (float)$b['net_balance'],
        ];
    }
    echo json_encode($rows);
?>;

var abPage = 1;

function abFmt(n) {
    if (isNaN(n)) return '0.00';
    var neg = n < 0;
    var abs = Math.abs(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return neg ? '-' + abs : abs;
}

function abRender(page) {
    abPage = page || 1;
    var perPage   = parseInt(document.getElementById('ab-per-page').value) || 25;
    var searchVal = (document.getElementById('ab-search').value || '').toLowerCase();

    var filtered = abData.filter(function(r) {
        if (!searchVal) return true;
        return (r.account_name.toLowerCase().indexOf(searchVal) !== -1) ||
               (r.account_number.toLowerCase().indexOf(searchVal) !== -1) ||
               (r.account_sub_group.toLowerCase().indexOf(searchVal) !== -1);
    });

    var total    = filtered.length;
    var pages    = Math.max(1, Math.ceil(total / perPage));
    if (abPage > pages) abPage = pages;
    var start    = (abPage - 1) * perPage;
    var end      = Math.min(start + perPage, total);
    var pageRows = filtered.slice(start, end);

    // Net total of all filtered rows
    var netTotal = 0;
    filtered.forEach(function(r) { netTotal += r.net_balance; });

    var tbody   = document.getElementById('ab-tbody');
    var html    = '';
    var lastGrp = null;
    var rowNum  = start;

    pageRows.forEach(function(r) {
        rowNum++;
        // Group header when sub_group changes
        if (r.account_sub_group !== lastGrp) {
            lastGrp = r.account_sub_group;
            var icon = lastGrp === 'Bank' ? 'fa-university' : 'fa-money';
            html += '<tr class="ab-group-hdr"><td colspan="6"><i class="fa ' + icon + '" style="margin-right:6px;"></i>' + escHtml(lastGrp) + '</td></tr>';
        }
        var cls = r.net_balance > 0 ? 'positive' : (r.net_balance < 0 ? 'negative' : 'zero');
        html += '<tr>' +
            '<td>' + rowNum + '</td>' +
            '<td><strong>' + escHtml(r.account_name) + '</strong></td>' +
            '<td>' + escHtml(r.account_number) + '</td>' +
            '<td>' + escHtml(r.account_sub_group) + '</td>' +
            '<td style="color:#aaa;">—</td>' +
            '<td class="num ' + cls + '">' + abFmt(r.net_balance) + '</td>' +
        '</tr>';
    });

    if (total === 0) {
        html = '<tr><td colspan="6" style="text-align:center;padding:40px;color:#aaa;">No Bank or Cash accounts found.</td></tr>';
    }
    tbody.innerHTML = html;

    // Total row
    var totalRow = document.getElementById('ab-total-row');
    var totalCls = netTotal > 0 ? 'positive' : (netTotal < 0 ? 'negative' : 'zero');
    document.getElementById('ab-total-val').innerHTML = '<strong class="' + totalCls + '">' + abFmt(netTotal) + '</strong>';
    totalRow.style.display = total > 0 ? '' : 'none';

    // Info
    document.getElementById('ab-info').textContent =
        total === 0
            ? 'Showing 0 entries'
            : 'Showing ' + (start + 1) + ' to ' + end + ' of ' + total + ' entries';

    // Pagination
    var pgHtml = '';
    pgHtml += '<button class="ab-page-btn" onclick="abRender(' + (abPage - 1) + ')"' + (abPage <= 1 ? ' disabled' : '') + '>&laquo; Prev</button>';
    var pStart = Math.max(1, abPage - 2);
    var pEnd   = Math.min(pages, abPage + 2);
    if (pStart > 1) pgHtml += '<button class="ab-page-btn" onclick="abRender(1)">1</button>' + (pStart > 2 ? '<span style="padding:4px 2px;color:#aaa;">…</span>' : '');
    for (var p = pStart; p <= pEnd; p++) {
        pgHtml += '<button class="ab-page-btn' + (p === abPage ? ' active' : '') + '" onclick="abRender(' + p + ')">' + p + '</button>';
    }
    if (pEnd < pages) pgHtml += (pEnd < pages - 1 ? '<span style="padding:4px 2px;color:#aaa;">…</span>' : '') + '<button class="ab-page-btn" onclick="abRender(' + pages + ')">' + pages + '</button>';
    pgHtml += '<button class="ab-page-btn" onclick="abRender(' + (abPage + 1) + ')"' + (abPage >= pages ? ' disabled' : '') + '>Next &raquo;</button>';
    document.getElementById('ab-pagination').innerHTML = pgHtml;
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

function exportAbCSV() {
    var searchVal = (document.getElementById('ab-search').value || '').toLowerCase();
    var filtered = abData.filter(function(r) {
        if (!searchVal) return true;
        return (r.account_name.toLowerCase().indexOf(searchVal) !== -1) ||
               (r.account_number.toLowerCase().indexOf(searchVal) !== -1) ||
               (r.account_sub_group.toLowerCase().indexOf(searchVal) !== -1);
    });

    var rows = [['#','Account Name','Code','Group','Balance']];
    filtered.forEach(function(r, idx) {
        rows.push([
            idx + 1,
            '"' + r.account_name.replace(/"/g, '""') + '"',
            '"' + r.account_number.replace(/"/g, '""') + '"',
            '"' + r.account_sub_group.replace(/"/g, '""') + '"',
            abFmt(r.net_balance)
        ]);
    });

    var netTotal = 0;
    filtered.forEach(function(r) { netTotal += r.net_balance; });
    rows.push(['', '"Total"', '', '', abFmt(netTotal)]);

    var csv  = rows.map(function(r) { return r.join(','); }).join('\n');
    var blob = new Blob([csv], { type: 'text/csv' });
    var a    = document.createElement('a');
    a.href   = URL.createObjectURL(blob);
    a.download = 'account_balances_<?php echo $as_of; ?>.csv';
    a.click();
}

// Initial render
abRender(1);
</script>

