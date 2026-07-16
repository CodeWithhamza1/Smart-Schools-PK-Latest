<style>
    /* Modern Dashboard Styles - Premium & Attractive */
    :root {
        --glass-surface: rgba(255, 255, 255, 0.16);
        --glass-surface-strong: rgba(255, 255, 255, 0.24);
        --glass-border: rgba(255, 255, 255, 0.28);
        --glass-shadow: 0 12px 30px rgba(15, 23, 42, 0.14);
        --glass-shadow-hover: 0 18px 40px rgba(15, 23, 42, 0.2);
    }

    .content-wrapper {
        background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
        min-height: 100vh !important;
    }

    .content {
        padding: 20px;
    }

    /* Modern Widget Cards */
    .small-box {
        --mx: 50%;
        --my: 50%;
        border-radius: 15px;
        box-shadow: var(--glass-shadow);
        border: 1px solid var(--glass-border);
        position: relative;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        background-color: var(--glass-surface);
        backdrop-filter: blur(18px) saturate(140%);
        -webkit-backdrop-filter: blur(18px) saturate(140%);
        margin-bottom: 25px;
        isolation: isolate;
    }

    .small-box:hover {
        transform: translateY(-8px);
        box-shadow: var(--glass-shadow-hover);
    }

    .small-box::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 5px;
        background: linear-gradient(90deg, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0.1) 100%);
    }

    .small-box::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(220px circle at var(--mx) var(--my), rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0) 60%);
        opacity: 0;
        transition: opacity 0.35s ease;
        pointer-events: none;
        z-index: 0;
    }

    .small-box:hover::after {
        opacity: 1;
    }

    .small-box .inner {
        padding: 25px 20px;
        position: relative;
        z-index: 2;
    }

    .small-box .inner h3 {
        font-size: 36px;
        font-weight: 700;
        margin: 0 0 10px;
        color: #fff;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .small-box .inner p {
        font-size: 15px;
        margin: 0;
        color: rgba(255, 255, 255, 0.95);
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .small-box .icon {
        position: absolute;
        top: 50%;
        right: 20px;
        transform: translateY(-50%);
        font-size: 80px;
        color: rgba(255, 255, 255, 0.18);
        background: rgba(255, 255, 255, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 18px;
        width: 92px;
        height: 92px;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1;
        transition: all 0.3s ease;
    }

    .small-box:hover .icon {
        font-size: 90px;
        color: rgba(255, 255, 255, 0.25);
        transform: translateY(-50%) scale(1.04);
    }

    .small-box-footer {
        display: block;
        padding: 12px 20px;
        text-decoration: none;
        color: rgba(255, 255, 255, 0.9);
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-top: 1px solid rgba(255, 255, 255, 0.12);
        text-align: center;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 14px;
        letter-spacing: 0.3px;
    }

    .small-box-footer:hover {
        background: rgba(255, 255, 255, 0.16);
        color: #fff;
        text-decoration: none;
    }

    .small-box-footer i {
        margin-left: 8px;
        transition: transform 0.3s ease;
    }

    .small-box-footer:hover i {
        transform: translateX(5px);
    }

    /* Premium Color Schemes with Gradients */
    .bg-blue {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .bg-aqua {
        background: linear-gradient(135deg, #2af598 0%, #009efd 100%);
    }

    .bg-orange {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    }

    .bg-green {
        background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
    }

    .bg-red {
        background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
    }

    .bg-yellow {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    /* Custom color for monthly income */
    .bg-teal {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    /* Notification Alerts */
    .dashalert {
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.42);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
        margin-bottom: 20px;
        padding: 18px 20px;
        font-weight: 500;
        animation: slideInDown 0.5s ease;
        background: rgba(255, 255, 255, 0.55);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }

    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .dashalert a {
        color: inherit;
        font-weight: 600;
        text-decoration: none;
        border-bottom: 2px solid currentColor;
        transition: all 0.3s ease;
    }

    .dashalert a:hover {
        opacity: 0.8;
        border-bottom-width: 3px;
    }

    /* Box Styling for Charts */
    .box {
        border-radius: 15px;
        box-shadow: 0 10px 26px rgba(15, 23, 42, 0.08);
        border: 1px solid rgba(255, 255, 255, 0.46);
        margin-bottom: 25px;
        background: rgba(255, 255, 255, 0.52);
        backdrop-filter: blur(18px) saturate(135%);
        -webkit-backdrop-filter: blur(18px) saturate(135%);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .box:hover {
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.12);
    }

    .box::after {
        content: '';
        position: absolute;
        inset: 0;
        background: radial-gradient(260px circle at var(--mx, 50%) var(--my, 50%), rgba(255, 255, 255, 0.24), rgba(255, 255, 255, 0) 62%);
        opacity: 0;
        transition: opacity 0.35s ease;
        pointer-events: none;
    }

    .box:hover::after {
        opacity: 1;
    }

    .box-header {
        border-bottom: 2px solid #f5f7fa;
        padding: 20px;
        border-radius: 15px 15px 0 0;
    }

    .box-title {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .box-body {
        padding: 25px;
    }

    .box-primary {
        border-top: 4px solid #667eea;
    }

    .box-info {
        border-top: 4px solid #2af598;
    }

    /* Progress Bars */
    .topprograssstart {
        background: rgba(255, 255, 255, 0.6);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.46);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
    }

    .topprograssstart:hover {
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
    }

    .pro-border {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        padding-bottom: 15px;
        border-bottom: 3px solid #667eea;
        margin-bottom: 20px;
    }

    .progress-minibar {
        height: 8px;
        border-radius: 10px;
        background: #e8ecf1;
        overflow: hidden;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .progress-bar {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
        transition: width 1s ease;
    }

    .progress-bar-aqua {
        background: linear-gradient(90deg, #2af598 0%, #009efd 100%);
    }

    .progress-bar-red {
        background: linear-gradient(90deg, #eb3349 0%, #f45c43 100%);
    }

    .progress-bar-yellow {
        background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%);
    }

    .progress-bar-green {
        background: linear-gradient(90deg, #0ba360 0%, #3cba92 100%);
    }

    /* Quick Links - Modern Card Grid Design */
    .quicklinks-container {
        width: 100%;
        margin-bottom: 30px;
    }

    .quicklinks-box {
        background: rgba(255, 255, 255, 0.5);
        border-radius: 15px;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.09);
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid rgba(255, 255, 255, 0.48);
        backdrop-filter: blur(18px) saturate(140%);
        -webkit-backdrop-filter: blur(18px) saturate(140%);
    }

    .quicklinks-box:hover {
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
    }

    .quicklinks-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 20px 25px;
        border-bottom: none;
    }

    .quicklinks-header i {
        color: #fff;
        font-size: 22px;
        margin-right: 12px;
        vertical-align: middle;
    }

    .quicklinks-header h3 {
        color: #fff;
        font-size: 20px;
        font-weight: 700;
        margin: 0;
        display: inline-block;
        vertical-align: middle;
    }

    .quicklinks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 15px;
        padding: 25px;
    }

    .quicklinks-grid a.quicklink-card {
        background-color: #ffffff;
        background-image: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-radius: 12px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        text-decoration: none;
        color: inherit !important;
        border: 1px solid rgba(255, 255, 255, 0.6);
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease, border-color 0.3s ease, background-color 0.3s ease;
        position: relative;
        overflow: hidden;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.07);
        isolation: isolate;
        transform: translateZ(0);
    }

    .quicklinks-grid a.quicklink-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, var(--card-color), var(--card-color-dark));
        transform: scaleY(0);
        transition: transform 0.3s ease;
        z-index: 0;
    }

    .quicklinks-grid a.quicklink-card:hover::before,
    .quicklinks-grid a.quicklink-card:focus::before {
        transform: scaleY(1);
    }

    .quicklinks-grid a.quicklink-card::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.34), rgba(255, 255, 255, 0.08));
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
        z-index: 0;
    }

    .quicklinks-grid a.quicklink-card:hover,
    .quicklinks-grid a.quicklink-card:focus,
    .quicklinks-grid a.quicklink-card:active {
        transform: translate3d(8px, 0, 0);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        border-color: var(--card-color);
        background-color: #ffffff !important;
        background-image: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
        color: inherit !important;
        text-decoration: none !important;
    }

    .quicklinks-grid a.quicklink-card:hover::after,
    .quicklinks-grid a.quicklink-card:focus::after {
        opacity: 1;
    }

    .quicklinks-grid a.quicklink-card:visited,
    .quicklinks-grid a.quicklink-card:link {
        color: inherit !important;
        text-decoration: none !important;
    }

    .quicklinks-grid .quicklink-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: #fff;
        margin-right: 15px;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
        background: linear-gradient(135deg, var(--card-color), var(--card-color-dark));
    }

    .quicklinks-grid .quicklink-card .quicklink-icon,
    .quicklinks-grid .quicklink-card .quicklink-content,
    .quicklinks-grid .quicklink-card .quicklink-arrow {
        position: relative;
        z-index: 1;
    }

    .quicklinks-grid a.quicklink-card:hover .quicklink-icon,
    .quicklinks-grid a.quicklink-card:focus .quicklink-icon {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
    }

    .quicklinks-grid .quicklink-content {
        flex: 1;
    }

    .quicklinks-grid .quicklink-title {
        font-size: 15px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0;
        line-height: 1.4;
        transition: color 0.3s ease;
    }

    .quicklinks-grid a.quicklink-card:hover .quicklink-title,
    .quicklinks-grid a.quicklink-card:focus .quicklink-title {
        color: var(--card-color);
    }

    .quicklinks-grid .quicklink-arrow {
        font-size: 18px;
        color: #95a5a6;
        margin-left: 10px;
        transition: all 0.3s ease;
    }

    .quicklinks-grid a.quicklink-card:hover .quicklink-arrow,
    .quicklinks-grid a.quicklink-card:focus .quicklink-arrow {
        color: var(--card-color);
        transform: translateX(5px);
    }

    /* Individual card color schemes */
    .quicklink-card.color-1 {
        --card-color: #667eea;
        --card-color-dark: #764ba2;
    }

    .quicklink-card.color-2 {
        --card-color: #f093fb;
        --card-color-dark: #f5576c;
    }

    .quicklink-card.color-3 {
        --card-color: #4facfe;
        --card-color-dark: #00f2fe;
    }

    .quicklink-card.color-4 {
        --card-color: #43e97b;
        --card-color-dark: #38f9d7;
    }

    .quicklink-card.color-5 {
        --card-color: #fa709a;
        --card-color-dark: #fee140;
    }

    .quicklink-card.color-6 {
        --card-color: #30cfd0;
        --card-color-dark: #330867;
    }

    .quicklink-card.color-7 {
        --card-color: #a8edea;
        --card-color-dark: #fed6e3;
    }

    .quicklink-card.color-8 {
        --card-color: #ff9a56;
        --card-color-dark: #ff6a88;
    }

    .quicklink-card.color-9 {
        --card-color: #f857a6;
        --card-color-dark: #ff5858;
    }

    .quicklink-card.color-10 {
        --card-color: #0ba360;
        --card-color-dark: #3cba92;
    }

    .quicklink-card.color-11 {
        --card-color: #667eea;
        --card-color-dark: #764ba2;
    }

    .quicklink-card.color-12 {
        --card-color: #f093fb;
        --card-color-dark: #f5576c;
    }

    .quicklink-card.color-13 {
        --card-color: #4facfe;
        --card-color-dark: #00f2fe;
    }

    .quicklink-card.color-14 {
        --card-color: #43e97b;
        --card-color-dark: #38f9d7;
    }

    .quicklink-card.color-15 {
        --card-color: #fa709a;
        --card-color-dark: #fee140;
    }

    @media (max-width: 768px) {
        .quicklinks-grid {
            grid-template-columns: 1fr;
            gap: 12px;
        }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .small-box .inner h3 {
            font-size: 28px;
        }

        .small-box .icon {
            font-size: 60px;
        }

        .box-header {
            padding: 15px;
        }
    }

    /* Animation on load */
    .dashboard-metric {
        animation: fadeInUp 0.6s ease;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Stagger animation for cards */
    .dashboard-metric:nth-child(1) {
        animation-delay: 0.1s;
    }

    .dashboard-metric:nth-child(2) {
        animation-delay: 0.2s;
    }

    .dashboard-metric:nth-child(3) {
        animation-delay: 0.3s;
    }

    .dashboard-metric:nth-child(4) {
        animation-delay: 0.4s;
    }

    .dashboard-metric:nth-child(5) {
        animation-delay: 0.5s;
    }

    .dashboard-metric:nth-child(6) {
        animation-delay: 0.6s;
    }

    /* Button improvements */
    .btn-info {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-info:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        transform: translateY(-2px);
    }
</style>

<div class="content-wrapper" style="min-height: 946px;">

    <section class="content">

        <div class="row">

            <div class="col-md-12">

                <?php

                foreach ($notifications as $notice_key => $notice_value) {

                ?>
                    <div class="dashalert alert alert-success alert-dismissible" role="alert">

                        <button type="button" class="alertclose close close_notice" data-dismiss="alert" aria-label="Close" data-noticeid="<?php echo $notice_value->id; ?>"><span aria-hidden="true">&times;</span></button>

                        <a href="<?php echo site_url('admin/notification') ?>"><?php echo $notice_value->title; ?></a>

                    </div>

                <?php

                }

                ?>

            </div>

            <?php
            $currency_symbol = $this->customlib->getSchoolCurrencyFormat();
            $format_dashboard_amount = static function ($amount, $decimals = 2) {
                return number_format((float) $amount, $decimals, '.', ',');
            };
            ?>
            <div class="col-md-12">

                <div class="row">

                    <?php if ($this->rbac->hasPrivilege('Monthly fees_collection_widget', 'can_view')) { ?>
                        <!-- Total Receivable Fee Box -->
                        <div class="col col-lg col-md-4 col-sm-6 col-xs-12 dashboard-metric">
                            <div class="small-box bg-blue">
                                <div class="inner">
                                    <h3><?php echo $currency_symbol . $format_dashboard_amount(isset($total_receivable_fee) ? $total_receivable_fee : 0); ?></h3>

                                    <p>Total Receivable Fee</p>
                                </div>
                                <div class="icon">
                                    <i class="fa fa-money"></i>
                                </div>
                                <a href="<?php echo site_url('studentfee/fee_summary_report_view') ?>" target="_blank" class="small-box-footer">Manage <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <!-- Monthly Fees Collection Box -->
                        <div class="col col-lg col-md-4 col-sm-6 col-xs-12 dashboard-metric">
                            <div class="small-box bg-aqua">
                                <div class="inner">
                                    <h3><?php echo $currency_symbol . $format_dashboard_amount($month_collection); ?></h3>

                                    <p><?php echo $this->lang->line('monthly_fees_collection'); ?></p>
                                </div>
                                <div class="icon">
                                    <i class="fa fa-money"></i>
                                </div>
                                <a href="<?php echo site_url('studentfee/collection_report_dashboard') ?>" target="_blank" class="small-box-footer">Manage <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                        <!-- Total Remaining Balance Box -->
                        <div class="col col-lg col-md-4 col-sm-6 col-xs-12 dashboard-metric">
                            <div class="small-box bg-orange">
                                <div class="inner">
                                    <?php
                                    $remaining_balance = isset($total_remaining_balance) ? floatval($total_remaining_balance) : 0;
                                    ?>
                                    <h3><?php echo $currency_symbol . $format_dashboard_amount($remaining_balance); ?></h3>

                                    <p>Total Remaining Balance</p>
                                </div>
                                <div class="icon">
                                    <i class="fa fa-money"></i>
                                </div>
                                <a href="<?php echo site_url('studentfee/pendingFeeReportView') ?>" target="_blank" class="small-box-footer">Manage <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($this->rbac->hasPrivilege('monthly_expense_widget', 'can_view')) { ?>
                        <div class="col col-lg col-md-4 col-sm-6 col-xs-12 dashboard-metric">
                            <div class="small-box bg-green">
                                <div class="inner">
                                    <h3><?php echo $currency_symbol . $format_dashboard_amount($month_expense); ?></h3>

                                    <p><?php echo $this->lang->line('monthly_expenses'); ?></p>
                                </div>
                                <div class="icon">
                                    <i class="fa fa-money"></i>
                                </div>
                                <a href="<?php echo site_url('report/expense_dashboard') ?>" target="_blank" class="small-box-footer">Manage <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ($this->rbac->hasPrivilege('monthly_expense_widget', 'can_view')) { ?>
                        <div class="col col-lg col-md-4 col-sm-6 col-xs-12 dashboard-metric">
                            <div class="small-box bg-teal">
                                <div class="inner">
                                    <h3><?php echo $currency_symbol . $format_dashboard_amount($month_income); ?></h3>

                                    <p><?php echo 'Monthly Income'; ?></p>
                                </div>
                                <div class="icon">
                                    <i class="fa fa-money"></i>
                                </div>
                                <a href="<?php echo site_url('report/income_dashboard') ?>" target="_blank" class="small-box-footer">Manage <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>

                    <?php } ?>

                    <?php if ($this->rbac->hasPrivilege('student_count_widget', 'can_view')) { ?>
                        <div class="col col-lg col-md-4 col-sm-6 col-xs-12 dashboard-metric">
                            <div class="small-box bg-yellow">
                                <div class="inner">
                                    <h3><?php echo number_format((int) $total_students, 0, '.', ','); ?></h3>

                                    <p><?php echo $this->lang->line('student'); ?>(s)</p>
                                </div>
                                <div class="icon">
                                    <i class="fa fa-id-card"></i>
                                </div>
                                <a href="<?php echo site_url('student/classStrengthReportPDF') ?>" target="_blank" class="small-box-footer">Manage <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    <?php } ?>

                    <!-- Pending Annual Charges Widget -->
                    <?php if ($this->rbac->hasPrivilege('pending_annual_charges_widget', 'can_view') && isset($pending_annual_charges) && is_array($pending_annual_charges)): ?>
                        <div class="col col-lg col-md-4 col-sm-6 col-xs-12 dashboard-metric">
                            <div class="small-box bg-red">
                                <div class="inner">
                                    <h3><?php echo isset($pending_annual_charges['total_amount']) ? $currency_symbol . $format_dashboard_amount($pending_annual_charges['total_amount']) : $currency_symbol . '0.00'; ?></h3>

                                    <p>Pending Annual Charges</p>
                                </div>
                                <div class="icon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <a href="<?php echo site_url('admin/admin/pendingAnnualChargesPDF') ?>" target="_blank" class="small-box-footer">View Report <i class="fa fa-arrow-circle-right"></i></a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($this->rbac->hasPrivilege('staff_role_count_widget', 'can_view')) {

                    ?>
                        <div class="col-lg-3 col-xs-6" style="display:none;">
                            <?php foreach ($roles as $key => $value) {

                            ?>
                                <!-- small box -->
                                <div class="small-box bg-red">
                                    <div class="inner">
                                        <h3><?php echo number_format((int) $value, 0, '.', ','); ?></h3>
                                        <p><?php echo $key; ?>(s)</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fa fa-users"></i>
                                    </div>
                                    <a href="<?php echo site_url('admin/staff') ?>" class="small-box-footer">Manage <i class="fa fa-arrow-circle-right"></i></a>
                                </div>
                                <?php break; ?>

                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <div class="col-md-12">

                <div class="row">
                    <?php if ($this->rbac->hasPrivilege('Monthly fees_collection_widget', 'can_view')) { ?>
                        <div class="col-lg-6">
                            <div class="box box-info">
                                <div class="box-header">
                                    <i class="fa fa-money"></i>
                                    <h3 class="box-title">Fees Overview</h3>
                                    <a href="<?php echo base_url('student/classFeeCollectionReportPDF') ?>" target="_blank" class="btn btn-info">Report</a>

                                </div>
                                <div class="box-body">


                                    <div class="col-md-12 col-sm-12">

                                        <div class="topprograssstart">
                                            <h5 class="pro-border pb10"><?php echo $this->lang->line('fees') . " " . $this->lang->line('overview') ?></h5>
                                            <p class="text-uppercase mt10 clearfix"><?php echo $fees_overview['total_unpaid'] + $fees_overview['total_partial']; ?> <?php echo $this->lang->line('unpaid'); ?><span class="pull-right"><?php echo round($fees_overview['unpaid_progress'] + $fees_overview['partial_progress'], 2); ?>%</span>
                                            </p>
                                            <div class="progress-group">
                                                <div class="progress progress-minibar">
                                                    <div class="progress-bar" style="width: <?php echo $fees_overview['unpaid_progress'] + $fees_overview['partial_progress']; ?>%"></div>
                                                </div>
                                            </div>

                                            <p class="text-uppercase mt10 clearfix" hidden><?php echo $fees_overview['total_partial']; ?> <?php echo $this->lang->line('partial'); ?><span class="pull-right"><?php echo round($fees_overview['partial_progress'], 2); ?>%</span>
                                            </p>
                                            <div class="progress-group" hidden>
                                                <div class="progress progress-minibar">
                                                    <div class="progress-bar progress-bar-aqua" style="width: <?php echo $fees_overview['partial_progress']; ?>%"></div>
                                                </div>
                                            </div>

                                            <p class="text-uppercase mt10 clearfix"><?php echo $fees_overview['total_paid']; ?> <?php echo $this->lang->line('paid'); ?><span class="pull-right"><?php echo round($fees_overview['paid_progress'], 2); ?>%</span>
                                            </p>
                                            <div class="progress-group">
                                                <div class="progress progress-minibar">
                                                    <div class="progress-bar progress-bar-aqua" style="width: <?php echo $fees_overview['paid_progress']; ?>%"></div>
                                                </div>
                                            </div>
                                        </div><!--./topprograssstart-->

                                    </div>
                                </div>
                            </div>

                        </div>
                    <?php
                    }

                    if ($this->rbac->hasPrivilege('Monthly fees_collection_widget', 'can_view')) { ?>
                        <div class="col-lg-6">
                            <div class="box box-info">
                                <div class="box-header">
                                    <i class="fa fa-users"></i>
                                    <h3 class="box-title">Student Today's Attendance</h3>
                                    <a href="<?php echo base_url('student/classAttReportPDF') ?>" target="_blank" class="btn btn-info">Report</a>
                                </div>
                                <div class="box-body">
                                    <div class="col-md-12 col-sm-12">
                                        <div class="topprograssstart">
                                            <p class="text-uppercase mt10 clearfix"><?php echo $attendence_data['total_present']; ?> <?php echo $this->lang->line('present'); ?><span class="pull-right"><?php echo $attendence_data['present']; ?></span>
                                            </p>
                                            <div class="progress-group">
                                                <div class="progress progress-minibar">
                                                    <div class="progress-bar" style="width: <?php echo $attendence_data['present']; ?>"></div>
                                                </div>
                                            </div>

                                            <p class="text-uppercase mt10 clearfix"><?php echo $attendence_data['total_late']; ?> <?php echo $this->lang->line('late') ?><span class="pull-right"><?php echo $attendence_data['late']; ?></span>
                                            </p>
                                            <div class="progress-group">
                                                <div class="progress progress-minibar">
                                                    <div class="progress-bar" style="width: <?php echo $attendence_data['late']; ?>"></div>
                                                </div>
                                            </div>
                                            <p class="text-uppercase mt10 clearfix"><?php echo $attendence_data['total_absent']; ?> <?php echo $this->lang->line('absent'); ?><span class="pull-right"><?php echo $attendence_data['absent']; ?></span>
                                            </p>
                                            <div class="progress-group">
                                                <div class="progress progress-minibar">
                                                    <div class="progress-bar" style="width: <?php echo $attendence_data['absent']; ?>"></div>
                                                </div>
                                            </div>
                                            <p class="text-uppercase mt10 clearfix"><?php echo $attendence_data['total_half_day']; ?> <?php echo $this->lang->line('half_day'); ?><span class="pull-right"><?php echo $attendence_data['half_day']; ?></span>
                                            </p>
                                            <div class="progress-group">
                                                <div class="progress progress-minibar">
                                                    <div class="progress-bar" style="width: <?php echo $attendence_data['half_day']; ?>"></div>
                                                </div>
                                            </div>
                                        </div><!--./topprograssstart-->

                                    </div>
                                </div>
                            </div>
                        </div><!--./col-md-3-->
                    <?php
                    } ?>
                </div>
            </div>
            <div class="col-md-12">
                <div class="row">
                    <!-- Quick Links - Full Width Modern Design -->
                    <div class="col-md-12 quicklinks-container">
                        <div class="quicklinks-box">
                            <div class="quicklinks-header">
                                <i class="fa fa-link"></i>
                                <h3 class="box-title">Quick Links</h3>
                            </div>
                            <div class="quicklinks-grid">
                                <a class="quicklink-card color-1" href="<?php echo base_url(); ?>admin/users/logindetailreport">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-key"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('student') . " Login Credential"; ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>

                                <a class="quicklink-card color-2" href="<?php echo base_url(); ?>studentfee">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-money"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('collect_fees'); ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>

                                <a class="quicklink-card color-3" href="<?php echo base_url(); ?>admin/income">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-plus-circle"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('add_income'); ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>

                                <a class="quicklink-card color-4" href="<?php echo base_url(); ?>admin/expense">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-credit-card"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('add_expense'); ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>

                                <a class="quicklink-card color-5" href="<?php echo base_url(); ?>admin/stuattendence">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('student_attendance'); ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>

                                <a class="quicklink-card color-6" href="<?php echo base_url(); ?>admin/staffattendance">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-calendar-check-o"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('staff_attendance'); ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>

                                <a class="quicklink-card color-7" href="<?php echo base_url(); ?>admin/staff">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-users"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('staff_directory'); ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>

                                <a class="quicklink-card color-8" href="<?php echo base_url(); ?>admin/exam">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-file-text"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('exam_list'); ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>

                                <a class="quicklink-card color-9" href="<?php echo base_url(); ?>admin/examschedule">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('exam_schedule'); ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>

                                <a class="quicklink-card color-10" href="<?php echo base_url(); ?>admin/timetable">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-table"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('class_timetable'); ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>

                                <a class="quicklink-card color-11" href="<?php echo base_url(); ?>admin/mailsms/compose">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-envelope"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('send_email_/_sms'); ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>

                                <a class="quicklink-card color-12" href="<?php echo base_url(); ?>admin/complaint">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-exclamation-circle"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('complain'); ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>

                                <a class="quicklink-card color-13" href="<?php echo base_url(); ?>admin/content">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-upload"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('upload_content'); ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>

                                <a class="quicklink-card color-14" href="<?php echo base_url(); ?>admin/notification">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-bell"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('notice_board'); ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>

                                <a class="quicklink-card color-15" href="<?php echo base_url(); ?>admin/itemstock">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-cube"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('add_item_stock'); ?></p>
                                    </div>
                                    <i class="fa fa-arrow-right quicklink-arrow"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

</div>

<div id="newEventModal" class="modal fade " role="dialog">

    <div class="modal-dialog modal-dialog2 modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal">&times;</button>

                <h4 class="modal-title"><?php echo "Add New Event"; ?></h4>

            </div>

            <div class="modal-body">



                <div class="row">

                    <form role="form" id="addevent_form" method="post" enctype="multipart/form-data" action="">

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event'); ?> <?php echo $this->lang->line('title'); ?></label>

                            <input class="form-control" name="title" id="input-field">

                            <span class="text-danger"><?php echo form_error('title'); ?></span>



                        </div>



                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>

                            <textarea name="description" class="form-control" id="desc-field"></textarea>
                        </div>

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event'); ?> <?php echo $this->lang->line('date'); ?></label>

                            <div class="input-group">

                                <div class="input-group-addon">

                                    <i class="fa fa-calendar"></i>

                                </div>

                                <input type="text" autocomplete="off" name="event_dates" class="form-control pull-right" id="date-field">

                            </div>

                            <!-- <input class="form-control" type="text" autocomplete="off"  name="event_dates" id="date-field"> -->
                        </div>

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event'); ?> <?php echo $this->lang->line('color'); ?></label>

                            <input type="hidden" name="eventcolor" autocomplete="off" id="eventcolor" class="form-control">

                        </div>

                        <div class="form-group col-md-12">

                            <?php //print_r($event_colors)  
                            ?>



                            <?php

                            $i = 0;

                            $colors = '';

                            foreach ($event_colors as $color) {

                                $color_selected_class = 'cpicker-small';

                                if ($i == 0) {

                                    $color_selected_class = 'cpicker-big';
                                }

                                $colors .= "<div class='calendar-cpicker cpicker " . $color_selected_class . "' data-color='" . $color . "' style='background:" . $color . ";border:1px solid " . $color . "; border-radius:100px'></div>";

                                //   echo $colors ;

                                $i++;
                            }

                            echo '<div class="cpicker-wrapper">';

                            echo $colors;

                            echo '</div>';

                            ?>

                        </div>



                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event'); ?> <?php echo $this->lang->line('type'); ?></label>

                            <br />

                            <label class="radio-inline">



                                <input type="radio" name="event_type" value="public" id="public"><?php echo $this->lang->line('public'); ?>

                            </label>

                            <label class="radio-inline">



                                <input type="radio" name="event_type" value="private" checked id="private"><?php echo $this->lang->line('private'); ?>

                            </label>

                            <label class="radio-inline">



                                <input type="radio" name="event_type" value="sameforall" id="public"><?php echo $this->lang->line('all'); ?> <?php echo $role ?>

                            </label>

                            <label class="radio-inline">



                                <input type="radio" name="event_type" value="protected" id="public"><?php echo $this->lang->line('protected'); ?>

                            </label>
                        </div>



                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

                            <input type="submit" class="btn btn-primary submit_addevent pull-right" value="<?php echo $this->lang->line('save'); ?>">
                        </div>
                    </form>

                </div>



            </div>

        </div>

    </div>

</div>

<div id="viewEventModal" class="modal fade " role="dialog">

    <div class="modal-dialog modal-dialog2 modal-lg">

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal">&times;</button>

                <h4 class="modal-title"><?php echo "View Event"; ?></h4>

            </div>

            <div class="modal-body">



                <div class="row">

                    <form role="form" method="post" id="updateevent_form" enctype="multipart/form-data" action="">

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event') ?><?php echo $this->lang->line('title') ?></label>

                            <input class="form-control" name="title" placeholder="Event Title" id="event_title">

                        </div>

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('description') ?></label>

                            <textarea name="description" class="form-control" placeholder="Event Description" id="event_desc"></textarea>
                        </div>

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event') ?><?php echo $this->lang->line('date') ?></label>

                            <div class="input-group">

                                <div class="input-group-addon">

                                    <i class="fa fa-calendar"></i>

                                </div>

                                <input type="text" autocomplete="off" name="eventdates" class="form-control pull-right" id="eventdates">

                            </div>

                            <!-- <input class="form-control" type="text" autocomplete="off" name="eventdates" placeholder="Event Dates" id="eventdates"> -->
                        </div>

                        <input type="hidden" name="eventid" id="eventid">

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event') ?><?php echo $this->lang->line('color') ?></label>

                            <input type="hidden" name="eventcolor" autocomplete="off" placeholder="Event Color" id="event_color" class="form-control">

                        </div>

                        <div class="form-group col-md-12">

                            <?php //print_r($event_colors)  
                            ?>



                            <?php

                            $i = 0;

                            $colors = '';

                            foreach ($event_colors as $color) {

                                $colorid = trim($color, "#");

                                // print_r($colorid);

                                $color_selected_class = 'cpicker-small';

                                if ($i == 0) {

                                    $color_selected_class = 'cpicker-big';
                                }

                                $colors .= "<div id=" . $colorid . " class='calendar-cpicker cpicker " . $color_selected_class . "' data-color='" . $color . "' style='background:" . $color . ";border:1px solid " . $color . "; border-radius:100px'></div>";

                                //   echo $colors ;

                                $i++;
                            }

                            echo '<div class="cpicker-wrapper selectevent">';

                            echo $colors;

                            echo '</div>';

                            ?>

                        </div>

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event') ?><?php echo $this->lang->line('type') ?></label>

                            <label class="radio-inline">



                                <input type="radio" name="eventtype" value="public" id="public"><?php echo $this->lang->line('public') ?>

                            </label>

                            <label class="radio-inline">

                                <input type="radio" name="eventtype" value="private" id="private"><?php echo $this->lang->line('private') ?>

                            </label>

                            <label class="radio-inline">


                                <input type="radio" name="eventtype" value="sameforall" id="public"><?php echo $this->lang->line('all') ?> <?php echo $role ?>

                            </label>

                            <label class="radio-inline">

                                <input type="radio" name="eventtype" value="protected" id="public"><?php echo $this->lang->line('protected') ?>

                            </label>

                        </div>



                        <div class="col-xs-11 col-sm-11 col-md-11 col-lg-11">



                            <input type="submit" class="btn btn-primary submit_update pull-right" value="<?php echo $this->lang->line('save'); ?>">

                        </div>

                        <div class="col-xs-1 col-sm-1 col-md-1 col-lg-1">

                            <?php if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_delete')) { ?>

                                <input type="button" id="delete_event" class="btn btn-primary submit_delete pull-right" value="<?php echo $this->lang->line('delete'); ?>">

                            <?php } ?>

                        </div>

                    </form>

                </div>



            </div>

        </div>

    </div>

</div>

<script>
    $(function () {
        $(document).on('click', '.close_notice', function () {
            var data = $(this).data();

            $.ajax({
                type: "POST",
                url: base_url + "admin/notification/read",
                data: {
                    notice: data.noticeid
                },
                dataType: "json",
                success: function (response) {
                    if (response.status === "fail") {
                        errorMsg(response.msg);
                    } else {
                        successMsg(response.msg);
                    }
                }
            });
        });

        document.querySelectorAll('.small-box, .box').forEach(function (node) {
            node.addEventListener('mousemove', function (event) {
                var rect = node.getBoundingClientRect();
                node.style.setProperty('--mx', (event.clientX - rect.left) + 'px');
                node.style.setProperty('--my', (event.clientY - rect.top) + 'px');
            });

            node.addEventListener('mouseleave', function () {
                node.style.setProperty('--mx', '50%');
                node.style.setProperty('--my', '50%');
            });
        });
    });
</script>
