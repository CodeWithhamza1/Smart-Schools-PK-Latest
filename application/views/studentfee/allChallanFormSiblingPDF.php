<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Challan Form</title>
    <style>
        .table {
            width: 100%;
            font-family: Arial, Helvetica, sans-serif !important;
        } .table1 {
            width: 100%;
            border-collapse:collapse;
            font-family: Arial, Helvetica, sans-serif !important;
        }
        .table1 td {
            vertical-align: top;
            border: 1px solid black;
            padding: 1px;
        }

        h1, h2, h3, h4, h5 {
            font-family: Arial, Helvetica, sans-serif !important;
        }
        @font-face {
            font-family: "Report Font";
            src: url("/backend/Fonts/code128.ttf");
        }
        .type{
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
        #tables::before {
            content: "";
            position: absolute;
            z-index: -1;
            margin-top: 200px;
            /*margin-left: 50px;*/
            background-image: url(<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>);
            background-repeat: no-repeat;
            background-size: 70%;
            opacity: 0.2;
            filter:alpha(opacity=40);
            height:30%;
            width:30%;
        }
        @media print {
            #tables::before {
                content: "";
                position: absolute;
                z-index: -1;
                margin-top: 200px;
                margin-left: 50px;
                background-image: url(<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>);
                background-repeat: no-repeat;
                background-size: 70%;
                opacity: 0.2;
                filter:alpha(opacity=40);
                height:30%;
                width:30%;
            }
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
    </style>
</head>
<body>
<div class="datatable">
    <div style="margin-top: 10px;"></div>
    <?php $flag= true;?>
    <?php foreach($masterData as $value2) {?>

<!--    --><?php //foreach($value3['sibling'] as $value2) {?>
            <?php
        if($value2['sibling']){
//            foreach($value2['sibling'] as $value2) {
//            print_r($value2['sibling'][0]['school']);
                ?>
            <div class="dataTable_wrapper" id="tables"
                 style="display:inline-block; width:330px;font-size:12px;margin-left: 0px">
                <div style="font-weight:bold;width:100%;margin-top: 5px;padding-left:5px;">
                <span style="width:220px;float: left;text-align: center;border-right: 1px solid black;border-left: 1px solid black;">--STUDENT FEE SLIP--</span>
                <span style="margin-left: 5px;">Office Copy</span>
                </div>
                <br>

                <div style="text-align: center;width: 100%; margin-left: 2px;height: 70px; border-bottom: 1px solid black;">
                    <span><img style="float:left; margin-top: -9px;"
                               src="<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>" height="70" width="70" alt=""></span>
                    <span style=" font-size: 20px;line-height:20px"><b><?php echo $school?> </b></span>
                    <br>
                    <span style="text-align: left; display:none;">Address: <?php echo $settinglist[0]['address'];?></span>
                    <br>
                    <span style="text-align: left; display:none;">Bank: The Bank Of Khyber <!--<br>-->Branch: Ryk Branch</span>
                    <br>
                    <span style="text-align: left; display:none;">Acc#: 00112-003</span>
                </div>
                <!--<h4 style="width: 290px; margin-left: 2px;height: 90px;">
                    <img style="float: right; margin-top: -9px;"
                         src="<?php /*echo base_url() */?>uploads/school_content/logo/<?php /*echo $settinglist[0]['image']; */?>" height="70" width="70" alt="">
                    <span><b><?php /*echo $school*/?> </b></span><br><br>Address: <?php /*echo $settinglist[0]['address'];*/?><br>Contact No: <?php /*echo $settinglist[0]['phone']; */?>

                </h4>-->
                <!--<div class="branchname">
                    <h3>Bank Copy</h3>
                </div>-->
                <table class="table borderbottom"style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="border-right: 1px solid black;"><b>Voucher No:</b></td>
                        <td style="text-align: center;border-right: 1px solid black;" class=""><?php
                                foreach ($value2['sibling'] as $key => $value3) {
                                    echo $value3['student']["parent_id"];
                                    break;
                                }
                            ?></td>
                        <td style="border-right: 1px solid black;"><b>Account Id:</b></td>
                        <td style="text-align: center;border-right: 1px solid black;" class=""><?php
                                foreach ($value2['sibling'] as $key => $value3) {
                                    echo $value3['student']["parent_id"];
                                    break;
                                }
                            ?></td>
                    </tr>
                    </tbody>
                </table>
                <table class="table " style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="text-align: center;border-right: 1px solid black;"
                            class=""><b>Fee Month:</b>
                        <?php
                            foreach ($value2['sibling'] as $key => $value1) {
                                foreach ($value1['student_due_fee'] as $key => $value) {
                                    if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                        ?>
                                        <?php echo date('M-y', strtotime($value->fees[0]->due_date))." to ".date('M-y', strtotime($value->fees[0]->due_date)) ?>
                                        <?php break;
                                    }  }break;}?>
                        </td>
                        <td style="border-right: 1px solid black;">1</td>
                        <td style="border-right: 1px solid black;">Student #:</td>
                        <td style="border-right: 1px solid black;">
                           <?php $no_std=0;

                            foreach ($value2['sibling'] as $key => $value3) {
                                $no_std++;
                            }
                            echo $no_std;
                            ?>
                        </td>
                    </tr>
                    </tbody>
                    <table class="table borderbottom"style="text-align: left">
                        <tbody>

                        <tr>
                            <td style="">
                                <b>Name: </b>
                                <?php


                                    foreach ($value2['sibling'] as $key => $value3) {
                                        echo ($value3['student']['firstname'] . ' ' . $value3['student']['lastname'] ." S/O ".$value3['student']['father_name']) ;
                                        break;}

                                ?>

                            </td>
                        </tr>
                        </tbody>
                    </table>
                </table>
                <table class="table borderbottom"style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="">
                            <?php

                                foreach ($value2['sibling'] as $key => $value3) {
                                    echo($value3['student']['class'] . '(' . $value3['student']['section'] . ')');
                                    break;}
                            ?>
                        </td>
                        <td style="text-align: center;" class="">Due Date:</td>
                        <td style="text-align: center;" class="">
                            <?php
                            foreach ($value2['sibling'] as $key => $value1) {
                            foreach ($value1['student_due_fee'] as $key => $value) {
                            if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                            ?>
                        <td style="text-align: center"
                            class=""><u><?php echo date('d-M-Y', strtotime($value->fees[0]->due_date)) ?><u></td>
                        <?php break;
                        }  }break;}?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                    <table class="table">
                        <tbody>
                   <!-- <tr>
                        <td><b>Validity Date:</b></td>
                        <?php /*  foreach($masterData as $value2){
                            foreach ($value2['sibling'] as $key => $value1) {
                        foreach ($value1['student_due_fee'] as $key => $value) {
                            if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                */?>
                                <td style="text-align: center"
                                    class="borderbottom"><?php /*echo date('d-F-Y', strtotime($value->fees[0]->due_date)) */?></td>
                                <?php /*break;
                            }  }break;}}*/?>
                    </tr>
                    <tr>
                        <td><b>Reg No:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
/*                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                echo  $value2['student']["admission_no"].',';
                            }break;}*/?></td>
                    </tr>
                    <tr>
                        <td><b>Roll No:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
/*
                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                    echo $value2['student']["roll_no"].',';
                                }break;}*/?></td>
                    </tr>
                    <tr>
                        <td><b>Name:</b></td>
                        <td style="text-align: center"
                            class="borderbottom"><?php
/*
                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                    echo ($value2['student']['firstname'] . ' ' . $value2['student']['lastname'].',') ;
                            }break;}
                            */?></td>
                    </tr>
                    <tr>
                        <td><b>Father:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
/*
                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                    echo ($value2['student']['father_name'] ) ;
                                    break;}break;}
                            */?></td>
                    </tr>
                    <tr>
                        <td><b>Class:</b></td>
                        <td style="text-align: center"
                            class="borderbottom"><?php
/*
                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                    echo($value2['student']['class'] . '(' . $value2['student']['section'] . ')'.',');
                                }break;}
                            */?></td>
                    </tr>
                    <tr>
                        <td><b>Fee For:</b></td>
                        <?php
/*                        foreach($masterData as $value2) {*/?>

                        <?php /*foreach ($value2['sibling'] as $key => $value1) {
                            foreach ($value1['student_due_fee'] as $key => $value) {
                                if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                    */?>
                                    <td style="text-align: center"
                                        class="borderbottom"><?php /*echo date('F', strtotime($value->fees[0]->due_date)) */?></td>
                                    <?php /*break;
                                }  }break;}}*/?>
                    </tr>-->
                    <tr class="" style="color: white;background-color: black;">
                        <td style=""><b>Description</b></td>
                        <td style="text-align: center" class=""><b>Amount(Rs.)</b></td>
                    </tr>
                    <?php $totalFees = 0;$pendingFee=0;$arears=0;

                        foreach ($value2['sibling'] as $key => $value3) {
                            $pendingFee +=  $value3['student_pending_fee'];
                            $arears += $value3['amount'];
                        /*foreach ($value3['student_pending_fee'] as $key => $value1) {
                            */?><!--
                            --><?php /*for ($i = 0; $i < count($value1->fees); $i++) {
                                $pendingFee+=$value1->fees[$i]->amount;
                            }
                        }*/
                        }?>
                    <?php $type='';$amount=0;$tution_fee=0;$anual_fee=0;$addmission_fee=0;$book_stationary_fee=0;$other_fee=0;

                     foreach ($value2['sibling'] as $key => $value3) {

                     foreach ($value3['student_due_fee'] as $key => $value) {
                    ?>
                    <?php for ($i = 0; $i < count($value->fees); $i++) {
                     if ($value->name == 'Balance Master') {
//                         $arears += $value->fees[$i]->amount;
                    }
                     if ($value->name != 'Balance Master') {
                        $amount_discount=0; $amount_deposit=0;
                        $amount_detail = json_decode($value->fees[$i]->amount_detail);
                        if (is_object($amount_detail)) {
                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                $amount_deposit += $amount_detail_value->amount;
                                $amount_discount += $amount_detail_value->amount_discount;
                            }
                        }
                        // echo "<pre>"; print_r($amount_deposit . " - " . $amount_discount); 
                    $type =$value->fees[$i]->type;
                    $code =$value->fees[$i]->code;
                    $amount+=$value->fees[$i]->amount;
                    $totalFees += $value->fees[$i]->amount - ($amount_deposit + $amount_discount);
                    if($code=="Tuition"){
                        $tution_fee+=$value->fees[$i]->amount - ($amount_deposit + $amount_discount);
                    }elseif($code=="Annual"){
                        $anual_fee+=$value->fees[$i]->amount;
                    }elseif($code=="Books&Stationery"){
                        $book_stationary_fee+=$value->fees[$i]->amount;
                    }elseif($code=="Admission"){
                        $addmission_fee+=$value->fees[$i]->amount;
                    }else{
                        $other_fee+=$value->fees[$i]->amount;
                    }
                    ?>

                    <?php
                    }

                    }
                    }
                    }
                    /*foreach($masterData as $value2) {
                        foreach ($value2['student_due_fee'] as $key => $value) {
                            */?><!--
                    <?php /*for ($i = 0; $i < count($value->fees); $i++) { */?>
                        <?php /*if ($value->name == 'Balance Master') { */?>
                            <?php /*$arears += $value->fees[$i]->amount; */?>
                        <?php /*} */?>
                        <?php /*if ($value->name != 'Balance Master') {
                            */?>
                            <tr >
                                <td ><b ><?php /*echo $value->fees[$i]->type */?></b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php /*$totalFees += $value->fees[$i]->amount;
                                        echo $value->fees[$i]->amount */?></b>
                                </td>
                            </tr>
                            --><?php
                    /*                        }

                                        }
                                    }
                                }*/
                    ?>

                   <?php $totalDiscount = 0; ?>
                   <?php
                    foreach ($value2['sibling'] as $key => $value3) {
                    foreach ($value3['student_discount_fee'] as $key => $value) {
                         $totalDiscount += $value['amount'];
                    }}?>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Monthly Fee" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $tution_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Annual Fund" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $anual_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Book & Note Book / Stationery" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $book_stationary_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Admission Fee" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $addmission_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Others (-)" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $other_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Discount" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php  echo $totalDiscount; ?></b>
                       </td>
                   </tr>
                    <tr>
                                        <td class="borderbottom"><b>  <?php echo 'Arrears (if any)'?></b></td>
