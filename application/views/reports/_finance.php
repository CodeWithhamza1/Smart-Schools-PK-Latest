
<div class="row">
    <div class="col-md-12">
        <div class="box box-primary border0 mb0 margesection">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('finance') ?></h3>

            </div>
            <div class="">
                <ul class="reportlists">
				
                    <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('Reports/finance/reportduefees'); ?>"><a href="<?php echo site_url('studentfee/reportduefees'); ?>"><i class="fa fa-file-text-o"></i> <?php echo $this->lang->line('balance_fees_statement'); ?></a></li>

                    <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('Reports/finance/reportdailycollection'); ?>"><a href="<?php echo site_url('studentfee/reportdailycollection'); ?>"><i class="fa fa-file-text-o"></i><?php echo $this->lang->line('daily_collection_report'); ?> </a></li>

                    <?php
                    if ($this->rbac->hasPrivilege('fees_statement', 'can_view')) {
                        ?>
                        <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('Reports/finance/reportbyname'); ?>"><a href="<?php echo base_url(); ?>studentfee/reportbyname"><i class="fa fa-file-text-o"></i> <?php echo $this->lang->line('fees_statement'); ?></a></li>
                        <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('transaction/searchtransaction'); ?>"><a href="<?php echo base_url(); ?>admin/transaction/searchtransaction"><i class="fa fa-file-text-o"></i> <?php echo $this->lang->line('transaction_report'); ?></a></li>
                        <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('transaction/transactionReportView'); ?>"><a href="<?php echo base_url(); ?>admin/transaction/transactionReportView"><i class="fa fa-file-text-o"></i> <?php echo "Profit Loss Report"; ?></a></li>
                        <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('studentfee/feeSummaryReportView'); ?>"><a href="<?php echo base_url(); ?>studentfee/feeSummaryReportView"><i class="fa fa-file-text-o"></i>Daily Fee Summary</a></li>
                        <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('studentfee/fee_summary_report_view'); ?>"><a href="<?php echo base_url(); ?>studentfee/fee_summary_report_view"><i class="fa fa-file-text-o"></i>Monthly Fee Summary</a></li>
                        <?php
                    }
                    if ($this->rbac->hasPrivilege('pending_fee_report', 'can_view')) {
                        ?>
                        <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('studentfee/pendingFeeReportView'); ?>"><a href="<?php echo base_url(); ?>studentfee/pendingFeeReportView"><i class="fa fa-file-text-o"></i>Pending Fee(MonthWise)</a></li>
                        <?php
                    }
                    if ($this->rbac->hasPrivilege('balance_fees_report', 'can_view')) {
                    ?>

                    <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('Reports/finance/studentacademicreport'); ?>"><a href="<?php echo base_url(); ?>admin/transaction/studentacademicreport"><i class="fa fa-file-text-o"></i>
                            <?php echo $this->lang->line('balance_fees_report'); ?></a></li>
                    <?php
                    }
                    if ($this->rbac->hasPrivilege('fees_collection_report', 'can_view')) {
                    ?>

                    <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('Reports/finance/collection_report'); ?>"><a href="<?php echo base_url(); ?>studentfee/collection_report"><i class="fa fa-file-text-o"></i> <?php echo $this->lang->line('fees') . " " . $this->lang->line('collection') . " " . $this->lang->line('report'); ?></a></li>
                    <?php } if ($this->rbac->hasPrivilege('online_fees_collection_report', 'can_view')) { ?>
                    <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('Reports/finance/onlinefees_report'); ?>"><a href="<?php echo base_url(); ?>report/onlinefees_report"><i class="fa fa-file-text-o"></i> <?php echo $this->lang->line('online') . " " . $this->lang->line('fees') . " " . $this->lang->line('collection') . " " . $this->lang->line('report'); ?></a></li>
                    <?php
                    }
                    if ($this->rbac->hasPrivilege('income_report', 'can_view')) {
                    ?>
                    <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('Reports/finance/income'); ?>"><a href="<?php echo base_url(); ?>report/income"><i class="fa fa-file-text-o"></i> <?php echo $this->lang->line('income') . " " . $this->lang->line('report'); ?></a></li>
                    <?php
                    }
                    if ($this->rbac->hasPrivilege('expense_report', 'can_view')) {
                    ?>
                    <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('Reports/finance/expense'); ?>"><a href="<?php echo base_url(); ?>report/expense"><i class="fa fa-file-text-o"></i> <?php echo $this->lang->line('expense') . ' ' . $this->lang->line('report'); ?></a></li>
                    <?php
                    }
                    if ($this->rbac->hasPrivilege('payroll_report', 'can_view')) {
                    ?>
                    <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('Reports/finance/payroll'); ?>"><a href="<?php echo base_url(); ?>report/payroll"><i class="fa fa-file-text-o"></i> <?php echo $this->lang->line('payroll') . " " . $this->lang->line('report'); ?></a></li>
                    <?php
                    }
                    if ($this->rbac->hasPrivilege('income_group_report', 'can_view')) {
                    ?>
                    <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('Reports/finance/incomegroup'); ?>"><a href="<?php echo base_url(); ?>report/incomegroup"><i class="fa fa-file-text-o"></i> <?php echo $this->lang->line('income') . " " . $this->lang->line('group') . " " . $this->lang->line('report'); ?></a></li>
                    <?php
                    }
                    if ($this->rbac->hasPrivilege('expense_group_report', 'can_view')) {
                    ?>
                    <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('Reports/finance/expensegroup'); ?>"><a href="<?php echo base_url(); ?>report/expensegroup"><i class="fa fa-file-text-o"></i> <?php echo $this->lang->line('expense') . " " . $this->lang->line('group') . " " . $this->lang->line('report'); ?></a></li>
                    <?php
                    }
                    if ($this->rbac->hasPrivilege('online_admission', 'can_view')) {
                    ?>
                    <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('Reports/finance/onlineadmission'); ?>"><a href="<?php echo base_url(); ?>report/onlineadmission"><i class="fa fa-file-text-o"></i> <?php echo $this->lang->line('online_admission') . " " . $this->lang->line('fees') . " " . $this->lang->line('collection') . " " . $this->lang->line('report'); ?></a></li>
                    <?php } ?>
                    <li class="col-lg-4 col-md-4 col-sm-6 <?php echo set_Submenu('studentfee/overDueNoticeView'); ?>"><a href="<?php echo base_url(); ?>studentfee/overDueNoticeView"><i class="fa fa-file-text-o"></i> OverDue Notice</a></li>

                </ul>
            </div>
        </div>
    </div>
</div>