<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>
/* ── Page shell ─────────────────────────────────────────── */
.jnl-wrapper {
    background: #f0f2f5;
    min-height: 100vh;
}
.jnl-topbar {
    background: #fff;
    padding: 14px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #e0e0e0;
}
.jnl-topbar h2 {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #222;
}
.jnl-breadcrumb {
    font-size: 12px;
    color: #888;
    margin-bottom: 2px;
}
.jnl-breadcrumb a { color: #3da8e0; text-decoration: none; }

/* Add New button */
.btn-add-new {
    background: #2ecc71;
    color: #fff;
    border: none;
    border-radius: 20px;
    padding: 8px 20px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}
.btn-add-new:hover { background: #27ae60; color: #fff; text-decoration: none; }

/* Icon buttons top-right */
.jnl-icon-btns { display: flex; gap: 8px; margin-left: 12px; }
.jnl-icon-btn {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    width: 34px; height: 34px;
    display: inline-flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 16px; color: #555;
    text-decoration: none;
}
.jnl-icon-btn:hover { background: #f5f5f5; color: #222; }

/* Filter bar */
.jnl-filter-bar {
    background: #fff;
    padding: 14px 24px;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    border-bottom: 1px solid #e0e0e0;
}
.jnl-filter-bar label {
    font-size: 12px;
    font-weight: 600;
    color: #555;
    margin-bottom: 3px;
    display: block;
}
.jnl-filter-group { display: flex; flex-direction: column; }
.jnl-filter-group select,
.jnl-filter-group input {
    border: 1px solid #ccc;
    border-radius: 4px;
    height: 34px;
    padding: 4px 10px;
    font-size: 13px;
    min-width: 150px;
}
.jnl-filter-group select { min-width: 180px; }

.btn-expand {
    background: none; border: none; color: #888; font-size: 18px;
    cursor: pointer; padding: 4px 6px;
}
.btn-clear {
    background: none;
    border: 1px solid #3da8e0;
    color: #3da8e0;
    border-radius: 20px;
    padding: 5px 18px;
    font-size: 13px;
    cursor: pointer;
    white-space: nowrap;
}
.btn-clear:hover { background: #eaf5fb; }

/* Table area */
.jnl-table-area {
    background: #fff;
    margin: 0;
    padding: 0 24px 24px;
}
.jnl-table-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 0 10px;
    font-size: 13px;
    color: #555;
}
.jnl-show-entries { display: flex; align-items: center; gap: 6px; }
.jnl-show-entries select {
    border: 1px solid #ccc; border-radius: 4px;
    padding: 3px 6px; font-size: 13px;
}

table.jnl-table {
    width: 100%;
    border-collapse: collapse;
}
table.jnl-table thead tr {
    background: #3da8e0;
    color: #fff;
}
table.jnl-table thead th {
    padding: 11px 14px;
    font-size: 13px;
    font-weight: 600;
    border: none;
    white-space: nowrap;
    cursor: pointer;
    user-select: none;
}
table.jnl-table thead th.text-right { text-align: right; }
table.jnl-table thead th .sort-icon { font-size: 10px; margin-left: 4px; opacity: .6; }
table.jnl-table thead th.cb-col { width: 40px; text-align: center; cursor: default; }
table.jnl-table thead th.action-col { width: 100px; text-align: right; cursor: default; }

table.jnl-table tbody tr {
    border-bottom: 1px solid #f0f0f0;
    transition: background .1s;
}
table.jnl-table tbody tr:hover { background: #f7fbff; }
table.jnl-table tbody td {
    padding: 10px 14px;
    font-size: 13px;
    color: #333;
    vertical-align: middle;
}
table.jnl-table tbody td.td-id a {
    color: #3da8e0;
    font-weight: 600;
    text-decoration: none;
}
table.jnl-table tbody td.td-total { text-align: right; font-weight: 600; }
table.jnl-table tbody td.cb-col { text-align: center; }
table.jnl-table tbody td.action-col { text-align: right; white-space: nowrap; }

/* View button with dropdown arrow */
.btn-view-wrap { display: inline-flex; align-items: stretch; }
.btn-view {
    background: none;
    border: 1px solid #ccc;
    border-right: none;
    border-radius: 4px 0 0 4px;
    padding: 4px 14px;
    font-size: 13px;
    color: #333;
    cursor: pointer;
}
.btn-view:hover { background: #f5f5f5; }
.btn-view-caret {
    background: none;
    border: 1px solid #ccc;
    border-left: 1px solid #eee;
    border-radius: 0 4px 4px 0;
    padding: 4px 8px;
    font-size: 12px;
    color: #555;
    cursor: pointer;
    position: relative;
}
.btn-view-caret:hover { background: #f5f5f5; }
.view-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 120px;
    z-index: 100;
    box-shadow: 0 2px 8px rgba(0,0,0,0.12);
}
.view-dropdown a {
    display: block;
    padding: 7px 14px;
    font-size: 13px;
    color: #333;
    text-decoration: none;
}
.view-dropdown a:hover { background: #f5f5f5; color: #e74c3c; }
.view-dd-wrap { position: relative; display: inline-flex; }

/* Pagination */
.jnl-pagination {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 0 0;
    font-size: 13px;
    color: #666;
}
.jnl-pages { display: flex; gap: 4px; align-items: center; }
.page-btn {
    width: 30px; height: 30px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 1px solid #ddd; border-radius: 50%;
    background: none; font-size: 13px; cursor: pointer; color: #555;
}
.page-btn.active { background: #3da8e0; color: #fff; border-color: #3da8e0; }
.page-btn:hover:not(.active) { background: #f0f0f0; }
.page-btn.arrow { border: none; background: none; color: #aaa; font-size: 16px; }
.page-btn.arrow:hover { color: #333; }

/* Flash message */
.jnl-flash { padding: 8px 24px 0; }

/* Empty state */
.jnl-empty { text-align: center; padding: 40px; color: #aaa; font-size: 14px; }
</style>

<div class="content-wrapper jnl-wrapper">
    <!-- Top bar -->
    <div class="jnl-topbar">
        <div>
            <div class="jnl-breadcrumb">
                <a href="<?php echo base_url(); ?>">Home</a> /
                <a href="<?php echo base_url(); ?>accounts/journal_new_list">Journals</a>
            </div>
            <h2>Journals</h2>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <a href="<?php echo base_url(); ?>accounts/journal_new" class="btn-add-new">
                <i class="fa fa-plus"></i> Add New
            </a>
            <div class="jnl-icon-btns">
                <a href="javascript:window.print()" class="jnl-icon-btn" title="Print"><i class="fa fa-print"></i></a>
                <a href="#" class="jnl-icon-btn" title="Export Excel" id="btn-export-excel"><i class="fa fa-file-excel-o" style="color:#e74c3c;"></i></a>
            </div>
        </div>
    </div>

    <?php if ($this->session->flashdata('msg')): ?>
        <div class="jnl-flash"><?php echo $this->session->flashdata('msg'); ?></div>
    <?php endif; ?>

    <!-- Filter bar -->
    <form method="get" action="<?php echo site_url('accounts/journal_new_list'); ?>" id="filter-form">
        <div class="jnl-filter-bar">
            <div class="jnl-filter-group">
                <label>Nominals</label>
                <select name="account_id" id="filter-account">
                    <option value="">Select</option>
                    <?php foreach ($accounts as $acc): ?>
                        <option value="<?php echo $acc['id']; ?>"
                            <?php echo (isset($filters['account_id']) && $filters['account_id'] == $acc['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($acc['account_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="jnl-filter-group">
                <label>Ref No.</label>
                <input type="text" name="ref_no" id="filter-ref"
                       placeholder="Ref No."
                       value="<?php echo htmlspecialchars($filters['ref_no'] ?? ''); ?>" />
            </div>

            <div class="jnl-filter-group">
                <label>Date Range</label>
                <select name="date_range" id="filter-date-range">
                    <option value="">All Dates</option>
                    <option value="this_month"  <?php echo (($filters['start_date'] ?? '') == date('Y-m-01') && ($filters['end_date'] ?? '') == date('Y-m-t')) ? 'selected' : ''; ?>>This Month</option>
                    <option value="last_month"  >Last Month</option>
                    <option value="this_year"   >This Year</option>
                    <option value="custom"      >Custom</option>
                </select>
            </div>

            <button type="button" class="btn-expand" id="btn-expand-filter" title="Custom date range">
                <i class="fa fa-expand"></i>
            </button>

            <div id="custom-date-wrap" style="display:none; gap:10px; flex-wrap:wrap; align-items:flex-end;">
                <div class="jnl-filter-group">
                    <label>From</label>
                    <input type="date" name="start_date" id="filter-start"
                           value="<?php echo htmlspecialchars($filters['start_date'] ?? ''); ?>" />
                </div>
                <div class="jnl-filter-group">
                    <label>To</label>
                    <input type="date" name="end_date" id="filter-end"
                           value="<?php echo htmlspecialchars($filters['end_date'] ?? ''); ?>" />
                </div>
            </div>

            <button type="submit" class="btn-add-new" style="margin-top:18px; background:#3da8e0;">
                <i class="fa fa-search"></i> Search
            </button>

            <button type="button" class="btn-clear" style="margin-top:18px;" id="btn-clear">Clear</button>

            <div style="margin-left:auto; display:flex; align-items:center; gap:8px; margin-top:18px;">
                <span style="font-size:13px;color:#555;">Show</span>
                <select id="page-size-select" style="border:1px solid #ccc;border-radius:4px;padding:4px 8px;font-size:13px;">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                <span style="font-size:13px;color:#555;">entries</span>
            </div>
        </div>
    </form>

    <!-- Table -->
    <div class="jnl-table-area">
        <div class="table-responsive">
            <table class="jnl-table" id="jnl-main-table">
                <thead>
                    <tr>
                        <th class="cb-col"><input type="checkbox" id="chk-all" /></th>
                        <th data-col="id">Journal ID <span class="sort-icon fa fa-sort"></span></th>
                        <th data-col="date">Date <span class="sort-icon fa fa-sort"></span></th>
                        <th data-col="ref">Ref. No. <span class="sort-icon fa fa-sort"></span></th>
                        <th class="text-right" data-col="total">Total <span class="sort-icon fa fa-sort"></span></th>
                        <th class="action-col">Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($journal_entries)): ?>
                    <tr><td colspan="6" class="jnl-empty"><i class="fa fa-inbox"></i> No journal entries found.</td></tr>
                <?php else: ?>
                    <?php foreach ($journal_entries as $entry): ?>
                    <tr>
                        <td class="cb-col"><input type="checkbox" class="row-chk" value="<?php echo $entry['id']; ?>"></td>
                        <td class="td-id">
                            <a href="javascript:void(0)" onclick="viewJournalEntry(<?php echo $entry['id']; ?>)">
                                <?php echo $entry['id']; ?>
                            </a>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($entry['voucher_date'])); ?></td>
                        <td><?php echo htmlspecialchars($entry['voucher_number']); ?></td>
                        <td class="td-total"><?php echo number_format((float)$entry['total'], 2); ?></td>
                        <td class="action-col">
                            <div class="view-dd-wrap">
                                <button type="button" class="btn-view" onclick="viewJournalEntry(<?php echo $entry['id']; ?>)">View</button>
                                <button type="button" class="btn-view-caret" onclick="toggleDropdown(this)">
                                    <i class="fa fa-chevron-down"></i>
                                    <div class="view-dropdown">
                                        <a href="javascript:void(0)" onclick="printJournalEntry(<?php echo $entry['id']; ?>)">
                                            <i class="fa fa-print"></i> Print
                                        </a>
                                        <a href="<?php echo base_url(); ?>accounts/delete_journal_new/<?php echo $entry['id']; ?>"
                                           onclick="return confirm('Delete this journal entry? This cannot be undone.');"
                                           style="color:#e74c3c;">
                                            <i class="fa fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination info -->
        <div class="jnl-pagination">
            <span id="pagination-info">
                Showing 1 to <?php echo count($journal_entries); ?> of <?php echo count($journal_entries); ?> entries
            </span>
            <div class="jnl-pages">
                <button class="page-btn arrow" id="btn-prev"><i class="fa fa-angle-left"></i></button>
                <button class="page-btn active" id="page-1">1</button>
                <button class="page-btn arrow" id="btn-next"><i class="fa fa-angle-right"></i></button>
            </div>
        </div>
    </div>
</div>

<!-- View / Print Modal -->
<div id="jnl-modal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background:#3da8e0;color:#fff;border-radius:4px 4px 0 0;">
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;">&times;</button>
                <h4 class="modal-title"><i class="fa fa-book"></i> Journal Entry Details</h4>
            </div>
            <div class="modal-body" id="jnl-modal-body">
                <div style="text-align:center;padding:30px;color:#aaa;">
                    <i class="fa fa-spinner fa-spin fa-2x"></i>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="btn-modal-print"><i class="fa fa-print"></i> Print</button>
            </div>
        </div>
    </div>
</div>

<script>
var jnlBaseUrl = '<?php echo base_url(); ?>';
var currentPrintId = null;

/* ── Dropdown toggle ──────────────────────────────────── */
function toggleDropdown(btn) {
    var dd = btn.querySelector('.view-dropdown');
    var allDd = document.querySelectorAll('.view-dropdown');
    allDd.forEach(function(d) { if (d !== dd) d.style.display = 'none'; });
    dd.style.display = (dd.style.display === 'block') ? 'none' : 'block';
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('.view-dd-wrap')) {
        document.querySelectorAll('.view-dropdown').forEach(function(d){ d.style.display='none'; });
    }
});

/* ── View modal ───────────────────────────────────────── */
function buildEntryHtml(data) {
    var e = data.journal_entry;
    var details = data.journal_entry_details;
    var html = '';
    html += '<table class="table" style="margin-bottom:10px;">';
    html += '<tr><td><strong>Date:</strong> ' + e.voucher_date + '</td>';
    html += '<td><strong>Ref. No.:</strong> ' + e.voucher_number + '</td></tr>';
    if (e.voucher_narration && e.voucher_narration !== e.voucher_number) {
        html += '<tr><td colspan="2"><strong>Narration:</strong> ' + e.voucher_narration + '</td></tr>';
    }
    html += '</table>';
    html += '<table class="table table-bordered table-condensed">';
    html += '<thead><tr style="background:#3da8e0;color:#fff;">';
    html += '<th>Nominal Account</th><th class="text-right">Debit</th><th class="text-right">Credit</th><th>Description</th>';
    html += '</tr></thead><tbody>';

    var totalDebit = 0, totalCredit = 0;
    details.forEach(function(d) {
        var dr = parseFloat(d.debit_amount)  || 0;
        var cr = parseFloat(d.credit_amount) || 0;
        if (dr > 0) {
            html += '<tr>';
            html += '<td>' + d.debit_account_name + ' (' + d.debit_account_number + ')</td>';
            html += '<td class="text-right">' + dr.toFixed(2) + '</td>';
            html += '<td class="text-right"></td>';
            html += '<td>' + (d.description || '') + '</td>';
            html += '</tr>';
            totalDebit += dr;
        }
        if (cr > 0) {
            html += '<tr>';
            html += '<td>' + d.credit_account_name + ' (' + d.credit_account_number + ')</td>';
            html += '<td class="text-right"></td>';
            html += '<td class="text-right">' + cr.toFixed(2) + '</td>';
            html += '<td>' + (d.description || '') + '</td>';
            html += '</tr>';
            totalCredit += cr;
        }
    });

    html += '<tr style="background:#f5f5f5;font-weight:700;">';
    html += '<td>Total</td>';
    html += '<td class="text-right">' + totalDebit.toFixed(2) + '</td>';
    html += '<td class="text-right">' + totalCredit.toFixed(2) + '</td>';
    html += '<td></td></tr>';
    html += '</tbody></table>';
    return html;
}

function viewJournalEntry(id) {
    currentPrintId = id;
    $('#jnl-modal-body').html('<div style="text-align:center;padding:30px;color:#aaa;"><i class="fa fa-spinner fa-spin fa-2x"></i></div>');
    $('#jnl-modal').modal('show');
    $.ajax({
        url: jnlBaseUrl + 'accounts/get_journal_entry_details/' + id,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            $('#jnl-modal-body').html(buildEntryHtml(data));
        },
        error: function() {
            $('#jnl-modal-body').html('<p class="text-danger">Failed to load entry details.</p>');
        }
    });
}

/* ── Print ────────────────────────────────────────────── */
function printJournalEntry(id) {
    $.ajax({
        url: jnlBaseUrl + 'accounts/get_journal_entry_details/' + id,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            var content = buildEntryHtml(data);
            var win = window.open('', 'Print', 'height=650,width=900');
            win.document.write('<html><head><title>Journal Entry</title>');
            win.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
            win.document.write('<style>body{padding:20px;} thead tr{background:#3da8e0!important;color:#fff!important;-webkit-print-color-adjust:exact;}</style>');
            win.document.write('</head><body>');
            win.document.write(content);
            win.document.write('</body></html>');
            win.document.close();
            win.onload = function() {
                setTimeout(function() { win.focus(); win.print(); win.close(); }, 50);
            };
        }
    });
}

$('#btn-modal-print').on('click', function() {
    if (currentPrintId) printJournalEntry(currentPrintId);
});

/* ── Filters ──────────────────────────────────────────── */
$('#btn-clear').on('click', function() {
    window.location.href = jnlBaseUrl + 'accounts/journal_new_list';
});

var $dateRange = $('#filter-date-range');
var $customWrap = $('#custom-date-wrap');
var $startInp  = $('#filter-start');
var $endInp    = $('#filter-end');

function applyDateRange(val) {
    var now = new Date();
    if (val === 'this_month') {
        var m = String(now.getMonth()+1).padStart(2,'0');
        var y = now.getFullYear();
        $startInp.val(y+'-'+m+'-01');
        var last = new Date(y, now.getMonth()+1, 0).getDate();
        $endInp.val(y+'-'+m+'-'+String(last).padStart(2,'0'));
        $customWrap.show();
    } else if (val === 'last_month') {
        var d = new Date(now.getFullYear(), now.getMonth()-1, 1);
        var m = String(d.getMonth()+1).padStart(2,'0');
        var y = d.getFullYear();
        var last = new Date(y, d.getMonth()+1, 0).getDate();
        $startInp.val(y+'-'+m+'-01');
        $endInp.val(y+'-'+m+'-'+String(last).padStart(2,'0'));
        $customWrap.show();
    } else if (val === 'this_year') {
        $startInp.val(now.getFullYear()+'-01-01');
        $endInp.val(now.getFullYear()+'-12-31');
        $customWrap.show();
    } else if (val === 'custom') {
        $customWrap.show();
    } else {
        $startInp.val('');
        $endInp.val('');
        $customWrap.hide();
    }
}

$dateRange.on('change', function() { applyDateRange($(this).val()); });
$('#btn-expand-filter').on('click', function() {
    $customWrap.toggle();
});

// Show custom dates if they are pre-filled from server
<?php if (!empty($filters['start_date']) || !empty($filters['end_date'])): ?>
$customWrap.show();
<?php endif; ?>

/* ── Select-all checkbox ──────────────────────────────── */
$('#chk-all').on('change', function() {
    $('.row-chk').prop('checked', this.checked);
});

/* ── Client-side page-size control (simple show/hide) ─── */
var pageSize = 25;
$('#page-size-select').on('change', function() {
    pageSize = parseInt($(this).val());
    renderPage(currentPage);
});

var allRows = [];
var currentPage = 1;

$(document).ready(function() {
    allRows = Array.from($('#jnl-main-table tbody tr'));
    renderPage(1);

    $('#btn-prev').on('click', function() { if (currentPage > 1) renderPage(currentPage - 1); });
    $('#btn-next').on('click', function() { if (currentPage < totalPages()) renderPage(currentPage + 1); });
});

function totalPages() {
    return Math.max(1, Math.ceil(allRows.length / pageSize));
}

function renderPage(page) {
    currentPage = page;
    var start = (page - 1) * pageSize;
    var end   = start + pageSize;
    allRows.forEach(function(row, i) {
        row.style.display = (i >= start && i < end) ? '' : 'none';
    });
    var showing = Math.min(end, allRows.length);
    $('#pagination-info').text('Showing ' + (allRows.length ? start+1 : 0) + ' to ' + showing + ' of ' + allRows.length + ' entries');

    // Rebuild page buttons
    var $pages = $('.jnl-pages');
    $pages.find('.page-num-btn').remove();
    var tp = totalPages();
    var btnPrev = $('#btn-prev').detach();
    var btnNext = $('#btn-next').detach();
    $pages.empty().append(btnPrev);
    for (var p = 1; p <= tp; p++) {
        (function(pg) {
            var btn = $('<button class="page-btn page-num-btn">' + pg + '</button>');
            if (pg === page) btn.addClass('active');
            btn.on('click', function() { renderPage(pg); });
            $pages.append(btn);
        })(p);
    }
    $pages.append(btnNext);
}
</script>

