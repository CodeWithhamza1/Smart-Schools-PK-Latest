<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Challan Form</title>
    <style>
        .table {
            width: 100%;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        h1,
        h2,
        h3,
        h4,
        h5 {
            font-family: Arial, Helvetica, sans-serif !important;
        }

        @font-face {
            font-family: "Report Font";
            src: url("/backend/Fonts/code128.ttf");
        }

        .type {
            font-family: "Report Font", Arial, BarcodeFont, sans-serif !important;
            font-size: 90px;
            text-align: center;
            /* width: 247px; */
            margin-top: 12px;
            margin-bottom: 6px;
        }

        #tables {
            border: 2px solid rgba(0, 0, 0, 0.81);
            border-radius: 2px;
        }

        .datatable {
            padding-left: 40px;
            width: 100%;
        }

        .table td {
            vertical-align: top;
            padding: 1px;
        }

        .branchname {
            text-align: center !important;
            border-bottom: 1pt solid black;
            border-top: 1pt solid black;
        }

        tr.LOGO td {
            border-bottom: 1pt solid black;
            height: 10px;
        }

        tr.copy td {
            border-bottom: 1pt solid black;
        }

        tr.particulars td {
            border-bottom: 1pt solid black;
            border-top: 1pt solid black;
            font-size: 14px;
        }

        tr.total td {
            border-bottom: 1pt solid black;
            border-top: 1pt solid black;
            font-size: 14px;
        }

        .borderbottom {
            border-bottom: 1pt solid black;
        }

        td.bank {
            margin-bottom: 10px;
        }

        .voucher-header-note {
            clear: both;
            font-size: 9px;
            line-height: 1.2;
            margin: 4px 2px 0;
            overflow-wrap: anywhere;
            text-align: center;
        }

        .voucher-student-row {
            break-inside: avoid;
            margin-bottom: 20px;
            page-break-inside: avoid;
            white-space: nowrap;
        }

        .voucher-student-row > .dataTable_wrapper {
            vertical-align: top;
            white-space: normal;
        }

        .voucher-copy {
            box-sizing: border-box;
            position: relative;
            vertical-align: top;
        }

        .voucher-copy + .voucher-copy {
            margin-left: 28px !important;
        }

        .voucher-copy + .voucher-copy:before {
            border-left: 1px dashed #777;
            color: #444;
            content: "\2702";
            font-size: 15px;
            height: calc(100% - 8px);
            left: -21px;
            line-height: 16px;
            padding-left: 2px;
            position: absolute;
            top: 4px;
        }

        .voucher-page-break {
            break-before: page;
            clear: both;
            height: 0;
            page-break-before: always;
            width: 100%;
        }

        .voucher-student-row.copy-count-2 {
            margin-bottom: 12px;
        }

        .voucher-student-row.copy-count-2.row-position-2 {
            border-top: 1px dashed #777;
            padding-top: 14px;
            position: relative;
        }

        .voucher-student-row.copy-count-2.row-position-2:before {
            background: #fff;
            color: #444;
            content: "\2702";
            font-size: 15px;
            left: 4px;
            line-height: 16px;
            padding-right: 4px;
            position: absolute;
            top: -8px;
        }

        .voucher-layout-2 .voucher-copy {
            font-size: 10px !important;
        }

        .voucher-layout-2 .voucher-school-header {
            height: auto !important;
            margin-bottom: 4px;
            margin-top: 4px;
            min-height: 68px !important;
        }

        .voucher-layout-2 .table td {
            line-height: 1.05;
            padding: 0;
        }

        .voucher-layout-2 .branchname h3 {
            font-size: 14px;
            margin: 4px 0;
        }

        .voucher-layout-2 .voucher-note-title {
            font-size: 13px;
            margin-bottom: 3px;
            margin-top: 4px;
        }

        .voucher-layout-2 .voucher-note-line {
            font-size: 10px;
            line-height: 1.1;
            margin-bottom: 2px;
            margin-top: 2px;
        }

        @media print {
            @page {
                margin: 7mm;
                size: A4 landscape;
            }

            .voucher-copy + .voucher-copy:before,
            .voucher-student-row.copy-count-2.row-position-2,
            .voucher-student-row.copy-count-2.row-position-2:before {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <?php
    $school_name = isset($settinglist[0]['name']) ? trim($settinglist[0]['name']) : '';
    $school_address = isset($settinglist[0]['address']) ? trim($settinglist[0]['address']) : '';
    $school_phone = isset($settinglist[0]['phone']) ? trim($settinglist[0]['phone']) : '';
    $voucher_defaults = array(
        'copy_count' => 1,
        'copy_1_label' => 'Student Copy',
        'copy_2_label' => 'School Copy',
        'copy_3_label' => 'Bank Copy',
        'header_note' => '',
        'footer_notes' => "1. Challan form is submitted only in this campus\n2. Due's must be submitted before validity date\n3. After expiry of validity date fine will charged",
    );
    $voucher_settings = array_merge($voucher_defaults, isset($voucher_settings) && is_array($voucher_settings) ? $voucher_settings : array());
    $voucher_copy_count = max(1, min(3, (int) $voucher_settings['copy_count']));
    $voucher_header_note = trim((string) $voucher_settings['header_note']);
    $voucher_footer_notes = trim((string) $voucher_settings['footer_notes']);
    $voucher_footer_lines = ($voucher_footer_notes === '') ? array() : preg_split('/\r\n|\r|\n/', $voucher_footer_notes);
    ?>
    <div class="datatable voucher-layout-<?php echo $voucher_copy_count; ?>">
        <div style="margin-top: 10px;"></div>
        <?php
        $flag = true;
        $voucher_students_per_page = ($voucher_copy_count === 1) ? 3 : (($voucher_copy_count === 2) ? 2 : 1);
        $voucher_rendered_students = 0;
        ?>
        <?php foreach ($masterData as $value2) { ?>
            <?php
            $crnt_fee = 0;
            foreach ($value2['student_due_fee'] as $key => $value) {
                $deposit = 0;
                $discount = 0;
                for ($i = 0; $i < count($value->fees); $i++) {
                    if ($value->name != 'Balance Master') {
                        $amount_detail = json_decode($value->fees[$i]->amount_detail);
                        if (is_array($amount_detail) || is_object($amount_detail)) {
                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                $deposit += $amount_detail_value->amount;
                                $discount = $discount + $amount_detail_value->amount_discount;
                            }
                        }
                        $crnt_fee += $value->fees[$i]->amount - ($discount + $deposit);
                    }
                }
            }

            //        print_r($value2['arrear_deposit']);
        
            $student_pending_fee = $value2['student_pending_fee'];
            //        echo $crnt_fee;
            // Generate challan if student has any fees due or pending fees
            $hasFeesDue = false;
            foreach ($value2['student_due_fee'] as $key => $value) {
                if ($value->name != 'Balance Master' && count($value->fees) > 0) {
                    $hasFeesDue = true;
                    break;
                }
            }
            
            if ($hasFeesDue || $student_pending_fee > 0) {
                if ($voucher_rendered_students > 0 && ($voucher_rendered_students % $voucher_students_per_page) === 0) {
                    echo '<div class="voucher-page-break"></div>';
                }
                $voucher_row_position = ($voucher_rendered_students % $voucher_students_per_page) + 1;
                $voucher_rendered_students++;
                ?>
                <?php if ($voucher_copy_count > 1) { ?><div class="voucher-student-row copy-count-<?php echo $voucher_copy_count; ?> row-position-<?php echo $voucher_row_position; ?>"><?php } ?>
                <div class="dataTable_wrapper voucher-copy" id="tables"
                    style="display:inline-block; width:300px;font-size:12px;margin-left: 1px;">
                    <h4 class="voucher-school-header" style="width: 290px; margin-left: 2px; min-height: 90px; height: auto; overflow: hidden;">
                        <img style="float: right; margin-top: -9px;"
                            src="<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>"
                            height="70" width="70" alt="">
                        <div style="text-align: center; line-height: 1.1;">
                            <div style="font-size: 18px; font-weight: bold; margin-bottom: 3px;"><?php echo $school_name; ?></div>
                            <div style="font-size: 10px; margin-bottom: 2px;"><?php echo $school_address; ?></div>
                            <div style="font-size: 10px; text-align: center; clear: both; width: 100%; margin-left:-20px!important;"><?php echo $school_phone; ?></div>
                            <?php if ($voucher_header_note !== '') { ?><div class="voucher-header-note"><?php echo nl2br(html_escape($voucher_header_note)); ?></div><?php } ?>
                        </div>


                    </h4>
                    <div class="branchname">
                        <h3><?php echo html_escape($voucher_settings['copy_1_label']); ?></h3>
                    </div>
                    <table class="table">
                        <tbody>
                            <!--<tr>
                    <td><b>Due Date:</b></td>
                    <?php /*foreach ($value2['student_due_fee'] as $key => $value) {
                      */ ?>
                        <td style="text-align: center"
                            class="borderbottom"><?php /*echo date('d-F-Y', strtotime($value->fees[0]->due_date)) */ ?></td>
                        <?php /*break;
} */ ?>
                </tr>-->
                            <tr>
                                <td><b>Validity Date:</b></td>
                                <?php

                                foreach ($value2['student_due_fee'] as $key => $value) {
                                    if ((isset($value->fees[0]) && !empty($value->fees[0]->due_date)) > 0) {
                                        ?>
                                        <td style="text-align: center" class="borderbottom">
                                            <?php echo date('d-F-Y', strtotime($value->fees[0]->due_date)) ?>
                                        </td>
                                        <?php break;
                                    }
                                } ?>
                            </tr>
                            <tr>
                                <td><b>Student ID:</b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <?php echo $value2['student']["admission_no"]; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Name:</b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php echo ($value2['student']['firstname'] . ' ' . $value2['student']['lastname']) ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Father:</b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php echo ($value2['student']['father_name']) ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Class:</b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php echo ($value2['student']['class'] . '(' . $value2['student']['section'] . ')') ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Fee for the month of:</b></td>
                                <?php foreach ($value2['student_due_fee'] as $key => $value) {
                                    if ((isset($value->fees[0]) && !empty($value->fees[0]->due_date)) > 0) {
                                        ?>
                                        <td style="text-align: center"><?php echo date('F', strtotime($value->fees[0]->due_date)) ?>
                                        </td>
                                        <?php break;
                                    }
                                } ?>
                            </tr>
                            <tr class="particulars">
                                <td><b>Particulars</b></td>
                                <td style="text-align: center" class="borderbottom"><b>Amount(Rs.)</b></td>
                            </tr>
                            <?php $totalFees = 0;
                            $pendingFee = 0;
                            $arears = 0;
                            /*foreach ($value2['student_pending_fee'] as $key => $value1) {
                             */ ?><!--
                    --><?php /*for ($i = 0; $i < count($value1->fees); $i++) {
$pendingFee+=$value1->fees[$i]->amount;
}
}*/
                    ?>
                            <?php
                            $dicsountPendingFee = 0;
                            foreach ($value2['student_due_fee'] as $key => $value) {
                                ?>
                                <?php for ($i = 0; $i < count($value->fees); $i++) {
                                    $fine = 0;
                                    $deposit = 0;
                                    $discount = 0;
                                    ?>
                                    <?php if ($value->name == 'Balance Master') {
                                        $arears += $value->fees[$i]->amount;
                                    }

                                    $amount_detail = json_decode($value->fees[$i]->amount_detail);
                                    //                     print_r($amount_detail);
                    
                                    if (is_array($amount_detail) || is_object($amount_detail)) {
                                        foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {

                                            //
                                            $deposit += $amount_detail_value->amount;
                                        $fine = $fine + $amount_detail_value->amount_fine;
                                        $discount = $discount + $amount_detail_value->amount_discount;
                                        //                    }
                                        }
                                    }
                                    if ($value->name != 'Balance Master') { ?>
                                        <tr>
                                            <td><b>
                                                    <?php echo ($value->fees[$i]->type);
                                                    if ($value->fees[$i]->amount_detail != 0) {

                                                        echo "(PAID)";
                                                    } ?>
                                                </b>
                                            </td>
                                            <td style="text-align: center" class="borderbottom">
                                                <b><?php 
                                                // Check if fee is already paid (deposit equals or exceeds the fee amount)
                                                $isPaid = ($deposit >= $value->fees[$i]->amount);
                                                
                                                if ($isPaid) {
                                                    // For paid fees, don't apply discount to total
                                                    $totalFees += ($value->fees[$i]->amount + $fine - $deposit);
                                                    echo ($value->fees[$i]->amount + $fine - $deposit);
                                                } else {
                                                    // For unpaid fees, apply discount
                                                    $totalFees += ($value->fees[$i]->amount + $fine - $deposit);
                                                    $dicsountPendingFee += $discount;
                                                    echo ($value->fees[$i]->amount + $fine - $deposit - $discount);
                                                }
                                                ?></b>
                                            </td>

                                            <td style="text-align: center" class="borderbottom"><b><?php /* $totalFees +=($value->fees[$i]->amount+$fine-($discount+$deposit));
//                                           echo ($value->fees[$i]->amount+$fine-($discount+$deposit)) */ ?></b></td>
                                        </tr>
                                    <?php }
                                }
                            }
                            ?>
                            <tr>
                                <td><b> <?php echo 'Pending Fee' ?></b></td>
                                <!--<td><b>  --><?php //echo 'Previous Balance' ?><!--</b></td>-->
                                <td style="text-align: center" class="borderbottom"><b><?php if ($value2['student_pending_fee'] > 0) {
                                    echo $value2['student_pending_fee'];
                                } else {
                                    echo "0";
                                } ?></b>
                                    <!--<td style="text-align: center" class="borderbottom"><b>--><?php // echo  $arears+$pendingFee ?><!--</b>-->
                                </td>
                            </tr>
                            <tr>
                                <td><b>Previous Balance</b></td>
                                <td style="text-align: center" class="borderbottom"><b><?php $previousBalance = ($arears - $value2['arrear_deposit']);
                                echo $previousBalance; ?></b>
                            </tr>
                            <?php $totalDiscount = 0; ?>
                            <?php /*foreach ($value2['student_discount_fee'] as $key => $value) {
                                ?>
                                <tr>
                                    <td><b><?php echo $value['name'] ?></b></td>
                                    <td style="text-align: center" class="borderbottom"><b><?php $totalDiscount += $value['amount'];
                                    echo $value['amount'] ?></b>
                                    </td>
                                </tr>
                                <?php
                            } */ ?>
                            <tr class="total">
                                <td><b>Total Payable</b></td>
                                <td style="text-align: center">
                                    <b><?php if ($value2['student_pending_fee'] < 0) {
                                        $value2['student_pending_fee'] = 0;
                                    }
                                    echo ($totalFees + $value2['student_pending_fee'] + $previousBalance - $dicsountPendingFee); ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td><b>After Due Date</b></td>
                                <td style="text-align: center; padding-bottom: 15px"><b>-</b></td>
                            </tr>
                            <tr>
                                <td><b>Issue Date:</b><?php echo date('d-m-Y') ?></td>
                                <td style="text-align: center; border-top: 1px solid black;">Accountant(Signature)</td>
                            </tr>
                        </tbody>
                    </table>
                    <?php if (!empty($voucher_footer_lines)) { ?>
                        <h3 style="margin-left: 2px" class="borderbottom voucher-note-title">Note:</h3>
                        <?php foreach ($voucher_footer_lines as $voucher_footer_line) { ?><h4 style="margin-left: 2px" class="month voucher-note-line"><?php echo html_escape($voucher_footer_line); ?></h4><?php } ?>
                    <?php } ?>
                    <!--            <center><img alt="testing" style=" height: 50px !important;
                 width: 150px !important;" src="<?php /*echo base_url()*/ ?>barcode.php?text=<?php /* echo $value2['student']["admission_no"]*/ ?>&print=false"/></center>
-->
                </div>

                <?php if ($voucher_copy_count >= 2): ?>
                <div class="dataTable_wrapper voucher-copy" id="tables"
                    style="display:inline-block; width:300px;font-size:12px;margin-left:16px;">
                    <h4 class="voucher-school-header" style="width: 290px; margin-left: 2px; min-height: 90px; height: auto; overflow: hidden;">
                        <img style="float: right; margin-top: -9px;"
                            src="<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>"
                            height="70" width="70" alt="">
                        <div style="text-align: center; line-height: 1.1;">
                            <div style="font-size: 18px; font-weight: bold; margin-bottom: 3px;"><?php echo $school_name; ?></div>
                            <div style="font-size: 10px; margin-bottom: 2px;"><?php echo $school_address; ?></div>
                            <div style="font-size: 10px; text-align: center; clear: both; width: 100%; margin-left:-40px!important;"><?php echo $school_phone; ?></div>
                            <?php if ($voucher_header_note !== '') { ?><div class="voucher-header-note"><?php echo nl2br(html_escape($voucher_header_note)); ?></div><?php } ?>
                        </div>

                    </h4>
                    <div class="branchname">
                        <h3><?php echo html_escape($voucher_settings['copy_2_label']); ?></h3>
                    </div>
                    <table class="table">
                        <tbody>
                            <!--<tr>
                    <td><b>Due Date:</b></td>
                    <?php /*foreach ($value2['student_due_fee'] as $key => $value) {
                      */ ?>
                        <td style="text-align: center"
                            class="borderbottom"><?php /*echo date('d-F-Y', strtotime($value->fees[0]->due_date)) */ ?></td>
                        <?php /*break;
} */ ?>
                </tr>-->
                            <tr>
                                <td><b>Validity Date:</b></td>
                                <?php foreach ($value2['student_due_fee'] as $key => $value) {
                                    if ((isset($value->fees[0]) && !empty($value->fees[0]->due_date)) > 0) {
                                        ?>
                                        <td style="text-align: center" class="borderbottom">
                                            <?php echo date('d-F-Y', strtotime($value->fees[0]->due_date)) ?>
                                        </td>
                                        <?php break;
                                    }
                                } ?>
                            </tr>
                            <tr>
                                <td><b>Student ID:</b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <?php echo $value2['student']["admission_no"]; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Name:</b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php echo ($value2['student']['firstname'] . ' ' . $value2['student']['lastname']) ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Father:</b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php echo ($value2['student']['father_name']) ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Class:</b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php echo ($value2['student']['class'] . '(' . $value2['student']['section'] . ')') ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Fee for the month of:</b></td>
                                <?php

                                foreach ($value2['student_due_fee'] as $key => $value) {
                                    if ((isset($value->fees[0]) && !empty($value->fees[0]->due_date)) > 0) {
                                        ?>
                                        <td style="text-align: center"><?php echo date('F', strtotime($value->fees[0]->due_date)) ?>
                                        </td>
                                        <?php break;
                                    }
                                } ?>
                            </tr>
                            <tr class="particulars">
                                <td><b>Particulars</b></td>
                                <td style="text-align: center" class="borderbottom"><b>Amount(Rs.)</b></td>
                            </tr>
                            <?php $totalFees = 0;
                            $pendingFee = 0;
                            $arears = 0;
                            /*foreach ($value2['student_pending_fee'] as $key => $value1) {
                             */ ?><!--
                    --><?php /*for ($i = 0; $i < count($value1->fees); $i++) {
$pendingFee+=$value1->fees[$i]->amount;
}
}*/
                    ?>
                            <?php
                            $dicsountPendingFee = 0;
                            foreach ($value2['student_due_fee'] as $key => $value) {
                                ?>
                                <?php for ($i = 0; $i < count($value->fees); $i++) {
                                    $fine = 0;
                                    $deposit = 0;
                                    $discount = 0;
                                    ?>
                                    <?php if ($value->name == 'Balance Master') {
                                        $arears += $value->fees[$i]->amount;
                                    }

                                    $amount_detail = json_decode($value->fees[$i]->amount_detail);
                                    //                     print_r($amount_detail);
                    
                                    if (is_array($amount_detail) || is_object($amount_detail)) {
                                        foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                            //                        print_r($amount_detail_value->amount_discount);
//
                                            $deposit += $amount_detail_value->amount;
                                        $fine = $fine + $amount_detail_value->amount_fine;
                                        $discount = $discount + $amount_detail_value->amount_discount;
                                        //                    }
                                        }
                                    }

                                    if ($value->name != 'Balance Master') { ?>
                                        <tr>
                                            <td><b>
                                                    <?php echo ($value->fees[$i]->type);
                                                    if ($value->fees[$i]->amount_detail != 0) {
                                                        echo "(PAID)";
                                                    } ?>
                                                </b>
                                            </td>
                                            <td style="text-align: center" class="borderbottom">
                                                <b><?php 
                                                // Check if fee is already paid (deposit equals or exceeds the fee amount)
                                                $isPaid = ($deposit >= $value->fees[$i]->amount);
                                                
                                                if ($isPaid) {
                                                    // For paid fees, don't apply discount to total
                                                    $totalFees += ($value->fees[$i]->amount + $fine - $deposit);
                                                    echo ($value->fees[$i]->amount + $fine - $deposit);
                                                } else {
                                                    // For unpaid fees, apply discount
                                                    $totalFees += ($value->fees[$i]->amount + $fine - $deposit);
                                                    $dicsountPendingFee += $discount;
                                                    echo ($value->fees[$i]->amount + $fine - $deposit - $discount);
                                                }
                                                ?></b>
                                            </td>
                                        </tr>
                                    <?php }
                                }
                            }
                            ?>
                            <tr>
                                <!--<td><b>  --><?php //echo 'Arrears' ?><!--</b></td>-->
                                <td><b> <?php echo 'Pending Fee' ?></b></td>
                                <td style="text-align: center" class="borderbottom"><b><?php if ($value2['student_pending_fee'] > 0) {
                                    echo $value2['student_pending_fee'];
                                } else {
                                    echo "0";
                                } ?></b>
                                    <!--<td style="text-align: center" class="borderbottom"><b>--><?php // echo  $arears+$pendingFee ?><!--</b>-->
                                </td>
                            </tr>
                            <tr>
                                <td><b>Previous Balance</b></td>
                                <td style="text-align: center" class="borderbottom"><b><?php $previousBalance = ($arears - $value2['arrear_deposit']);
                                echo $previousBalance; ?></b>
                            </tr>
                            <?php $totalDiscount = 0; ?>
                            <?php /*foreach ($value2['student_discount_fee'] as $key => $value) {
                                ?>
                                <tr>
                                    <td><b><?php echo $value['name'] ?></b></td>
                                    <td style="text-align: center" class="borderbottom"><b><?php $totalDiscount += $value['amount'];
                                    echo $value['amount'] ?></b>
                                    </td>
                                </tr>
                                <?php
                            } */ ?>
                            <tr class="total">
                                <td><b>Total Payable</b></td>
                                <td style="text-align: center">
                                    <b><?php echo ($totalFees + $value2['student_pending_fee'] + $previousBalance); ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td><b>After Due Date</b></td>
                                <td style="text-align: center; padding-bottom: 15px"><b>-</b></td>
                            </tr>
                            <tr>
                                <td><b>Issue Date:</b><?php echo date('d-m-Y') ?></td>
                                <td style="text-align: center; border-top: 1px solid black;">Accountant(Signature)</td>
                            </tr>
                        </tbody>
                    </table>
                    <?php if (!empty($voucher_footer_lines)) { ?>
                        <h3 style="margin-left: 2px" class="borderbottom voucher-note-title">Note:</h3>
                        <?php foreach ($voucher_footer_lines as $voucher_footer_line) { ?><h4 style="margin-left: 2px" class="month voucher-note-line"><?php echo html_escape($voucher_footer_line); ?></h4><?php } ?>
                    <?php } ?>
                    <!--            <center><img alt="testing" style=" height: 50px !important;
                 width: 150px !important;" src="<?php /*echo base_url()*/ ?>barcode.php?text=<?php /* echo $value2['student']["admission_no"]*/ ?>&print=false"/></center>
-->
                </div>
                <?php endif; ?>

                <?php if ($voucher_copy_count >= 3): ?>
                <div class="dataTable_wrapper voucher-copy" id="tables"
                    style="display:inline-block; width:300px;font-size:12px;margin-left:16px;">
                    <h4 class="voucher-school-header" style="width: 290px; margin-left: 2px; min-height: 90px; height: auto; overflow: hidden;">
                        <img style="float: right; margin-top: -9px;"
                            src="<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>"
                            height="70" width="70" alt="">
                        <div style="text-align: center; line-height: 1.1;">
                            <div style="font-size: 18px; font-weight: bold; margin-bottom: 3px;"><?php echo $school_name; ?></div>
                            <div style="font-size: 10px; margin-bottom: 2px;"><?php echo $school_address; ?></div>
                            <div style="font-size: 10px; text-align: center; clear: both; width: 100%; margin-left:-40px!important;"><?php echo $school_phone; ?></div>
                            <?php if ($voucher_header_note !== '') { ?><div class="voucher-header-note"><?php echo nl2br(html_escape($voucher_header_note)); ?></div><?php } ?>
                        </div>

                    </h4>
                    <div class="branchname">
                        <h3><?php echo html_escape($voucher_settings['copy_3_label']); ?></h3>
                    </div>
                    <table class="table">
                        <tbody>
                            <!--<tr>
                    <td><b>Due Date:</b></td>
                    <?php /*foreach ($value2['student_due_fee'] as $key => $value) {
                      */ ?>
                        <td style="text-align: center"
                            class="borderbottom"><?php /*echo date('d-F-Y', strtotime($value->fees[0]->due_date)) */ ?></td>
                        <?php /*break;
} */ ?>
                </tr>-->
                            <tr>
                                <td><b>Validity Date:</b></td>
                                <?php foreach ($value2['student_due_fee'] as $key => $value) {
                                    if ((isset($value->fees[0]) && !empty($value->fees[0]->due_date)) > 0) {
                                        ?>
                                        <td style="text-align: center" class="borderbottom">
                                            <?php echo date('d-F-Y', strtotime($value->fees[0]->due_date)) ?>
                                        </td>
                                        <?php break;
                                    }
                                } ?>
                            </tr>
                            <tr>
                                <td><b>Student ID:</b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <?php echo $value2['student']["admission_no"]; ?>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Name:</b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php echo ($value2['student']['firstname'] . ' ' . $value2['student']['lastname']) ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Father:</b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php echo ($value2['student']['father_name']) ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Class:</b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php echo ($value2['student']['class'] . '(' . $value2['student']['section'] . ')') ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td><b>Fee for the month of:</b></td>
                                <?php

                                foreach ($value2['student_due_fee'] as $key => $value) {
                                    if ((isset($value->fees[0]) && !empty($value->fees[0]->due_date)) > 0) {
                                        ?>
                                        <td style="text-align: center"><?php echo date('F', strtotime($value->fees[0]->due_date)) ?>
                                        </td>
                                        <?php break;
                                    }
                                } ?>
                            </tr>
                            <tr class="particulars">
                                <td><b>Particulars</b></td>
                                <td style="text-align: center" class="borderbottom"><b>Amount(Rs.)</b></td>
                            </tr>
                            <?php $totalFees = 0;
                            $pendingFee = 0;
                            $arears = 0;
                            /*foreach ($value2['student_pending_fee'] as $key => $value1) {
                             */ ?><!--
                    --><?php /*for ($i = 0; $i < count($value1->fees); $i++) {
$pendingFee+=$value1->fees[$i]->amount;
}
}*/
                    ?>
                            <?php
                            $dicsountPendingFee = 0;
                            foreach ($value2['student_due_fee'] as $key => $value) {
                                ?>
                                <?php for ($i = 0; $i < count($value->fees); $i++) {
                                    $fine = 0;
                                    $deposit = 0;
                                    $discount = 0;
                                    ?>
                                    <?php if ($value->name == 'Balance Master') {
                                        $arears += $value->fees[$i]->amount;
                                    }

                                    $amount_detail = json_decode($value->fees[$i]->amount_detail);
                                    //                          print_r($amount_detail);
                    
                                    if (is_array($amount_detail) || is_object($amount_detail)) {
                                        foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                            //                          print_r($amount_detail_value->amount_discount);
//
                                        $deposit += $amount_detail_value->amount;
                                        $fine = $fine + $amount_detail_value->amount_fine;
                                        $discount = $discount + $amount_detail_value->amount_discount;
                                        //                    }
                                        }
                                    }

                                    if ($value->name != 'Balance Master') { ?>
                                        <tr>
                                            <td><b>
                                                    <?php echo ($value->fees[$i]->type);
                                                    if ($value->fees[$i]->amount_detail != 0) {
                                                        echo "(PAID)";
                                                    } ?>
                                                </b>
                                            </td>
                                            <td style="text-align: center" class="borderbottom">
                                                <b><?php 
                                                // Check if fee is already paid (deposit equals or exceeds the fee amount)
                                                $isPaid = ($deposit >= $value->fees[$i]->amount);
                                                
                                                if ($isPaid) {
                                                    // For paid fees, don't apply discount to total
                                                    $totalFees += ($value->fees[$i]->amount + $fine - $deposit);
                                                    echo ($value->fees[$i]->amount + $fine - $deposit);
                                                } else {
                                                    // For unpaid fees, apply discount
                                                    $totalFees += ($value->fees[$i]->amount + $fine - $deposit);
                                                    $dicsountPendingFee += $discount;
                                                    echo ($value->fees[$i]->amount + $fine - $deposit - $discount);
                                                }
                                                ?></b>
                                            </td>
                                        </tr>
                                    <?php }
                                }
                            }
                            ?>
                            <tr>
                                <!--<td><b>  --><?php //echo 'Arrears' ?><!--</b></td>-->
                                <td><b> <?php echo 'Pending Fee' ?></b></td>
                                <td style="text-align: center" class="borderbottom"><b><?php if ($value2['student_pending_fee'] > 0) {
                                    echo $value2['student_pending_fee'];
                                } else {
                                    echo "0";
                                } ?></b>
                                    <!--<td style="text-align: center" class="borderbottom"><b>--><?php // echo  $arears+$pendingFee ?><!--</b>-->
                                </td>
                            </tr>
                            <tr>
                                <td><b>Previous Balance</b></td>
                                <td style="text-align: center" class="borderbottom"><b><?php $previousBalance = ($arears - $value2['arrear_deposit']);
                                echo $previousBalance; ?></b>
                            </tr>
                            <?php $totalDiscount = 0; ?>
                            <?php /*foreach ($value2['student_discount_fee'] as $key => $value) {
                                ?>
                                <tr>
                                    <td><b><?php echo $value['name'] ?></b></td>
                                    <td style="text-align: center" class="borderbottom"><b><?php $totalDiscount += $value['amount'];
                                    echo $value['amount'] ?></b>
                                    </td>
                                </tr>
                                <?php
                            } */ ?>
                            <tr class="total">
                                <td><b>Total Payable</b></td>
                                <td style="text-align: center">
                                    <b><?php echo ($totalFees + $value2['student_pending_fee'] + $previousBalance); ?></b>
                                </td>
                            </tr>
                            <tr>
                                <td><b>After Due Date</b></td>
                                <td style="text-align: center; padding-bottom: 15px"><b>-</b></td>
                            </tr>
                            <tr>
                                <td><b>Issue Date:</b><?php echo date('d-m-Y') ?></td>
                                <td style="text-align: center; border-top: 1px solid black;">Accountant(Signature)</td>
                            </tr>
                        </tbody>
                    </table>
                    <?php if (!empty($voucher_footer_lines)) { ?>
                        <h3 style="margin-left: 2px" class="borderbottom voucher-note-title">Note:</h3>
                        <?php foreach ($voucher_footer_lines as $voucher_footer_line) { ?><h4 style="margin-left: 2px" class="month voucher-note-line"><?php echo html_escape($voucher_footer_line); ?></h4><?php } ?>
                    <?php } ?>
                    <!--            <center><img alt="testing" style=" height: 50px !important;
                 width: 150px !important;" src="<?php /*echo base_url()*/ ?>barcode.php?text=<?php /* echo $value2['student']["admission_no"]*/ ?>&print=false"/></center>
-->
                </div>
                <?php endif; ?>
                <?php if ($voucher_copy_count > 1) { ?></div><?php } ?>

                <!--        <div class="dataTable_wrapper" id="tables"
             style="display:inline-block; width:300px;font-size:12px;margin-left:16px;">
            <h4 style="width: 290px; margin-left: 2px;height: 50px;">
                <img style="float: right; margin-top: -9px;"
                     src="<?php /*echo base_url() */ ?>uploads/school_content/logo/<?php /*echo $settinglist[0]['image']; */ ?>" height="70" width="70" alt="">
                <div style="text-align: center; line-height: 1.1;">
                    <div style="font-size: 18px; font-weight: bold; margin-bottom: 3px;"><?php /*echo $settinglist[0]['name'];*/ ?></div>
                    <div style="font-size: 10px; margin-bottom: 2px;"><?php /*echo $settinglist[0]['address'];*/ ?></div>
                    <div style="font-size: 10px;"><?php /*echo $settinglist[0]['phone'];*/ ?></div>
                </div>
            </h4>
            <div class="branchname">
                <h3>Bank Copy</h3>
            </div>
            <table class="table">
                <tbody>

                <tr>
                    <td><b>Validity Date:</b></td>
                    <?php
                    /*
                                        foreach ($value2['student_due_fee'] as $key => $value) {
                                            if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                                */ ?>
                            <td style="text-align: center"
                                class="borderbottom"><?php /*echo date('d-F-Y', strtotime($value->fees[0]->due_date)) */ ?></td>
                            <?php /*break;
} }*/ ?>
                </tr>
                <tr>
                    <td><b>Portal ID:</b></td>
                    <td style="text-align: center" class="borderbottom"><?php /*echo $value2['student']["admission_no"]; */ ?></td>
                </tr>
                <tr>
                    <td><b>Roll No:</b></td>
                    <td style="text-align: center" class="borderbottom"><?php /*echo $value2['student']["roll_no"]; */ ?></td>
                </tr>
                <tr>
                    <td><b>Name:</b></td>
                    <td style="text-align: center"
                        class="borderbottom"><?php /*echo($value2['student']['firstname'] . ' ' . $value2['student']['lastname']) */ ?></td>
                </tr>
                <tr>
                    <td><b>Father:</b></td>
                    <td style="text-align: center" class="borderbottom"><?php /*echo($value2['student']['father_name']) */ ?></td>
                </tr>
                <tr>
                    <td><b>Class:</b></td>
                    <td style="text-align: center"
                        class="borderbottom"><?php /*echo($value2['student']['class'] . '(' . $value2['student']['section'] . ')') */ ?></td>
                </tr>
                <tr>
                    <td><b>Fee for the month of:</b></td>
                    <?php /*foreach ($value2['student_due_fee'] as $key => $value) {
if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
*/ ?>
                            <td style="text-align: center"
                            ><?php /*echo date('F', strtotime($value->fees[0]->due_date)) */ ?></td>
                            <?php /*break;
} }*/ ?>
                </tr>
                <tr class="particulars">
                    <td><b>Particulars</b></td>
                    <td style="text-align: center" class="borderbottom"><b>Amount(Rs.)</b></td>
                </tr>
                <?php /*$totalFees = 0;
$pendingFee=0;$arears=0;

*/ ?>
                <?php /*foreach ($value2['student_due_fee'] as $key => $value) {
$fine = 0;$deposit = 0;$discount = 0;
*/ ?>
                    <?php /*for ($i = 0; $i < count($value->fees); $i++) {
                      */ ?>
                        <?php /*if($value->name =='Balance Master'){
$arears += $value->fees[$i]->amount;
}

$amount_detail = json_decode($value->fees[$i]->amount_detail);
//                     print_r($amount_detail);

if (is_array($amount_detail) || is_object($amount_detail)) {
    foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
        //                        print_r($amount_detail_value->amount_discount);
        //
        $deposit += $amount_detail_value->amount;
$fine = $fine + $amount_detail_value->amount_fine;
$discount = $discount+  $amount_detail_value->amount_discount;
//                    }
    }
}

if($value->name !='Balance Master') {*/ ?>
                            <tr>
                                <td><b>
                                        <?php /* echo ($value->fees[$i]->type);
if ($value->fees[$i]->amount_detail != 0) {
    echo "(PAID)";
}*/ ?>
                                    </b>
                                </td>
                                <td style="text-align: center" class="borderbottom"><b><?php /*$totalFees +=($value->fees[$i]->amount+$fine-($discount+$deposit));
echo ($value->fees[$i]->amount+$fine-($discount+$deposit)) */ ?></b></td>
                            </tr>
                        <?php /*}
}
}
*/ ?>
                <tr>
                    <td><b>  <?php /*echo 'Arrears'*/ ?></b></td>
                    <td style="text-align: center" class="borderbottom"><b><?php /* echo  $value2['student_pending_fee'] */ ?></b>
                    </td>
                </tr>
                <?php /*$totalDiscount = 0; */ ?>
                <?php /*foreach ($value2['student_discount_fee'] as $key => $value) {
                  */ ?>
                    <tr>
                        <td><b><?php /*echo $value['name'] */ ?></b></td>
                        <td style="text-align: center" class="borderbottom"><b><?php /*$totalDiscount += $value['amount']; echo $value['amount'] */ ?></b>
                        </td>
                    </tr>
                    <?php
                    /*                } */ ?>
                <tr class="total">
                    <td><b>Total Payable</b></td>
                    <td style="text-align: center"><b><?php /*echo ($totalFees+$value2['student_pending_fee'] - $totalDiscount); */ ?></b></td>
                </tr>
                <tr>
                    <td><b>After Due Date</b></td>
                    <td style="text-align: center; padding-bottom: 15px"><b>-</b></td>
                </tr>
                <tr>
                    <td><b>Issue Date:</b><?php /*echo date('d-m-Y') */ ?></td>
                    <td style="text-align: center; border-top: 1px solid black;">Accountant(Signature)</td>
                </tr>
                </tbody>
            </table>
            <h3 style="margin-left: 2px" class="borderbottom">Note:</h3>
            <h4 style="margin-left: 2px" class="month">1. Challan form is submitted only in this campus</h4>
            <h4 style="margin-left: 2px" class="month">2. Due's must be submitted before validity date</h4>
            <h4 style="margin-left: 2px" class="month">3. After expiry of validity date fine will charged</h4>
            <center><img alt="testing" style=" height: 50px !important;width: 150px !important;" src="<?php /*echo base_url()*/ ?>barcode.php?text=<?php /* echo $value2['student']["admission_no"]*/ ?>&print=false"/></center>
        </div>
-->
                <?php //<br><br><br>
                        ?>
            <?php }
            ?>
        <?php }
        /*if($flag==false){
            echo("Fees not found for the given month!");
        }*/
        ?>
    </div>
</body>

</html>
