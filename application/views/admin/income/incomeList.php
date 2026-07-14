<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$language = $this->customlib->getLanguage();
$language_name = $language["short_code"];
$logo_url = base_url('uploads/school_content/logo/' . (isset($settinglist[0]['image']) ? $settinglist[0]['image'] : 'images.png'));
?>
<style type="text/css">
    @media print {
        .no-print {
            visibility: hidden !important;
            display: none !important;
        }
    }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <section class="content-header">
        <h1>
            <i class="fa fa-usd"></i> <?php echo $this->lang->line('income'); ?>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php
            if ($this->rbac->hasPrivilege('income', 'can_add')) {
                ?>
                <div class="col-md-4">
                    <!-- Horizontal Form -->
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo $this->lang->line('add_income'); ?></h3>
                        </div><!-- /.box-header -->

                        <form id="form1" action="<?php echo base_url() ?>admin/income" id="employeeform" name="employeeform"
                            method="post" accept-charset="utf-8" enctype="multipart/form-data">
                            <div class="box-body">
                                <?php if ($this->session->flashdata('msg')) { ?>
                                    <?php echo $this->session->flashdata('msg') ?>
                                <?php } ?>
                                <?php
                                if (isset($error_message)) {
                                    echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                                }
                                ?>
                                <?php echo $this->customlib->getCSRF(); ?>

                                <div class="form-group">
                                    <label
                                        for="exampleInputEmail1"><?php echo $this->lang->line('income_head'); ?></label><small
                                        class="req"> *</small>

                                    <select autofocus="" id="inc_head_id" name="inc_head_id" class="form-control">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($incheadlist as $inchead) {
                                            ?>
                                            <option value="<?php echo $inchead['id'] ?>" <?php
                                               if (set_value('inc_head_id') == $inchead['id']) {
                                                   echo "selected = selected";
                                               }
                                               ?>><?php echo $inchead['income_category'] ?></option>

                                            <?php
                                            //$count++;
                                        }
                                        ?>
                                    </select><span class="text-danger"><?php echo form_error('inc_head_id'); ?></span>

                                </div>

                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('name'); ?><small
                                            class="req"> *</small></label>
                                    <input id="name" name="name" placeholder="" type="text" class="form-control"
                                        value="<?php echo set_value('name'); ?>" />
                                    <span class="text-danger"><?php echo form_error('name'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('invoice_no'); ?></label>
                                    <input id="invoice_no" name="invoice_no" placeholder="" type="text" class="form-control"
                                        value="<?php echo set_value('invoice_no'); ?>" />
                                    <span class="text-danger"><?php echo form_error('invoice_no'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('date'); ?><small
                                            class="req"> *</small></label>
                                    <input id="date" name="date" placeholder="" type="text" class="form-control"
                                        value="<?php echo set_value('date', date($this->customlib->getSchoolDateFormat())); ?>" readonly="readonly" />
                                    <span class="text-danger"><?php echo form_error('date'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('amount'); ?><small
                                            class="req"> *</small></label>
                                    <input id="amount" name="amount" placeholder="" type="text" class="form-control"
                                        value="<?php echo set_value('amount'); ?>" />
                                    <span class="text-danger"><?php echo form_error('amount'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label
                                        for="exampleInputEmail1"><?php echo $this->lang->line('attach_document'); ?></label>
                                    <input id="documents" name="documents" placeholder="" type="file"
                                        class="filestyle form-control" data-height="40"
                                        value="<?php echo set_value('documents'); ?>" />
                                    <span class="text-danger"><?php echo form_error('documents'); ?></span>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('description'); ?></label>
                                    <textarea class="form-control" id="description" name="description" placeholder=""
                                        rows="3" placeholder="Enter ..."><?php echo set_value('description'); ?></textarea>
                                    <span class="text-danger"></span>
                                </div>
                            </div><!-- /.box-body -->

                            <div class="box-footer">
                                <button type="submit"
                                    class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                            </div>
                        </form>
                    </div>

                </div><!--/.col (right) -->
                <!-- left column -->
            <?php } ?>
            <div class="col-md-<?php
            if ($this->rbac->hasPrivilege('income', 'can_add')) {
                echo "8";
            } else {
                echo "12";
            }
            ?>">
                <!-- general form elements -->
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"> <?php echo $this->lang->line('income_list'); ?></h3>
                        <div class="box-tools pull-right">
                            <button class="btn btn-sm btn-warning" id="printSelected">
                                <i class="fa fa-print"></i> <?php echo $this->lang->line('print_selected'); ?>
                            </button>
                        </div><!-- /.box-tools -->
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages">
                            <table class="table table-striped table-bordered table-hover income-list"
                                data-export-title="<?php echo $this->lang->line('income_list'); ?>">
                                <thead>
                                    <tr>
                                        <th><?php echo $this->lang->line('select'); ?></th>
                                        <th><?php echo $this->lang->line('name'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('description'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('invoice_no'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('date'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('income_head'); ?>
                                        </th>
                                        <th><?php echo $this->lang->line('amount'); ?>
                                        </th>
                                        <th class="text-right noExport"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table><!-- /.table -->
                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div><!--/.col (left) -->
            <!-- right column -->
        </div>
    </section><!-- /.content -->
    <div id="printArea" style="display:none;"></div>
</div><!-- /.content-wrapper -->

<script>
    (function ($) {
        'use strict';
        $(document).ready(function () {
            initDatatable('income-list', 'admin/income/getincomelist', [], [], 100);
            
            // Date field is readonly - no date picker initialization needed
        });
    }(jQuery))
</script>
<script>
    // Pass logo URL to JavaScript
    var logoUrl = '<?php echo $logo_url; ?>';
    console.log('Logo URL:', logoUrl);
</script>
<script>
    $(document).on('click', '#printSelected', function () {
        var selectedRows = [];
        $('.rowCheckbox:checked').each(function () {
            selectedRows.push($(this).closest('tr').clone());
        });

        if (selectedRows.length === 0) {
            alert('Please select at least one record to print.');
            return;
        }

        var printWindow = window.open('', '', 'height=800,width=1000');
        printWindow.document.write('<html><head><title>Income Receipt</title>');
        printWindow.document.write('<style>');
        printWindow.document.write('@media print { @page { size: 70mm auto; margin: 0; } }');
        printWindow.document.write('body{font-family: "Courier New", monospace; font-size: 10px; line-height: 1.2; margin: 0; padding: 2mm; width: 70mm; max-width: 70mm; box-sizing: border-box;}');
        printWindow.document.write('.receipt, .receipt *{color:#000 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact;}');
        printWindow.document.write('.info, .table td, .table th, .footer{font-weight: 600;}');
        printWindow.document.write('.receipt{border: 1px solid #000; padding: 2mm; margin-bottom: 2mm; width: 100%; box-sizing: border-box;}');
        printWindow.document.write('.header{text-align: center; margin-bottom: 2mm; border-bottom: 1px dashed #000; padding-bottom: 1mm;}');
        printWindow.document.write('.logo{max-width: 30px; max-height: 30px; margin-bottom: 1mm;}');
        printWindow.document.write('.school-name{font-size: 12px; font-weight: bold; margin-bottom: 1mm; text-transform: uppercase;}');
        printWindow.document.write('.school-address{font-size: 9px; line-height: 1.2; margin-bottom: 1mm; font-weight: 600;}');
        printWindow.document.write('.school-phone{font-size: 9px; margin-bottom: 1mm; font-weight: 600;}');
        printWindow.document.write('.receipt-title{text-align: center; font-size: 11px; font-weight: bold; margin: 2mm 0; text-decoration: underline; text-transform: uppercase;}');
        printWindow.document.write('.info{margin-bottom: 2mm; font-size: 9px; line-height: 1.3;}');
        printWindow.document.write('.info-name{font-weight: bold; font-size: 10px; margin-bottom: 1mm;}');
        printWindow.document.write('.info-detail{margin-bottom: 0.5mm;}');
        printWindow.document.write('.table{width: 100%; border-collapse: collapse; margin-bottom: 2mm; font-size: 8px; table-layout: fixed;}');
        printWindow.document.write('.table th, .table td{padding: 1mm; border: 1px solid #000; text-align: left; word-wrap: break-word; vertical-align: top;}');
        printWindow.document.write('.table th:nth-child(1){width: 40%;}');
        printWindow.document.write('.table th:nth-child(2){width: 20%;}');
        printWindow.document.write('.table th:nth-child(3){width: 40%;}');
        printWindow.document.write('.table th{background-color: #f0f0f0; font-weight: bold; text-align: center; font-size: 9px;}');
        printWindow.document.write('.table .text-right{text-align: right;}');
        printWindow.document.write('.total{border-top: 2px solid #000; font-weight: bold; font-size: 10px; padding-top: 1mm; margin-top: 2mm; line-height: 1.4;}');
        printWindow.document.write('.total-line{margin-bottom: 0.5mm;}');
        printWindow.document.write('.footer{text-align: center; font-size: 8px; margin-top: 2mm; border-top: 1px dashed #000; padding-top: 1mm; line-height: 1.2;}');
        printWindow.document.write('.divider{text-align: center; margin: 1mm 0; font-size: 8px;}');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');

        selectedRows.forEach(function (row) {
            let name = row.find('td:eq(1)').text().trim();
            let description = row.find('td:eq(2)').text().trim();
            let invoice = row.find('td:eq(3)').text().trim();
            let date = row.find('td:eq(4)').text().trim();
            let income_head = row.find('td:eq(5)').text().trim();
            let amount = row.find('td:eq(6)').text().trim();

            printWindow.document.write(`
                <div class="receipt">
                    <div class="header">
                        <img class="logo" src="` + logoUrl + `" alt="Logo">
                        <div class="school-name">Unique School System</div>
                        <div class="school-address">1-A Shama pir gazi road</div>
                        <div class="school-phone">Ph: 0345 6840852</div>
                    </div>

                    <div class="receipt-title">INCOME RECEIPT</div>

                    <div class="info">
                        <div class="info-name">${name}</div>
                        <div class="info-detail">Date: ${date}</div>
                        <div class="info-detail">Invoice: ${invoice || 'N/A'}</div>
                        <div class="info-detail">Category: ${income_head}</div>
                        <div class="info-detail">Description: ${description || 'N/A'}</div>
                    </div>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>${income_head}</td>
                                <td>1</td>
                                <td class="text-right">${amount}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="total">
                        <div class="total-line">Total: ${amount}</div>
                        <div class="total-line">Received: ${amount}</div>
                        <div class="total-line">Balance: Rs.0.00</div>
                    </div>

                    <div class="divider">--------------------------------</div>

                    <div class="footer">
                        <div>Thank you for your payment!</div>
                        <div>Printed: ${new Date().toLocaleString()}</div>
                    </div>
                </div>
            `);
        });

        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    });
</script>
