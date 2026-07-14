<style type="text/css">
    .collect_grp_fees{
        font-size: 15px;
        font-weight: 600;
    }

    .fees-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    .fees-list>.item {
        border-radius: 3px;
        -webkit-box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
        padding: 10px 0;
        background: #fff;
    }
    .fees-list>.item:before, .fees-list>.item:after {
        content: " ";
        display: table;
    }
    .fees-list>.item:after {
        clear: both;
    }
    .fees-list .product-img {
        float: left;
    }
    .fees-list .product-img img {
        width: 50px;
        height: 50px;
    }
    .fees-list .product-info {
        margin-left: 0px;
    }
    .fees-list .product-title {
        font-weight: 600;
        font-size: 15px;
        display: inline;
    }
    .fees-list .product-title span{

        font-size: 15px;
        display: inline;
        font-weight: 100 !important;
    }
    .fees-list .product-description {
        display: block;
        color: #999;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .fees-list-in-box>.item {
        -webkit-box-shadow: none;
        box-shadow: none;
        border-radius: 0;
        border-bottom: 1px solid #f4f4f4;
    }
    .fees-list-in-box>.item:last-of-type {
        border-bottom-width: 0;
    }
</style>
<div class="form-horizontal">
    <div class="form-group">
        <label for="inputEmail3" class="col-sm-3 control-label"><?php echo $this->lang->line('date'); ?></label>
        <div class="col-sm-9">
            <input id="date" name="collected_date" placeholder="" type="text"  class="form-control " value="<?php echo date($this->customlib->getSchoolDateFormat()); ?>" readonly="readonly" autocomplete="off">
            <span id="form_collection_collected_date_error" class="text text-danger"></span>
        </div>
    </div>


    <div class="form-group">
        <label for="inputPassword3" class="col-sm-3 control-label"> <?php echo $this->lang->line('payment') . " " . $this->lang->line('mode'); ?></label>
        <div class="col-sm-9">
            <label class="radio-inline">
                <input type="radio" name="payment_mode_fee" value="Cash" checked="checked"> <?php echo $this->lang->line('cash'); ?></label>
            <label class="radio-inline">
                <input type="radio" name="payment_mode_fee" value="JazzCash"> <?php echo $this->lang->line('cheque'); ?></label>
            <label class="radio-inline">
                <input type="radio" name="payment_mode_fee" value="DD"><?php echo $this->lang->line('dd'); ?></label>
            <span class="text-danger" id="payment_mode_error"></span>
        </div>
        <span id="form_collection_payment_mode_fee_error" class="text text-danger"></span>
    </div>
    <div class="form-group" style="display: none">
        <label for="inputPassword3" class="col-sm-3 control-label"><?php echo $this->lang->line('discount'); ?> <?php echo $this->lang->line('group'); ?></label>
        <div class="col-sm-9">
            <select class="form-control discount_group" id="discount_group">
                <!--                <option value="0">--><?php //echo $this->lang->line('select'); ?><!--</option>-->

                <?php
                foreach ($discount_not_applied as $discount){
                    $discount_amount=$discount->amount;
                    $student_fees_discount_id=$discount->student_fees_discount_id;
                    ?>
                    <option value="<?php echo $discount->amount; ?>" data-student-fees-discount-id="<?php echo $discount->student_fees_discount_id ;?>"><?php echo $discount->code; ?></option>

                <?php }

                ?>
            </select>

            <span class="text-danger" id="amount_error"></span>
        </div>
    </div>

    <div class="form-group">
        <input type="hidden" id="student_session_id"name="student_session_id" value="<?php echo $student_session_id?>">

        <label for="inputPassword3" class="col-sm-3 control-label"> <?php echo $this->lang->line('note') ?></label>

        <div class="col-sm-9">
            <textarea class="form-control" rows="3" name="fee_gupcollected_note" id="description" placeholder="">
            </textarea>
            <span id="form_collection_fee_gupcollected_note_error" class="text text-danger"></span>
        </div>
    </div>

