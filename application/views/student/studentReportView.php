<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-print"></i> Student Report </h1>
    </section>
    <!-- Main content -->
    <section class="content">
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
                        <form role="form" action="<?php echo site_url('student/studentReportPDF') ?>" method="post"
                              class="">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-4 col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('class'); ?></label>
                                        <small class="req"> *</small>
                                        <select autofocus="" id="class_id" name="class_id"
                                                class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
<!--                                            <option value="ALL">All Classes</option>-->
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
                                <div class="col-md-4 col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label>
                                        <select id="section_id" name="section_id" class="form-control">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-4">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('type'); ?></label>
                                        <select id="type" name="type" class="form-control">
                                            <option value="1"><?php echo "Student Award Report (9 Column)"; ?></option>
                                            <option value="2"><?php echo "Student Award Report (2 Column)"; ?></option>
                                            <option value="3"><?php echo "Board Registration Report"; ?></option>
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