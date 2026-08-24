<style>
/* Modern Dashboard Styles - Premium & Attractive */
body.hold-transition .wrapper,
body.hold-transition .content-wrapper,
body.hold-transition .right-side,
body.skin-blue .wrapper,
body.skin-blue .content-wrapper,
body.skin-blue .right-side,
body.skin-blue .main-footer,
body.skin-blue-light .wrapper,
body.skin-blue-light .content-wrapper,
body.skin-blue-light .right-side,
body.skin-blue-light .main-footer {
    background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%) !important;
}

.content-wrapper {
    background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
    border-left: 0 !important;
    min-height: 100vh !important;
}

.content {
    background: transparent !important;
    padding: 20px;
}

.content > .row {
    margin-left: 0;
    margin-right: 0;
}

.content > .row > [class*='col-'] {
    padding-left: 0;
    padding-right: 0;
}

/* Modern Widget Cards */
.small-box {
    border-radius: 15px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    border: none;
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    background: #fff;
    margin-bottom: 25px;
}

.small-box:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.2);
}

.small-box::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, rgba(255,255,255,0.3) 0%, rgba(255,255,255,0.1) 100%);
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
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.small-box .inner p {
    font-size: 15px;
    margin: 0;
    color: rgba(255,255,255,0.95);
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
    color: rgba(255, 255, 255, 0.15);
    z-index: 1;
    transition: all 0.3s ease;
}

.small-box:hover .icon {
    font-size: 90px;
    color: rgba(255, 255, 255, 0.25);
    transform: translateY(-50%) rotate(10deg);
}

.small-box-footer {
    display: block;
    padding: 12px 20px;
    text-decoration: none;
    color: rgba(255,255,255,0.9);
    background: rgba(0, 0, 0, 0.15);
    text-align: center;
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 14px;
    letter-spacing: 0.3px;
    margin-top: auto;
}

.small-box-footer:hover {
    background: rgba(0, 0, 0, 0.25);
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
    border: none;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    padding: 18px 20px;
    font-weight: 500;
    animation: slideInDown 0.5s ease;
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
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    border: none;
    margin-bottom: 25px;
    background: #fff;
    transition: all 0.3s ease;
}

.box:hover {
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
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
    background: #fff;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
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
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
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
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 10px 28px rgba(24, 39, 75, 0.08);
    overflow: hidden;
    padding-left: 0;
    padding-right: 0;
}

.quicklinks-box {
    background: transparent;
    border-radius: 20px;
    box-shadow: none;
    overflow: hidden;
    transition: all 0.3s ease;
}

.quicklinks-box:hover {
    box-shadow: none;
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

.quicklinks-campus-links {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    padding: 20px 25px 0;
}

.quicklinks-campus-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 150px;
    padding: 10px 18px;
    border-radius: 999px;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    line-height: 1;
    text-decoration: none;
    box-shadow: 0 8px 18px rgba(24, 39, 75, 0.16);
    transition: transform 0.25s ease, box-shadow 0.25s ease, opacity 0.25s ease;
}

.quicklinks-campus-link:hover,
.quicklinks-campus-link:focus {
    color: #fff;
    text-decoration: none;
    transform: translateY(-2px);
    box-shadow: 0 12px 22px rgba(24, 39, 75, 0.2);
    opacity: 0.95;
}

.quicklinks-campus-link--boys {
    background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);
}

