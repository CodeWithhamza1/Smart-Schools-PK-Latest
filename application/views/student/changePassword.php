<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style type="text/css">
    /*REQUIRED*/
    .carousel-row {
        margin-bottom: 10px;
    }
    .slide-row {
        padding: 0;
        background-color: #ffffff;
        min-height: 150px;
        border: 1px solid #e7e7e7;
        overflow: hidden;
        height: auto;
        position: relative;
    }
    .slide-carousel {
        width: 20%;
        float: left;
        display: inline-block;
    }
    .slide-carousel .carousel-indicators {
        margin-bottom: 0;
        bottom: 0;
        background: rgba(0, 0, 0, .5);
    }
    .slide-carousel .carousel-indicators li {
        border-radius: 0;
        width: 20px;
        height: 6px;
    }
    .slide-carousel .carousel-indicators .active {
        margin: 1px;
    }
    .slide-content {
        position: absolute;
        top: 0;
        left: 20%;
        display: block;
        float: left;
        width: 80%;
        max-height: 76%;
        padding: 1.5% 2% 2% 2%;
        overflow-y: auto;
    }
    .slide-content h4 {
        margin-bottom: 3px;
        margin-top: 0;
    }
    .slide-footer {
        position: absolute;
        bottom: 0;
        left: 20%;
        width: 78%;
        height: 20%;
        margin: 1%;
    }
    /* Scrollbars */
    .slide-content::-webkit-scrollbar {
        width: 5px;
    }
    .slide-content::-webkit-scrollbar-thumb:vertical {
        margin: 5px;
        background-color: #999;
        -webkit-border-radius: 5px;
    }
    .slide-content::-webkit-scrollbar-button:start:decrement,
    .slide-content::-webkit-scrollbar-button:end:increment {
        height: 5px;
        display: block;
    }
</style>

<div class="content-wrapper" style="min-height: 946px;">

    <section class="content-header">
        <h1>
            <i class="fa fa-line-chart"></i> <?php echo $this->lang->line('reports'); ?>
        </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>

                    <form role="form" action="<?php echo site_url('student/changePassword') ?>" method="post" class="">
                        <?php echo $this->customlib->getCSRF(); ?>

                        <?php
                        if($student_id=='ALL'){
                            foreach ($resultlist as $student) {?>
                                <div class="box-body">
                                    <div class="row">
                                    <div class="col-sm-2 col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('admission_no'); ?></label>
                                            <input type="text" class="form-control" value="<?php echo $student['admission_no']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-2 col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('student_name'); ?></label>
                                            <input type="text" class="form-control" value="<?php echo $student['firstname'] . " " . $student['lastname']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-2 col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('username'); ?></label>
                                            <input type="text" class="form-control" value="<?php echo $student['student_username']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-2 col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('password'); ?></label>
                                            <input type="text" class="form-control" value="<?php echo $student['student_password']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-2 col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('parent'); ?> <?php echo $this->lang->line('username'); ?></label>
                                            <input type="text" class="form-control" value="<?php echo $student['parent_username']; ?>">
                                        </div>
                                    </div>
                                    <div class="col-sm-2 col-md-2">
                                        <div class="form-group">
                                            <label><?php echo $this->lang->line('parent'); ?> <?php echo $this->lang->line('password'); ?></label>
                                            <input type="text" class="form-control" value="<?php echo $student['parent_password']; ?>">
                                        </div>
                                    </div>
                                    </div>
                                </div>
                            <?php }}
                        else{?>
                        <div class="box-body">
                            <div class="row">
                            <input type="hidden" name="child" value="<?php echo $resultlist['childs']; ?>">
                                <input type="hidden" name="user_id" value="<?php echo $resultlist['user_id']; ?>">
                                <input type="hidden" name="std_id" value="<?php echo $resultlist['std_id']; ?>">
                            <div class="col-sm-2 col-md-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('admission_no'); ?></label>
                                    <input type="text" class="form-control" readonly value="<?php echo $resultlist['admission_no']; ?>">
                                </div>
                            </div>
                            <div class="col-sm-2 col-md-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('student_name'); ?></label>
                                    <input type="text" class="form-control" readonly value="<?php echo $resultlist['firstname'] . " " . $resultlist['lastname']; ?>">
                                </div>
                            </div>
                           <div class="col-sm-2 col-md-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('username'); ?></label>
                                    <input type="text" class="form-control" readonly="" value="<?php echo $resultlist['student_username']; ?>">
                                </div>
                            </div>
                            <div class="col-sm-2 col-md-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('password'); ?></label>
                                    <input type="text" class="form-control" name="student_password" value="<?php echo $resultlist['student_password']; ?>">
                                </div>
                            </div>

                                <div class="col-sm-2 col-md-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('parent'); ?> <?php echo $this->lang->line('username'); ?></label>
                                    <input type="text" class="form-control" readonly value="<?php echo $resultlist['parent_username']; ?>">
                                </div>
                            </div>
                            <div class="col-sm-2 col-md-2">
                                <div class="form-group">
                                    <label><?php echo $this->lang->line('parent'); ?> <?php echo $this->lang->line('password'); ?></label>
                                    <input type="text" class="form-control" name="parent_password" value="<?php echo $resultlist['parent_password']; ?>">
                                </div>
                            </div>

                       <?php }?>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <button type="submit" name="search" value="search_filter" class="btn btn-primary btn-sm checkbox-toggle pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('submit'); ?></button>
                            </div>
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
                    $.each(data, function (i, obj)
                    {
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
                    $.each(data, function (i, obj)
                    {
                        div_data += "<option value=" + obj.section_id + ">" + obj.section + "</option>";
                    });
                    $('#section_id').append(div_data);
                }
            });
        });
    });
</script>