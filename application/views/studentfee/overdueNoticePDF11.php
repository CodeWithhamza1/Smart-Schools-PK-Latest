<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Student Over Due Notice</title>
    <style>
        .table {
            width: 100%;
            font-family: Arial, Helvetica, sans-serif !important;
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
    <div style="margin-top: 15px;"></div>
    <?php $flag= true;?>
    <?php foreach($masterData as $value2) {?>
        <?php
        /*if(count($value2['student_due_fee']) > 0){
            if(count($value2['student_due_fee'][0]->fees) > 0){
                $flag = true;
                //break;
            }
            else{
                $flag= false;
            }
        }*/
        if($value2['std_arrears'] > 0){
            $flag = true;
        }
        else{
            $flag= false;
        }
        ?>
        <?php // if($flag){?>
        <div class="dataTable_wrapper" id="tables"
             style="display:inline-block; width:320px;font-size:12px;margin-left: 5px;margin-top: 10px">
            <h4 style="width: 290px; margin-left: 2px;height: 50px;">
                <img style="float: right; margin-top: -9px;"
                     src="<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>" height="70" width="70" alt="">
                <b><?php echo $school?> </b><br><br> <?php echo $settinglist[0]['address'].' '. $settinglist[0]['phone']; ?>
            </h4>
            <div class="branchname">
                <h4> <?php $dateObj   = DateTime::createFromFormat('!m', $month);
                    $monthName = $dateObj->format('F');
                    echo 'Fees Clearance Certificate for the month of :'.' '.$monthName.'-'.$year?></h4>
            </div>
            <table class="table">
                <tbody>
                <tr>
                    <td><b>Std Code:</b></td>
                    <td style="text-align: center"
                                class="borderbottom"><?php echo $value2['student']["admission_no"]; ?></td>
                </tr>
                <tr>
                    <td><b>Class:</b></td>
                    <td style="text-align: center"
                        class="borderbottom"><?php echo($value2['student']['class']) ?></td>
                </tr>
                <tr>
                    <td><b>Section:</b></td>
                    <td style="text-align: center" class="borderbottom"><?php echo $value2['student']['section']; ?></td>
                </tr>
                <tr>
                    <td><b>Name:</b></td>
                    <td style="text-align: center"
                        class="borderbottom"><?php echo($value2['student']['firstname'] . ' ' . $value2['student']['lastname']) ?></td>
                </tr>
                <tr>
                    <td><b>Father:</b></td>
                    <td style="text-align: center" class="borderbottom"><?php echo($value2['student']['father_name']) ?></td>
                </tr>
                <tr>
                    <td><b>Mob #:</b></td>
                    <td style="text-align: center" class="borderbottom"><?php echo($value2['student']['father_phone']) ?></td>
                </tr>
                <tr>
                    <td><b>Address #:</b></td>
                    <td style="text-align: center" class="borderbottom"><?php echo($value2['student']['guardian_address']) ?></td>
                </tr>
               <!-- --><?php /*$totalFees = 0;
                $pendingFee=0;$pendingFee_paid=0;$pendingFee_discount=0;
                $arears=0;$arears_paid=0;$arears_discount=0;
                foreach ($value2['student_pending_fee'] as $key => $value1) {
                    for ($i = 0; $i < count($value1->fees); $i++) {
//                        $pendingFee+=$value1->fees[$i]->amount;
//                         if ($value->name != 'Balance Master') {
                            $amount_detail = json_decode($value->amount_detail);
                             $pendingFee += $value->fees[$i]->amount;
                            if (is_object($amount_detail)) {
                                foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                    //print_r($amount_detail);
                                    $pendingFee_paid += $amount_detail_value->amount;
                                    $pendingFee_discount = $discount + $amount_detail_value->amount_discount;
                                }
                            }
//                        }
                    }
                }
                foreach ($value2['student_due_fee'] as $key => $value) {
                     for ($i = 0; $i < count($value->fees); $i++) {
                        print_r($value);
                         if ($value->name == 'Balance Master') {
                            $amount_detail = json_decode($value->amount_detail);
                            $arears += $value->fees[$i]->amount;
                            if (is_object($amount_detail)) {
                                foreach ($amount_detail as $amount_detail_key => $amount_detail_value) {
                                    //print_r($amount_detail);
                                    $arears_paid += $amount_detail_value->amount;
                                    $arears_discount = $discount + $amount_detail_value->amount_discount;
                                }
                            }
                        }
                    }
                }
                $balance= ($pendingFee-($pendingFee_paid+$pendingFee_discount))+($arears-($arears_paid+$arears_discount));
                */?>
                
                </tbody>
            </table>
            <h3 style="margin-left: 2px" class="borderbottom">Note:</h3>
            <h5 style="margin-left: 2px;text-align: right" class="month"><br> </h5>
            <h5 style="margin-left: 2px;text-align: right" class="month"> <b><br></b></h5>
            <h5 style="margin-left: 2px;text-align: right" class="month"><br></h5>
            <h5 style="margin-left: 2px;text-align: right" class="month"><br> </h5>
        </div>
        <!--<div class="dataTable_wrapper" id="tables"
             style="display:inline-block; width:300px;font-size:12px;margin-left:16px;">
            <h4 style="width: 290px; margin-left: 2px;height: 50px;">
                <img style="float: right; margin-top: -9px;"
                     src="<?php /*echo base_url() */?>uploads/school_content/logo/<?php /*echo $settinglist[0]['image']; */?>" height="70" width="70" alt="">
                <b><?php /*echo $school*/?> </b><br><br>Contact No: <?php /*echo $settinglist[0]['Phone']; */?>
            </h4>
            <div class="branchname">
                <h4> <?php /*$dateObj   = DateTime::createFromFormat('!m', $month);
                    $monthName = $dateObj->format('F');
                    echo 'Over Dues Notice for the month of :'.' '.$monthName.'-'.$year*/?></h4>
            </div>
            <table class="table">
                <tbody>
                <tr>
                    <td><b>Std Code:</b></td>
                    <td style="text-align: center"
                        class="borderbottom"><?php /*echo $value2['student']["admission_no"]; */?></td>
                </tr>
                <tr>
                    <td><b>Class:</b></td>
                    <td style="text-align: center"
                        class="borderbottom"><?php /*echo($value2['student']['class']) */?></td>
                </tr>
                <tr>
                    <td><b>Section:</b></td>
                    <td style="text-align: center" class="borderbottom"><?php /*echo $value2['student']['section']; */?></td>
                </tr>
                <tr>
                    <td><b>Name:</b></td>
                    <td style="text-align: center"
                        class="borderbottom"><?php /*echo($value2['student']['firstname'] . ' ' . $value2['student']['lastname']) */?></td>
                </tr>
                <tr>
                    <td><b>Father:</b></td>
                    <td style="text-align: center" class="borderbottom"><?php /*echo($value2['student']['father_name']) */?></td>
                </tr>
                <tr>
                    <td><b>Mob #:</b></td>
                    <td style="text-align: center" class="borderbottom"><?php /*echo($value2['student']['father_name']) */?></td>
                </tr>
                <tr>
                    <td><b>Address #:</b></td>
                    <td style="text-align: center" class="borderbottom"><?php /*echo($value2['student']['father_name']) */?></td>
                </tr>
                <tr>
                    <td><b>Arrears Rs:</b></td>
                </tr>
                </tbody>
            </table>
            <h3 style="margin-left: 2px" class="borderbottom">Note:</h3>
            <h4 style="margin-left: 2px" class="month">1. Challan form is submitted only in this campus only</h4>
            <h4 style="margin-left: 2px" class="month">2. Due's must be submitted before validity date</h4>
            <h4 style="margin-left: 2px" class="month">3. After expiry of validity date fine will charged</h4>
        </div>-->
    <?php } 
    // }
    ?>
    <!-- --><?php /*}
    if($flag==false){
        echo("Fees not found for the given month!");
    }
    */?>
</div>
</body>
</html>