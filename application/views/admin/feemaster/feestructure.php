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
            <i class="fa fa-money"></i>Edit Fee Structure</h1>
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
                            <?php echo $this->customlib->getCSRF();

                            ?>
                            <input  name="session_id" id="session_id"  type="hidden" class="form-control" value="<?php echo $this->setting_model->getCurrentSession(); ?>"  />
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Class</label> <small class="req">*</small>
                                        <select autofocus="" id="class" name="class" class="form-control select2" required>
                                            <option disabled selected value="">Select</option>
                                            <?php foreach ($class as $item){?>
                                                <option value="<?php echo $item['id'] ?>"><?php echo($item['class'] )?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Section</label>
                                        <select autofocus="" id="section" name="section" class="form-control select2">
                                            <option disabled selected value="">Select Section</option>
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
                                        <select  id="feetype_id" name="feetype_id[]" class="form-control select2" onchange="getFees(this)">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($feetypeList as $feetype) {
                                                ?>
                                                <option value="<?php echo $feetype['id'] ?>"<?php
                                                if (set_value('feetype_id') == $feetype['id']) {
                                                    echo "selected =selected";
                                                }
                                                ?> data-code="<?php echo $feetype['code'] ?>" ><?php echo $feetype['type'] ?></option>
                                                <?php
                                                $count++;
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('feetype_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3" id="amountDiv">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Amount</label><small class="req"> *</small>
                                        <input id="amount" name="amount[]" placeholder="" type="text" class="form-control"  value="<?php echo set_value('amount'); ?>" />
                                        <span class="text-danger"><?php echo form_error('amount'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('due_date'); ?></label>
                                        <input  name="due_date[]" placeholder="" type="date" class="form-control" required  />
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
                                                            <a href="<?php echo base_url(); ?>admin/feemaster/delete/<?php echo $feetype_value->id ?>" data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo $this->lang->line('delete_confirm') ?>');">
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
                var class_id = $(this).val();
                
                // Get sections for the selected class
                $.ajax({
                    type: "GET",
                    url: newURL + "admin/feemaster/getSectionsByClass",
                    dataType: "json",
                    data: {
                        "class_id": class_id
                    },
                    success: function (response) {
                        var sectionItems = '<option disabled selected value="">Select Section</option>';
                        for (var i = 0; i < response.length; i++) {
                            sectionItems += "<option value='" + response[i].section_id + "'>" + response[i].section + "</option>";
                        }
                        $("#section").html(sectionItems);
                    }
                });
                
                // Get students for the selected class
                $.ajax({
                    type: "GET",
                    url: newURL + "admin/feemaster/getStudentByClass",
                    dataType: "json",
                    data: {
                        "class": class_id
                    },
                    success: function (response) {
                        console.log(response);
                        var listItems;
                        listItems = '<option value="ALL">All Students</option>';
                        //listItems += "<option disabled selected> Select Student</option>";
                        for (var i = 0; i < response.length; i++) {
                            listItems += "<option value='" + response[i].id + "'>"/*+ response[i].admission_no +' '*/+ response[i].firstname +' '+response[i].lastname + "</option>";
                        }
                        $("#student").html(listItems);
                    }
                });
            });
        });
        
        // Handle section change to filter students
        $("#section").on('change', function () {
            var class_id = $("#class").val();
            var section_id = $(this).val();
            
            
            if (class_id && section_id) {
                $.ajax({
                    type: "GET",
                    url: newURL + "admin/feemaster/getStudentByClassSection",
                    dataType: "json",
                    data: {
                        "class_id": class_id,
                        "section_id": section_id
                    },
                    success: function (response) {
                        var listItems = '<option value="ALL">All Students</option>';
                        for (var i = 0; i < response.length; i++) {
                            listItems += "<option value='" + response[i].id + "'>" + response[i].firstname +' '+response[i].lastname + "</option>";
                        }
                        $("#student").html(listItems);
                    }
                });
            }
        });
    });


