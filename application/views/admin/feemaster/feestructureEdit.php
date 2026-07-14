<style type="text/css">
    .liststyle1 {
        margin: 0;
        list-style: none;
        line-height: 28px;
    }
</style>

<?php $currency_symbol = $this->customlib->getSchoolCurrencyFormat(); ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">

    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> Fee Structure</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <?php if ($this->rbac->hasPrivilege('fees_master', 'can_add')) { ?>
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo $this->lang->line('add_fees_master') . " : " . $this->setting_model->getCurrentSessionName(); ?></h3>
                    </div><!-- /.box-header -->
                    <form id="form1" action="<?php echo base_url() ?>admin/feemaster/addFeeStructure"  id="feemasterform" name="feemasterform" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php echo $this->session->flashdata('msg') ?>
                            <?php } ?>
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Class</label> <small class="req">*</small>
                                        <select autofocus="" id="class" name="class" class="form-control select2">
                                            <?php foreach ($class as $item){?>
                                                <option value="<?php echo $item['id'] ?>"><?php echo($item['class'] )?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Student</label> <small class="req">*</small>
                                        <select autofocus="" id="student" name="student" class="form-control select2">
                                            <option disabled selected>Select Student</option>
                                            <!-- <?php /*foreach ($studentlist as $item){*/?>
                                                <option value="<?php /*echo $item['id'] */?>"><?php /*echo($item['firstname'].' '. $item['lastname'].'-'.$item['class'] )*/?></option>
                                            --><?php /*} */?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Session</label> <small class="req">*</small>
                                        <select autofocus="" id="session" name="session" class="form-control">
                                            <option>2019-2020(1)</option>
                                            <option>2019-2021(2)</option>
                                            <option>2019-2022(3)</option>
                                            <option>2019-2023(4)</option>
                                            <option>2019-2024(5)</option>
                                            <option>2020-2021(1)</option>
                                            <option>2020-2022(2)</option>
                                            <option>2020-2023(3)</option>
                                            <option>2020-2024(4)</option>
                                            <option>2020-2025(5)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Total Package</label><small class="req"> *</small>
                                        <input id="package" name="package" placeholder="" type="text" class="form-control"  value="<?php echo set_value('amount'); ?>" />
                                        <span class="text-danger"><?php echo form_error('amount'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Fee Payment Method</label> <small class="req">*</small>
                                        <select autofocus="" id="method" name="method" class="form-control">
                                            <option>Monthly</option>
                                            <option>Quarterly</option>
                                            <option>Yearly</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">

                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('fees_type'); ?></label><small class="req"> *</small>
                                        <select  id="feetype_id" name="feetype_id[]" class="form-control select2" >
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($feetypeList as $feetype) {
                                                ?>
                                                <option value="<?php echo $feetype['id'] ?>"<?php
                                                if (set_value('feetype_id') == $feetype['id']) {
                                                    echo "selected =selected";
                                                }
                                                ?>><?php echo $feetype['type'] ?></option>
                                                <?php
                                                $count++;
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('feetype_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Amount</label><small class="req"> *</small>
                                        <input id="amount" name="amount[]" placeholder="" type="text" class="form-control"  value="<?php echo set_value('amount'); ?>" />
                                        <span class="text-danger"><?php echo form_error('amount'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('due_date'); ?></label>
                                        <input  name="due_date[]" placeholder="" type="date" class="form-control"  />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <div class="input-group-btn">
                                            <button class="btn blue-hoki ADD" type="button" style="margin-top: 20px;"  onclick="fee_details();"> <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="FeeDetails">
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-info pull-right"><?php echo $this->lang->line('save'); ?></button>
                        </div>
                </div>

                </form>
            </div>
        </div>
        <?php } ?>
        <div class="row">
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"><?php echo $this->lang->line('fees_master_list') . " : " . $this->setting_model->getCurrentSessionName(); ?></h3>

                    </div><!-- /.box-header -->

                    <div class="box-body">
                        <div class="download_label"><?php echo $this->lang->line('fees_master_list') . " : " . $this->setting_model->getCurrentSessionName(); ?></div>
                        <div class="table-responsive mailbox-messages">
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                <tr>
                                    <th><?php echo $this->lang->line('fees_group'); ?></th>
                                    <th><?php echo $this->lang->line('fees_code'); ?></th>

                                    <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($feemasterList as $feegroup) {
                                    ?>
                                    <tr>
                                        <td class="mailbox-name">
                                            <a href="#" data-toggle="popover" class="detail_popover"><?php echo $feegroup->group_name; ?></a>
                                        </td>
                                        <td class="mailbox-name">
                                            <ul class="liststyle1">
                                                <?php
                                                foreach ($feegroup->feetypes as $feetype_key => $feetype_value) {
                                                    $dateObj   = DateTime::createFromFormat('!m', $feetype_value->clnd_month);
                                                    $monthName = $dateObj->format('F');
                                                    $date = DateTime::createFromFormat("Y-m-d", $feetype_value->due_date);
                                                    ?>
                                                    <li> <i class="fa fa-money"></i>
                                                        <?php echo $monthName.'-'.$date->format("Y"). " " . $currency_symbol . $feetype_value->amount.'('.$feetype_value->Method.')'; ?> &nbsp;&nbsp;
                                                        <?php /*if ($this->rbac->hasPrivilege('fees_master', 'can_edit')) { */?><!--
                                                            <a href="<?php /*echo base_url(); */?>admin/feemaster/edit/<?php /*echo $feetype_value->id */?>"   data-toggle="tooltip" title="<?php /*echo $this->lang->line('edit'); */?>">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>&nbsp;
                                                            --><?php
                                                        /*                                                        }*/
                                                        if ($this->rbac->hasPrivilege('fees_master', 'can_delete')) {
                                                            ?>
                                                            <a href="<?php echo base_url(); ?>admin/feemaster/delete2/<?php echo $feetype_value->id ?>" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                                <i class="fa fa-remove"></i>
                                                            </a>
                                                        <?php } ?>

                                                    </li>

                                                    <?php
                                                }
                                                ?>
                                            </ul>
                                        </td>

                                        <td class="mailbox-date pull-right">
                                            <?php /*if ($this->rbac->hasPrivilege('fees_group_assign', 'can_view')) { */?><!--
                                            <a href="<?php /*echo base_url(); */?>admin/feemaster/assign/<?php /*echo $feegroup->id */?>"
                                               class="btn btn-default btn-xs" data-toggle="tooltip" title="<?php /*echo $this->lang->line('assign / view'); */?>">
                                                <i class="fa fa-tag"></i>
                                            </a>
                                        --><?php /*} */?>
                                            <?php if ($this->rbac->hasPrivilege('fees_master', 'can_delete')) { ?>
                                                <a href="<?php echo base_url(); ?>admin/feemaster/deletegrp/<?php echo $feegroup->id ?>" class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
                                                    <i class="fa fa-remove"></i>
                                                </a>
                                            <?php } ?>

                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                </tbody>
                            </table><!-- /.table -->
                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div>
        </div>


</div>
</section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script type="text/javascript">
    $(document).ready(function () {
        var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy',]) ?>';

        $('#date').datepicker({
            //  format: "dd-mm-yyyy",
            format: date_format,
            autoclose: true
        });

        $("#btnreset").click(function () {
            $("#form1")[0].reset();
        });
        var newURL = '<?php echo base_url() ?>';
        $("#fee_start_month").each(function (index) {
            $("#fee_start_month").on('change', function () {
                $.ajax({
                    type: "GET",
                    url: newURL + "admin/feemaster/getEndMonth",
                    dataType: "json",
                    data: {
                        "start_month": $(this).val(),
                        "year":$('#year').val()
                    },
                    success: function (response) {
                        var listItems;
                        listItems += "<option disabled selected> Select End Month</option>";
                        for (var i = 0; i < response.length; i++) {
                            listItems += "<option value='" + response[i].NO + "'>" + response[i].NAME +  "</option>";
                        }
                        //$("#fee_end_month").html(listItems);
                    }
                });
            });
        });

    });
</script>
<script>
    $(document).ready(function () {
        //$('#student').select2();
        $('.detail_popover').popover({
            placement: 'right',
            trigger: 'hover',
            container: 'body',
            html: true,
            content: function () {
                return $(this).closest('td').find('.fee_detail_popover').html();
            }
        });


        var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy',]) ?>';
        $('#due_date').datepicker({
            format: date_format,
            autoclose: true
        });
        var newURL = '<?php echo base_url() ?>';
        $("#class").each(function (index) {
            $("#class").on('change', function () {
                $.ajax({
                    type: "GET",
                    url: newURL + "admin/feemaster/getStudentByClass",
                    dataType: "json",
                    data: {
                        "class": $(this).val()
                    },
                    success: function (response) {
                        console.log(response);
                        var listItems;
                        //listItems += "<option disabled selected> Select Student</option>";
                        for (var i = 0; i < response.length; i++) {
                            listItems += "<option value='" + response[i].id + "'>" + response[i].firstname +' '+response[i].lastname + "</option>";
                        }
                        $("#student").html(listItems);
                    }
                });
            });
        });
    });


</script>
<script>
    var room = 1;
    function fee_details() {
        room++;
        var objTo = document.getElementById('FeeDetails');
        var divtest = document.createElement("div");
        divtest.setAttribute("class", "form-group removeclass"+room);
        var rdiv = 'removeclass'+room;
        divtest.innerHTML ='<div class="row"><div class="col-md-3"><div class="form-group">\n'+
            '<select class="form-control feeType" id="feeType'+room+'" name="feetype_id[]">\n'+
            '<option disabled selected>Fees Type *</option></select></div></div>\n'+
            '<div class="col-md-3"><div class="form-group">\n'+
            '<input id="activeFrom" type="text"  class="form-control"  name="amount[]">\n'+
            '</div></div><div class="col-md-3"><div class="form-group">\n'+
            '<input id="due_date" name="due_date[]" placeholder="" type="date" class="form-control" />\n'+
            '</div></div>\n'+
            '<button class="btn btn-danger" type="button" style="margin-left: 15px;" onclick="remove_product_details('+ room +');">\n'+
            '<span class="glyphicon glyphicon-minus" aria-hidden="true"></span>\n' +
            '</button><div class="clear"></div></div>';
        objTo.appendChild(divtest);
        var html= document.getElementById('feetype_id');
        $("#" + 'feeType' + room).html(html.innerHTML);
        /*$(".feeType").select2();*/
    }
    function remove_product_details(rid) {
        $('.removeclass'+rid).remove();
    }
</script>