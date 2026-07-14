<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();

$total_accounts = count($accounts);
$type_counts = [];
foreach ($accounts as $acc) {
    $t = $acc['account_type'] ?: 'Uncategorized';
    $type_counts[$t] = ($type_counts[$t] ?? 0) + 1;
}
ksort($type_counts);

$total_debit  = 0;
$total_credit = 0;
foreach ($balances as $b) {
    $total_debit  += (float)$b['debit_amount'];
    $total_credit += (float)$b['credit_amount'];
}
?>
<style>
.ob-page { background:#f0f2f5; min-height:100vh; }

.ob-topbar {
    background:#fff; border-bottom:1px solid #dde3ec;
    padding:14px 26px;
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap:10px;
}
.ob-topbar h2 { margin:0; font-size:18px; font-weight:700; color:#1a1a2e; }
.ob-topbar-right { display:flex; align-items:center; gap:8px; }

.btn-primary-ob {
    background:#1a6fc4; color:#fff; border:none;
    border-radius:4px; padding:7px 18px; font-size:13px;
    font-weight:600; cursor:pointer; display:inline-flex;
    align-items:center; gap:6px; text-decoration:none;
}
.btn-primary-ob:hover { background:#145ca8; color:#fff; text-decoration:none; }
.btn-outline-ob {
    background:#fff; color:#1a6fc4; border:1px solid #1a6fc4;
    border-radius:4px; padding:7px 16px; font-size:13px;
    font-weight:600; cursor:pointer; display:inline-flex;
    align-items:center; gap:6px; text-decoration:none;
}
.btn-outline-ob:hover { background:#eef4fb; text-decoration:none; color:#145ca8; }
.btn-danger-ob {
    background:#fff; color:#c0392b; border:1px solid #e8d0ce;
    border-radius:4px; padding:5px 12px; font-size:12px;
    cursor:pointer; display:inline-flex; align-items:center; gap:4px;
}
.btn-danger-ob:hover { background:#fdf0ef; color:#a93226; }

.ob-summary {
    background:#fff; border-bottom:1px solid #dde3ec;
    padding:12px 26px;
    display:flex; align-items:center; gap:14px; flex-wrap:wrap;
}
.badge-type {
    display:inline-flex; align-items:center; gap:6px;
    background:#eef4fb; color:#1a3a5c;
    border:1px solid #c8daf0; border-radius:20px;
    padding:4px 12px; font-size:12px; font-weight:600;
    cursor:pointer; transition:background .15s;
}
.badge-type:hover, .badge-type.active { background:#1a6fc4; color:#fff; border-color:#1a6fc4; }
.badge-type .cnt {
    background:#fff; color:#1a6fc4;
    border-radius:10px; padding:1px 7px; font-size:11px; font-weight:700;
}
.badge-type.active .cnt { color:#1a6fc4; }

.ob-filter-bar {
    background:#fff; border-bottom:1px solid #edf0f5;
    padding:10px 26px;
    display:flex; align-items:center; gap:12px;
}
.ob-search-wrap { position:relative; flex:1; max-width:340px; }
.ob-search-wrap input {
    border:1px solid #c8d0db; border-radius:4px;
    height:34px; padding:0 10px 0 34px; font-size:13px;
    width:100%; color:#333;
}
.ob-search-wrap input:focus { border-color:#1a6fc4; outline:none; }
.ob-search-wrap .fa-search {
    position:absolute; left:10px; top:50%; transform:translateY(-50%);
    color:#aaa; font-size:13px;
}
.ob-filter-info { font-size:12px; color:#888; margin-left:auto; }

.ob-balances-summary {
    background:#fff; border-bottom:1px solid #dde3ec;
    padding:10px 26px;
    display:flex; gap:24px; flex-wrap:wrap;
}
.ob-stat { display:flex; align-items:center; gap:10px; }
.ob-stat-label { font-size:11px; font-weight:700; color:#888; text-transform:uppercase; letter-spacing:.5px; }
.ob-stat-value { font-size:15px; font-weight:700; }
.ob-stat-value.debit  { color:#27ae60; }
.ob-stat-value.credit { color:#c0392b; }
.ob-stat-value.diff   { color:#1a6fc4; }

.ob-table-wrap { padding:20px 26px; }
.ob-paper {
    background:#fff; border:1px solid #dde3ec;
    border-radius:4px; overflow:hidden;
}

.ob-report-head {
    text-align:center; padding:20px 24px 14px;
    border-bottom:2px solid #27ae60;
}
.ob-report-head h3 { margin:0 0 3px; font-size:17px; font-weight:700; color:#1a1a2e; }
.ob-report-head p  { margin:2px 0; font-size:12px; color:#777; }

.ob-type-block { margin:0; }
.ob-type-header {
    background:#27ae60; color:#fff;
    padding:9px 16px;
    display:flex; align-items:center; justify-content:space-between;
    font-size:13px; font-weight:700;
}
.ob-type-header .type-badge {
    background:rgba(255,255,255,.18);
    border-radius:12px; padding:2px 10px;
    font-size:11px; font-weight:600;
}

.ob-group-header {
    background:#d5eedd; color:#1a5c2e;
    padding:7px 16px 7px 20px;
    font-size:12px; font-weight:700;
    text-transform:uppercase; letter-spacing:.5px;
    border-bottom:1px solid #b8dcc5;
    display:flex; align-items:center; gap:8px;
}

.ob-subgroup-header {
    background:#f0faf3;
    padding:5px 16px 5px 32px;
    font-size:12px; color:#4a7a5a;
    font-style:italic;
    border-bottom:1px solid #edf5f0;
}

table.ob-tbl {
    width:100%; border-collapse:collapse; font-size:13px;
}
table.ob-tbl thead tr {
    background:#f7faf8;
    border-bottom:2px solid #c5ddc9;
}
table.ob-tbl thead th {
    padding:8px 12px; font-size:11px; font-weight:700;
    color:#555; text-transform:uppercase; letter-spacing:.4px;
    white-space:nowrap;
}
table.ob-tbl tbody tr {
    border-bottom:1px solid #edf0f5;
    transition:background .1s;
}
table.ob-tbl tbody tr:hover { background:#f7fdf8; }
table.ob-tbl tbody td {
    padding:8px 12px; color:#333; vertical-align:middle;
}
table.ob-tbl td.td-num { text-align:right; }
table.ob-tbl td.td-code { font-size:12px; color:#888; font-family:monospace; }
table.ob-tbl td.td-name { font-weight:500; color:#222; }
table.ob-tbl td.td-action { text-align:right; width:90px; white-space:nowrap; }
td.has-balance { background:#f0faf3; }

.btn-edit-ob {
    background:#fff; color:#27ae60; border:1px solid #a8d5b5;
    border-radius:4px; padding:5px 10px; font-size:12px;
    cursor:pointer; display:inline-flex; align-items:center; gap:4px;
}
.btn-edit-ob:hover { background:#edfaf2; color:#1e8449; }

tr.ob-type-total td {
    background:#d5eedd; font-weight:700; font-size:12px;
    color:#1a5c2e; border-top:2px solid #27ae60;
    padding:7px 12px;
}

.ob-grand-total {
    background:#27ae60; color:#fff;
    padding:11px 16px;
    display:flex; align-items:center; justify-content:space-between;
    font-size:13px; font-weight:700;
    border-top:2px solid #1e8449;
}

.ob-modal-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.45); z-index:9999;
    align-items:center; justify-content:center;
}
.ob-modal-overlay.open { display:flex; }
.ob-modal {
    background:#fff; border-radius:6px;
    box-shadow:0 8px 32px rgba(0,0,0,.22);
    width:520px; max-width:96vw;
    overflow:hidden;
}
.ob-modal-header {
    background:#27ae60; color:#fff;
    padding:14px 20px;
    display:flex; align-items:center; justify-content:space-between;
    font-size:15px; font-weight:700;
}
.ob-modal-header button {
    background:none; border:none; color:#fff;
    font-size:20px; cursor:pointer; line-height:1; padding:0;
}
.ob-modal-body { padding:20px 22px 10px; }
.ob-modal-body .fg { margin-bottom:14px; }
.ob-modal-body label {
    display:block; font-size:11px; font-weight:700;
    color:#555; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px;
}
.ob-modal-body input[type=text],
.ob-modal-body input[type=number],
.ob-modal-body input[type=date] {
    width:100%; border:1px solid #c8d0db; border-radius:4px;
    height:36px; padding:0 10px; font-size:13px; color:#333;
}
.ob-modal-body input:focus,
.ob-modal-body select:focus { border-color:#27ae60; outline:none; }
.ob-modal-body .amount-row { display:flex; gap:14px; }
.ob-modal-body .amount-row .fg { flex:1; }
.ob-modal-footer {
    padding:12px 22px 18px;
    display:flex; justify-content:flex-end; gap:10px;
}

.admin-only-notice {
    background:#fff8f0; border:1px solid #f5c59a;
    border-radius:4px; padding:10px 18px;
    color:#8a5a00; font-size:13px;
    display:flex; align-items:center; gap:8px;
    margin:10px 26px 0;
}

@media print {
    .ob-topbar, .ob-summary, .ob-filter-bar, .ob-balances-summary,
    .no-print, .content-header { display:none!important; }
    .ob-table-wrap { padding:0; }
    .ob-paper { border:none; }
    .ob-type-header { background:#27ae60!important; -webkit-print-color-adjust:exact; }
    .ob-group-header { background:#d5eedd!important; -webkit-print-color-adjust:exact; }
}
</style>

<div class="content-wrapper ob-page">
    <section class="content-header no-print">
        <h1><i class="fa fa-balance-scale"></i> Opening Balances</h1>
    </section>

    <section class="content" style="padding:0;">

        <?php if ($this->session->flashdata('msg')): ?>
            <div style="padding:10px 26px 0;"><?php echo $this->session->flashdata('msg'); ?></div>
        <?php endif; ?>

        <?php if (!$is_admin): ?>
        <div class="admin-only-notice no-print">
            <i class="fa fa-lock"></i>
            <span>You have <strong>read-only</strong> access. Only administrators can add or update opening balances.</span>
        </div>
        <?php endif; ?>

        <!-- Top action bar -->
        <div class="ob-topbar no-print">
            <h2><i class="fa fa-balance-scale" style="color:#27ae60;"></i> &nbsp;Opening Balances</h2>
            <div class="ob-topbar-right">
                <a href="<?php echo site_url('accounts/all_accounts'); ?>" class="btn-outline-ob">
                    <i class="fa fa-list-alt"></i> Chart of Accounts
                </a>
                <?php if ($is_admin): ?>
                <button class="btn-primary-ob" onclick="openAddModal()" style="background:#27ae60;">
                    <i class="fa fa-plus"></i> Add Opening Balance
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Balance totals bar -->
        <div class="ob-balances-summary no-print">
            <div class="ob-stat">
                <div>
                    <div class="ob-stat-label">Total Debit Balances</div>
                    <div class="ob-stat-value debit"><?php echo $currency_symbol; ?><?php echo number_format($total_debit, 2); ?></div>
                </div>
            </div>
            <div style="width:1px;background:#dde3ec;align-self:stretch;"></div>
            <div class="ob-stat">
                <div>
                    <div class="ob-stat-label">Total Credit Balances</div>
                    <div class="ob-stat-value credit"><?php echo $currency_symbol; ?><?php echo number_format($total_credit, 2); ?></div>
                </div>
            </div>
            <div style="width:1px;background:#dde3ec;align-self:stretch;"></div>
            <?php $diff = $total_debit - $total_credit; ?>
            <div class="ob-stat">
                <div>
                    <div class="ob-stat-label">Difference (Dr - Cr)</div>
                    <div class="ob-stat-value diff" style="color:<?php echo $diff == 0 ? '#27ae60' : '#c0392b'; ?>">
                        <?php echo $currency_symbol; ?><?php echo number_format(abs($diff), 2); ?>
                        <?php echo $diff == 0 ? '<i class="fa fa-check-circle" style="font-size:12px;"></i>' : ''; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary badges -->
        <div class="ob-summary no-print">
            <span style="font-size:12px;color:#888;font-weight:600;margin-right:4px;">Filter:</span>
            <span class="badge-type active" data-type="all" onclick="filterByType('all', this)">
                <i class="fa fa-th-list"></i> All
                <span class="cnt"><?php echo $total_accounts; ?></span>
            </span>
            <?php foreach ($type_counts as $tname => $tcnt): ?>
            <span class="badge-type" data-type="<?php echo htmlspecialchars($tname); ?>"
                  onclick="filterByType('<?php echo htmlspecialchars($tname); ?>', this)">
                <?php echo htmlspecialchars($tname); ?>
                <span class="cnt"><?php echo $tcnt; ?></span>
            </span>
            <?php endforeach; ?>
        </div>

        <!-- Search bar -->
        <div class="ob-filter-bar no-print">
            <div class="ob-search-wrap">
                <i class="fa fa-search"></i>
                <input type="text" id="ob-search" placeholder="Search by name or account number…"
                       oninput="searchAccounts(this.value)">
            </div>
            <span class="ob-filter-info" id="filter-info">
                Showing <strong><?php echo $total_accounts; ?></strong> accounts
            </span>
        </div>

        <!-- Table -->
        <div class="ob-table-wrap">
            <div class="ob-paper">

                <div class="ob-report-head">
                    <h3><?php echo htmlspecialchars($school_name ?: 'School'); ?></h3>
                    <p><strong>Opening Balances</strong></p>
                    <p>As of <?php echo date('d M Y'); ?> &nbsp;|&nbsp; Total Accounts: <?php echo $total_accounts; ?></p>
                </div>

                <?php
                $type_order = ['Asset','Liability','Equity','Income','Expense'];
                foreach (array_keys($grouped) as $t) {
                    if (!in_array($t, $type_order)) $type_order[] = $t;
                }
                $row_num = 0;
                foreach ($type_order as $type_name):
                    if (!isset($grouped[$type_name])) continue;
                    $groups     = $grouped[$type_name];
                    $type_count = 0;
                    $type_debit = 0; $type_credit = 0;
                    foreach ($groups as $subs) foreach ($subs as $accs) {
                        $type_count += count($accs);
                        foreach ($accs as $acc) {
                            if (isset($balances[$acc['id']])) {
                                $type_debit  += (float)$balances[$acc['id']]['debit_amount'];
                                $type_credit += (float)$balances[$acc['id']]['credit_amount'];
                            }
                        }
                    }
                ?>

                <div class="ob-type-block" data-type="<?php echo htmlspecialchars($type_name); ?>">
                    <div class="ob-type-header">
                        <span><i class="fa fa-folder"></i> &nbsp;<?php echo htmlspecialchars($type_name); ?></span>
                        <span class="type-badge"><?php echo $type_count; ?> accounts</span>
                    </div>

                    <table class="ob-tbl">
                        <thead>
                            <tr>
                                <th style="width:50px;">#</th>
                                <th style="width:110px;">Code</th>
                                <th>Account Name</th>
                                <th style="width:130px;">Group</th>
                                <th style="width:110px;text-align:center;">Year</th>
                                <th style="width:130px;text-align:right;">Dr Balance</th>
                                <th style="width:130px;text-align:right;">Cr Balance</th>
                                <?php if ($is_admin): ?><th style="width:80px;">Action</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($groups as $group_name => $subs):
                            $group_count = 0;
                            foreach ($subs as $accs) $group_count += count($accs);
                        ?>
                            <tr class="ob-group-row" data-type="<?php echo htmlspecialchars($type_name); ?>">
                                <td colspan="<?php echo $is_admin ? 8 : 7; ?>" style="padding:0;">
                                    <div class="ob-group-header">
                                        <i class="fa fa-angle-right"></i>
                                        <?php echo htmlspecialchars($group_name); ?>
                                        <span style="font-size:11px;color:#6a9a7a;font-weight:400;font-style:italic;margin-left:6px;">(<?php echo $group_count; ?>)</span>
                                    </div>
                                </td>
                            </tr>

                            <?php foreach ($subs as $sub_name => $accs): ?>
                            <?php if ($sub_name !== ''): ?>
                            <tr class="ob-subgroup-row" data-type="<?php echo htmlspecialchars($type_name); ?>">
                                <td colspan="<?php echo $is_admin ? 8 : 7; ?>" style="padding:0;">
                                    <div class="ob-subgroup-header">
                                        <i class="fa fa-minus" style="font-size:9px;"></i>
                                        &nbsp;<?php echo htmlspecialchars($sub_name); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>

                            <?php foreach ($accs as $acc):
                                $row_num++;
                                $bal = isset($balances[$acc['id']]) ? $balances[$acc['id']] : null;
                                $has_bal = ($bal && ((float)$bal['debit_amount'] > 0 || (float)$bal['credit_amount'] > 0));
                            ?>
                            <tr class="ob-acc-row <?php echo $has_bal ? 'has-balance' : ''; ?>"
                                data-type="<?php echo htmlspecialchars($type_name); ?>"
                                data-search="<?php echo strtolower(htmlspecialchars($acc['account_name'] . ' ' . $acc['account_number'] . ' ' . $group_name . ' ' . $sub_name)); ?>">
                                <td class="td-code" style="color:#bbb;font-size:11px;"><?php echo $row_num; ?></td>
                                <td class="td-code"><?php echo htmlspecialchars($acc['account_number']); ?></td>
                                <td class="td-name"><?php echo htmlspecialchars($acc['account_name']); ?></td>
                                <td class="td-code"><?php echo htmlspecialchars($acc['account_group'] ?: '—'); ?></td>
                                <td style="text-align:center;font-size:12px;color:#888;">
                                    <?php echo $bal ? htmlspecialchars($bal['financial_year']) : '—'; ?>
                                </td>
                                <td class="td-num" style="color:#27ae60;">
                                    <?php if ($bal && (float)$bal['debit_amount'] > 0): ?>
                                        <?php echo $currency_symbol; ?><?php echo number_format((float)$bal['debit_amount'], 2); ?>
                                    <?php else: ?>
                                        <span style="color:#ccc;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="td-num" style="color:#c0392b;">
                                    <?php if ($bal && (float)$bal['credit_amount'] > 0): ?>
                                        <?php echo $currency_symbol; ?><?php echo number_format((float)$bal['credit_amount'], 2); ?>
                                    <?php else: ?>
                                        <span style="color:#ccc;">—</span>
                                    <?php endif; ?>
                                </td>
                                <?php if ($is_admin): ?>
                                <td class="td-action">
                                    <button class="btn-edit-ob"
                                            onclick="openEditModal(<?php echo $acc['id']; ?>, '<?php echo addslashes($acc['account_name']); ?>', '<?php echo addslashes($acc['account_number']); ?>')"
                                            title="<?php echo $bal ? 'Edit' : 'Add'; ?> Opening Balance">
                                        <i class="fa fa-<?php echo $bal ? 'pencil' : 'plus'; ?>"></i>
                                    </button>
                                    <?php if ($bal): ?>
                                    <a href="<?php echo site_url('accounts/delete_opening_balance/' . $bal['id']); ?>"
                                       class="btn-danger-ob"
                                       title="Delete"
                                       onclick="return confirm('Delete opening balance for \'<?php echo addslashes($acc['account_name']); ?>\'?');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; // accs ?>
                            <?php endforeach; // subs ?>
                        <?php endforeach; // groups ?>

                        <tr class="ob-type-total">
                            <td colspan="4">Total <?php echo htmlspecialchars($type_name); ?> Accounts</td>
                            <td></td>
                            <td style="text-align:right;"><?php echo $currency_symbol; ?><?php echo number_format($type_debit, 2); ?></td>
                            <td style="text-align:right;"><?php echo $currency_symbol; ?><?php echo number_format($type_credit, 2); ?></td>
                            <?php if ($is_admin): ?><td></td><?php endif; ?>
                        </tr>

                        </tbody>
                    </table>
                </div>

                <?php endforeach; // types ?>

                <div class="ob-grand-total">
                    <span><i class="fa fa-balance-scale"></i> &nbsp;Grand Total</span>
                    <span style="display:flex;gap:40px;">
                        <span>Dr: <?php echo $currency_symbol; ?><?php echo number_format($total_debit, 2); ?></span>
                        <span>Cr: <?php echo $currency_symbol; ?><?php echo number_format($total_credit, 2); ?></span>
                    </span>
                </div>

            </div><!-- /.ob-paper -->
        </div><!-- /.ob-table-wrap -->

    </section>
</div>

<!-- Add/Edit Modal (admin only) -->
<?php if ($is_admin): ?>
<div class="ob-modal-overlay no-print" id="ob-modal-overlay" onclick="closeModalOnBg(event)">
    <div class="ob-modal">
        <div class="ob-modal-header">
            <span id="modal-title"><i class="fa fa-balance-scale" style="margin-right:7px;"></i> Opening Balance</span>
            <button onclick="closeModal()" title="Close">&times;</button>
        </div>
        <form method="post" action="<?php echo site_url('accounts/save_opening_balance'); ?>" id="ob-form">
            <?php echo $this->customlib->getCSRF(); ?>
            <input type="hidden" name="account_id" id="ob-account-id">
            <div class="ob-modal-body">
                <div class="fg">
                    <label>Account</label>
                    <div id="ob-account-display" style="padding:8px 10px;background:#f7f9fc;border:1px solid #e0e4ea;border-radius:4px;font-size:13px;font-weight:600;color:#1a3a5c;min-height:36px;"></div>
                </div>
                <div style="display:flex;gap:14px;">
                    <div class="fg">
                        <label>Opening Date</label>
                        <input type="date" name="opening_date" id="ob-date" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="fg">
                        <label>Financial Year</label>
                        <input type="text" name="financial_year" id="ob-year" value="<?php echo date('Y'); ?>" placeholder="e.g. 2024-25">
                    </div>
                </div>
                <div class="amount-row">
                    <div class="fg">
                        <label>Debit Balance</label>
                        <input type="number" name="debit_amount" id="ob-debit" value="0.00" step="0.01" min="0"
                               oninput="if(+this.value>0){document.getElementById('ob-credit').value='0.00';}">
                    </div>
                    <div class="fg">
                        <label>Credit Balance</label>
                        <input type="number" name="credit_amount" id="ob-credit" value="0.00" step="0.01" min="0"
                               oninput="if(+this.value>0){document.getElementById('ob-debit').value='0.00';}">
                    </div>
                </div>
                <div class="fg">
                    <label>Notes (optional)</label>
                    <input type="text" name="notes" id="ob-notes" placeholder="e.g. Carry-forward from previous year">
                </div>
            </div>
            <div class="ob-modal-footer">
                <button type="button" onclick="closeModal()"
                        style="background:#fff;border:1px solid #c8d0db;border-radius:4px;padding:7px 18px;font-size:13px;cursor:pointer;color:#555;">
                    Cancel
                </button>
                <button type="submit"
                        style="background:#27ae60;color:#fff;border:none;border-radius:4px;padding:7px 22px;font-size:13px;font-weight:600;cursor:pointer;">
                    <i class="fa fa-save"></i> Save Balance
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
var activeType = 'all';

function filterByType(type, el) {
    activeType = type;
    document.querySelectorAll('.badge-type').forEach(function(b){ b.classList.remove('active'); });
    el.classList.add('active');
    applyFilters();
}

function searchAccounts(q) { applyFilters(q); }

function applyFilters(q) {
    q = (q || document.getElementById('ob-search').value || '').toLowerCase().trim();
    var visibleCount = 0;
    document.querySelectorAll('.ob-type-block').forEach(function(block) {
        var blockType = block.getAttribute('data-type');
        var typeMatch = (activeType === 'all' || activeType === blockType);
        var accRows = block.querySelectorAll('tr.ob-acc-row');
        var anyVisible = false;
        accRows.forEach(function(row) {
            var searchStr = row.getAttribute('data-search') || '';
            var show = typeMatch && (!q || searchStr.indexOf(q) !== -1);
            row.style.display = show ? '' : 'none';
            if (show) { anyVisible = true; visibleCount++; }
        });
        block.style.display = (typeMatch && anyVisible) ? '' : 'none';
        if (typeMatch) {
            block.querySelectorAll('tr.ob-group-row, tr.ob-subgroup-row').forEach(function(gr) {
                gr.style.display = anyVisible ? '' : 'none';
            });
        }
    });
    document.getElementById('filter-info').innerHTML =
        'Showing <strong>' + visibleCount + '</strong> of <strong><?php echo $total_accounts; ?></strong> accounts';
}

<?php if ($is_admin): ?>
function openAddModal() {
    document.getElementById('ob-account-id').value = '';
    document.getElementById('ob-account-display').textContent = '— Select from table rows —';
    document.getElementById('ob-date').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('ob-year').value = '<?php echo date('Y'); ?>';
    document.getElementById('ob-debit').value  = '0.00';
    document.getElementById('ob-credit').value = '0.00';
    document.getElementById('ob-notes').value  = '';
    document.getElementById('modal-title').innerHTML = '<i class="fa fa-plus-circle" style="margin-right:7px;"></i> Add Opening Balance';
    document.getElementById('ob-modal-overlay').classList.add('open');
}

function openEditModal(accountId, accountName, accountNumber) {
    document.getElementById('ob-account-id').value = accountId;
    document.getElementById('ob-account-display').textContent = accountNumber + '  —  ' + accountName;
    document.getElementById('ob-date').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('ob-year').value = '<?php echo date('Y'); ?>';
    document.getElementById('ob-debit').value  = '0.00';
    document.getElementById('ob-credit').value = '0.00';
    document.getElementById('ob-notes').value  = '';
    document.getElementById('modal-title').innerHTML = '<i class="fa fa-pencil" style="margin-right:7px;"></i> Edit Opening Balance: ' + accountName;
    document.getElementById('ob-modal-overlay').classList.add('open');

    // Fetch existing balance if any
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?php echo site_url('accounts/get_opening_balance_json'); ?>/' + accountId, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var d = JSON.parse(xhr.responseText);
                if (d.success) {
                    if (d.opening_date) document.getElementById('ob-date').value  = d.opening_date;
                    if (d.financial_year) document.getElementById('ob-year').value = d.financial_year;
                    document.getElementById('ob-debit').value  = parseFloat(d.debit_amount  || 0).toFixed(2);
                    document.getElementById('ob-credit').value = parseFloat(d.credit_amount || 0).toFixed(2);
                    document.getElementById('ob-notes').value  = d.notes || '';
                }
            } catch(e) {}
        }
    };
    xhr.send();
}

function closeModal() {
    document.getElementById('ob-modal-overlay').classList.remove('open');
}
function closeModalOnBg(e) {
    if (e.target === document.getElementById('ob-modal-overlay')) closeModal();
}
<?php endif; ?>
</script>