<!--                        <td><b>  --><?php //echo 'Previous Balance'?><!--</b></td>-->
                        <td style="text-align: center" class="borderbottom">
                            <b><?php echo $arears + $pendingFee ?></b>
                            <!--<b><?php
/*            foreach ($value2['sibling'] as $key => $value3) {
                echo $pendingFee = $value3['student_pending_fee'];
            }*/?></b>-->
                        </td>
                    </tr>
                    <!--<tr>
                <td><b>Previous Balance</b></td>
                <td style="text-align: center" class="borderbottom"><b><?php /*echo $pendingFee */?></b>
                </td>
            </tr>-->


                    <tr class="total">
                        <td style="color: white;background-color: black;"><b>Payable Amount (within Date)</b></td>
                        <td style="text-align: center;border-right: 1px solid black;border-left: 1px solid black;"><b><?php echo ($totalFees+$pendingFee+$arears); ?></b></td>
                    </tr>
                   <tr class="">
                        <td style="text-align: right" colspan="2"class="borderbottom">
                            <?php
                            echo convertNumberToWord(($totalFees+$pendingFee+$arears ))." only";
                            ?>
                        </td>
                    </tr>
                   <tr class="total">
                       <td style="color: white;background-color: black;"><b>Payable Amount (after due Date)</b></td>
                       <td style="text-align: center;border-right: 1px solid black;border-left: 1px solid black;"><b><?php echo ($totalFees+$pendingFee+$arears ); ?></b></td>
                   </tr>
                    </tbody>
                </table>
                <h3 style="margin-left: 2px" class="borderbottom">Note:</h3>
                <h4 style="margin-left: 2px" class="month">1. Last date of Fee submission is 10th of every month.</h4>
                <h4 style="margin-left: 2px" class="month">2. Fine will be charged Rs: 200 after due date.</h4>
                <h4 style="margin-left: 2px" class="month">3. Save your paid slip for atleast one academic year.</h4>
                <table class="table1" hidden>
                    <tbody>
                    <tr>
                        <td colspan="8" style="background-color: black;color: white;text-align: center;">Voucher Paid History</td>

                    </tr>
                    <tr>
                        <td>Month</td>
                        <td>Vou#</td>
                        <td>AMT</td>
                        <td>Paid</td>
                        <td>Month</td>
                        <td>Vou#</td>
                        <td>AMT</td>
                        <td>Paid</td>
                    </tr>
                    <tr>
                        <td>Jan-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Jul-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Feb-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Aug-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Mar-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Sep-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Apr-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Oct-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>May-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Nov-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Jun-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Dec-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
                <table class="table">
                    <tbody>
                    <tr>
                        <td>Received Amount by Bank Official</td>
                        <td>___________________</td>
                    </tr>
                    <tr>
                        <td style="font-size: 20px">* <b> <?php
                                foreach ($value2['sibling'] as $key => $value3) {
                                    echo $value3['student']["parent_id"];
                                    break;
                                }
                                ?></b> *</td>
                        <td>Bank Stamp & Signature</td>
                    </tr>
                    </tbody>
                </table>

            </div>
            <div class="dataTable_wrapper" id="tables"
                 style="display:inline-block; width:330px;font-size:12px;margin-left: 0px">
                <div style="font-weight:bold;width:100%;margin-top: 5px;padding-left:5px;">
                <span style="width:220px;float: left;text-align: center;border-right: 1px solid black;border-left: 1px solid black;">--STUDENT FEE SLIP--</span>
                <span style="margin-left: 5px;">Bank Copy</span>
                </div>
                <br>

                <div style="text-align: center;width: 100%; margin-left: 2px;height: 70px; border-bottom: 1px solid black;">
                    <span><img style="float:left; margin-top: -9px;"
                               src="<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>" height="70" width="70" alt=""></span>
                    <span style=" font-size: 20px;line-height:20px"><b><?php echo $school?> </b></span>
                    <br>
                    <span style="text-align: left; display:none;">Address: <?php echo $settinglist[0]['address'];?></span>
                    <br>
                    <span style="text-align: left; display:none;">Bank: The Bank Of Khyber <!--<br>-->Branch: Ryk Branch</span>
                    <br>
                    <span style="text-align: left; display:none;">Acc#: 00112-003</span>
                </div>
                <!--<h4 style="width: 290px; margin-left: 2px;height: 90px;">
                    <img style="float: right; margin-top: -9px;"
                         src="<?php /*echo base_url() */?>uploads/school_content/logo/<?php /*echo $settinglist[0]['image']; */?>" height="70" width="70" alt="">
                    <span><b><?php /*echo $school*/?> </b></span><br><br>Address: <?php /*echo $settinglist[0]['address'];*/?><br>Contact No: <?php /*echo $settinglist[0]['phone']; */?>

                </h4>-->
                <!--<div class="branchname">
                    <h3>Bank Copy</h3>
                </div>-->
                <table class="table borderbottom"style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="border-right: 1px solid black;"><b>Voucher No:</b></td>
                        <td style="text-align: center;border-right: 1px solid black;" class=""><?php
                                foreach ($value2['sibling'] as $key => $value3) {
                                    echo $value3['student']["parent_id"];
                                    break;
                                }
                            ?></td>
                        <td style="border-right: 1px solid black;"><b>Account Id:</b></td>
                        <td style="text-align: center;border-right: 1px solid black;" class=""><?php
                                foreach ($value2['sibling'] as $key => $value3) {
                                    echo $value3['student']["parent_id"];
                                    break;
                                }
                            ?></td>
                    </tr>
                    </tbody>
                </table>
                <table class="table " style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="text-align: center;border-right: 1px solid black;"
                            class=""><b>Fee Month:</b>
                        <?php
                            foreach ($value2['sibling'] as $key => $value1) {
                                foreach ($value1['student_due_fee'] as $key => $value) {
                                    if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                        ?>
                                        <?php echo date('M-y', strtotime($value->fees[0]->due_date))." to ".date('M-y', strtotime($value->fees[0]->due_date)) ?>
                                        <?php break;
                                    }  }break;}?>
                        </td>
                        <td style="border-right: 1px solid black;">1</td>
                        <td style="border-right: 1px solid black;">Student #:</td>
                        <td style="border-right: 1px solid black;">
                           <?php $no_std=0;

                            foreach ($value2['sibling'] as $key => $value3) {
                                $no_std++;
                            }
                            echo $no_std;
                            ?>
                        </td>
                    </tr>
                    </tbody>
                    <table class="table borderbottom"style="text-align: left">
                        <tbody>

                        <tr>
                            <td style="">
                                <b>Name: </b>
                                <?php


                                    foreach ($value2['sibling'] as $key => $value3) {
                                        echo ($value3['student']['firstname'] . ' ' . $value3['student']['lastname'] ." S/O ".$value3['student']['father_name']) ;
                                        break;}

                                ?>

                            </td>
                        </tr>
                        </tbody>
                    </table>
                </table>
                <table class="table borderbottom"style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="">
                            <?php

                                foreach ($value2['sibling'] as $key => $value3) {
                                    echo($value3['student']['class'] . '(' . $value3['student']['section'] . ')');
                                    break;}
                            ?>
                        </td>
                        <td style="text-align: center;" class="">Due Date:</td>
                        <td style="text-align: center;" class="">
                            <?php
                            foreach ($value2['sibling'] as $key => $value1) {
                            foreach ($value1['student_due_fee'] as $key => $value) {
                            if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                            ?>
                        <td style="text-align: center"
                            class=""><u><?php echo date('d-M-Y', strtotime($value->fees[0]->due_date)) ?><u></td>
                        <?php break;
                        }  }break;}?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                    <table class="table">
                        <tbody>
                   <!-- <tr>
                        <td><b>Validity Date:</b></td>
                        <?php /*  foreach($masterData as $value2){
                            foreach ($value2['sibling'] as $key => $value1) {
                        foreach ($value1['student_due_fee'] as $key => $value) {
                            if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                */?>
                                <td style="text-align: center"
                                    class="borderbottom"><?php /*echo date('d-F-Y', strtotime($value->fees[0]->due_date)) */?></td>
                                <?php /*break;
                            }  }break;}}*/?>
                    </tr>
                    <tr>
                        <td><b>Reg No:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
/*                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                echo  $value2['student']["admission_no"].',';
                            }break;}*/?></td>
                    </tr>
                    <tr>
                        <td><b>Roll No:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
