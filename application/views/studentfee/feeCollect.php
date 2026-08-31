<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <section class="content-header">
        <h1>
            <i class="fa fa-calendar-check-o"></i> Fee Collection</h1>
        <div class="fee-page-intro">Enter one admission number to open the complete sibling/family fee collection flow.</div>
    </section>

    <!-- Main content -->
    <section class="content">
        <?php $collection_tab = 'family'; $this->load->view('studentfee/_collection_tabs', compact('collection_tab')); ?>
        <div class="row">
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <div class="box box-primary fee-setup-card">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-barcode"></i> Family Admission Lookup</h3>
                    </div><!-- /.box-header -->

                    <div class="box-body">
                            <div  style="margin-top: 5px;">
                                <?php if($this->session->flashdata('msg')): ?>
                                    <div class="alert alert-success" role="alert">
                                        <p><?php echo $this->session->flashdata('msg'); ?></p>
                                    </div>
                                <?php endif; ?>
                                <?php if($this->session->flashdata('err')): ?>
                                    <div class="alert alert-danger" role="alert">
                                        <p><?php echo $this->session->flashdata('err'); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="exampleInputFile">Admission No</label><small class="req"> *</small>
                                        <div><input class="form-control" type='text' name='barcode' id="barcode" autofocus="autofocus" "/>
                                        </div></div>

                                </div>
                            </div>
                    </div>
                    <div class="box box-info">
                        <div class="box-header ptbnull">
                            <h3 class="box-title titlefix"><i class="fa fa-users"></i> <?php echo $this->lang->line('student'); ?> <?php echo $this->lang->line('list'); ?>
                                </i> <?php echo form_error('student'); ?></h3>
                            <div class="box-tools pull-right">
                            </div>
                        </div>
                        <div class="box-body table-responsive">
                            <div class="download_label"><?php echo $this->lang->line('student'); ?> <?php echo $this->lang->line('list'); ?></div>
                            <table class="table table-striped table-bordered table-hover example" id="FeeTable">
                                <thead>
                                <tr>
                                    <th><?php echo $this->lang->line('class'); ?></th>
                                    <th><?php echo $this->lang->line('section'); ?></th>
                                    <th><?php echo $this->lang->line('admission_no'); ?></th>
                                    <th><?php echo $this->lang->line('student'); ?> <?php echo $this->lang->line('name'); ?></th>
                                    <th><?php echo $this->lang->line('father_name'); ?></th>
                                  <!--  <th><?php /*echo $this->lang->line('date_of_birth'); */?></th>
                                    <th><?php /*echo $this->lang->line('phone'); */?></th>-->
                                    <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                                </thead>
                               <!-- <tbody>
                                <?php
/*                                $count = 1;
                                foreach ($resultlist as $student) {
                                    */?>
                                    <tr>
                                        <td><?php /*echo $student['class']; */?></td>
                                        <td><?php /*echo $student['section']; */?></td>
                                        <td><?php /*echo $student['admission_no']; */?></td>
                                        <td><?php /*echo $student['firstname'] . " " . $student['lastname']; */?></td>
                                        <td><?php /*echo $student['father_name']; */?></td>
                                        <td><?php
/*                                            if (!empty($student['dob'])) {
                                                echo date($this->customlib->getSchoolDateFormat(), $this->customlib->dateyyyymmddTodateformat($student['dob']));
                                            }
                                            */?></td>
                                        <td><?php /*echo $student['guardian_phone']; */?></td>
                                        <td class="pull-right">
                                            <?php /*if ($this->rbac->hasPrivilege('collect_fees', 'can_add')) { */?>
                                                <a  href="<?php /*echo base_url(); */?>studentfee/addfee/<?php /*echo $student['id'] */?>" class="btn btn-info btn-xs" data-toggle="tooltip" title="" data-original-title="">
                                                    <?php /*echo $currency_symbol; */?> <?php /*echo $this->lang->line('collect_fees'); */?>
                                                </a>
                                            <?php /*} */?>
                                        </td>

                                    </tr>
                                    <?php
/*                                }
                                $count++;
                                */?>
                                </tbody>-->
                            </table>
                        </div>
                    </div>
            </div><!--/.col (right) -->
            <!-- left column -->

        </div>

    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<script type="text/javascript" src="<?php echo base_url(); ?>backend/bootstrap-toastr/toastr.min.js"></script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/bootstrap-toastr/ui-toastr.min.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy',]) ?>';

        $('#date').datepicker({
            //  format: "dd-mm-yyyy",
            format: date_format,
            endDate: '+0d',
            autoclose: true
        });

        $("#btnreset").click(function () {
            $("#form1")[0].reset();
        });

    });

    $(document).ready(function () {
        $('.detail_popover').popover({
            placement: 'right',
            trigger: 'hover',
            container: 'body',
            html: true,
            content: function () {
                return $(this).closest('td').find('.fee_detail_popover').html();
            }
        });
    });

    var base_url = '<?php echo base_url() ?>';
    $('#barcode').keyup (function(e) {
        var value = $('#barcode').val();
        var key = e.which;
        var datatable = $('#FeeTable').DataTable();
        if (($(this).val().length  >0) && (key == 13)) {
            $.ajax({
                type: "GET",
                url: base_url + "studentfee/getStudentList",
                dataType: "json",
                data: {
                    "admission_no": $('#barcode').val()
                },
                success: function (response) {
                    if (response != 'fail') {
                        window.location = "<?php echo base_url() ?>"+ "studentfee/addfee1/"+response;
                    }
                    else{
                        alert('No record found against this number. Please enter a valid number');
                    }

                }
            });
        }
    });
</script>
