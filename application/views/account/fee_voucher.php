<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>
    .dataTables_filter,
    .dt-buttons {
        display: none;
    }
</style>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-print"></i> Create Fee Voucher </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> Create Fee Voucher</h3>
                    </div>
                    <div class="box-body">
                        <?php $voucher_entries = json_decode($voucher->voucher_entry) ?>
                        <form role="form" action="<?php echo site_url('accounts/store_fee_voucher') ?>" method="post">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Voucher Date</label>
                                        <small class="req"> *</small>
                                        <input type="date" value="<?php echo $voucher->voucher_date; ?>" required
                                            id="voucher_date" name="voucher_date" class="form-control " />
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Voucher Number</label>
                                        <small class="req"> *</small>
                                        <input type="text" required readonly id="voucher_number" value="<?php
                                        if ($voucher->voucher_number) {
                                            echo $voucher->voucher_number;

                                        } else {
                                            echo $voucher_number;
                                        }
                                        ?>" name="voucher_number" class="form-control " />
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Voucher Type</label>
                                        <small class="req"> *</small>
                                        <select autofocus="" required id="voucher_type" name="voucher_type"
                                            class="form-control ">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($voucher_types as $voucher_type) {
                                                ?>
                                                <option value="<?php echo $voucher_type['type'] ?>" <?php if ($voucher_entries[0]->voucher_type == $voucher_type['type'])
                                                       echo "selected=selected" ?>><?php echo $voucher_type['name'] ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <label>Voucher Narration</label>
                                        <small class="req"> *</small>
                                        <input required type="text" autofocus="" id="voucher_narration"
                                            name="voucher_narration"
                                            value="<?php echo $voucher_entries[0]->voucher_narration ?>"
                                            class="form-control " />
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-6">
                                    <div class="form-group">
                                        <label>Select Debit Account</label>
                                        <small class="req"> *</small>
                                        <select id="debit_account" name="debit_account" class="form-control select2">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($accounts as $account) {
                                                $account_class = "";
                                                if ($voucher_entries[0]->voucher_type == "bank_receipt_voucher" || $voucher_entries[0]->voucher_type == "cash_receipt_voucher") {
                                                    $account_style = "none";
                                                } else {
                                                    $account_style = "block";
                                                }

                                                if ($account['id'] == 29) {
                                                    $account_class = "cash_account";
                                                    if ($voucher_entries[0]->voucher_type == "cash_receipt_voucher") {
                                                        $account_style = "block";
                                                    }
                                                } elseif ($account['id'] == 30 || $account['id'] == 31) {
                                                    $account_class = "bank_account";
                                                    if ($voucher_entries[0]->voucher_type == "bank_receipt_voucher") {
                                                        $account_style = "block";
                                                    }
                                                }
                                                ?>

                                                <option class="all_debit_account <?php echo $account_class ?>"
                                                    style="display: <?php echo $account_style; ?>;"
                                                    data-acctype="<?php echo $account_class ?>"
                                                    value="<?php echo $account['id'] ?>">
                                                    <?php echo $account['account_name'] . "(" . $account['account_number'] . ")" ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-6">
                                    <div class="form-group">
                                        <label>Debit Amount</label>
                                        <small class="req"> *</small>
                                        <input type="number" step="1" autofocus="" id="debit_amount" name="debit_amount"
                                            required class="form-control " />
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-6">
                                    <div class="form-group">
                                        <label>Select Credit Account</label>
                                        <small class="req"> *</small>
                                        <select id="credit_account" name="credit_account" class="form-control select2"
                                            required>
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($accounts as $account) {
                                                $account_class = "";
                                                if ($voucher_entries[0]->voucher_type == "bank_payment_voucher" || $voucher_entries[0]->voucher_type == "cash_payment_voucher") {
                                                    $account_style = "none";
                                                } else {
                                                    $account_style = "block";
                                                }
                                                if ($account['id'] == 29) {
                                                    $account_class = "cash_account";
                                                    if ($voucher_entries[0]->voucher_type == "cash_payment_voucher") {
                                                        $account_style = "block";
                                                    }
                                                } elseif ($account['id'] == 30 || $account['id'] == 31) {
                                                    $account_class = "bank_account";
                                                    if ($voucher_entries[0]->voucher_type == "bank_payment_voucher") {
                                                        $account_style = "block";
                                                    }
                                                }
                                                ?>

                                                <option class="all_credit_account <?php echo $account_class ?>"
                                                    style="display: <?php echo $account_style; ?>;"
                                                    data-acctype="<?php echo $account_class ?>"
                                                    value="<?php echo $account['id'] ?>">
                                                    <?php echo $account['account_name'] . "(" . $account['account_number'] . ")" ?>
                                                </option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-6">
                                    <div class="form-group">
                                        <label>Credit Amount</label>
                                        <small class="req"> *</small>
                                        <input type="number" step="1" autofocus="" id="credit_amount" readonly required
                                            name="credit_amount" class="form-control " />
                                    </div>
                                </div>

                                <div class="col-md-3 col-sm-12">
                                    <div class="form-group">
                                        <label>Note / Description / Remarks</label>
                                        <input type="text" value="" autofocus="" id="description" name="description"
                                            class="form-control" />
                                    </div>
                                </div>

                                <div class="col-md-1 col-sm-12">
                                    <div class="form-group">
                                        <div>&nbsp;</div>
                                        <button type="submit" class="btn btn-primary">+</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div> -->
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> Create Fee Voucher</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo base_url(); ?>accounts/post_to_fee_journal" type="button"
                                class="btn btn-success">Post to Journal</a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages">
                            <div class="download_label"><?php echo "Create Voucher"; ?></div>
                            <!-- <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                    <tr>

                                        <th>Account</th>
                                        <th>Account Number</th>
                                        <th>Description</th>
                                        <th>Debit</th>
                                        <th>Credit</th>

                                        <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    foreach ($voucher_entries as $index => $voucher_entry) {
                                        ?>
                                        <tr>
                                            <td class="mailbox-name"><?php echo $voucher_entry->debit_account_name ?></td>
                                            <td><?php echo $voucher_entry->debit_account_number ?></td>
                                            <td><?php echo $voucher_entry->voucher_narration ?></td>
                                            <td><?php echo $voucher_entry->debit_amount ?></td>
                                            <td></td>
                                            <td class="mailbox-date pull-right">
                                                <a data-placement="left"
                                                    href="<?php echo base_url(); ?>accounts/delete_entry/<?php echo $index; ?>"
                                                    class="btn btn-default btn-xs" data-toggle="tooltip"
                                                    title="<?php echo $this->lang->line('delete'); ?>"
                                                    onclick="return confirm('<?php echo "Deleting this record also delete it relevant entry so be careful as this action is irreversible" ?>');">
                                                    <i class="fa fa-remove"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="mailbox-name"><?php echo $voucher_entry->credit_account_name ?></td>
                                            <td><?php echo $voucher_entry->credit_account_number ?></td>
                                            <td><?php echo $voucher_entry->voucher_narration ?></td>
                                            <td></td>
                                            <td><?php echo $voucher_entry->credit_amount ?></td>
                                            <td class="mailbox-date pull-right">
                                                <a data-placement="left"
                                                    href="<?php echo base_url(); ?>accounts/delete_entry/<?php echo $index; ?>"
                                                    class="btn btn-default btn-xs" data-toggle="tooltip"
                                                    title="<?php echo $this->lang->line('delete'); ?>"
                                                    onclick="return confirm('<?php echo "Deleting this record also delete it relevant entry so be careful as this action is irreversible" ?>');">
                                                    <i class="fa fa-remove"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>

                                </tbody>
                            </table>/.table -->
                            <table id="studentsTable" class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Id</th>
                                        <th>Payment Type</th>
                                        <th>Name</th>
                                        <th>Admission No</th>
                                        <th>Class</th>
                                        <th>Section</th>
                                        <th>Fee Amount (<?= $currency_symbol; ?>)</th>
                                        <th>Submitted Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paid_students as $student): ?>
                                        <?php
                                        $fee_amount = 0;
                                        $submitted_date = '';
                                        $payment_type = '';
                                        if (!empty($student['amount_detail'])) {
                                            $details = json_decode($student['amount_detail'], true);
                                            if (is_array($details)) {
                                                $first = reset($details);
                                                $fee_amount = $first['amount'];
                                                $submitted_date = $first['date'];
                                                $payment_type = $first['payment_mode'];
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td><?= $student['unique_id']; ?></td>
                                            <td><?= $payment_type; ?></td>
                                            <td><?= $student['firstname'] . ' ' . $student['lastname']; ?></td>
                                            <td><?= $student['admission_no']; ?></td>
                                            <td><?= $student['class']; ?></td>
                                            <td><?= $student['section']; ?></td>
                                            <td><?= number_format((float) $fee_amount, 2); ?></td>
                                            <td><?= !empty($submitted_date) ? date("d-M-Y", strtotime($submitted_date)) : ''; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(function () {

        $('#studentsTable').DataTable({
            "pageLength": 500, // show 10 records per page
            "lengthChange": false, // hide "show x entries"
            "ordering": true, // enable sorting
            "searching": true, // enable search bar
            "language": {
                "search": "Search Student:"
            }
        });

        // $(".select2").select2({});
        <?php
        if ($voucher_entries[0]->voucher_type == "bank_receipt_voucher" || $voucher_entries[0]->voucher_type == "cash_receipt_voucher") {
        } else { ?>
            $('#debit_account').select2();
        <?php } ?>
        <?php
        if ($voucher_entries[0]->voucher_type == "bank_payment_voucher" || $voucher_entries[0]->voucher_type == "cash_payment_voucher") {
        } else { ?>
            $('#credit_account').select2();
        <?php } ?>
        $('#debit_amount').on('keyup', function () {
            $('#credit_amount').val($(this).val());
        });
        $('#credit_amount').on('keyup', function () {
            $('#debit_amount').val($(this).val());
        });
        $('#voucher_type').on('change', function (e) {
            var type = $(this).val();
            // alert(type);

            try { $('#credit_account').val('').trigger('change'); } catch (e) { }
            try { $('#debit_account').val('').trigger('change'); } catch (e) { }


            $('#credit_account').val('');
            $('#debit_account').val('');
            if (type == "bank_payment_voucher") {
                try { $('#credit_account').select2('destroy'); } catch (e) { }

                $('#debit_account').select2();

                $('.all_credit_account').hide();//hide option
                $('.all_credit_account.bank_account').show();//show option
                $('.all_debit_account').show();//show option


            }
            else if (type == "cash_payment_voucher") {
                try { $('#credit_account').select2('destroy'); } catch (e) { }
                $('#debit_account').select2();

                $('.all_credit_account').hide();//hide option
                $('.all_credit_account.cash_account').show();//show option
                $('.all_debit_account').show();//show option


            }
            else if (type == "bank_receipt_voucher") {
                $('#credit_account').select2();
                try { $('#debit_account').select2('destroy'); } catch (e) { }

                $('.all_debit_account').hide();//hide option
                $('.all_debit_account.bank_account').show();//show option
                $('.all_credit_account').show();//show option

            }
            else if (type == "cash_receipt_voucher") {
                $('#credit_account').select2();
                try { $('#debit_account').select2('destroy'); } catch (e) { }

                $('.all_debit_account').hide();//hide option
                $('.all_debit_account.cash_account').show();//show option
                $('.all_credit_account').show();//show option

            }
            else {
                $('#credit_account').select2();
                $('#debit_account').select2();
                $('.all_credit_account').show();//show option
                $('.all_debit_account').show();//show option
            }
        });
    });

</script>
