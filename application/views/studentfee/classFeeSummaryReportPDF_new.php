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
            /*background-color: #eeeaee;*/
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
    </style>
</head>
<body >
<div class="container" style="display: inline-flex;text-align: center; ">
    <div style="padding-left: 20px"><img src="<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>" alt="Trulli" width="85px" height="85px">
    </div>
    <div style="padding-left: 150px"> <h4 style=" text-align: center">
            <u><b><?php echo $school?>  </b></u>
        </h4>
        <h4 style="text-align: center"><b><b><u><?php
                        $dateObj   = DateTime::createFromFormat('!m', $month);
                        $monthName = $dateObj->format('F');
                        echo 'Fee Summary for the month of :'.' '.$monthName.'-'.$year?></u></b></h4>
    </div>
</div>
<div class="dataTable_wrapper TABLE">
    <table class="table" id="dataTables-example" align="center">
        <thead>
        <tr style="background-color: #ddd;">
            <th>
                <?php echo $this->lang->line('class'); ?>
            </th>
            <th>
                <?php echo 'Current' ?>
            </th>
            <th>
                <?php echo ('Arrears'); ?>
            </th>
           <!-- <th>
                <?php /*echo ('Previous month recive'); */?>
            </th><th>
                <?php /*echo ('Previous month fee'); */?>
            </th>-->
            <th>
                <?php echo ('Fine'); ?>
            </th>
            <th>
                <?php echo 'Total'; ?>
            </th>
            <th>
                <?php echo 'Received' ?>
            </th>
            <th>
                <?php echo 'Discounted' ?>
            </th>
            <th>
                <?php echo 'Balance' ?>
            </th>
            <th>
                <?php echo 'New Admission' ?>
            </th>
        </tr>
        </thead>
        <tbody>

        <?php $flag=true;$grd_current=0;$grd_arrears=0;$grd_discount=0;$grd_fine=0;$grd_total=0;$grd_received=0;$grd_balance=0;$grd_previous_received=0;$grd_previous_fee1=0;$sum=0;
        foreach ($class as $key=>$classes) {
            $current=0;$discount=0;$arrears=0;$fine=0;$total=0;$received=0;$balance=0;$previous_received=0;$previous_fine=0;$previous_balance=0;$previous_fee1=0;
            foreach ($student_due_fee as $key => $student) {
                if($classes['class']== $student->class){
                    $current += $student->totalfee;
                    $arrears +=$student->previous;
                    $fine +=$student->fine;
                    $received +=$student->deposit;
                    $discount +=$student->discount;
                    $previous_received +=$student->previous_deposit;
                    $previous_fee1 +=$student->previous_monthfee;
                    $previous_fine +=$student->previous_fine;
                    $previous_balance +=$student->previous_balance;
                }
            }
            $amount=0;$count=0;
            foreach ($newAdmission as $item) {
                if ($classes['id'] == $item->class_id) {
                    $fee_deposits = json_decode(($item->amount_detail));
                    foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                        $y = date('Y', strtotime($fee_deposits_value->date));
                        $m = date('m', strtotime($fee_deposits_value->date));
                        if ($year == $y && $month==$m) {
                            $amount += $fee_deposits_value->amount;
                        }
                    }
                    $count++;
                }
            }
            ?>
            <tr>
                <td><?php echo $classes['class'] ?></td>
                <td><?php echo number_format($current) ?></td>
                <td><?php echo number_format(/*((*/$arrears/*+$previous_fine)-$previous_received)*/) ?></td>
               <!-- <td><?php /*echo number_format($previous_received) */?></td>
                <td><?php /*echo number_format($previous_fee1) */?></td>-->
                <td><?php echo number_format($fine) ?></td>
                <td><?php echo number_format($current+$arrears+$fine) ?></td>
                <td><?php echo number_format($received) ?></td>
                <td><?php echo number_format($discount) ?></td>
                <td><?php echo number_format((($current+/*((*/$arrears/*+$previous_fine)-$previous_received)*/+$fine)-$received)) ?></td>
                <td><?php echo number_format($amount).'('.$count.')' ?></td>
            </tr>
            <?php
            $grd_discount+=$discount;
            $grd_current += $current;$grd_previous_received += $previous_received;$grd_previous_fee1 += $previous_fee1;$grd_arrears+=(($arrears/*+$previous_fine)-$previous_received*/));$grd_fine+=$fine;
            $grd_total+=($current+(($arrears/*+$previous_fine)-$previous_received*/))+$fine);$grd_received+=$received;
            $grd_balance+=($current+(($arrears/*+$previous_fine)-$previous_received*/))+$fine)-$received-$discount;
            $sum+=$amount;

        }
        ?>
        <tr style="background-color: #ddd;">
            <td><b>Grand Total</b></td>
            <td><b><?php echo number_format($grd_current);?></b></td>
            <td><b><?php echo number_format($grd_arrears);?></b></td>
            <!--<td><b><?php /*echo number_format($grd_previous_received);*/?></b></td>
            <td><b><?php /*echo number_format($grd_previous_fee1);*/?></b></td>-->
            <td><b><?php echo number_format($grd_fine);?></b></td>
            <td><b><?php echo number_format($grd_total);?></b></td>
            <td><b><?php echo number_format($grd_received);?></b></td>
            <td><b><?php echo number_format($grd_discount);?></b></td>
            <td><b><?php echo number_format($grd_balance);?></b></td>
            <td><b><?php echo number_format($sum);?></b></td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>