/*
                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                    echo $value2['student']["roll_no"].',';
                                }break;}*/?></td>
                    </tr>
                    <tr>
                        <td><b>Name:</b></td>
                        <td style="text-align: center"
                            class="borderbottom"><?php
/*
                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                    echo ($value2['student']['firstname'] . ' ' . $value2['student']['lastname'].',') ;
                            }break;}
                            */?></td>
                    </tr>
                    <tr>
                        <td><b>Father:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
/*
                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                    echo ($value2['student']['father_name'] ) ;
                                    break;}break;}
                            */?></td>
                    </tr>
                    <tr>
                        <td><b>Class:</b></td>
                        <td style="text-align: center"
                            class="borderbottom"><?php
/*
                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                    echo($value2['student']['class'] . '(' . $value2['student']['section'] . ')'.',');
                                }break;}
                            */?></td>
                    </tr>
                    <tr>
                        <td><b>Fee For:</b></td>
                        <?php
/*                        foreach($masterData as $value2) {*/?>

                        <?php /*foreach ($value2['sibling'] as $key => $value1) {
                            foreach ($value1['student_due_fee'] as $key => $value) {
                                if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                    */?>
                                    <td style="text-align: center"
                                        class="borderbottom"><?php /*echo date('F', strtotime($value->fees[0]->due_date)) */?></td>
                                    <?php /*break;
                                }  }break;}}*/?>
                    </tr>-->
                    <tr class="" style="color: white;background-color: black;">
                        <td style=""><b>Description</b></td>
                        <td style="text-align: center" class=""><b>Amount(Rs.)</b></td>
                    </tr>
                    <?php $totalFees = 0;$pendingFee=0;$arears=0;

                        foreach ($value2['sibling'] as $key => $value3) {
                            $pendingFee +=  $value3['student_pending_fee'];
                    $arears += $value3['amount'];
                        /*foreach ($value3['student_pending_fee'] as $key => $value1) {
                            */?><!--
                            --><?php /*for ($i = 0; $i < count($value1->fees); $i++) {
                                $pendingFee+=$value1->fees[$i]->amount;
                            }
                        }*/
                        }?>
                    <?php $type='';$amount=0;$tution_fee=0;$anual_fee=0;$addmission_fee=0;$book_stationary_fee=0;$other_fee=0;

                     foreach ($value2['sibling'] as $key => $value3) {

                     foreach ($value3['student_due_fee'] as $key => $value) {
                    ?>
                    <?php for ($i = 0; $i < count($value->fees); $i++) {
                     if ($value->name == 'Balance Master') {
//                         $arears += $value->fees[$i]->amount;
                    }
                     if ($value->name != 'Balance Master') {
                        $amount_discount=0; $amount_deposit=0;
                        $amount_detail = json_decode($value->fees[$i]->amount_detail);
                        if (is_object($amount_detail)) {
                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                $amount_deposit += $amount_detail_value->amount;
                                $amount_discount += $amount_detail_value->amount_discount;
                            }
                        }
                        // echo "<pre>"; print_r($amount_deposit . " - " . $amount_discount); 
                    $type =$value->fees[$i]->type;
                    $code =$value->fees[$i]->code;
                    $amount+=$value->fees[$i]->amount;
                    $totalFees += $value->fees[$i]->amount - ($amount_deposit + $amount_discount);
                    if($code=="Tuition"){
                        $tution_fee+=$value->fees[$i]->amount - ($amount_deposit + $amount_discount);
                    }elseif($code=="Annual"){
                        $anual_fee+=$value->fees[$i]->amount;
                    }elseif($code=="Books&Stationery"){
                        $book_stationary_fee+=$value->fees[$i]->amount;
                    }elseif($code=="Admission"){
                        $addmission_fee+=$value->fees[$i]->amount;
                    }else{
                        $other_fee+=$value->fees[$i]->amount;
                    }
                    ?>

                    <?php
                    }

                    }
                    }
                    }
                    /*foreach($masterData as $value2) {
                        foreach ($value2['student_due_fee'] as $key => $value) {
                            */?><!--
                    <?php /*for ($i = 0; $i < count($value->fees); $i++) { */?>
                        <?php /*if ($value->name == 'Balance Master') { */?>
                            <?php /*$arears += $value->fees[$i]->amount; */?>
                        <?php /*} */?>
                        <?php /*if ($value->name != 'Balance Master') {
                            */?>
                            <tr >
                                <td ><b ><?php /*echo $value->fees[$i]->type */?></b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php /*$totalFees += $value->fees[$i]->amount;
                                        echo $value->fees[$i]->amount */?></b>
                                </td>
                            </tr>
                            --><?php
                    /*                        }

                                        }
                                    }
                                }*/
                    ?>

                   <?php $totalDiscount = 0; ?>
                   <?php
                    foreach ($value2['sibling'] as $key => $value3) {
                    foreach ($value3['student_discount_fee'] as $key => $value) {
                         $totalDiscount += $value['amount'];
                        $arears += $value3['amount'];
                    }}?>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Monthly Fee" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $tution_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Annual Fund" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $anual_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Book & Note Book / Stationery" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $book_stationary_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Admission Fee" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $addmission_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Others (-)" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $other_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Discount" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php  echo $totalDiscount; ?></b>
                       </td>
                   </tr>
                    <tr>
                                        <td class="borderbottom"><b>  <?php echo 'Arrears (if any)'?></b></td>
<!--                        <td><b>  --><?php //echo 'Previous Balance'?><!--</b></td>-->
                        <td style="text-align: center" class="borderbottom">
                            <b><?php echo $arears + $pendingFee ?></b>
                            <!--<b><?php
/*            foreach ($value2['sibling'] as $key => $value3) {
                echo $pendingFee = $value3['student_pending_fee'];
            }*/?></b>-->
                        </td>
                    </tr>
                    <!--<tr>
                <td><b>Previous Balance</b></td>
                <td style="text-align: center" class="borderbottom"><b><?php /*echo $pendingFee */?></b>
                </td>
            </tr>-->


                    <tr class="total">
                        <td style="color: white;background-color: black;"><b>Payable Amount (within Date)</b></td>
                        <td style="text-align: center;border-right: 1px solid black;border-left: 1px solid black;"><b><?php echo ($totalFees+$pendingFee+$arears ); ?></b></td>
                    </tr>
                   <tr class="">
                        <td style="text-align: right" colspan="2"class="borderbottom">
                            <?php
                            echo convertNumberToWord(($totalFees+$pendingFee+$arears ))." only";
                            ?>
                        </td>
                    </tr>
                   <tr class="total">
                       <td style="color: white;background-color: black;"><b>Payable Amount (after due Date)</b></td>
                       <td style="text-align: center;border-right: 1px solid black;border-left: 1px solid black;"><b><?php echo ($totalFees+$pendingFee+$arears ); ?></b></td>
                   </tr>
                    </tbody>
                </table>
                <h3 style="margin-left: 2px" class="borderbottom">Note:</h3>
                <h4 style="margin-left: 2px" class="month">1. Last date of Fee submission is 10th of every month.</h4>
                <h4 style="margin-left: 2px" class="month">2. Fine will be charged Rs: 200 after due date.</h4>
                <h4 style="margin-left: 2px" class="month">3. Save your paid slip for atleast one academic year.</h4>
                <table class="table1" hidden>
                    <tbody>
                    <tr>
                        <td colspan="8" style="background-color: black;color: white;text-align: center;">Voucher Paid History</td>

                    </tr>
                    <tr>
                        <td>Month</td>
                        <td>Vou#</td>
                        <td>AMT</td>
                        <td>Paid</td>
                        <td>Month</td>
                        <td>Vou#</td>
                        <td>AMT</td>
                        <td>Paid</td>
                    </tr>
                    <tr>
                        <td>Jan-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Jul-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Feb-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Aug-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Mar-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Sep-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Apr-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Oct-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>May-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Nov-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Jun-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Dec-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
                <table class="table">
                    <tbody>
                    <tr>
                        <td>Received Amount by Bank Official</td>
                        <td>___________________</td>
                    </tr>
                    <tr>
                        <td style="font-size: 20px">* <b> <?php
                                foreach ($value2['sibling'] as $key => $value3) {
                                    echo $value3['student']["parent_id"];
                                    break;
                                }
                                ?></b> *</td>
                        <td>Bank Stamp & Signature</td>
                    </tr>
                    </tbody>
                </table>

            </div>
            <div class="dataTable_wrapper" id="tables"
                 style="display:inline-block; width:330px;font-size:12px;margin-left: 0px">
                <div style="font-weight:bold;width:100%;margin-top: 5px;padding-left:5px;">
                <span style="width:220px;float: left;text-align: center;border-right: 1px solid black;border-left: 1px solid black;">--STUDENT FEE SLIP--</span>
                <span style="margin-left: 5px;">Student Copy</span>
                </div>
                <br>

                <div style="text-align: center;width: 100%; margin-left: 2px;height: 70px; border-bottom: 1px solid black;">
                    <span><img style="float:left; margin-top: -9px;"
                               src="<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>" height="70" width="70" alt=""></span>
                    <span style=" font-size: 20px;line-height:20px"><b><?php echo $school?> </b></span>
                    <br>
                    <span style="text-align: left; display:none;">Address: <?php echo $settinglist[0]['address'];?></span>
                    <br>
                    <span style="text-align: left; display:none;">Bank: The Bank Of Khyber <!--<br>-->Branch: Ryk Branch</span>
                    <br>
                    <span style="text-align: left; display:none;">Acc#: 00112-003</span>
                </div>
                <!--<h4 style="width: 290px; margin-left: 2px;height: 90px;">
                    <img style="float: right; margin-top: -9px;"
                         src="<?php /*echo base_url() */?>uploads/school_content/logo/<?php /*echo $settinglist[0]['image']; */?>" height="70" width="70" alt="">
                    <span><b><?php /*echo $school*/?> </b></span><br><br>Address: <?php /*echo $settinglist[0]['address'];*/?><br>Contact No: <?php /*echo $settinglist[0]['phone']; */?>

                </h4>-->
                <!--<div class="branchname">
                    <h3>Bank Copy</h3>
                </div>-->
                <table class="table borderbottom"style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="border-right: 1px solid black;"><b>Voucher No:</b></td>
                        <td style="text-align: center;border-right: 1px solid black;" class=""><?php
                                foreach ($value2['sibling'] as $key => $value3) {
                                    echo $value3['student']["parent_id"];
                                    break;
                                }
                            ?></td>
                        <td style="border-right: 1px solid black;"><b>Account Id:</b></td>
                        <td style="text-align: center;border-right: 1px solid black;" class=""><?php
                                foreach ($value2['sibling'] as $key => $value3) {
                                    echo $value3['student']["parent_id"];
                                    break;
                                }
                            ?></td>
                    </tr>
                    </tbody>
                </table>
                <table class="table " style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="text-align: center;border-right: 1px solid black;"
                            class=""><b>Fee Month:</b>
                        <?php
                            foreach ($value2['sibling'] as $key => $value1) {
                                foreach ($value1['student_due_fee'] as $key => $value) {
                                    if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                        ?>
                                        <?php echo date('M-y', strtotime($value->fees[0]->due_date))." to ".date('M-y', strtotime($value->fees[0]->due_date)) ?>
                                        <?php break;
                                    }  }break;}?>
                        </td>
                        <td style="border-right: 1px solid black;">1</td>
                        <td style="border-right: 1px solid black;">Student #:</td>
                        <td style="border-right: 1px solid black;">
                           <?php $no_std=0;

                            foreach ($value2['sibling'] as $key => $value3) {
                                $no_std++;
                            }
                            echo $no_std;
                            ?>
                        </td>
                    </tr>
                    </tbody>
                    <table class="table borderbottom"style="text-align: left">
                        <tbody>

                        <tr>
                            <td style="">
                                <b>Name: </b>
                                <?php


                                    foreach ($value2['sibling'] as $key => $value3) {
                                        echo ($value3['student']['firstname'] . ' ' . $value3['student']['lastname'] ." S/O ".$value3['student']['father_name']) ;
                                        break;}

                                ?>

                            </td>
                        </tr>
                        </tbody>
                    </table>
                </table>
                <table class="table borderbottom"style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="">
                            <?php

                                foreach ($value2['sibling'] as $key => $value3) {
                                    echo($value3['student']['class'] . '(' . $value3['student']['section'] . ')');
                                    break;}
                            ?>
                        </td>
                        <td style="text-align: center;" class="">Due Date:</td>
                        <td style="text-align: center;" class="">
                            <?php
                            foreach ($value2['sibling'] as $key => $value1) {
                            foreach ($value1['student_due_fee'] as $key => $value) {
                            if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                            ?>
                        <td style="text-align: center"
                            class=""><u><?php echo date('d-M-Y', strtotime($value->fees[0]->due_date)) ?><u></td>
                        <?php break;
                        }  }break;}?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                    <table class="table">
                        <tbody>
                   <!-- <tr>
                        <td><b>Validity Date:</b></td>
                        <?php /*  foreach($masterData as $value2){
                            foreach ($value2['sibling'] as $key => $value1) {
                        foreach ($value1['student_due_fee'] as $key => $value) {
                            if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                */?>
                                <td style="text-align: center"
                                    class="borderbottom"><?php /*echo date('d-F-Y', strtotime($value->fees[0]->due_date)) */?></td>
                                <?php /*break;
                            }  }break;}}*/?>
                    </tr>
                    <tr>
                        <td><b>Reg No:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
