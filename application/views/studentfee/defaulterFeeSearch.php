<style type="text/css">
    @media print
    {
        .no-print, .no-print *
        {
            display: none !important;
        }
        .print, .print *
        {
            display: block;
        }
    }
    .print, .print *
    {
        display: none;
    }
</style>
<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1>
            <i class="fa fa-money"></i> Pending Fee List</h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form action="<?php echo site_url('studentfee/defaulterFeeSearch') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">Start Date</label>
                                        <input id="start_date" name="start_date" placeholder="" type="date" class="form-control" value="<?php echo $startDate ?>">
                                        <span class="text-danger"><?php echo form_error('start_date'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1">End Date</label>
                                        <input id="end_date" name="end_date" placeholder="" type="date" class="form-control" value="<?php echo $endDate ?>">
                                        <span class="text-danger"><?php echo form_error('due_date'); ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('class'); ?></label><small class="req"> *</small>
                                        <select  id="class_id" name="class_id" class="form-control" >
                                            <option value="ALL" selected>All Classes</option>
                                            <?php
                                            foreach ($classlist as $class) {
                                                ?>
                                                <option value="<?php echo $class['id'] ?>" <?php if (set_value('class_id') == $class['id']) echo "selected=selected" ?>><?php echo $class['class'] ?></option>
                                                <?php
                                                $count++;
                                            }
                                            ?>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label><?php echo $this->lang->line('section'); ?></label>

                                        <select  id="section_id" name="section_id" class="form-control" >
                                            <option value="ALL" selected>All Section</option>

                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('section_id'); ?></span>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-sm btn-primary pull-right"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                        </div>
                    </form>
                </div>
                <?php
                if (isset($student_due_fee)) {
                    ?>

                    <div class="row">
                        <div class="col-xs-12">
                            <div class="box box-info" id="transfee">
                                <div class="box-header ptbnull">
                                    <h3 class="box-title titlefix"><i class="fa fa-users"></i> Student Pending Fee Report</h3>
                                </div>

                                <input class="primary" id="primaryid" type="hidden">
                                <div class="box-body table-responsive">
                                    <div class="download_label"><?php echo $this->lang->line('student_fees_report'); ?></div>
                                    <table class="table table-striped table-bordered table-hover" cellspacing="0" width="100%" id="STUDENTTABLE">
                                        <thead>
                                        <tr>
                                            <th><input name="select_all" value="1" type="checkbox"></th>
                                            <th>Student ID</th>
                                            <th class="text text-left"><?php echo $this->lang->line('student_name'); ?></th>
                                            <th class="text text-left"><?php echo $this->lang->line('admission_no'); ?></th>
                                            <th class="text text-left"><?php echo $this->lang->line('roll_no'); ?></th>
                                            <th class="text text-left"><?php echo $this->lang->line('father_name'); ?></th>
                                            <th class="text text-left"><?php echo $this->lang->line('mobile_no'); ?></th>
                                            <th class="text text-left">Paid</th>
                                            <th class="text-right"><?php echo $this->lang->line('balance'); ?> <span><?php echo "(" . $currency_symbol . ")"; ?></span></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $grd_total = 0;
                                        $grd_paid = 0;
                                        $grd_discount = 0;
                                        $grd_fine = 0;
                                        if (!empty($student_due_fee)) {
                                            foreach ($student_due_fee as $key => $student) {
                                                if($student->balance > 0) {
                                                    ?>
                                                    <tr>
                                                        <td><input type="checkbox"></td>
                                                        <td><?php echo $student->student_session_id; ?></td>
                                                        <td><?php echo $student->name; ?>   </td>
                                                        <td><?php echo $student->admission_no; ?>   </td>
                                                        <td><?php echo $student->roll_no; ?></td>
                                                        <td><?php echo $student->father_name; ?>   </td>
                                                        <td><?php echo $student->guardian_phone; ?>   </td>
                                                        <td class="text-right">
                                                            <?php
                                                            if ($student->deposit === "N/A") {
                                                                ?>
                                                                <span class="text text-red righttext">xxx</span>
                                                                <?php
                                                            } else {
                                                                echo(number_format($student->deposit, 2, '.', ''));
                                                            }
                                                            ?>
                                                        </td>
                                                        <td class="text-right">
                                                            <?php
                                                            if ($student->balance === "N/A") {
                                                                ?>
                                                                <span class="text text-red righttext">xxx</span>
                                                                <?php
                                                            } else {
                                                                echo(number_format($student->balance, 2, '.', ''));
                                                            }
                                                            ?>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                    $grd_total+=$student->balance;
                                                }
                                            }
                                        } else {
                                        ?>
                                        <tfoot>
                                        <tr>
                                            <td colspan="8" class="text-danger text-center">
                                                <span class="input input-danger"><?php echo $this->lang->line('no_record_found'); ?></span>
                                            </td>
                                        </tr>
                                        </tfoot>
                                        <?php
                                        }
                                        ?>
                                        <!--<tr  class="box box-solid total-bg">
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td class="text text-right">
                                                <?php /*echo $this->lang->line('grand_total'); */?>
                                            </td>
                                            <td class="text text-right">
                                                <?php /*echo ($currency_symbol . number_format(($grd_total), 2, '.', '')); */?>
                                            </td>
                                        </tr>-->
                                        </tbody>
                                    </table>
                                    <div class="row">
                                        <!--<div class="col-md-6">
                                            <div class="form-group">
                                                <label for="exampleInputEmail1">Message</label><small class="req"></small>
                                                <textarea  id="" name="message" class="form-control" ></textarea>
                                            </div>
                                        </div>-->
                                        <div class="panel-body">
                                            <div class="row position-right" style="text-align: end; margin: 6px">
                                                <button type="button" id="button" class="btn btn-info DISABLE"><i class="icon-floppy-disk position-left"></i>Send Message</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
                ?>

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
                            sel = "selected=selected";
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
            var div_data = '<option value="All"><?php echo "All Section"; ?></option>';
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
        var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy',]) ?>';

        $('#dob,#admission_date').datepicker({
            format: date_format,
            autoclose: true
        });
    });