.quicklinks-campus-link--girls {
    background: linear-gradient(135deg, #10b981 0%, #22c55e 100%);
}

.quicklinks-campus-link--junior {
    background: linear-gradient(135deg, #f97316 0%, #f59e0b 100%);
}

.quicklink-card {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 12px;
    padding: 18px 20px;
    display: flex;
    align-items: center;
    text-decoration: none;
    border: 2px solid transparent;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    position: relative;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.quicklink-card::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, var(--card-color), var(--card-color-dark));
    transform: scaleY(0);
    transition: transform 0.3s ease;
}

.quicklink-card:hover::before {
    transform: scaleY(1);
}

.quicklink-card:hover {
    transform: translateX(8px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    border-color: var(--card-color);
    background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
}

.quicklink-icon {
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

.quicklink-card:hover .quicklink-icon {
    transform: scale(1.1) rotate(5deg);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
}

.quicklink-content {
    flex: 1;
}

.quicklink-title {
    font-size: 15px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
    line-height: 1.4;
    transition: color 0.3s ease;
}

.quicklink-card:hover .quicklink-title {
    color: var(--card-color);
}

.quicklink-arrow {
    font-size: 18px;
    color: #95a5a6;
    margin-left: 10px;
    transition: all 0.3s ease;
}

.quicklink-card:hover .quicklink-arrow {
    color: var(--card-color);
    transform: translateX(5px);
}

/* Individual card color schemes */
.quicklink-card.color-1 { --card-color: #667eea; --card-color-dark: #764ba2; }
.quicklink-card.color-2 { --card-color: #f093fb; --card-color-dark: #f5576c; }
.quicklink-card.color-3 { --card-color: #4facfe; --card-color-dark: #00f2fe; }
.quicklink-card.color-4 { --card-color: #43e97b; --card-color-dark: #38f9d7; }
.quicklink-card.color-5 { --card-color: #fa709a; --card-color-dark: #fee140; }
.quicklink-card.color-6 { --card-color: #30cfd0; --card-color-dark: #330867; }
.quicklink-card.color-7 { --card-color: #a8edea; --card-color-dark: #fed6e3; }
.quicklink-card.color-8 { --card-color: #ff9a56; --card-color-dark: #ff6a88; }
.quicklink-card.color-9 { --card-color: #f857a6; --card-color-dark: #ff5858; }
.quicklink-card.color-10 { --card-color: #0ba360; --card-color-dark: #3cba92; }
.quicklink-card.color-11 { --card-color: #667eea; --card-color-dark: #764ba2; }
.quicklink-card.color-12 { --card-color: #f093fb; --card-color-dark: #f5576c; }
.quicklink-card.color-13 { --card-color: #4facfe; --card-color-dark: #00f2fe; }
.quicklink-card.color-14 { --card-color: #43e97b; --card-color-dark: #38f9d7; }
.quicklink-card.color-15 { --card-color: #fa709a; --card-color-dark: #fee140; }

@media (max-width: 768px) {
    .quicklinks-campus-links {
        padding: 18px 20px 0;
        gap: 10px;
    }

    .quicklinks-campus-link {
        flex: 1 1 100%;
        min-width: 0;
    }

    .quicklinks-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
}

/* Quick Links Buttons */
.btn.icon-btn {
    border-radius: 10px;
    padding: 12px 20px;
    margin: 5px 0;
    width: 100%;
    text-align: left;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s ease;
    font-weight: 600;
    color: #fff;
}

.btn.icon-btn:hover {
    transform: translateX(5px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
}

.btn.icon-btn .fa {
    margin-right: 10px;
}

/* Info Boxes */
.info-box {
    border-radius: 15px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    background: #fff;
    transition: all 0.3s ease;
    overflow: hidden;
}

.info-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
}

.info-box-icon {
    border-radius: 12px 0 0 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 50px;
    transition: all 0.3s ease;
}

.info-box:hover .info-box-icon {
    font-size: 55px;
}

.bg-yellow {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

/* Calendar Styling */
#calendar {
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
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
.col-md-4, .col-lg-4, .col-sm-6 {
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
.col-md-4:nth-child(1) { animation-delay: 0.1s; }
.col-md-4:nth-child(2) { animation-delay: 0.2s; }
.col-md-4:nth-child(3) { animation-delay: 0.3s; }
.col-md-4:nth-child(4) { animation-delay: 0.4s; }
.col-md-4:nth-child(5) { animation-delay: 0.5s; }
.col-md-4:nth-child(6) { animation-delay: 0.6s; }

/* Charts container improvement */
.chart-responsive {
    position: relative;
    padding: 10px;
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

.dashboard-summary-shell {
    margin: 10px 0 24px;
    padding: 0;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
}

.dashboard-summary-row {
    margin-left: 0;
    margin-right: 0;
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    grid-template-areas: "receivable collection dues";
    gap: 24px;
    align-items: stretch;
}

.dashboard-summary-row > [class*='col-'] {
    float: none;
    width: auto;
    max-width: none;
    padding-left: 0;
    padding-right: 0;
    display: block;
    margin: 0;
    min-width: 0;
}

.dashboard-summary-item--receivable {
    grid-area: receivable;
}

.dashboard-summary-item--collection {
    grid-area: collection;
}

.dashboard-summary-item--dues {
    grid-area: dues;
}

.dashboard-summary-item--attendance {
    grid-area: attendance;
}

.dashboard-summary-row > [class*='col-'] .small-box,
.dashboard-summary-row > [class*='col-'] .receivable-fee-widget,
.dashboard-summary-row > [class*='col-'] .fees-collection-widget,
.dashboard-summary-row > [class*='col-'] .previous-balance-widget,
.dashboard-summary-row > [class*='col-'] .attendance-overview-widget {
    width: 100%;
    margin-bottom: 0;
}

.dashboard-summary-row > [class*='col-'] .small-box {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.dashboard-summary-row > [class*='col-'] .small-box .inner {
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.dashboard-summary-item--receivable .receivable-fee-widget,
.dashboard-summary-item--collection .fees-collection-widget,
.dashboard-summary-item--dues .previous-balance-widget {
    min-height: 320px;
}

.dashboard-summary-item--attendance .attendance-overview-widget {
    min-height: 290px;
}

.dashboard-activity-shell {
    margin: 0 0 24px;
}

.dashboard-activity-row {
    margin-left: 0;
    margin-right: 0;
    display: grid;
    grid-template-columns: minmax(0, 60%) minmax(0, 40%);
    grid-template-areas: "attendance exams";
    gap: 24px;
    align-items: stretch;
    width: 100%;
}

.dashboard-activity-row--single {
    grid-template-columns: minmax(0, 1fr);
    grid-template-areas: "attendance";
}

.dashboard-activity-item {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    margin: 0;
    padding: 0;
    float: none;
    display: block;
}

.dashboard-activity-item--attendance {
    grid-area: attendance;
}

.dashboard-activity-item--exams {
    grid-area: exams;
}

.dashboard-activity-item .attendance-overview-widget,
.dashboard-activity-item .dashboard-exam-widget {
    width: 100%;
    margin-bottom: 0;
}

.dashboard-exam-widget {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 26px;
    box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
    display: flex;
    flex-direction: column;
    min-height: 290px;
    overflow: hidden;
    transition: all 0.35s ease;
}

.dashboard-exam-widget:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 36px rgba(15, 23, 42, 0.14);
}

.dashboard-exam-widget__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding: 20px 22px 14px;
}

.dashboard-exam-widget__title-wrap {
    min-width: 0;
}

.dashboard-exam-widget__title {
    margin: 0;
    color: #0f172a;
    font-size: 22px;
    font-weight: 700;
    line-height: 1.2;
}

.dashboard-exam-widget__subtitle {
    margin: 6px 0 0;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.4;
}

.dashboard-exam-widget__action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 16px;
    border-radius: 12px;
    background: linear-gradient(135deg, #0f9d58 0%, #18b56b 100%);
    color: #ffffff;
    text-decoration: none;
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
    box-shadow: 0 8px 18px rgba(15, 157, 88, 0.22);
}

.dashboard-exam-widget__action:hover,
.dashboard-exam-widget__action:focus {
    color: #ffffff;
    text-decoration: none;
    opacity: 0.96;
}

.dashboard-exam-widget__stats {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    padding: 0 22px 14px;
}

.dashboard-exam-widget__pill {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 12px;
    border-radius: 999px;
    background: #f8fafc;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
}

.dashboard-exam-widget__pill strong {
    font-size: 13px;
    color: #0f172a;
}

.dashboard-exam-widget__table-wrap {
    flex: 1;
    padding: 0 14px 14px;
}

.dashboard-exam-widget__table-scroll {
    max-height: 320px;
    overflow: auto;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
}

.dashboard-exam-widget__table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    background: #ffffff;
}

.dashboard-exam-widget__table thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    background: #eef2f7;
    color: #334155;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.04em;
    text-transform: uppercase;
    padding: 11px 10px;
    border-bottom: 1px solid #d8e1ec;
}

.dashboard-exam-widget__table td {
    padding: 11px 10px;
    border-bottom: 1px solid #edf2f7;
    color: #0f172a;
    font-size: 13px;
    vertical-align: top;
}

.dashboard-exam-widget__table tbody tr:last-child td {
    border-bottom: 0;
}

.dashboard-exam-widget__event {
    font-weight: 700;
    line-height: 1.35;
    word-break: break-word;
}

.dashboard-exam-widget__class {
    color: #475569;
    font-weight: 600;
    line-height: 1.35;
}

.dashboard-exam-widget__metric {
    text-align: center;
    font-weight: 800;
    font-size: 14px;
}

.dashboard-exam-widget__metric--done {
    color: #0f766e;
}

.dashboard-exam-widget__metric--pending {
    color: #c2410c;
}

.dashboard-exam-widget__empty {
    margin: 0 14px 14px;
    padding: 28px 20px;
    border: 1px dashed #cbd5e1;
    border-radius: 18px;
    text-align: center;
    color: #64748b;
    font-size: 14px;
    font-weight: 600;
}

.dashboard-exam-widget__footer {
    padding: 0 22px 18px;
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
}

.receivable-fee-widget {
    position: relative;
    background:
        radial-gradient(circle at top right, rgba(255, 255, 255, 0.08) 0, rgba(255, 255, 255, 0.08) 115px, transparent 116px),
        linear-gradient(135deg, #5143ec 0%, #7e39f1 100%);
    border-radius: 26px;
    box-shadow: 0 12px 26px rgba(56, 44, 143, 0.22);
    overflow: hidden;
    height: 100%;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.receivable-fee-widget::before {
    content: '';
    position: absolute;
    inset: 0 0 auto 0;
    height: 4px;
    background: rgba(255, 255, 255, 0.24);
}

.receivable-fee-widget:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.2);
}

.receivable-fee-widget__link {
    min-height: 100%;
    display: flex;
    flex-direction: column;
    color: inherit;
    text-decoration: none;
}

.receivable-fee-widget__link:hover,
.receivable-fee-widget__link:focus {
    color: inherit;
    text-decoration: none;
}

.receivable-fee-widget__body {
    position: relative;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
    padding: 24px 28px 18px;
    flex: 1;
}

.receivable-fee-widget__eyebrow {
    margin: 0 0 10px;
    color: rgba(255, 255, 255, 0.72);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1.4px;
    text-transform: uppercase;
}

.receivable-fee-widget__title {
    margin: 0 0 14px;
    color: #fff;
    font-size: 24px;
    font-weight: 700;
    line-height: 1.15;
}

.receivable-fee-widget__amount {
    margin: 0 0 10px;
    color: #fff;
    font-size: 46px;
    font-weight: 800;
    line-height: 1.05;
    letter-spacing: -0.6px;
    word-break: break-word;
}

.receivable-fee-widget__meta {
    margin: 0;
    max-width: 260px;
    color: rgba(255, 255, 255, 0.72);
    font-size: 14px;
    font-weight: 600;
    line-height: 1.5;
}

.receivable-fee-widget__icon {
    width: 70px;
    height: 70px;
    flex: 0 0 70px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.18);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2);
    color: #ffce34;
    font-size: 30px;
}

.receivable-fee-widget__footer {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin: 0 28px 24px;
    border-radius: 16px;
    padding: 14px 18px;
    background: rgba(255, 255, 255, 0.12);
    border: 2px solid rgba(255, 255, 255, 0.18);
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: 0.2px;
}

.receivable-fee-widget__footer i {
    transition: transform 0.3s ease;
}

.receivable-fee-widget:hover .receivable-fee-widget__footer i {
    transform: translateX(5px);
}

.fees-collection-widget {
    background:
        radial-gradient(circle at top right, rgba(255, 255, 255, 0.09) 0, rgba(255, 255, 255, 0.09) 110px, transparent 111px),
        linear-gradient(135deg, #157b77 0%, #1f9cbc 100%);
    border-radius: 26px;
    box-shadow: 0 12px 26px rgba(14, 87, 102, 0.24);
    overflow: hidden;
    height: 100%;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.fees-collection-widget:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.2);
}

.fees-collection-widget__inner {
    min-height: 0;
    padding: 22px 24px;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.fees-collection-widget__month {
    color: #fff;
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
    margin: 0 0 8px;
    text-transform: capitalize;
}

.fees-collection-widget__subtitle {
    margin: 0 0 14px;
    color: rgba(255, 255, 255, 0.72);
    font-size: 14px;
    font-weight: 600;
}

.fees-collection-widget__chart {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
}

.fees-collection-widget__meter-link {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    color: inherit;
    text-decoration: none;
}

.fees-collection-widget__meter-link:hover,
.fees-collection-widget__meter-link:focus {
    color: inherit;
    text-decoration: none;
}

.fees-collection-widget__ring {
    position: relative;
    width: 190px;
    height: 190px;
    transition: transform 0.3s ease;
}

.fees-collection-widget:hover .fees-collection-widget__ring {
    transform: scale(1.03);
}

.fees-collection-widget__svg {
    width: 190px;
    height: 190px;
}

.fees-collection-widget__track {
    fill: none;
    stroke: rgba(255, 255, 255, 0.18);
    stroke-width: 14;
}

.fees-collection-widget__progress {
    fill: none;
    stroke: url(#feesCollectionRingGradient);
    stroke-width: 14;
    stroke-linecap: round;
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
    transition: stroke-dashoffset 0.6s ease;
    filter: drop-shadow(0 4px 10px rgba(0, 158, 253, 0.22));
}

.fees-collection-widget__value {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 6px;
    color: #fff;
    font-weight: 600;
    width: 100%;
    pointer-events: none;
}

.fees-collection-widget__percent-row {
    display: flex;
    align-items: baseline;
    justify-content: center;
    gap: 4px;
    line-height: 1;
}

.fees-collection-widget__percent {
    font-size: 46px;
    line-height: 1;
    font-weight: 400;
}

.fees-collection-widget__percent-sign {
    font-size: 22px;
    line-height: 1;
    font-weight: 600;
}

.fees-collection-widget__ratio {
    color: rgba(255, 255, 255, 0.8);
    font-size: 20px;
    font-weight: 500;
    letter-spacing: 0.5px;
    line-height: 1;
}

.fees-collection-widget__legend {
    margin-top: 18px;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 18px;
    color: rgba(255, 255, 255, 0.82);
    font-size: 14px;
    font-weight: 600;
}

.fees-collection-widget__legend-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.fees-collection-widget__legend-dot {
    width: 14px;
    height: 14px;
    border-radius: 50%;
    display: inline-block;
}

.fees-collection-widget__legend-dot--unpaid {
    background: #f66b6b;
}

.fees-collection-widget__legend-dot--paid {
    background: rgba(255, 255, 255, 0.82);
}

.previous-balance-widget {
    background:
        radial-gradient(circle at top right, rgba(255, 255, 255, 0.08) 0, rgba(255, 255, 255, 0.08) 110px, transparent 111px),
        linear-gradient(135deg, #c85f00 0%, #e47f00 100%);
    border-radius: 26px;
    box-shadow: 0 12px 26px rgba(135, 76, 6, 0.24);
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 24px 20px;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.previous-balance-widget:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 40px rgba(0, 0, 0, 0.2);
}

.previous-balance-widget__title {
    margin: 0 0 18px;
    color: #fff;
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
}

.previous-balance-widget__badge {
    width: 84px;
    height: 84px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.12);
    border: 4px solid rgba(255, 255, 255, 0.32);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 44px;
    font-weight: 500;
    line-height: 1;
    margin-bottom: 18px;
    box-shadow: none;
}

.previous-balance-widget__amount {
    margin: 0;
    color: #fff;
    font-size: 46px;
    font-weight: 700;
    line-height: 1.1;
    letter-spacing: 0.4px;
}

.previous-balance-widget__meta {
    margin: 10px 0 0;
    color: rgba(255, 255, 255, 0.78);
    font-size: 14px;
    font-weight: 600;
    line-height: 1.4;
    text-align: center;
    max-width: 320px;
}

.previous-balance-widget__link {
    color: inherit;
    text-decoration: none;
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.previous-balance-widget__link:hover,
.previous-balance-widget__link:focus {
    color: inherit;
    text-decoration: none;
}

.attendance-overview-widget {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 26px;
    box-shadow: 0 12px 26px rgba(15, 23, 42, 0.08);
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    margin-bottom: 20px!important;
}

.attendance-overview-widget:hover {
    transform: translateY(-8px);
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.14);
}

.attendance-overview-widget__link {
    min-height: 100%;
    display: flex;
    flex-direction: column;
    color: inherit;
    text-decoration: none;
}

.attendance-overview-widget__link:hover,
.attendance-overview-widget__link:focus {
    color: inherit;
    text-decoration: none;
}

.attendance-overview-widget__inner {
    flex: 1;
    padding: 22px 26px 6px;
}

.attendance-overview-widget__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 16px;
}

.attendance-overview-widget__title {
    margin: 0 0 10px;
    color: #111827;
    font-size: 24px;
    font-weight: 700;
    line-height: 1.2;
}

.attendance-overview-widget__stats {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 12px;
    color: #374151;
    font-size: 14px;
    font-weight: 600;
}

.attendance-overview-widget__chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.attendance-overview-widget__chip-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    padding: 4px 10px;
    border-radius: 999px;
    color: #fff;
    font-size: 14px;
    font-weight: 700;
    line-height: 1;
}

.attendance-overview-widget__chip-badge--total {
    background: #f3f4f6;
    color: #374151;
}

.attendance-overview-widget__chip-badge--present {
    background: #16a34a;
    color: #fff;
}

.attendance-overview-widget__chip-badge--leave {
    background: #f59e0b;
    color: #fff;
}

.attendance-overview-widget__chip-badge--absent {
    background: #dc2626;
    color: #fff;
}

.attendance-overview-widget__percent {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 58px;
    padding: 6px 12px;
    border-radius: 999px;
    background: #f0fdf4;
    color: #166534;
    font-size: 15px;
    font-weight: 700;
    line-height: 1;
    white-space: nowrap;
}

.attendance-overview-widget__rows {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.attendance-overview-widget__row {
    display: grid;
    grid-template-columns: 44px minmax(0, 1fr) 34px minmax(42px, auto);
    gap: 10px;
    align-items: center;
}

.attendance-overview-widget__metric {
    color: #111827;
    font-size: 18px;
    font-weight: 500;
    line-height: 1;
}

.attendance-overview-widget__metric--right {
    text-align: right;
    font-size: 16px;
    color: #dc2626;
}

.attendance-overview-widget__label {
    color: #374151;
    font-size: 16px;
    font-weight: 500;
    text-align: right;
    line-height: 1.1;
}

.attendance-overview-widget__track-link {
    display: block;
    height: 100%;
    color: inherit;
    text-decoration: none;
}

.attendance-overview-widget__track-link:hover,
.attendance-overview-widget__track-link:focus {
    color: inherit;
    text-decoration: none;
}

.attendance-overview-widget__track {
    position: relative;
    height: 28px;
    border-radius: 10px;
    background: #f3f4f6;
    overflow: hidden;
}

.attendance-overview-widget__bar {
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 12px;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    line-height: 1;
    white-space: nowrap;
}

.attendance-overview-widget__bar--green {
    background: linear-gradient(90deg, #16a34a 0%, #4ade80 100%);
}

.attendance-overview-widget__bar--blue {
    background: linear-gradient(90deg, #16a34a 0%, #4ade80 100%);
}

.attendance-overview-widget__empty {
    padding: 24px 0 30px;
    text-align: center;
    color: #6b7280;
    font-size: 16px;
    font-weight: 600;
}

.attendance-overview-widget__footer {
    margin: 16px 38px 28px;
    display: block;
    padding: 14px 18px;
    text-align: center;
    text-decoration: none;
    color: #166534;
    background: #f0fdf4;
    border-radius: 18px;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.2px;
}

.attendance-overview-widget__footer:hover,
.attendance-overview-widget__footer:focus {
    color: #166534;
    text-decoration: none;
    background: #dcfce7;
}

.fees-collection-widget__legend-link {
    color: inherit;
    text-decoration: underline;
    text-underline-offset: 2px;
}

.fees-collection-widget__legend-link:hover,
.fees-collection-widget__legend-link:focus {
    color: inherit;
    text-decoration: underline;
}

@media (max-width: 991px) {
    .dashboard-summary-row {
        grid-template-columns: 1fr;
        grid-template-areas:
            "receivable"
            "collection"
            "dues";
    }

    .dashboard-activity-row {
        grid-template-columns: 1fr;
        grid-template-areas:
            "attendance"
            "exams";
    }
}

@media (max-width: 768px) {
    .dashboard-summary-row {
        grid-template-columns: 1fr;
        gap: 18px;
    }

    .dashboard-summary-shell {
        margin-bottom: 20px;
    }

    .dashboard-activity-shell {
        margin-bottom: 20px;
    }

    .dashboard-activity-row {
        gap: 18px;
    }

    .fees-collection-widget__inner {
        min-height: 0;
    }

    .receivable-fee-widget__body {
        padding: 24px 22px 18px;
    }

    .receivable-fee-widget__title {
        font-size: 24px;
    }

    .receivable-fee-widget__amount {
        font-size: 42px;
    }

    .receivable-fee-widget__icon {
        width: 74px;
        height: 74px;
        flex-basis: 74px;
        font-size: 30px;
        border-radius: 20px;
    }

    .receivable-fee-widget__footer,
    .attendance-overview-widget__footer {
        margin-left: 22px;
        margin-right: 22px;
    }

    .fees-collection-widget__month {
        font-size: 24px;
    }

    .fees-collection-widget__ring,
    .fees-collection-widget__svg {
        width: 160px;
        height: 160px;
    }

    .fees-collection-widget__percent {
        font-size: 44px;
    }

    .fees-collection-widget__ratio {
        font-size: 17px;
    }

    .previous-balance-widget {
        padding-top: 24px;
        padding-bottom: 24px;
    }

    .previous-balance-widget__title {
        font-size: 30px;
        margin-bottom: 14px;
    }

    .previous-balance-widget__badge {
        width: 84px;
        height: 84px;
        font-size: 44px;
        margin-bottom: 14px;
    }

    .previous-balance-widget__amount {
        font-size: 42px;
    }

    .previous-balance-widget__meta {
        font-size: 13px;
    }

    .attendance-overview-widget__stats {
        font-size: 14px;
    }

    .attendance-overview-widget__row {
        grid-template-columns: 38px minmax(0, 1fr) 30px minmax(36px, auto);
        gap: 8px;
    }

    .attendance-overview-widget__metric {
        font-size: 20px;
    }

    .attendance-overview-widget__metric--right,
    .attendance-overview-widget__label,
    .attendance-overview-widget__bar {
        font-size: 16px;
    }

    .dashboard-exam-widget__header {
        flex-direction: column;
        align-items: stretch;
        padding: 18px 18px 12px;
    }

    .dashboard-exam-widget__stats {
        padding: 0 18px 12px;
    }

    .dashboard-exam-widget__table-wrap {
        padding: 0 10px 12px;
    }

    .dashboard-exam-widget__table thead th,
    .dashboard-exam-widget__table td {
        padding: 10px 8px;
        font-size: 12px;
    }

    .dashboard-exam-widget__footer {
        padding: 0 18px 16px;
    }
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

            ?>

            <?php

            $div_col = 12;

            $div_rol = 12;

            $bar_chart = false;

            $line_chart = false;

            if ($this->rbac->hasPrivilege('staff_role_count_widget', 'can_view')) {

                $div_col = 9;

                $div_rol = 12;

            }
            $widget_col = array();

            if ($this->rbac->hasPrivilege('Monthly fees_collection_widget', 'can_view')) {

                $widget_col[0] = 1;

                $div_rol = 3;

            }

            if ($this->rbac->hasPrivilege('monthly_expense_widget', 'can_view')) {

                $widget_col[1] = 2;

                $div_rol = 3;

            }

            if ($this->rbac->hasPrivilege('student_count_widget', 'can_view')) {

                $widget_col[2] = 3;

                $div_rol = 3;

            }

            $div = sizeof($widget_col);

            if (!empty($widget_col)) {

                $widget = 12 / $div;

            } else {

                $widget = 12;

            }

            ?>
            <div class="col-md-12">
                <?php if ($this->rbac->hasPrivilege('Monthly fees_collection_widget', 'can_view')) { ?>
                <div class="dashboard-summary-shell">
                    <div class="row dashboard-summary-row">
                        <!-- Total Receiveable Fee Box -->
                        <div class="col col-lg col-md-4 col-sm-6 col-xs-12 dashboard-summary-item--receivable">
                            <div class="receivable-fee-widget">
                                <a href="<?php echo site_url('studentfee/fee_summary_report_view') ?>" target="_blank" class="receivable-fee-widget__link" aria-label="Open total receivable fee summary">
                                    <div class="receivable-fee-widget__body">
                                        <div class="receivable-fee-widget__content">
                                            <p class="receivable-fee-widget__eyebrow">Fee Summary</p>
                                            <p class="receivable-fee-widget__title">Total Receivable Fee</p>
                                            <p class="receivable-fee-widget__amount"><?php echo $currency_symbol . number_format(isset($total_receivable_fee) ? $total_receivable_fee : 0, 0); ?></p>
                                            <p class="receivable-fee-widget__meta">Current month student fee amount available for collection.</p>
                                        </div>
                                        <div class="receivable-fee-widget__icon">
                                            <i class="fa fa-credit-card"></i>
                                        </div>
                                    </div>
                                    <span class="receivable-fee-widget__footer">Open fee summary <i class="fa fa-arrow-circle-right"></i></span>
                                </a>
                            </div>
                        </div>

                        <!-- Monthly Fees Collection Box -->
                        <div class="col col-lg col-md-4 col-sm-6 col-xs-12 dashboard-summary-item--collection">
                            <?php
                            $monthly_fee_total = isset($total_fees) ? (int) $total_fees : 0;
                            $monthly_fee_paid = isset($total_paid) ? (int) $total_paid : 0;
                            $monthly_fee_pending = max(0, $monthly_fee_total - $monthly_fee_paid);
                            $monthly_fee_percentage = isset($fessprogressbar) ? round((float) $fessprogressbar) : 0;
                            $monthly_fee_percentage = max(0, min(100, $monthly_fee_percentage));
                            $monthly_fee_month_label = date('F') . ' - ' . date('Y');
                            $monthly_fee_circumference = 2 * pi() * 54;
                            $monthly_fee_offset = $monthly_fee_circumference - (($monthly_fee_percentage / 100) * $monthly_fee_circumference);
                            $pending_fee_report_url = site_url('studentfee/pendingFeeReport') . '?' . http_build_query(array(
                                'year' => date('Y'),
                                'month' => date('n'),
                                'class_id' => 'ALL',
                                'fee_type' => 'all',
                            ));
                            ?>
                            <div class="fees-collection-widget">
                                <div class="fees-collection-widget__inner">
                                    <p class="fees-collection-widget__month"><?php echo html_escape($monthly_fee_month_label); ?></p>
                                    <p class="fees-collection-widget__subtitle">Fee collection status</p>

                                    <div class="fees-collection-widget__chart">
                                        <a href="<?php echo site_url('studentfee/collection_report_dashboard') ?>" target="_blank" class="fees-collection-widget__meter-link" aria-label="Open monthly fees collection report">
                                            <div class="fees-collection-widget__ring">
                                                <svg class="fees-collection-widget__svg" viewBox="0 0 156 156" aria-hidden="true">
                                                    <defs>
                                                        <linearGradient id="feesCollectionRingGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                                            <stop offset="0%" stop-color="#eef2f3"></stop>
                                                            <stop offset="55%" stop-color="#eef2f3"></stop>
                                                            <stop offset="56%" stop-color="#f66b6b"></stop>
                                                            <stop offset="100%" stop-color="#f66b6b"></stop>
                                                        </linearGradient>
                                                    </defs>
                                                    <circle class="fees-collection-widget__track" cx="78" cy="78" r="54"></circle>
                                                    <circle class="fees-collection-widget__progress" cx="78" cy="78" r="54" style="stroke-dasharray: <?php echo number_format($monthly_fee_circumference, 2, '.', ''); ?>; stroke-dashoffset: <?php echo number_format($monthly_fee_offset, 2, '.', ''); ?>;"></circle>
                                                </svg>
                                                <div class="fees-collection-widget__value">
                                                    <div class="fees-collection-widget__percent-row">
                                                        <span class="fees-collection-widget__percent"><?php echo $monthly_fee_percentage; ?></span>
                                                        <span class="fees-collection-widget__percent-sign">%</span>
                                                    </div>
                                                    <div class="fees-collection-widget__ratio"><?php echo $monthly_fee_paid; ?>/<?php echo $monthly_fee_total; ?></div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="fees-collection-widget__legend">
                                        <span class="fees-collection-widget__legend-item">
                                            <span class="fees-collection-widget__legend-dot fees-collection-widget__legend-dot--unpaid"></span>
                                            <a href="<?php echo $pending_fee_report_url; ?>" target="_blank" rel="noopener noreferrer" class="fees-collection-widget__legend-link">Pending</a> (<?php echo $monthly_fee_pending; ?>)
                                        </span>
                                        <span class="fees-collection-widget__legend-item">
                                            <span class="fees-collection-widget__legend-dot fees-collection-widget__legend-dot--paid"></span>
                                            Paid (<?php echo $monthly_fee_paid; ?>)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Previous Balance Box -->
                        <div class="col col-lg col-md-4 col-sm-6 col-xs-12 dashboard-summary-item--dues">
                            <?php
                            $previous_balance_amount = isset($previous_balance_students_amount) ? (float) $previous_balance_students_amount : 0;
                            $previous_balance_students_count = isset($previous_balance_students_count) ? (int) $previous_balance_students_count : 0;
                            $previous_balance_only_amount = isset($previous_balance_only_amount) ? (float) $previous_balance_only_amount : 0;
                            ?>
                            <div class="previous-balance-widget">
                                <a href="<?php echo site_url('admin/admin/previousBalanceStudentsReport'); ?>" target="_blank" class="previous-balance-widget__link" aria-label="Open previous balance students report">
                                    <p class="previous-balance-widget__title">Prevoius Balance</p>
                                    <div class="previous-balance-widget__badge"><?php echo $previous_balance_students_count; ?></div>
                                    <p class="previous-balance-widget__amount"><?php echo $currency_symbol . number_format($previous_balance_amount, 0); ?></p>
                                    <p class="previous-balance-widget__meta">
                                        Previous months only: <?php echo $currency_symbol . number_format($previous_balance_only_amount, 0); ?>
                                    </p>
                                </a>
                            </div>
                        </div>

                        <!--    <div class="col-md-3 col-sm-6">

                                <div class="wrimagecard wrimagecard-topimage">

                                    <a href="<?php /*echo site_url('studentfee') */?>">

                                        <div class="wrimagecard-topimage_header" style="background-color:rgba(187, 120, 36, 0.1) ">

                                            <center><i class="fa fa-area-chart" style="color:#FAA21C"></i></center>

                                        </div>

                                        <div class="wrimagecard-topimage_title">

                                            <h4><?php /*echo $this->lang->line('monthly_fees_collection'); */?>

                                                <div class="pull-right badge"> <?php /*echo $currency_symbol . $month_collection; */?> </div></h4>

                                        </div>

                                    </a>

                                </div>

                            </div>-->

                        <!--<div class="col-md-3 col-sm-6">

                                <div class="info-box">

                                    <a href="<?php /*echo site_url('studentfee') */?>">

                                        <span class="info-box-icon bg-yellow"><i class="fa fa-money"></i></span>

                                        <div class="info-box-content">

                                            <span class="info-box-text"><?php /*echo $this->lang->line('monthly_fees_collection'); */?></span>

                                            <span class="info-box-number"><?php /*echo $currency_symbol . $month_collection; */?></span>

                                        </div>

                                    </a>

                                </div>
                                

                            </div>-->
                    </div>
                </div>
                <?php } ?>

                <?php
                $show_attendance_widget = $this->rbac->hasPrivilege('student_attendance', 'can_view');
                $show_exam_widget = $this->rbac->hasPrivilege('exam_group', 'can_view');
                $dashboard_activity_row_class = ($show_attendance_widget && $show_exam_widget) ? 'dashboard-activity-row' : 'dashboard-activity-row dashboard-activity-row--single';
                ?>
                <?php if ($show_attendance_widget || $show_exam_widget) { ?>
                <div class="dashboard-activity-shell">
                    <div class="<?php echo $dashboard_activity_row_class; ?>">
                        <?php if ($show_attendance_widget) { ?>
                        <div class="dashboard-activity-item dashboard-activity-item--attendance">
                            <?php
                            $attendance_widget_data = (isset($attendance_widget) && is_array($attendance_widget)) ? $attendance_widget : array();
                            $attendance_rows = isset($attendance_widget_data['rows']) ? $attendance_widget_data['rows'] : array();
                            $attendance_total_students = isset($attendance_widget_data['total_students']) ? (int) $attendance_widget_data['total_students'] : 0;
                            $attendance_present_students = isset($attendance_widget_data['present_students']) ? (int) $attendance_widget_data['present_students'] : 0;
                            $attendance_leave_students = isset($attendance_widget_data['leave_students']) ? (int) $attendance_widget_data['leave_students'] : 0;
                            $attendance_absent_students = isset($attendance_widget_data['absent_students']) ? (int) $attendance_widget_data['absent_students'] : 0;
                            $attendance_present_percentage = isset($attendance_widget_data['present_percentage']) ? (int) $attendance_widget_data['present_percentage'] : 0;
                            $attendance_date_label = isset($attendance_widget_data['date_label']) ? (string) $attendance_widget_data['date_label'] : "Today's Attendance";
                            $attendance_date_value = date($this->customlib->getSchoolDateFormat());
                            ?>
                            <div class="attendance-overview-widget">
                                <div class="attendance-overview-widget__inner">
                                    <p class="attendance-overview-widget__title"><?php echo html_escape($attendance_date_label); ?> Attendance</p>
                                    <div class="attendance-overview-widget__header">
                                        <div class="attendance-overview-widget__stats">
                                            <span class="attendance-overview-widget__chip">
                                                <span>Total:</span>
                                                <span class="attendance-overview-widget__chip-badge attendance-overview-widget__chip-badge--total"><?php echo $attendance_total_students; ?></span>
                                            </span>
                                            <span class="attendance-overview-widget__chip">
                                                <span>Present:</span>
                                                <span class="attendance-overview-widget__chip-badge attendance-overview-widget__chip-badge--present"><?php echo $attendance_present_students; ?></span>
                                            </span>
                                            <span class="attendance-overview-widget__chip">
                                                <span>Leave:</span>
                                                <span class="attendance-overview-widget__chip-badge attendance-overview-widget__chip-badge--leave"><?php echo $attendance_leave_students; ?></span>
                                            </span>
                                            <span class="attendance-overview-widget__chip">
                                                <span>Absent:</span>
                                                <span class="attendance-overview-widget__chip-badge attendance-overview-widget__chip-badge--absent"><?php echo $attendance_absent_students; ?></span>
                                            </span>
                                        </div>
                                        <span class="attendance-overview-widget__percent"><?php echo $attendance_present_percentage; ?>%</span>
                                    </div>

                                    <?php if (!empty($attendance_rows)) { ?>
                                    <div class="attendance-overview-widget__rows">
                                        <?php foreach ($attendance_rows as $attendance_index => $attendance_row) { ?>
                                            <?php
                                            $attendance_bar_class = ($attendance_index % 2 === 0) ? 'attendance-overview-widget__bar--green' : 'attendance-overview-widget__bar--blue';
                                            $attendance_bar_width = ((int) $attendance_row['present_students'] > 0)
                                                ? max(12, (int) $attendance_row['present_percentage'])
                                                : 0;
                                            $attendance_click_params = array(
                                                'class_id' => (int) $attendance_row['class_id'],
                                                'date' => $attendance_date_value,
                                            );

                                            if (!empty($attendance_row['section_id'])) {
                                                $attendance_click_params['section_id'] = (int) $attendance_row['section_id'];
                                                $attendance_click_params['autoload'] = 1;
                                            }

                                            $attendance_click_url = site_url('admin/stuattendence/index') . '?' . http_build_query($attendance_click_params);
                                            ?>
                                            <div class="attendance-overview-widget__row">
                                                <div class="attendance-overview-widget__metric"><?php echo (int) $attendance_row['total_students']; ?></div>
                                                <div class="attendance-overview-widget__track">
                                                    <a href="<?php echo $attendance_click_url; ?>" class="attendance-overview-widget__track-link" aria-label="Open attendance for <?php echo html_escape($attendance_row['class_label']); ?>">
                                                        <?php if ((int) $attendance_row['present_students'] > 0) { ?>
                                                            <div class="attendance-overview-widget__bar <?php echo $attendance_bar_class; ?>" style="width: <?php echo $attendance_bar_width; ?>%;">
                                                                <?php echo (int) $attendance_row['present_students']; ?>
                                                            </div>
                                                        <?php } ?>
                                                    </a>
                                                </div>
                                                <div class="attendance-overview-widget__metric attendance-overview-widget__metric--right"><?php echo (int) $attendance_row['absent_students']; ?></div>
                                                <div class="attendance-overview-widget__label"><?php echo html_escape($attendance_row['class_label']); ?></div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <?php } else { ?>
                                        <div class="attendance-overview-widget__empty">No attendance marked for today.</div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <?php if ($show_exam_widget) { ?>
                        <div class="dashboard-activity-item dashboard-activity-item--exams">
                            <?php
                            $quick_exam_widget_data = (isset($quick_exam_widget) && is_array($quick_exam_widget)) ? $quick_exam_widget : array();
                            $quick_exam_rows = isset($quick_exam_widget_data['rows']) ? $quick_exam_widget_data['rows'] : array();
                            $quick_exam_total_tests = isset($quick_exam_widget_data['total_tests']) ? (int) $quick_exam_widget_data['total_tests'] : 0;
                            $quick_exam_completed_tests = isset($quick_exam_widget_data['completed_tests']) ? (int) $quick_exam_widget_data['completed_tests'] : 0;
                            $quick_exam_pending_tests = isset($quick_exam_widget_data['pending_tests']) ? (int) $quick_exam_widget_data['pending_tests'] : 0;
                            ?>
                            <div class="dashboard-exam-widget">
                                <div class="dashboard-exam-widget__header">
                                    <div class="dashboard-exam-widget__title-wrap">
                                        <p class="dashboard-exam-widget__title">Test Management</p>
                                    </div>
                                    <a href="<?php echo site_url('admin/quickexam'); ?>" class="dashboard-exam-widget__action">Test Detail</a>
                                </div>

                                <div class="dashboard-exam-widget__stats">
                                    <span class="dashboard-exam-widget__pill">Tests <strong><?php echo $quick_exam_total_tests; ?></strong></span>
                                    <span class="dashboard-exam-widget__pill">Entered <strong><?php echo $quick_exam_completed_tests; ?></strong></span>
                                    <span class="dashboard-exam-widget__pill">Pending <strong><?php echo $quick_exam_pending_tests; ?></strong></span>
                                </div>

                                <?php if (!empty($quick_exam_rows)) { ?>
                                <div class="dashboard-exam-widget__table-wrap">
                                    <div class="dashboard-exam-widget__table-scroll">
                                        <table class="dashboard-exam-widget__table">
                                            <thead>
                                                <tr>
                                                    <th style="width: 42%;">Event Name</th>
                                                    <th style="width: 34%;">Class</th>
                                                    <th style="width: 12%;" title="Tests with marks entered">T</th>
                                                    <th style="width: 12%;" title="Tests still pending marks entry">P</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($quick_exam_rows as $quick_exam_row) { ?>
                                                <tr>
                                                    <td class="dashboard-exam-widget__event"><?php echo html_escape($quick_exam_row['event_name']); ?></td>
                                                    <td class="dashboard-exam-widget__class"><?php echo html_escape($quick_exam_row['class_label']); ?></td>
                                                    <td class="dashboard-exam-widget__metric dashboard-exam-widget__metric--done"><?php echo (int) $quick_exam_row['completed_tests']; ?></td>
                                                    <td class="dashboard-exam-widget__metric dashboard-exam-widget__metric--pending"><?php echo (int) $quick_exam_row['pending_tests']; ?></td>
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php } else { ?>
                                <div class="dashboard-exam-widget__empty">No quick tests found yet. Create tests from the quick exam page to see them here.</div>
                                <?php } ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>

                    <?php /* Monthly expenses widget hidden per dashboard customization. */ ?>

                    <?php /* Monthly income widget hidden per dashboard customization. */ ?>

                    <?php /* Student count widget hidden per dashboard customization. */ ?>

                    <?php /* Pending annual charges widget hidden per dashboard customization. */ ?>
                        <!--  <div class="col-md-3 col-sm-6">

                                <div class="wrimagecard wrimagecard-topimage">

                                    <a href="<?php /*echo site_url('student/search') */?>">

                                        <div class="wrimagecard-topimage_header" style="background-color:rgba(187, 120, 36, 0.1) ">

                                            <center><i class="fa fa-users" style="color:#FAA21C"></i></center>

                                        </div>

                                        <div class="wrimagecard-topimage_title">

                                            <h4><?php /*echo $this->lang->line('student'); */?>(s)<br>

                                                <div class="pull-right badge"> <?php /*echo $total_students; */?> </div></h4>

                                        </div>

                                    </a>

                                </div>

                            </div>-->

                        <!--<div class="col-md-3 col-sm-6">

                                <div class="info-box">

                                    <a href="<?php /*echo site_url('student/search') */?>">

                                        <span class="info-box-icon bg-aqua"><i class="fa fa-user"></i></span>

                                        <div class="info-box-content">

                                            <span class="info-box-text"><?php /*echo $this->lang->line('student'); */?></span>

                                            <span class="info-box-number"><?php /*echo $total_students; */?></span>

                                        </div>

                                    </a>

                                </div>

                            </div>-->

                    <?php if ($this->rbac->hasPrivilege('staff_role_count_widget', 'can_view')) {

                        ?>
                        <div class="col-lg-3 col-xs-6" style="display:none;">
                            <?php foreach ($roles as $key => $value) {

                                ?>
                                <!-- small box -->
                                <div class="small-box bg-red">
                                    <div class="inner">
                                        <h3><?php echo $value; ?></h3>
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
                        <!-- <div class="col-md-3 col-sm-6">

                                <?php /*foreach ($roles as $key => $value) {

                                    */?>

                                    <div class="wrimagecard wrimagecard-topimage">

                                        <a href="<?php /*echo site_url('student/search') */?>">

                                            <div class="wrimagecard-topimage_header" style="background-color:rgba(187, 120, 36, 0.1) ">

                                                <center><i class="fa fa-user-secret" style="color:#FAA21C"></i></center>

                                            </div>

                                            <div class="wrimagecard-topimage_title">

                                                <h4><?php /*echo $key; */?>(s)<br>

                                                    <div class="pull-right badge"> <?php /*echo $value; */?> </div></h4>

                                            </div>

                                        </a>

                                    </div>

                                    <?php /*break; */?>

                                <?php /*} */?>

                            </div>-->

                        <!--<div class="col-md-3">

                                <?php /*foreach ($roles as $key => $value) {

                                    */?>

                                    <div class="info-box">

                                        <a href="#">

                                            <span class="info-box-icon bg-yellow"><i class="fa fa-user-secret"></i></span>

                                            <div class="info-box-content">

                                                <span class="info-box-text"><?php /*echo $key; */?></span>

                                                <span class="info-box-number"><?php /*echo $value; */?></span>

                                            </div>

                                        </a>

                                    </div>

                                    <?php /*break; */?>

                                <?php /*} */?>

                            </div>-->

                    <?php } ?>
                </div>

                <!--<div class="row">

                        <div class="col-md-12 col-sm-12 col-lg-12">

                            <div class="box-body">

                                <img class="wrimagecard wrimagecard-topimage img-fluid" style="max-width: 100%; height: auto" width="100%" src="<?php /*echo base_url() */?>backend/images/dashboard.png" alt="">

                            </div>

                        </div>
                    </div>-->

            </div>

            <?php /* Fees overview and legacy attendance detail widgets hidden per dashboard customization. */ ?>
            <div class="col-md-12">
                <div class="row">
                    <!-- Quick Links - Full Width Modern Design -->
                    <div class="col-md-12 quicklinks-container">
                        <div class="quicklinks-box">
                            <div class="quicklinks-header">
                                <i class="fa fa-link"></i>
                                <h3 class="box-title">Quick Links</h3>
                            </div>
                            <div class="quicklinks-campus-links">
                                <a class="quicklinks-campus-link quicklinks-campus-link--boys" href="https://jfs-bc.edusystemic.com/admin/admin/dashboard" target="_blank" rel="noopener noreferrer">Boys Campus</a>
                                <a class="quicklinks-campus-link quicklinks-campus-link--girls" href="https://jfs.edusystemic.com/admin/admin/dashboard" target="_blank" rel="noopener noreferrer">Girls Campus</a>
                                <a class="quicklinks-campus-link quicklinks-campus-link--junior" href="https://jfs-jc.edusystemic.com/admin/admin/dashboard" target="_blank" rel="noopener noreferrer">Junior Campus</a>
                            </div>
                            <div class="quicklinks-grid">
                                <a class="quicklink-card color-1" href="<?php echo base_url(); ?>admin/users/logindetailreport">
                                    <div class="quicklink-icon">
                                        <i class="fa fa-key"></i>
                                    </div>
                                    <div class="quicklink-content">
                                        <p class="quicklink-title"><?php echo $this->lang->line('student')." Login Credential"; ?></p>
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
                    
                    <?php /* Monthly comparison widget hidden per dashboard customization. */ ?>
                    <canvas id="pieChart" style="height:0px;display: none;"></canvas>

                </div>
            </div>

            <?php /* Monthly charts, attendance graphs, and final calendar widget hidden per dashboard customization. */ ?>

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

                    <form role="form"  id="addevent_form" method="post" enctype="multipart/form-data" action="">

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event'); ?> <?php echo $this->lang->line('title'); ?></label>

                            <input class="form-control" name="title" id="input-field">

                            <span class="text-danger"><?php echo form_error('title'); ?></span>



                        </div>



                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>

                            <textarea name="description" class="form-control" id="desc-field"></textarea></div>

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event'); ?> <?php echo $this->lang->line('date'); ?></label>

                            <div class="input-group">

                                <div class="input-group-addon">

                                    <i class="fa fa-calendar"></i>

                                </div>

                                <input type="text" autocomplete="off" name="event_dates" class="form-control pull-right" id="date-field">

                            </div>

                            <!-- <input class="form-control" type="text" autocomplete="off"  name="event_dates" id="date-field"> --></div>

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event'); ?> <?php echo $this->lang->line('color'); ?></label>

                            <input type="hidden" name="eventcolor" autocomplete="off" id="eventcolor" class="form-control">

                        </div>

                        <div class="form-group col-md-12">

                            <?php //print_r($event_colors)  ?>



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

                            <br/>

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

                            </label> </div>



                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">

                            <input type="submit" class="btn btn-primary submit_addevent pull-right" value="<?php echo $this->lang->line('save'); ?>"></div> </form>

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

                    <form role="form"   method="post" id="updateevent_form"  enctype="multipart/form-data" action="" >

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event') ?><?php echo $this->lang->line('title') ?></label>

                            <input class="form-control" name="title" placeholder="Event Title" id="event_title">

                        </div>

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('description') ?></label>

                            <textarea name="description" class="form-control" placeholder="Event Description" id="event_desc"></textarea></div>

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event') ?><?php echo $this->lang->line('date') ?></label>

                            <div class="input-group">

                                <div class="input-group-addon">

                                    <i class="fa fa-calendar"></i>

                                </div>

                                <input type="text" autocomplete="off" name="eventdates" class="form-control pull-right" id="eventdates">

                            </div>

                            <!-- <input class="form-control" type="text" autocomplete="off" name="eventdates" placeholder="Event Dates" id="eventdates"> --></div>

                        <input type="hidden" name="eventid" id="eventid">

                        <div class="form-group col-md-12">

                            <label for="exampleInputEmail1"><?php echo $this->lang->line('event') ?><?php echo $this->lang->line('color') ?></label>

                            <input type="hidden" name="eventcolor" autocomplete="off" placeholder="Event Color" id="event_color" class="form-control">

                        </div>

                        <div class="form-group col-md-12">

                            <?php //print_r($event_colors)  ?>



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

<script type="text/javascript">

    var url;

    $(function () {

        url = base_url;

        var areaChartOptions = {

            showScale: true,

            scaleShowGridLines: false,

            scaleGridLineColor: "rgba(0,0,0,.05)",

            scaleGridLineWidth: 1,

            scaleShowHorizontalLines: true,

            scaleShowVerticalLines: true,

            bezierCurve: true,

            bezierCurveTension: 0.3,

            pointDot: false,

            pointDotRadius: 4,

            pointDotStrokeWidth: 1,

            pointHitDetectionRadius: 20,

            datasetStroke: true,

            datasetStrokeWidth: 2,

            datasetFill: true,



            maintainAspectRatio: true,

            responsive: true

        };

        var bar_chart = "<?php echo $bar_chart ?>";

        var line_chart = "<?php echo $line_chart ?>";

        if (line_chart) {





            var lineChartCanvas = $("#lineChart").get(0).getContext("2d");

            var lineChart = new Chart(lineChartCanvas);

            var lineChartOptions = areaChartOptions;

            lineChartOptions.datasetFill = false;

            var yearly_collection_array = <?php echo json_encode($yearly_collection) ?>;

            var yearly_expense_array = <?php echo json_encode($yearly_expense) ?>;


            var total_month = <?php echo json_encode($total_month) ?>;

            var areaChartData_expense_Income = {

                labels: total_month,

                datasets: [

                    {

                        label: "Expense",

                        fillColor: "rgba(215, 44, 44, 0.7)",

                        strokeColor: "rgba(215, 44, 44, 0.7)",

                        pointColor: "rgba(233, 30, 99, 0.9)",

                        pointStrokeColor: "#c1c7d1",

                        pointHighlightFill: "#fff",

                        pointHighlightStroke: "rgba(220,220,220,1)",

                        data: yearly_expense_array

                    },

                    {

                        label: "Collection",

                        fillColor: "rgba(102, 170, 24, 0.6)",

                        strokeColor: "rgba(102, 170, 24, 0.6)",

                        pointColor: "rgba(102, 170, 24, 0.9)",

                        pointStrokeColor: "rgba(102, 170, 24, 0.6)",

                        pointHighlightFill: "#fff",

                        pointHighlightStroke: "rgba(60,141,188,1)",

                        data: yearly_collection_array

                    }

                ]

            };





            lineChart.Line(areaChartData_expense_Income, lineChartOptions);

        }



        var current_month_days = <?php echo json_encode($current_month_days) ?>;

        var days_collection = <?php echo json_encode($days_collection) ?>;

        var days_expense = <?php echo json_encode($days_expense) ?>;
        console.log(days_collection);


        var areaChartData_classAttendence = {

            labels: current_month_days,

            datasets: [

                {

                    label: "Electronics",

                    fillColor: "rgba(102, 170, 24, 0.6)",

                    strokeColor: "rgba(102, 170, 24, 0.6)",

                    pointColor: "rgba(102, 170, 24, 0.6)",

                    pointStrokeColor: "#c1c7d1",

                    pointHighlightFill: "#fff",

                    pointHighlightStroke: "rgba(220,220,220,1)",

                    data: days_collection

                },

                {

                    label: "Digital Goods",

                    fillColor: "rgba(233, 30, 99, 0.9)",

                    strokeColor: "rgba(233, 30, 99, 0.9)",

                    pointColor: "rgba(233, 30, 99, 0.9)",

                    pointStrokeColor: "rgba(233, 30, 99, 0.9)",

                    pointHighlightFill: "rgba(233, 30, 99, 0.9)",

                    pointHighlightStroke: "rgba(60,141,188,1)",

                    data: days_expense

                }

            ]

        };

        if (bar_chart) {

            var barChartCanvas = $("#barChart").get(0).getContext("2d");

            var barChart = new Chart(barChartCanvas);



            var barChartData = areaChartData_classAttendence;
            barChartData.datasets[0].fillColor = "rgba(191, 214, 24, 1)";

            barChartData.datasets[0].strokeColor = "rgba(191, 214, 24, 1)";

            barChartData.datasets[0].pointColor = "rgba(191, 214, 24, 1)";

            barChartData.datasets[1].fillColor = "rgba(239, 94, 75, 1)";

            barChartData.datasets[1].strokeColor = "rgba(239, 94, 75, 1)";

            barChartData.datasets[1].pointColor = "rgba(239, 94, 75, 1)";


            var barChartOptions = {

                scaleBeginAtZero: true,

                scaleShowGridLines: true,

                scaleGridLineColor: "rgba(88, 96, 113,.05)",

                scaleGridLineWidth: 1,

                scaleShowHorizontalLines: true,

                scaleShowVerticalLines: true,

                barShowStroke: true,

                barStrokeWidth: 2,

                barValueSpacing: 5,

                barDatasetSpacing: 1,

                responsive: true,

                maintainAspectRatio: true

            };



            barChartOptions.datasetFill = false;

            barChart.Bar(barChartData, barChartOptions);

        }

        var attendenceGraph = base_url +'admin/admin/getStudentStats';

        $.ajax({

            url: attendenceGraph,

            type: 'GET',

            success: function (data, textStatus, jQxhr) {

                var data2 = JSON.parse(data);

                var chart = AmCharts.makeChart("chartdiv2", {

                    "type": "serial",

                    "theme": "light",

                    "dataProvider":data2,

                    "valueAxes": [{

                        "gridColor": "#FFFFFF",

                        "gridAlpha": 0.2,

                        "dashLength": 0

                    }],

                    "legend": {

                        "equalWidths": false,

                        "useGraphSettings": true,

                        "valueAlign": "left",

                        "valueWidth": 120

                    },

                    "gridAboveGraphs": true,

                    "startDuration": 1,

                    "graphs": [

                        {

                            "alphaField": "alpha",

                            "fillColorsField": "213488",

                            "balloonText": "Present [[value]]",

                            "dashLengthField": "dashLength",

                            "fillAlphas": 0.7,

                            "legendPeriodValueText": ": [[value.sum]]",

                            "legendValueText": "[[value]]",

                            "title": "Present Students",

                            "type": "column",

                            "valueField": "TOTAL_PRESENT",

                            "valueAxis": "distanceAxis",

                            "labelColor": "#213488",

                            "labelClass": "bold-label",

                            "labelText": " [[value]]",

                            "fontSize":"12"



                        },

                        {

                            "alphaField": "alpha",

                            "fillColorsField": "213488",

                            "balloonText": "Absent [[value]]",

                            "dashLengthField": "dashLength",

                            "fillAlphas": 0.7,

                            "legendPeriodValueText": ": [[value.sum]]",

                            "legendValueText": "[[value]]",

                            "title": "Absent Students",

                            "type": "column",

                            "valueField": "TOTAL_ABSENT",

                            "valueAxis": "distanceAxis",

                            "labelColor": "#213488",

                            "labelClass": "bold-label",

                            "labelText": " [[value]]",

                            "fontSize":"12"



                        }
                    ],

                    "chartCursor": {

                        "categoryBalloonEnabled": false,

                        "cursorAlpha": 0,

                        "zoomable": false

                    },

                    "categoryField": "DATE",

                    "categoryAxis": {

                        "gridPosition": "start",

                        "gridAlpha": 0,

                        "tickPosition": "start",

                        "tickLength": 20

                    }



                });

            }

        });



        var attendenceGraph2 = base_url +'admin/admin/getTeacherStats';

        $.ajax({

            url: attendenceGraph2,

            type: 'GET',

            success: function (data, textStatus, jQxhr) {

                var data2 = JSON.parse(data);

                var chart = AmCharts.makeChart("chartdiv3", {

                    "type": "serial",

                    "theme": "dark",

                    "dataProvider":data2,

                    "valueAxes": [{

                        "gridColor": "#FFFFFF",

                        "gridAlpha": 0.2,

                        "dashLength": 0

                    }],

                    "legend": {

                        "equalWidths": false,

                        "useGraphSettings": true,

                        "valueAlign": "left",

                        "valueWidth": 120

                    },

                    "gridAboveGraphs": true,

                    "startDuration": 1,

                    "graphs": [

                        {

                            "alphaField": "alpha",

                            "fillColors": "#ff7000",

                            "balloonText": "Present [[value]]",

                            "dashLengthField": "dashLength",

                            "fillAlphas": 0.7,

                            "legendPeriodValueText": ": [[value.sum]]",

                            "legendValueText": "[[value]]",

                            "title": "Present Teacher(s)",

                            "type": "column",

                            "valueField": "TOTAL_PRESENT",

                            "valueAxis": "distanceAxis",

                            "labelColor": "#213488",

                            "labelClass": "bold-label",

                            "labelText": " [[value]]",

                            "fontSize":"12"



                        },

                        {

                            "alphaField": "alpha",

                            "fillColors": "#01c421",

                            "balloonText": "Absent [[value]]",

                            "dashLengthField": "dashLength",

                            "fillAlphas": 0.7,

                            "legendPeriodValueText": ": [[value.sum]]",

                            "legendValueText": "[[value]]",

                            "title": "Absent Teacher(s)",

                            "type": "column",

                            "valueField": "TOTAL_ABSENT",

                            "valueAxis": "distanceAxis",

                            "labelColor": "#213488",

                            "labelClass": "bold-label",

                            "labelText": " [[value]]",

                            "fontSize":"12"



                        }],

                    "chartCursor": {

                        "categoryBalloonEnabled": false,

                        "cursorAlpha": 0,

                        "zoomable": false

                    },

                    "categoryField": "DATE",

                    "categoryAxis": {

                        "gridPosition": "start",

                        "gridAlpha": 0,

                        "tickPosition": "start",

                        "tickLength": 20

                    }



                });

            }

        });

    });



    $(document).ready(function () {

        $(document).on('click', '.close_notice', function () {

            var data = $(this).data();





            $.ajax({

                type: "POST",

                url: base_url + "admin/notification/read",

                data: {'notice': data.noticeid},

                dataType: "json",

                success: function (data) {

                    if (data.status == "fail") {



                        errorMsg(data.msg);

                    } else {

                        successMsg(data.msg);

                    }



                }

            });





        });

        var attendenceGraph = base_url +'admin/admin/getStudentStats';

        $( "#studentClass" ).each(function(index) {

            $("#studentClass").on('change',function(){

                var classID = $(this).val();

                $.ajax({

                    url: attendenceGraph,

                    type: 'GET',

                    data:{

                        "CLASS": classID

                    },

                    success: function (data, textStatus, jQxhr) {

                        var data2 = JSON.parse(data);

                        var chart = AmCharts.makeChart("chartdiv2", {

                            "type": "serial",

                            "theme": "light",

                            "dataProvider":data2,

                            "valueAxes": [{

                                "gridColor": "#FFFFFF",

                                "gridAlpha": 0.2,

                                "dashLength": 0

                            }],

                            "legend": {

                                "equalWidths": false,

                                "useGraphSettings": true,

                                "valueAlign": "left",

                                "valueWidth": 120

                            },

                            "gridAboveGraphs": true,

                            "startDuration": 1,

                            "graphs": [
                                {

                                    "alphaField": "alpha",

                                    "fillColorsField": "213488",

                                    "balloonText": "Present [[value]]",

                                    "dashLengthField": "dashLength",

                                    "fillAlphas": 0.7,

                                    "legendPeriodValueText": ": [[value.sum]]",

                                    "legendValueText": "[[value]]",

                                    "title": "Present Students",

                                    "type": "column",

                                    "valueField": "TOTAL_PRESENT",

                                    "valueAxis": "distanceAxis",

                                    "labelColor": "#213488",

                                    "labelClass": "bold-label",

                                    "labelText": " [[value]]",

                                    "fontSize":"12"



                                },

                                {

                                    "alphaField": "alpha",

                                    "fillColorsField": "213488",

                                    "balloonText": "Absent [[value]]",

                                    "dashLengthField": "dashLength",

                                    "fillAlphas": 0.7,

                                    "legendPeriodValueText": ": [[value.sum]]",

                                    "legendValueText": "[[value]]",

                                    "title": "Absent Students",

                                    "type": "column",

                                    "valueField": "TOTAL_ABSENT",

                                    "valueAxis": "distanceAxis",

                                    "labelColor": "#213488",

                                    "labelClass": "bold-label",

                                    "labelText": " [[value]]",

                                    "fontSize":"12"



                                }],



                            "chartCursor": {

                                "categoryBalloonEnabled": false,

                                "cursorAlpha": 0,

                                "zoomable": false

                            },

                            "categoryField": "DATE",

                            "categoryAxis": {

                                "gridPosition": "start",

                                "gridAlpha": 0,

                                "tickPosition": "start",

                                "tickLength": 20

                            }



                        });

                    }

                });

            });

        });

    });

</script>

<style>

    #chartdiv2 {

        width        : 100%;

        height		: 250px;

        font-size	: 11px;

    }

    #chartdiv3 {

        width		: 100%;

        height		: 250px;

        font-size	: 11px;

    }

</style>

<script src="<?php echo base_url(); ?>backend/amcharts2/amcharts.js"></script>

<script src="<?php echo base_url(); ?>backend/amcharts2/serial.js"></script>


<script src="<?php echo base_url(); ?>backend/amcharts2/export.min.js"></script>

<link rel="stylesheet" href="<?php echo base_url(); ?>backend/amcharts2/export.css" type="text/css" media="all">

<script src="<?php echo base_url(); ?>backend/amcharts2/light.js"></script>
<script>
    $(function () {
        // - PIE CHART -
        // -------------
        // Get context with jQuery - using jQuery's .get() method.
        var pieChartCanvas = $('#pieChart').get(0).getContext('2d');
        var pieChart       = new Chart(pieChartCanvas);
        var PieData        = [
            {
                value    : <?php echo  $month_collection; ?>,
                color    : '#3FB1C1',
                highlight: '#3FE5C1',
                label    : 'Month Collection'
            },
            {
                value    : <?php echo $month_expense; ?>,
                color    : '#EF5E4B',
                highlight: '#EF414B',
                label    : 'Month Expense'
            },
            {
                value    : <?php echo $month_collection-$month_expense; ?>,
                color    : '#BFD618',
                highlight: '#B8F21B',
                label    : 'Profit'
            }
        ];
        var pieOptions     = {
            // Boolean - Whether we should show a stroke on each segment
            segmentShowStroke    : true,
            // String - The colour of each segment stroke
            segmentStrokeColor   : '#fff',
            // Number - The width of each segment stroke
            segmentStrokeWidth   : 1,
            // Number - The percentage of the chart that we cut out of the middle
            percentageInnerCutout: 50, // This is 0 for Pie charts
            // Number - Amount of animation steps
            animationSteps       : 100,
            // String - Animation easing effect
            animationEasing      : 'easeOutBounce',
            // Boolean - Whether we animate the rotation of the Doughnut
            animateRotate        : true,
            // Boolean - Whether we animate scaling the Doughnut from the centre
            animateScale         : false,
            // Boolean - whether to make the chart responsive to window resizing
            responsive           : true,
            // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
            maintainAspectRatio  : false,
            // String - A legend template
            legendTemplate       : '<ul class=\'<%=name.toLowerCase()%>-legend\'><% for (var i=0; i<segments.length; i++){%><li><span style=\'background-color:<%=segments[i].fillColor%>\'></span><%if(segments[i].label){%><%=segments[i].label%><%}%></li><%}%></ul>',
            // String - A tooltip template
            tooltipTemplate      : '<%=value %> <%=label%> Orders'
        };
        // Create pie or douhnut chart
        // You can switch between pie and douhnut using the method below.
        pieChart.Doughnut(PieData, pieOptions);
        // -----------------
        // - END PIE CHART -
        // -----------------
    })
</script>

<!--<script src="<?php /*echo base_url(); */?>backend/js/dashboardGraph.js"></script>-->

<!--<script src="https://www.amcharts.com/lib/3/amcharts.js"></script>

<script src="https://www.amcharts.com/lib/3/serial.js"></script>

<script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>

<link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />

<script src="https://www.amcharts.com/lib/3/themes/light.js"></script>-->

