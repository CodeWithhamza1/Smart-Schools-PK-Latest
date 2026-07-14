<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$fee_items = (isset($feearray) && is_array($feearray)) ? $feearray : array();
$student = !empty($fee_items) ? $fee_items[0] : null;
$school_name = isset($sch_setting->name) ? trim($sch_setting->name) : '';
$school_address = isset($sch_setting->address) ? trim($sch_setting->address) : '';
$school_phone = isset($sch_setting->phone) ? trim($sch_setting->phone) : '';
$school_logo = !empty($sch_setting->image) ? base_url() . 'uploads/school_content/logo/' . $sch_setting->image : '';
$print_date = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat(date('Y-m-d')));
$rows = array();
$totals = array(
    'base' => 0,
    'paid' => 0,
    'discount' => 0,
    'fine' => 0,
    'payable' => 0,
);

foreach ($fee_items as $fee_item) {
    if (!isset($fee_item->amount)) {
        continue;
    }

    if (!empty($fee_item->is_system) && isset($fee_item->student_fees_master_amount)) {
        $fee_item->amount = $fee_item->student_fees_master_amount;
    }

    $fee_paid = 0;
    $fee_discount = 0;
    $amount_details = !empty($fee_item->amount_detail) ? json_decode($fee_item->amount_detail, true) : array();

    if (is_array($amount_details)) {
        foreach ($amount_details as $amount_detail) {
            $fee_paid += isset($amount_detail['amount']) ? (float) $amount_detail['amount'] : 0;
            $fee_discount += isset($amount_detail['amount_discount']) ? (float) $amount_detail['amount_discount'] : 0;
        }
    }

    $fine_due = 0;
    if (!empty($fee_item->fine_amount) && !empty($fee_item->due_date) && $fee_item->due_date !== '0000-00-00') {
        if (strtotime($fee_item->due_date) < strtotime(date('Y-m-d'))) {
            $fine_due = (float) $fee_item->fine_amount;
        }
    }

    $base_amount = (float) $fee_item->amount;
    $payable_amount = max(0, ($base_amount + $fine_due) - ($fee_paid + $fee_discount));
    $due_date = '-';
    $month_label = '-';

    if (!empty($fee_item->due_date) && $fee_item->due_date !== '0000-00-00') {
        $due_date = date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($fee_item->due_date));
        $month_label = date('M Y', strtotime($fee_item->due_date));
    }

    $rows[] = array(
        'type' => isset($fee_item->type) ? $fee_item->type : (isset($fee_item->code) ? $fee_item->code : 'Fee'),
        'month' => $month_label,
        'due_date' => $due_date,
        'base' => $base_amount,
        'paid' => $fee_paid,
        'discount' => $fee_discount,
        'fine' => $fine_due,
        'payable' => $payable_amount,
    );

    $totals['base'] += $base_amount;
    $totals['paid'] += $fee_paid;
    $totals['discount'] += $fee_discount;
    $totals['fine'] += $fine_due;
    $totals['payable'] += $payable_amount;
}