</script>

<script type="text/javascript">
    $(document).ready(function () {
        function getSectionByClass(feecategory_id, feetype_id) {
            if (feecategory_id != "" && feetype_id != "") {
                $('#feetype_id').html("");
                var base_url = '<?php echo base_url() ?>';
                var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
                $.ajax({
                    type: "GET",
                    url: base_url + "feemaster/getByFeecategory",
                    data: {'feecategory_id': feecategory_id},
                    dataType: "json",
                    success: function (data) {
                        $.each(data, function (i, obj)
                        {
                            var sel = "";
                            if (feetype_id == obj.id) {
                                sel = "selected=selected";
                            }
                            div_data += "<option value=" + obj.id + " " + sel + ">" + obj.type + "</option>";
                        });
                        $('#feetype_id').append(div_data);
                    }
                });
            }
        }

        var feecategory_id = $('#feecategory_id').val();
        var feetype_id = '<?php echo set_value('feetype_id') ?>';
        getSectionByClass(feecategory_id, feetype_id);
        $(document).on('change', '#feecategory_id', function (e) {
            $('#feetype_id').html("");
            var feecategory_id = $(this).val();
            var base_url = '<?php echo base_url() ?>';
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "feemaster/getByFeecategory",
                data: {'feecategory_id': feecategory_id},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj)
                    {
                        div_data += "<option value=" + obj.id + ">" + obj.type + "</option>";
                    });
                    $('#feetype_id').append(div_data);
                }
            });
        });
        var rows_selected = [];
        var table = $('#STUDENTTABLE').dataTable();
        var datatable = $('#STUDENTTABLE').DataTable({
            "bProcessing": true,
            bDestroy: true,
            selectRow: true,
            select:{
                style:'multi'
            },
            order:[[1,'asc']],
            'rowCallback': function(row, data, dataIndex){
                // Get row ID
                var rowId = data[0];
                // If row ID is in the list of selected row IDs
                if($.inArray(rowId, rows_selected) !== -1){
                    $(row).find('input[type="checkbox"]').prop('checked', true);
                    $(row).addClass('selected');
                }
            }
        });
        $('#STUDENTTABLE').on('click', 'tbody td, thead th:first-child', function(e){
            if(this.checked){
                $('#datatables tbody input[type="checkbox"]:not(:checked)').trigger('click');
            } else {
                $('#datatables tbody input[type="checkbox"]:checked').trigger('click');
            }
            // Prevent click event from propagating to parent
            e.stopPropagation();
        });

        $('#STUDENTTABLE tbody').on('click', 'input[type="checkbox"]', function(e){

            // Get row data
            var $row = $(this).closest('tr');
            var data = table.fnGetData($row[0]);
            var rowId = data[1];

            // Determine whether row ID is in the list of selected row IDs
            var index = $.inArray(rowId, rows_selected);

            // If checkbox is checked and row ID is not in list of selected row IDs
            if(this.checked && index === -1){
                rows_selected.push(rowId);

                // Otherwise, if checkbox is not checked and row ID is in list of selected row IDs
            } else if (!this.checked && index !== -1){
                rows_selected.splice(index, 1);
            }

            if(this.checked){
                $row.addClass('selected');
            } else {
                $row.removeClass('selected');
            }

            // Update state of "Select all" control
            //updateDataTableSelectAllCtrl(table);

            // Prevent click event from propagating to parent
            e.stopPropagation();
            $('#primaryid').val(rows_selected);
        });
        $('#STUDENTTABLE').on('click', 'thead input[name="select_all"]',function (e) {

            if(this.checked){
                $('#STUDENTTABLE tbody input[type="checkbox"]:not(:checked)').trigger('click');
            } else {
                $('#STUDENTTABLE tbody input[type="checkbox"]:checked').trigger('click');
            }
            $.each(rows_selected, function(index, rowId){
                // Create a hidden element
                $('#primaryid').val(rows_selected);
            });

            // Prevent click event from propagating to parent
            e.stopPropagation();
        });

        // Handle click on table cells with checkboxes
        $('#STUDENTTABLE').on('click', 'tbody td, thead th:first-child', function(e){
            $(this).parent().find('input[type="checkbox"]').trigger('click');
        });
        $(".DISABLE").on('click',function(){
            $.ajax({
                type: "GET",
                url: base_url + "Studentfee/sendDefaultersMessage",
                dataType: "json",
                data:{
                    "STUDENT_ID": $('#primaryid').val()
                },
                success: function(response) {
                    console.log(response)
                    if (response == 'Success') {
                        alert("Message Sent Successfully");
                        window.location.reload();
                    }
                }
            });

        });
    });