/*                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                echo  $value2['student']["admission_no"].',';
                            }break;}*/?></td>
                    </tr>
                    <tr>
                        <td><b>Roll No:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
/*
                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                    echo $value2['student']["roll_no"].',';
                                }break;}*/?></td>
                    </tr>
                    <tr>
                        <td><b>Name:</b></td>
                        <td style="text-align: center"
                            class="borderbottom"><?php
/*
                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                    echo ($value2['student']['firstname'] . ' ' . $value2['student']['lastname'].',') ;
                            }break;}
                            */?></td>
                    </tr>
                    <tr>
                        <td><b>Father:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
/*
                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                    echo ($value2['student']['father_name'] ) ;
                                    break;}break;}
                            */?></td>
                    </tr>
                    <tr>
                        <td><b>Class:</b></td>
                        <td style="text-align: center"
                            class="borderbottom"><?php
/*
                            foreach($masterData as $value1){
                                foreach ($value1['sibling'] as $key => $value2) {
                                    echo($value2['student']['class'] . '(' . $value2['student']['section'] . ')'.',');
                                }break;}
                            */?></td>
                    </tr>
                    <tr>
                        <td><b>Fee For:</b></td>
                        <?php
/*                        foreach($masterData as $value2) {*/?>

                        <?php /*foreach ($value2['sibling'] as $key => $value1) {
                            foreach ($value1['student_due_fee'] as $key => $value) {
                                if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                    */?>
                                    <td style="text-align: center"
                                        class="borderbottom"><?php /*echo date('F', strtotime($value->fees[0]->due_date)) */?></td>
                                    <?php /*break;
                                }  }break;}}*/?>
                    </tr>-->
                    <tr class="" style="color: white;background-color: black;">
                        <td style=""><b>Description</b></td>
                        <td style="text-align: center" class=""><b>Amount(Rs.)</b></td>
                    </tr>
                    <?php $totalFees = 0;$pendingFee=0;$arears=0;

                        foreach ($value2['sibling'] as $key => $value3) {
                            $pendingFee +=  $value3['student_pending_fee'];
                    $arears += $value3['amount'];
                        /*foreach ($value3['student_pending_fee'] as $key => $value1) {
                            */?><!--
                            --><?php /*for ($i = 0; $i < count($value1->fees); $i++) {
                                $pendingFee+=$value1->fees[$i]->amount;
                            }
                        }*/
                        }?>
                    <?php $type='';$amount=0;$tution_fee=0;$anual_fee=0;$addmission_fee=0;$book_stationary_fee=0;$other_fee=0;

                     foreach ($value2['sibling'] as $key => $value3) {

                     foreach ($value3['student_due_fee'] as $key => $value) {
                    ?>
                    <?php for ($i = 0; $i < count($value->fees); $i++) {
                     if ($value->name == 'Balance Master') {
//                         $arears += $value->fees[$i]->amount;
                    }
                     if ($value->name != 'Balance Master') {
                        $amount_discount=0; $amount_deposit=0;
                        $amount_detail = json_decode($value->fees[$i]->amount_detail);
                        if (is_object($amount_detail)) {
                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                $amount_deposit += $amount_detail_value->amount;
                                $amount_discount += $amount_detail_value->amount_discount;
                            }
                        }
                        // echo "<pre>"; print_r($amount_deposit . " - " . $amount_discount); 
                    $type =$value->fees[$i]->type;
                    $code =$value->fees[$i]->code;
                    $amount+=$value->fees[$i]->amount;
                    $totalFees += $value->fees[$i]->amount - ($amount_deposit + $amount_discount);
                    if($code=="Tuition"){
                        $tution_fee+=$value->fees[$i]->amount - ($amount_deposit + $amount_discount);
                    }elseif($code=="Annual"){
                        $anual_fee+=$value->fees[$i]->amount;
                    }elseif($code=="Books&Stationery"){
                        $book_stationary_fee+=$value->fees[$i]->amount;
                    }elseif($code=="Admission"){
                        $addmission_fee+=$value->fees[$i]->amount;
                    }else{
                        $other_fee+=$value->fees[$i]->amount;
                    }
                    ?>

                    <?php
                    }

                    }
                    }
                    }
                    /*foreach($masterData as $value2) {
                        foreach ($value2['student_due_fee'] as $key => $value) {
                            */?><!--
                    <?php /*for ($i = 0; $i < count($value->fees); $i++) { */?>
                        <?php /*if ($value->name == 'Balance Master') { */?>
                            <?php /*$arears += $value->fees[$i]->amount; */?>
                        <?php /*} */?>
                        <?php /*if ($value->name != 'Balance Master') {
                            */?>
                            <tr >
                                <td ><b ><?php /*echo $value->fees[$i]->type */?></b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php /*$totalFees += $value->fees[$i]->amount;
                                        echo $value->fees[$i]->amount */?></b>
                                </td>
                            </tr>
                            --><?php
                    /*                        }

                                        }
                                    }
                                }*/
                    ?>

                   <?php $totalDiscount = 0; ?>
                   <?php
                    foreach ($value2['sibling'] as $key => $value3) {
                    foreach ($value3['student_discount_fee'] as $key => $value) {
                         $totalDiscount += $value['amount'];
                         $arears += $value3['amount'];
                    }}?>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Monthly Fee" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $tution_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Annual Fund" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $anual_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Book & Note Book / Stationery" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $book_stationary_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Admission Fee" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $addmission_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Others (-)" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php
                               echo $other_fee ?></b>
                       </td>
                   </tr>
                   <tr>
                       <td class="borderbottom"><b><?php echo "Discount" ?></b></td>
                       <td style="text-align: center" class="borderbottom">
                           <b><?php  echo $totalDiscount; ?></b>
                       </td>
                   </tr>
                    <tr>
                                        <td class="borderbottom"><b>  <?php echo 'Arrears (if any)'?></b></td>
<!--                        <td><b>  --><?php //echo 'Previous Balance'?><!--</b></td>-->
                        <td style="text-align: center" class="borderbottom">
                            <b><?php echo $arears + $pendingFee ?></b>
                            <!--<b><?php
