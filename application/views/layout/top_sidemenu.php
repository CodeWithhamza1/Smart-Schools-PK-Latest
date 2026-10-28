<?php
$has_session_switch = $this->rbac->hasPrivilege('quick_session_change', 'can_view');

// [privilege, permission, url, icon, label]
$quick_links = array(
    array('student', 'can_view', 'student/search', 'fa-user-plus', $this->lang->line('student_details')),
    array('collect_fees', 'can_view', 'studentfee', 'fa-money', $this->lang->line('collect_fees')),
    array('income', 'can_add', 'admin/income', 'fa-usd', $this->lang->line('add_income')),
    array('expense', 'can_view', 'admin/expense', 'fa-credit-card', $this->lang->line('add_expense')),
    array('student_attendance', 'can_view', 'admin/stuattendence', 'fa-calendar-check-o', $this->lang->line('student_attendance')),
    array('staff_attendance', 'can_view', 'admin/staffattendance', 'fa-calendar-check-o', $this->lang->line('staff_attendance')),
    array('staff', 'can_view', 'admin/staff', 'fa-users', $this->lang->line('staff_directory')),
    array('exam', 'can_view', 'admin/examgroup', 'fa-map-o', $this->lang->line('exam') . " " . $this->lang->line('group')),
    array('exam_schedule', 'can_view', 'admin/examresult', 'fa-columns', $this->lang->line('exam') . " " . $this->lang->line('result')),
    array('class_timetable', 'can_view', 'admin/timetable/create', 'fa-calendar-times-o', $this->lang->line('class_timetable')),
    array('admission_enquiry', 'can_view', 'admin/enquiry', 'fa-question-circle-o', $this->lang->line('admission_enquiry')),
    array('complaint', 'can_view', 'admin/complaint', 'fa-commenting-o', $this->lang->line('complain')),
    array('upload_content', 'can_view', 'admin/content', 'fa-download', $this->lang->line('upload_content')),
    array('item_stock', 'can_add', 'admin/itemstock', 'fa-object-group', $this->lang->line('add_item_stock')),
    array('notice_board', 'can_view', 'admin/notification', 'fa-bullhorn', $this->lang->line('notice_board')),
    array('email_sms', 'can_view', 'admin/mailsms/compose', 'fa-envelope-o', $this->lang->line('send_email_/_sms')),
);
?>
<ul class="sessionul fixedmenu<?php echo $has_session_switch ? ' has-session' : ''; ?>">
    <?php if ($has_session_switch) { ?>
        <li class="session-item">
            <a data-toggle="modal" data-target="#sessionModal" title="<?php echo html_escape($this->lang->line('current_session') . ": " . $this->setting_model->getCurrentSessionName()); ?>">
                <i class="fa fa-calendar sb-lead"></i>
                <span class="sb-text">
                    <small><?php echo $this->lang->line('current_session'); ?></small>
                    <b><?php echo $this->setting_model->getCurrentSessionName(); ?></b>
                </span>
                <i class="fa fa-pencil sb-edit"></i>
            </a>
        </li>
    <?php } ?>

    <li class="sb-tools">
        <div class="sb-search">
            <i class="fa fa-search"></i>
            <input type="text" id="sbMenuSearch" autocomplete="off" spellcheck="false"
                   placeholder="<?php echo html_escape($this->lang->line('search')); ?>"
                   aria-label="<?php echo html_escape($this->lang->line('search')); ?>"
                   data-empty="<?php echo html_escape($this->lang->line('no_record_found')); ?>">
            <kbd>/</kbd>
        </div>

        <div class="dropdown quick-item">
            <a class="dropdown-toggle sb-quick" data-toggle="dropdown" href="#" aria-expanded="false" title="<?php echo html_escape($this->lang->line('quick_links')); ?>">
                <i class="fa fa-th"></i>
            </a>

            <ul class="dropdown-menu verticalmenu">
                <li class="verticalmenu-title"><?php echo $this->lang->line('quick_links'); ?></li>
                <?php foreach ($quick_links as $link) {
                    if ($this->rbac->hasPrivilege($link[0], $link[1])) { ?>
                        <li role="presentation"><a role="menuitem" tabindex="-1" href="<?php echo base_url() . $link[2]; ?>"><i class="fa <?php echo $link[3]; ?>"></i><span><?php echo $link[4]; ?></span></a></li>
                    <?php }
                } ?>
            </ul>
        </div>
    </li>
</ul>
