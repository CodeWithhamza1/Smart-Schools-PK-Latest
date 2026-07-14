<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pending Annual Charges Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .report-title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .report-subtitle {
            font-size: 16px;
            color: #666;
        }
        .summary-section {
            margin-bottom: 30px;
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        .summary-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .summary-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .detail-section {
            margin-bottom: 30px;
        }
        .detail-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            color: #333;
        }
        .detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .detail-table th, .detail-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .detail-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 12px;
        }
        .detail-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #666;
            padding: 20px;
        }
        .footer {
            text-align: right;
            margin-top: 30px;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .amount {
            text-align: right;
            font-weight: bold;
        }
        @media print {
            body { margin: 0; }
            .header { page-break-after: avoid; }
            .summary-section { page-break-inside: avoid; }
            .detail-table { page-break-inside: auto; }
            .detail-table tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="report-title">Pending Annual Charges Report</div>
        <div class="report-subtitle">For the month of: <?php echo $current_month; ?></div>
    </div>

    <div class="summary-section">
        <div class="summary-title">Summary</div>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Total Students with Pending Annual Charges</th>
                    <th>Total Pending Annual Charges Amount</th>
                    <th>Total Pending Annual Charges</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $summary['total_students']; ?></td>
                    <td class="amount"><?php echo $currency_symbol . number_format($summary['total_amount'], 2); ?></td>
                    <td><?php echo $summary['total_charges']; ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="detail-section">
        <div class="detail-title">Detailed Report</div>
        <?php if (!empty($pending_annual_charges)): ?>
            <table class="detail-table">
                <thead>
                    <tr>
                        <th>Admission No</th>
                        <th>Student Name</th>
                        <th>Class (Section)</th>
                        <th>Father Name</th>
                        <th>Fee Group</th>
                        <th>Amount (<?php echo $currency_symbol; ?>)</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_annual_charges as $charge): ?>
                        <tr>
                            <td><?php echo $charge->admission_no; ?></td>
                            <td><?php echo trim($charge->firstname . " " . $charge->middlename . " " . $charge->lastname); ?></td>
                            <td><?php echo $charge->class . " (" . $charge->section . ")"; ?></td>
                            <td><?php echo $charge->father_name; ?></td>
                            <td><?php echo $charge->fee_group_name; ?></td>
                            <td class="amount"><?php echo number_format($charge->amount, 2); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($charge->due_date)); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-data">
                No pending annual charges found for the current month.
            </div>
        <?php endif; ?>
    </div>

    <div class="footer">
        Report generated on: <?php echo date('d-m-Y H:i:s'); ?>
    </div>

    <script>
        // Auto-print when page loads
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 1000);
        };
    </script>
</body>
</html>