if (!function_exists('render_fee_challan_copy')) {
    function render_fee_challan_copy($copy_label, $student, $rows, $totals, $currency_symbol, $school_name, $school_address, $school_phone, $school_logo, $print_date)
    {
        $student_name = $student ? trim($student->firstname . ' ' . $student->lastname) : '-';
        $student_class = $student ? trim($student->class . (!empty($student->section) ? ' (' . $student->section . ')' : '')) : '-';
        ?>
        <section class="fee-challan-copy">
            <div class="fee-challan-copy__header">
                <div class="fee-challan-school">
                    <?php if ($school_logo != '') { ?>
                        <img src="<?php echo $school_logo; ?>" alt="School Logo" class="fee-challan-school__logo">
                    <?php } ?>
                    <div class="fee-challan-school__details">
                        <h2><?php echo html_escape($school_name != '' ? $school_name : 'School'); ?></h2>
                        <?php if ($school_address != '') { ?>
                            <p><?php echo html_escape($school_address); ?></p>
                        <?php } ?>
                        <?php if ($school_phone != '') { ?>
                            <p><?php echo html_escape($school_phone); ?></p>
                        <?php } ?>
                    </div>
                </div>
                <div class="fee-challan-copy__badge"><?php echo html_escape($copy_label); ?></div>
            </div>

            <table class="fee-challan-info">
                <tr>
                    <td><span>Student</span><strong><?php echo html_escape($student_name != '' ? $student_name : '-'); ?></strong></td>
                    <td><span>Admission No</span><strong><?php echo $student ? html_escape($student->admission_no) : '-'; ?></strong></td>
                    <td><span>Class</span><strong><?php echo html_escape($student_class != '' ? $student_class : '-'); ?></strong></td>
                </tr>
                <tr>
                    <td colspan="2"><span>Father Name</span><strong><?php echo $student ? html_escape($student->father_name) : '-'; ?></strong></td>
                    <td><span>Print Date</span><strong><?php echo html_escape($print_date); ?></strong></td>
                </tr>
            </table>

            <table class="fee-challan-table">
                <thead>
                    <tr>
                        <th>Particulars</th>
                        <th>Due Date</th>
                        <th class="text-right">Amount</th>
                        <th class="text-right">Adj.</th>
                        <th class="text-right">Payable</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)) { ?>
                        <tr>
                            <td colspan="5" class="fee-challan-empty">No fee items available.</td>
                        </tr>
                    <?php } ?>
                    <?php foreach ($rows as $row) { ?>
                        <tr>
                            <td>
                                <strong><?php echo html_escape($row['type']); ?></strong>
                                <span class="fee-challan-muted"><?php echo html_escape($row['month']); ?></span>
                            </td>
                            <td><?php echo html_escape($row['due_date']); ?></td>
                            <td class="text-right"><?php echo $currency_symbol . number_format($row['base'], 2, '.', ''); ?></td>
                            <td class="text-right">
                                <span>P: <?php echo number_format($row['paid'], 2, '.', ''); ?></span>
                                <span>D: <?php echo number_format($row['discount'], 2, '.', ''); ?></span>
                                <span>F: <?php echo number_format($row['fine'], 2, '.', ''); ?></span>
                            </td>
                            <td class="text-right fee-challan-payable"><?php echo $currency_symbol . number_format($row['payable'], 2, '.', ''); ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <table class="fee-challan-summary">
                <tr>
                    <td><span>Base</span><strong><?php echo $currency_symbol . number_format($totals['base'], 2, '.', ''); ?></strong></td>
                    <td><span>Paid</span><strong><?php echo $currency_symbol . number_format($totals['paid'], 2, '.', ''); ?></strong></td>
                    <td><span>Discount</span><strong><?php echo $currency_symbol . number_format($totals['discount'], 2, '.', ''); ?></strong></td>
                    <td><span>Fine</span><strong><?php echo $currency_symbol . number_format($totals['fine'], 2, '.', ''); ?></strong></td>
                    <td class="fee-challan-summary__highlight"><span>Total Payable</span><strong><?php echo $currency_symbol . number_format($totals['payable'], 2, '.', ''); ?></strong></td>
                </tr>
            </table>

            <div class="fee-challan-footer">
                <div class="fee-challan-notes">
                    Bring this copy at the time of fee submission and verify all particulars before payment.
                </div>
                <div class="fee-challan-signature">
                    <span>Authorized Signature</span>
                </div>
            </div>
        </section>
        <?php
    }
}
?>

