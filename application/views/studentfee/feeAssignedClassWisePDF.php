<!doctype html>
<html>
<head>
    <title>Fee Assigned Report </title>
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
            /*border: none;*/

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
        <h4 style="text-align: center"><b><b><u><?php echo "Fee Assigned Report"?></u></b></h4>
        <h4 style="text-align: center"><b><b><u><?php echo $class."-".$section?></u></b></h4>

    </div>
</div>

<div class="dataTable_wrapper TABLE">

    <table class="table" id="dataTables-example" align="center">
        <thead>
        <tr style="background-color: #ddd;">
            <th>
                <b><?php echo 'Code'; ?></b>
            </th>
            <th>
                <b><?php echo 'Name'; ?></b>
            </th>
            <th>
               <b><?php echo 'Fee Group'; ?></b>
            </th>

        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($report as $item){
         ?>
        <tr style="">
            <td>
                <b><?php echo $item->admission_no; ?></b>
            </td>
            <td>
                <b><?php echo $item->name; ?></b>
            </td>
            <td>
                <b><?php echo $item->student_fees_group; ?></b>
            </td>


        </tr>
        <?php
        }
        ?>
        </tbody>

    </table>
</div>
</body>
</html>