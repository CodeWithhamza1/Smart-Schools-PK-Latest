<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#133b73">
    <title><?php echo $name; ?> | Login Portal</title>
    <link href="<?php echo base_url(); ?>uploads/school_content/admin_small_logo/<?php $this->setting_model->getAdminsmalllogo(); ?>" rel="shortcut icon" type="image/x-icon">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo base_url(); ?>backend/usertemplate/assets/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap">

    <style>
        :root {
            --bg-1: #08162f;
            --bg-2: #0e2d56;
            --bg-3: #18477f;
            --text: #e6efff;
            --muted: #bdd0f0;
            --card: rgba(255, 255, 255, 0.1);
            --card-border: rgba(255, 255, 255, 0.2);
            --staff: #4f9b48;
            --staff-hover: #44853f;
            --portal: #2a6a9e;
            --portal-hover: #245b88;
        }

        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            min-height: 100%;
            font-family: 'Manrope', sans-serif;
            background: #061225;
            color: var(--text);
        }

        .chooser-page {
            min-height: 100vh;
            padding: 30px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(1000px 460px at -10% -10%, rgba(61, 125, 232, 0.38), transparent 65%),
                radial-gradient(600px 360px at 110% 0%, rgba(26, 176, 140, 0.28), transparent 55%),
                linear-gradient(135deg, var(--bg-1) 0%, var(--bg-2) 56%, var(--bg-3) 100%);
        }

        .chooser-card {
            width: 100%;
            max-width: 760px;
            border-radius: 24px;
            padding: 34px 30px 26px;
            background: var(--card);
            border: 1px solid var(--card-border);
            box-shadow: 0 28px 60px rgba(0, 0, 0, 0.35);
            backdrop-filter: blur(7px);
            text-align: center;
        }

        .logo-wrap {
            width: 160px;
            height: 160px;
            margin: 0 auto 14px;
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }
        .logo-wrap img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .school-title {
            margin: 0;
            font-size: 34px;
            line-height: 1.18;
            font-weight: 800;
            color: #fff;
        }

        .subtitle {
            margin: 12px auto 0;
            max-width: 560px;
            font-size: 17px;
            color: var(--muted);
            line-height: 1.7;
        }

        .actions {
            margin-top: 26px;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .login-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-width: 236px;
            border-radius: 999px;
            padding: 14px 22px;
            text-decoration: none;
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: .2px;
            border: 1px solid transparent;
            transition: all .2s ease;
        }
        .login-btn:hover, .login-btn:focus {
            text-decoration: none;
            color: #fff;
            transform: translateY(-1px);
        }
        .btn-staff { background: var(--staff); }
        .btn-staff:hover, .btn-staff:focus { background: var(--staff-hover); }
        .btn-user { background: var(--portal); }
        .btn-user:hover, .btn-user:focus { background: var(--portal-hover); }

        .portal-note {
            margin: 18px 0 0;
            font-size: 13px;
            color: #d8e6ff;
        }

        .brand-strip {
            margin-top: 22px;
            padding-top: 16px;
            border-top: 1px dashed rgba(255, 255, 255, 0.24);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #c7d9f9;
        }
        .brand-strip i { color: #8ab6ff; }

        @media (max-width: 768px) {
            .chooser-card {
                border-radius: 18px;
                padding: 26px 18px 20px;
            }
            .logo-wrap {
                width: 128px;
                height: 128px;
                border-radius: 18px;
            }
            .school-title { font-size: 27px; }
            .subtitle { font-size: 15px; }
            .login-btn {
                width: 100%;
                min-width: 0;
                font-size: 15px;
                padding: 13px 18px;
            }
        }
    </style>
</head>
<body>
<main class="chooser-page">
    <section class="chooser-card">
        <div class="logo-wrap">
            <img src="<?php echo base_url(); ?>uploads/school_content/logo/<?php echo $settinglist[0]['image']; ?>" alt="<?php echo $name; ?> logo">
        </div>

        <h1 class="school-title"><?php echo $name; ?></h1>
        <p class="subtitle">Please sign in to continue. Choose your portal to access dashboard, communication, and academic services.</p>

        <div class="actions">
            <a class="login-btn btn-staff" href="<?php echo base_url(); ?>site/login">
                <i class="fa fa-briefcase"></i>
                Staff/Admin Login
            </a>
            <a class="login-btn btn-user" href="<?php echo base_url(); ?>site/userlogin">
                <i class="fa fa-graduation-cap"></i>
                Student/Parent Login
            </a>
        </div>

        <p class="portal-note">Secure portals with role-based access.</p>

        <div class="brand-strip">
            <i class="fa fa-shield"></i>
            <span>Smart Institute Management System</span>
        </div>
    </section>
</main>
</body>
</html>