<style type="text/css">
    body {
        margin: 0;
        padding: 18px;
        background: #f3f5f7;
        color: #1c2530;
        font-family: Arial, Helvetica, sans-serif;
    }

    .fee-challan-sheet {
        max-width: 920px;
        margin: 0 auto;
        display: grid;
        gap: 10px;
    }

    .fee-challan-copy {
        background: #ffffff;
        border: 1px solid #1f2d3d;
        border-radius: 4px;
        padding: 8px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        page-break-inside: avoid;
    }

    .fee-challan-copy + .fee-challan-copy {
        border-top: 2px dashed #8b98a7;
    }

    .fee-challan-copy__header {
        align-items: flex-start;
        border-bottom: 2px solid #1f2d3d;
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding-bottom: 6px;
    }

    .fee-challan-school {
        align-items: center;
        display: flex;
        gap: 14px;
        min-width: 0;
    }

    .fee-challan-school__logo {
        height: 40px;
        width: 40px;
        object-fit: contain;
    }

    .fee-challan-school__details h2 {
        font-size: 16px;
        font-weight: 700;
        line-height: 1.1;
        margin: 0 0 2px;
    }

    .fee-challan-school__details p {
        font-size: 10px;
        margin: 1px 0;
    }

    .fee-challan-copy__badge {
        background: #1f2d3d;
        border-radius: 999px;
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.08em;
        padding: 4px 9px;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .fee-challan-info,
    .fee-challan-summary {
        border-collapse: collapse;
        margin: 6px 0;
        width: 100%;
    }

    .fee-challan-info td,
    .fee-challan-summary td {
        border: 1px solid #cfd8e3;
        padding: 5px 6px;
        vertical-align: top;
    }

    .fee-challan-info span,
    .fee-challan-summary span {
        color: #52606d;
        display: block;
        font-size: 9px;
        margin-bottom: 2px;
        text-transform: uppercase;
    }

    .fee-challan-info strong,
    .fee-challan-summary strong {
        display: block;
        font-size: 10px;
        line-height: 1.15;
    }

    .fee-challan-table {
        border-collapse: collapse;
        margin-bottom: 6px;
        width: 100%;
    }

    .fee-challan-table th,
    .fee-challan-table td {
        border: 1px solid #cfd8e3;
        font-size: 10px;
        padding: 4px 5px;
        vertical-align: top;
    }

    .fee-challan-table th {
        background: #eaf0f6;
        font-size: 9px;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .fee-challan-table .text-right {
        text-align: right;
    }

    .fee-challan-muted {
        color: #52606d;
        display: block;
        font-size: 8px;
        margin-top: 1px;
    }

    .fee-challan-table td span {
        display: block;
        font-size: 8px;
        line-height: 1.15;
    }

    .fee-challan-payable {
        font-weight: 700;
    }

    .fee-challan-empty {
        color: #8a4b08;
        padding: 14px;
        text-align: center;
    }

    .fee-challan-summary__highlight {
        background: #1f2d3d;
        border-color: #1f2d3d;
        color: #ffffff;
    }

    .fee-challan-summary__highlight span {
        color: #d9e2ec;
    }

    .fee-challan-footer {
        align-items: flex-end;
        display: flex;
        gap: 12px;
        justify-content: space-between;
        margin-top: 6px;
    }

    .fee-challan-notes {
        font-size: 9px;
        line-height: 1.2;
    }

    .fee-challan-signature {
        min-width: 150px;
        padding-top: 12px;
        text-align: center;
    }

    .fee-challan-signature span {
        border-top: 1px solid #1f2d3d;
        display: block;
        font-size: 9px;
        padding-top: 4px;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 4mm;
        }

        body {
            background: #ffffff;
            padding: 0;
        }

        .fee-challan-sheet {
            max-width: none;
            gap: 2mm;
            grid-template-rows: auto auto;
        }

        .fee-challan-copy {
            border-radius: 0;
            box-shadow: none;
            margin: 0;
            min-height: 0;
            padding: 6px;
        }

        .fee-challan-copy + .fee-challan-copy {
            margin-top: 0;
        }

        .fee-challan-copy__header {
            gap: 8px;
            padding-bottom: 4px;
        }

        .fee-challan-school {
            gap: 8px;
        }

        .fee-challan-school__logo {
            height: 34px;
            width: 34px;
        }

        .fee-challan-school__details h2 {
            font-size: 13px;
            margin-bottom: 1px;
        }

        .fee-challan-school__details p,
        .fee-challan-copy__badge,
        .fee-challan-info span,
        .fee-challan-table th,
        .fee-challan-table td,
        .fee-challan-table td span,
        .fee-challan-summary span,
        .fee-challan-notes,
        .fee-challan-signature span {
            font-size: 8px;
        }

        .fee-challan-info,
        .fee-challan-summary {
            margin: 4px 0;
        }

        .fee-challan-info td,
        .fee-challan-summary td {
            padding: 3px 4px;
        }

        .fee-challan-info strong,
        .fee-challan-summary strong {
            font-size: 8px;
            line-height: 1.1;
        }

        .fee-challan-table {
            margin-bottom: 4px;
        }

        .fee-challan-table th,
        .fee-challan-table td {
            padding: 3px 4px;
        }

        .fee-challan-muted {
            font-size: 7px;
            margin-top: 1px;
        }

        .fee-challan-footer {
            gap: 8px;
            margin-top: 4px;
        }

        .fee-challan-signature {
            min-width: 110px;
            padding-top: 8px;
        }
    }

    @media (max-width: 768px) {
        body {
            padding: 10px;
        }

        .fee-challan-copy {
            padding: 14px;
        }

        .fee-challan-copy__header,
        .fee-challan-footer,
        .fee-challan-school {
            display: block;
        }

        .fee-challan-copy__badge {
            display: inline-block;
            margin-top: 12px;
        }

        .fee-challan-school__logo {
            margin-bottom: 10px;
        }

        .fee-challan-signature {
            margin-top: 16px;
            min-width: 0;
            width: 100%;
        }
    }
</style>

<div class="fee-challan-sheet">
    <?php render_fee_challan_copy('Student Copy', $student, $rows, $totals, $currency_symbol, $school_name, $school_address, $school_phone, $school_logo, $print_date); ?>
    <?php render_fee_challan_copy('School Copy', $student, $rows, $totals, $currency_symbol, $school_name, $school_address, $school_phone, $school_logo, $print_date); ?>
</div>
