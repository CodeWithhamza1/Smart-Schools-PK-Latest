<?php

defined('BASEPATH') OR exit('No direct script access allowed');

$route['default_controller'] = 'welcome/loginPage';
$route['user/resetpassword/([a-z]+)/(:any)'] = 'site/resetpassword/$1/$2';
$route['admin/resetpassword/(:any)'] = 'site/admin_resetpassword/$1';
$route['admin/unauthorized'] = 'admin/admin/unauthorized';
$route['parent/unauthorized'] = 'parent/parents/unauthorized';
$route['student/unauthorized'] = 'user/user/unauthorized';
$route['teacher/unauthorized'] = 'teacher/teacher/unauthorized';
$route['accountant/unauthorized'] = 'accountant/accountant/unauthorized';
$route['librarian/unauthorized'] = 'librarian/librarian/unauthorized';
$route['404_override'] = 'welcome/show_404';
$route['translate_uri_dashes'] = FALSE;
$route['cron/(:any)'] = 'cron/index/$1';
$route['ai_widget/ask'] = 'ai_widget/ask';
$route['ai_widget/sessions'] = 'ai_widget/sessions';
$route['ai_widget/history/(:any)'] = 'ai_widget/history/$1';
$route['ai_widget/new_session'] = 'ai_widget/new_session';
$route['ai_widget/delete_session/(:any)'] = 'ai_widget/delete_session/$1';
$route['cron/ai_widget_cleanup/(:any)'] = 'cron/ai_widget_cleanup/$1';

// ZKTeco attendance module routes.
$route['api/attendance/health'] = 'api_attendance/health';
$route['api/attendance/sync'] = 'api_attendance/sync';
$route['api/attendance/reconcile'] = 'api_attendance/reconcile';
$route['admin/attendance/student_report'] = 'admin/attendance/student_report';
$route['admin/voucher-settings'] = 'vouchersettings/index';

//======= front url rewriting==========
$route['page/(:any)'] = 'welcome/page/$1';
$route['read/(:any)'] = 'welcome/read/$1';
$route['online_admission'] = 'welcome/admission';
$route['frontend'] = 'welcome';
