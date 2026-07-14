<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>
.bra-wrap { padding: 20px 24px; background: #f0f2f5; min-height: 600px; }
.bra-card { background: #fff; border: 1px solid #dde3ec; border-radius: 4px; overflow: hidden; }

/* Header */
.bra-header {
    padding: 14px 20px; border-bottom: 1px solid #dde3ec;
    display: flex; align-items: center; justify-content: space-between;
}
.bra-header h2 { margin: 0; font-size: 16px; font-weight: 700; color: #1a1a2e; }

/* Bank selector */
.bra-bank-bar {
    padding: 14px 20px; border-bottom: 1px solid #eef0f5;
    display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    background: #fafbfd;
}
.bra-bank-bar label { font-size: 13px; font-weight: 700; color: #333; white-space: nowrap; }
.bra-bank-bar select {
    border: 1px solid #c8d0db; border-radius: 4px; height: 36px;
    padding: 0 10px; font-size: 13px; color: #333; min-width: 280px;
}
.bra-bank-bar select:focus { border-color: #1a6fc4; outline: none; }

/* Table */
.bra-table-wrap { padding: 16px 20px; }
table.bra-table { width: 100%; border-collapse: collapse; font-size: 13px; }
table.bra-table thead tr { background: #1a6fc4; color: #fff; }
table.bra-table thead th {
    padding: 10px 12px; font-weight: 600; font-size: 12px;
    text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; border: none;
}
table.bra-table thead th.num { text-align: right; }
table.bra-table thead th.req::after { content: ' *'; color: #ffcccc; }
table.bra-table tbody tr { border-bottom: 1px solid #eef0f5; }
table.bra-table tbody td { padding: 7px 8px; vertical-align: middle; }
table.bra-table input[type=date],
table.bra-table input[type=text],
table.bra-table input[type=number],
table.bra-table select {
    border: 1px solid #c8d0db; border-radius: 4px; height: 34px;
    padding: 0 8px; font-size: 13px; color: #333; width: 100%; background: #fff;
}
table.bra-table input:focus, table.bra-table select:focus { border-color: #1a6fc4; outline: none; }
table.bra-table input[type=number] { text-align: right; }
table.bra-table .td-del { text-align: center; width: 36px; }
table.bra-table .btn-del-row {
    background: none; border: none; color: #c0392b; font-size: 16px; cursor: pointer; padding: 4px;
}
table.bra-table .btn-del-row:hover { color: #a93226; }

/* Totals */
.bra-totals-wrap { padding: 0 20px 10px; }
table.bra-totals-tbl { width: 100%; border-collapse: collapse; font-size: 13px; }
table.bra-totals-tbl td {
    padding: 8px 12px; font-weight: 700; color: #1a5f1a;
    text-align: right; border-top: 2px solid #27ae60; background: #f0fff4;
}
table.bra-totals-tbl td:first-child { text-align: left; color: #1a3a5c; }

/* Add more */
.bra-add-more { padding: 10px 20px 16px; }
.btn-add-more {
    background: none; border: 1px dashed #27ae60; color: #27ae60;
    border-radius: 20px; padding: 6px 18px; font-size: 13px; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-add-more:hover { background: #f0fff4; }

/* Footer */
.bra-footer {
    padding: 14px 20px; border-top: 1px solid #dde3ec;
    display: flex; align-items: center; justify-content: space-between;
}
.btn-bra-cancel {
    background: #fff; color: #555; border: 1px solid #c8d0db; border-radius: 20px;
    padding: 7px 22px; font-size: 13px; cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-bra-cancel:hover { background: #f5f7fa; color: #333; text-decoration: none; }
.btn-bra-approve {
    background: #27ae60; color: #fff; border: none; border-radius: 20px;
    padding: 8px 24px; font-size: 13px; font-weight: 700; cursor: pointer;
    display: inline-flex; align-items: center; gap: 8px;
}
.btn-bra-approve:hover { background: #219150; }
</style>

<div class="content-wrapper" style="min-height: 946px; background: #f0f2f5;">
    <section class="content-header">
        <h1><i class="fa fa-arrow-down"></i> Add Bank Receipt</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?php echo site_url('accounts/bank_receipts'); ?>">Bank Receipts</a></li>
            <li class="active">Add Bank Receipt</li>
        </ol>
    </section>

    <section class="content" style="padding: 0 10px 10px;">
        <?php if ($this->session->flashdata('msg')): ?>
            <div style="padding: 10px 0 0;"><?php echo $this->session->flashdata('msg'); ?></div>
        <?php endif; ?>

        <div class="bra-wrap">
            <div class="bra-card">

                <div class="bra-header">
                    <h2><i class="fa fa-arrow-circle-down" style="color:#27ae60; margin-right:6px;"></i> Add Bank Receipts</h2>
                </div>

                <form method="post" action="<?php echo site_url('accounts/save_bank_receipt'); ?>" id="bra-form">
                    <?php echo $this->customlib->getCSRF(); ?>

                    <!-- Bank selector -->
                    <div class="bra-bank-bar">
                        <label>Bank <span style="color:#e74c3c;">*</span></label>
                        <select name="bank_id" id="bra-bank" required>
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

                    <!-- Lines table -->
                    <div class="bra-table-wrap">
                        <table class="bra-table" id="bra-lines-table">
                            <thead>
                                <tr>
                                    <th class="req" style="width:120px;">Date</th>
                                    <th style="width:130px;">Ref. No.</th>
                                    <th style="width:110px;">Mode</th>
                                    <th class="req">Nominal Account</th>
                                    <th>Details</th>
                                    <th class="num req" style="width:130px;">Amount (<?php echo htmlspecialchars($currency_symbol); ?>)</th>
                                    <th style="width:36px;"></th>
                                </tr>
                            </thead>
                            <tbody id="bra-tbody"></tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="bra-totals-wrap">
                        <table class="bra-totals-tbl">
                            <tr>
                                <td>Total</td>
                                <td id="bra-total-display">0.00</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Add more -->
                    <div class="bra-add-more">
                        <button type="button" class="btn-add-more" onclick="braAddRow()">
                            <i class="fa fa-plus-circle"></i> + Add More
                        </button>
                    </div>

                    <!-- Footer -->
                    <div class="bra-footer">
                        <a href="<?php echo site_url('accounts/bank_receipts'); ?>" class="btn-bra-cancel">Cancel</a>
                        <button type="submit" class="btn-bra-approve">
                            <i class="fa fa-check-circle"></i> Approve and Close
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>

<script>
var braAccounts = <?php
    $accs = [];
    foreach ($nominal_accounts as $a) {
        $accs[] = ['id' => (int)$a['id'], 'name' => $a['account_name'], 'number' => $a['account_number']];
    }
    echo json_encode($accs);
?>;

var braRowCount = 0;
var today = '<?php echo date('Y-m-d'); ?>';

function braAddRow() {
    var idx  = braRowCount++;
    var opts = '<option value="">— Select Account —</option>';
    braAccounts.forEach(function(a) {
        opts += '<option value="' + a.id + '">' + escHtml(a.name + ' (' + a.number + ')') + '</option>';
    });

    var tr   = document.createElement('tr');
    tr.id    = 'bra-row-' + idx;
    tr.innerHTML =
        '<td><input type="date" name="lines[' + idx + '][date]" value="' + today + '" required></td>' +
        '<td><input type="text"  name="lines[' + idx + '][ref_no]" placeholder="Ref No."></td>' +
        '<td>' +
            '<select name="lines[' + idx + '][mode]">' +
                '<option value="Cash">Cash</option>' +
                '<option value="Cheque">Cheque</option>' +
                '<option value="Transfer">Transfer</option>' +
                '<option value="Online">Online</option>' +
            '</select>' +
        '</td>' +
        '<td><select name="lines[' + idx + '][nominal_id]" required onchange="braRecalc()">' + opts + '</select></td>' +
        '<td><input type="text" name="lines[' + idx + '][details]" placeholder="Narration / details"></td>' +
        '<td><input type="number" name="lines[' + idx + '][amount]" value="" min="0.01" step="0.01" placeholder="0.00" required oninput="braRecalc()"></td>' +
        '<td class="td-del"><button type="button" class="btn-del-row" onclick="braDelRow(' + idx + ')" title="Remove"><i class="fa fa-trash"></i></button></td>';

    document.getElementById('bra-tbody').appendChild(tr);
    braRecalc();
}

function braDelRow(idx) {
    var tr = document.getElementById('bra-row-' + idx);
    if (tr) tr.parentNode.removeChild(tr);
    braRecalc();
}

function braRecalc() {
    var inputs = document.querySelectorAll('#bra-tbody input[type=number]');
    var total  = 0;
    inputs.forEach(function(i){ total += parseFloat(i.value) || 0; });
    document.getElementById('bra-total-display').textContent = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.getElementById('bra-form').addEventListener('submit', function(e) {
    if (!document.getElementById('bra-bank').value) {
        alert('Please select a Bank / Cash account.'); e.preventDefault(); return;
    }
    if (document.querySelectorAll('#bra-tbody tr').length === 0) {
        alert('Please add at least one receipt line.'); e.preventDefault(); return;
    }
    var total = 0;
    document.querySelectorAll('#bra-tbody input[type=number]').forEach(function(i){ total += parseFloat(i.value)||0; });
    if (total <= 0) { alert('Total amount must be greater than zero.'); e.preventDefault(); return; }
});

braAddRow();
</script>
