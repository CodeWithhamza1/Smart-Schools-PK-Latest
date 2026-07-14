<style type="text/css">
    #div_avail{
        display: none;
    }
</style>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><i class="fa fa-building-o"></i> <?php echo $this->lang->line('inventory'); ?></h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <!-- Horizontal Form -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><?php echo 'Sell item'; ?></h3>
                    </div><!-- /.box-header -->
                    <!-- form start -->

                    <form  action="<?php echo site_url('admin/sellitem/add') ?>"  id="issueitem" name="employeeform" method="post" accept-charset="utf-8">
                        <div class="box-body">
                            <div class="row">
                                <?php if ($this->session->flashdata('msg')) { ?>
                                    <?php echo $this->session->flashdata('msg') ?>
                                <?php } ?>
                                <?php
                                if (isset($error_message)) {
                                    echo "<div class='alert alert-danger'>" . $error_message . "</div>";
                                }
                                ?>      
                                <?php echo $this->customlib->getCSRF(); ?>                     

                                <div class="form-group col-md-4 col-sm-4" hidden>
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('user_type'); ?></label><small class="req"> *</small>

                                    <select name="account_type" onchange="getIssueUser(this.value)"  id="input-type-student" class="form-control ac_type">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                        <?php
                                        foreach ($roles as $role_key => $role_value) {
                                            ?>


                         <!--input autofocus="" name="account_type" class="ac_type" id="input-type-student" value="<?php echo $role_value['id']; ?>" type="radio" /-->
                                            <option value="<?php echo $role_value['id']; ?>"><?php echo $role_value['name'] ?></option>

                                            <?php echo $role_value['name']; ?>


                                            <?php
                                        }
                                        ?>

                                    </select>

                                    <span class="text-danger"><?php echo form_error('Items'); ?></span>

                                </div>

                                <div class="form-group col-md-4 col-sm-4">
                                    <label for="exampleInputEmail1"><?php echo 'Customer name'; ?></label><small class="req"> *</small>

                                    <select  id="issue_to" name="issue_to" class="form-control" required>
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('Items'); ?></span>

                                </div>
                                <!-- <div class="clearfix"></div> -->

                                <div class="form-group col-md-4 col-sm-4">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('issue_date'); ?></label><small class="req"> *</small>
                                    <input id="issue_date" name="issue_date" placeholder="" type="date" class="form-control"  value="<?php echo date('Y-m-d'); ?>" />
                                    <span class="text-danger"><?php echo form_error('issue_date'); ?></span>
                                </div>
                                <!-- <div class="clearfix"></div> -->
                                <div class="form-group col-md-4 col-sm-4" hidden>
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('return_date'); ?></label>
                                    <input id="return_date" name="return_date" placeholder="" type="text" class="form-control date"  value="<?php echo set_value('return_date'); ?>" readonly/>
                                    <span class="text-danger"><?php echo form_error('return_date'); ?></span>
                                </div>

                                <div class="form-group col-md-4 col-sm-4">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('note'); ?></label>
                                    <textarea name="note" class="form-control" id="note"/><?php echo set_value('note'); ?></textarea>
                                    <span class="text-danger"><?php echo form_error('note'); ?></span>
                                </div>
                                <?php // echo $this->Staff_model->getEmployeeId(); die(); ?>
                                <div class="form-group col-md-4 col-sm-4" style="visibility: hidden">
                                    <label for="exampleInputEmail1"><?php echo $this->lang->line('issue_by'); ?></label><small class="req"> *</small>
                                    <select class="form-control " name="issue_by">
                                        <option value=""><?php echo $this->lang->line('select'); ?></option>
<!--                                        <option selected value="--><?php //echo $this->session->userdata('admin')['username'] .' ('. $this->session->userdata('admin')['id'].')'; ?><!--">--><?php //echo $this->session->userdata('admin')['username'] .' ('. $this->session->userdata('admin')['id'].')'; ?><!--</option>-->
                                        <?php
//                                         foreach ($staff as $key => $value) {
                                         ?>
                                         <option selected value="<?php echo $staff['name'] . ' (' . $staff['employee_id'] . ')'; ?>"><?php echo $staff['name'] . ' (' . $staff['employee_id'] . ')'; ?></option>

                                        <?php
