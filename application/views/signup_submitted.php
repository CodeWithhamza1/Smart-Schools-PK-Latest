<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#11325b">
    <title>Request Submitted : <?php echo $name; ?></title>
    <link href="<?php echo base_url(); ?>uploads/school_content/admin_small_logo/<?php $this->setting_model->getAdminsmalllogo(); ?>" rel="shortcut icon" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <style>
        :root {
            --brand-primary: #174ea6;
            --brand-dark: #0f2744;
            --surface: #ffffff;
            --text-main: #1f2937;
            --text-soft: #64748b;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            color: var(--text-main);
            background: linear-gradient(135deg, #0c2240 0%, #153765 45%, #26538c 100%);
            min-height: 100vh;
        }
        .auth-wrap {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 28px 14px;
        }
        .submitted-panel {
            width: 100%;
            max-width: 500px;
            background: var(--surface);
            border-radius: 16px;
            padding: 36px 30px 28px;
            text-align: center;
            box-shadow: 0 25px 65px rgba(0, 0, 0, 0.35);
        }
        .status-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #e8f2ff;
            color: var(--brand-primary);
            font-size: 28px;
            margin-bottom: 16px;
        }
        h3 {
            margin: 0 0 8px;
            color: var(--brand-dark);
            font-weight: 700;
            font-size: 24px;
        }
        p {
            margin: 0;
            color: var(--text-soft);
            font-size: 14px;
            line-height: 1.7;
        }
        .mail-error {
            margin-top: 16px;
            padding: 10px 12px;
            border-radius: 10px;
            background: #fff1f1;
            color: #b42318;
            font-size: 13px;
            text-align: left;
            word-break: break-word;
        }
        .login-link {
            display: inline-block;
            margin-top: 20px;
            font-size: 13px;
            color: var(--brand-primary);
            text-decoration: none;
        }
        .login-link:hover { color: var(--brand-dark); text-decoration: none; }
    </style>
</head>
<body>
<div class="auth-wrap">
    <section class="submitted-panel">
        <div class="status-icon">
            <i class="fa fa-clock-o"></i>
        </div>
        <h3>Your request is submitted</h3>
        <p>Please wait while our team reviews your school sign-up request.</p>
        <?php if ($this->session->flashdata('mail_error')) { ?>
            <div class="mail-error"><?php echo $this->session->flashdata('mail_error'); ?></div>
        <?php } ?>
        <a href="<?php echo site_url('site/login') ?>" class="login-link">
            <i class="fa fa-sign-in"></i> Back to login
        </a>
    </section>
</div>
</body>
</html>