</script>
<script>
    var room = 1;
    function fee_details() {
        room++;
        var objTo = document.getElementById('FeeDetails');
        var divtest = document.createElement("div");
//        student = document.getElementById('student').value;
//        feetype_id = document.getElementById('feetype_id').getAttribute('data-code');
//        alert(feetype_id);
        divtest.setAttribute("class", "form-group removeclass"+room);
        var rdiv = 'removeclass'+room;
        divtest.innerHTML ='<div class="row"><div class="col-md-3"><div class="form-group">\n'+
            '<select class="form-control feeType" id="feeType'+room+'" name="feetype_id[]" data-room="'+room+'" onchange="getFeesNew(this)">\n'+
            '<option disabled selected>Fees Type *</option></select></div></div>\n'+
            '<div class="col-md-3" id="amountDiv'+room+'"><div class="form-group">\n'+
            '<input id="activeFrom'+room+'" type="text"  class="form-control"  name="amount[]">\n'+
            '</div></div><div class="col-md-3"><div class="form-group">\n'+
            '<input id="due_date" name="due_date[]" placeholder="" type="date" class="form-control" required/>\n'+
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

    function getFees(selected){
        feesCode = selected.options[selected.selectedIndex].getAttribute('data-code');
        if(feesCode == "Tuition" || feesCode == "Admission" ) {
            student_id = $('#student').val();
            amount = $('#amount').val();
            var newURL = '<?php echo base_url() ?>';
            if (student_id == "ALL") {
                $("#amountDiv").hide();
                // Set a placeholder value for ALL students - will be auto-populated server-side
                $('#amount').val('0');
            }
            else {
                $.ajax({
                    type: "POST",
                    url: newURL + "admin/feemaster/getFeesFromAdmission",
                    dataType: "json",
                    data: {
                        "student_id": student_id,
                        "fees_code": feesCode
                    },
                    success: function (response) {
                        $("#amountDiv").show();
                        if (feesCode == "Tuition") {
                            $('#amount').val(response.monthly_fee);
                            $('#amount').prop('readonly', true);
                        }
                        if (feesCode == "Admission") {
                            $('#amount').val(response.admission_fee);
                            $('#amount').prop('readonly', true);
                        }

                    },
                    error: function (request, status, errorThrown) {
//                        alert(errorThrown)
                    }
                });
            }
        }else{
            $("#amountDiv").show();
            $('#amount').prop('readonly', false);
        }


    }

    function getFeesNew(selected){
//        console.log(selected.parentElement.parentElement)
        feesCode = selected.options[selected.selectedIndex].getAttribute('data-code');
        if(feesCode == "Tuition" || feesCode == "Admission" ){
            student_id = $('#student').val();
            room = selected.getAttribute('data-room');

            var newURL = '<?php echo base_url() ?>';
            if (student_id == "ALL") {
                $("#amountDiv" +room).hide();
                // Set a placeholder value for ALL students - will be auto-populated server-side
                $('#activeFrom' + room).val('0');
            }else{
                $.ajax({
                    type: "POST",
                    url: newURL + "admin/feemaster/getFeesFromAdmission",
                    dataType: "json",
                    data: {
                        "student_id": student_id,
                        "fees_code": feesCode
                    },
                    success: function (response) {
                        $("#amountDiv").show();
                        if (feesCode == "Tuition") {
                            $('#activeFrom' + room).val(response.monthly_fee);
                            $('#activeFrom'+ room).prop('readonly', true);
                            //                        alert(response.final_fee);
                        }
                        if (feesCode == "Admission") {
                            $('#activeFrom' + room).val(response.admission_fee);
                            $('#activeFrom'+ room).prop('readonly', true);
                            //                        alert(response.final_fee);
                        }
                    },
                    error: function (request, status, errorThrown) {
                        //                        alert(errorThrown)
                    }
                });
            }

        }else{
            $("#amountDiv" +room).show();
            $('#activeFrom'+ room).prop('readonly', false);
        }

    }
</script>