</script>

<script type="text/javascript">
    var base_url = '<?php echo base_url() ?>';
    function printDiv(elem) {
        var fcat = $("#feecategory_id option:selected").text();
        var ftype = $("#feetype_id option:selected").text();
        var cls = $("#class_id option:selected").text();
        var sec = $("#section_id option:selected").text();
        $('.fcat').html(fcat);
        $('.ftype').html(ftype);
        $('.cls').html(cls + '(' + sec + ')');
        Popup(jQuery(elem).html());
    }

    function Popup(data)
    {

        var frame1 = $('<iframe />');
        frame1[0].name = "frame1";
        frame1.css({"position": "absolute", "top": "-1000000px"});
        $("body").append(frame1);
        var frameDoc = frame1[0].contentWindow ? frame1[0].contentWindow : frame1[0].contentDocument.document ? frame1[0].contentDocument.document : frame1[0].contentDocument;
        frameDoc.document.open();
        //Create a new HTML document.
        frameDoc.document.write('<html>');
        frameDoc.document.write('<head>');
        frameDoc.document.write('<title></title>');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/bootstrap/css/bootstrap.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/font-awesome.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/ionicons.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/AdminLTE.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/dist/css/skins/_all-skins.min.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/iCheck/flat/blue.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/morris/morris.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/jvectormap/jquery-jvectormap-1.2.2.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/datepicker/datepicker3.css">');
        frameDoc.document.write('<link rel="stylesheet" href="' + base_url + 'backend/plugins/daterangepicker/daterangepicker-bs3.css">');
        frameDoc.document.write('</head>');
        frameDoc.document.write('<body>');
        frameDoc.document.write(data);
        frameDoc.document.write('</body>');
        frameDoc.document.write('</html>');
        frameDoc.document.close();
        setTimeout(function () {
            window.frames["frame1"].focus();
            window.frames["frame1"].print();
            frame1.remove();
        }, 500);


        return true;
    }
</script>