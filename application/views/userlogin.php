<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#11325b">
    <title>Login : <?php echo $name; ?></title>
    <link href="<?php echo base_url(); ?>uploads/school_content/admin_small_logo/<?php $this->setting_model->getAdminsmalllogo(); ?>" rel="shortcut icon" type="image/x-icon">

    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/css/jquery.mCustomScrollbar.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">

    <style>
        :root {
            --brand-primary: #174ea6;
            --brand-secondary: #2f855a;
            --brand-dark: #0f2744;
            --surface: #ffffff;
            --surface-soft: #f4f8ff;
            --text-main: #1f2937;
            --text-soft: #64748b;
            --danger: #d14343;
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
        .auth-grid {
            width: 100%;
            max-width: 1180px;
            display: grid;
            grid-template-columns: 430px 1fr;
            border-radius: 18px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 65px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(5px);
        }
        .panel-left {
            background: var(--surface);
            padding: 32px 30px 24px;
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
        .alert {
            border-radius: 10px;
            font-size: 13px;
            padding: 10px 12px;
            margin-bottom: 10px;
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
            height: 44px;
            border: 1px solid #d6e0f0;
            border-radius: 10px;
            padding: 10px 12px 10px 38px;
            font-size: 14px;
            box-shadow: none;
            transition: border-color .2s, box-shadow .2s;
        }
        .form-control:focus {
            border-color: #7aa6e8;
            box-shadow: 0 0 0 3px rgba(23, 78, 166, 0.12);
            outline: none;
        }
        .text-danger { font-size: 12px; display: block; margin-top: 4px; }
        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 2px 0 12px;
            font-size: 13px;
            color: var(--text-soft);
        }
        .remember-row input {
            margin: 0;
        }
        .captcha-row {
            display: grid;
            grid-template-columns: 1fr 120px;
            gap: 10px;
            margin-bottom: 10px;
            align-items: center;
        }
        .captcha-box {
            background: var(--surface-soft);
            border: 1px solid #d8e4f5;
            border-radius: 10px;
            padding: 8px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 44px;
        }
        .catpcha {
            color: var(--brand-primary);
            cursor: pointer;
            font-size: 16px;
            margin-left: 10px;
        }
        .btn-submit {
            width: 100%;
            border: 0;
            border-radius: 10px;
            background: linear-gradient(90deg, var(--brand-primary), #2e6fcd);
            color: #fff;
            height: 44px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 8px;
        }
        .btn-submit:hover { opacity: 0.95; }
        .login-fields { border: 0; margin: 0; padding: 0; min-width: 0; }
        .login-fields[disabled] { opacity: 0.55; pointer-events: none; user-select: none; }
        .login-fields[disabled] .btn-submit { cursor: not-allowed; background: #8a9bb3; }
        .lock-countdown { display: block; margin-top: 6px; font-weight: 600; }
        .forgot-link {
            display: inline-block;
            margin-top: 14px;
            margin-right: 14px;
            font-size: 13px;
            color: var(--brand-primary);
            text-decoration: none;
        }
        .signup-link {
            display: inline-block;
            margin-top: 14px;
            font-size: 13px;
            color: var(--brand-primary);
            text-decoration: none;
        }
        .signup-link:hover,
        .forgot-link:hover { color: var(--brand-dark); text-decoration: none; }
        .panel-right {
            padding: 32px 28px;
            color: #fff;
            background: linear-gradient(145deg, rgba(20, 53, 96, .78), rgba(13, 39, 76, .82));
        }
        .news-title {
            font-size: 24px;
            font-weight: 600;
            margin: 0 0 18px;
            line-height: 1.3;
        }
        .news-wrap {
            height: 460px;
            overflow: hidden;
            padding-right: 10px;
        }
        .news-item {
            padding: 14px 0;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.2);
        }
        .news-item h4 {
            margin: 0 0 8px;
            font-size: 16px;
            font-weight: 600;
            color: #fff;
        }
        .news-item p {
            margin: 0;
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
            line-height: 1.65;
        }
        .news-item a { color: #8fd3ff; }
        @media (max-width: 991px) {
            .auth-grid { grid-template-columns: 1fr; }
            .panel-right { display: none; }
            .panel-left { border-radius: 16px; }
        }
    </style>
</head>
<body>
<div class="auth-wrap">
    <?php
    $empty_notice = empty($notice);
    ?>
    <div class="auth-grid">
        <section class="panel-left">
            <div class="brand-logo">
                <img src="<?php echo base_url(); ?>uploads/school_content/admin_logo/<?php $this->setting_model->getAdminlogo(); ?>" alt="School Logo">
            </div>
            <h3 class="panel-title"><?php echo $this->lang->line('user_login'); ?></h3>
            <p class="panel-subtitle">Use your credentials to access your portal.</p>

            <?php if (isset($error_message)) { ?>
                <div class="alert alert-danger"><?php echo $error_message; ?>
                    <?php if (!empty($locked_for)) { ?>
                        <span class="lock-countdown">Sign-in is disabled. Time remaining: <span id="lock_countdown" data-seconds="<?php echo (int) $locked_for; ?>"><?php echo sprintf('%02d:%02d', floor($locked_for / 60), $locked_for % 60); ?></span></span>
                    <?php } ?>
                </div>
            <?php } ?>
            <?php if ($this->session->flashdata('message')) { ?>
                <div class="alert alert-success"><?php echo $this->session->flashdata('message'); ?></div>
            <?php } ?>
            <?php if ($this->session->flashdata('disable_message')) { ?>
                <div class="alert alert-danger"><?php echo $this->session->flashdata('disable_message'); ?></div>
            <?php } ?>

            <form action="<?php echo site_url('site/userlogin') ?>" method="post" autocomplete="off">
                <?php echo $this->customlib->getCSRF(); ?>
                <fieldset class="login-fields" <?php echo !empty($locked_for) ? 'disabled aria-disabled="true"' : ''; ?>>
                <div class="input-wrap">
                    <i class="fa fa-envelope"></i>
                    <input type="text" name="username" placeholder="<?php echo $this->lang->line('username'); ?>" value="<?php echo set_value('username') ?>" class="form-control">
                    <span class="text-danger"><?php echo form_error('username'); ?></span>
                </div>
                <div class="input-wrap">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" placeholder="<?php echo $this->lang->line('password'); ?>" value="<?php echo set_value('password') ?>" class="form-control">
                    <span class="text-danger"><?php echo form_error('password'); ?></span>
                </div>
                <label class="remember-row">
                    <input type="checkbox" name="remember_me" value="1" <?php echo set_checkbox('remember_me', '1'); ?>>
                    <span>Remember me</span>
                </label>

                <?php if ($is_captcha && empty($locked_for)) { ?>
                    <div class="captcha-row">
                        <div class="captcha-box">
                            <span id="captcha_image"><?php echo $captcha_image; ?></span>
                            <span title="Refresh Captcha" class="fa fa-refresh catpcha" onclick="refreshCaptcha()"></span>
                        </div>
                        <div>
                            <input type="text" name="captcha" placeholder="<?php echo $this->lang->line('captcha'); ?>" class="form-control">
                            <span class="text-danger"><?php echo form_error('captcha'); ?></span>
                        </div>
                    </div>
                <?php } ?>

                <button type="submit" class="btn-submit"><?php echo $this->lang->line('sign_in'); ?></button>
                </fieldset>
            </form>

            <a href="<?php echo site_url('site/ufpassword') ?>" class="forgot-link">
                <i class="fa fa-key"></i> <?php echo $this->lang->line('forgot_password'); ?>?
            </a>
            <a href="<?php echo site_url('site/signup') ?>" class="signup-link">
                <i class="fa fa-user-plus"></i> Sign Up
            </a>
        </section>

        <aside class="panel-right">
            <h3 class="news-title"><?php echo $this->lang->line('what_is_new_in'); ?> <?php echo $school['name']; ?></h3>
            <div class="news-wrap mCustomScrollbar">
                <?php if (!$empty_notice) { ?>
                    <?php foreach ($notice as $notice_value) { ?>
                        <div class="news-item">
                            <h4><?php echo $notice_value['title']; ?></h4>
                            <?php
                            $string = strip_tags($notice_value['description']);
                            if (strlen($string) > 130) {
                                $stringCut = substr($string, 0, 130);
                                $endPoint = strrpos($stringCut, ' ');
                                $string = $endPoint ? substr($stringCut, 0, $endPoint) : $stringCut;
                                $string .= '... <a href="' . site_url('read/' . $notice_value['slug']) . '" target="_blank">' . $this->lang->line('read') . ' ' . $this->lang->line('more') . '</a>';
                            }
                            echo '<p>' . $string . '</p>';
                            ?>
                        </div>
                    <?php } ?>
                <?php } else { ?>
                    <div class="news-item">
                        <p>No latest notice found.</p>
                    </div>
                <?php } ?>
            </div>
        </aside>
    </div>
</div>

<script src="<?php echo base_url(); ?>backend/usertemplate/assets/js/jquery-1.11.1.min.js"></script>
<script src="<?php echo base_url(); ?>backend/usertemplate/assets/bootstrap/js/bootstrap.min.js"></script>
<script src="<?php echo base_url(); ?>backend/usertemplate/assets/js/jquery.mCustomScrollbar.min.js"></script>
<script>
    function refreshCaptcha() {
        $.ajax({
            type: "POST",
            url: "<?php echo base_url('site/refreshCaptcha'); ?>",
            data: {},
            success: function (captcha) {
                $("#captcha_image").html(captcha);
            }
        });
    }

    // Lockout countdown. This is only a display: the server refuses every sign-in from this client until the
    // lock ends, even if the form is re-enabled in dev tools. When it reaches zero the page reloads and the
    // server decides whether the form comes back.
    (function () {
        var el = document.getElementById('lock_countdown');
        if (!el) return;
        var endsAt = Date.now() + parseInt(el.getAttribute('data-seconds'), 10) * 1000;
        var timer = setInterval(function () {
            var left = Math.max(0, Math.ceil((endsAt - Date.now()) / 1000));
            el.textContent = ('0' + Math.floor(left / 60)).slice(-2) + ':' + ('0' + (left % 60)).slice(-2);
            if (left === 0) {
                clearInterval(timer);
                window.location.replace(window.location.pathname);
            }
        }, 1000);
    })();
</script>
</body>
</html>
