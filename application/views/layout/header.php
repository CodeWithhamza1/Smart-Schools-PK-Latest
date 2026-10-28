
<!DOCTYPE html> 
<html <?php echo $this->customlib->getRTL(); ?>>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title><?php echo $this->customlib->getAppName(); ?></title>
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
        <meta http-equiv="Cache-control" content="no-cache">
        <meta name="theme-color" content="#424242" />
       <link href="<?php echo base_url(); ?>uploads/school_content/admin_small_logo/<?php $this->setting_model->getAdminsmalllogo();?>" rel="shortcut icon" type="image/x-icon">
        
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/bootstrap/css/bootstrap.min.css" media="all">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/jquery.mCustomScrollbar.min.css">
        <?php
          $this->load->view('layout/theme');
         ?>
       
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/font-awesome.min.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/ionicons.min.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/iCheck/flat/blue.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/morris/morris.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/jvectormap/jquery-jvectormap-1.2.2.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/datepicker/datepicker3.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/colorpicker/bootstrap-colorpicker.css">

        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/daterangepicker/daterangepicker-bs3.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">

        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/custom_style.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/fee-modern.css">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/sidebar-modern.css?v=<?php echo (int) @filemtime(FCPATH . 'backend/dist/css/sidebar-modern.css'); ?>">
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/datepicker/css/bootstrap-datetimepicker.css">
        <!--file dropify-->
        <link rel="stylesheet" href="<?php echo base_url(); ?>backend/dist/css/dropify.min.css">
        <!--file nprogress-->
        <link href="<?php echo base_url(); ?>backend/dist/css/nprogress.css" rel="stylesheet">

        <!--print table-->
        <link href="<?php echo base_url(); ?>backend/dist/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>backend/dist/datatables/css/buttons.dataTables.min.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>backend/dist/datatables/css/dataTables.bootstrap.min.css" rel="stylesheet">
        <!--print table mobile support-->
        <link href="<?php echo base_url(); ?>backend/dist/datatables/css/responsive.dataTables.min.css" rel="stylesheet">
        <link href="<?php echo base_url(); ?>backend/dist/datatables/css/rowReorder.dataTables.min.css" rel="stylesheet">
        <!--language css-->
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/0.8.2/css/flag-icon.min.css">
        <link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>backend/dist/css/bootstrap-select.min.css">
        <script src="<?php echo base_url(); ?>backend/custom/jquery.min.js"></script>
        <script src="<?php echo base_url(); ?>backend/dist/js/moment.min.js"></script>
        <script src="<?php echo base_url(); ?>backend/datepicker/js/bootstrap-datetimepicker.js"></script>
        <script src="<?php echo base_url(); ?>backend/plugins/colorpicker/bootstrap-colorpicker.js"></script>
        <script src="<?php echo base_url(); ?>backend/datepicker/date.js"></script>
        <script src="<?php echo base_url(); ?>backend/dist/js/jquery-ui.min.js"></script>
        <script src="<?php echo base_url(); ?>backend/js/school-custom.js"></script>
        <script src="<?php echo base_url(); ?>backend/js/school-admin-custom.js"></script>
        <script src="<?php echo base_url(); ?>backend/js/sstoast.js"></script>         
        <!-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script>          -->
        <!-- fullCalendar -->
        <link rel="stylesheet" href="<?php echo base_url() ?>backend/fullcalendar/dist/fullcalendar.min.css">
        <link rel="stylesheet" href="<?php echo base_url() ?>backend/fullcalendar/dist/fullcalendar.print.min.css" media="print">	
        <script type="text/javascript">
            var baseurl = "<?php echo base_url(); ?>";
            var start_week=<?php echo $this->customlib->getStartWeek();?>;
            var chk_validate="<?php echo $this->config->item('SSLK')?>";
        </script>
     
  <style type="text/css">
        span.flag-icon.flag-icon-us{text-orientation: mixed;}
        .main-header, .main-header > .navbar, .main-header .logo { height: 50px; max-height: 50px; }
        .fixed .content-wrapper, .fixed .right-side { padding-top: 50px; }
        .fixed .main-sidebar, .fixed .left-side { padding-top: 50px; }
        .header-school-area, .header-actions-area { height: 50px; }
        .main-header .sidebar-session { padding: 12px 0; line-height: 26px; }
        .header-actions { display: flex; align-items: center; justify-content: flex-end; flex-wrap: nowrap; gap: 8px; width: 100%; min-width: 0; height: 50px; min-height: 50px; float: none !important; }
        .header-actions .navbar-form { flex: 1 1 290px; min-width: 220px; max-width: 330px; margin: 0; padding: 0; order: 1; float: none; }
        .header-actions .navbar-form .input-group { display: table; width: 100%; margin-top: 0; }
        .header-actions .search-form3 { min-width: 0; width: 100%; height: 38px; }
        .header-actions .topsidesearchbtn { height: 38px !important; }
        .today-collection-pill { display: inline-flex; align-items: center; gap: 6px; flex: 0 0 205px; width: 205px; min-width: 0; height: 36px; margin: 0; padding: 0 10px; border: 1px solid rgba(255,255,255,.25); border-radius: 999px; background: linear-gradient(135deg, #159570, #22b982); color: #fff; box-shadow: 0 3px 8px rgba(15,118,90,.2); font-size: 12px; font-weight: 600; line-height: 1; white-space: nowrap; overflow: hidden; }
        .today-collection-pill i { flex: 0 0 auto; font-size: 12px; opacity: .95; }
        .today-collection-label { flex: 1 1 auto; min-width: 0; overflow: hidden; text-overflow: ellipsis; }
        .today-collection-pill strong { display: block; flex: 0 0 auto; min-width: max-content; overflow: visible; text-overflow: clip; font-size: 13px; font-weight: 700; font-variant-numeric: tabular-nums; }
        .today-collection-pill:not(.today-profit-pill) { order: 2; }
        .today-profit-pill { flex-basis: 195px; width: 195px; order: 3; background: linear-gradient(135deg, #5b4ab7, #806bd8); box-shadow: 0 3px 8px rgba(91,74,183,.2); }
        .today-profit-pill.is-loss { background: linear-gradient(135deg, #c53f55, #e06469); box-shadow: 0 3px 8px rgba(197,63,85,.2); }
        .header-actions .navbar-custom-menu { flex: 0 0 auto; order: 4; float: none !important; }
        .header-actions .headertopmenu { display: flex; align-items: center; flex-wrap: nowrap; margin-right: 0; white-space: nowrap; }
        .header-actions .headertopmenu > li > a { padding-left: 10px; padding-right: 10px; }
        @media (min-width: 1200px) { .header-school-area { width: 30%; } .header-actions-area { width: 70%; padding-left: 0; } }
        @media (min-width: 768px) and (max-width: 1499px) { .header-actions { gap: 5px; } .header-actions .navbar-form { min-width: 175px; max-width: 245px; } .today-collection-pill { flex-basis: 132px; width: 132px; padding: 0 8px; } .today-profit-pill { flex-basis: 122px; width: 122px; } .today-collection-label { display: none; } .header-actions .headertopmenu > li > a { padding-left: 8px; padding-right: 8px; } }
        @media (max-width: 767px) { .main-header { height: 100px; max-height: 100px; } .main-header .logo, .main-header > .navbar { height: 50px; max-height: 50px; } .fixed .content-wrapper, .fixed .right-side { padding-top: 100px; } .fixed .main-sidebar, .fixed .left-side { padding-top: 100px; } .header-actions { gap: 4px; height: 50px; min-height: 50px; } .today-collection-pill { flex-basis: 112px; width: 112px; height: 32px; padding: 0 7px; } .today-profit-pill { flex-basis: 105px; width: 105px; } .today-collection-label { display: none; } }
  </style>
    </head>
    <?php
    $fee_route_root = strtolower((string) $this->uri->segment(1));
    $fee_route_controller = strtolower((string) $this->uri->segment(2));
    $fee_admin_controllers = array('feegroup', 'feetype', 'feesforward', 'feemaster', 'feesupdate', 'feediscount');
    $is_fee_module = $this->session->userdata('top_menu') === 'Fees Collection'
        || in_array($fee_route_root, array('studentfee', 'challanform', 'familychallanform'), true)
        || ($fee_route_root === 'admin' && in_array($fee_route_controller, $fee_admin_controllers, true));
    ?>
    <body class="hold-transition skin-blue fixed sidebar-mini<?php echo $is_fee_module ? ' fee-module-modern' : ''; ?>">


 <?php
if ($this->config->item('SSLK') == "") {
    ?>
 <div class="topaleart" style="display: none;">
    <div class="slidealert">
    <div class="alert alert-dismissible topaleart-inside"> 
   <p class="palert"><strong>Alert!</strong> You are using unregistered version of Smart School. Please <a  href="#" class="purchasemodal">click here</a> to register your purchase code for Smart School.</p>
</div></div>
</div>
                    <?php
}


?> 
<script>

    function collapseSidebar() {

        if (Boolean(sessionStorage.getItem('sidebar-toggle-collapsed'))) {
        sessionStorage.setItem('sidebar-toggle-collapsed', '');
        } else {
        sessionStorage.setItem('sidebar-toggle-collapsed', '1');
        }

        }

    function checksidebar() {
        if (Boolean(sessionStorage.getItem('sidebar-toggle-collapsed'))) {
        var body = document.getElementsByTagName('body')[0];
        body.className = body.className + ' sidebar-collapse';
        }
    }

    checksidebar();

</script> 
       <div class="wrapper">

            <header class="main-header" id="alert">
                <a href="<?php echo base_url(); ?>admin/admin/dashboard" class="logo">
                    <span class="logo-mini"><img src="<?php echo base_url(); ?>uploads/school_content/admin_small_logo/<?php $this->setting_model->getAdminsmalllogo();?>" alt="<?php echo $this->customlib->getAppName() ?>" /></span>
                    <span class="logo-lg"><img src="<?php echo base_url(); ?>uploads/school_content/admin_logo/<?php $this->setting_model->getAdminlogo();?>" alt="<?php echo $this->customlib->getAppName() ?>" /></span>
                </a>
                <nav class="navbar navbar-static-top" role="navigation">
                    <a onclick="collapseSidebar()"  class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div class="col-lg-5 col-md-3 col-sm-2 col-xs-5 header-school-area">
                        <span href="#"  class="sidebar-session">
                            <?php echo $this->setting_model->getCurrentSchoolName(); ?>
                        </span>
                    </div>
                    <div class="col-lg-7 col-md-9 col-sm-10 col-xs-7 header-actions-area">
                        <div class="pull-right header-actions">
                            <?php
                            if (!isset($today_collection) || !isset($today_profit_loss)) {
                                $today_header_summary = $this->studentfee_model->getTodayCollectionSummary();
                                $today_collection = isset($today_header_summary['today_collection']) ? $today_header_summary['today_collection'] : 0;
                                $today_profit_loss = isset($today_header_summary['today_profit_loss']) ? $today_header_summary['today_profit_loss'] : 0;
                            }
                            ?>
                            <?php if (isset($today_collection)) { $header_currency = $this->customlib->getSchoolCurrencyFormat(); ?>
                                <span class="today-collection-pill" title="Today's fee collection: <?php echo html_escape($header_currency) . number_format((float) $today_collection, 2); ?>">
                                    <i class="fa fa-money"></i>
                                    <span class="today-collection-label">Today's collection</span>
                                    <strong><?php echo html_escape($header_currency) . number_format((float) $today_collection, 2); ?></strong>
                                </span>
                                <?php $is_today_loss = isset($today_profit_loss) && (float) $today_profit_loss < 0; ?>
                                <span class="today-collection-pill today-profit-pill<?php echo $is_today_loss ? ' is-loss' : ''; ?>" title="Today's profit or loss: <?php echo $is_today_loss ? '-' : ''; ?><?php echo html_escape($header_currency) . number_format(abs((float) $today_profit_loss), 2); ?>">
                                    <i class="fa <?php echo $is_today_loss ? 'fa-arrow-down' : 'fa-line-chart'; ?>"></i>
                                    <span class="today-collection-label">Profit/Loss</span>
                                    <strong><?php echo $is_today_loss ? '-' : ''; ?><?php echo html_escape($header_currency) . number_format(abs((float) $today_profit_loss), 2); ?></strong>
                                </span>
                            <?php } ?>
                            <?php if ($this->rbac->hasPrivilege('student', 'can_view')) {?>
                              
                                <form id="header_search_form" class="navbar-form navbar-left search-form" role="search"  action="<?php echo site_url('admin/admin/search'); ?>" method="POST">
                                    <?php echo $this->customlib->getCSRF(); ?>
                                    <div class="input-group">
                                        <input type="text" value="<?php echo set_value('search_text1');?>" name="search_text1" id="search_text1" class="form-control search-form search-form3" placeholder="<?php echo $this->lang->line('search_by_student_name'); ?>">
                                        <span class="input-group-btn">
                                            <button type="submit" name="search" id="search-btn" onclick="getstudentlist()" style="" class="btn btn-flat topsidesearchbtn"><i class="fa fa-search"></i></button>
                                        </span>
                                    </div>
 
                                </form>
                            <?php }?>
                            <div class="navbar-custom-menu">
                                <?php if($this->rbac->hasPrivilege('language_switcher','can_view')){
                                    ?>
                                    <div class="langdiv"><select class="languageselectpicker" onchange="set_languages(this.value)"  type="text" id="languageSwitcher" >
                                          
                                           <?php $this->load->view('admin/language/languageSwitcher')?>

                                        </select></div> 
                                    <?php
                               }?>
                                
                                     
                                <ul class="nav navbar-nav headertopmenu">
 <?php
if ($this->module_lib->hasActive('calendar_to_do_list')) {
    if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_view')) {
        ?>
                                            <li class="cal15"><a data-placement="bottom" data-toggle="tooltip" title="<?php echo $this->lang->line('calendar') ?>" href="<?php echo base_url() ?>admin/calendar/events" ><i class="fa fa-calendar"></i></a>

                                            </li>
                                            <?php
}
}
?>
                                    <?php
if ($this->module_lib->hasActive('calendar_to_do_list')) {
    if ($this->rbac->hasPrivilege('calendar_to_do_list', 'can_view')) {
        ?>
                                            <li class="dropdown" data-placement="bottom" data-toggle="tooltip" title="<?php echo $this->lang->line('task') ?>">
                                                <a href="#"  class="dropdown-toggle todoicon" data-toggle="dropdown">
                                                    <i class="fa fa-check-square-o"></i>
                                                    <?php
$userdata = $this->customlib->getUserData();
        $count    = $this->customlib->countincompleteTask($userdata["id"]);
        if ($count > 0) {
            ?>

                                                        <span class="todo-indicator"><?php echo $count ?></span>
                                                    <?php }?>
                                                </a>
                                                <ul class="dropdown-menu menuboxshadow">

                                                    <li class="todoview plr10 ssnoti"><?php echo $this->lang->line('today_you_have'); ?> <?php echo $count; ?> <?php echo $this->lang->line('pending_task'); ?><a href="<?php echo base_url() ?>admin/calendar/events" class="pull-right pt0"><?php echo $this->lang->line('view'); ?> <?php echo $this->lang->line('all'); ?></a></li>
                                                    <li>
                                                        <ul class="todolist">
                                                            <?php
$tasklist = $this->customlib->getincompleteTask($userdata["id"]);
        foreach ($tasklist as $key => $value) {
            ?>
                                                                <li><div class="checkbox">
                                                                        <label><input type="checkbox" id="newcheck<?php echo $value["id"] ?>" onclick="markc('<?php echo $value["id"] ?>')" name="eventcheck"  value="<?php echo $value["id"]; ?>"><?php echo $value["event_title"] ?></label>
                                                                    </div></li>
                                                            <?php }?>

                                                        </ul>
                                                    </li>
                                                </ul>
                                            </li>

                                        
                                            <?php
}
}
                                if ($this->module_lib->hasActive('chat')){
                                    if($this->rbac->hasPrivilege('chat','can_view')){
                                        ?>
                                         <li class="cal15"><a data-placement="bottom" data-toggle="tooltip" title="" href="<?php echo base_url()?>admin/chat" data-original-title="<?php echo $this->lang->line('chat')?>" class="todoicon"><i class="fa fa-whatsapp"></i></a></li> 
                                        <?php
                                    }
                                ?>

                                  
                                <?php }
$file   = "";
$result = $this->customlib->getUserData();

$image = $result["image"];
$role  = $result["user_type"];
$id    = $result["id"];
if (!empty($image)) {

    $file = "uploads/staff_images/" . $image;
} else {
    if($result['gender']=='Female'){
                $file= "uploads/staff_images/default_female.jpg";
    }else{
                $file ="uploads/staff_images/default_male.jpg";
     }

    
}
?>
                                    <li class="dropdown user-menu">
                                        <a class="dropdown-toggle" style="padding: 15px 13px;" data-toggle="dropdown" href="#" aria-expanded="false">
                                            <img src="<?php echo base_url() . $file; ?>" class="topuser-image" alt="User Image">
                                        </a>
                                        <ul class="dropdown-menu dropdown-user menuboxshadow">
                                            <li>
                                                <div class="sstopuser">
                                                    <div class="ssuserleft">
                                                        <a href="<?php echo base_url() . "admin/staff/profile/" . $id ?>"><img src="<?php echo base_url() . $file; ?>" alt="User Image"></a>
                                                    </div>

                                                    <div class="sstopuser-test">
                                                        <h4 class="text-capitalize"><?php echo $this->customlib->getAdminSessionUserName(); ?></h4>
                                                        <h5><?php echo $role; ?></h5>
                                                     
                                                    </div>

                                                    <div class="divider"></div>
                                                    <div class="sspass">
                                                        <a href="<?php echo base_url() . "admin/staff/profile/" . $id ?>" data-toggle="tooltip" title="" data-original-title="<?php echo $this->lang->line('my_profile'); ?>"><i class="fa fa-user"></i><?php echo $this->lang->line('profile'); ?> </a> 
                                                        <a class="pl25" href="<?php echo base_url(); ?>admin/admin/changepass" data-toggle="tooltip" title="" data-original-title="<?php echo $this->lang->line('change_password'); ?>"><i class="fa fa-key"></i><?php echo $this->lang->line('password'); ?></a> <a class="pull-right" href="<?php echo base_url(); ?>site/logout"><i class="fa fa-sign-out fa-fw"></i><?php echo $this->lang->line('logout'); ?></a>
                                                    </div>
                                                </div><!--./sstopuser--></li>
                                        </ul>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </nav>
            </header>

            <?php $this->load->view('layout/sidebar');?>
            <script>
                function defoult(id){
      var defoult=  $('#languageSwitcher').val();
   

        $.ajax({
            type: "POST",
            url: base_url + "admin/language/default_language/"+id,
            data: {},
            success: function (data) {
                successMsg("Status Change Successfully");
              $('#languageSwitcher').html(data);

            }
        });

        window.location.reload('true');        
    }

    function set_languages(lang_id){       
        $.ajax({
            type: "POST",
            url: base_url + "admin/language/user_language/"+lang_id,
            data: {},
            success: function (data) { 
                successMsg("Status Change Successfully");
                 window.location.reload('true');

            }
        });

    }
 </script>

 