//                                         }
                                        ?>
                                    </select>
                                    <span class="text-danger"><?php echo form_error('issue_by'); ?></span>

                                </div>
                                <div class="clearfix"></div>



                                <hr>

                                <div class="col-md-12">
                                    <div class="row">



                                        <div class="form-group col-sm-2">
                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('item_category'); ?></label><small class="req"> *</small>

                                            <select  id="item_category_id" name="item_category_id[]" class="form-control">
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                <?php
                                                foreach ($itemcatlist as $item_category) {
                                                    ?>
                                                    <option value="<?php echo $item_category['id'] ?>"<?php
                                                    if (set_value('item_category_id') == $item_category['id']) {
                                                        echo "selected = selected";
                                                    }
                                                    ?>><?php echo $item_category['item_category'] ?></option>

                                                    <?php
                                                }
                                                ?>
                                            </select>
                                            <span class="text-danger"><?php echo form_error('item_category_id'); ?></span>
                                        </div>
                                        <div class="form-group col-sm-2">
                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('item'); ?></label><small class="req"> *</small>

                                            <select  id="item_id" name="item_id[]" class="form-control" >
                                                <option value=""><?php echo $this->lang->line('select'); ?></option>

                                            </select>
                                            <span class="text-danger"><?php echo form_error('item_id'); ?></span>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('quantity'); ?></label><small class="req"> *</small>
                                            <input  class="form-control" id="quantity" type="number" name="quantity[]" onchange="getPrice(this)" />
                                            <div id="div_avail">
                                                <span>Available Quantity : </span>
                                                <span id="item_available_quantity">0</span>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('discount'); ?></label>
                                            <input  class="form-control" name="discount[]" type="number" id="discount" onchange="getDiscount(this)"/>
                                            <!--                                            <div id="div_discount" hidden>-->
                                            <!--                                                <span>Price </span>-->
                                            <!--                                                <span id="price">0</span>-->
                                            <!--                                            </div>-->
                                        </div>
                                        <div class="form-group col-md-2">
                                            <label for="exampleInputEmail1"><?php echo $this->lang->line('price'); ?></label><small class="req"> *</small>
                                            <input  class="form-control" name="price[]" type="number" id="price"/>
                                            <!--                                            <div id="div_price" hidden>-->
                                            <!--                                                <span>Price </span>-->
                                            <!--                                                <span id="price">0</span>-->
                                            <!--                                            </div>-->
                                        </div>
                                        <div class="form-group col-md-1">
                                            <div class="form-group">
                                                <div class="input-group-btn">
                                                    <button class="btn blue-hoki ADD" type="button" style="margin-top: 20px;"  onclick="item_details();"> <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="itemDetails">
                                    </div>

                                </div> 



                            </div>       
                        </div><!-- /.box-body -->
                        <div class="box-footer">
                            <div class="row">
                                <div class="form-group col-md-2"></div>
                                <div class="form-group col-md-2 text-right">Total</div>
                                <div class="form-group col-md-2">
                                    <div class="form-group">
                                        <div class="input-group-btn">
                                            <input  class="form-control" id="totalQuantity" disabled/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <div class="form-group">
                                        <div class="input-group-btn">
                                            <input  class="form-control" id="totalDiscount" disabled/>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <div class="form-group">
                                        <div class="input-group-btn">
                                            <input  class="form-control" id="totalPrice" disabled/>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="box-footer">
                            <input  class="form-control" name="tPrice" value="" style="display: none"/>
                            <button type="submit" class="allot-fees btn btn-primary btn-sm pull-right" id="load" data-loading-text="<i class='fa fa-spinner fa-spin '></i> Please Wait.."><?php echo $this->lang->line('submit'); ?>
                            </button>
                        </div>
                    </form>
                </div>

            </div><!--/.col (right) -->
        </div>
        <div class="row">
            <div class="col-md-12">
            </div><!--/.col (right) -->
        </div>   <!-- /.row -->
    </section><!-- /.content -->
</div><!-- /.content-wrapper -->