/*            foreach ($value2['sibling'] as $key => $value3) {
                echo $pendingFee = $value3['student_pending_fee'];
            }*/?></b>-->
                        </td>
                    </tr>
                    <!--<tr>
                <td><b>Previous Balance</b></td>
                <td style="text-align: center" class="borderbottom"><b><?php /*echo $pendingFee */?></b>
                </td>
            </tr>-->


                    <tr class="total">
                        <td style="color: white;background-color: black;"><b>Payable Amount (within Date)</b></td>
                        <td style="text-align: center;border-right: 1px solid black;border-left: 1px solid black;"><b><?php echo ($totalFees+$pendingFee+$arears ); ?></b></td>
                    </tr>
                   <tr class="">
                        <td style="text-align: right" colspan="2"class="borderbottom">
                            <?php
                            echo convertNumberToWord(($totalFees+$pendingFee+$arears ))." only";
                            ?>
                        </td>
                    </tr>
                   <tr class="total">
                       <td style="color: white;background-color: black;"><b>Payable Amount (after due Date)</b></td>
                       <td style="text-align: center;border-right: 1px solid black;border-left: 1px solid black;"><b><?php echo ($totalFees+$pendingFee+$arears ); ?></b></td>
                   </tr>
                    </tbody>
                </table>
                <h3 style="margin-left: 2px" class="borderbottom">Note:</h3>
                <h4 style="margin-left: 2px" class="month">1. Last date of Fee submission is 10th of every month.</h4>
                <h4 style="margin-left: 2px" class="month">2. Fine will be charged Rs: 200 after due date.</h4>
                <h4 style="margin-left: 2px" class="month">3. Save your paid slip for atleast one academic year.</h4>
                <table class="table1" hidden>
                    <tbody>
                    <tr>
                        <td colspan="8" style="background-color: black;color: white;text-align: center;">Voucher Paid History</td>

                    </tr>
                    <tr>
                        <td>Month</td>
                        <td>Vou#</td>
                        <td>AMT</td>
                        <td>Paid</td>
                        <td>Month</td>
                        <td>Vou#</td>
                        <td>AMT</td>
                        <td>Paid</td>
                    </tr>
                    <tr>
                        <td>Jan-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Jul-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Feb-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Aug-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Mar-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Sep-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Apr-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Oct-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>May-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Nov-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Jun-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Dec-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
                <table class="table">
                    <tbody>
                    <tr>
                        <td>Received Amount by Bank Official</td>
                        <td>___________________</td>
                    </tr>
                    <tr>
                        <td style="font-size: 20px">* <b> <?php
                                foreach ($value2['sibling'] as $key => $value3) {
                                    echo $value3['student']["parent_id"];
                                    break;
                                }
                                ?></b> *</td>
                        <td>Bank Stamp & Signature</td>
                    </tr>
                    </tbody>
                </table>

            </div>



        <br><br><br>
        <?php } else{?>
            <div class="dataTable_wrapper" id="tables"
                 style="display:inline-block; width:330px;font-size:12px;margin-left: 0px">
                <div style="font-weight:bold;width:100%;margin-top: 5px;padding-left:5px;">
                    <span style="width:220px;float: left;text-align: center;border-right: 1px solid black;border-left: 1px solid black;">--STUDENT FEE SLIP--</span>
                    <span style="margin-left: 5px;">Office Copy</span>
                </div>
                <br>

                <div style="text-align: center;width: 100%; margin-left: 2px;height: 70px; border-bottom: 1px solid black;">
                    <span><img style="float:left; margin-top: -9px;"
                               src="<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>" height="70" width="70" alt=""></span>
                    <span style=" font-size: 20px;line-height:20px"><b><?php echo $school?> </b></span>
                    <br>
                    <span style="text-align: left; display:none;">Address: <?php echo $settinglist[0]['address'];?></span>
                    <br>
                    <span style="text-align: left; display:none;">Bank: The Bank Of Khyber <!--<br>-->Branch: Ryk Branch</span>
                    <br>
                    <span style="text-align: left; display:none;">Acc#: 00112-003</span>
                </div>
                <!--<h4 style="width: 290px; margin-left: 2px;height: 90px;">
                    <img style="float: right; margin-top: -9px;"
                         src="<?php /*echo base_url() */?>uploads/school_content/logo/<?php /*echo $settinglist[0]['image']; */?>" height="70" width="70" alt="">
                    <span><b><?php /*echo $school*/?> </b></span><br><br>Address: <?php /*echo $settinglist[0]['address'];*/?><br>Contact No: <?php /*echo $settinglist[0]['phone']; */?>

                </h4>-->
                <!--<div class="branchname">
                    <h3>Bank Copy</h3>
                </div>-->
                <table class="table borderbottom"style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="border-right: 1px solid black;"><b>Voucher No:</b></td>
                        <td style="text-align: center;border-right: 1px solid black;" class=""><?php

                              echo $value2['student']["parent_id"];

                            ?></td>
                        <td style="border-right: 1px solid black;"><b>Account Id:</b></td>
                        <td style="text-align: center;border-right: 1px solid black;" class=""><?php

                                    echo $value2['student']["parent_id"];

                            ?></td>
                    </tr>
                    </tbody>
                </table>
                <table class="table " style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="text-align: center;border-right: 1px solid black;"
                            class=""><b>Fee Month:</b>
                        <?php
                           foreach ($value2['student_due_fee'] as $key => $value) {
                               if ((isset($value->fees[0]) && !empty($value->fees[0]->due_date)) > 0) {
                                   echo date('M-y', strtotime($value->fees[0]->due_date)) . " to " . date('M-y', strtotime($value->fees[0]->due_date));
                                   break;
                               }
                           }
                        ?>
                        </td>
                        <td style="border-right: 1px solid black;">1</td>
                        <td style="border-right: 1px solid black;">Student #:</td>
                        <td style="border-right: 1px solid black;">1</td>
                    </tr>
                    </tbody>
                    <table class="table borderbottom"style="text-align: left">
                        <tbody>

                        <tr>
                            <td style="">
                                <b>Name: </b>
                                <?php
                                        echo ($value2['student']['firstname'] . ' ' . $value2['student']['lastname'] ." S/O ".$value2['student']['father_name']) ;
                                       ?>

                            </td>
                        </tr>
                        </tbody>
                    </table>
                </table>
                <table class="table borderbottom"style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="">
                            <?php
                                    echo($value2['student']['class'] . '(' . $value2['student']['section'] . ')');

                            ?>
                        </td>
                        <td style="text-align: center;" class="">Due Date:</td>
                        <td style="text-align: center;" class="">
                            <?php
                            foreach ($value2['student_due_fee'] as $key => $value) {
                            if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                            ?>
                        <td style="text-align: center"
                            class=""><u><?php echo date('d-M-Y', strtotime($value->fees[0]->due_date)) ?><u></td>
                        <?php break;
                        }  }?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <table class="table">
                    <tbody>
                    <!-- <tr>
                        <td><b>Validity Date:</b></td>
                        <?php /*  foreach($masterData as $value2){
                            foreach ($value2['sibling'] as $key => $value1) {
                        foreach ($value1['student_due_fee'] as $key => $value) {
                            if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                */?>
                                <td style="text-align: center"
                                    class="borderbottom"><?php /*echo date('d-F-Y', strtotime($value->fees[0]->due_date)) */?></td>
                                <?php /*break;
                            }  }break;}}*/?>
                    </tr>
                    <tr>
                        <td><b>Reg No:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
                    /*                            foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                    echo  $value2['student']["admission_no"].',';
                                                }break;}*/?></td>
                    </tr>
                    <tr>
                        <td><b>Roll No:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
                    /*
                                                foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                        echo $value2['student']["roll_no"].',';
                                                    }break;}*/?></td>
                    </tr>
                    <tr>
                        <td><b>Name:</b></td>
                        <td style="text-align: center"
                            class="borderbottom"><?php
                    /*
                                                foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                        echo ($value2['student']['firstname'] . ' ' . $value2['student']['lastname'].',') ;
                                                }break;}
                                                */?></td>
                    </tr>
                    <tr>
                        <td><b>Father:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
                    /*
                                                foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                        echo ($value2['student']['father_name'] ) ;
                                                        break;}break;}
                                                */?></td>
                    </tr>
                    <tr>
                        <td><b>Class:</b></td>
                        <td style="text-align: center"
                            class="borderbottom"><?php
                    /*
                                                foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                        echo($value2['student']['class'] . '(' . $value2['student']['section'] . ')'.',');
                                                    }break;}
                                                */?></td>
                    </tr>
                    <tr>
                        <td><b>Fee For:</b></td>
                        <?php
                    /*                        foreach($masterData as $value2) {*/?>

                        <?php /*foreach ($value2['sibling'] as $key => $value1) {
                            foreach ($value1['student_due_fee'] as $key => $value) {
                                if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                    */?>
                                    <td style="text-align: center"
                                        class="borderbottom"><?php /*echo date('F', strtotime($value->fees[0]->due_date)) */?></td>
                                    <?php /*break;
                                }  }break;}}*/?>
                    </tr>-->
                    <tr class="" style="color: white;background-color: black;">
                        <td style=""><b>Description</b></td>
                        <td style="text-align: center" class=""><b>Amount(Rs.)</b></td>
                    </tr>
                    <?php $totalFees = 0;$pendingFee=0;$arears=0;

                           /* foreach ($value2['student_pending_fee'] as $key => $value1) {
                                */?><!--
                                --><?php /*for ($i = 0; $i < count($value1->fees); $i++) {
                                    $pendingFee+=$value1->fees[$i]->amount;
                                }
                            }*/?>
                    <?php $type='';$amount=0;$tution_fee=0;$anual_fee=0;$addmission_fee=0;$book_stationary_fee=0;$other_fee=0;
                    foreach ($value2['student_due_fee'] as $key => $value) {
                     for ($i = 0; $i < count($value->fees); $i++) {
                     if ($value->name == 'Balance Master') {
//                         $arears += $value->fees[$i]->amount;
                     }
                    if ($value->name != 'Balance Master') {
                        $amount_discount=0; $amount_deposit=0;
                        $amount_detail = json_decode($value->fees[$i]->amount_detail);
                        if (is_object($amount_detail)) {
                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                $amount_deposit += $amount_detail_value->amount;
                                $amount_discount += $amount_detail_value->amount_discount;
                            }
                        }
                        // echo "<pre>"; print_r($amount_deposit . " - " . $amount_discount); 
                    $type =$value->fees[$i]->type;
                    $code =$value->fees[$i]->code;
                    $amount+=$value->fees[$i]->amount;
                    $totalFees += $value->fees[$i]->amount - ($amount_deposit + $amount_discount);
                    if($code=="Tuition"){
                        $tution_fee+=$value->fees[$i]->amount - ($amount_deposit + $amount_discount);
                    }elseif($code=="Annual"){
                        $anual_fee+=$value->fees[$i]->amount;
                    }elseif($code=="Books&Stationery"){
                        $book_stationary_fee+=$value->fees[$i]->amount;
                    }elseif($code=="Admission"){
                        $addmission_fee+=$value->fees[$i]->amount;
                    }else{
                        $other_fee+=$value->fees[$i]->amount;
                    }
                    ?>

                    <?php
                    }

                    }
                    }

                    /*foreach($masterData as $value2) {
                        foreach ($value2['student_due_fee'] as $key => $value) {
                            */?><!--
                    <?php /*for ($i = 0; $i < count($value->fees); $i++) { */?>
                        <?php /*if ($value->name == 'Balance Master') { */?>
                            <?php /*$arears += $value->fees[$i]->amount; */?>
                        <?php /*} */?>
                        <?php /*if ($value->name != 'Balance Master') {
                            */?>
                            <tr >
                                <td ><b ><?php /*echo $value->fees[$i]->type */?></b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php /*$totalFees += $value->fees[$i]->amount;
                                        echo $value->fees[$i]->amount */?></b>
                                </td>
                            </tr>
                            --><?php
                    /*                        }

                                        }
                                    }
                                }*/
                    ?>

                    <?php $totalDiscount = 0; ?>
                    <?php
                            foreach ($value2['student_discount_fee'] as $key => $value) {
                                $totalDiscount += $value['amount'];
                            }
                    ?>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Monthly Fee" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $tution_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Annual Fund" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $anual_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Book & Note Book / Stationery" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $book_stationary_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Admission Fee" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $addmission_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Others (-)" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $other_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Discount" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php  echo $totalDiscount; ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b>  <?php echo 'Arrears (if any)'?></b></td>
                        <!--                        <td><b>  --><?php //echo 'Previous Balance'?><!--</b></td>-->
                        <td style="text-align: center" class="borderbottom">
