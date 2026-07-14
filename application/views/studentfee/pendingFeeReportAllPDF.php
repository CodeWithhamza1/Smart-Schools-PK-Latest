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
    <table class="table" id="dataTables-example" align="center">
        <thead>
        <tr style="background-color: #ddd;">
            <th>
                <?php echo 'Sr#' ?>
            </th>
            <th>
                <?php echo $this->lang->line('admission_no'); ?>
            </th><!--<th>
                <?php /*echo $this->lang->line('class'); */?>
            </th><th>
                <?php /*echo $this->lang->line('section'); */?>
            </th>-->
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

        $fcurrent=0;$farrears=0;$ffine=0;$ftotal=0;$fsr=1;$fbalance=0;
        foreach ($class as $key=>$classes) {
            foreach ($section as $key => $sections) {

//            foreach ($class_section as $key => $class_sections) {
//        if($classes['id'] == $class_sections['class_id'] && $sections['id'] == $class_sections['section_id']){
                $class_1="";$section_1="";            $current=0;$arrears=0;$fine=0;$total=0;$sr=1;$balance=0;

                foreach ($student_due_fee as $key => $student) {
                    if ($classes['class'] == $student->class && $sections['section'] == $student->section) {

                        if($student->deposit==0){
                            $totalfee=$student->totalfee-($student->discount+$student->deposit);
                            if($student->previous>0 || $totalfee >0){
                                if($class_1!=$student->class&&$section_1!=$student->section){
                                    $class_1=$student->class;$section_1=$student->section;
                                    ?>
                                    <tr style="background-color: #ddd;text-align: center">
                                        <th colspan="8"style="text-align: center"><span style="font-weight: 800">Class :</span><?php echo  $classes['class'] ." - ".$sections['section']; ?></span></th>
                                    </tr>
                                    <?php
                                }

                                ?>
                                <tr>
                                    <td><?php echo $sr++; ?></td>
                                    <td><?php echo $student->admission_no; ?></td>
                                    <!-- <td><?php /*echo $classes['class']; */?></td>
            <td><?php /*echo  $sections['section']; */?></td>-->
                                    <td><?php echo $student->name; ?></td>
                                    <td><?php echo $student->father_name; ?></td>
                                    <td><?php echo $student->guardian_phone; ?></td>
                                    <td><?php echo number_format($student->previous) ?></td>
                                    <td><?php echo number_format($totalfee) ?></td>
                                    <td><?php echo number_format(($totalfee+$student->previous)) ?></td>
                                </tr>
                                <?php
//            $current += $student->totalfee;
                                $class_2=$student->class;$section_2=$student->section;
                                $current += $totalfee;
                                $arrears +=$student->previous;
                                $fcurrent +=$totalfee;
                                $farrears +=$student->previous;
                                $fsr ++;
                            }
                        }
                        if($student->deposit>0){
                            $totalfee=$student->totalfee-($student->discount+$student->deposit);
                            if($student->previous >0 ||$totalfee >0){
                                if($class_1!=$student->class&&$section_1!=$student->section){
                                    $class_1=$student->class;$section_1=$student->section;
                                    ?>
                                    <tr style="background-color: #ddd;text-align: center">
                                        <th colspan="8"style="text-align: center"><?php echo "Class :". $classes['class'] ?><span style="margin-left: 9px"><?php echo "Section :".$sections['section']; ?></span></th>
                                    </tr>
                                <?php }?>

                                <tr>
                                    <td><?php echo $sr++; ?></td>
                                    <td><?php echo $student->admission_no; ?></td>
                                    <!--<td><?php /*echo $classes['class']; */?></td>
            <td><?php /*echo  $sections['section']; */?></td>-->
                                    <td><?php echo $student->name; ?></td>
                                    <td><?php echo $student->father_name; ?></td>
                                    <td><?php echo $student->guardian_phone; ?></td>
                                    <td><?php echo number_format($student->previous) ?></td>
                                    <td><?php echo number_format($totalfee) ?></td>
                                    <td><?php echo number_format(($totalfee+$student->previous)) ?></td>
                                </tr>
                                <?php
                                $class_2=$student->class;$section_2=$student->section;
                                $current += $totalfee;
                                $arrears +=$student->previous;
                                $fcurrent += $totalfee;
                                $farrears +=$student->previous;
                                $fsr ++;

                            } }} }?>
                <?php if($class_1==$class_2&&$section_1==$section_2){
                    if($arrears>0||$current>0){

                        ?>
                        <tr style="background-color: #ddd;">
                            <!-- <td></td>
                             <td></td>-->

                            <td colspan="3" style="text-align: center"><b>Students: <?php echo $sr-1;?></b></td>

                            <td colspan="2" style="text-align: center">Total</td>
                            <td><b><?php echo number_format($arrears);?></b></td>
                            <td><b><?php echo number_format($current);?></b></td>
                            <td><b><?php echo number_format($current+$arrears);?></b></td>
                        </tr>
                        <tr style="">
                            <th colspan="8"style="text-align: center"></th>
                        </tr>

                        <?php
//            }
//        }
                    }
                }}
        }?>
        <tr style="background-color: #ddd;">

            <td></td>
            <td></td>
            <td><b>Students: <?php echo $fsr-1;?></b></td>
            <td></td>
            <td style="text-align: right">Total</td>
            <td><b><?php echo number_format($farrears);?></b></td>
            <td><b><?php echo number_format($fcurrent);?></b></td>
            <td><b><?php echo number_format($fcurrent+$farrears);?></b></td>
        </tr>
        </tbody>
    </table>
</div>
</body>
</html>
