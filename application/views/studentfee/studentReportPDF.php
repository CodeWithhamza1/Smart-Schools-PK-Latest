<!doctype html>
<html>
<head>
    <title>Student Report</title>
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
            /*width: 10px;*/
            /*font-size: 12px;*/
        }

        table td{
            font-family: "Report Font", Arial, Helvetica, sans-serif !important;
           /* border: none;
            font-size: 10px;*/
padding: 5px;
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
    <div style="padding-left: 150px">
        <h4 style=" text-align: ;"><b><?php echo $school?>  </b></h4>
        <p style="font-size: 12px; text-align: center;margin-top: -20px"><?php echo $settinglist[0]['address'].".    Ph:".$settinglist[0]['phone']?></p>
    </div>
</div>
<?php
$count=0;
foreach ($student_due_fee as $key => $student) {
    $count++;
}
?>
<div class="dataTable_wrapper TABLE">
    <center>
    <h4 style="margin-top: -25px;"><b>Class:</b> <?php echo $class['class'].' - '.$section['section'];?></h4>
    </center>
    <center>
        <h4 style="margin-top: -15px;"><b>Strength:</b> <?php echo $count;?></h4>
    </center>
    <p style="font-size: 12px; position: absolute;right: 30px;margin-top: -38px;"><b>Printed on:</b> <?php echo date('d-M-Y h:i:sa') ?></p>
    <table style="margin-top: -20px;" class="table" id="dataTables-example" align="center">
        <thead>
        <tr style="background-color: #ddd;">
            <th style="width: 10px;">
                <?php echo 'Sr#' ?>
            </th>
            <th style="width: 10px;">
                <?php echo "Code" ?>
            </th>
            <th style="width: 150px;">
                <?php echo $this->lang->line('student'); ?>
            </th>
            <th style="width: 30px;"></th>
            <th style="width: 30px;"></th>
            <th style="width: 30px;"></th>
            <th style="width: 30px;"></th>
            <th style="width: 30px;"></th>
            <th style="width: 30px;"></th>
            <th style="width: 30px;"></th>
            <th style="width: 30px;"></th>
            <th style="width: 30px;"></th>

        </tr>
        </thead>
        <tbody>
        <?php
        $sr=1;
        foreach ($student_due_fee as $key => $student) {
        ?>
            <tr>
                <td><?php echo $sr++; ?></td>
                <td><?php echo $student->admission_no; ?></td>
                <td><?php echo $student->name; ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>

             </tr>
        <?php }?>

        </tbody>
    </table>
</div>
</body>
</html>