<!--                            <b>--><?php //echo $arears + $pendingFee ?><!--</b>-->
                            <b><?php echo $pendingFee= $value2['student_pending_fee'] ?></b>
                        </td>
                    </tr>
                    <!--<tr>
                <td><b>Previous Balance</b></td>
                <td style="text-align: center" class="borderbottom"><b><?php /*echo $pendingFee */?></b>
                </td>
            </tr>-->


                    <tr class="total">
                        <td style="color: white;background-color: black;"><b>Payable Amount (within Date)</b></td>
                        <td style="text-align: center;border-right: 1px solid black;border-left: 1px solid black;"><b><?php echo ($totalFees+$pendingFee ); ?></b></td>
                    </tr>
                    <tr class="">
                        <td style="text-align: right" colspan="2"class="borderbottom">
                            <?php
                            echo convertNumberToWord(($totalFees+$pendingFee ))." only";
                            ?>
                        </td>
                    </tr>
                    <tr class="total">
                        <td style="color: white;background-color: black;"><b>Payable Amount (after due Date)</b></td>
                        <td style="text-align: center;border-right: 1px solid black;border-left: 1px solid black;"><b><?php echo ($totalFees+$pendingFee ); ?></b></td>
                    </tr>
                    </tbody>
                </table>
                <h3 style="margin-left: 2px" class="borderbottom">Note:</h3>
                <h4 style="margin-left: 2px" class="month">1. Last date of Fee submission is 10th of every month.</h4>
                <h4 style="margin-left: 2px" class="month">2. Fine will be charged Rs: 200 after due date.</h4>
                <h4 style="margin-left: 2px" class="month">3. Save your paid slip for atleast one academic year.</h4>
                <table class="table1" hidden>
                    <tbody>
                    <tr>
                        <td colspan="8" style="background-color: black;color: white;text-align: center;">Voucher Paid History</td>

                    </tr>
                    <tr>
                        <td>Month</td>
                        <td>Vou#</td>
                        <td>AMT</td>
                        <td>Paid</td>
                        <td>Month</td>
                        <td>Vou#</td>
                        <td>AMT</td>
                        <td>Paid</td>
                    </tr>
                    <tr>
                        <td>Jan-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Jul-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Feb-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Aug-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Mar-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Sep-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Apr-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Oct-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>May-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Nov-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Jun-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Dec-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
                <table class="table">
                    <tbody>
                    <tr>
                        <td>Received Amount by Bank Official</td>
                        <td>___________________</td>
                    </tr>
                    <tr>
                        <td style="font-size: 20px">* <b> <?php echo $value2['student']["parent_id"];?></b> *</td>
                        <td>Bank Stamp & Signature</td>
                    </tr>
                    </tbody>
                </table>

            </div>
            <div class="dataTable_wrapper" id="tables"
                 style="display:inline-block; width:330px;font-size:12px;margin-left: 0px">
                <div style="font-weight:bold;width:100%;margin-top: 5px;padding-left:5px;">
                    <span style="width:220px;float: left;text-align: center;border-right: 1px solid black;border-left: 1px solid black;">--STUDENT FEE SLIP--</span>
                    <span style="margin-left: 5px;">Bank Copy</span>
                </div>
                <br>

                <div style="text-align: center;width: 100%; margin-left: 2px;height: 70px; border-bottom: 1px solid black;">
                    <span><img style="float:left; margin-top: -9px;"
                               src="<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>" height="70" width="70" alt=""></span>
                    <span style=" font-size: 20px;line-height:20px"><b><?php echo $school?> </b></span>
                    <br>
                    <span style="text-align: left; display:none;">Address: <?php echo $settinglist[0]['address'];?></span>
                    <br>
                    <span style="text-align: left; display:none;">Bank: The Bank Of Khyber <!--<br>-->Branch: Ryk Branch</span>
                    <br>
                    <span style="text-align: left; display:none;">Acc#: 00112-003</span>
                </div>
                <!--<h4 style="width: 290px; margin-left: 2px;height: 90px;">
                    <img style="float: right; margin-top: -9px;"
                         src="<?php /*echo base_url() */?>uploads/school_content/logo/<?php /*echo $settinglist[0]['image']; */?>" height="70" width="70" alt="">
                    <span><b><?php /*echo $school*/?> </b></span><br><br>Address: <?php /*echo $settinglist[0]['address'];*/?><br>Contact No: <?php /*echo $settinglist[0]['phone']; */?>

                </h4>-->
                <!--<div class="branchname">
                    <h3>Bank Copy</h3>
                </div>-->
                <table class="table borderbottom"style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="border-right: 1px solid black;"><b>Voucher No:</b></td>
                        <td style="text-align: center;border-right: 1px solid black;" class=""><?php

                              echo $value2['student']["parent_id"];

                            ?></td>
                        <td style="border-right: 1px solid black;"><b>Account Id:</b></td>
                        <td style="text-align: center;border-right: 1px solid black;" class=""><?php

                                    echo $value2['student']["parent_id"];

                            ?></td>
                    </tr>
                    </tbody>
                </table>
                <table class="table " style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="text-align: center;border-right: 1px solid black;"
                            class=""><b>Fee Month:</b>
                        <?php
                           foreach ($value2['student_due_fee'] as $key => $value) {
                               if ((isset($value->fees[0]) && !empty($value->fees[0]->due_date)) > 0) {
                                   echo date('M-y', strtotime($value->fees[0]->due_date)) . " to " . date('M-y', strtotime($value->fees[0]->due_date));
                                   break;
                               }
                           }
                        ?>
                        </td>
                        <td style="border-right: 1px solid black;">1</td>
                        <td style="border-right: 1px solid black;">Student #:</td>
                        <td style="border-right: 1px solid black;">1</td>
                    </tr>
                    </tbody>
                    <table class="table borderbottom"style="text-align: left">
                        <tbody>

                        <tr>
                            <td style="">
                                <b>Name: </b>
                                <?php
                                        echo ($value2['student']['firstname'] . ' ' . $value2['student']['lastname'] ." S/O ".$value2['student']['father_name']) ;
                                       ?>

                            </td>
                        </tr>
                        </tbody>
                    </table>
                </table>
                <table class="table borderbottom"style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="">
                            <?php
                                    echo($value2['student']['class'] . '(' . $value2['student']['section'] . ')');

                            ?>
                        </td>
                        <td style="text-align: center;" class="">Due Date:</td>
                        <td style="text-align: center;" class="">
                            <?php
                            foreach ($value2['student_due_fee'] as $key => $value) {
                            if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                            ?>
                        <td style="text-align: center"
                            class=""><u><?php echo date('d-M-Y', strtotime($value->fees[0]->due_date)) ?><u></td>
                        <?php break;
                        }  }?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <table class="table">
                    <tbody>
                    <!-- <tr>
                        <td><b>Validity Date:</b></td>
                        <?php /*  foreach($masterData as $value2){
                            foreach ($value2['sibling'] as $key => $value1) {
                        foreach ($value1['student_due_fee'] as $key => $value) {
                            if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                */?>
                                <td style="text-align: center"
                                    class="borderbottom"><?php /*echo date('d-F-Y', strtotime($value->fees[0]->due_date)) */?></td>
                                <?php /*break;
                            }  }break;}}*/?>
                    </tr>
                    <tr>
                        <td><b>Reg No:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
                    /*                            foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                    echo  $value2['student']["admission_no"].',';
                                                }break;}*/?></td>
                    </tr>
                    <tr>
                        <td><b>Roll No:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
                    /*
                                                foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                        echo $value2['student']["roll_no"].',';
                                                    }break;}*/?></td>
                    </tr>
                    <tr>
                        <td><b>Name:</b></td>
                        <td style="text-align: center"
                            class="borderbottom"><?php
                    /*
                                                foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                        echo ($value2['student']['firstname'] . ' ' . $value2['student']['lastname'].',') ;
                                                }break;}
                                                */?></td>
                    </tr>
                    <tr>
                        <td><b>Father:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
                    /*
                                                foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                        echo ($value2['student']['father_name'] ) ;
                                                        break;}break;}
                                                */?></td>
                    </tr>
                    <tr>
                        <td><b>Class:</b></td>
                        <td style="text-align: center"
                            class="borderbottom"><?php
                    /*
                                                foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                        echo($value2['student']['class'] . '(' . $value2['student']['section'] . ')'.',');
                                                    }break;}
                                                */?></td>
                    </tr>
                    <tr>
                        <td><b>Fee For:</b></td>
                        <?php
                    /*                        foreach($masterData as $value2) {*/?>

                        <?php /*foreach ($value2['sibling'] as $key => $value1) {
                            foreach ($value1['student_due_fee'] as $key => $value) {
                                if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                    */?>
                                    <td style="text-align: center"
                                        class="borderbottom"><?php /*echo date('F', strtotime($value->fees[0]->due_date)) */?></td>
                                    <?php /*break;
                                }  }break;}}*/?>
                    </tr>-->
                    <tr class="" style="color: white;background-color: black;">
                        <td style=""><b>Description</b></td>
                        <td style="text-align: center" class=""><b>Amount(Rs.)</b></td>
                    </tr>
                    <?php $totalFees = 0;$pendingFee=0;$arears=0;

                           /* foreach ($value2['student_pending_fee'] as $key => $value1) {
                                */?><!--
                                --><?php /*for ($i = 0; $i < count($value1->fees); $i++) {
                                    $pendingFee+=$value1->fees[$i]->amount;
                                }
                            }*/?>
                    <?php $type='';$amount=0;$tution_fee=0;$anual_fee=0;$addmission_fee=0;$book_stationary_fee=0;$other_fee=0; 
                    foreach ($value2['student_due_fee'] as $key => $value) {
                    for ($i = 0; $i < count($value->fees); $i++) {
                        //  if($value->amount_detail)
                     if ($value->name == 'Balance Master') {
//                         $arears += $value->fees[$i]->amount;
                     }
                    if ($value->name != 'Balance Master') {
                        $amount_discount=0; $amount_deposit=0;
                        $amount_detail = json_decode($value->fees[$i]->amount_detail);
                        if (is_object($amount_detail)) {
                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                $amount_deposit += $amount_detail_value->amount;
                                $amount_discount += $amount_detail_value->amount_discount;
                            }
                        }
                        // echo "<pre>"; print_r($amount_deposit . " - " . $amount_discount); 
                    $type =$value->fees[$i]->type;
                    $code =$value->fees[$i]->code;
                    $amount+=$value->fees[$i]->amount;
                    $totalFees += $value->fees[$i]->amount - ($amount_deposit + $amount_discount);
                    if($code=="Tuition"){
                        $tution_fee+=$value->fees[$i]->amount - ($amount_deposit + $amount_discount);
                    }elseif($code=="Annual"){
                        $anual_fee+=$value->fees[$i]->amount;
                    }elseif($code=="Books&Stationery"){
                        $book_stationary_fee+=$value->fees[$i]->amount;
                    }elseif($code=="Admission"){
                        $addmission_fee+=$value->fees[$i]->amount;
                    }else{
                        $other_fee+=$value->fees[$i]->amount;
                    }
                    ?>

                    <?php
                    }

                    }
                    }

                    /*foreach($masterData as $value2) {
                        foreach ($value2['student_due_fee'] as $key => $value) {
                            */?><!--
                    <?php /*for ($i = 0; $i < count($value->fees); $i++) { */?>
                        <?php /*if ($value->name == 'Balance Master') { */?>
                            <?php /*$arears += $value->fees[$i]->amount; */?>
                        <?php /*} */?>
                        <?php /*if ($value->name != 'Balance Master') {
                            */?>
                            <tr >
                                <td ><b ><?php /*echo $value->fees[$i]->type */?></b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php /*$totalFees += $value->fees[$i]->amount;
                                        echo $value->fees[$i]->amount */?></b>
                                </td>
                            </tr>
                            --><?php
                    /*                        }

                                        }
                                    }
                                }*/
                    ?>

                    <?php $totalDiscount = 0; ?>
                    <?php
                            foreach ($value2['student_discount_fee'] as $key => $value) {
                                $totalDiscount += $value['amount'];
                            }
                    ?>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Monthly Fee" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $tution_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Annual Fund" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $anual_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Book & Note Book / Stationery" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $book_stationary_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Admission Fee" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $addmission_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Others (-)" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $other_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Discount" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php  echo $totalDiscount; ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b>  <?php echo 'Arrears (if any)'?></b></td>
                        <!--                        <td><b>  --><?php //echo 'Previous Balance'?><!--</b></td>-->
                        <td style="text-align: center" class="borderbottom">
