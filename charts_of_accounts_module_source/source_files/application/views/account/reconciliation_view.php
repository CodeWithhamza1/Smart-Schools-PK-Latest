<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>
.rec-wrap { padding: 20px 24px; background: #f0f2f5; min-height: 600px; }
.rec-card { background: #fff; border: 1px solid #dde3ec; border-radius: 4px; overflow: hidden; }

/* Header */
.rec-header {
    padding: 14px 20px; border-bottom: 1px solid #dde3ec;
    display: flex; align-items: center;
}
.rec-header h2 { margin: 0; font-size: 16px; font-weight: 700; color: #1a1a2e; }

/* Criteria bar */
.rec-criteria {
    padding: 16px 20px; border-bottom: 1px solid #eef0f5; background: #fafbfd;
    display: flex; align-items: flex-end; gap: 16px; flex-wrap: wrap;
}
.rec-fg { display: flex; flex-direction: column; }
.rec-fg label {
    font-size: 11px; font-weight: 700; color: #555;
    text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px;
}
.rec-fg select, .rec-fg input[type=date], .rec-fg input[type=number], .rec-fg input[type=text] {
    border: 1px solid #c8d0db; border-radius: 4px; height: 36px;
    padding: 0 10px; font-size: 13px; color: #333; background: #fff;
}
.rec-fg select    { min-width: 280px; }
.rec-fg input[type=date]   { min-width: 160px; }
.rec-fg input[type=number] { min-width: 160px; text-align: right; }
.rec-fg select:focus, .rec-fg input:focus { border-color: #1a6fc4; outline: none; }
.btn-rec-load {
    background: #27ae60; color: #fff; border: none; border-radius: 4px;
    height: 36px; padding: 0 22px; font-size: 13px; font-weight: 600; cursor: pointer;
    white-space: nowrap; display: inline-flex; align-items: center; gap: 7px;
}
.btn-rec-load:hover { background: #219150; }

/* Summary bar */
.rec-summary {
    display: flex; align-items: center; gap: 0;
    padding: 14px 20px; border-bottom: 1px solid #eef0f5; background: #fff;
    flex-wrap: wrap;
}
.rec-summary-item {
    flex: 1; min-width: 160px; padding: 0 20px 0 0;
    border-right: 1px solid #eee; margin-right: 20px;
}
.rec-summary-item:last-child { border-right: none; margin-right: 0; }
.rec-summary-item label {
    display: block; font-size: 11px; font-weight: 700; color: #888;
    text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px;
}
.rec-summary-item .rec-val {
    font-size: 18px; font-weight: 700; color: #1a3a5c;
}
.rec-summary-item .rec-val.positive { color: #1a5f1a; }
.rec-summary-item .rec-val.negative { color: #c0392b; }
.rec-summary-op {
    font-size: 22px; color: #aaa; font-weight: 300; padding: 0 4px; align-self: flex-end; padding-bottom: 2px;
}
.rec-diff-label {
    font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .4px; margin-bottom: 4px; display: block;
}
.rec-diff-val {
    font-size: 20px; font-weight: 700;
}
.rec-diff-val.zero   { color: #27ae60; }
.rec-diff-val.nonzero { color: #c0392b; }

/* Controls */
.rec-controls {
    display: flex; align-items: center; justify-content: flex-end;
    padding: 8px 20px; border-bottom: 1px solid #eef0f5; background: #fafbfd;
}
.rec-search-box { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #555; }
.rec-search-box input { border: 1px solid #c8d0db; border-radius: 3px; height: 30px; padding: 0 8px; font-size: 13px; width: 220px; }

/* Table */
table.rec-table { width: 100%; border-collapse: collapse; font-size: 13px; }
table.rec-table thead tr { background: #1a6fc4; color: #fff; }
table.rec-table thead th {
    padding: 10px 12px; font-weight: 600; font-size: 12px;
    text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; border: none;
}
table.rec-table thead th.num    { text-align: right; }
table.rec-table thead th.center { text-align: center; }
table.rec-table tbody tr { border-bottom: 1px solid #edf0f5; transition: background .1s; }
table.rec-table tbody tr:hover  { background: #f7f9fd; }
table.rec-table tbody tr.reconciled-row { background: #f0fff4; }
table.rec-table tbody tr.reconciled-row:hover { background: #e6faea; }
table.rec-table tbody td { padding: 9px 12px; color: #333; vertical-align: middle; }
table.rec-table tbody td.num    { text-align: right; font-variant-numeric: tabular-nums; }
table.rec-table tbody td.center { text-align: center; }
table.rec-table tbody td.payment { text-align: right; color: #c0392b; font-weight: 600; }
table.rec-table tbody td.receipt { text-align: right; color: #1a5f1a; font-weight: 600; }
table.rec-table tbody td.type-badge span {
    background: #eef4fb; color: #1a6fc4; border-radius: 3px;
    padding: 2px 7px; font-size: 11px; font-weight: 700; font-family: monospace;
}

/* Rec checkbox */
.rec-chk {
    width: 17px; height: 17px; cursor: pointer; accent-color: #27ae60;
}

/* Empty state */
.rec-empty {
    text-align: center; padding: 50px 20px; color: #aaa; font-size: 14px;
}

/* Footer actions */
.rec-footer {
    padding: 14px 20px; border-top: 1px solid #dde3ec;
    display: flex; align-items: center; justify-content: space-between;
}
.btn-rec-cancel {
    background: #fff; color: #555; border: 1px solid #c8d0db; border-radius: 20px;
    padding: 7px 22px; font-size: 13px; cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-rec-cancel:hover { background: #f5f7fa; color: #333; text-decoration: none; }
.btn-rec-save {
    background: #27ae60; color: #fff; border: none; border-radius: 20px;
    padding: 8px 26px; font-size: 13px; font-weight: 700; cursor: pointer;
    display: inline-flex; align-items: center; gap: 8px;
}
.btn-rec-save:hover { background: #219150; }
.btn-rec-save:disabled { background: #95d5b2; cursor: not-allowed; }

/* Spinner */
.rec-loading { text-align: center; padding: 40px; color: #888; font-size: 14px; }

@media print {
    .rec-criteria, .rec-controls, .rec-footer, .content-header, .no-print { display: none !important; }
    .rec-wrap { padding: 0; background: #fff; }
    .rec-card { border: none; }
    table.rec-table thead tr { background: #333 !important; -webkit-print-color-adjust: exact; }
}
</style>

<div class="content-wrapper" style="min-height: 946px; background: #f0f2f5;">
    <section class="content-header no-print">
        <h1><i class="fa fa-balance-scale"></i> Reconciliation</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li>Bank</li>
            <li class="active">Reconciliation</li>
        </ol>
    </section>

    <section class="content" style="padding: 0 10px 10px;">
        <div id="rec-flash"></div>

        <div class="rec-wrap">
            <div class="rec-card">

                <!-- Header -->
                <div class="rec-header">
                    <h2><i class="fa fa-balance-scale" style="color:#1a6fc4; margin-right:7px;"></i> Reconciliation Balance</h2>
                </div>

                <!-- Criteria -->
                <div class="rec-criteria no-print">
                    <div class="rec-fg">
                        <label>Bank Account <span style="color:#e74c3c;">*</span></label>
                        <select id="rec-bank-id">
                            <option value="">— Select Bank / Cash Account —</option>
                            <?php
                            $last_sub = '';
                            foreach ($bank_accounts as $ba):
                                if ($ba['account_sub_group'] !== $last_sub):
                                    if ($last_sub !== '') echo '</optgroup>';
                                    echo '<optgroup label="' . htmlspecialchars($ba['account_sub_group']) . '">';
                                    $last_sub = $ba['account_sub_group'];
                                endif;
                            ?>
                                <option value="<?php echo $ba['id']; ?>">
                                    <?php echo htmlspecialchars($ba['account_name'] . ' (' . $ba['account_number'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                            <?php if ($last_sub !== '') echo '</optgroup>'; ?>
                        </select>
                    </div>
                    <div class="rec-fg">
                        <label>Statement Date <span style="color:#e74c3c;">*</span></label>
                        <input type="date" id="rec-statement-date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="rec-fg">
                        <label>Statement Balance <span style="color:#e74c3c;">*</span></label>
                        <input type="number" id="rec-statement-balance" step="0.01" placeholder="0.00" value="">
                    </div>
                    <div class="rec-fg" style="justify-content: flex-end;">
                        <button class="btn-rec-load" onclick="recLoadTransactions()">
                            <i class="fa fa-refresh"></i> Load Transactions
                        </button>
                    </div>
                </div>

                <!-- Summary bar -->
                <div class="rec-summary" id="rec-summary" style="display:none;">
                    <div class="rec-summary-item">
                        <label>Opening Balance</label>
                        <div class="rec-val" id="rec-opening-val">0.00</div>
                    </div>
                    <div class="rec-summary-op">+</div>
                    <div class="rec-summary-item">
                        <label>Receipts</label>
                        <div class="rec-val positive" id="rec-receipts-val">0.00</div>
                    </div>
                    <div class="rec-summary-op">&minus;</div>
                    <div class="rec-summary-item">
                        <label>Payments</label>
                        <div class="rec-val negative" id="rec-payments-val">0.00</div>
                    </div>
                    <div class="rec-summary-op">=</div>
                    <div class="rec-summary-item">
                        <label>Balance</label>
                        <div class="rec-val" id="rec-balance-val">0.00</div>
                    </div>
                    <div class="rec-summary-item" style="margin-left:30px; border-left:2px solid #eee; padding-left:30px;">
                        <span class="rec-diff-label">Difference</span>
                        <div class="rec-diff-val zero" id="rec-diff-val">0.00</div>
                    </div>
                </div>

                <!-- Table area -->
                <div id="rec-table-area">
                    <!-- Empty state -->
                    <div class="rec-empty" id="rec-empty-state">
                        <i class="fa fa-bank fa-2x" style="display:block;margin-bottom:10px;color:#ccc;"></i>
                        Select a Bank Account, enter Statement Date &amp; Balance, then click <strong>Load Transactions</strong>.
                    </div>
                </div>

                <!-- Footer -->
                <div class="rec-footer" id="rec-footer" style="display:none;">
                    <a href="<?php echo site_url('accounts/reconciliation'); ?>" class="btn-rec-cancel">Cancel</a>
                    <button class="btn-rec-save" id="rec-save-btn" onclick="recSave()">
                        <i class="fa fa-check-circle"></i> Save and Close
                    </button>
                </div>

            </div>
        </div>
    </section>
</div>

<script>
var recTransactions = [];
var recOpening      = 0;

function recFmt(n) {
    var neg = n < 0;
    var s   = Math.abs(n).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    return neg ? '-' + s : s;
}
function escH(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmtDate(str) {
    if (!str) return '';
    var d = new Date(str);
    return !isNaN(d) ? ('0'+d.getDate()).slice(-2)+'/'+('0'+(d.getMonth()+1)).slice(-2)+'/'+d.getFullYear() : str;
}
function typeLabel(t) {
    var map = { BPV:'BP', BRV:'BR', TRF:'TRF', JNL:'JN', JV:'JV', EP:'EP' };
    return map[t] || t;
}

function recLoadTransactions() {
    var bankId  = document.getElementById('rec-bank-id').value;
    var stDate  = document.getElementById('rec-statement-date').value;
    var stBal   = document.getElementById('rec-statement-balance').value;

    if (!bankId)  { alert('Please select a Bank Account.'); return; }
    if (!stDate)  { alert('Please enter a Statement Date.'); return; }
    if (stBal === '') { alert('Please enter the Statement Balance.'); return; }

    document.getElementById('rec-table-area').innerHTML =
        '<div class="rec-loading"><i class="fa fa-spinner fa-spin fa-2x"></i><br>Loading transactions…</div>';
    document.getElementById('rec-summary').style.display = 'none';
    document.getElementById('rec-footer').style.display  = 'none';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo site_url('accounts/reconciliation_load_transactions'); ?>', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        var raw = xhr.responseText;
        // Strip any leading PHP warnings/notices before the JSON
        var jsonStart = raw.indexOf('{');
        if (jsonStart > 0) raw = raw.substring(jsonStart);
        try {
            var resp = JSON.parse(raw);
            if (resp.success) {
                recTransactions = resp.transactions;
                recOpening      = parseFloat(resp.opening_balance) || 0;
                recRenderTable();
                recUpdateSummary();
                document.getElementById('rec-summary').style.display = 'flex';
                document.getElementById('rec-footer').style.display  = 'flex';
            } else {
                document.getElementById('rec-table-area').innerHTML =
                    '<div class="rec-empty"><i class="fa fa-warning fa-2x" style="color:#e74c3c;display:block;margin-bottom:8px;"></i>' +
                    escH(resp.message || 'Error loading transactions.') + '</div>';
            }
        } catch(e) {
            document.getElementById('rec-table-area').innerHTML =
                '<div class="rec-empty" style="color:#c0392b;"><i class="fa fa-warning fa-2x" style="display:block;margin-bottom:8px;"></i>' +
                'Server error. Response: <pre style="font-size:11px;text-align:left;margin-top:8px;overflow:auto;">' + escH(xhr.responseText.substring(0, 500)) + '</pre></div>';
        }
    };
    xhr.onerror = function() {
        document.getElementById('rec-table-area').innerHTML =
            '<div class="rec-empty" style="color:#c0392b;">Network error — could not reach server.</div>';
    };
    var body = 'bank_id=' + encodeURIComponent(bankId) +
               '&statement_date=' + encodeURIComponent(stDate);
    xhr.send(body);
}

function recRenderTable() {
    var q = (document.getElementById('rec-q') ? document.getElementById('rec-q').value : '').toLowerCase();

    var filtered = recTransactions.filter(function(r) {
        if (!q) return true;
        return ((r.counterpart_name||'').toLowerCase().indexOf(q) !== -1) ||
               ((r.voucher_number||'').toLowerCase().indexOf(q) !== -1) ||
               ((r.description||'').toLowerCase().indexOf(q) !== -1) ||
               ((r.voucher_type||'').toLowerCase().indexOf(q) !== -1) ||
               (r.voucher_date.indexOf(q) !== -1);
    });

    if (filtered.length === 0) {
        document.getElementById('rec-table-area').innerHTML =
            '<div class="rec-controls no-print"><div class="rec-search-box">Search: <input type="text" id="rec-q" placeholder="Search transactions…" oninput="recRenderTable()"></div></div>' +
            '<div class="rec-empty"><i class="fa fa-inbox fa-2x" style="display:block;margin-bottom:10px;color:#ccc;"></i>No transactions found for this period.</div>';
        return;
    }

    var html =
        '<div class="rec-controls no-print"><div class="rec-search-box">Search: <input type="text" id="rec-q" placeholder="Search transactions…" oninput="recRenderTable()" value="' + escH(q) + '"></div></div>' +
        '<table class="rec-table" id="rec-tbl">' +
        '<thead><tr>' +
            '<th style="width:100px;">Date</th>' +
            '<th style="width:70px;">V. ID</th>' +
            '<th style="width:70px;">Type</th>' +
            '<th>Account / Payee</th>' +
            '<th style="width:130px;">Ref. No.</th>' +
            '<th>Details</th>' +
            '<th class="num" style="width:120px;">Payments</th>' +
            '<th class="num" style="width:120px;">Receipts</th>' +
            '<th class="center" style="width:46px;">Rec <input type="checkbox" id="rec-check-all" style="margin-left:3px;accent-color:#27ae60;" onclick="recToggleAll(this)"></th>' +
        '</tr></thead>' +
        '<tbody>';

    filtered.forEach(function(r) {
        var isRec   = (r.reconciled == 1 || r.reconciled === '1');
        var trClass = isRec ? ' class="reconciled-row"' : '';
        var payAmt  = parseFloat(r.payment_amount) || 0;
        var recAmt  = parseFloat(r.receipt_amount) || 0;

        html += '<tr' + trClass + '>' +
            '<td>' + escH(fmtDate(r.voucher_date)) + '</td>' +
            '<td style="color:#1a6fc4;font-weight:600;">' + escH(r.je_id) + '</td>' +
            '<td class="type-badge"><span>' + escH(typeLabel(r.voucher_type)) + '</span></td>' +
            '<td>' + escH(r.counterpart_name || '—') + '</td>' +
            '<td class="ref" style="font-size:11px;color:#888;">' + escH(r.voucher_number || '') + '</td>' +
            '<td>' + escH(r.description || '') + '</td>' +
            '<td class="payment">' + (payAmt > 0 ? recFmt(payAmt) : '') + '</td>' +
            '<td class="receipt">' + (recAmt > 0 ? recFmt(recAmt) : '') + '</td>' +
            '<td class="center">' +
                '<input type="checkbox" class="rec-chk" data-detail-id="' + r.detail_id + '"' +
                       ' data-payment="' + payAmt + '" data-receipt="' + recAmt + '"' +
                       (isRec ? ' checked' : '') +
                       ' onchange="recOnCheck(this)">' +
            '</td>' +
        '</tr>';
    });

    html += '</tbody></table>';
    document.getElementById('rec-table-area').innerHTML = html;
    recUpdateSummary();
}

function recToggleAll(masterCb) {
    document.querySelectorAll('.rec-chk').forEach(function(cb) {
        cb.checked = masterCb.checked;
    });
    recUpdateSummary();
}

function recOnCheck(cb) {
    var tr = cb.closest('tr');
    if (cb.checked) {
        tr.classList.add('reconciled-row');
    } else {
        tr.classList.remove('reconciled-row');
        var master = document.getElementById('rec-check-all');
        if (master) master.checked = false;
    }
    recUpdateSummary();
}

function recUpdateSummary() {
    var checkedReceipts = 0;
    var checkedPayments = 0;

    document.querySelectorAll('.rec-chk:checked').forEach(function(cb) {
        checkedReceipts += parseFloat(cb.getAttribute('data-receipt')) || 0;
        checkedPayments += parseFloat(cb.getAttribute('data-payment')) || 0;
    });

    var balance    = recOpening + checkedReceipts - checkedPayments;
    var stBal      = parseFloat(document.getElementById('rec-statement-balance').value) || 0;
    var difference = balance - stBal;

    document.getElementById('rec-opening-val').textContent  = recFmt(recOpening);
    document.getElementById('rec-receipts-val').textContent = recFmt(checkedReceipts);
    document.getElementById('rec-payments-val').textContent = recFmt(checkedPayments);
    document.getElementById('rec-balance-val').textContent  = recFmt(balance);

    var diffEl = document.getElementById('rec-diff-val');
    diffEl.textContent = recFmt(difference);
    diffEl.className   = 'rec-diff-val ' + (Math.abs(difference) < 0.005 ? 'zero' : 'nonzero');

    // Enable/disable save button
    var saveBtn = document.getElementById('rec-save-btn');
    if (saveBtn) {
        saveBtn.disabled = false; // allow saving even with difference for partial reconciliation
    }
}

function recSave() {
    var bankId  = document.getElementById('rec-bank-id').value;
    var stDate  = document.getElementById('rec-statement-date').value;
    var stBal   = document.getElementById('rec-statement-balance').value;

    if (!bankId || !stDate) {
        alert('Please load transactions first.');
        return;
    }

    var reconciledIds = [];
    document.querySelectorAll('.rec-chk:checked').forEach(function(cb) {
        reconciledIds.push(cb.getAttribute('data-detail-id'));
    });

    document.getElementById('rec-save-btn').disabled = true;
    document.getElementById('rec-save-btn').innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving…';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo site_url('accounts/reconciliation_save'); ?>', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var raw = xhr.responseText;
                var jsonStart = raw.indexOf('{');
                if (jsonStart > 0) raw = raw.substring(jsonStart);
                var resp = JSON.parse(raw);

                var flash = document.getElementById('rec-flash');
                if (resp.success) {
                    flash.innerHTML = '<div class="alert alert-success" style="margin:10px 0 0;"><i class="fa fa-check"></i> ' + escH(resp.message) + '</div>';
                    // Highlight reconciled rows
                    document.querySelectorAll('.rec-chk:checked').forEach(function(cb){
                        cb.closest('tr').classList.add('reconciled-row');
                    });
                    setTimeout(function(){ flash.innerHTML = ''; }, 4000);
                } else {
                    flash.innerHTML = '<div class="alert alert-danger" style="margin:10px 0 0;">' + escH(resp.message || 'Error saving.') + '</div>';
                }
            } catch(e) {}
        }
        document.getElementById('rec-save-btn').disabled = false;
        document.getElementById('rec-save-btn').innerHTML = '<i class="fa fa-check-circle"></i> Save and Close';
    };

    var body = 'bank_id=' + encodeURIComponent(bankId) +
               '&statement_date=' + encodeURIComponent(stDate) +
               '&statement_balance=' + encodeURIComponent(stBal) +
               '&reconciled_ids=' + encodeURIComponent(reconciledIds.join(','));
    xhr.send(body);
}

// Update difference live when statement balance changes
document.getElementById('rec-statement-balance').addEventListener('input', function() {
    if (document.getElementById('rec-diff-val')) recUpdateSummary();
});
</script>
