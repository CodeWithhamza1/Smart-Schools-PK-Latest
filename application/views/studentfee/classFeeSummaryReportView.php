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
            <i class="fa fa-money"></i> Monthly Fee Summary</h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> <?php echo $this->lang->line('select_criteria'); ?></h3>
                    </div>
                    <form action="<?php echo site_url('studentfee/fee_summary_report_pdf') ?>"  method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-4 col-sm-6">
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
                                <div class="col-md-4 col-sm-6">
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
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="exampleInputEmail1"><?php echo $this->lang->line('class'); ?></label><small class="req"> *</small>
                                        <select  id="class_id" name="class_id" class="form-control" >
                                            <option value="ALL" selected>All Classes</option>
                                            <option value="ALLSECTION" >All Classes (Section Wise)</option>
                                        </select>
                                        <span class="text-danger"><?php echo form_error('class_id'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-sm btn-primary pull-right" formtarget="_blank"><i class="fa fa-search"></i> <?php echo $this->lang->line('search'); ?></button>
                        </div>
                    </form>
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
