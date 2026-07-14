<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper" style="min-height: 946px;">
    <div class="">
        <section class="content-header">
            <h1>
                <i class="fa fa-print"></i> Fee Summary Report </h1>
        </section>
        <!-- Main content -->
        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i
                                        class="fa fa-search"></i>Daily Fee Summary</h3>
                            <!--<h3 class="box-title"><i
                                        class="fa fa-search"></i> <?php /*echo $this->lang->line('select_criteria'); */?></h3>-->
                        </div>
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php echo $this->session->flashdata('msg') ?>
                            <?php } ?>
                            <form role="form" action="<?php echo site_url('studentfee/feeSummaryReport') ?>" method="post"
                                  class="">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="row"> </div>
                                <div class="row">
                                    <div class="col-md-3 col-sm-3">
                                        <div class="form-group">
                                            <label>Date</label>
                                            <small class="req"> *</small>
                                            <input autofocus="" id="date" name="date" type="date"
                                                   class="form-control" value="<?php echo $endDate;?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <div class="form-group">
                                            <label>Collected By</label>
                                            <small class="req"> *</small>
                                            <select class="form-control"  name="collect_by" >
                                                <!--                                            <option value="">--><?php //echo $this->lang->line('select') ?><!--</option>-->
                                                <option value="all">All</option>
                                                <?php

                                                foreach ($collect_by as $key => $value) {

                                                    $name=explode(' ', $value);
                                                    ?>
                                                    <option value="<?php echo $name[0] ?>" ><?php echo($value); ?></option>
                                                <?php  } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <div class="form-group">
                                            <label>Fee Type</label>
                                            <small class="req"> *</small>
                                            <select autofocus="" id="fee_type" name="fee_type"
                                                    class="form-control">
                                                <option value="all">All </option>
                                                <?php
                                                foreach ($feetype as $fee) {
                                                    ?>
                                                    <option value="<?php echo $fee['id'] ?>" ><?php echo $fee['type'] ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <div class="form-group">
                                            <label>Criteria</label>
                                            <select class="form-control" name="criteria">
                                                <option value="All">All</option>
                                                <option value="student">Student Wise</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <button type="submit" formtarget="_blank" name="search" value="search_filter"
                                                class="btn btn-primary pull-right btn-sm checkbox-toggle"><i
                                                    class="fa fa-check-square"></i> Print
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
    <div class="">

        <section class="content">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title"><i
                                        class="fa fa-search"></i>Date Wise Fee Summary</h3>
                        </div>
                        <div class="box-body">
                            <?php if ($this->session->flashdata('msg')) { ?>
                                <?php echo $this->session->flashdata('msg') ?>
                            <?php } ?>
                            <form role="form" action="<?php echo site_url('studentfee/feeSummaryReportDateWise') ?>" method="post"
                                  class="">
                                <?php echo $this->customlib->getCSRF(); ?>
                                <div class="row"> </div>
                                <div class="row">
                                    <div class="col-md-3 col-sm-3">
                                        <div class="form-group">
                                            <label>Start Date</label>
                                            <small class="req"> *</small>
                                            <input autofocus="" id="start_date" name="start_date" type="date"
                                                   class="form-control" value="<?php echo $startDate;?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <div class="form-group">
                                            <label>End Date</label>
                                            <small class="req"> *</small>
                                            <input autofocus="" id="end_date" name="end_date" type="date"
                                                   class="form-control" value="<?php echo $endDate;?>">
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <div class="form-group">
                                            <label>Fee Type</label>
                                            <small class="req"> *</small>
                                            <select autofocus="" id="fee_type" name="fee_type"
                                                    class="form-control">
                                                <option value="all">All </option>
                                                <?php
                                                foreach ($feetype as $fee) {
                                                    ?>
                                                    <option value="<?php echo $fee['id'] ?>" ><?php echo $fee['type'] ?></option>
                                                    <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-sm-3">
                                        <div class="form-group">
                                            <label>Collected By</label>
                                            <small class="req"> *</small>
                                            <select class="form-control"  name="collect_by" >
                                                <!--                                            <option value="">--><?php //echo $this->lang->line('select') ?><!--</option>-->
                                                <option value="all">All</option>
                                                <?php

                                                foreach ($collect_by as $key => $value) {

                                                    $name=explode(' ', $value);
                                                    ?>
                                                    <option value="<?php echo $name[0] ?>" ><?php echo($value); ?></option>
                                                <?php  } ?>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <button type="submit" formtarget="_blank" name="search" value="search_filter"
                                                class="btn btn-primary pull-right btn-sm checkbox-toggle"><i
                                                    class="fa fa-check-square"></i> Print
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

</div>

<script type="text/javascript">
    function getSectionByClass(class_id, section_id) {
        if (class_id != "" && section_id != "") {
            $('#section_id').html("");
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj) {
                        var sel = "";
                        if (section_id == obj.section_id) {
                            sel = "selected";
                        }
                        div_data += "<option value=" + obj.section_id + " " + sel + ">" + obj.section + "</option>";
                    });
                    $('#section_id').append(div_data);
                }
            });
        }
    }

    $(document).ready(function () {
        $("#student").select2({

        });
        var class_id = $('#class_id').val();
        var section_id = '<?php echo set_value('section_id') ?>';
        getSectionByClass(class_id, section_id);
        $(document).on('change', '#class_id', function (e) {
            $('#section_id').html("");
            var class_id = $(this).val();
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "sections/getByClass",
                data: {'class_id': class_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj) {
                        div_data += "<option value=" + obj.section_id + ">" + obj.section + "</option>";
                    });
                    $('#section_id').append(div_data);
                }
            });
        });

        $( "#section_id" ).each(function(index) {
            $("#section_id").change(function(){
                var base_url = '<?php echo base_url() ?>';
                $.ajax({
                    type: "GET",
                    url: base_url + "challanform/getStudents",
                    dataType: "json",
                    data: {
                        "CLASS_ID" : $('#class_id').val(), "SECTION_ID" : $(this).val()
                    },
                    success: function(response) {
                        var listItems;
                        listItems += "<option disabled selected>Select Students</option>";
                        listItems += "<option value='ALL'>ALL STUDENTS</option>";
                        for (var i = 0; i < response.length; i++){
                            listItems += "<option value='" + response[i].id + "'>"+ response[i].student_name+'('+response[i].admission_no +')'+"</option>";
                        }
                        $("#student").html(listItems);
                    }
                });
            });
        });
    });
</script>