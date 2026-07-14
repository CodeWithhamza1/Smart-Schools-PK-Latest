<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$voucher_entries = [];
if (isset($voucher) && isset($voucher->voucher_entry)) {
    $decoded = json_decode($voucher->voucher_entry);
    if (is_array($decoded)) { $voucher_entries = $decoded; }
}

// Build merged summary for display
function cv_extract_keys($narration) {
    $ft = $period = null;
    preg_match('/FeeType: ([^,]+)/', $narration, $fm);
    preg_match('/Period: ([^,]*)$/', $narration, $pm);
    if (isset($fm[1])) $ft = trim($fm[1]);
    if (isset($pm[1])) $period = trim($pm[1]);
    return [$ft, $period];
}
$merged = [];
foreach ($voucher_entries as $idx => $entry) {
    list($fee_type, $period) = cv_extract_keys($entry->voucher_narration ?? '');
    $key = $entry->debit_account_id.'_'.$entry->credit_account_id.'_'.$fee_type.'_'.$period;
    if (!isset($merged[$key])) {
        $merged[$key] = [
            'indices'              => [],
            'debit_account_name'   => $entry->debit_account_name,
            'debit_account_number' => $entry->debit_account_number,
            'credit_account_name'  => $entry->credit_account_name,
            'credit_account_number'=> $entry->credit_account_number,
            'fee_type'             => $fee_type,
            'period'               => $period,
            'debit_amount'         => 0,
            'credit_amount'        => 0,
            'students'             => [],
            'voucher_type'         => $entry->voucher_type ?? '',
        ];
    }
    $merged[$key]['indices'][] = $idx;
    preg_match('/Student: ([^ ]+) \(([^)]*)\)/', $entry->voucher_narration ?? '', $sm);
    $adm  = isset($sm[1]) ? $sm[1] : '-';
    $name = isset($sm[2]) ? $sm[2] : '-';
    $merged[$key]['students'][] = $adm.' ('.$name.')';
    $merged[$key]['debit_amount']  += $entry->debit_amount;
    $merged[$key]['credit_amount'] += $entry->credit_amount;
}
?>
<style>
/* ===== Create Voucher — Professional Layout ===== */
.cv-wrap { padding: 20px 24px; background: #f0f2f5; min-height: 600px; }
.cv-card { background: #fff; border: 1px solid #dde3ec; border-radius: 4px; overflow: hidden; margin-bottom: 18px; }

.cv-header {
    padding: 14px 20px; border-bottom: 1px solid #dde3ec;
    display: flex; align-items: center; justify-content: space-between;
}
.cv-header h2 { margin: 0; font-size: 16px; font-weight: 700; color: #1a1a2e; }

/* Entry form grid */
.cv-form-body { padding: 16px 20px; }
.cv-row { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 12px; }
.cv-field { display: flex; flex-direction: column; min-width: 0; }
.cv-field label { font-size: 12px; font-weight: 600; color: #555; margin-bottom: 4px; }
.cv-field label .req { color: #c0392b; }
.cv-field input[type=date],
.cv-field input[type=text],
.cv-field input[type=number],
.cv-field select {
    border: 1px solid #c8d0db; border-radius: 4px; height: 34px;
    padding: 0 8px; font-size: 13px; color: #333; background: #fff;
}
.cv-field input:focus, .cv-field select:focus { border-color: #1a6fc4; outline: none; }
.cv-field input[type=number] { text-align: right; }
.cv-field input[readonly] { background: #f5f7fa; color: #666; }
.cv-field .select2-container { height: 34px; }
.cv-field .select2-container .select2-selection--single {
    height: 34px; border: 1px solid #c8d0db; border-radius: 4px;
}
.cv-field .select2-container .select2-selection__rendered { line-height: 34px; font-size: 13px; }
.cv-field .select2-container .select2-selection__arrow { height: 34px; }

.cv-field-sm  { flex: 0 0 120px; }
.cv-field-md  { flex: 0 0 160px; }
.cv-field-lg  { flex: 1 1 200px; }
.cv-field-xl  { flex: 2 1 240px; }

.btn-cv-add {
    background: #1a6fc4; color: #fff; border: none; border-radius: 4px;
    height: 34px; padding: 0 20px; font-size: 13px; font-weight: 600;
    cursor: pointer; white-space: nowrap; align-self: flex-end; margin-bottom: 0;
}
.btn-cv-add:hover { background: #155da0; }

/* Draft table */
.cv-table-wrap { padding: 0 20px 16px; }
.cv-topbar {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 20px; border-top: 1px solid #eef0f5;
}
.cv-topbar-left { display: flex; gap: 8px; align-items: center; }

.btn-post-sel {
    background: #27ae60; color: #fff; border: none; border-radius: 20px;
    padding: 7px 20px; font-size: 13px; font-weight: 700; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-post-sel:hover { background: #219150; }
.btn-post-sel:disabled { background: #95d5b2; cursor: not-allowed; }

.btn-post-all {
    background: #fff; color: #27ae60; border: 1px solid #27ae60; border-radius: 20px;
    padding: 7px 20px; font-size: 13px; font-weight: 700; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-post-all:hover { background: #eafaf1; }

.btn-clear-draft {
    background: #fff; color: #c0392b; border: 1px solid #c0392b; border-radius: 20px;
    padding: 7px 16px; font-size: 13px; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px;
}
.btn-clear-draft:hover { background: #fdf0ed; }

table.cv-table { width: 100%; border-collapse: collapse; font-size: 13px; }
table.cv-table thead tr { background: #1a6fc4; color: #fff; }
table.cv-table thead th {
    padding: 9px 10px; font-weight: 600; font-size: 12px;
    text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; border: none;
}
table.cv-table thead th.num { text-align: right; }
table.cv-table thead th.ctr { text-align: center; }
table.cv-table tbody tr { border-bottom: 1px solid #eef0f5; }
table.cv-table tbody tr:hover { background: #f9fbff; }
table.cv-table tbody tr.cv-row-checked { background: #eafaf1; }
table.cv-table tbody td { padding: 7px 10px; vertical-align: middle; }
table.cv-table tbody td.num { text-align: right; font-weight: 600; }
table.cv-table tbody td.ctr { text-align: center; }

.cv-badge {
    display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 700;
    background: #e8f0fe; color: #1a6fc4;
}

.btn-del-entry {
    background: none; border: none; color: #c0392b; font-size: 14px; cursor: pointer; padding: 3px 6px;
}
.btn-del-entry:hover { color: #922b21; }

.cv-empty { padding: 28px 20px; text-align: center; color: #aaa; font-size: 13px; }

/* Totals footer */
.cv-totals {
    display: flex; justify-content: flex-end; gap: 32px;
    padding: 10px 20px; background: #f9f0ff; border-top: 2px solid #8e44ad;
    font-size: 13px; font-weight: 700; color: #5b2c6f;
}
.cv-totals span { color: #1a3a5c; }
</style>

<div class="content-wrapper" style="min-height: 946px; background: #f0f2f5;">
    <section class="content-header">
        <h1><i class="fa fa-file-text-o"></i> Create Voucher</h1>
        <ol class="breadcrumb">
            <li><a href="<?php echo base_url(); ?>"><i class="fa fa-home"></i> Home</a></li>
            <li class="active">Create Voucher</li>
        </ol>
    </section>

    <section class="content" style="padding: 0 10px 10px;">
        <?php if ($this->session->flashdata('msg')): ?>
            <div style="padding: 10px 0 0;"><?php echo $this->session->flashdata('msg'); ?></div>
        <?php endif; ?>
        <?php if ($this->session->flashdata('success')): ?>
            <div style="padding: 10px 0 0;"><div class="alert alert-success"><i class="fa fa-check"></i> <?php echo $this->session->flashdata('success'); ?></div></div>
        <?php endif; ?>

        <div class="cv-wrap">

            <!-- Entry Form Card -->
            <div class="cv-card">
                <div class="cv-header">
                    <h2><i class="fa fa-file-text-o" style="color:#1a6fc4; margin-right:6px;"></i> New Voucher Entry</h2>
                </div>

                <form method="post" action="<?php echo site_url('accounts/store_voucher'); ?>" id="cv-entry-form">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <div class="cv-form-body">

                        <!-- Row 1: Voucher meta -->
                        <div class="cv-row">
                            <div class="cv-field cv-field-md">
                                <label>Voucher Date <span class="req">*</span></label>
                                <input type="date" name="voucher_date" id="cv_date"
                                    value="<?php echo isset($voucher->voucher_date) && $voucher->voucher_date ? htmlspecialchars($voucher->voucher_date) : date('Y-m-d'); ?>"
                                    required>
                            </div>
                            <div class="cv-field cv-field-md">
                                <label>Voucher No.</label>
                                <input type="text" name="voucher_number" id="cv_number" readonly
                                    value="<?php echo isset($voucher->voucher_number) && $voucher->voucher_number ? htmlspecialchars($voucher->voucher_number) : htmlspecialchars($voucher_number); ?>">
                            </div>
                            <div class="cv-field cv-field-md">
                                <label>Voucher Type <span class="req">*</span></label>
                                <select name="voucher_type" id="cv_type" required>
                                    <option value="">— Select —</option>
                                    <?php foreach ($voucher_types as $vt): ?>
                                        <option value="<?php echo htmlspecialchars($vt['type']); ?>">
                                            <?php echo htmlspecialchars($vt['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="cv-field cv-field-xl">
                                <label>Narration <span class="req">*</span></label>
                                <input type="text" name="voucher_narration" id="cv_narration" required
                                    placeholder="Brief description of this voucher entry">
                            </div>
                        </div>

                        <!-- Row 2: Accounts + amounts + description -->
                        <div class="cv-row" style="align-items: flex-end;">
                            <div class="cv-field cv-field-xl">
                                <label>Debit Account <span class="req">*</span></label>
                                <select name="debit_account" id="cv_debit_acc" required class="cv-select2">
                                    <option value="">— Select Account —</option>
                                    <?php foreach ($accounts as $acc): ?>
                                        <option value="<?php echo $acc['id']; ?>">
                                            <?php echo htmlspecialchars($acc['account_name'].' ('.$acc['account_number'].')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="cv-field cv-field-md">
                                <label>Debit Amount <span class="req">*</span></label>
                                <input type="number" name="debit_amount" id="cv_debit_amt"
                                    min="0.01" step="0.01" placeholder="0.00" required>
                            </div>
                            <div class="cv-field cv-field-xl">
                                <label>Credit Account <span class="req">*</span></label>
                                <select name="credit_account" id="cv_credit_acc" required class="cv-select2">
                                    <option value="">— Select Account —</option>
                                    <?php foreach ($accounts as $acc): ?>
                                        <option value="<?php echo $acc['id']; ?>">
                                            <?php echo htmlspecialchars($acc['account_name'].' ('.$acc['account_number'].')'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="cv-field cv-field-md">
                                <label>Credit Amount</label>
                                <input type="number" name="credit_amount" id="cv_credit_amt"
                                    min="0.01" step="0.01" placeholder="0.00" readonly>
                            </div>
                            <div class="cv-field cv-field-xl">
                                <label>Note / Remarks</label>
                                <input type="text" name="description" id="cv_desc" placeholder="Optional remark">
                            </div>
                            <div class="cv-field" style="justify-content: flex-end;">
                                <button type="submit" class="btn-cv-add">
                                    <i class="fa fa-plus"></i> Add Entry
                                </button>
                            </div>
                        </div>

                    </div><!-- /cv-form-body -->
                </form>
            </div><!-- /entry card -->

            <!-- Draft Entries Card -->
            <div class="cv-card">
                <div class="cv-header">
                    <h2><i class="fa fa-list-alt" style="color:#8e44ad; margin-right:6px;"></i>
                        Draft Entries
                        <?php if (count($voucher_entries) > 0): ?>
                            <span style="font-size:12px; font-weight:400; color:#888; margin-left:8px;">(<?php echo count($voucher_entries); ?> line<?php echo count($voucher_entries) > 1 ? 's' : ''; ?>)</span>
                        <?php endif; ?>
                    </h2>
                    <?php if (isset($voucher->id) && $voucher->id && count($voucher_entries) > 0): ?>
                    <div style="display:flex; gap:8px; align-items:center;">
                        <button type="button" class="btn-post-sel" id="btn-post-sel" onclick="cvPostSelected()" disabled>
                            <i class="fa fa-check-circle"></i> Post Selected
                        </button>
                        <form method="post" action="<?php echo site_url('accounts/post_to_journal/'.$voucher->id); ?>" style="margin:0;" onsubmit="return confirm('Post ALL draft entries to journal?');">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <button type="submit" class="btn-post-all">
                                <i class="fa fa-check-circle"></i> Post All
                            </button>
                        </form>
                        <a href="<?php echo site_url('accounts/clear_draft'); ?>"
                           class="btn-clear-draft"
                           onclick="return confirm('Clear all draft entries? This cannot be undone.');">
                            <i class="fa fa-trash"></i> Clear Draft
                        </a>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if (count($merged) > 0): ?>
                <div class="cv-table-wrap">
                    <form method="post" action="<?php echo site_url('accounts/post_selected_entries'); ?>" id="cv-post-sel-form">
                        <?php echo $this->customlib->getCSRF(); ?>
                        <input type="hidden" name="voucher_id" value="<?php echo isset($voucher->id) ? $voucher->id : ''; ?>">
                        <table class="cv-table">
                            <thead>
                                <tr>
                                    <th class="ctr" style="width:36px;">
                                        <input type="checkbox" id="cv-chk-all" title="Select all" onclick="cvToggleAll(this)">
                                    </th>
                                    <th>Debit Account</th>
                                    <th>Debit Acc. No.</th>
                                    <th>Credit Account</th>
                                    <th>Credit Acc. No.</th>
                                    <th class="num">Total Debit (<?php echo htmlspecialchars($currency_symbol); ?>)</th>
                                    <th class="num">Total Credit (<?php echo htmlspecialchars($currency_symbol); ?>)</th>
                                    <th>Type</th>
                                    <th>Fee Type</th>
                                    <th>Period</th>
                                    <th>Students</th>
                                    <th class="ctr" style="width:40px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $grand_debit  = 0;
                            $grand_credit = 0;
                            foreach ($merged as $mkey => $g):
                                $grand_debit  += $g['debit_amount'];
                                $grand_credit += $g['credit_amount'];
                            ?>
                                <tr id="cv-mrow-<?php echo htmlspecialchars($mkey); ?>">
                                    <td class="ctr">
                                        <?php foreach ($g['indices'] as $ridx): ?>
                                            <input type="checkbox" name="entry_indices[]"
                                                value="<?php echo (int)$ridx; ?>"
                                                class="cv-row-chk"
                                                onchange="cvUpdateSelBtn()"
                                                checked>
                                        <?php endforeach; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($g['debit_account_name']); ?></td>
                                    <td><?php echo htmlspecialchars($g['debit_account_number']); ?></td>
                                    <td><?php echo htmlspecialchars($g['credit_account_name']); ?></td>
                                    <td><?php echo htmlspecialchars($g['credit_account_number']); ?></td>
                                    <td class="num"><?php echo number_format($g['debit_amount'], 2); ?></td>
                                    <td class="num"><?php echo number_format($g['credit_amount'], 2); ?></td>
                                    <td><span class="cv-badge"><?php echo htmlspecialchars($g['voucher_type'] ?: '—'); ?></span></td>
                                    <td><?php echo htmlspecialchars($g['fee_type'] ?: '—'); ?></td>
                                    <td><?php echo htmlspecialchars($g['period']   ?: '—'); ?></td>
                                    <td style="max-width:160px; font-size:12px; color:#666;">
                                        <?php echo htmlspecialchars(implode(', ', array_unique($g['students']))); ?>
                                    </td>
                                    <td class="ctr">
                                        <?php foreach ($g['indices'] as $ridx): ?>
                                        <a href="<?php echo site_url('accounts/delete_entry/'.$ridx); ?>"
                                           class="btn-del-entry"
                                           title="Remove entry"
                                           onclick="return confirm('Remove this entry from draft?');">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </form>
                </div>

                <div class="cv-totals">
                    <div><span>Total Debit:</span> <?php echo htmlspecialchars($currency_symbol); ?> <?php echo number_format($grand_debit, 2); ?></div>
                    <div><span>Total Credit:</span> <?php echo htmlspecialchars($currency_symbol); ?> <?php echo number_format($grand_credit, 2); ?></div>
                    <?php
                    $diff = abs($grand_debit - $grand_credit);
                    $balanced = $diff < 0.005;
                    ?>
                    <div style="color: <?php echo $balanced ? '#27ae60' : '#c0392b'; ?>">
                        <span>Difference:</span> <?php echo htmlspecialchars($currency_symbol); ?> <?php echo number_format($diff, 2); ?>
                        <?php if ($balanced): ?><i class="fa fa-check-circle" style="margin-left:4px;"></i><?php endif; ?>
                    </div>
                </div>

                <?php else: ?>
                <div class="cv-empty">
                    <i class="fa fa-inbox" style="font-size:28px; display:block; margin-bottom:8px; color:#ccc;"></i>
                    No draft entries yet. Add an entry above to get started.
                </div>
                <?php endif; ?>

            </div><!-- /draft entries card -->

        </div><!-- /cv-wrap -->
    </section>
</div>

<script>
$(document).ready(function () {
    // Initialize Select2 on account dropdowns
    $('#cv_debit_acc, #cv_credit_acc').select2({
        width: '100%',
        dropdownAutoWidth: false
    });

    // Mirror debit amount to credit amount
    $('#cv_debit_amt').on('input', function () {
        $('#cv_credit_amt').val($(this).val());
    });

    // Form validation before submit
    $('#cv-entry-form').on('submit', function (e) {
        var type    = $('#cv_type').val();
        var narr    = $.trim($('#cv_narration').val());
        var dAcc    = $('#cv_debit_acc').val();
        var cAcc    = $('#cv_credit_acc').val();
        var dAmt    = parseFloat($('#cv_debit_amt').val()) || 0;

        if (!type)  { alert('Please select a Voucher Type.'); e.preventDefault(); return; }
        if (!narr)  { alert('Please enter a Narration.'); e.preventDefault(); return; }
        if (!dAcc)  { alert('Please select a Debit Account.'); e.preventDefault(); return; }
        if (!cAcc)  { alert('Please select a Credit Account.'); e.preventDefault(); return; }
        if (dAcc === cAcc) { alert('Debit Account and Credit Account cannot be the same.'); e.preventDefault(); return; }
        if (dAmt <= 0) { alert('Amount must be greater than zero.'); e.preventDefault(); return; }
    });

    // Initialise selection state
    cvUpdateSelBtn();
});

function cvToggleAll(master) {
    document.querySelectorAll('.cv-row-chk').forEach(function (chk) {
        chk.checked = master.checked;
        chk.closest('tr').classList.toggle('cv-row-checked', master.checked);
    });
    cvUpdateSelBtn();
}

function cvUpdateSelBtn() {
    var anyChecked = document.querySelectorAll('.cv-row-chk:checked').length > 0;
    var btn = document.getElementById('btn-post-sel');
    if (btn) btn.disabled = !anyChecked;

    // Sync "select all" master checkbox indeterminate state
    var all   = document.querySelectorAll('.cv-row-chk').length;
    var chked = document.querySelectorAll('.cv-row-chk:checked').length;
    var master = document.getElementById('cv-chk-all');
    if (master) {
        master.checked       = (chked > 0 && chked === all);
        master.indeterminate = (chked > 0 && chked < all);
    }

    // Highlight selected rows
    document.querySelectorAll('.cv-row-chk').forEach(function (chk) {
        chk.closest('tr').classList.toggle('cv-row-checked', chk.checked);
    });
}

function cvPostSelected() {
    var checked = document.querySelectorAll('.cv-row-chk:checked');
    if (checked.length === 0) { alert('Select at least one entry to post.'); return; }
    if (!confirm('Post the ' + checked.length + ' selected entr' + (checked.length === 1 ? 'y' : 'ies') + ' to journal?')) return;
    document.getElementById('cv-post-sel-form').submit();
}
</script>