</div>
<ul class="fees-list fees-list-in-box">
    <?php
    $row_counter  = 1;
    $total_amount = 0;
//    print_r($feearray);
    foreach ($feearray as $fee_key => $fee_value) {
        $amount_prev_paid = 0;
        $student_fees_deposite_id= $fee_value->student_fees_deposite_id;

        if($fee_value->code =="Tuition"){
        if($discount_not_applied){
            foreach ($discount_not_applied as $discount) {
                if($fee_value->student_session_id == $discount->student_session_id) {
                    $amount_to_be_pay = ($fee_value->amount) - $discount->amount;
                }
            }
        }else{
            $amount_to_be_pay = $fee_value->amount;
        }

        }else{
            $amount_to_be_pay = $fee_value->amount;
        }
        $amount_to_be_pay1= $fee_value->amount;
        if ($fee_value->is_system) {
            if($fee_value->code =="Tuition"){
                if($discount_not_applied) {

                    foreach ($discount_not_applied as $discount) {
                        if ($fee_value->student_session_id == $discount->student_session_id) {
                            $amount_to_be_pay = ($fee_value->student_fees_master_amount) - $discount->amount;
                        }
                    }
                }else{
                    $amount_to_be_pay = $fee_value->student_fees_master_amount;
                }

            }else{
                $amount_to_be_pay = $fee_value->student_fees_master_amount;
            }
            $amount_to_be_pay1= $fee_value->student_fees_master_amount;
        }
        if (is_string(($fee_value->amount_detail)) && is_array(json_decode(($fee_value->amount_detail), true))) {

            $amount_data = json_decode($fee_value->amount_detail);

            foreach ($amount_data as $amount_data_key => $amount_data_value) {
                $amount_prev_paid = $amount_prev_paid + ($amount_data_value->amount + $amount_data_value->amount_discount);
            }
            if ($fee_value->is_system) {
                if($fee_value->code =="Tuition"){
                    if (in_array($student_fees_deposite_id, $discount_applied_ids)){
                        $amount_to_be_pay = ($fee_value->student_fees_master_amount - $amount_prev_paid);

                    }else {
                        if ($discount_not_applied) {

                            foreach ($discount_not_applied as $discount) {
                                if ($fee_value->student_session_id == $discount->student_session_id) {
                                    $amount_to_be_pay = ($fee_value->student_fees_master_amount - $amount_prev_paid) - $discount->amount;
                                }
                            }
//                        $amount_to_be_pay = ($fee_value->student_fees_master_amount - $amount_prev_paid)-$discount_amount;

                        } else {
                            $amount_to_be_pay = ($fee_value->student_fees_master_amount - $amount_prev_paid);
                        }
                    }
                }else{
                    $amount_to_be_pay = ($fee_value->student_fees_master_amount - $amount_prev_paid);
                }
                $amount_to_be_pay1= $fee_value->student_fees_master_amount;

            } else {
                if($fee_value->code =="Tuition"){
                    if (in_array($student_fees_deposite_id, $discount_applied_ids)){
                        $amount_to_be_pay = ($fee_value->amount - $amount_prev_paid);

                    }else{
                        if ($discount_not_applied) {
                        foreach ($discount_not_applied as $discount) {
                            if($fee_value->student_session_id == $discount->student_session_id) {
                                $amount_to_be_pay = ($fee_value->amount - $amount_prev_paid) - $discount->amount;
                            }
                        }
//                        $amount_to_be_pay = ($fee_value->amount - $amount_prev_paid)-$discount_amount;
                        }else{
                            $amount_to_be_pay = ($fee_value->amount - $amount_prev_paid);
                        }
                    }
                }else{
                    $amount_to_be_pay = ($fee_value->amount - $amount_prev_paid);
                }
                $amount_to_be_pay1= $fee_value->amount;
            }
        }
        $total_amount = $total_amount + $amount_to_be_pay;
        if ($amount_to_be_pay >= 0) {

            ?>
            <li class="item">
                <input name="row_counter[]" type="hidden" value="<?php echo $row_counter; ?>">
                <input name="student_fees_master_id_<?php echo $row_counter; ?>" type="hidden" value="<?php echo $fee_value->id; ?>">
                <input name="fee_groups_feetype_id_<?php echo $row_counter; ?>" type="hidden" value="<?php echo $fee_value->fee_groups_feetype_id; ?>">
                <input name="fee_session_group_id<?php echo $row_counter; ?>" type="hidden" value="<?php echo $fee_value->fee_session_group_id; ?>">
                <?php     if ($fee_value->code =="Tuition") {
                ?>
                <div class="product-info" id="amount-info">
                    <?php }else{  ?>
                    <div class="product-info" id="">
                        <?php } ?>
                        <div>
                            <span class="" style="float: left;margin-left: 50%;">Discount</span>
                            <span class="" style="margin-left: 20%;">Amount</span>
                        </div>

                        <a href="#" class="product-title"><?php echo $fee_value->name; ?></a>
                        <a href="#" class="product-title"><?php echo  date('M', $this->customlib->dateyyyymmddTodateformat($fee_value->due_date)); ?></a>
                        <p id="fee_amount_error_<?php echo $row_counter; ?> " style="color: red;"></p>
                        <input class=" pull-right fee_amount_<?php echo $row_counter; ?>" id="fee_amount_<?php echo $row_counter; ?>" style="" name="fee_amount_<?php echo $row_counter; ?>" type="text" value="<?php echo $amount_to_be_pay; ?>">
                        <input class=" pull-right fee_amount1_<?php echo $row_counter; ?>" id="fee_amount1_<?php echo $row_counter; ?>" style="" name="fee_amount1_<?php echo $row_counter; ?>" type="hidden" value="<?php echo $amount_to_be_pay1; ?>">
                        <?php
                        /* if($role_id==7 || $role_id==1){
                             if ($fee_value->code =="Tuition") {
                                 if (in_array($student_fees_deposite_id, $discount_applied_ids)){
 //                                    print_r($discount_applied_ids) ;
                                     */?><!--
                                    <input class=" pull-right fee_discount_<?php /*echo $row_counter; */?>" id="fee_discount_<?php /*echo $row_counter; */?>" name="fee_discount_<?php /*echo $row_counter; */?>" type="text" value="0" >
                                    <input class=" pull-right student_fees_discount_id_<?php /*echo $row_counter; */?>" id="student_fees_discount_id_<?php /*echo $row_counter; */?>" name="student_fees_discount_id_<?php /*echo $row_counter; */?>" type="hidden" value="" >

                                <?php /*}else{*/?>
                                    <input class=" pull-right fee_discount_<?php /*echo $row_counter; */?>" id="fee_discount_<?php /*echo $row_counter; */?>" name="fee_discount_<?php /*echo $row_counter; */?>" type="text" value="<?php /*echo $discount_amount;*/?>" >
                                    <input class=" pull-right student_fees_discount_id_<?php /*echo $row_counter; */?>" id="student_fees_discount_id_<?php /*echo $row_counter; */?>" name="student_fees_discount_id_<?php /*echo $row_counter; */?>" type="hidden" value="<?php /*echo $student_fees_discount_id;*/?>" >
                                <?php /*}}else{ */?>
                                <input class=" pull-right fee_discount_<?php /*echo $row_counter; */?>" id="fee_discount_<?php /*echo $row_counter; */?>" name="fee_discount_<?php /*echo $row_counter; */?>" type="text" value="0" >
                                <input class=" pull-right student_fees_discount_id_<?php /*echo $row_counter; */?>" id="student_fees_discount_id_<?php /*echo $row_counter; */?>" name="student_fees_discount_id_<?php /*echo $row_counter; */?>" type="hidden" value="" >

                            --><?php /*}}
                        else{*/
                        if ($fee_value->code =="Tuition") {
                            if (in_array($student_fees_deposite_id, $discount_applied_ids)){
                                ?>
                                <input class=" pull-right fee_discount_<?php echo $row_counter; ?>" id="fee_discount_<?php echo $row_counter; ?>" name="fee_discount_<?php echo $row_counter; ?>" type="text" value="0"readonly >
                                <input class=" pull-right student_fees_discount_id_<?php echo $row_counter; ?>" id="student_fees_discount_id_<?php echo $row_counter; ?>" name="student_fees_discount_id_<?php echo $row_counter; ?>" type="hidden" value="" >

                            <?php }else{?>
                                <input class=" pull-right fee_discount_<?php echo $row_counter; ?>" id="fee_discount_<?php echo $row_counter; ?>" name="fee_discount_<?php echo $row_counter; ?>" type="text" value="<?php

                                //                                    echo $discount_amount;
                                foreach ($discount_not_applied as $discount) {
                                    if($fee_value->student_session_id == $discount->student_session_id) {
                                        echo $discount->amount;
                                    }
                                }
                                ?>

                                    " readonly>
                                <input class=" pull-right student_fees_discount_id_<?php echo $row_counter; ?>" id="student_fees_discount_id_<?php echo $row_counter; ?>" name="student_fees_discount_id_<?php echo $row_counter; ?>" type="hidden" value="<?php echo $student_fees_discount_id;?>" >
                            <?php }}else{ ?>
                            <input class=" pull-right fee_discount_<?php echo $row_counter; ?>" id="fee_discount_<?php echo $row_counter; ?>" name="fee_discount_<?php echo $row_counter; ?>" type="text" value="0"  readonly>
                            <input class=" pull-right student_fees_discount_id_<?php echo $row_counter; ?>" id="student_fees_discount_id_<?php echo $row_counter; ?>" name="student_fees_discount_id_<?php echo $row_counter; ?>" type="hidden" value="" readonly>

                        <?php }
                        //                        }
                        ?>
                        <!--                    <span class="pull-right">--><?php //echo $amount_to_be_pay; ?>
                        <span class="product-description">
                    <?php echo $fee_value->code; ?>
                </span
                ><span class="product-description">
                    <?php echo $fee_value->due_date; ?>
                </span>
                    </div>
            </li>

            <?php
        }

        $row_counter++;


    }

    //echo $row_counter;
    ?>
    <input class=" pull-right" id="total_row" style="" name="" type="hidden" value="<?php echo $row_counter; ?>">

