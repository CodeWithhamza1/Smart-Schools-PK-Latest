<?php
$students = isset($result) && is_array($result) ? $result : array();
$classlist = isset($class) && is_array($class) ? $class : array();
$school_name = !empty($school) ? $school : (isset($settinglist[0]['name']) ? $settinglist[0]['name'] : '');
$campus_name = isset($settinglist[0]['address']) ? $settinglist[0]['address'] : '';
$logo_file = isset($settinglist[0]['image']) ? $settinglist[0]['image'] : '';
$logo_src = base_url() . 'uploads/school_content/logo/' . $logo_file;

$ordered_students = array();
$seen = array();

foreach ($classlist as $class_row) {
    $class_name = isset($class_row['class']) ? $class_row['class'] : '';
    foreach ($students as $student) {
        if (isset($student->class) && $student->class == $class_name) {
            $key = $student->class . '|' . $student->section;
            $ordered_students[] = $student;
            $seen[$key] = true;
        }
    }
}

foreach ($students as $student) {
    $key = $student->class . '|' . $student->section;
    if (!isset($seen[$key])) {
        $ordered_students[] = $student;
    }
}

$total_boys = 0;
$total_girls = 0;
$total_students = 0;

foreach ($ordered_students as $student) {
    $total_boys += (int) $student->m_std;
    $total_girls += (int) $student->f_std;
    $total_students += (int) $student->cnt_sum;
}

