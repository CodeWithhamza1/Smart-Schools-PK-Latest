<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Chart of Accounts</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 13px; color: #222; background: #fff; }

        .page-wrapper { max-width: 900px; margin: 0 auto; padding: 30px 20px; }

        .report-header { text-align: center; margin-bottom: 24px; border-bottom: 2px solid #333; padding-bottom: 14px; }
        .report-header h2 { font-size: 20px; font-weight: bold; letter-spacing: 1px; text-transform: uppercase; }
        .report-header p { font-size: 12px; color: #555; margin-top: 4px; }

        .print-btn { display: inline-block; margin-bottom: 20px; padding: 7px 18px; background: #3c8dbc; color: #fff; border: none; border-radius: 3px; cursor: pointer; font-size: 13px; text-decoration: none; }
        .print-btn:hover { background: #2a6e99; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        th, td { padding: 7px 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #f0f0f0; font-weight: bold; font-size: 12px; }

        .type-header td {
            background: #2c3e50;
            color: #fff;
            font-weight: bold;
            font-size: 13px;
            padding: 8px 10px;
        }

        .group-header td {
            background: #dce9f5;
            color: #1a3a55;
            font-weight: bold;
            font-size: 12px;
            padding: 6px 10px 6px 22px;
        }

        .account-row td { background: #fff; }
        .account-row:nth-child(even) td { background: #f9f9f9; }

        .col-code { width: 22%; }
        .col-name { width: 78%; }

        .page-footer { margin-top: 24px; text-align: right; font-size: 11px; color: #888; border-top: 1px solid #ddd; padding-top: 8px; }

        @media print {
            .print-btn { display: none !important; }
            body { font-size: 12px; }
            .page-wrapper { padding: 10px; }
        }
    </style>
</head>
<body>
<div class="page-wrapper">

    <button class="print-btn" onclick="window.print()">&#128438; Print</button>

    <div class="report-header">
        <h2>Chart of Accounts</h2>
        <p>Printed on: <?php echo date('d M Y, h:i A'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-code">Code</th>
                <th class="col-name">Nominal Account</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $grand_total = 0;
        foreach ($grouped as $type => $groups):
            $type_count = 0;
            foreach ($groups as $accounts) $type_count += count($accounts);
            $grand_total += $type_count;
        ?>
            <tr class="type-header">
                <td colspan="2"><?php echo htmlspecialchars($type); ?> (<?php echo $type_count; ?>)</td>
            </tr>
            <?php foreach ($groups as $group => $accounts): ?>
            <tr class="group-header">
                <td colspan="2">&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($group); ?> (<?php echo count($accounts); ?>)</td>
            </tr>
            <?php foreach ($accounts as $account): ?>
            <tr class="account-row">
                <td class="col-code"><?php echo htmlspecialchars($account['account_number']); ?></td>
                <td class="col-name"><?php echo htmlspecialchars($account['account_name']); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <div class="page-footer">
        Total Accounts: <?php echo $grand_total; ?>
    </div>

</div>
</body>
</html>
