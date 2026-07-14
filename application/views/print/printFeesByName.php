<?php 
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();

// Detect thermal printer from controller data, URL parameters, or session
$is_thermal_printer = false;
if (isset($thermal_printer) && $thermal_printer) {
    $is_thermal_printer = true;
} elseif (isset($_GET['thermal']) && $_GET['thermal'] == '1') {
    $is_thermal_printer = true;
} elseif (isset($_SESSION['thermal_printer']) && $_SESSION['thermal_printer'] == true) {
    $is_thermal_printer = true;
} elseif (isset($_SERVER['HTTP_USER_AGENT']) && 
          (stripos($_SERVER['HTTP_USER_AGENT'], 'thermal') !== false || 
           stripos($_SERVER['HTTP_USER_AGENT'], 'pos') !== false ||
           stripos($_SERVER['HTTP_USER_AGENT'], 'receipt') !== false)) {
    $is_thermal_printer = true;
}

// Set thermal printer class for body
$thermal_class = $is_thermal_printer ? 'thermal-printer' : '';
?>
<style type="text/css"> 
    .page-break { display: block; page-break-before: always; }
    
    /* Thermal Printer Optimized Styles */
    @media print {
        * {
            box-sizing: border-box;
        }
        
        body {
            margin: 0;
            padding: 0;
            font-family: 'Courier New', monospace;
            font-size: 9px;
            line-height: 1.1;
            color: #000;
            background: #fff;
            width: 75mm;
            max-width: 75mm;
        }
        
        .thermal-container {
            width: 75mm;
            max-width: 75mm;
            margin: 0 auto;
            padding: 1mm;
        }
        
        .thermal-receipt {
            width: 100%;
            border: 1px solid #000;
            padding: 2mm;
            margin-bottom: 1mm;
            page-break-inside: avoid;
        }
        
        .thermal-header {
            text-align: center;
            margin-bottom: 2mm;
            border-bottom: 1px dashed #000;
            padding-bottom: 1mm;
        }
        
        .thermal-logo {
            max-width: 30px;
            max-height: 30px;
            margin-bottom: 1mm;
        }
        
        .thermal-school-name {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 1mm;
        }
        
        .thermal-school-address {
            font-size: 8px;
            line-height: 1.1;
            margin-bottom: 1mm;
        }
        
        .thermal-school-phone {
            font-size: 8px;
        }
        
        .thermal-receipt-title {
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            margin: 2mm 0;
            text-decoration: underline;
        }
        
        .thermal-student-info {
            margin-bottom: 2mm;
            font-size: 8px;
        }
        
        .thermal-student-name {
            font-weight: bold;
            font-size: 9px;
        }
        
        .thermal-payment-info {
            margin-bottom: 2mm;
            font-size: 8px;
        }
        
        .thermal-payment-id {
            font-weight: bold;
        }
        
        .thermal-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2mm;
            font-size: 7px;
            table-layout: fixed;
        }
        
        .thermal-table th,
        .thermal-table td {
            padding: 1mm;
            border: 1px solid #000;
            text-align: left;
            word-wrap: break-word;
        }
        
        .thermal-table th:nth-child(1),
        .thermal-table td:nth-child(1) {
            width: 15%;
        }
        
        .thermal-table th:nth-child(2),
        .thermal-table td:nth-child(2) {
            width: 20%;
        }
        
        .thermal-table th:nth-child(3),
        .thermal-table td:nth-child(3) {
            width: 35%;
        }
        
        .thermal-table th:nth-child(4),
        .thermal-table td:nth-child(4) {
            width: 30%;
        }
        
        .thermal-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .thermal-table .text-right {
            text-align: right;
        }
        
        .thermal-total {
            border-top: 2px solid #000;
            font-weight: bold;
            font-size: 9px;
            padding-top: 1mm;
            margin-top: 2mm;
        }
        
        .thermal-footer {
            text-align: center;
            font-size: 7px;
            margin-top: 2mm;
            border-top: 1px dashed #000;
            padding-top: 1mm;
        }
        
        .thermal-copy-label {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 1mm;
        }
        
        /* Hide elements not needed for thermal printing */
        .thermal-hidden {
            display: none !important;
        }
        
        /* Ensure proper page breaks */
        .page-break {
            page-break-before: always;
        }
        
        /* Optimize for thermal printer paper */
        @page {
            size: 80mm auto;
            margin: 0;
        }
        
        /* Override Bootstrap classes for thermal printing */
        .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12 {
            float: none;
            width: 100%;
            margin: 0;
            padding: 0;
        }
        
        .container {
            width: 80mm !important;
            max-width: 80mm !important;
            margin: 0 auto;
            padding: 0;
        }
        
        .row {
            margin: 0;
            width: 100%;
        }
        
        .visible-xs {
            display: none !important;
        }
        .hidden-xs {
            display: block !important;
        }
        table.hidden-xs {
            display: table;
        }
        tr.hidden-xs {
            display: table-row !important;
        }
        th.hidden-xs,
        td.hidden-xs {
            display: table-cell !important;
        }
        .hidden-xs.hidden-print {
            display: none !important;
        }
        .hidden-sm {
            display: none !important;
        }
        .visible-sm {
            display: block !important;
        }
        table.visible-sm {
            display: table;
        }
        tr.visible-sm {
            display: table-row !important;
        }
        th.visible-sm,
        td.visible-sm {
            display: table-cell !important;
        }
    }
    
    /* Thermal printer specific styles */
    .thermal-printer .thermal-container {
        width: 75mm !important;
        max-width: 75mm !important;
        margin: 0 auto !important;
        padding: 1mm !important;
    }
    
    .thermal-printer body {
        width: 75mm !important;
        max-width: 75mm !important;
        margin: 0 !important;
        padding: 1mm !important;
        font-size: 9px !important;
        line-height: 1.1 !important;
    }
    
    .thermal-active {
        width: 75mm !important;
        max-width: 75mm !important;
    }
    
    /* Screen styles for preview */
    @media screen {
        .thermal-container {
            width: 300px;
            margin: 20px auto;
            border: 1px solid #ccc;
            padding: 10px;
        }
        
        .thermal-receipt {
            width: 100%;
            border: 1px solid #000;
            padding: 10px;
            margin-bottom: 10px;
        }
        
        /* Show thermal printer preview */
        .thermal-printer .thermal-container {
            width: 300px;
            margin: 20px auto;
            border: 2px solid #ff6b6b;
            padding: 10px;
            background-color: #fff5f5;
        }
        
        .thermal-printer .thermal-container::before {
            content: "THERMAL PRINTER MODE - 80mm Width";
            display: block;
            background: #ff6b6b;
            color: white;
            padding: 5px;
            margin: -10px -10px 10px -10px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
    }
</style>

<html lang="en">
    <head>
        <title><?php echo $this->lang->line('fees_receipt'); ?></title>
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/AdminLTE.min.css">
    </head>
    <body class="<?php echo $thermal_class; ?>">
    <!-- Thermal Printer Toggle for Testing -->
    <div style="position: fixed; top: 10px; right: 10px; z-index: 9999; background: #fff; padding: 10px; border: 1px solid #ccc; border-radius: 5px;">
        <button onclick="toggleThermalMode()" style="background: <?php echo $is_thermal_printer ? '#ff6b6b' : '#4CAF50'; ?>; color: white; border: none; padding: 8px 15px; border-radius: 3px; cursor: pointer;">
            <?php echo $is_thermal_printer ? 'Thermal Mode ON' : 'Thermal Mode OFF'; ?>
        </button>
        <br><small style="font-size: 10px; color: #666;">Click to toggle</small>
    </div>
    
    <div class="thermal-container <?php echo $is_thermal_printer ? 'thermal-active' : ''; ?>">
        <!-- Office Copy -->
        <div class="thermal-receipt">
            <div class="thermal-header">
                                <?php
                                if ($sch_setting->image != "") {
                                    ?>
                    <img class="thermal-logo" src="<?php echo base_url(); ?>/uploads/school_content/logo/<?php echo $sch_setting->image; ?>">
                                    <?php
                                }
                                ?>
                <div class="thermal-school-name"><?php echo $sch_setting->name; ?></div>
                <div class="thermal-school-address"><?php echo $sch_setting->address; ?></div>
                <div class="thermal-school-phone"><?php echo $this->lang->line('phone'); ?>: <?php echo $sch_setting->phone; ?></div>
                            </div>
            
            <div class="thermal-receipt-title">FEE RECEIPT - OFFICE COPY</div>
            
            <div class="thermal-student-info">
                <div class="thermal-student-name"><?php echo $feeList->firstname." ".$feeList->lastname; ?> (<?php echo $feeList->admission_no; ?>)</div>
                <div><?php echo $this->lang->line('father_name'); ?>: <?php echo $student['father_name']; ?></div>
                <div><?php echo $this->lang->line('class'); ?>: <?php echo $feeList->class . " (" . $feeList->section . ")"; ?></div>
                            </div>
            
            <div class="thermal-payment-info">
                <div><strong><?php echo $this->lang->line('date'); ?>: <?php
$date = date('d-m-Y');
echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($date));
?></strong></div>
                <div class="thermal-payment-id"><?php echo $this->lang->line('payment_id'); ?>: <?php echo $feeList->id . "/" . $sub_invoice_id; ?></div>
                <div><strong><?php echo $this->lang->line('collected_by'); ?>: <?php
