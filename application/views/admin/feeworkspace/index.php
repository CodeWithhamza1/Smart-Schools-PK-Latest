<style>
    .fee-workspace-page { color: #24324a; }
    .fee-workspace-page .workspace-hero {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 18px;
        margin-bottom: 18px;
        padding: 20px 22px;
        border-radius: 8px;
        background: linear-gradient(135deg, #17233c 0%, #22385f 100%);
        color: #fff;
        box-shadow: 0 4px 14px rgba(23, 35, 60, .14);
    }
    .fee-workspace-page .workspace-hero h2 { margin: 0 0 5px; color: #fff; font-size: 23px; }
    .fee-workspace-page .workspace-hero p { margin: 0; color: rgba(255, 255, 255, .78); }
    .fee-workspace-page .workspace-label {
        display: inline-block;
        padding: 5px 10px;
        border: 1px solid rgba(255, 255, 255, .25);
        border-radius: 14px;
        background: rgba(255, 255, 255, .08);
        color: #fff;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .4px;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .fee-workspace-page .workspace-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-bottom: 18px;
        padding: 8px;
        border: 1px solid #e2e8f0;
        border-radius: 7px;
        background: #fff;
        box-shadow: 0 2px 7px rgba(23, 35, 60, .06);
    }
    .fee-workspace-page .workspace-nav a {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 12px;
        border-radius: 5px;
        color: #526076;
        font-size: 13px;
        font-weight: 600;
        transition: background .15s ease, color .15s ease;
    }
    .fee-workspace-page .workspace-nav a:hover { background: #f1f5f9; color: #17233c; }
    .fee-workspace-page .workspace-nav a.active { background: #1677b8; color: #fff; }
    .fee-workspace-page .section-heading {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 14px;
    }
    .fee-workspace-page .section-icon {
        display: inline-flex;
        flex: 0 0 40px;
        width: 40px;
        height: 40px;
        align-items: center;
        justify-content: center;
        border-radius: 7px;
        background: #eaf4fb;
        color: #1677b8;
        font-size: 17px;
    }
    .fee-workspace-page .section-heading h3 { margin: 0 0 3px; color: #17233c; font-size: 19px; }
    .fee-workspace-page .section-heading p { margin: 0; color: #6b778c; }
    .fee-workspace-page .workspace-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    .fee-workspace-page .workspace-card {
        position: relative;
        display: flex;
        min-height: 168px;
        flex-direction: column;
        padding: 18px;
        overflow: hidden;
        border: 1px solid #e1e7ef;
        border-radius: 7px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(23, 35, 60, .06);
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
    }
    .fee-workspace-page .workspace-card:hover {
        transform: translateY(-2px);
        border-color: #b9d7e9;
        box-shadow: 0 7px 18px rgba(23, 35, 60, .11);
    }
    .fee-workspace-page .workspace-card-icon {
        display: inline-flex;
        width: 35px;
        height: 35px;
        margin-bottom: 11px;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        background: #f0f7fc;
        color: #1677b8;
        font-size: 15px;
    }
    .fee-workspace-page .workspace-card h4 { margin: 0 0 6px; color: #17233c; font-weight: 600; }
    .fee-workspace-page .workspace-card p { flex: 1; margin: 0 0 14px; color: #69768a; line-height: 1.5; }
    .fee-workspace-page .workspace-card .btn { align-self: flex-start; min-width: 130px; }
    .fee-workspace-page .card-eyebrow {
        position: absolute;
        top: 0;
        right: 0;
        padding: 5px 9px;
        border-radius: 0 0 0 6px;
        background: #fff3d8;
        color: #8a5a0a;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .4px;
        text-transform: uppercase;
    }
    .fee-workspace-page .integrity-note {
        display: flex;
        gap: 9px;
        margin-top: 16px;
        padding: 11px 13px;
        border-left: 3px solid #20a166;
        border-radius: 4px;
        background: #eef9f3;
        color: #336b52;
        font-size: 12px;
    }
    @media (max-width: 767px) {
        .fee-workspace-page .workspace-hero { align-items: flex-start; flex-direction: column; }
        .fee-workspace-page .workspace-nav a { flex: 1 1 145px; }
        .fee-workspace-page .workspace-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="content-wrapper fee-workspace-page">
    <section class="content-header">
        <h1><i class="fa fa-money"></i> Fee Workspace</h1>
    </section>

    <section class="content">
        <div class="workspace-hero">
            <div>
                <h2><?php echo html_escape($sections[$active_section]['title']); ?></h2>
                <p><?php echo html_escape($sections[$active_section]['description']); ?></p>
            </div>
            <span class="workspace-label">New Parallel Workspace</span>
        </div>

        <nav class="workspace-nav" aria-label="Fee workspace sections">
            <?php foreach ($sections as $section_key => $section) {
                if (empty($section['actions'])) {
                    continue;
                }
                $is_active = $section_key === $active_section;
                ?>
                <a href="<?php echo site_url('admin/feeworkspace/' . $section_key); ?>" class="<?php echo $is_active ? 'active' : ''; ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
                    <i class="fa <?php echo html_escape($section['icon']); ?>"></i>
                    <?php echo html_escape($section['title']); ?>
                </a>
            <?php } ?>
        </nav>

        <div class="section-heading">
            <span class="section-icon"><i class="fa <?php echo html_escape($sections[$active_section]['icon']); ?>"></i></span>
            <div>
                <h3><?php echo html_escape($sections[$active_section]['title']); ?></h3>
                <p>Choose the task you want to complete.</p>
            </div>
        </div>

        <div class="workspace-grid">
            <?php foreach ($sections[$active_section]['actions'] as $action) { ?>
                <article class="workspace-card">
                    <?php if (!empty($action['eyebrow'])) { ?>
                        <span class="card-eyebrow"><?php echo html_escape($action['eyebrow']); ?></span>
                    <?php } ?>
                    <span class="workspace-card-icon"><i class="fa <?php echo html_escape($action['icon']); ?>"></i></span>
                    <h4><?php echo html_escape($action['title']); ?></h4>
                    <p><?php echo html_escape($action['description']); ?></p>
                    <a class="btn btn-primary workspace-open" href="<?php echo site_url($action['url']); ?>">
                        Open Workspace <i class="fa fa-arrow-right"></i>
                    </a>
                </article>
            <?php } ?>
        </div>

        <div class="integrity-note">
            <i class="fa fa-shield"></i>
            <span>These actions open the application's existing fee workflows. Payment posting, receipts, reports, notifications and Chart of Accounts vouchers continue through their current protected routes.</span>
        </div>
    </section>
</div>

<script>
(function ($) {
    'use strict';
    $('.workspace-open').on('click', function () {
        var button = $(this);
        if (button.hasClass('disabled')) return false;
        button.addClass('disabled').attr('aria-disabled', 'true').html('<i class="fa fa-spinner fa-spin"></i> Opening...');
    });
})(jQuery);
</script>