function strength_percent($value, $total) {
    if ($total <= 0) {
        return 0;
    }

    return round(($value / $total) * 100);
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Class Strength Report</title>
    <style type="text/css">
        @font-face {
            font-family: "Report Font";
            src: url("/backend/Fonts/WhitneyHTF-Book.otf");
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #ffffff;
            color: #1f2630;
            font-family: "Report Font", Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .page {
            width: 430px;
            min-height: 100vh;
            margin: 0 auto;
            padding: 18px 22px 16px;
            background: #ffffff;
        }

        .school-header {
            text-align: center;
            padding-bottom: 16px;
            border-bottom: 1px solid #1f4574;
        }

        .logo-wrap {
            width: 42px;
            height: 42px;
            margin: 0 auto 8px;
            border-radius: 50%;
            background: #f5f7fb;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .logo-wrap img {
            max-width: 32px;
            max-height: 32px;
            display: block;
        }

        .school-name {
            margin: 0;
            color: #1f4574;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: .7px;
            line-height: 1.15;
            text-transform: uppercase;
        }

        .campus-name {
            margin-top: 2px;
            color: #202733;
            font-size: 12px;
            letter-spacing: 1.6px;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .report-title {
            margin: 22px 0 18px;
            padding: 8px 0;
            border-top: 1px solid #d5d8dd;
            border-bottom: 1px solid #d5d8dd;
            text-align: center;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: 24px;
        }

        .summary-box {
            min-height: 54px;
            padding: 10px 8px;
            border-radius: 7px;
            background: #fbfaf6;
            text-align: center;
        }

        .label {
            display: block;
            color: #2d333c;
            font-size: 9px;
            font-weight: 700;
            line-height: 1;
            text-transform: uppercase;
        }

        .value {
            display: block;
            margin-top: 4px;
            font-size: 19px;
            font-weight: 800;
            line-height: 1;
        }

        .boys {
            color: #1f4574;
        }

        .girls {
            color: #a4376a;
        }

        .total {
            color: #257840;
        }

        .class-card {
            overflow: hidden;
            margin-bottom: 16px;
            border: 1px solid #d8d2ca;
            border-radius: 7px;
            page-break-inside: avoid;
        }

        .class-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 34px;
            padding: 7px 12px 7px 14px;
            background: #1f4574;
            color: #ffffff;
        }

        .class-title {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: .2px;
            text-transform: uppercase;
        }

        .serial {
            min-width: 24px;
            padding: 2px 7px;
            border-radius: 9px;
            background: rgba(255, 255, 255, .16);
            font-size: 10px;
            line-height: 1.2;
            text-align: center;
        }

        .section {
            color: rgba(255, 255, 255, .78);
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .class-counts {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            border-bottom: 1px solid #d8d2ca;
        }

        .class-count {
            min-height: 58px;
            padding: 13px 14px 10px;
            border-right: 1px solid #d8d2ca;
        }

        .class-count:last-child {
            border-right: 0;
        }

        .class-count .value {
            margin-top: 7px;
            font-size: 16px;
        }

        .ratio-row {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 12px 13px;
            background: #fffefa;
        }

        .ratio-bar {
            position: relative;
            flex: 1;
            height: 6px;
            overflow: hidden;
            border-radius: 20px;
            background: #d8d8d5;
        }

        .ratio-boys,
        .ratio-girls {
            position: absolute;
            top: 0;
            bottom: 0;
        }

        .ratio-boys {
            left: 0;
            background: #1f4574;
        }

        .ratio-girls {
            right: 0;
            background: #a4376a;
        }

        .legend {
            display: flex;
            align-items: center;
            gap: 8px;
            white-space: nowrap;
            font-size: 9px;
            font-weight: 800;
        }

        .legend span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            display: inline-block;
        }

        .grand-total {
            margin: 18px 0 22px;
            padding: 14px 16px 16px;
            border: 1px solid #d8d2ca;
            border-radius: 8px;
            background: #fffefa;
            page-break-inside: avoid;
        }

        .grand-title {
            margin-bottom: 10px;
            color: #333840;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .8px;
            text-transform: uppercase;
        }

        .grand-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            text-align: center;
        }

        .footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 12px;
            padding-top: 16px;
            border-top: 1px solid #d5d8dd;
            color: #1f2630;
            font-size: 11px;
        }

        .print-button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-width: 78px;
            height: 34px;
            padding: 0 15px;
            border: 1px solid #c8ccd2;
            border-radius: 7px;
            background: #ffffff;
            color: #222831;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .empty-state {
            margin: 24px 0;
            padding: 24px 12px;
            border: 1px solid #d8d2ca;
            border-radius: 8px;
            background: #fffefa;
            text-align: center;
            font-weight: 700;
        }

        @page {
            margin: 10mm;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .page {
                width: auto;
                min-height: auto;
                padding: 0;
            }

            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
<main class="page">
    <header class="school-header">
        <div class="logo-wrap">
            <img src="<?php echo $logo_src; ?>" alt="<?php echo htmlspecialchars($school_name); ?>">
        </div>
        <h1 class="school-name"><?php echo htmlspecialchars($school_name); ?></h1>
        <?php if (!empty($campus_name)) { ?>
            <div class="campus-name"><?php echo htmlspecialchars($campus_name); ?></div>
        <?php } ?>
    </header>

    <div class="report-title">Class Strength Report</div>

    <section class="summary-grid">
        <div class="summary-box">
            <span class="label">Boys</span>
            <span class="value boys"><?php echo $total_boys; ?></span>
        </div>
        <div class="summary-box">
            <span class="label">Girls</span>
            <span class="value girls"><?php echo $total_girls; ?></span>
        </div>
        <div class="summary-box">
            <span class="label">Total</span>
            <span class="value total"><?php echo $total_students; ?></span>
        </div>
    </section>

    <?php if (empty($ordered_students)) { ?>
        <div class="empty-state">No class strength data found.</div>
    <?php } ?>

    <?php
    $serial = 1;
    foreach ($ordered_students as $student) {
        $boys = (int) $student->m_std;
        $girls = (int) $student->f_std;
        $total = (int) $student->cnt_sum;
        $boys_percent = strength_percent($boys, $total);
        $girls_percent = $total > 0 ? 100 - $boys_percent : 0;
        ?>
        <section class="class-card">
            <div class="class-card-header">
                <div class="class-title">
                    <span class="serial"><?php echo str_pad($serial, 2, '0', STR_PAD_LEFT); ?></span>
                    <span><?php echo htmlspecialchars($student->class); ?></span>
                </div>
                <div class="section">Section <?php echo htmlspecialchars($student->section); ?></div>
            </div>
            <div class="class-counts">
                <div class="class-count">
                    <span class="label">Boys</span>
                    <span class="value boys"><?php echo $boys; ?></span>
                </div>
                <div class="class-count">
                    <span class="label">Girls</span>
                    <span class="value girls"><?php echo $girls; ?></span>
                </div>
                <div class="class-count">
                    <span class="label">Total</span>
                    <span class="value total"><?php echo $total; ?></span>
                </div>
            </div>
            <div class="ratio-row">
                <div class="ratio-bar">
                    <div class="ratio-boys" style="width: <?php echo $boys_percent; ?>%;"></div>
                    <div class="ratio-girls" style="width: <?php echo $girls_percent; ?>%;"></div>
                </div>
                <div class="legend">
                    <span><i class="dot" style="background:#1f4574;"></i><?php echo $boys_percent; ?>%</span>
                    <span><i class="dot" style="background:#a4376a;"></i><?php echo $girls_percent; ?>%</span>
                </div>
            </div>
        </section>
        <?php
        $serial++;
    }
    ?>

    <section class="grand-total">
        <div class="grand-title">Grand Total - All Classes</div>
        <div class="grand-grid">
            <div>
                <span class="label">Boys</span>
                <span class="value boys"><?php echo $total_boys; ?></span>
            </div>
            <div>
                <span class="label">Girls</span>
                <span class="value girls"><?php echo $total_girls; ?></span>
            </div>
            <div>
                <span class="label">Total</span>
                <span class="value total"><?php echo $total_students; ?></span>
            </div>
        </div>
    </section>

    <footer class="footer">
        <div>Generated: <?php echo date('F j, Y'); ?></div>
        <button type="button" class="print-button" onclick="window.print()">
            <span aria-hidden="true">&#128424;</span>
            <span>Print</span>
        </button>
    </footer>
</main>
</body>
</html>
