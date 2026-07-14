<!doctype html>
<html>
<head>
    <title>Class Result Card</title>
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
    <div style="padding-left: 100px">
        <h4 style=" text-align: center">
            <u><b><?php echo $school?>  </b></u>
        </h4>
        <h4 style="text-align: center">
                <span style="padding-right: 40px;"><b>Class:</b><b> <?php echo $class['class'].'-'.$section['section']?></span></b>
            <b><?php echo '('.$exam['name'].')'?></b>
            <span style="padding-left: 40px"><b>Session:</b> <?php echo $session ?></span>
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
                <?php echo ('FeeCode'); ?>
            </th>
            <!--<th>
                <?php /*echo 'Role#' */?>
            </th>-->
            <th>
                <?php echo $this->lang->line('student'); ?>
            </th>
            <?php
            if($parameter == 'nonempty') {
                $s = 0;
                if ($examSchedule['status'] == "yes") {
                    foreach ($examSchedule['result'] as $key => $st) {
                        if ($s == 0) {
                            foreach ($st['exam_array'] as $key => $exam_schedule) {
                                ?>
                                <th>
                                    <?php
                                    echo $exam_schedule['exam_name'];
                                    ?>
                                </th>
                                <?php
                            }
                        }
                        $s++;
                    }
                } else {
                    ?>

                    <?php
                }?>
                <th><?php echo $this->lang->line('grand_total'); ?></th>
                <th><?php echo $this->lang->line('percent') . ' (%)'; ?></th>
            <?php }
            else{
                for($i=0;$i<=9;$i++){
                    ?>
                    <th style="width: 10%">

                    </th>
            <?php     }?>
            <?php }
            ?>

            <!-- <th><?php /*echo 'Grade'; */?></th>
            <th><?php /*echo $this->lang->line('result'); */?></th>-->
        </tr>
        </thead>
        <tbody>
        <?php
        $s = 0;$sr=1;
        foreach ($examSchedule['result'] as $key => $student) {
            ?>
            <input type="hidden" name="student[]" value="<?php echo $student['student_id'] ?>">
            <?php
            if (!empty($student['exam_array'])) {
                if ($s == 0) {
                    foreach ($student['exam_array'] as $key => $exam_schedule) {
                        ?>
                        <input type="hidden" name="exam_schedule[]" value="<?php echo $exam_schedule['exam_schedule_id'] ?>">
                        <?php
                    }
                }
            } else {
                ?>

                <?php
            }
            $s++;
        }
        ?>
        <?php
        foreach ($examSchedule['result'] as $key => $student) {
            $total_marks = 0;
            $obtain_marks = "0";
            $result = "Pass";
            ?>
            <tr>
                <td>
                    <?php echo $sr ?>
                </td>
                <td>
                    <?php echo $student['admission_no'] ?>
                </td>
                <!-- <td>
                    <?php /*echo $student['roll_no'] */?>
                </td>-->
                <td>
                    <?php echo $student['firstname'] . " " . $student['lastname']; ?>
                </td>
                <?php
                if (!empty($student['exam_array'])) {
                    count($student['exam_array']);
                    $s = 0;
                    foreach ($student['exam_array'] as $key => $exam_schedule) {
                        $total_marks = (int) $total_marks + (int) $exam_schedule['full_marks'];
                        ?>
                        <td>
                        </td>
                        <?php
                    }
                    if ($s == count($student['exam_array'])) {
                        $obtain_marks = 0;
                    }
                    ?>
                    <td> <?php /*echo $obtain_marks . " /" . $total_marks; */?> </td>
                    <td><!-- --><?php
/*                        $per = $obtain_marks * 100 / $total_marks;
                        echo number_format($per, 2, '.', '');
                        */?>

                    </td>
                    <!-- <td style="text-align: center">
                        <?php
                    /*                        $per = ($obtain_marks  / $total_marks)*100;
                                            $per1 =number_format($per, 2, '.', '');
                                            if($per1 >= 91) {
                                                echo 'A+';
                                            }
                                            else if($per1>=81 && $per1<91){
                                                echo 'A';
                                            }
                                            else if($per1>=71 && $per1<81){
                                                echo 'B';
                                            }
                                            else if($per1>=61 && $per1<71){
                                                echo 'C';
                                            }
                                            else if($per1>=50 && $per1<61){
                                                echo 'D';
                                            }
                                            else{
                                                echo 'F';
                                            }
                                            */?>
                    </td>
                    <th>
                        <?php
                    /*                        if ($result == "Pass") {

                                                echo "<label class='label label-success'>";
                                            }
                                            else {

                                                echo "<label class='label label-danger'>";
                                            }
                                            echo $result;
                                            echo "<label/>";
                                            */?>
                    </th>-->
                    <?php
                } else {
                    ?>

                    <?php
                }
                ?>

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