<script type="text/javascript">

    var base_url = '<?php echo base_url() ?>';
    function populateItem(item_id_post, item_category_id_post) {
        if (item_category_id_post != "") {
            $('#item_id').html("");
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "admin/itemstock/getItemByCategory",
                data: {'item_category_id': item_category_id_post},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj)
                    {
                        var select = "";
                        if (item_id_post == obj.id) {
                            var select = "selected=selected";
                        }
                        div_data += "<option value=" + obj.id + " " + select + ">" + obj.name + "</option>";
                    });
                    $('#item_id').append(div_data);
                }

            });
        }
    }

    function populateItemNew(item_id_post, item_category_id_post,room) {
        if (item_category_id_post != "") {
            $('#item_id'+room).html("");
            var div_data = '<option value=""><?php echo $this->lang->line('select'); ?></option>';
            $.ajax({
                type: "GET",
                url: base_url + "admin/itemstock/getItemByCategory",
                data: {'item_category_id': item_category_id_post},
                dataType: "json",
                success: function (data) {
                    $.each(data, function (i, obj)
                    {
                        var select = "";
                        if (item_id_post == obj.id) {
                            var select = "selected=selected";
                        }
                        div_data += "<option value=" + obj.id + " " + select + ">" + obj.name + "</option>";
                    });
                    $('#item_id'+room).append(div_data);
                }

            });
        }
    }


    $(document).on('change', '#item_category_id', function (e) {
        $('#item_id').html("");
        var item_category_id = $(this).val();
        populateItem(0, item_category_id);
    });
    $(document).on('change', '#item_id', function (e) {
        $('#div_avail').hide();
        var item_id = $(this).val();
        availableQuantity(item_id);

    });

    function getItem(selected){
        room = selected.getAttribute("data-room");
        selectedItem = selected.value;
//        console.log(room + " " + selectedItem)
//        $('#item_id+room').html("");
        var item_category_id = selectedItem;
        populateItemNew(0, item_category_id, room);
    }

    function getQuantity(selected) {
        room = selected.getAttribute("data-room");
        item_id = selected.value;
        $('#div_avail'+room).hide();
        var item_id = item_id;
        availableQuantityNew(item_id,room);

    };

    function availableQuantity(item_id) {
        if (item_id != "") {
            $('#item_available_quantity').html("");
            var div_data = '';
            $.ajax({
                type: "GET",
                url: base_url + "admin/item/getAvailQuantity",
                data: {'item_id': item_id},
                dataType: "json",
                success: function (data) {

                    $('#item_available_quantity').html(data.available);
                    $('#div_avail').show();
                }

            });
        }
    }

    function availableQuantityNew(item_id, room) {
        if (item_id != "") {
            $('#item_available_quantity'+room).html("");
            var div_data = '';
            $.ajax({
                type: "GET",
                url: base_url + "admin/item/getAvailQuantity",
                data: {'item_id': item_id},
                dataType: "json",
                success: function (data) {

                    $('#item_available_quantity'+room).html(data.available);
                    $('#div_avail'+room).show();
                }

            });
        }
    }

    function getPrice(selected)
    {
        quantity = selected.value;
        item_id = document.getElementById("item_id").value;
        price = document.getElementById("price").value;
        $.ajax({
            type: "GET",
            url: base_url + "admin/sellitem/getPrice",
            data: {'item_id': item_id},
            dataType: "json",
            success: function (data) {
                newPrice = quantity*data;
                $('#price').val(newPrice);

                old = $('#quantity').val();
                if(old != "")
                {
                    old = parseInt(old);
                }
                var num = $('#itemDetails').find('input[name="quantity[]"]');
                var totalItems = num.length;
                totalSum = 0;
                var i = 0;
                for(i = 0; i < totalItems; i++) {
                    if (num[i].value !== "") {
                        totalSum = totalSum + parseInt(num[i].value);
                    }
                }
                $('#totalQuantity').val(old + totalSum);
            }

        });
    }

    function getPriceNew(selected)
    {
        quantity = selected.value;
        room = selected.getAttribute("data-room");
        item_id = document.getElementById("item_id"+room).value;
        price = document.getElementById("price"+room).value;
        $.ajax({
            type: "GET",
            url: base_url + "admin/sellitem/getPrice",
            data: {'item_id': item_id},
            dataType: "json",
            success: function (data) {
                newPrice = quantity*data;
                $('#price'+room).val(newPrice);


                old = $('#quantity').val();
                if(old != "")
                {
                    old = parseInt(old);
                }
                var num = $('#itemDetails').find('input[name="quantity[]"]');
                var totalItems = num.length;
                totalSum = 0;
                var i = 0;
                for(i = 0; i < totalItems; i++) {
                    if (num[i].value !== "") {
                        totalSum = totalSum + parseInt(num[i].value);
                    }
                }
                $('#totalQuantity').val(old + totalSum);

            }

        });
    }

    function getDiscount(selected)
    {
        discount = selected.value;
        price = document.getElementById("price").value;
        quantity = document.getElementById("quantity").value;
        item_id = document.getElementById("item_id").value;
        newPrice = price-discount;
        $.ajax({
            type: "GET",
            url: base_url + "admin/sellitem/getPrice",
            data: {'item_id': item_id},
            dataType: "json",
            success: function (data) {
                newPrice = (quantity*data)-discount;
                $('#price').val(newPrice);

                old = $('#discount').val();
                if(old != "")
                {
                    old = parseInt(old);
                }
                var num = $('#itemDetails').find('input[name="discount[]"]');
                var totalItems = num.length;
                totalSum = 0;
                var i = 0;
                for(i = 0; i < totalItems; i++) {
                    if (num[i].value !== "") {
                        totalSum = totalSum + parseInt(num[i].value);
                    }
                }
                $('#totalDiscount').val(old + totalSum);

                old = $('#price').val();
                if(old != "")
                {
                    old = parseInt(old);
                }
                var num = $('#itemDetails').find('input[name="price[]"]');
                var totalItems = num.length;
                totalSum = 0;
                var i = 0;
                for(i = 0; i < totalItems; i++) {
                    if (num[i].value !== "") {
                        totalSum = totalSum + parseInt(num[i].value);
                    }
                }
                $('#totalPrice').val(old + totalSum);
                $('input[name="tPrice"]').val(old + totalSum);
            }
        });
    }

    function getDiscountNew(selected)
    {
        discount = selected.value;
        room = selected.getAttribute("data-room");
        price = document.getElementById("price"+room).value;
        quantity = document.getElementById("quantity"+room).value;
        item_id = document.getElementById("item_id"+room).value;
        newPrice = price-discount;
        $.ajax({
            type: "GET",
            url: base_url + "admin/sellitem/getPrice",
            data: {'item_id': item_id},
            dataType: "json",
            success: function (data) {
                newPrice = (quantity*data)-discount;
                $('#price'+room).val(newPrice);

                old = $('#discount').val();
                if(old != "")
                {
                    old = parseInt(old);
                }
                var num = $('#itemDetails').find('input[name="discount[]"]');
                var totalItems = num.length;
                totalSum = 0;
                var i = 0;
                for(i = 0; i < totalItems; i++) {
                    if (num[i].value !== "") {
                        totalSum = totalSum + parseInt(num[i].value);
                    }
                }
                $('#totalDiscount').val(old + totalSum);

                old = $('#price').val();
                if(old != "")
                {
                    old = parseInt(old);
                }
                var num = $('#itemDetails').find('input[name="price[]"]');
                var totalItems = num.length;
                totalSum = 0;
                var i = 0;
                for(i = 0; i < totalItems; i++) {
                    if (num[i].value !== "") {
                        totalSum = totalSum + parseInt(num[i].value);
                    }
                }
                $('#totalPrice').val(old + totalSum);
                $('input[name="tPrice"]').val(old + totalSum);
            }
        });
    }



    $("input[name=account_type]:radio").change(function () {
        var user = $('input[name=account_type]:checked').val();
        getIssueUser(user);



    });
    function getIssueUser(usertype) {
        $('#issue_to').html("");
        var div_data = "";
        $.ajax({
            type: "POST",
            url: base_url + "admin/sellitem/getUser",
            data: {'usertype': usertype},
            dataType: "json",
            success: function (data) {

                $.each(data.result, function (i, obj)
                {
                    if (data.usertype == "admin") {
                        name = obj.username;
                    } else {
                        name = obj.name + " " + obj.surname + " (" + obj.employee_id + ")";

                    }
                    div_data += "<option value=" + obj.id + ">" + name + "</option>";
                });
                $('#issue_to').append(div_data);
            }

        });
    }

    $("#issueitem").submit(function (e)
    {
        var data = $(this).serializeArray();
        var issue_to = $('#issue_to option:selected').text();
        data.push({name: 'issue_to_name', value: issue_to});

        var $this = $('.allot-fees');
        $this.button('loading');
        e.preventDefault();
        var postData = data;
        var formURL = $(this).attr("action");
        $.ajax(
                {
                    url: formURL,
                    type: "POST",
                    data: postData,
                    dataType: 'Json',
                    success: function (data, textStatus, jqXHR)
                    {
                        if (data.status == "fail") {
                            var message = "";
                            $.each(data.error, function (index, value) {

                                message += value;
                            });
                            errorMsg(message);
                        } else {
                            $('#item_available_quantity').html("");
                            $('#div_avail').css('display', 'none');
                            document.getElementById("issueitem").reset();
                            window.location.reload();
                            successMsg(data.message);
                        }

                        $this.button('reset');
                    },
                    error: function (jqXHR, textStatus, errorThrown)
                    {
                        $this.button('reset');
                    }
                });

    });