<!--                            <b>--><?php //echo $arears + $pendingFee ?><!--</b>-->
                            <b><?php echo $pendingFee= $value2['student_pending_fee'] ?></b>
                        </td>
                    </tr>
                    <!--<tr>
                <td><b>Previous Balance</b></td>
                <td style="text-align: center" class="borderbottom"><b><?php /*echo $pendingFee */?></b>
                </td>
            </tr>-->


                    <tr class="total">
                        <td style="color: white;background-color: black;"><b>Payable Amount (within Date)</b></td>
                        <td style="text-align: center;border-right: 1px solid black;border-left: 1px solid black;"><b><?php echo ($totalFees+$pendingFee); ?></b></td>
                    </tr>
                    <tr class="">
                        <td style="text-align: right" colspan="2"class="borderbottom">
                            <?php
                            echo convertNumberToWord(($totalFees+$pendingFee))." only";
                            ?>
                        </td>
                    </tr>
                    <tr class="total">
                        <td style="color: white;background-color: black;"><b>Payable Amount (after due Date)</b></td>
                        <td style="text-align: center;border-right: 1px solid black;border-left: 1px solid black;"><b><?php echo ($totalFees+$pendingFee); ?></b></td>
                    </tr>
                    </tbody>
                </table>
                <h3 style="margin-left: 2px" class="borderbottom">Note:</h3>
                <h4 style="margin-left: 2px" class="month">1. Last date of Fee submission is 10th of every month.</h4>
                <h4 style="margin-left: 2px" class="month">2. Fine will be charged Rs: 200 after due date.</h4>
                <h4 style="margin-left: 2px" class="month">3. Save your paid slip for atleast one academic year.</h4>
                <table class="table1" hidden>
                    <tbody>
                    <tr>
                        <td colspan="8" style="background-color: black;color: white;text-align: center;">Voucher Paid History</td>

                    </tr>
                    <tr>
                        <td>Month</td>
                        <td>Vou#</td>
                        <td>AMT</td>
                        <td>Paid</td>
                        <td>Month</td>
                        <td>Vou#</td>
                        <td>AMT</td>
                        <td>Paid</td>
                    </tr>
                    <tr>
                        <td>Jan-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Jul-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Feb-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Aug-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Mar-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Sep-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Apr-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Oct-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>May-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Nov-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Jun-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Dec-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
                <table class="table">
                    <tbody>
                    <tr>
                        <td>Received Amount by Bank Official</td>
                        <td>___________________</td>
                    </tr>
                    <tr>
                        <td style="font-size: 20px">* <b> <?php echo $value2['student']["parent_id"];?></b> *</td>
                        <td>Bank Stamp & Signature</td>
                    </tr>
                    </tbody>
                </table>

            </div>
            <div class="dataTable_wrapper" id="tables"
                 style="display:inline-block; width:330px;font-size:12px;margin-left: 0px">
                <div style="font-weight:bold;width:100%;margin-top: 5px;padding-left:5px;">
                    <span style="width:220px;float: left;text-align: center;border-right: 1px solid black;border-left: 1px solid black;">--STUDENT FEE SLIP--</span>
                    <span style="margin-left: 5px;">Student Copy</span>
                </div>
                <br>

                <div style="text-align: center;width: 100%; margin-left: 2px;height: 70px; border-bottom: 1px solid black;">
                    <span><img style="float:left; margin-top: -9px;"
                               src="<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>" height="70" width="70" alt=""></span>
                    <span style=" font-size: 20px;line-height:20px"><b><?php echo $school?> </b></span>
                    <br>
                    <span style="text-align: left; display:none;">Address: <?php echo $settinglist[0]['address'];?></span>
                    <br>
                    <span style="text-align: left; display:none;">Bank: The Bank Of Khyber <!--<br>-->Branch: Ryk Branch</span>
                    <br>
                    <span style="text-align: left; display:none;">Acc#: 00112-003</span>
                </div>
                <!--<h4 style="width: 290px; margin-left: 2px;height: 90px;">
                    <img style="float: right; margin-top: -9px;"
                         src="<?php /*echo base_url() */?>uploads/school_content/logo/<?php /*echo $settinglist[0]['image']; */?>" height="70" width="70" alt="">
                    <span><b><?php /*echo $school*/?> </b></span><br><br>Address: <?php /*echo $settinglist[0]['address'];*/?><br>Contact No: <?php /*echo $settinglist[0]['phone']; */?>

                </h4>-->
                <!--<div class="branchname">
                    <h3>Bank Copy</h3>
                </div>-->
                <table class="table borderbottom"style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="border-right: 1px solid black;"><b>Voucher No:</b></td>
                        <td style="text-align: center;border-right: 1px solid black;" class=""><?php

                              echo $value2['student']["parent_id"];

                            ?></td>
                        <td style="border-right: 1px solid black;"><b>Account Id:</b></td>
                        <td style="text-align: center;border-right: 1px solid black;" class=""><?php

                                    echo $value2['student']["parent_id"];

                            ?></td>
                    </tr>
                    </tbody>
                </table>
                <table class="table " style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="text-align: center;border-right: 1px solid black;"
                            class=""><b>Fee Month:</b>
                        <?php
                           foreach ($value2['student_due_fee'] as $key => $value) {
                               if ((isset($value->fees[0]) && !empty($value->fees[0]->due_date)) > 0) {
                                   echo date('M-y', strtotime($value->fees[0]->due_date)) . " to " . date('M-y', strtotime($value->fees[0]->due_date));
                                   break;
                               }
                           }
                        ?>
                        </td>
                        <td style="border-right: 1px solid black;">1</td>
                        <td style="border-right: 1px solid black;">Student #:</td>
                        <td style="border-right: 1px solid black;">1</td>
                    </tr>
                    </tbody>
                    <table class="table borderbottom"style="text-align: left">
                        <tbody>

                        <tr>
                            <td style="">
                                <b>Name: </b>
                                <?php
                                        echo ($value2['student']['firstname'] . ' ' . $value2['student']['lastname'] ." S/O ".$value2['student']['father_name']) ;
                                       ?>

                            </td>
                        </tr>
                        </tbody>
                    </table>
                </table>
                <table class="table borderbottom"style="text-align: center">
                    <tbody>

                    <tr>
                        <td style="">
                            <?php
                                    echo($value2['student']['class'] . '(' . $value2['student']['section'] . ')');

                            ?>
                        </td>
                        <td style="text-align: center;" class="">Due Date:</td>
                        <td style="text-align: center;" class="">
                            <?php
                            foreach ($value2['student_due_fee'] as $key => $value) {
                            if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                            ?>
                        <td style="text-align: center"
                            class=""><u><?php echo date('d-M-Y', strtotime($value->fees[0]->due_date)) ?><u></td>
                        <?php break;
                        }  }?>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <table class="table">
                    <tbody>
                    <!-- <tr>
                        <td><b>Validity Date:</b></td>
                        <?php /*  foreach($masterData as $value2){
                            foreach ($value2['sibling'] as $key => $value1) {
                        foreach ($value1['student_due_fee'] as $key => $value) {
                            if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                */?>
                                <td style="text-align: center"
                                    class="borderbottom"><?php /*echo date('d-F-Y', strtotime($value->fees[0]->due_date)) */?></td>
                                <?php /*break;
                            }  }break;}}*/?>
                    </tr>
                    <tr>
                        <td><b>Reg No:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
                    /*                            foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                    echo  $value2['student']["admission_no"].',';
                                                }break;}*/?></td>
                    </tr>
                    <tr>
                        <td><b>Roll No:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
                    /*
                                                foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                        echo $value2['student']["roll_no"].',';
                                                    }break;}*/?></td>
                    </tr>
                    <tr>
                        <td><b>Name:</b></td>
                        <td style="text-align: center"
                            class="borderbottom"><?php
                    /*
                                                foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                        echo ($value2['student']['firstname'] . ' ' . $value2['student']['lastname'].',') ;
                                                }break;}
                                                */?></td>
                    </tr>
                    <tr>
                        <td><b>Father:</b></td>
                        <td style="text-align: center" class="borderbottom"><?php
                    /*
                                                foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                        echo ($value2['student']['father_name'] ) ;
                                                        break;}break;}
                                                */?></td>
                    </tr>
                    <tr>
                        <td><b>Class:</b></td>
                        <td style="text-align: center"
                            class="borderbottom"><?php
                    /*
                                                foreach($masterData as $value1){
                                                    foreach ($value1['sibling'] as $key => $value2) {
                                                        echo($value2['student']['class'] . '(' . $value2['student']['section'] . ')'.',');
                                                    }break;}
                                                */?></td>
                    </tr>
                    <tr>
                        <td><b>Fee For:</b></td>
                        <?php
                    /*                        foreach($masterData as $value2) {*/?>

                        <?php /*foreach ($value2['sibling'] as $key => $value1) {
                            foreach ($value1['student_due_fee'] as $key => $value) {
                                if((isset($value->fees[0]) && !empty($value->fees[0]->due_date))>0){
                                    */?>
                                    <td style="text-align: center"
                                        class="borderbottom"><?php /*echo date('F', strtotime($value->fees[0]->due_date)) */?></td>
                                    <?php /*break;
                                }  }break;}}*/?>
                    </tr>-->
                    <tr class="" style="color: white;background-color: black;">
                        <td style=""><b>Description</b></td>
                        <td style="text-align: center" class=""><b>Amount(Rs.)</b></td>
                    </tr>
                    <?php $totalFees = 0;$pendingFee=0;$arears=0;

                           /* foreach ($value2['student_pending_fee'] as $key => $value1) {
                                */?><!--
                                --><?php /*for ($i = 0; $i < count($value1->fees); $i++) {
                                    $pendingFee+=$value1->fees[$i]->amount;
                                }
                            }*/?>
                    <?php $type='';$amount=0;$tution_fee=0;$anual_fee=0;$addmission_fee=0;$book_stationary_fee=0;$other_fee=0;
                    foreach ($value2['student_due_fee'] as $key => $value) {
                     for ($i = 0; $i < count($value->fees); $i++) {
                     if ($value->name == 'Balance Master') {
//                         $arears += $value->fees[$i]->amount;
                     }
                    if ($value->name != 'Balance Master') {
                        $amount_discount=0; $amount_deposit=0;
                        $amount_detail = json_decode($value->fees[$i]->amount_detail);
                        if (is_object($amount_detail)) {
                            foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                $amount_deposit += $amount_detail_value->amount;
                                $amount_discount += $amount_detail_value->amount_discount;
                            }
                        }
                        // echo "<pre>"; print_r($amount_deposit . " - " . $amount_discount); 
                    $type =$value->fees[$i]->type;
                    $code =$value->fees[$i]->code;
                    $amount+=$value->fees[$i]->amount;
                    $totalFees += $value->fees[$i]->amount - ($amount_deposit + $amount_discount);
                    if($code=="Tuition"){
                        $tution_fee+=$value->fees[$i]->amount - ($amount_deposit + $amount_discount);
                    }elseif($code=="Annual"){
                        $anual_fee+=$value->fees[$i]->amount;
                    }elseif($code=="Books&Stationery"){
                        $book_stationary_fee+=$value->fees[$i]->amount;
                    }elseif($code=="Admission"){
                        $addmission_fee+=$value->fees[$i]->amount;
                    }else{
                        $other_fee+=$value->fees[$i]->amount;
                    }
                    ?>

                    <?php
                    }

                    }
                    }

                    /*foreach($masterData as $value2) {
                        foreach ($value2['student_due_fee'] as $key => $value) {
                            */?><!--
                    <?php /*for ($i = 0; $i < count($value->fees); $i++) { */?>
                        <?php /*if ($value->name == 'Balance Master') { */?>
                            <?php /*$arears += $value->fees[$i]->amount; */?>
                        <?php /*} */?>
                        <?php /*if ($value->name != 'Balance Master') {
                            */?>
                            <tr >
                                <td ><b ><?php /*echo $value->fees[$i]->type */?></b></td>
                                <td style="text-align: center" class="borderbottom">
                                    <b><?php /*$totalFees += $value->fees[$i]->amount;
                                        echo $value->fees[$i]->amount */?></b>
                                </td>
                            </tr>
                            --><?php
                    /*                        }

                                        }
                                    }
                                }*/
                    ?>

                    <?php $totalDiscount = 0; ?>
                    <?php
                            foreach ($value2['student_discount_fee'] as $key => $value) {
                                $totalDiscount += $value['amount'];
                            }
                    ?>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Monthly Fee" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $tution_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Annual Fund" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $anual_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Book & Note Book / Stationery" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $book_stationary_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Admission Fee" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $addmission_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Others (-)" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php
                                echo $other_fee ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b><?php echo "Discount" ?></b></td>
                        <td style="text-align: center" class="borderbottom">
                            <b><?php  echo $totalDiscount; ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td class="borderbottom"><b>  <?php echo 'Arrears (if any)'?></b></td>
                        <!--                        <td><b>  --><?php //echo 'Previous Balance'?><!--</b></td>-->
                        <td style="text-align: center" class="borderbottom">
