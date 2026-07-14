<!doctype html>
<html>
<head>
    <title>Fee Summary Report Date Wise</title>
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
            font-size: 10px;
            border: none;

        }table tr {
            border: 1px solid #060303;

        }

        table td{
            font-family: "Report Font", Arial, Helvetica, sans-serif !important;
            font-weight: 100;
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
            font-weight: 900 !important;
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
            <u><b><?php echo $school?>  </b></u>
        </h4>
        <h4 style="text-align: center"><b><b><u><?php echo "Date Wise Fee Received"/*. ' '. date('d-m-Y', strtotime($start_date)). '-'. date('d-m-Y', strtotime($end_date))*/?></u></b></h4>
        <?php if($user!='all'){?>
            <h4 style="text-align: center"><b><b><u><?php echo "Collected By". ' '. $user?></u></b></h4>
        <?php }
        ?>
        <?php $sum=0;
        foreach ($report as $item) {
            $date=date('d-M-y', strtotime($item['date']));
//            $fee_deposits = json_decode(($item->amount_detail));

            if($user =='all') {
                /*foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                    $sum += $fee_deposits_value->amount;
                }*/
                $sum  += $item['amount'];

            }else{
                /*foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                    $name_user= ltrim($fee_deposits_value->description," Collected By: ");
                    $user1= trim($user);
                    if ($name_user == $user1) {
                        $sum += $fee_deposits_value->amount;
                    }
                }*/
                $name_user= ltrim($item['description']," Collected By: ");
                $user1= trim($user);
                if ($name_user == $user1) {
                    $sum += $item['amount'];
                }

            }}
        ?>
        <p><span class="print_date" style=" margin-top: -35px !important;">Printed on: <?php echo date('d-M-y h:i:s A');?></span></p>

        <h4 style="text-align: left;margin-bottom: 0px;"><span style="margin-left: -250px;"><b><u><?php echo "Grand Total". ': '.$sum ?></u></b></span></h4>

    </div>
</div>

<div class="dataTable_wrapper TABLE">

    <table class="table" id="dataTables-example" align="" style="width: 50%;">
        <thead>
        <tr style="background-color: #ddd;">
            <th>
                <b><?php echo 'Date'; ?></b>
            </th>
            <th>
                <b><?php echo 'Dues Type'; ?></b>
            </th>
            <th>
               <b><?php echo 'Amount'; ?></b>
            </th>

        </tr>
        </thead>
        <tbody>
        <?php
        $s = 0;$sr=1;$sum=0;
        foreach ($dates as $key =>$item2) {
            $date_sum=0;
        $date1 = date('d-M-y', strtotime($item2->created_at));
        $date = date('Y-m-d', strtotime($item2->created_at));
        foreach ($type as $key =>$types) {
            $amount1=0;
            foreach ($report as $item) {
                if($types['id'] == $item['feetype_id']){
                   /* $fee_deposits = json_decode(($item->amount_detail));
                    foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                            if ($fee_deposits_value->date == $date) {
                        $amount1 += $fee_deposits_value->amount;
                            }
                    }*/
                    if($item['date'] == $date){
                        $amount1 += $item['amount'];

                    }
                }
            }
            if($amount1>0){
                ?>
                <tr>
                    <td><?php echo $date1?></td>
                    <td><?php echo $types['type']?></td>
                    <?php  $amount=0;
                    foreach ($report as $item) {
                        if($types['id'] == $item['feetype_id']){
//                            $date = date('d-M-y', strtotime($item->created_at));

//                            $fee_deposits = json_decode(($item->amount_detail));
//                print_r($fee_deposits);

                            if($user =='all') {
                                /*foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                        if ($fee_deposits_value->date == $date) {
                                    $amount += $fee_deposits_value->amount;
                                    $sum += $fee_deposits_value->amount;
                                    $date_sum += $fee_deposits_value->amount;
                                        }
                                }*/
                                if($item['date'] == $date){
                                    $amount += $item['amount'];
                                    $sum  += $item['amount'];
                                    $date_sum += $item['amount'];
                                }
                            }else{
                                /*foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                    $name_user= ltrim($fee_deposits_value->description," Collected By:");
                                    $user1= trim($user);
//                                    $name_user= "Super Admin";
//                                    echo$name_user;
                                    if ($fee_deposits_value->date == $date && $name_user == $user1) {

                                        $amount += $fee_deposits_value->amount;
                                        $sum += $fee_deposits_value->amount;
                                        $date_sum += $fee_deposits_value->amount;
                                    }
                                }*/
                                $name_user= ltrim($item['description']," Collected By: ");
                                $user1= trim($user);
                                if($item['date'] == $date && $name_user == $user1){
                                    $amount += $item['amount'];
                                    $sum  += $item['amount'];
                                    $date_sum += $item['amount'];
                                }
                            }

                        }
                    }
                    ?>

<!--                    <td>--><?php //echo $date;?><!--</td>-->
                    <!--                    <td>--><?php //echo $item->type;?><!--</td>-->
                    <td><?php echo $amount;?></td>
                </tr>
            <?php } } if($date_sum>0){?>
            <tr>
                <td colspan="2" style="text-align: right"><b> Date Total</b></td>
                <td><b><?php echo $date_sum;?></b></td>
            </tr>
        <?php }}?>


        <!--<tr>
            <td colspan="2" style="text-align: right">  Total</td>
            <td><?php /*echo $sum;*/?></td>
        </tr>-->
        </tbody>

    </table>
</div>
</body>
</html>