</script>
<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/js/savemode.js"></script>

<link rel="stylesheet" href="<?php echo base_url(); ?>backend/plugins/select2/select2.min.css">
<script src="<?php echo base_url(); ?>backend/plugins/select2/select2.full.min.js"></script>
<script>
    var base_url = '<?php echo base_url() ?>';
    $('#issue_to').select2({
        placeholder: 'Select a Student',
        ajax: {
            url:  base_url + 'admin/sellitem/getStudents',
            dataType: 'json',
            delay: 250,
            data: function (data) {
                return {
                    searchTerm: data.term // search term
                };
            },
            processResults: function (response) {
                return {
                    results:response
                };
            },
            cache: true
        }
    });


</script>

<script>
    var room = 1;
    function item_details() {
        room++;
        var objTo = document.getElementById('itemDetails');
        var divtest = document.createElement("div");
//        student = document.getElementById('student').value;
//        feetype_id = document.getElementById('feetype_id').getAttribute('data-code');
//        alert(feetype_id);
        divtest.setAttribute("class", "form-group removeclass"+room);
        var rdiv = 'removeclass'+room;
        divtest.innerHTML ='<div class="row"><div class="col-md-2"><div class="form-group">\n'+
            '<select id="item_category'+room+'" name="item_category_id[]" data-room="'+room+'" class="form-control item_category" onchange="getItem(this)" >\n'+
            '<option disabled selected>Item Category *</option></select></div></div>\n'+
            '<div class="col-md-2" id="itemDiv'+room+'"><div class="form-group">\n'+
            '<select id="item_id'+room+'" name="item_id[]" class="form-control" onchange="getQuantity(this)" data-room="'+room+'">\n'+
            '</select></div></div><div class="col-md-2"><div class="form-group">\n'+
            '<input id="quantity'+room+'" name="quantity[]"  type="number" class="form-control" onchange="getPriceNew(this)" data-room="'+room+'" required/>\n'+
            '<div id="div_avail'+room+'" hidden><span>Available Quantity : </span><span id="item_available_quantity'+room+'">0</span></div>\n'+
            '</div></div>\n'+
            '<div class="col-md-2"><div class="form-group">\n'+
            '<input id="discount'+room+'" name="discount[]"  type="number" class="form-control" onchange="getDiscountNew(this)" data-room="'+room+'" required/>\n'+
            '</div></div>\n'+
            '<div class="col-md-2"><div class="form-group">\n'+
            '<input id="price'+room+'" name="price[]"  type="number" class="form-control" required/>\n'+
            '</div></div>\n'+
            '<button class="btn btn-danger" type="button" style="margin-left: 5px;" onclick="remove_product_details('+ room +');">\n'+
            '<span class="glyphicon glyphicon-minus" aria-hidden="true"></span>\n' +
            '</button><div class="clear"></div></div>';
        objTo.appendChild(divtest);
        var html= document.getElementById('item_category_id');
        $("#" + 'item_category' + room).html(html.innerHTML);
        /*$(".feeType").select2();*/
    }
    function remove_product_details(rid) {
        $('.removeclass'+rid).remove();
    }
</script>
