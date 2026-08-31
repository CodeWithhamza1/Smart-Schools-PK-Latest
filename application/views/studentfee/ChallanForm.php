<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
$system_current_year = (int) date('Y');
$system_current_month = (int) date('n');
$selected_year = set_value('year');
if ($selected_year === '') {
    $selected_year = isset($year) && $year !== '' ? (int) $year : $system_current_year;
}
$selected_month = set_value('month');
if ($selected_month === '') {
    $selected_month = isset($month) && $month !== '' ? (int) $month : $system_current_month;
}
$year_start = min($system_current_year - 5, (int) $selected_year);
$year_end = max($system_current_year + 2, (int) $selected_year);
$month_list = array(
    1 => 'January',
    2 => 'February',
    3 => 'March',
    4 => 'April',
    5 => 'May',
    6 => 'June',
    7 => 'July',
    8 => 'August',
    9 => 'September',
    10 => 'October',
    11 => 'November',
    12 => 'December',
);
?>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-print"></i> Challan Form </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <style>.challan-tabs{display:flex;gap:6px;margin-bottom:15px;border-bottom:1px solid #dfe4ea}.challan-tabs a{padding:10px 16px;border-radius:5px 5px 0 0;color:#52606d;font-weight:600}.challan-tabs a.active{background:#3c8dbc;color:#fff}</style>
        <div class="challan-tabs">
            <a class="active" href="<?php echo site_url('challanform/loadView'); ?>"><i class="fa fa-user"></i> Student Challan</a>
            <a href="<?php echo site_url('FamilyChallanform/loadView'); ?>"><i class="fa fa-users"></i> Family Challan</a>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i
                                class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <div class="box-body">
                        <?php if ($this->session->flashdata('msg')) { ?>
                            <?php echo $this->session->flashdata('msg') ?>
                        <?php } ?>
                        <form role="form" action="<?php echo site_url('challanform/generateChallanForm') ?>" method="post"
                              class="">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label>Year</label>
                                        <small class="req"> *</small>
                                        <select autofocus id="year" name="year" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php for ($year_option = $year_start; $year_option <= $year_end; $year_option++) { ?>
                                                <option value="<?php echo $year_option; ?>" <?php if ((int) $selected_year === $year_option) { echo 'selected="selected"'; } ?>><?php echo $year_option; ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('year'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label>Month</label>
                                        <small class="req"> *</small>
                                        <select id="month" name="month" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php foreach ($month_list as $month_value => $month_label) { ?>
                                                <option value="<?php echo $month_value; ?>" <?php if ((int) $selected_month === (int) $month_value) { echo 'selected="selected"'; } ?>><?php echo $month_label; ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('month'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label>
                                        <small class="req"> *</small>
                                        <select autofocus="" id="class_id" name="class_id"
                                                class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($classlist as $class) {
                                                ?>
                                                <option value="<?php echo $class['id'] ?>" <?php if (set_value('class_id') == $class['id']) echo "selected=selected" ?>><?php echo $class['class'] ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label>
                                        <small class="req"> *</small>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label>Student(s)</label>
                                        <small class="req"> *</small>
                                        <select autofocus="" id="student" name="student"
                                                class="form-control select2">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('student'); ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <button type="submit" name="search" formtarget="_blank" value="search_filter"
                                            class="btn btn-primary pull-right btn-sm checkbox-toggle"><i
                                            class="fa fa-check-square"></i> Generate Challan Form
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
