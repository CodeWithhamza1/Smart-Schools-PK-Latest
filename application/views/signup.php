<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#11325b">
    <title>Sign Up : <?php echo $name; ?></title>
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
        .signup-panel {
            width: 100%;
            max-width: 520px;
            background: var(--surface);
            border-radius: 16px;
            padding: 32px 30px 24px;
            box-shadow: 0 25px 65px rgba(0, 0, 0, 0.35);
        }
        .brand-logo {
            text-align: center;
            margin-bottom: 16px;
        }
        .brand-logo img {
            max-width: 100%;
            max-height: 70px;
        }
        .panel-title {
            margin: 10px 0 4px;
            text-align: center;
            color: var(--brand-dark);
            font-weight: 700;
            font-size: 24px;
        }
        .panel-subtitle {
            margin: 0 0 24px;
            text-align: center;
            color: var(--text-soft);
            font-size: 13px;
        }
        .input-wrap { position: relative; margin-bottom: 12px; }
        .input-wrap .fa {
            position: absolute;
            left: 14px;
            top: 14px;
            color: #8aa0bf;
            font-size: 14px;
        }
        .form-control {
            width: 100%;
            min-height: 44px;
            border: 1px solid #d6e0f0;
            border-radius: 10px;
            padding: 10px 12px 10px 38px;
            font-size: 14px;
            box-shadow: none;
            transition: border-color .2s, box-shadow .2s;
        }
        textarea.form-control {
            min-height: 88px;
            resize: vertical;
        }
        .form-control:focus {
            border-color: #7aa6e8;
            box-shadow: 0 0 0 3px rgba(23, 78, 166, 0.12);
            outline: none;
        }
        .text-danger { font-size: 12px; display: block; margin-top: 4px; }
        .btn-submit {
            width: 100%;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(90deg, var(--brand-primary), #2e6fcd);
            color: #fff;
            min-height: 44px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 8px;
        }
        .login-link {
            display: inline-block;
            margin-top: 14px;
            font-size: 13px;
            color: var(--brand-primary);
            text-decoration: none;
        }
        .login-link:hover { color: var(--brand-dark); text-decoration: none; }
    </style>
</head>
<body>
<div class="auth-wrap">
    <section class="signup-panel">
        <div class="brand-logo">
            <img src="<?php echo base_url(); ?>uploads/school_content/admin_logo/<?php $this->setting_model->getAdminlogo(); ?>" alt="School Logo">
        </div>
        <h3 class="panel-title">Sign Up</h3>
        <p class="panel-subtitle">Submit your school details and our team will review your request.</p>

        <form action="<?php echo site_url('site/signup') ?>" method="post" autocomplete="off">
            <?php echo $this->customlib->getCSRF(); ?>
            <div class="input-wrap">
                <i class="fa fa-user"></i>
                <input type="text" name="full_name" placeholder="Full Name" value="<?php echo set_value('full_name'); ?>" class="form-control">
                <span class="text-danger"><?php echo form_error('full_name'); ?></span>
            </div>
            <div class="input-wrap">
                <i class="fa fa-phone"></i>
                <input type="text" name="phone" placeholder="Phone Number" value="<?php echo set_value('phone'); ?>" class="form-control">
                <span class="text-danger"><?php echo form_error('phone'); ?></span>
            </div>
            <div class="input-wrap">
                <i class="fa fa-envelope"></i>
                <input type="text" name="email" placeholder="Email Address" value="<?php echo set_value('email'); ?>" class="form-control">
                <span class="text-danger"><?php echo form_error('email'); ?></span>
            </div>
            <div class="input-wrap">
                <i class="fa fa-building"></i>
                <input type="text" name="school_name" placeholder="School Name" value="<?php echo set_value('school_name'); ?>" class="form-control">
                <span class="text-danger"><?php echo form_error('school_name'); ?></span>
            </div>
            <div class="input-wrap">
                <i class="fa fa-map-marker"></i>
                <textarea name="school_address" placeholder="School Address" class="form-control"><?php echo set_value('school_address'); ?></textarea>
                <span class="text-danger"><?php echo form_error('school_address'); ?></span>
            </div>
            <div class="input-wrap">
                <i class="fa fa-map"></i>
                <input type="text" name="city" placeholder="City" value="<?php echo set_value('city'); ?>" class="form-control">
                <span class="text-danger"><?php echo form_error('city'); ?></span>
            </div>

            <button type="submit" class="btn-submit">Submit Request</button>
        </form>

        <a href="<?php echo site_url('site/login') ?>" class="login-link">
            <i class="fa fa-sign-in"></i> Back to login
        </a>
    </section>
</div>
</body>
</html>
