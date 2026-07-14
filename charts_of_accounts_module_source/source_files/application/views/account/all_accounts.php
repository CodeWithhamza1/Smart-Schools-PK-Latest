<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();

// Count totals
$total_accounts = count($accounts);
$type_counts = [];
foreach ($accounts as $acc) {
    $t = $acc['account_type'] ?: 'Uncategorized';
    $type_counts[$t] = ($type_counts[$t] ?? 0) + 1;
}
ksort($type_counts);
?>
<style>
/* ── Page layout ───────────────────────────────── */
.coa-page { background:#f0f2f5; min-height:100vh; }

/* ── Top action bar ────────────────────────────── */
.coa-topbar {
    background:#fff; border-bottom:1px solid #dde3ec;
    padding:14px 26px;
    display:flex; align-items:center; justify-content:space-between;
    flex-wrap:wrap; gap:10px;
}
.coa-topbar h2 { margin:0; font-size:18px; font-weight:700; color:#1a1a2e; }
.coa-topbar-right { display:flex; align-items:center; gap:8px; }

.btn-primary-coa {
    background:#1a6fc4; color:#fff; border:none;
    border-radius:4px; padding:7px 18px; font-size:13px;
    font-weight:600; cursor:pointer; display:inline-flex;
    align-items:center; gap:6px; text-decoration:none;
}
.btn-primary-coa:hover { background:#145ca8; color:#fff; text-decoration:none; }
.btn-outline-coa {
    background:#fff; color:#1a6fc4; border:1px solid #1a6fc4;
    border-radius:4px; padding:7px 16px; font-size:13px;
    font-weight:600; cursor:pointer; display:inline-flex;
    align-items:center; gap:6px; text-decoration:none;
}
.btn-outline-coa:hover { background:#eef4fb; text-decoration:none; color:#145ca8; }
.btn-danger-coa {
    background:#fff; color:#c0392b; border:1px solid #e8d0ce;
    border-radius:4px; padding:5px 12px; font-size:12px;
    cursor:pointer; display:inline-flex; align-items:center; gap:4px;
}
.btn-danger-coa:hover { background:#fdf0ef; color:#a93226; }

/* ── Create Account panel ──────────────────────── */
.coa-create-panel {
    background:#fff; border-bottom:1px solid #dde3ec;
    padding:0;
    overflow:hidden;
    transition:max-height .3s ease;
}
.coa-create-toggle {
    padding:12px 26px;
    cursor:pointer;
    display:flex; align-items:center; justify-content:space-between;
    background:#f7f9fc; border-bottom:1px solid #e8ecf2;
    font-weight:700; font-size:14px; color:#1a3a5c;
    user-select:none;
}
.coa-create-toggle:hover { background:#eef3fb; }
.coa-create-body { padding:20px 26px 24px; }

.coa-create-body .form-row {
    display:flex; align-items:flex-end; gap:16px; flex-wrap:wrap;
}
.coa-create-body .fg { display:flex; flex-direction:column; flex:1; min-width:160px; }
.coa-create-body .fg-sm { flex:0 0 auto; min-width:130px; }
.coa-create-body label {
    font-size:11px; font-weight:700; color:#555;
    margin-bottom:4px; text-transform:uppercase; letter-spacing:.4px;
}
.coa-create-body input[type=text],
.coa-create-body select {
    border:1px solid #c8d0db; border-radius:4px;
    height:36px; padding:0 10px; font-size:13px;
    color:#333; background:#fff; width:100%;
}
.coa-create-body input[type=text]:focus,
.coa-create-body select:focus {
    border-color:#1a6fc4; outline:none;
    box-shadow:0 0 0 2px rgba(26,111,196,.15);
}
.req { color:#e74c3c; }

/* ── Summary badges ────────────────────────────── */
.coa-summary {
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

/* ── Filter/search bar ──────────────────────────── */
.coa-filter-bar {
    background:#fff; border-bottom:1px solid #edf0f5;
    padding:10px 26px;
    display:flex; align-items:center; gap:12px;
}
.coa-search-wrap { position:relative; flex:1; max-width:340px; }
.coa-search-wrap input {
    border:1px solid #c8d0db; border-radius:4px;
    height:34px; padding:0 10px 0 34px; font-size:13px;
    width:100%; color:#333;
}
.coa-search-wrap input:focus { border-color:#1a6fc4; outline:none; }
.coa-search-wrap .fa-search {
    position:absolute; left:10px; top:50%; transform:translateY(-50%);
    color:#aaa; font-size:13px;
}
.coa-filter-info { font-size:12px; color:#888; margin-left:auto; }

/* ── COA table area ─────────────────────────────── */
.coa-table-wrap { padding:20px 26px; }
.coa-paper {
    background:#fff; border:1px solid #dde3ec;
    border-radius:4px; overflow:hidden;
}

/* Report header inside paper */
.coa-report-head {
    text-align:center; padding:20px 24px 14px;
    border-bottom:2px solid #1a6fc4;
}
.coa-report-head h3 { margin:0 0 3px; font-size:17px; font-weight:700; color:#1a1a2e; }
.coa-report-head p  { margin:2px 0; font-size:12px; color:#777; }

/* Section: one per account_type */
.coa-type-block { margin:0; }
.coa-type-header {
    background:#1a6fc4; color:#fff;
    padding:9px 16px;
    display:flex; align-items:center; justify-content:space-between;
    font-size:13px; font-weight:700;
}
.coa-type-header .type-badge {
    background:rgba(255,255,255,.18);
    border-radius:12px; padding:2px 10px;
    font-size:11px; font-weight:600;
}

/* Group sub-header */
.coa-group-header {
    background:#dce6f5; color:#1a3a5c;
    padding:7px 16px 7px 20px;
    font-size:12px; font-weight:700;
    text-transform:uppercase; letter-spacing:.5px;
    border-bottom:1px solid #c8d8ee;
    display:flex; align-items:center; gap:8px;
}

/* Sub-group row */
.coa-subgroup-header {
    background:#f2f6fb;
    padding:5px 16px 5px 32px;
    font-size:12px; color:#557;
    font-style:italic;
    border-bottom:1px solid #edf0f5;
}

/* Account rows */
table.coa-tbl {
    width:100%; border-collapse:collapse; font-size:13px;
}
table.coa-tbl tbody tr {
    border-bottom:1px solid #edf0f5;
    transition:background .1s;
}
table.coa-tbl tbody tr:hover { background:#f7f9fd; }
table.coa-tbl tbody td {
    padding:8px 16px; color:#333; vertical-align:middle;
}
table.coa-tbl td.td-num { text-align:right; color:#1a3a5c; font-weight:600; font-size:12px; }
table.coa-tbl td.td-type {
    font-size:11px; font-weight:600;
    color:#1a6fc4;
}
table.coa-tbl td.td-name { font-weight:500; color:#222; }
table.coa-tbl td.td-code { font-size:12px; color:#888; font-family:monospace; }
table.coa-tbl td.td-action { text-align:right; width:110px; white-space:nowrap; }
.btn-edit-coa {
    background:#fff; color:#1a6fc4; border:1px solid #b0cce8;
    border-radius:4px; padding:5px 10px; font-size:12px;
    cursor:pointer; display:inline-flex; align-items:center; gap:4px;
    text-decoration:none;
}
.btn-edit-coa:hover { background:#eef4fb; color:#145ca8; }

/* Edit modal */
.coa-modal-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.45); z-index:9999;
    align-items:center; justify-content:center;
}
.coa-modal-overlay.open { display:flex; }
.coa-modal {
    background:#fff; border-radius:6px;
    box-shadow:0 8px 32px rgba(0,0,0,.22);
    width:560px; max-width:96vw;
    overflow:hidden;
}
.coa-modal-header {
    background:#1a6fc4; color:#fff;
    padding:14px 20px;
    display:flex; align-items:center; justify-content:space-between;
    font-size:15px; font-weight:700;
}
.coa-modal-header button {
    background:none; border:none; color:#fff;
    font-size:20px; cursor:pointer; line-height:1; padding:0;
}
.coa-modal-body { padding:22px 22px 10px; }
.coa-modal-body .fg { margin-bottom:14px; }
.coa-modal-body label {
    display:block; font-size:11px; font-weight:700;
    color:#555; text-transform:uppercase; letter-spacing:.4px; margin-bottom:4px;
}
.coa-modal-body input[type=text],
.coa-modal-body select {
    width:100%; border:1px solid #c8d0db; border-radius:4px;
    height:36px; padding:0 10px; font-size:13px; color:#333;
}
.coa-modal-body input:focus,
.coa-modal-body select:focus { border-color:#1a6fc4; outline:none; }
.coa-modal-footer {
    padding:12px 22px 18px;
    display:flex; justify-content:flex-end; gap:10px;
}

/* Type total row */
tr.coa-type-total td {
    background:#e4edf8; font-weight:700; font-size:12px;
    color:#1a3a5c; border-top:2px solid #1a6fc4;
    padding:7px 16px;
}

/* Grand total */
.coa-grand-total {
    background:#1a6fc4; color:#fff;
    padding:11px 16px;
    display:flex; align-items:center; justify-content:space-between;
    font-size:13px; font-weight:700;
    border-top:2px solid #0d4a8a;
}

/* Print */
@media print {
    .coa-topbar, .coa-create-panel, .coa-summary,
    .coa-filter-bar, .no-print, .content-header { display:none!important; }
    .coa-table-wrap { padding:0; }
    .coa-paper { border:none; }
    .coa-type-header { background:#333!important; -webkit-print-color-adjust:exact; }
    .coa-group-header { background:#ddd!important; -webkit-print-color-adjust:exact; }
}

/* Hidden rows when filtering */
.coa-row-hidden { display:none!important; }
</style>

<div class="content-wrapper coa-page">
    <section class="content-header no-print">
        <h1><i class="fa fa-list-alt"></i> Chart of Accounts</h1>
    </section>

    <section class="content" style="padding:0;">

        <!-- Flash messages -->
        <?php if ($this->session->flashdata('msg')): ?>
            <div style="padding:10px 26px 0;"><?php echo $this->session->flashdata('msg'); ?></div>
        <?php endif; ?>
        <?php echo validation_errors('<div style="padding:8px 26px 0;"><div class="alert alert-danger">', '</div></div>'); ?>

        <!-- Top action bar -->
        <div class="coa-topbar no-print">
            <h2><i class="fa fa-list-alt" style="color:#1a6fc4;"></i> &nbsp;Chart of Accounts</h2>
            <div class="coa-topbar-right">
                <a href="<?php echo site_url('accounts/print_chart_of_accounts'); ?>" target="_blank" class="btn-outline-coa">
                    <i class="fa fa-print"></i> Print COA
                </a>
                <button class="btn-primary-coa" onclick="toggleCreatePanel()">
                    <i class="fa fa-plus"></i> Add Account
                </button>
            </div>
        </div>

        <!-- Create Account panel (collapsible) -->
        <div class="coa-create-panel no-print" id="create-panel" style="display:none;">
            <div class="coa-create-toggle" onclick="toggleCreatePanel()">
                <span><i class="fa fa-plus-circle" style="color:#1a6fc4;"></i> &nbsp; Create New Account</span>
                <i class="fa fa-chevron-up" id="create-panel-icon"></i>
            </div>
            <div class="coa-create-body">
                <form method="post" action="<?php echo site_url('accounts/create_account'); ?>" id="create-form">
                    <?php echo $this->customlib->getCSRF(); ?>
                    <div class="form-row">
                        <div class="fg">
                            <label>Account Number <span class="req">*</span></label>
                            <input type="text" name="account_number" id="account_number"
                                   value="<?php echo set_value('account_number'); ?>"
                                   placeholder="e.g. CA001" required>
                        </div>
                        <div class="fg" style="flex:2;">
                            <label>Account Name <span class="req">*</span></label>
                            <input type="text" name="account_name" id="account_name"
                                   value="<?php echo set_value('account_name'); ?>"
                                   placeholder="e.g. Cash in Hand" required>
                        </div>
                        <div class="fg">
                            <label>Account Type <span class="req">*</span></label>
                            <select name="account_type" id="account_type_sel" required>
                                <option value="">— Select —</option>
                                <option value="Asset"     <?php echo set_value('account_type')=='Asset'     ?'selected':''; ?>>Asset</option>
                                <option value="Liability" <?php echo set_value('account_type')=='Liability' ?'selected':''; ?>>Liability</option>
                                <option value="Income"    <?php echo set_value('account_type')=='Income'    ?'selected':''; ?>>Income</option>
                                <option value="Expense"   <?php echo set_value('account_type')=='Expense'   ?'selected':''; ?>>Expense</option>
                                <option value="Equity"    <?php echo set_value('account_type')=='Equity'    ?'selected':''; ?>>Equity</option>
                            </select>
                        </div>
                        <div class="fg">
                            <label>Account Group</label>
                            <input type="text" name="account_group"
                                   value="<?php echo set_value('account_group'); ?>"
                                   placeholder="e.g. Current Assets">
                        </div>
                        <div class="fg">
                            <label>Sub Group</label>
                            <input type="text" name="account_sub_group"
                                   value="<?php echo set_value('account_sub_group'); ?>"
                                   placeholder="e.g. Bank">
                        </div>
                        <div class="fg-sm" style="display:flex;align-items:flex-end;">
                            <button type="submit" class="btn-primary-coa" style="width:100%;justify-content:center;">
                                <i class="fa fa-save"></i> Save
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary badges -->
        <div class="coa-summary no-print">
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
        <div class="coa-filter-bar no-print">
            <div class="coa-search-wrap">
                <i class="fa fa-search"></i>
                <input type="text" id="coa-search" placeholder="Search by name or account number…"
                       oninput="searchAccounts(this.value)">
            </div>
            <span class="coa-filter-info" id="filter-info">
                Showing <strong><?php echo $total_accounts; ?></strong> accounts
            </span>
        </div>

        <!-- COA table -->
        <div class="coa-table-wrap">
            <div class="coa-paper">

                <!-- Report header (visible on print) -->
                <div class="coa-report-head">
                    <h3><?php echo htmlspecialchars($school_name ?: 'School'); ?></h3>
                    <p><strong>Chart of Accounts</strong></p>
                    <p>As of <?php echo date('d M Y'); ?> &nbsp;|&nbsp; Total Accounts: <?php echo $total_accounts; ?></p>
                </div>

                <?php
                $type_order = ['Asset','Liability','Equity','Income','Expense'];
                // add any types not in order list
                foreach (array_keys($grouped) as $t) {
                    if (!in_array($t, $type_order)) $type_order[] = $t;
                }
                $row_num = 0;
                foreach ($type_order as $type_name):
                    if (!isset($grouped[$type_name])) continue;
                    $groups      = $grouped[$type_name];
                    $type_count  = 0;
                    foreach ($groups as $subs) foreach ($subs as $accs) $type_count += count($accs);
                ?>

                <div class="coa-type-block" data-type="<?php echo htmlspecialchars($type_name); ?>">
                    <div class="coa-type-header">
                        <span><i class="fa fa-folder"></i> &nbsp;<?php echo htmlspecialchars($type_name); ?></span>
                        <span class="type-badge"><?php echo $type_count; ?> accounts</span>
                    </div>

                    <table class="coa-tbl">
                        <thead style="display:none;">
                            <tr>
                                <th style="width:50px;">#</th>
                                <th style="width:120px;">Code</th>
                                <th>Account Name</th>
                                <th style="width:160px;">Group</th>
                                <th style="width:140px;">Sub Group</th>
                                <th style="width:70px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($groups as $group_name => $subs):
                            $group_count = 0;
                            foreach ($subs as $accs) $group_count += count($accs);
                        ?>
                            <!-- Group header row -->
                            <tr class="coa-group-row" data-type="<?php echo htmlspecialchars($type_name); ?>">
                                <td colspan="6" style="padding:0;">
                                    <div class="coa-group-header">
                                        <i class="fa fa-angle-right"></i>
                                        <?php echo htmlspecialchars($group_name); ?>
                                        <span style="font-size:11px;color:#7a9ac0;font-weight:400;font-style:italic;margin-left:6px;">(<?php echo $group_count; ?>)</span>
                                    </div>
                                </td>
                            </tr>

                            <?php foreach ($subs as $sub_name => $accs): ?>
                            <?php if ($sub_name !== ''): ?>
                            <!-- Sub-group header -->
                            <tr class="coa-subgroup-row" data-type="<?php echo htmlspecialchars($type_name); ?>">
                                <td colspan="6" style="padding:0;">
                                    <div class="coa-subgroup-header">
                                        <i class="fa fa-minus" style="font-size:9px;"></i>
                                        &nbsp;<?php echo htmlspecialchars($sub_name); ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>

                            <?php foreach ($accs as $acc):
                                $row_num++;
                            ?>
                            <tr class="coa-acc-row"
                                data-type="<?php echo htmlspecialchars($type_name); ?>"
                                data-search="<?php echo strtolower(htmlspecialchars($acc['account_name'] . ' ' . $acc['account_number'] . ' ' . $group_name . ' ' . $sub_name)); ?>">
                                <td class="td-code" style="width:50px;color:#bbb;font-size:11px;"><?php echo $row_num; ?></td>
                                <td class="td-code" style="width:120px;"><?php echo htmlspecialchars($acc['account_number']); ?></td>
                                <td class="td-name"><?php echo htmlspecialchars($acc['account_name']); ?></td>
                                <td class="td-code" style="width:160px;"><?php echo htmlspecialchars($acc['account_group'] ?: '—'); ?></td>
                                <td class="td-code" style="width:140px;"><?php echo htmlspecialchars($acc['account_sub_group'] ?: '—'); ?></td>
                                <td class="td-action">
                                    <button class="btn-edit-coa"
                                            title="Edit"
                                            onclick="openEditModal(<?php echo $acc['id']; ?>)">
                                        <i class="fa fa-pencil"></i>
                                    </button>
                                    <a href="<?php echo base_url(); ?>accounts/delete_account/<?php echo $acc['id']; ?>"
                                       class="btn-danger-coa"
                                       title="Delete"
                                       onclick="return confirm('Delete account \'<?php echo addslashes($acc['account_name']); ?>\'? This cannot be undone.');">
                                        <i class="fa fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; // accs ?>
                            <?php endforeach; // subs ?>
                        <?php endforeach; // groups ?>

                        <!-- Type subtotal row -->
                        <tr class="coa-type-total">
                            <td colspan="5">Total <?php echo htmlspecialchars($type_name); ?> Accounts</td>
                            <td style="text-align:right; font-weight:700;"><?php echo $type_count; ?></td>
                        </tr>

                        </tbody>
                    </table>
                </div>

                <?php endforeach; // types ?>

                <!-- Grand total bar -->
                <div class="coa-grand-total">
                    <span><i class="fa fa-list"></i> &nbsp;Total Accounts in Chart of Accounts</span>
                    <span><?php echo $total_accounts; ?></span>
                </div>

            </div><!-- /.coa-paper -->
        </div><!-- /.coa-table-wrap -->

    </section>
</div>

<!-- Edit Account Modal -->
<div class="coa-modal-overlay no-print" id="edit-modal-overlay" onclick="closeEditModalOnBg(event)">
    <div class="coa-modal">
        <div class="coa-modal-header">
            <span><i class="fa fa-pencil" style="margin-right:7px;"></i> Edit Account</span>
            <button onclick="closeEditModal()" title="Close">&times;</button>
        </div>
        <form method="post" action="<?php echo site_url('accounts/update_account'); ?>">
            <?php echo $this->customlib->getCSRF(); ?>
            <input type="hidden" name="id" id="edit-id">
            <div class="coa-modal-body">
                <div style="display:flex;gap:14px;">
                    <div class="fg" style="flex:1;">
                        <label>Account Number <span class="req">*</span></label>
                        <input type="text" name="account_number" id="edit-number" required placeholder="e.g. CA001">
                    </div>
                    <div class="fg" style="flex:2;">
                        <label>Account Name <span class="req">*</span></label>
                        <input type="text" name="account_name" id="edit-name" required placeholder="e.g. Cash in Hand">
                    </div>
                </div>
                <div class="fg">
                    <label>Account Type <span class="req">*</span></label>
                    <select name="account_type" id="edit-type" required>
                        <option value="">— Select —</option>
                        <option value="Asset">Asset</option>
                        <option value="Liability">Liability</option>
                        <option value="Income">Income</option>
                        <option value="Expense">Expense</option>
                        <option value="Equity">Equity</option>
                    </select>
                </div>
                <div style="display:flex;gap:14px;">
                    <div class="fg" style="flex:1;">
                        <label>Account Group</label>
                        <input type="text" name="account_group" id="edit-group" placeholder="e.g. Current Assets">
                    </div>
                    <div class="fg" style="flex:1;">
                        <label>Sub Group</label>
                        <input type="text" name="account_sub_group" id="edit-subgroup" placeholder="e.g. Bank">
                    </div>
                </div>
            </div>
            <div class="coa-modal-footer">
                <button type="button" onclick="closeEditModal()"
                        style="background:#fff;border:1px solid #c8d0db;border-radius:4px;padding:7px 18px;font-size:13px;cursor:pointer;color:#555;">
                    Cancel
                </button>
                <button type="submit"
                        style="background:#1a6fc4;color:#fff;border:none;border-radius:4px;padding:7px 22px;font-size:13px;font-weight:600;cursor:pointer;">
                    <i class="fa fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
/* ── Create panel toggle ────────────────────── */
var panelOpen = <?php echo (validation_errors() ? 'true' : 'false'); ?>;

function toggleCreatePanel() {
    var panel = document.getElementById('create-panel');
    var icon  = document.getElementById('create-panel-icon');
    panelOpen = !panelOpen;
    panel.style.display = panelOpen ? 'block' : 'none';
    icon.className = panelOpen ? 'fa fa-chevron-up' : 'fa fa-chevron-down';
}

// Auto-open if validation errors
if (panelOpen) {
    document.getElementById('create-panel').style.display = 'block';
}

/* ── Filter by type badge ───────────────────── */
var activeType = 'all';

function filterByType(type, el) {
    activeType = type;
    document.querySelectorAll('.badge-type').forEach(function(b){ b.classList.remove('active'); });
    el.classList.add('active');
    applyFilters();
}

/* ── Search ─────────────────────────────────── */
function searchAccounts(q) {
    applyFilters(q);
}

function applyFilters(q) {
    q = (q || document.getElementById('coa-search').value || '').toLowerCase().trim();

    var visibleCount = 0;
    var typeBlocks = document.querySelectorAll('.coa-type-block');

    typeBlocks.forEach(function(block) {
        var blockType = block.getAttribute('data-type');
        var typeMatch = (activeType === 'all' || activeType === blockType);

        var accRows = block.querySelectorAll('tr.coa-acc-row');
        var anyAccVisible = false;

        accRows.forEach(function(row) {
            var searchStr = row.getAttribute('data-search') || '';
            var qMatch = (!q || searchStr.indexOf(q) !== -1);
            var show = typeMatch && qMatch;
            row.style.display = show ? '' : 'none';
            if (show) { anyAccVisible = true; visibleCount++; }
        });

        // Show/hide the whole block
        block.style.display = (typeMatch && anyAccVisible) ? '' : 'none';

        // Show/hide group/subgroup header rows based on whether their account rows are visible
        if (typeMatch) {
            var groupRows    = block.querySelectorAll('tr.coa-group-row, tr.coa-subgroup-row');
            groupRows.forEach(function(gr) { gr.style.display = anyAccVisible ? '' : 'none'; });
        }
    });

    document.getElementById('filter-info').innerHTML =
        'Showing <strong>' + visibleCount + '</strong> of <strong><?php echo $total_accounts; ?></strong> accounts';
}

/* ── Edit modal ─────────────────────────────── */
function openEditModal(id) {
    var overlay = document.getElementById('edit-modal-overlay');
    overlay.classList.add('open');
    // Clear previous values
    document.getElementById('edit-id').value      = '';
    document.getElementById('edit-number').value  = '';
    document.getElementById('edit-name').value    = '';
    document.getElementById('edit-type').value    = '';
    document.getElementById('edit-group').value   = '';
    document.getElementById('edit-subgroup').value= '';

    // Fetch account data
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '<?php echo site_url('accounts/get_account_json'); ?>/' + id, true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                var d = JSON.parse(xhr.responseText);
                if (d.success) {
                    document.getElementById('edit-id').value      = d.id;
                    document.getElementById('edit-number').value  = d.account_number;
                    document.getElementById('edit-name').value    = d.account_name;
                    document.getElementById('edit-type').value    = d.account_type;
                    document.getElementById('edit-group').value   = d.account_group  || '';
                    document.getElementById('edit-subgroup').value= d.account_sub_group || '';
                }
            } catch(e) {}
        }
    };
    xhr.send();
}

function closeEditModal() {
    document.getElementById('edit-modal-overlay').classList.remove('open');
}

function closeEditModalOnBg(e) {
    if (e.target === document.getElementById('edit-modal-overlay')) closeEditModal();
}
</script>