if (isJSON($feeList->amount_detail)) {
    $fee    = json_decode($feeList->amount_detail);
    $record = $fee->{$sub_invoice_id};
    if (!empty($record->received_by)) {
        echo $record->collected_by;
    }
}
?></strong></div>
                            </div>
            
            <div class="thermal-table-container">
                            <?php
if (!empty($feeList)) {
    ?>
                <table class="thermal-table">
                                    <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('date'); ?></th>
                                            <th><?php echo $this->lang->line('fees_code'); ?></th>
                            <th class="text-right"><?php echo $this->lang->line('amount'); ?></th>
                            <th class="text-right"><?php echo $this->lang->line('discount'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
$amount    = 0;
    $discount  = 0;
    $fine      = 0;
    $total     = 0;
    $grd_total = 0;
    if (empty($feeList)) {
        ?>
                                            <tr>
                                <td colspan="4" class="text-danger text-center">
                                                    <?php echo $this->lang->line('no_transaction_found'); ?>
                                                </td>
                                            </tr>
                                            <?php
} else {
        $count = 1;
        ?>
                                            <tr>
                                <td><?php echo date("m-Y", strtotime($record->date)); ?></td>
                                <td><?php echo $feeList->code; ?></td>
                                <td class="text-right">
                                                    <?php
$amount = number_format($record->amount, 2, '.', '');
        echo $currency_symbol . $amount;
        ?>
                                                </td>
                                <td class="text-right">
                                                    <?php
$amount_discount = number_format($record->amount_discount, 2, '.', '');
        echo $currency_symbol . $amount_discount;
        ?>
                                                </td>
                                            </tr>
                                            <?php
}
    ?>
                                    </tbody>
                                </table>
                                <?php
}
?>
            </div>
            
            <div class="thermal-footer">
                <div class="thermal-copy-label">Office Copy</div>
                <div><?php echo date('d-m-Y H:i:s'); ?></div>
                        </div>
                    </div>

        <!-- Student Copy -->
        <div class="thermal-receipt">
            <div class="thermal-header">
                                    <?php
                                    if ($sch_setting->image != "") {
                                        ?>
                    <img class="thermal-logo" src="<?php echo base_url(); ?>/uploads/school_content/logo/<?php echo $sch_setting->image; ?>">
                                        <?php
                                    }
                                    ?>
                <div class="thermal-school-name"><?php echo $sch_setting->name; ?></div>
                <div class="thermal-school-address"><?php echo $sch_setting->address; ?></div>
                <div class="thermal-school-phone"><?php echo $this->lang->line('phone'); ?>: <?php echo $sch_setting->phone; ?></div>
                                </div>
            
            <div class="thermal-receipt-title">FEE RECEIPT - STUDENT COPY</div>
            
            <div class="thermal-student-info">
                <div class="thermal-student-name"><?php echo $feeList->firstname." ".$feeList->lastname; ?> (<?php echo $feeList->admission_no; ?>)</div>
                <div><?php echo $this->lang->line('father_name'); ?>: <?php echo $student['father_name']; ?></div>
                <div><?php echo $this->lang->line('class'); ?>: <?php echo $feeList->class . " (" . $feeList->section . ")"; ?></div>
                                </div>
            
            <div class="thermal-payment-info">
                <div><strong><?php echo $this->lang->line('date'); ?>: <?php
                                            $date = date('d-m-Y');
                                            echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($date));
