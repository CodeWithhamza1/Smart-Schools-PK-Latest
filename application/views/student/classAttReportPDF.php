<!doctype html>
<html>
<head>
    <title>Student Attendance Report</title>
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

<button id="printpagebutton" onclick="myFunction()">Print this page</button>
<div class="container" style="display: inline-flex;text-align: center; ">
    <div style="padding-left: 20px"><img id="image" src="<?php echo base_url() ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>" alt="Trulli" width="85px" height="85px">
    </div>
    <div style="padding-left: 150px"> <h4 id="school" style=" text-align: center">
            <u><b><?php echo $school?>  </b></u>
        </h4>
        <h4 id="report">Student Attendance Report Class Wise</h4>
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
                <?php echo ('Class Name'); ?>
            </th>
            <th>
                <?php echo ('Section'); ?>
            </th>

            <th>
                <?php echo 'Boys' ?>
            </th>
            <th>
                <?php echo 'Girls' ?>
            </th>
            <th>
                <?php echo 'Total' ?>
            </th>
            <th>
                <?php echo 'Present Students' ?>
            </th>
            <th>
                <?php echo 'Absent Students' ?>
            </th>

        </tr>
        </thead>
        <tbody>
        <?php
        $s = 0;$sr=1;$total_std=0;$total_mstd=0;$total_fstd=0;$total_pstd=0;$total_astd=0;$total_capacity=0;$total_seats=0;
        foreach ($class as $key=>$classes) {
            $c_std = 0;
            $m_std = 0;
            $f_std = 0;
            $p_std = 0;
            $a_std = 0;
            $capacity = 40;
            $c_capacity = 0;
            $c_seats = 0;
            foreach ($result as $key => $student) {
                if($classes['class'] == $student->class ) {
                    $class_1 = "";
                    $section_1 = "";
                    ?>
                    <tr>
                        <td>
                            <?php echo $sr ?>
                        </td>
                        <td>
                            <?php echo $student->class ?>
                        </td>
                        <td>
                            <?php echo $student->section; ?>
                        </td>

                        <td>
                            <?php echo $student->m_std; ?>
                        </td
                        ><td>
                            <?php echo $student->f_std; ?>
                        </td>
                        <td>
                            <?php echo $student->cnt_sum; ?>
                        </td>
                        <td>
                            <?php echo $student->present_std; ?>
                        </td>
                        <td>
                            <?php echo ($student->cnt_sum-$student->present_std); ?>
                        </td>

                    </tr>
                    <?php
                    $sr++;
                    $class_1=$student->class;
                    $c_std+=$student->cnt_sum;
                    $m_std+=$student->m_std;
                    $f_std+=$student->f_std;
                    $p_std+=$student->present_std;
                    $a_std+=($student->cnt_sum-$student->present_std);
                    $c_capacity += $capacity;
                    $c_seats += ($capacity-$student->cnt_sum);
                    $total_std += $student->cnt_sum;
                    $total_mstd += $student->m_std;
                    $total_fstd += $student->f_std;
                    $total_pstd+=$student->present_std;
                    $total_astd+=($student->cnt_sum-$student->present_std);
                    $total_capacity += $capacity;
                    $total_seats += ($capacity-$student->cnt_sum);
                }
            }if($class_id=="ALL"){ if($classes['class'] == $class_1){?>
                <tr style="background-color: #ddd;">
                    <th>
                    </th>
                    <th>
                    </th>

                    <th>
                        <?php echo ('Total Strength'); ?>
                    </th>
                    <th>
                        <?php echo $m_std;?>
                    </th>
                    <th>
                        <?php echo $f_std;?>
                    </th>
                    <th>
                        <?php echo $c_std;?>
                    </th>
                    <th>
                        <?php echo $p_std;?>
                    </th>
                    <th>
                        <?php echo $a_std;?>
                    </th>
                </tr>
                <tr >
                    <td style="border-right: 1px solid white"></td>
                    <td style="border-right: 1px solid white"></td>
                    <td style="border-right: 1px solid white"></td>
                    <td style="border-right: 1px solid white"></td>
                    <td style="border-right: 1px solid white"></td>
                    <td style="border-right: 1px solid white"></td>
                    <td style="border-right: 1px solid white"></td>
                    <!--<td style="border-right: 1px solid white"></td>
                    <td style="border-right: 1px solid white"></td>-->
                    <td></td>
                </tr>
            <?php }}}
        //       if($class=="ALL"){
        ?>
        <tr style="background-color: #ddd;">
            <th>

            </th>

            <th>

            </th>
            <th>
                <?php echo ('Total Strength'); ?>
            </th>
            <th>
                <?php echo $total_mstd;?>
            </th>
            <th>
                <?php echo $total_fstd;?>
            </th>
            <th>
                <?php echo $total_std;?>
            </th>
            <th>
                <?php echo $total_pstd;?>
            </th>
            <th>
                <?php echo $total_astd;?>
            </th>
        </tr>
        <?php
        //         }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>