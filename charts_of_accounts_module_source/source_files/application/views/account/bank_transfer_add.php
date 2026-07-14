<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>
.bta-wrap { padding: 20px 24px; background: #f0f2f5; min-height: 600px; }
.bta-card { background: #fff; border: 1px solid #dde3ec; border-radius: 4px; overflow: hidden; }

.bta-header {
    padding: 14px 20px; border-bottom: 1px solid #dde3ec;
    display: flex; align-items: center;
}
.bta-header h2 { margin: 0; font-size: 16px; font-weight: 700; color: #1a1a2e; }

.bta-table-wrap { padding: 16px 20px; }
table.bta-table { width: 100%; border-collapse: collapse; font-size: 13px; }
table.bta-table thead tr { background: #1a6fc4; color: #fff; }
table.bta-table thead th {
    padding: 10px 12px; font-weight: 600; font-size: 12px;
    text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; border: none;
}
table.bta-table thead th.num  { text-align: right; }
table.bta-table thead th.req::after { content: ' *'; color: #ffcccc; }
table.bta-table tbody tr { border-bottom: 1px solid #eef0f5; }
table.bta-table tbody td { padding: 7px 8px; vertical-align: middle; }

table.bta-table input[type=date],
table.bta-table input[type=text],
table.bta-table input[type=number],
table.bta-table select {
    border: 1px solid #c8d0db; border-radius: 4px; height: 34px;
    padding: 0 8px; font-size: 13px; color: #333; width: 100%; background: #fff;
}
table.bta-table input:focus, table.bta-table select:focus { border-color: #1a6fc4; outline: none; }
table.bta-table input[type=number] { text-align: right; }
table.bta-table .td-del { text-align: center; width: 36px; }
table.bta-table .btn-del-row {
    background: none; border: none; color: #c0392b; font-size: 16px; cursor: pointer; padding: 4px;
}
table.bta-table .btn-del-row:hover { color: #a93226; }

/* Custom searchable dropdown for bank selection */
.bank-select-wrap { position: relative; }
.bank-select-input {
    border: 1px solid #c8d0db; border-radius: 4px; height: 34px;
    padding: 0 30px 0 8px; font-size: 13px; color: #333; width: 100%; background: #fff;
    cursor: pointer; text-align: left; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.bank-select-input:focus { border-color: #1a6fc4; outline: none; }
.bank-dropdown-list {
    display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 1000;
    background: #fff; border: 1px solid #c8d0db; border-top: none; border-radius: 0 0 4px 4px;
    max-height: 200px; overflow-y: auto;
    box-shadow: 0 4px 10px rgba(0,0,0,.12);
}
.bank-dropdown-list.open { display: block; }
.bank-dropdown-search {
    padding: 6px 8px; border-bottom: 1px solid #eee;
}
.bank-dropdown-search input {
    width: 100%; border: 1px solid #c8d0db; border-radius: 3px;
    height: 28px; padding: 0 6px; font-size: 12px;
}
.bank-dropdown-item {
    padding: 8px 10px; font-size: 13px; cursor: pointer; color: #333;
}
.bank-dropdown-item:hover { background: #eef4fb; color: #1a6fc4; }
.bank-dropdown-item.selected { background: #ddeeff; font-weight: 600; }
.bank-caret {
    position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
    color: #aaa; pointer-events: none; font-size: 11px;
}

/* Totals */
.bta-totals-wrap { padding: 0 20px 10px; }
table.bta-totals-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
table.bta-totals-tbl td {
    padding: 8px 12px; font-weight: 700;
    text-align: right; border-top: 2px solid #8e44ad; background: #f9f0ff;
    color: #5b2c6f;
}
table.bta-totals-tbl td:first-child { text-align: left; color: #1a3a5c; }

.bta-add-more { padding: 10px 20px 16px; }
.btn-add-more {
    background: none; border: 1px dashed #8e44ad; color: #8e44ad;
    border-radius: 20px; padding: 6px 18px; font-size: 13px; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-add-more:hover { background: #f9f0ff; }

.bta-footer {
    padding: 14px 20px; border-top: 1px solid #dde3ec;
    display: flex; align-items: center; justify-content: space-between;
}
.btn-bta-cancel {
    background: #fff; color: #555; border: 1px solid #c8d0db; border-radius: 20px;
    padding: 7px 22px; font-size: 13px; cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-bta-cancel:hover { background: #f5f7fa; color: #333; text-decoration: none; }
.btn-bta-approve {
    background: #27ae60; color: #fff; border: none; border-radius: 20px;
    padding: 8px 24px; font-size: 13px; font-weight: 700; cursor: pointer;
    display: inline-flex; align-items: center; gap: 8px;
}
.btn-bta-approve:hover { background: #219150; }
</style>

<div class="content-wrapper" style="min-height: 946px; background: #f0f2f5;">
    <section class="content-header">
        <h1><i class="fa fa-exchange"></i> Add Transfer</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?php echo site_url('accounts/bank_transfers'); ?>">Transfers</a></li>
            <li class="active">Add Transfers</li>
        </ol>
    </section>

    <section class="content" style="padding: 0 10px 10px;">
        <?php if ($this->session->flashdata('msg')): ?>
            <div style="padding: 10px 0 0;"><?php echo $this->session->flashdata('msg'); ?></div>
        <?php endif; ?>

        <div class="bta-wrap">
            <div class="bta-card">

                <div class="bta-header">
                    <h2><i class="fa fa-exchange" style="color:#8e44ad; margin-right:6px;"></i> Add Transfers</h2>
                </div>

                <form method="post" action="<?php echo site_url('accounts/save_bank_transfer'); ?>" id="bta-form">
                    <?php echo $this->customlib->getCSRF(); ?>

                    <!-- Lines table -->
                    <div class="bta-table-wrap">
                        <table class="bta-table" id="bta-lines-table">
                            <thead>
                                <tr>
                                    <th class="req" style="width:120px;">Date</th>
                                    <th class="req" style="width:200px;">From Bank</th>
                                    <th style="width:130px;">Ref. No.</th>
                                    <th style="width:100px;">Mode</th>
                                    <th class="req" style="width:200px;">To Bank</th>
                                    <th>Details</th>
                                    <th class="num req" style="width:130px;">Amount (<?php echo htmlspecialchars($currency_symbol); ?>)</th>
                                    <th style="width:36px;"></th>
                                </tr>
                            </thead>
                            <tbody id="bta-tbody"></tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="bta-totals-wrap">
                        <table class="bta-totals-tbl">
                            <tr>
                                <td>Total</td>
                                <td id="bta-total-display">0.00</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Add more -->
                    <div class="bta-add-more">
                        <button type="button" class="btn-add-more" onclick="btaAddRow()">
                            <i class="fa fa-plus-circle"></i> + Add More
                        </button>
                    </div>

                    <!-- Footer -->
                    <div class="bta-footer">
                        <a href="<?php echo site_url('accounts/bank_transfers'); ?>" class="btn-bta-cancel">Cancel</a>
                        <button type="submit" class="btn-bta-approve">
                            <i class="fa fa-check-circle"></i> Approve and Close
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>

<script>
var btaAccounts = <?php
    $accs = [];
    foreach ($bank_accounts as $a) {
        $accs[] = [
            'id'        => (int)$a['id'],
            'name'      => $a['account_name'],
            'number'    => $a['account_number'],
            'sub_group' => $a['account_sub_group'],
        ];
    }
    echo json_encode($accs);
?>;

var btaRowCount = 0;
var today = '<?php echo date('Y-m-d'); ?>';

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function buildBankDropdown(name, rowIdx, side) {
    var uid = 'bsd-' + rowIdx + '-' + side;
    var html =
        '<div class="bank-select-wrap" id="wrap-' + uid + '">' +
            '<input type="hidden" name="lines[' + rowIdx + '][' + name + ']" id="hid-' + uid + '" value="">' +
            '<button type="button" class="bank-select-input" id="btn-' + uid + '" onclick="btaToggleDrop(\'' + uid + '\')">' +
                '<span id="lbl-' + uid + '" style="color:#aaa;">— Select Bank —</span>' +
            '</button>' +
            '<span class="bank-caret"><i class="fa fa-chevron-down"></i></span>' +
            '<div class="bank-dropdown-list" id="list-' + uid + '">' +
                '<div class="bank-dropdown-search"><input type="text" placeholder="Search…" oninput="btaFilterDrop(\'' + uid + '\', this.value)"></div>' +
                '<div id="items-' + uid + '">';

    var lastGrp = '';
    btaAccounts.forEach(function(a) {
        if (a.sub_group !== lastGrp) {
            html += '<div style="padding:5px 10px 2px;font-size:10px;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:.5px;">' + escHtml(a.sub_group) + '</div>';
            lastGrp = a.sub_group;
        }
        html += '<div class="bank-dropdown-item" data-id="' + a.id + '" data-label="' + escHtml(a.name + ' (' + a.number + ')') + '" onclick="btaSelectBank(\'' + uid + '\',' + a.id + ',\'' + escHtml(a.name + ' (' + a.number + ')').replace(/'/g,"\\'") + '\')">' +
            escHtml(a.name + ' (' + a.number + ')') + '</div>';
    });

    html += '</div></div></div>';
    return html;
}

function btaToggleDrop(uid) {
    // Close all others
    document.querySelectorAll('.bank-dropdown-list.open').forEach(function(d){
        if (d.id !== 'list-' + uid) d.classList.remove('open');
    });
    document.getElementById('list-' + uid).classList.toggle('open');
}

function btaSelectBank(uid, id, label) {
    document.getElementById('hid-' + uid).value  = id;
    document.getElementById('lbl-' + uid).textContent = label;
    document.getElementById('lbl-' + uid).style.color = '#333';
    document.getElementById('list-' + uid).classList.remove('open');
    // mark selected
    document.querySelectorAll('#items-' + uid + ' .bank-dropdown-item').forEach(function(el){
        el.classList.toggle('selected', parseInt(el.getAttribute('data-id')) === id);
    });
}

function btaFilterDrop(uid, q) {
    q = q.toLowerCase();
    document.querySelectorAll('#items-' + uid + ' .bank-dropdown-item').forEach(function(el){
        var lbl = (el.getAttribute('data-label') || '').toLowerCase();
        el.style.display = (!q || lbl.indexOf(q) !== -1) ? '' : 'none';
    });
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.bank-select-wrap')) {
        document.querySelectorAll('.bank-dropdown-list.open').forEach(function(d){ d.classList.remove('open'); });
    }
});

function btaAddRow() {
    var idx = btaRowCount++;
    var tr  = document.createElement('tr');
    tr.id   = 'bta-row-' + idx;
    tr.innerHTML =
        '<td><input type="date" name="lines[' + idx + '][date]" value="' + today + '" required></td>' +
        '<td>' + buildBankDropdown('from_bank_id', idx, 'from') + '</td>' +
        '<td><input type="text"   name="lines[' + idx + '][ref_no]"  placeholder="Ref No."></td>' +
        '<td>' +
            '<select name="lines[' + idx + '][mode]">' +
                '<option value="Cash">Cash</option>' +
                '<option value="Cheque">Cheque</option>' +
                '<option value="Transfer">Transfer</option>' +
                '<option value="Online">Online</option>' +
            '</select>' +
        '</td>' +
        '<td>' + buildBankDropdown('to_bank_id', idx, 'to') + '</td>' +
        '<td><input type="text"   name="lines[' + idx + '][details]" placeholder="Narration / details"></td>' +
        '<td><input type="number" name="lines[' + idx + '][amount]"  value="" min="0.01" step="0.01" placeholder="0.00" required oninput="btaRecalc()"></td>' +
        '<td class="td-del"><button type="button" class="btn-del-row" onclick="btaDelRow(' + idx + ')" title="Remove"><i class="fa fa-trash"></i></button></td>';
    document.getElementById('bta-tbody').appendChild(tr);
    btaRecalc();
}

function btaDelRow(idx) {
    var tr = document.getElementById('bta-row-' + idx);
    if (tr) tr.parentNode.removeChild(tr);
    btaRecalc();
}

function btaRecalc() {
    var total = 0;
    document.querySelectorAll('#bta-tbody input[type=number]').forEach(function(i){ total += parseFloat(i.value)||0; });
    document.getElementById('bta-total-display').textContent = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g,',');
}

document.getElementById('bta-form').addEventListener('submit', function(e) {
    var rows = document.querySelectorAll('#bta-tbody tr');
    if (rows.length === 0) { alert('Please add at least one transfer line.'); e.preventDefault(); return; }
    var valid = true;
    rows.forEach(function(tr) {
        var fromId = tr.querySelector('input[name*="from_bank_id"]').value;
        var toId   = tr.querySelector('input[name*="to_bank_id"]').value;
        var amt    = parseFloat(tr.querySelector('input[type=number]').value) || 0;
        if (!fromId || !toId) { valid = false; }
        if (fromId && toId && fromId === toId) { alert('From Bank and To Bank cannot be the same account.'); valid = false; }
        if (amt <= 0) valid = false;
    });
    if (!valid) { e.preventDefault(); }
});

btaAddRow();
</script>
