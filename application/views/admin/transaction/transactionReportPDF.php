<!DOCTYPE html>
<html>
<head>
    <title>Profit Loss Report</title>
    <style>
        @font-face {
            font-family: "Report Font";
            src: url("/assets/Fonts/WhitneyHTF-Book.otf");
        }
        table {
            font-family: "Report Font", Arial, Helvetica, sans-serif !important;
            border-collapse: collapse;
            width: 100%;
        }

        table td, table th {
            border: 1px solid #ddd;
            padding: 5px;
            font-size: 12px;
        }

        table td{
            font-family: "Report Font", Arial, Helvetica, sans-serif !important;
        }
        table tr:nth-child(even){background-color: #ddd;}

        table tr:hover {background-color: #ddd;}

        h1,h2,h3,h4,h5{
            font-family: "Report Font", Arial, Helvetica, sans-serif !important;
        }
        table th {
            font-family: "Report Font", Arial, Helvetica, sans-serif !important;
            padding-top: 6px;
            padding-bottom: 8px;
            text-align: left;
            background-color: #4CAF50;
            color: white;
        }
        footer { position: fixed; bottom: -45px; left: 0px; right: 0px; height: 50px; }
        p { page-break-after: always; }
        p:last-child { page-break-after: never; }
        @media print {
            #printpagebutton {
                display: none;
            }
        }
    </style>
</head>
<body>
<button id="printpagebutton" onclick="myFunction()">Print this page</button>
<h3 style="text-align: center;color: brown;"><b> Profit Loss Report </b></h3>
<h4 style="text-align: center;color: darkgreen"><b><?php echo date('F d, Y', strtotime($startDate)) . ' To '.  date('F d, Y', strtotime($endDate))?></b></h4>
<h4 style="text-align: center;color: darkgreen"><b><?php echo $school?></b></h4>
<div class="dataTable_wrapper TABLE">
    <table class="table" id="dataTables-example" align="center">
        <thead>
        <tr align="center" class="row">
            <th>Description</th>
            <th style="text-align: right">Debit</th>
            <th  style="text-align: right">Credit</th>
            <th  style="text-align: right">Balance</th>
        </tr>
        </thead>
        <tbody>
        <?php $totalFee=0;$room=1;  foreach($feeList as $list){
            $totalFee+=$list['amount'];
        } ?>
            <tr>
                <td style="text-align: left">School Fees</td>
                <td  style="text-align: right;" class="sale"> <?php  echo $totalFee ?></td>
                <td></td>
                <td></td>
            </tr>
        <?php $totalSalary=0;$room=1;  foreach($salary as $list){
            $totalSalary+=$list['net_salary'];
        } ?>
        <tr>
            <td style="text-align: left">Salary</td>
            <td></td>
            <td  style="text-align: right;" class="sale"> <?php  echo $totalSalary ?></td>
            <td></td>
        </tr>
        <?php $totalExpense=0;$room=1;  foreach($expenseList as $list){
            $totalExpense+=$list['amount'];
         } ?>
            <tr>
                <td style="text-align: left">Expense</td>
                <td></td>
                <td  style="text-align: right;" class="sale"> <?php  echo $totalExpense ?></td>
                <td></td>
            </tr>

        <?php $totalIncome=0;$room=1;  foreach($incomeList as $list){
            $totalIncome+=$list['amount'];} ?>
            <tr>
                <td style="text-align: left">Income</td>
                <td  style="text-align: right;" class="sale"> <?php  echo $totalIncome ?></td>
                <td></td>
                <td></td>
            </tr>
        <tr>
            <td style="text-align: right"><b>Total</b></td>
            <td  style="text-align: right;" class="sale"> <b><?php  echo $totalFee+$totalIncome ?></b></td>
            <td  style="text-align: right;" class="sale"> <b><?php  echo $totalSalary+$totalExpense ?></b></td>
            <td style="text-align: right;"> <b><?php  echo ($totalFee+$totalIncome) - ($totalSalary+$totalExpense) ?></b></td>
        </tr>
        </tbody>
    </table>
</div>
<script>
    function myFunction() {
        window.print();
    }
</script>
</body>
</html>
