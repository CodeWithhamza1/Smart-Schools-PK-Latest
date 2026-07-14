<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>
    .dataTables_filter,.dt-buttons{
        display: none;
    }
</style>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-print"></i> Create Account </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i
                                class="fa fa-search"></i> Create Account</h3>
                    </div>
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('accounts/create_account') ?>" method="post"
                              class="">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label>Account Number</label>
                                        <small class="req"> *</small>
                                        <input type="text"  id="account_number" value="" name="account_number" class="form-control " required />
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label>Account Name</label>
                                        <small class="req"> *</small>
                                        <input type="text" id="account_name" value="" name="account_name" class="form-control " required />
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label>Account Type</label>
                                        <small class="req"> *</small>
                                        <select autofocus="" required id="account_type" name="account_type" class="form-control ">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
<!--                                            --><?php
//                                            foreach ($account_types as $account_type) {
//                                                ?>
<!--                                                <option value="--><?php //echo $account_type['account_type'] ?><!--">--><?php //echo $account_type['account_type'] ?><!--</option>-->
<!--                                                --><?php
//                                            }
//                                            ?>
                                            <option value="Asset">Asset</option>
                                            <option value="Liability">Liability</option>
                                            <option value="Income">Income</option>
                                            <option value="Expense">Expense</option>
                                            <option value="Equity">Equity</option>
                                        </select>
                                    </div>
                                </div>


                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">

                                        <button type="submit" class="btn btn-primary  pull-right">Save</button>
                                    </div>
                                </div>
                                <?php echo validation_errors(); ?>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages">
                            <div class="download_label"><?php echo "Create Voucher"; ?></div>
                            <div class="box-tools pull-right">

                            </div>

                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                <tr>

                                    <th>Account</th>
                                    <th>Account Number</th>
                                    <th>Account Type</th>
                                    <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach (($accounts) as $index=> $account) {
                                    ?>
                                    <tr>
                                        <td><?php echo $account['account_name']?></td>
                                        <td><?php echo $account['account_number']?></td>
                                        <td><?php echo $account['account_type']?></td>
                                        <td></td>
                                        <td class="mailbox-date pull-right">
                                            <a data-placement="left" href="<?php echo base_url(); ?>accounts/delete_account/<?php echo $account['id']; ?>"class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo "Deleting this record also delete it relevant entry so be careful as this action is irreversible" ?>');">
                                                <i class="fa fa-remove"></i>
                                            </a>
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
    </section>
</div>

<script>
    $(document).ready(function () {
        // $(".select2").select2({});
        $('#debit_amount').on('keyup',function(){
            $('#credit_amount').val($(this).val());
        });
        $('#credit_amount').on('keyup',function(){
            $('#debit_amount').val($(this).val());
        });
        $('#voucher_type').on('change', function (e) {
            var type = $(this).val();
            $('#credit_account').val('');
            $('#debit_account').val('');
            if(type=="bank_payment_voucher"){
                $('.all_credit_account').hide();//hide option
                $('.all_credit_account.bank_account').show();//show option
                $('.all_debit_account').show();//show option

            }
            else if(type=="cash_payment_voucher"){
                $('.all_credit_account').hide();//hide option
                $('.all_credit_account.cash_account').show();//show option
                $('.all_debit_account').show();//show option

            }
            else if(type=="bank_receipt_voucher"){
                $('.all_debit_account').hide();//hide option
                $('.all_debit_account.bank_account').show();//show option
                $('.all_credit_account').show();//show option

            }
            else if(type=="cash_receipt_voucher"){
                $('.all_debit_account').hide();//hide option
                $('.all_debit_account.cash_account').show();//show option
                $('.all_credit_account').show();//show option

            }
            else{
                $('.all_credit_account').show();//show option
                $('.all_debit_account').show();//show option
            }
        });
    });

</script>