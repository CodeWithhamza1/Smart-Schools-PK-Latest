<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>
.jn-wrapper {
    background: #f4f6f9;
    min-height: 100vh;
    padding: 20px;
    font-family: 'Helvetica Neue', Arial, sans-serif;
}
.jn-card {
    background: #fff;
    border-radius: 4px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.12);
    padding: 0;
    margin-bottom: 20px;
}
.jn-header {
    padding: 18px 24px 10px;
    border-bottom: 1px solid #e8e8e8;
}
.jn-header h2 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}
.jn-meta {
    display: flex;
    align-items: center;
    gap: 30px;
    padding: 14px 24px;
    border-bottom: 1px solid #e8e8e8;
}
.jn-meta .form-group {
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.jn-meta label {
    font-weight: 600;
    font-size: 13px;
    color: #333;
    white-space: nowrap;
    margin: 0;
}
.jn-meta label .req { color: #e74c3c; }
.jn-meta input {
    border: 1px solid #ccc;
    border-radius: 3px;
    padding: 5px 10px;
    font-size: 13px;
    height: 32px;
}
.jn-meta input#jn_date { width: 140px; }
.jn-meta input#jn_ref   { width: 200px; }
.jn-meta-right { margin-left: auto; }

/* Table */
.jn-table-wrap { overflow-x: auto; }
table.jn-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
table.jn-table col.col-account     { width: 25%; }
table.jn-table col.col-description { width: 45%; }
table.jn-table col.col-amount      { width: 13%; }
table.jn-table col.col-action      { width: 4%; }

table.jn-table thead tr {
    background: #3da8e0;
    color: #fff;
}
table.jn-table thead th {
    padding: 10px 12px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    text-align: left;
}
table.jn-table thead th.text-right { text-align: right; }

table.jn-table tbody tr {
    border-bottom: 1px solid #e8e8e8;
}
table.jn-table tbody td {
    padding: 6px 8px;
    vertical-align: middle;
}
table.jn-table tbody td.td-amount input {
    text-align: right;
}

/* select2 inside table */
table.jn-table .select2-container {
    width: 100% !important;
}
table.jn-table .select2-selection--single {
    height: 30px !important;
    border-radius: 3px !important;
    border-color: #ccc !important;
}
table.jn-table .select2-selection__rendered {
    line-height: 28px !important;
    font-size: 13px;
}
table.jn-table .select2-selection__arrow { height: 30px !important; }

table.jn-table input.form-control {
    height: 30px;
    font-size: 13px;
    padding: 3px 8px;
    border-radius: 3px;
}
td.td-delete {
    text-align: center;
}
td.td-delete button {
    background: none;
    border: none;
    color: #3da8e0;
    font-size: 16px;
    cursor: pointer;
    padding: 2px 6px;
}
td.td-delete button:hover { color: #e74c3c; }

/* Totals row */
table.jn-total-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
table.jn-total-table col.col-account     { width: 25%; }
table.jn-total-table col.col-description { width: 45%; }
table.jn-total-table col.col-amount      { width: 13%; }
table.jn-total-table col.col-action      { width: 4%; }
.jn-total-row td {
    background: #e8e8e8;
    font-weight: 700;
    font-size: 13px;
    padding: 8px 12px;
    text-align: right;
    border-top: 2px solid #ccc;
}
.jn-total-row td.total-label { text-align: right; color: #555; }

/* Footer buttons */
.jn-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 24px;
    border-top: 1px solid #e8e8e8;
}
.btn-add-row {
    background: none;
    border: 1px solid #3da8e0;
    color: #3da8e0;
    border-radius: 20px;
    padding: 5px 16px;
    font-size: 13px;
    cursor: pointer;
    transition: background .15s;
}
.btn-add-row:hover { background: #eaf5fb; }

.btn-cancel {
    background: none;
    border: 1px solid #aaa;
    color: #555;
    border-radius: 20px;
    padding: 5px 18px;
    font-size: 13px;
    cursor: pointer;
}
.btn-approve {
    background: #2ecc71;
    border: none;
    color: #fff;
    border-radius: 20px;
    padding: 7px 22px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
}
.btn-approve:hover { background: #27ae60; }
.btn-approve .caret-btn {
    background: rgba(0,0,0,0.15);
    border-radius: 0 20px 20px 0;
    padding: 7px 10px;
    margin: -7px -22px -7px 6px;
    cursor: pointer;
}

.balance-warn {
    color: #e74c3c;
    font-size: 12px;
    font-weight: 600;
    display: none;
    padding: 4px 24px;
}
</style>

<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-book"></i> Journal New</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>accounts/all_accounts">Chart of Account</a></li>
            <li class="active">Journal New</li>
        </ol>
    </section>

    <section class="content">
        <?php if ($this->session->flashdata('msg')): ?>
            <?php echo $this->session->flashdata('msg'); ?>
        <?php endif; ?>

        <div class="jn-card">
            <div class="jn-header">
                <h2>Add Journal</h2>
            </div>

            <form id="journal-new-form" action="<?php echo site_url('accounts/save_journal_new'); ?>" method="post">
                <?php echo $this->customlib->getCSRF(); ?>

                <!-- Meta row: Date + Ref No -->
                <div class="jn-meta">
                    <div class="form-group">
                        <label>Date <span class="req">*</span></label>
                        <input type="date" id="jn_date" name="jn_date" value="<?php echo date('Y-m-d'); ?>"
                               required class="form-control" />
                    </div>
                    <div class="form-group jn-meta-right">
                        <label>Ref. No.</label>
                        <input type="text" id="jn_ref" name="jn_ref" placeholder="Ref_No" class="form-control" />
                    </div>
                </div>

                <!-- Journal lines table -->
                <div class="jn-table-wrap" style="padding: 0 0 0 0;">
                    <table class="jn-table" id="jn-lines-table">
                        <colgroup>
                            <col class="col-account">
                            <col class="col-description">
                            <col class="col-amount">
                            <col class="col-amount">
                            <col class="col-action">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Nominal Account</th>
                                <th>Description</th>
                                <th class="text-right">Debit</th>
                                <th class="text-right">Credit</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="jn-lines-body">
                            <!-- rows injected by JS -->
                        </tbody>
                    </table>

                    <!-- Totals — separate table so DataTables cannot move it -->
                    <table class="jn-total-table">
                        <colgroup>
                            <col class="col-account">
                            <col class="col-description">
                            <col class="col-amount">
                            <col class="col-amount">
                            <col class="col-action">
                        </colgroup>
                        <tbody>
                            <tr class="jn-total-row">
                                <td class="total-label" colspan="2">Total</td>
                                <td id="jn-total-debit">0.00</td>
                                <td id="jn-total-credit">0.00</td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="balance-warn" id="balance-warn">
                    &#9888; Debit and Credit totals must be equal before approving.
                </div>

                <div class="jn-footer">
                    <button type="button" class="btn-add-row" id="btn-add-row">+ Add More</button>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <a href="<?php echo base_url(); ?>accounts/journal_entries" class="btn-cancel">Cancel</a>
                        <button type="submit" class="btn-approve" id="btn-approve">
                            Approve and Close
                            <span class="caret-btn"><i class="fa fa-chevron-down"></i></span>
                        </button>
                    </div>
                </div>

            </form><!-- /form -->
        </div><!-- /jn-card -->
    </section>
</div>

<script>
// Accounts list from PHP
var jnAccounts = <?php echo json_encode(array_map(function($a){
    return ['id' => $a['id'], 'text' => $a['account_name'] . ' (' . $a['account_number'] . ')'];
}, $accounts)); ?>;

var jnRowIndex = 0;

function jnBuildRow(idx) {
    var optionsHtml = '<option value=""></option>';
    $.each(jnAccounts, function(i, acc) {
        optionsHtml += '<option value="' + acc.id + '">' + $('<div/>').text(acc.text).html() + '</option>';
    });
    var row = '<tr id="jn-row-' + idx + '" data-idx="' + idx + '">' +
        '<td>' +
            '<select name="lines[' + idx + '][account_id]" class="jn-account-select" data-idx="' + idx + '">' +
            optionsHtml +
            '</select>' +
        '</td>' +
        '<td><input type="text" name="lines[' + idx + '][description]" class="form-control" placeholder="Description" /></td>' +
        '<td class="td-amount"><input type="number" step="0.01" min="0" name="lines[' + idx + '][debit]" class="form-control jn-debit" data-idx="' + idx + '" value="0.00" /></td>' +
        '<td class="td-amount"><input type="number" step="0.01" min="0" name="lines[' + idx + '][credit]" class="form-control jn-credit" data-idx="' + idx + '" value="0.00" /></td>' +
        '<td class="td-delete"><button type="button" class="jn-del-row" data-idx="' + idx + '" title="Remove row"><i class="fa fa-trash"></i></button></td>' +
    '</tr>';
    return row;
}

function jnAddRow() {
    var idx = jnRowIndex++;
    $('#jn-lines-body').append(jnBuildRow(idx));
    // init select2 on the new select
    $('#jn-lines-body tr[data-idx="' + idx + '"] .jn-account-select').select2({
        placeholder: 'Select account...',
        allowClear: true,
        width: '100%'
    });
    jnRecalcTotals();
}

function jnRecalcTotals() {
    var totalDebit = 0, totalCredit = 0;
    $('.jn-debit').each(function() { totalDebit  += parseFloat($(this).val()) || 0; });
    $('.jn-credit').each(function(){ totalCredit += parseFloat($(this).val()) || 0; });
    $('#jn-total-debit').text(totalDebit.toFixed(2));
    $('#jn-total-credit').text(totalCredit.toFixed(2));
    if (Math.abs(totalDebit - totalCredit) > 0.001 && (totalDebit > 0 || totalCredit > 0)) {
        $('#balance-warn').show();
    } else {
        $('#balance-warn').hide();
    }
}

$(document).ready(function() {
    // Start with 2 blank rows
    jnAddRow();
    jnAddRow();

    // Add row button
    $('#btn-add-row').on('click', function() {
        jnAddRow();
    });

    // Delete row
    $(document).on('click', '.jn-del-row', function() {
        var idx = $(this).data('idx');
        $('#jn-row-' + idx).remove();
        jnRecalcTotals();
    });

    // Live total recalc
    $(document).on('input change', '.jn-debit, .jn-credit', function() {
        jnRecalcTotals();
    });

    // Form submit validation
    $('#journal-new-form').on('submit', function(e) {
        var totalDebit  = 0;
        var totalCredit = 0;
        var hasLine = false;

        $('.jn-debit').each(function()  { totalDebit  += parseFloat($(this).val()) || 0; });
        $('.jn-credit').each(function() { totalCredit += parseFloat($(this).val()) || 0; });

        // Check at least one line has an account and a non-zero amount
        var valid = true;
        $('.jn-account-select').each(function() {
            if ($(this).val()) { hasLine = true; }
        });

        if (!hasLine) {
            e.preventDefault();
            alert('Please add at least one journal line with a nominal account.');
            return false;
        }
        if (Math.abs(totalDebit - totalCredit) > 0.001) {
            e.preventDefault();
            $('#balance-warn').show();
            alert('Debit total (' + totalDebit.toFixed(2) + ') does not equal Credit total (' + totalCredit.toFixed(2) + '). Please balance the journal before approving.');
            return false;
        }
        if (totalDebit === 0) {
            e.preventDefault();
            alert('Journal total cannot be zero.');
            return false;
        }
    });
});
</script>