?></strong></div>
                <div class="thermal-payment-id"><?php echo $this->lang->line('payment_id'); ?>: <?php echo $feeList->id . "/" . $sub_invoice_id; ?></div>
                <div><strong><?php echo $this->lang->line('collected_by'); ?>: <?php
                                            if (isJSON($feeList->amount_detail)) {
                                                $fee    = json_decode($feeList->amount_detail);
                                                $record = $fee->{$sub_invoice_id};
                                                if (!empty($record->received_by)) {
                                                    echo $record->collected_by;
                                                }
                                            }
?></strong></div>
                                </div>
            
            <div class="thermal-table-container">
                                <?php
                                if (!empty($feeList)) {
                                    ?>
                <table class="thermal-table">
                                        <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('date'); ?></th>
                                            <th><?php echo $this->lang->line('fees_code'); ?></th>
                            <th class="text-right"><?php echo $this->lang->line('amount'); ?></th>
                            <th class="text-right"><?php echo $this->lang->line('discount'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        if (empty($feeList)) {
                                            ?>
                                            <tr>
                                <td colspan="4" class="text-danger text-center">
                                                    <?php echo $this->lang->line('no_transaction_found'); ?>
                                                </td>
                                            </tr>
                                            <?php
                                        } else {
                                            ?>
                                            <tr>
                                <td><?php echo date("m-Y", strtotime($record->date)); ?></td>
                                <td><?php echo $feeList->code; ?></td>
                                <td class="text-right">
                                                    <?php
                                                    $amount = number_format($record->amount, 2, '.', '');
                                                    echo $currency_symbol . $amount;
                                                    ?>
                                                </td>
                                <td class="text-right">
                                                    <?php
                                                    $amount_discount = number_format($record->amount_discount, 2, '.', '');
                                                    echo $currency_symbol . $amount_discount;
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                    <?php
                                }
                                ?>
            </div>
            
            <div class="thermal-footer">
                <div class="thermal-copy-label">Student Copy</div>
                <div><?php echo date('d-m-Y H:i:s'); ?></div>
                            </div>
                        </div>

        <!-- Teacher Copy -->
        <div class="thermal-receipt">
            <div class="thermal-header">
                                    <?php
                                    if ($sch_setting->image != "") {
                                        ?>
                    <img class="thermal-logo" src="<?php echo base_url(); ?>/uploads/school_content/logo/<?php echo $sch_setting->image; ?>">
                                        <?php
                                    }
                                    ?>
                <div class="thermal-school-name"><?php echo $sch_setting->name; ?></div>
                <div class="thermal-school-address"><?php echo $sch_setting->address; ?></div>
                <div class="thermal-school-phone"><?php echo $this->lang->line('phone'); ?>: <?php echo $sch_setting->phone; ?></div>
                                </div>
            
            <div class="thermal-receipt-title">FEE RECEIPT - TEACHER COPY</div>
            
            <div class="thermal-student-info">
                <div class="thermal-student-name"><?php echo $feeList->firstname." ".$feeList->lastname; ?> (<?php echo $feeList->admission_no; ?>)</div>
                <div><?php echo $this->lang->line('father_name'); ?>: <?php echo $student['father_name']; ?></div>
                <div><?php echo $this->lang->line('class'); ?>: <?php echo $feeList->class . " (" . $feeList->section . ")"; ?></div>
                                </div>
            
            <div class="thermal-payment-info">
                <div><strong><?php echo $this->lang->line('date'); ?>: <?php
                                            $date = date('d-m-Y');
                                            echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($date));
?></strong></div>
                <div class="thermal-payment-id"><?php echo $this->lang->line('payment_id'); ?>: <?php echo $feeList->id . "/" . $sub_invoice_id; ?></div>
                <div><strong><?php echo $this->lang->line('collected_by'); ?>: <?php
                                            if (isJSON($feeList->amount_detail)) {
                                                $fee    = json_decode($feeList->amount_detail);
                                                $record = $fee->{$sub_invoice_id};
                                                if (!empty($record->received_by)) {
                                                    echo $record->collected_by;
                                                }
                                            }
?></strong></div>
                                </div>
            
            <div class="thermal-table-container">
                                <?php
                                if (!empty($feeList)) {
                                    ?>
                <table class="thermal-table">
                                        <thead>
                                        <tr>
                                            <th><?php echo $this->lang->line('date'); ?></th>
                                            <th><?php echo $this->lang->line('fees_code'); ?></th>
                            <th class="text-right"><?php echo $this->lang->line('amount'); ?></th>
                            <th class="text-right"><?php echo $this->lang->line('discount'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        if (empty($feeList)) {
                                            ?>
                                            <tr>
                                <td colspan="4" class="text-danger text-center">
                                                    <?php echo $this->lang->line('no_transaction_found'); ?>
                                                </td>
                                            </tr>
                                            <?php
                                        } else {
                                            ?>
                                            <tr>
                                <td><?php echo date("m-Y", strtotime($record->date)); ?></td>
                                <td><?php echo $feeList->code; ?></td>
                                <td class="text-right">
                                                    <?php
                                                    $amount = number_format($record->amount, 2, '.', '');
                                                    echo $currency_symbol . $amount;
                                                    ?>
                                                </td>
                                <td class="text-right">
                                                    <?php
                                                    $amount_discount = number_format($record->amount_discount, 2, '.', '');
                                                    echo $currency_symbol . $amount_discount;
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                    <?php
                                }
                                ?>
                        </div>

            <div class="thermal-footer">
                <div class="thermal-copy-label">Teacher Copy</div>
                <div><?php echo date('d-m-Y H:i:s'); ?></div>
        </div>
        </div>
    </div>
        <div class="clearfix"></div>
        <footer>
        </footer>
        
        <script>
        // Thermal printer detection and setup
        function detectThermalPrinter() {
            // Check if it's a thermal printer by user agent or other indicators
            const userAgent = navigator.userAgent.toLowerCase();
            const isThermalPrinter = userAgent.includes('thermal') || 
                                    userAgent.includes('pos') || 
                                    userAgent.includes('receipt') ||
                                    window.location.search.includes('thermal=1');
            
            if (isThermalPrinter) {
                // Apply thermal printer styles
                document.body.classList.add('thermal-printer');
                document.querySelector('.thermal-container').classList.add('thermal-active');
                
                // Set page size for thermal printer
                const style = document.createElement('style');
                style.textContent = `
                    @media print {
                        @page {
                            size: 80mm auto !important;
                            margin: 0 !important;
                        }
                        body {
                            width: 75mm !important;
                            max-width: 75mm !important;
                            margin: 0 !important;
                            padding: 1mm !important;
                        }
                        .thermal-container {
                            width: 75mm !important;
                            max-width: 75mm !important;
                        }
                    }
                `;
                document.head.appendChild(style);
            }
        }
        
        function printReceipt() {
            // Detect thermal printer before printing
            detectThermalPrinter();
            
            // Small delay to ensure styles are applied
            setTimeout(() => {
                window.print();
            }, 100);
        }
        
        // Auto-detect on page load
        document.addEventListener('DOMContentLoaded', function() {
            detectThermalPrinter();
        });
        
        // Also detect on window load
        window.addEventListener('load', function() {
            detectThermalPrinter();
        });
        
        // Toggle thermal printer mode
        function toggleThermalMode() {
            const currentUrl = new URL(window.location);
            if (currentUrl.searchParams.has('thermal')) {
                currentUrl.searchParams.delete('thermal');
            } else {
                currentUrl.searchParams.set('thermal', '1');
            }
            window.location.href = currentUrl.toString();
        }
        
        // Override the print function
        function myFunction() {
            printReceipt();
        }
        </script>
    </body>
</html>