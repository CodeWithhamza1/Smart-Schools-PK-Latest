<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>
.bpa-wrap { padding: 20px 24px; background: #f0f2f5; min-height: 600px; }
.bpa-card { background: #fff; border: 1px solid #dde3ec; border-radius: 4px; overflow: hidden; }

/* Header */
.bpa-header {
    padding: 14px 20px; border-bottom: 1px solid #dde3ec;
    display: flex; align-items: center; justify-content: space-between;
}
.bpa-header h2 { margin: 0; font-size: 16px; font-weight: 700; color: #1a1a2e; }

/* Bank selector */
.bpa-bank-bar {
    padding: 14px 20px; border-bottom: 1px solid #eef0f5;
    display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    background: #fafbfd;
}
.bpa-bank-bar label { font-size: 13px; font-weight: 700; color: #333; white-space: nowrap; }
.bpa-bank-bar select {
    border: 1px solid #c8d0db; border-radius: 4px; height: 36px;
    padding: 0 10px; font-size: 13px; color: #333; min-width: 280px;
}
.bpa-bank-bar select:focus { border-color: #1a6fc4; outline: none; }

/* Table */
.bpa-table-wrap { padding: 16px 20px; }
table.bpa-table { width: 100%; border-collapse: collapse; font-size: 13px; }
table.bpa-table thead tr { background: #1a6fc4; color: #fff; }
table.bpa-table thead th {
    padding: 10px 12px; font-weight: 600; font-size: 12px;
    text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; border: none;
}
table.bpa-table thead th.num { text-align: right; }
table.bpa-table thead th.req::after { content: ' *'; color: #ffcccc; }

table.bpa-table tbody tr { border-bottom: 1px solid #eef0f5; }
table.bpa-table tbody td { padding: 7px 8px; vertical-align: middle; }

table.bpa-table input[type=date],
table.bpa-table input[type=text],
table.bpa-table input[type=number],
table.bpa-table select {
    border: 1px solid #c8d0db; border-radius: 4px; height: 34px;
    padding: 0 8px; font-size: 13px; color: #333; width: 100%; background: #fff;
}
table.bpa-table input:focus, table.bpa-table select:focus { border-color: #1a6fc4; outline: none; }
table.bpa-table input[type=number] { text-align: right; }
table.bpa-table .td-del { text-align: center; width: 36px; }
table.bpa-table .btn-del-row {
    background: none; border: none; color: #c0392b; font-size: 16px; cursor: pointer; padding: 4px;
}
table.bpa-table .btn-del-row:hover { color: #a93226; }

/* Totals table */
.bpa-totals-wrap { padding: 0 20px 10px; }
table.bpa-totals-tbl {
    width: 100%; border-collapse: collapse; font-size: 13px;
    margin-top: 0;
}
table.bpa-totals-tbl td {
    padding: 8px 12px; font-weight: 700; color: #1a3a5c;
    text-align: right; border-top: 2px solid #1a6fc4;
    background: #f0f4ff;
}
table.bpa-totals-tbl td:first-child { text-align: left; }

/* Add more */
.bpa-add-more {
    padding: 10px 20px 16px;
}
.btn-add-more {
    background: none; border: 1px dashed #1a6fc4; color: #1a6fc4;
    border-radius: 20px; padding: 6px 18px; font-size: 13px; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-add-more:hover { background: #eef4fb; }

/* Footer actions */
.bpa-footer {
    padding: 14px 20px; border-top: 1px solid #dde3ec;
    display: flex; align-items: center; justify-content: space-between;
}
.btn-bpa-cancel {
    background: #fff; color: #555; border: 1px solid #c8d0db; border-radius: 20px;
    padding: 7px 22px; font-size: 13px; cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-bpa-cancel:hover { background: #f5f7fa; color: #333; text-decoration: none; }
.btn-bpa-approve {
    background: #27ae60; color: #fff; border: none; border-radius: 20px;
    padding: 8px 24px; font-size: 13px; font-weight: 700; cursor: pointer;
    display: inline-flex; align-items: center; gap: 8px;
}
.btn-bpa-approve:hover { background: #219150; }
</style>

<div class="content-wrapper" style="min-height: 946px; background: #f0f2f5;">
    <section class="content-header">
        <h1><i class="fa fa-university"></i> Add Bank Payment</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li><a href="<?php echo site_url('accounts/bank_payments'); ?>">Bank Payments</a></li>
            <li class="active">Add Bank Payment</li>
        </ol>
    </section>

    <section class="content" style="padding: 0 10px 10px;">
        <?php if ($this->session->flashdata('msg')): ?>
            <div style="padding: 10px 0 0;"><?php echo $this->session->flashdata('msg'); ?></div>
        <?php endif; ?>

        <div class="bpa-wrap">
            <div class="bpa-card">

                <div class="bpa-header">
                    <h2><i class="fa fa-university" style="color:#1a6fc4;margin-right:6px;"></i> Add Bank Payment</h2>
                </div>

                <form method="post" action="<?php echo site_url('accounts/save_bank_payment'); ?>" id="bpa-form">
                    <?php echo $this->customlib->getCSRF(); ?>

                    <!-- Bank selector -->
                    <div class="bpa-bank-bar">
                        <label>Bank <span style="color:#e74c3c;">*</span></label>
                        <select name="bank_id" id="bpa-bank" required>
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
                    <div class="bpa-table-wrap">
                        <table class="bpa-table" id="bpa-lines-table">
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
                            <tbody id="bpa-tbody">
                                <!-- rows injected by JS -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Totals -->
                    <div class="bpa-totals-wrap">
                        <table class="bpa-totals-tbl">
                            <tr>
                                <td>Total</td>
                                <td id="bpa-total-display">0.00</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Add more -->
                    <div class="bpa-add-more">
                        <button type="button" class="btn-add-more" onclick="bpaAddRow()">
                            <i class="fa fa-plus-circle"></i> + Add More
                        </button>
                    </div>

                    <!-- Footer -->
                    <div class="bpa-footer">
                        <a href="<?php echo site_url('accounts/bank_payments'); ?>" class="btn-bpa-cancel">
                            Cancel
                        </a>
                        <button type="submit" class="btn-bpa-approve">
                            <i class="fa fa-check-circle"></i> Approve and Close
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </section>
</div>

<script>
var bpaAccounts = <?php
    $accs = [];
    foreach ($nominal_accounts as $a) {
        $accs[] = ['id' => (int)$a['id'], 'name' => $a['account_name'], 'number' => $a['account_number']];
    }
    echo json_encode($accs);
?>;

var bpaRowCount = 0;
var today = '<?php echo date('Y-m-d'); ?>';

function bpaAddRow() {
    var idx = bpaRowCount++;
    var opts = '<option value="">— Select Account —</option>';
    bpaAccounts.forEach(function(a) {
        opts += '<option value="' + a.id + '">' + escHtml(a.name + ' (' + a.number + ')') + '</option>';
    });

    var tr = document.createElement('tr');
    tr.id  = 'bpa-row-' + idx;
    tr.innerHTML =
        '<td><input type="date" name="lines[' + idx + '][date]" value="' + today + '" required></td>' +
        '<td><input type="text" name="lines[' + idx + '][ref_no]" placeholder="Ref No."></td>' +
        '<td>' +
            '<select name="lines[' + idx + '][mode]">' +
                '<option value="Cash">Cash</option>' +
                '<option value="Cheque">Cheque</option>' +
                '<option value="Transfer">Transfer</option>' +
                '<option value="Online">Online</option>' +
            '</select>' +
        '</td>' +
        '<td><select name="lines[' + idx + '][nominal_id]" required onchange="bpaRecalc()">' + opts + '</select></td>' +
        '<td><input type="text" name="lines[' + idx + '][details]" placeholder="Narration / details"></td>' +
        '<td><input type="number" name="lines[' + idx + '][amount]" value="" min="0.01" step="0.01" placeholder="0.00" required oninput="bpaRecalc()"></td>' +
        '<td class="td-del"><button type="button" class="btn-del-row" onclick="bpaDelRow(' + idx + ')" title="Remove"><i class="fa fa-trash"></i></button></td>';

    document.getElementById('bpa-tbody').appendChild(tr);
    bpaRecalc();
}

function bpaDelRow(idx) {
    var tr = document.getElementById('bpa-row-' + idx);
    if (tr) tr.parentNode.removeChild(tr);
    bpaRecalc();
}

function bpaRecalc() {
    var inputs = document.querySelectorAll('#bpa-tbody input[type=number]');
    var total  = 0;
    inputs.forEach(function(inp) { total += parseFloat(inp.value) || 0; });
    document.getElementById('bpa-total-display').textContent = total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

document.getElementById('bpa-form').addEventListener('submit', function(e) {
    var bank = document.getElementById('bpa-bank').value;
    if (!bank) { alert('Please select a Bank / Cash account.'); e.preventDefault(); return; }
    var rows = document.querySelectorAll('#bpa-tbody tr');
    if (rows.length === 0) { alert('Please add at least one payment line.'); e.preventDefault(); return; }
    var total = 0;
    document.querySelectorAll('#bpa-tbody input[type=number]').forEach(function(i){ total += parseFloat(i.value)||0; });
    if (total <= 0) { alert('Total amount must be greater than zero.'); e.preventDefault(); return; }
});

// Start with one empty row
bpaAddRow();
</script>
