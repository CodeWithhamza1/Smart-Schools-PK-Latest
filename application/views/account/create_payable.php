<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>
    .dataTables_filter,.dt-buttons{
        display: none;
    }
    .payable-row {
        margin-bottom: 10px;
    }
    .remove-row {
        color: #d9534f;
        cursor: pointer;
    }
    .remove-row:hover {
        color: #c9302c;
    }
    .add-row-icon:hover {
        color: #449d44;
    }
</style>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-file-text-o"></i> Create Payable </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <?php if ($this->session->flashdata('msg')) { ?>
                    <?php echo $this->session->flashdata('msg') ?>
                <?php } ?>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-plus"></i> Create Payable Voucher</h3>
                    </div>
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('accounts/store_payable') ?>" method="post" id="payableForm">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label>Date (Month-Year)</label>
                                        <small class="req"> *</small>
                                        <input type="month" 
                                               value="<?php echo (isset($payable) && $payable && isset($payable->payable_date)) ? date('Y-m', strtotime($payable->payable_date)) : date('Y-m'); ?>" 
                                               required 
                                               id="payable_date" 
                                               name="payable_date" 
                                               class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label>Payable Number</label>
                                        <small class="req"> *</small>
                                        <input type="text" 
                                               required 
                                               readonly 
                                               id="payable_number"
                                               value="<?php
                                               if(isset($payable) && $payable && isset($payable->payable_number) && $payable->payable_number) {
                                                   echo $payable->payable_number;
                                               } else {
                                                   echo isset($payable_number) ? $payable_number : '';
                                               }
                                               ?>" 
                                               name="payable_number" 
                                               class="form-control"/>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label>Narration</label>
                                        <small class="req"> *</small>
                                        <input type="text" 
                                               required 
                                               id="payable_narration" 
                                               name="payable_narration" 
                                               value="<?php echo (isset($payable) && $payable && isset($payable->payable_narration)) ? $payable->payable_narration : ''; ?>" 
                                               class="form-control" 
                                               placeholder="Enter narration"/>
                                    </div>
                                </div>
                            </div>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <h4>Payable Entries</h4>
                                    <table class="table table-bordered table-striped" id="payableEntriesTable">
                                        <thead>
                                            <tr>
                                                <th style="width: 20%">Debit Account</th>
                                                <th style="width: 20%">Credit Account</th>
                                                <th style="width: 25%">Description</th>
                                                <th style="width: 12%">Amount (<?php echo $currency_symbol; ?>)</th>
                                                <th style="width: 5%">Select</th>
                                                <th style="width: 12%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="payableEntriesBody">
                                            <?php
                                            $payable_entries = array();
                                            if (isset($payable) && $payable && isset($payable->payable_entry)) {
                                                // Try to decode JSON, handle both empty string and valid JSON
                                                if (!empty($payable->payable_entry) && $payable->payable_entry !== 'null') {
                                                    $decoded = json_decode($payable->payable_entry, true);
                                                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                        $payable_entries = $decoded;
                                                    }
                                                }
                                            }
                                            
                                            // Display existing entries or one empty row
                                            if (empty($payable_entries)) {
                                                ?>
                                                <tr class="payable-row">
                                                    <td>
                                                        <select name="debit_account_id[]" class="form-control select2 debit-account-select" required>
                                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                            <?php foreach ($expense_accounts as $account) { ?>
                                                                <option value="<?php echo $account['id'] ?>"><?php echo $account['account_name'] . " (" . $account['account_number'] . ")" ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="credit_account_id[]" class="form-control select2 credit-account-select" required>
                                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                            <?php foreach ($liability_accounts as $account) { ?>
                                                                <option value="<?php echo $account['id'] ?>"><?php echo $account['account_name'] . " (" . $account['account_number'] . ")" ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" 
                                                               name="description[]" 
                                                               class="form-control description-input" 
                                                               placeholder="e.g., Electricity Bill, Rent, etc." 
                                                               required/>
                                                    </td>
                                                    <td>
                                                        <input type="number" 
                                                               step="0.01" 
                                                               min="0" 
                                                               name="amount[]" 
                                                               class="form-control amount-input" 
                                                               placeholder="0.00" 
                                                               required/>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="checkbox" 
                                                               name="selected[0]" 
                                                               value="1" 
                                                               class="selected-checkbox"/>
                                                    </td>
                                                    <td class="text-center">
                                                        <i class="fa fa-plus add-row-icon" style="cursor: pointer; color: #5cb85c; margin-right: 10px;" title="Add Row"></i>
                                                        <i class="fa fa-times remove-row" style="cursor: pointer; color: #d9534f;" title="Remove Row"></i>
                                                    </td>
                                                </tr>
                                                <?php
                                            } else {
                                                // Use sequential index starting from 0 to match form submission
                                                $row_index = 0;
                                                foreach ($payable_entries as $entry) {
                                                    ?>
                                                    <tr class="payable-row">
                                                        <td>
                                                            <select name="debit_account_id[]" class="form-control select2 debit-account-select" required>
                                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                                <?php foreach ($expense_accounts as $account) { ?>
                                                                    <option value="<?php echo $account['id'] ?>" <?php echo (isset($entry['debit_account_id']) && $entry['debit_account_id'] == $account['id']) || (isset($entry['account_id']) && $entry['account_id'] == $account['id']) ? 'selected' : ''; ?>>
                                                                        <?php echo $account['account_name'] . " (" . $account['account_number'] . ")" ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <select name="credit_account_id[]" class="form-control select2 credit-account-select" required>
                                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                                <?php foreach ($liability_accounts as $account) { ?>
                                                                    <option value="<?php echo $account['id'] ?>" <?php echo (isset($entry['credit_account_id']) && $entry['credit_account_id'] == $account['id']) ? 'selected' : ''; ?>>
                                                                        <?php echo $account['account_name'] . " (" . $account['account_number'] . ")" ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="text" 
                                                                   name="description[]" 
                                                                   class="form-control description-input" 
                                                                   value="<?php echo isset($entry['description']) ? htmlspecialchars($entry['description']) : (isset($entry['account_name']) ? htmlspecialchars($entry['account_name']) : ''); ?>" 
                                                                   placeholder="e.g., Electricity Bill, Rent, etc." 
                                                                   required/>
                                                        </td>
                                                        <td>
                                                            <input type="number" 
                                                                   step="0.01" 
                                                                   min="0" 
                                                                   name="amount[]" 
                                                                   class="form-control amount-input" 
                                                                   value="<?php echo isset($entry['amount']) ? $entry['amount'] : ''; ?>" 
                                                                   placeholder="0.00" 
                                                                   required/>
                                                        </td>
                                                        <td class="text-center">
                                                            <input type="checkbox" 
                                                                   name="selected[<?php echo $row_index; ?>]" 
                                                                   value="1" 
                                                                   class="selected-checkbox" 
                                                                   <?php echo (isset($entry['selected']) && $entry['selected']) ? 'checked' : ''; ?>/>
                                                        </td>
                                                        <td class="text-center">
                                                            <i class="fa fa-plus add-row-icon" style="cursor: pointer; color: #5cb85c; margin-right: 10px;" title="Add Row"></i>
                                                            <i class="fa fa-times remove-row" style="cursor: pointer; color: #d9534f;" title="Remove Row"></i>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    $row_index++;
                                                }
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa fa-save"></i> Save
                                    </button>
                                    <?php if(isset($payable) && $payable && isset($payable->id) && $payable->id) { ?>
                                    <button type="button" class="btn btn-success" id="postPayableBtn" form="payableForm">
                                        <i class="fa fa-check"></i> Post to Journal
                                    </button>
                                    <?php } ?>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php if(isset($payable) && $payable && isset($payable->id) && $payable->id && !empty($payable_entries)) { 
                // Calculate totals for all entries and selected entries
                $total_all_amount = 0;
                $total_selected_amount = 0;
                $selected_count = 0;
                foreach ($payable_entries as $entry) {
                    $amount = floatval($entry['amount']);
                    $total_all_amount += $amount;
                    if (isset($entry['selected']) && $entry['selected']) {
                        $total_selected_amount += $amount;
                        $selected_count++;
                    }
                }
            ?>
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> Saved Payable Entries</h3>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Description</th>
                                        <th>Amount (<?php echo $currency_symbol; ?>)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    if (!empty($payable_entries)) {
                                        foreach ($payable_entries as $entry) { 
                                            $is_selected = isset($entry['selected']) && $entry['selected'];
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(isset($entry['description']) ? $entry['description'] : (isset($entry['account_name']) ? $entry['account_name'] : '')); ?></td>
                                        <td><b><?php echo number_format($entry['amount'], 2); ?></b></td>
                                        <td>
                                            <?php if ($is_selected) { ?>
                                                <span class="label label-success">Selected</span>
                                            <?php } else { ?>
                                                <span class="label label-default">Not Selected</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                    <?php 
                                        }
                                    } else {
                                    ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No entries saved yet.</td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th class="text-right">Total (All Entries):</th>
                                        <th><?php echo number_format($total_all_amount, 2); ?></th>
                                        <th></th>
                                    </tr>
                                    <?php if ($selected_count > 0) { ?>
                                    <tr>
                                        <th class="text-right">Total (Selected Entries):</th>
                                        <th class="text-success"><strong><?php echo number_format($total_selected_amount, 2); ?></strong></th>
                                        <th><span class="label label-info"><?php echo $selected_count; ?> selected</span></th>
                                    </tr>
                                    <?php } ?>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </section>
</div>

<script>

// Check if jQuery is loaded
if (typeof jQuery === 'undefined') {
    console.error('jQuery is not loaded!');
    alert('jQuery is not loaded. Please refresh the page.');
} else {
}

jQuery(document).ready(function ($) {
    
    // Initialize select2 for account dropdowns
    if (typeof $.fn.select2 !== 'undefined') {
        $('.select2').select2();
    } else {
        console.warn('Select2 is not available');
    }
    
    // Calculate initial row index based on existing rows
    var rowIndex = $('#payableEntriesBody tr').length;
    if (rowIndex === 0) rowIndex = 1;
    
    // Test if icons exist
    
    // Add new row - triggered by plus icon
    $(document).on('click', '.add-row-icon', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            var debitAccountOptions = '';
            var creditAccountOptions = '';
            
            <?php if (!empty($expense_accounts)) { ?>
            <?php foreach ($expense_accounts as $account) { ?>
            debitAccountOptions += '<option value="<?php echo $account['id'] ?>"><?php echo addslashes($account['account_name'] . " (" . $account['account_number'] . ")"); ?></option>';
            <?php } ?>
            <?php } ?>
            
            <?php if (!empty($liability_accounts)) { ?>
            <?php foreach ($liability_accounts as $account) { ?>
            creditAccountOptions += '<option value="<?php echo $account['id'] ?>"><?php echo addslashes($account['account_name'] . " (" . $account['account_number'] . ")"); ?></option>';
            <?php } ?>
            <?php } ?>
            
            
            if (!debitAccountOptions) {
                alert('No expense accounts available. Please add expense accounts first.');
                console.warn('No expense accounts available');
                return false;
            }
            
            if (!creditAccountOptions) {
                alert('No liability accounts available. Please add liability accounts first.');
                console.warn('No liability accounts available');
                return false;
            }
            
            var rowHtml = '<tr class="payable-row">' +
                '<td>' +
                    '<select name="debit_account_id[]" class="form-control select2 debit-account-select" required>' +
                        '<option value=""><?php echo $this->lang->line('select'); ?></option>' +
                        debitAccountOptions +
                    '</select>' +
                '</td>' +
                '<td>' +
                    '<select name="credit_account_id[]" class="form-control select2 credit-account-select" required>' +
                        '<option value=""><?php echo $this->lang->line('select'); ?></option>' +
                        creditAccountOptions +
                    '</select>' +
                '</td>' +
                '<td>' +
                    '<input type="text" name="description[]" class="form-control description-input" placeholder="e.g., Electricity Bill, Rent, etc." required/>' +
                '</td>' +
                '<td>' +
                    '<input type="number" step="0.01" min="0" name="amount[]" class="form-control amount-input" placeholder="0.00" required/>' +
                '</td>' +
                '<td class="text-center">' +
                    '<input type="checkbox" name="selected[' + rowIndex + ']" value="1" class="selected-checkbox"/>' +
                '</td>' +
                '<td class="text-center">' +
                    '<i class="fa fa-plus add-row-icon" style="cursor: pointer; color: #5cb85c; margin-right: 10px;" title="Add Row"></i>' +
                    '<i class="fa fa-times remove-row" style="cursor: pointer; color: #d9534f;" title="Remove Row"></i>' +
                '</td>' +
            '</tr>';
            
            var newRow = $(rowHtml);
            
            $('#payableEntriesBody').append(newRow);
            
            // Initialize select2 only for the newly added row
            if (typeof $.fn.select2 !== 'undefined') {
                newRow.find('.select2').select2();
            }
            
            rowIndex++;
        } catch(err) {
            alert('Error adding new row: ' + err.message);
        }
    });
    
    // Remove row
    $(document).on('click', '.remove-row', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        try {
            var currentRowCount = $('#payableEntriesBody tr').length;
            
            if (currentRowCount > 1) {
                var rowToRemove = $(this).closest('tr');
                rowToRemove.remove();
            } else {
                alert('At least one row is required');
            }
        } catch(err) {
            console.error('=== ERROR REMOVING ROW ===');
            console.error('Error details:', err);
            console.error('Error stack:', err.stack);
            alert('Error removing row: ' + err.message);
        }
    });
    
    // Test click handlers are attached
    setTimeout(function() {        
        // Test if handlers are attached
        var addIconEvents = $._data($('.add-row-icon').first()[0], 'events');
        var removeIconEvents = $._data($('.remove-row').first()[0], 'events');

    }, 1000);
    
    // Post Payable Button - validate and submit
    $('#postPayableBtn').on('click', function(e) {
        e.preventDefault();
        
        // Check if at least one checkbox is checked
        var hasSelected = false;
        var selectedRows = [];
        
        $('#payableEntriesBody tr').each(function(index) {
            var checkbox = $(this).find('.selected-checkbox');
            if (checkbox.is(':checked')) {
                hasSelected = true;
                var debitAccount = $(this).find('.debit-account-select').val();
                var creditAccount = $(this).find('.credit-account-select').val();
                var description = $(this).find('.description-input').val();
                var amount = $(this).find('.amount-input').val();
                
                if (!debitAccount) {
                    alert('Please select a debit account for entry: ' + (description || 'Row ' + (index + 1)));
                    hasSelected = false;
                    return false; // break loop
                }
                
                if (!creditAccount) {
                    alert('Please select a credit account for entry: ' + (description || 'Row ' + (index + 1)));
                    hasSelected = false;
                    return false; // break loop
                }
                
                selectedRows.push({
                    debit_account: debitAccount,
                    credit_account: creditAccount,
                    description: description,
                    amount: amount
                });
            }
        });
        
        if (!hasSelected || selectedRows.length === 0) {
            alert('Please select at least one entry with both debit and credit accounts to post to journal.');
            return false;
        }
        
        // Create a form and submit to post_payable
        var form = $('<form>', {
            'method': 'POST',
            'action': '<?php echo site_url('accounts/post_payable'); ?>'
        });
        
        // Add CSRF token
        <?php 
        $csrf_token = $this->customlib->getCSRF();
        $csrf_name = $this->security->get_csrf_token_name();
        $csrf_value = $this->security->get_csrf_hash();
        ?>
        form.append($('<input>', {
            'type': 'hidden',
            'name': '<?php echo $csrf_name; ?>',
            'value': '<?php echo $csrf_value; ?>'
        }));
        
        // Add account selections from selected rows
        selectedRows.forEach(function(row, index) {
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'entries[' + index + '][debit_account_id]',
                'value': row.debit_account
            }));
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'entries[' + index + '][credit_account_id]',
                'value': row.credit_account
            }));
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'entries[' + index + '][description]',
                'value': row.description
            }));
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'entries[' + index + '][amount]',
                'value': row.amount
            }));
        });
        
        // Append form to body and submit
        $('body').append(form);
        form.submit();
    });
    
    // Form validation before submit
    $('#payableForm').on('submit', function(e) {
        var hasValidEntry = false;
        $('#payableEntriesBody tr').each(function() {
            var description = $(this).find('.description-input').val();
            var amount = $(this).find('.amount-input').val();
            if (description && description.trim() !== '' && amount && parseFloat(amount) > 0) {
                hasValidEntry = true;
                return false; // break loop
            }
        });
        
        if (!hasValidEntry) {
            e.preventDefault();
            alert('Please add at least one valid entry (description and amount) before saving.');
            return false;
        }
    });
    
});
</script>