<!--                            <b>--><?php //echo $arears + $pendingFee ?><!--</b>-->
                            <b><?php echo $pendingFee= $value2['student_pending_fee'] ?></b>
                        </td>
                    </tr>
                    <!--<tr>
                <td><b>Previous Balance</b></td>
                <td style="text-align: center" class="borderbottom"><b><?php /*echo $pendingFee */?></b>
                </td>
            </tr>-->


                    <tr class="total">
                        <td style="color: white;background-color: black;"><b>Payable Amount (within Date)</b></td>
                        <td style="text-align: center;border-right: 1px solid black;border-left: 1px solid black;"><b><?php echo ($totalFees+$pendingFee); ?></b></td>
                    </tr>
                    <tr class="">
                        <td style="text-align: right" colspan="2"class="borderbottom">
                            <?php
                            echo convertNumberToWord(($totalFees+$pendingFee ))." only";
                            ?>
                        </td>
                    </tr>
                    <tr class="total">
                        <td style="color: white;background-color: black;"><b>Payable Amount (after due Date)</b></td>
                        <td style="text-align: center;border-right: 1px solid black;border-left: 1px solid black;"><b><?php echo ($totalFees+$pendingFee ); ?></b></td>
                    </tr>
                    </tbody>
                </table>
                <h3 style="margin-left: 2px" class="borderbottom">Note:</h3>
                <h4 style="margin-left: 2px" class="month">1. Last date of Fee submission is 10th of every month.</h4>
                <h4 style="margin-left: 2px" class="month">2. Fine will be charged Rs: 200 after due date.</h4>
                <h4 style="margin-left: 2px" class="month">3. Save your paid slip for atleast one academic year.</h4>
                <table class="table1" hidden>
                    <tbody>
                    <tr>
                        <td colspan="8" style="background-color: black;color: white;text-align: center;">Voucher Paid History</td>

                    </tr>
                    <tr>
                        <td>Month</td>
                        <td>Vou#</td>
                        <td>AMT</td>
                        <td>Paid</td>
                        <td>Month</td>
                        <td>Vou#</td>
                        <td>AMT</td>
                        <td>Paid</td>
                    </tr>
                    <tr>
                        <td>Jan-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Jul-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Feb-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Aug-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Mar-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Sep-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Apr-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Oct-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>May-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Nov-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Jun-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>Dec-21</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    </tbody>
                </table>
                <table class="table">
                    <tbody>
                    <tr>
                        <td>Received Amount by Bank Official</td>
                        <td>___________________</td>
                    </tr>
                    <tr>
                        <td style="font-size: 20px">* <b> <?php echo $value2['student']["parent_id"];?></b> *</td>
                        <td>Bank Stamp & Signature</td>
                    </tr>
                    </tbody>
                </table>

            </div>


            <br><br><br>
    <?php  }}
    ?>
   <!-- -->
</div>
<?php
function convertNumberToWord($num = false)
{
    $num = str_replace(array(',', ' '), '' , trim($num));
    if(! $num) {
        return false;
    }
    $num = (int) $num;
    $words = array();
    $list1 = array('', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven',
        'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'
    );
    $list2 = array('', 'ten', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety', 'hundred');
    $list3 = array('', 'thousand', 'million', 'billion', 'trillion', 'quadrillion', 'quintillion', 'sextillion', 'septillion',
        'octillion', 'nonillion', 'decillion', 'undecillion', 'duodecillion', 'tredecillion', 'quattuordecillion',
        'quindecillion', 'sexdecillion', 'septendecillion', 'octodecillion', 'novemdecillion', 'vigintillion'
    );
    $num_length = strlen($num);
    $levels = (int) (($num_length + 2) / 3);
    $max_length = $levels * 3;
    $num = substr('00' . $num, -$max_length);
    $num_levels = str_split($num, 3);
    for ($i = 0; $i < count($num_levels); $i++) {
        $levels--;
        $hundreds = (int) ($num_levels[$i] / 100);
        $hundreds = ($hundreds ? ' ' . $list1[$hundreds] . ' hundred' . ' ' : '');
        $tens = (int) ($num_levels[$i] % 100);
        $singles = '';
        if ( $tens < 20 ) {
            $tens = ($tens ? ' ' . $list1[$tens] . ' ' : '' );
        } else {
            $tens = (int)($tens / 10);
            $tens = ' ' . $list2[$tens] . ' ';
            $singles = (int) ($num_levels[$i] % 10);
            $singles = ' ' . $list1[$singles] . ' ';
        }
        $words[] = $hundreds . $tens . $singles . ( ( $levels && ( int ) ( $num_levels[$i] ) ) ? ' ' . $list3[$levels] . ' ' : '' );
    } //end for loop
    $commas = count($words);
    if ($commas > 1) {
        $commas = $commas - 1;
    }
    return implode(' ', $words);
}
?>
</body>
</html>
