<!doctype html>
<html>
<head>
    <title>Fee Summary Report</title>
    <!-- Latest compiled and minified CSS -->
    <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <!-- jQuery library -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <!-- Latest compiled JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <style type="text/css">
        @font-face {
            font-family: "Report Font";
            src: url("/backend/Fonts/WhitneyHTF-Book.otf");
        }
        table {
            font-family: "Report Font", Arial, Helvetica, sans-serif !important;
            border-collapse: collapse;
            width: 95%;
        }

        table td, table th {
            border: 1px solid #060303;
            padding: 8px;
            font-size: 12px;
        }

        table td{
            font-family: "Report Font", Arial, Helvetica, sans-serif !important;
            font-weight: bold;
        }
        /* table tr:nth-child(even){background-color: #ddd;}

         table tr:hover {background-color: #ddd;}*/

        h1,h2,h3,h4,h5{
            font-family: "Report Font", Arial, Helvetica, sans-serif !important;
        }
        table th {
            font-family: "Report Font", Arial, Helvetica, sans-serif !important;
            padding-top: 6px;
            padding-bottom: 8px;
            text-align: left;
            font-weight: bold;
            background-color: #eeeaee;
        }
        footer { position: fixed; bottom: -45px; left: 0px; right: 0px; height: 50px; }
        p { page-break-after: always; }
        p:last-child { page-break-after: never; }
        @media print {
            #printpagebutton {
                display: none;
            }
        }
        .TABLE{
            font-family: "Report Font", Arial, Helvetica, sans-serif !important;
        }
        body{
            font-family: "Report Font", Arial, Helvetica, sans-serif !important;
            color: black;
        }
        .print_date{
            position: absolute;
            right: 0px;
            margin:0px ;
            margin-top: -20px;
            padding: 0px;
            font-weight: 200;
            font-size: 10px
        }
    </style>
</head>
<body >
<div class="container" style="display: inline-flex;text-align: center; ">
    <div style="padding-left: 20px"><img src="<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>" alt="Trulli" width="85px" height="85px">
    </div>
    <div style="padding-left: 150px"> <h4 style=" text-align: center">
            <u><b><?php echo $school?></b></u>
        </h4>
        <h4 style="text-align: center"><b><b><u><?php echo "Daily Fee Summary". ' '. date('d-F-Y', strtotime($date))?></u></b></h4>
        <?php if($user!='all'){?>
            <h4 style="text-align: center"><b><b><u><?php echo "Collected By". ' '. $user?></u></b></h4>
        <?php }?>
        <p><span class="print_date">Printed on: <?php echo date('d-M-y h:i:s A');?></span></p>

    </div>
</div>
<div class="dataTable_wrapper TABLE">
    <table class="table" id="dataTables-example" align="center">
        <thead>
        <tr style="background-color: #ddd;">

            <th>
                <?php echo 'Class'; ?>
            </th>
            <th>
                <?php echo 'Code'; ?>
            </th>
            <th>
                <?php echo 'Name'; ?>
            </th>
            <th>
                <?php echo 'Due Type'; ?>
            </th>
            <!--<th>
                <?php /*echo 'Arrear'; */?>
            </th>
            <th>
                <?php /*echo 'Rcvable'; */?>
            </th>-->
            <th>
                <?php echo 'Rcvd'; ?>
            </th>
            <th>
                <?php echo 'Conc'; ?>
            </th>
           <!-- <th>
                <?php /*echo 'Balance'; */?>
            </th>-->
        </tr>
        </thead>
        <tbody>
        <?php $fsum = 0;$data_1=array();
        foreach ($class as $key =>$classes) {
            $rcd=0;
        foreach ($sections as $key =>$sectionss) {
        foreach ($report as $item){
        if ($item['class_id'] == $classes->id&&$item['section_id'] ==$sectionss->id) {
            $obj = new stdClass();

            $obj->classId=$classes->id;
            $obj->sectionId=$sectionss->id;
            $obj->rcd=1;
            $data_1[]=$obj;

        }}}} ?>

        <?php $sum = 0;$sr=0;$s_arrear=0;$s_rcvable=0;$s_blnc=0;$s_discount=0;
        foreach ($class as $key =>$classes) {
        foreach ($sections as $key =>$sectionss) {
        $rcd = 1;
        foreach ($data_1  as $key => $item2) {

            if($classes->id==$item2->classId&&$sectionss->id==$item2->sectionId&&$item2->rcd==$rcd) {
                ?>
                <tr>
                    <td colspan="8" style="text-align: left;">
                        <b><?php echo 'Class' . ':' . ' ' . $classes->class . "-" . $sectionss->section; ?></b>
                    </td>
                </tr>
                <?php
                $rcd=0;
            }}

        foreach ($report as $item){

        if ($item['class_id'] == $classes->id&&$item['section_id'] ==$sectionss->id) {
        $fee_deposits = json_decode(($item->amount_detail));
        $amount = 0;
        $discount = 0;
        if ($user == 'all') {
            /*foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                if ($fee_deposits_value->date == $date) {
                    $amount += $fee_deposits_value->amount;
                    $discount += $fee_deposits_value->amount_discount;
                    $s_discount += $fee_deposits_value->amount_discount;
                    $sum += $fee_deposits_value->amount;
                }
            }*/
            if($item['date'] == $date){
                $amount += $item['amount'];
                $sum  += $item['amount'];
                $discount += $item['amount_discount'];
                $s_discount += $item['amount_discount'];
            }
        } else {
            /*foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                $name_user = ltrim($fee_deposits_value->description, " Collected By: ");
                $user1= trim($user);
                if ($fee_deposits_value->date == $date && $name_user == $user1) {

                    $amount += $fee_deposits_value->amount;
                    $discount += $fee_deposits_value->amount_discount;
                    $s_discount += $fee_deposits_value->amount_discount;
                    $sum += $fee_deposits_value->amount;
                }
            }*/
            $name_user= ltrim($item['description']," Collected By: ");
            $user1= trim($user);
            if($item['date'] == $date && $name_user == $user1){
                $amount += $item['amount'];
                $sum  += $item['amount'];
                $discount += $item['amount_discount'];
                $s_discount += $item['amount_discount'];
            }
        }

        ?>
        <td style="border:none">

        </td>
        <td>
            <?php echo $item['admission_no']; ?>
        </td>
        <td>
            <b><?php echo $item['firstname'] . ' ' . $item['lastname'] ?></b>
        </td>
        <td>
            <?php echo $item['type']; ?>
        </td>
       <!-- <td>
            <?php /*echo 'Arrear'; */?>
        </td>
        <td>
            <?php /*echo 'Rcvable'; */?>
        </td>-->
        <td>
            <?php echo $amount; ?>
        </td>
        <td>
            <?php echo $discount ; ?>
        </td>
       <!-- <td>
            <?php /*echo 'Balance'; */?>
        </td>-->
        </tbody>
        <?php
        }
        }
        }
        }
            ?>
        <tr style="color: ">
            <!--<th colspan="2" >
                <?php /*echo 'Student'; */?>
            </th>

            <th>
                <?php /*echo 'Name'; */?>
            </th>-->
            <th style="text-align: right"colspan="4">
                <?php echo 'Grand Total'; ?>
            </th>
           <!-- <th>
                <?php /*echo $s_arrear; */?>
            </th>
            <th>
                <?php /*echo $s_rcvable; */?>
            </th>-->
            <th>
                <?php echo $sum; ?>
            </th><th>
                <?php echo $s_discount; ?>
            </th>
            <!--<th>
                <?php /*echo $s_blnc; */?>
            </th>-->
        </tr>
    </table>
</div>
</body>
</html>