<!doctype html>
<html>
<head>
    <title>New Admission Report</title>
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
            width: 100%;
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
    <div style="padding-left: 0px">
        <h4 style=" text-align: center">
            <u><b><?php echo $school?>  </b></u>
        </h4>
        <h4 style=" text-align: center">
            <u><b><?php echo 'New Admission Report'?>  </b></u>
        </h4>
        <h4 style="text-align: center">
            <span style="padding-right: 0px;"><b>Class:</b><b> <?php if($class_id=='ALL')
            {echo 'All Classes';}
            else{
                echo $class['class'];
            }?></span></b>
            <span style="padding-left: 40px"><b>Session:</b> <?php echo $session ?></span>
            <span style="padding-left: 40px"><b><?php echo date('F d, Y', strtotime($startDate)).' '.  'To'.' ' .date('F d, Y', strtotime($endDate))?></b></span>
        </h4>
    </div>
</div>
<div class="dataTable_wrapper TABLE">
    <!--<h4 style="margin-left: 31px;float: left"><b>Class:</b> <?php /*echo $class['class'].'-'.$section['section']*/?></h4>
    <h4 style="margin-left: 31px;float: left"><b>Session:</b> <?php /*echo $session */?></h4>-->
    <table class="table" id="dataTables-example" align="center">
        <thead>
        <tr style="background-color: #ddd;">
            <th>
                <?php echo 'Sr#' ?>
            </th>
            <th>
                <?php echo ('Admission No'); ?>
            </th>
            <th>
                Date
            </th>
            <th>
                <?php echo 'Student Name' ?>
            </th>
            <th>
                <?php echo 'Father Name' ?>
            </th>
            <?php if($class_id=='ALL'){?>

            <th>
                <?php echo 'Class' ?>
            </th><?php }?>
            <th>
                <?php echo 'Section' ?>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php
        $s = 0;$sr=1;
        foreach ($result as $key => $student) {
            ?>
            <tr>
                <td>
                    <?php echo $sr ?>
                </td>
                <td>
                    <?php echo $student['admission_no'] ?>
                </td>
                 <td>
                    <?php echo date('F d, Y', strtotime($student['admission_date']))  ?>
                </td>
                <td>
                    <?php echo $student['firstname'] . " " . $student['lastname']; ?>
                </td>
                <td>
                    <?php echo $student['father_name']; ?>
                </td>
                <?php if($class_id=='ALL'){?>
                <td>
                    <?php echo $student['class']; ?>
                </td>
                <?php }?>
                <td>
                    <?php echo $student['section']; ?>
                </td>
            </tr>
            <?php
            $sr++;
        }
        ?>

        </tbody>
    </table>
</div>
</body>
</html>