<!doctype html>
<html>
<head>
    <title>Fee Summary Report</title>
    <!-- Latest compiled and minified CSS -->
<!--    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">-->
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
                <?php echo 'Dues Type'; ?>
            </th>
            <th>
                <?php echo 'Receive'; ?>
            </th>
            <th>
                <?php echo 'Advance'; ?>
            </th>
            <th>
                <?php echo 'Total Amount'; ?>
            </th>
        </tr>
        </thead>
        <?php if($criteria=='All'){?>
            <tbody>
            <?php
            $s = 0;$sr=1;$sum=0;

            foreach ($type as $key =>$types) {
                $amount1=0;
            foreach ($report as $key => $item) {

                if($types['id'] == $item['feetype_id']){
                    /*$fee_deposits = ($item->amount_detail);
                    foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                        if ($fee_deposits_value->date == $date) {
                            $amount1 += $fee_deposits_value->amount;
                        }
                    }*/
                if($item['date'] == $date){
                    $amount1 += $item['amount'];}
                }}
//                echo $amount1;
                if($amount1>0){
                ?>
                <tr>
                    <td><?php echo $types['type']?></td>
                    <?php  $amount=0;
                    foreach ($report as $item) {
                        if($types['id'] == $item['feetype_id']){
//                            $fee_deposits = json_decode(($item->amount_detail));
//                print_r($fee_deposits);

                            if($user =='all') {
                                /*foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                    if ($fee_deposits_value->date == $date) {
                                        $amount += $fee_deposits_value->amount;
                                        $sum += $fee_deposits_value->amount;
                                    }
                                }*/
                                if($item['date'] == $date){
                                    $amount += $item['amount'];
                                    $sum  += $item['amount'];
                                }
                            }else{
                                /*foreach ($fee_deposits as $fee_deposits_key => $fee_deposits_value) {
                                    $name_user= ltrim($fee_deposits_value->description," Collected By: ");
                                    $user1= trim($user);
                                    if ($fee_deposits_value->date == $date && $name_user == $user1) {

                                        $amount += $fee_deposits_value->amount;
                                        $sum += $fee_deposits_value->amount;
                                    }
                                }*/
                                $name_user= ltrim($item['description']," Collected By: ");
                                $user1= trim($user);
                                if($item['date'] == $date && $name_user == $user1){
                                    $amount += $item['amount'];
                                    $sum  += $item['amount'];
                                }
                            }

                        }
                    }
                    ?>
                    <td>
                        <?php echo $amount ?>
                    </td>
                    <td>0
                    </td>
                    <td> <?php echo $amount ?>
                    </td>
                </tr>
                <?php
            }}
            ?>

            <tr>
                <td>Total</td>
                <td><?php echo $sum;?></td>
                <td>0</td>
                <td><?php echo $sum;?></td>
            </tr>
            </tbody>
        <?php }?>
    </table>
</div>
</body>
</html>