</ul>

<div class="row collect_grp_fees">
    <div class="col-md-8">
       <span class="pull-right">
       <?php echo $this->lang->line('total') . " " . $this->lang->line('pay'); ?>
        </span>
    </div>
    <div class="col-md-4">
        <span class="pull-right">
            <p id="total_pay"><?php echo number_format((float) $total_amount, 2, '.', ''); ?></p>

        </span>

    </div>
</div>
<!--<script src="https://code.jquery.com/jquery-3.5.0.js"></script>-->
<script>
    $( ".discount_group" ).change(function() {

        var amount = $('option:selected', this).val();
        var total_row = $('input#total_row').val();
        var total_pay = 0;
        for(var i = 1; i < total_row; i++) {

            var fee_amount = $('div#amount-info').find('input#fee_amount1_'+i).val();
//            var fee_amount = $('div#myFeesModal').find('input#amount').val();

            var balance_amount = (parseFloat(fee_amount) - parseFloat(amount)).toFixed(2);
            if (typeof amount !== typeof undefined && amount !== false) {
//                $('div#amount-info').find('input#fee_discount_'+i).prop('readonly', true).val(amount);
                $('div#amount-info').find('input#fee_discount_'+i).val(amount);
                $('div#amount-info').find('input#fee_amount_'+i).val(balance_amount);
                var total_pay = parseInt(total_pay) + parseInt(balance_amount);


            } else {
//                $('div#amount-info').find('input#fee_discount_'+i).prop('readonly', true).val(0);
                $('div#amount-info').find('input#fee_discount_'+i).val(0);
                $('div#amount-info').find('input#fee_amount_'+i).val(fee_amount);
                var total_pay = parseInt(total_pay) + parseInt(fee_amount);

            }
        }
        $('#total_pay').text(total_pay);
    });
</script>