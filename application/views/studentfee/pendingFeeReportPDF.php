<!doctype html>
<html>
<head>
    <title>Pending Fee Report</title>
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
            padding: 2px;
            font-size: 12px;
        }

        table td{
            font-family: "Report Font", Arial, Helvetica, sans-serif !important;
            border: none;
            font-size: 10px;

            /*border-bottom:  1px solid #060303;*/
            font-weight: bold;
        }table tr{
            border:  1px solid #060303;
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
                        echo 'Student Pending Fee for the month of :'.' '.$monthName.'-'.$year?></u></b></h4>
    </div>
</div>
<div class="dataTable_wrapper TABLE">
    <h4 style="margin-left: 31px;float: left"><b>Class:</b> <?php
        if($classID=='ALL'){
            echo 'All Classes';
        }
        else{
            echo $class['class'].'-'.$section['section'];}?></h4>
    <h4 style="margin-left: 31px;float: left"><b>Session:</b> <?php echo $session ?></h4>
	<?php if($classteacher_name != " "){ echo "<h4 style='margin-left: 31px;float: left'><b>Class teacher:</b> " . $classteacher_name ."</h4>"; } ?>
	<h4 style="margin-right: 31px;float: right"><?php echo "Print date : ". date("h:i:s a  d/m/Y "); ?></h4>
    <table class="table" id="dataTables-example" align="center">
        <thead>
        <tr style="background-color: #ddd;">
            <th>
                <?php echo 'Sr#' ?>
            </th>
            <th>
                <?php echo $this->lang->line('admission_no'); ?>
            </th>
            <th>
                <?php echo $this->lang->line('student'); ?>
            </th>
            <th>
                <?php echo $this->lang->line('father_name'); ?>
            </th>
            <th>
                <?php echo 'Father Phone'; ?>
            </th>
            <th>
                <?php echo 'Arrear' ?>
            </th>
            <th >
                <?php echo 'Current' ?>

            </th>
            <th>
                <?php echo 'Balance' ?>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php
        $current=0;$arrears=0;$fine=0;$total=0;$sr=1;$balance=0;
        foreach ($student_due_fee as $key => $student) {
            if($student->deposit==0){
                if($student->previous>0||$student->totalfee>0){
                    $totalfee=$student->totalfee-($student->discount+$student->deposit);
                    if($totalfee>0 ||$student->previous>0){
                    ?>
                    <tr>
                        <td><?php echo $sr++; ?></td>
                        <td><?php echo $student->admission_no; ?></td>
                        <td><?php echo $student->name; ?></td>
                        <td><?php echo $student->father_name; ?></td>
                        <td><?php echo $student->guardian_phone; ?></td>
                        <td><?php echo number_format($student->previous) ?></td>
                        <td><?php echo number_format($totalfee) ?></td>
                        <td><?php echo number_format(($totalfee+$student->previous)) ?></td>
                    </tr>
                    <?php
                    $current += $totalfee;
                    $arrears +=$student->previous;
                } }}
            if($student->deposit>0){
                $totalfee=$student->totalfee-($student->discount+$student->deposit);
                if($totalfee>0 ||$student->previous>0){
                ?>
                <tr>
                    <td><?php echo $sr++; ?></td>
                    <td><?php echo $student->admission_no; ?></td>
                    <td><?php echo $student->name; ?></td>
                    <td><?php echo $student->father_name; ?></td>
                    <td><?php echo $student->guardian_phone; ?></td>
                    <td><?php echo number_format($student->previous) ?></td>
                    <td><?php echo number_format($totalfee) ?></td>
                    <td><?php echo number_format(($totalfee+$student->previous)) ?></td>
                </tr>
                <?php
                $current += $totalfee;
                $arrears +=$student->previous;
                 }} }?>
        <tr style="background-color: #ddd;">
            <td></td>
            <td></td>
            <td><b>Students: <?php echo $sr-1;?></b></td>
            <td></td>
            <td style="text-align: right">Total</td>
            <td><b><?php echo number_format($arrears);?></b></td>
            <td><b><?php echo number_format($current);?></b></td>
            <td><b><?php echo number_format($current+$arrears);?></b></td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>