<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>

</style>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-print"></i> Report By Account </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i
                                    class="fa fa-search"></i> Report By Account</h3>
                    </div>
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('accounts/report_by_account') ?>" method="post"
                              class="">

                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label for="start_date">Start Date:</label>
                                        <input type="date" id="start_date" value="<?php echo $start_date; ?>" name="start_date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label for="end_date">End Date:</label>
                                        <input type="date" id="end_date" value="<?php echo $end_date; ?>"  name="end_date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-group">
                                        <label>Account</label>
                                        <small class="req"> *</small>
                                        <select autofocus="" required id="account_id" name="account_id" class="form-control select2">
                                            <option value=""><?php echo $this->lang->line('select'); ?></option>
                                            <?php
                                            foreach ($accounts as $account) {
                                                ?>
                                                <option value="<?php echo $account['id'] ?>"
                                                    <?php if (set_value('account_id') == $account['id']) echo "selected=selected" ?>
                                                ><?php echo $account['account_name'] ?></option>
                                                <?php
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-12 col-sm-12 ">
                                    <div class="form-group">
<!--                                        <div>&nbsp;</div>-->
                                        <button type="submit" class="btn btn-primary pull-right">Get Report</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="box-header ptbnull">
<!--                    <h3 class="box-title titlefix">Trial Balance</h3>-->
                    <div class="box-tools pull-right">
                    </div><!-- /.box-tools -->
                </div><!-- /.box-header -->
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages">
<!--                            <div class="download_label">--><?php //echo "Trial Balance"; ?><!--</div>-->
                            <div class="download_label"><?php echo "Report By Account <br> <br>". "From: ". $start_date ."  To: ".$end_date; ?></div>
                            <div class="box-tools pull-right">
                            </div>
                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                <tr>

                                    <th>Date</th>
                                    <th>Account Number</th>
                                    <th>Account Name</th>
                                    <th>Opening</th>
                                    <th>Debit Balance</th>
                                    <th>Credit Balance</th>
                                    <th>Closing</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                $total_debit_balance = 0;
                                $total_credit_balance = 0;
                                foreach ($trial_balance as $balance) {
                                    $total_debit_balance += $balance['debit_balance'];
                                    $total_credit_balance += $balance['credit_balance'];

                                    ?>
                                    <tr>
                                        <td><?php echo $balance['voucher_date']; ?></td>
                                        <td><?php echo $balance['account_number']; ?></td>
                                        <td><?php echo $balance['account_name']; ?></td>
                                        <td><?php echo $balance['opening_balance']; ?></td>
                                        <td><?php echo number_format($balance['debit_balance'], 2); ?></td>
                                        <td><?php echo number_format($balance['credit_balance'], 2); ?></td>
                                        <td><?php echo $balance['closing_balance']; ?></td>
                                    </tr>
                                <?php } if(count($trial_balance)>0){?>
                                <tr>
                                    <td ><strong>Total Balance</strong></td>
                                    <td></td>
                                    <td></td>
                                    <td><strong><?php echo number_format($total_debit_balance, 2); ?></strong></td>
                                    <td><strong><?php echo number_format($total_credit_balance, 2); ?></strong></td>
                                    <td></td>
                                </tr>
                                <?php } ?>

                                </tbody>
                            </table><!-- /.table -->

                            <!-- Modal -->
                            <div id="journalDetailsModal" class="modal fade" role="dialog">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                                            <h4 class="modal-title">Journal Entry Details</h4>
                                        </div>
                                        <div class="modal-body" id="modal-body-content">
                                            <!-- Journal entry details will be loaded here -->
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /.mail-box-messages -->
                    </div><!-- /.box-body -->
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(function () {
        $(".select2").select2({});
    });

    function printEntry(id) {
        $.ajax({
            url: '<?php echo site_url('accounts/get_journal_entry_details/'); ?>' + id,
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                var content = '<h3>Journal Entry Details</h3>';
                content += '<p><strong>Voucher Date:</strong> ' + data.journal_entry.voucher_date + '</p>';
                content += '<p><strong>Voucher Type:</strong> ' + convertToHeading(data.journal_entry.voucher_type) + '</p>';
                content += '<p><strong>Voucher Number:</strong> ' + data.journal_entry.voucher_number + '</p>';
                content += '<p><strong>Voucher Narration:</strong> ' + data.journal_entry.voucher_narration + '</p>';
                content += '<hr>';
                content += '<h4>Details</h4>';
                content += '<table class="table table-bordered"><thead><tr><th>Debit Account</th><th>Debit Amount</th><th>Credit Account</th><th>Credit Amount</th><th>Description</th></tr></thead><tbody>';
                data.journal_entry_details.forEach(function (detail) {
                    content += '<tr>';
                    content += '<td>' + detail.debit_account_name + ' (' + detail.debit_account_number + ')</td>';
                    content += '<td>' + detail.debit_amount + '</td>';
                    content += '<td>' + detail.credit_account_name + ' (' + detail.credit_account_number + ')</td>';
                    content += '<td>' + detail.credit_amount + '</td>';
                    content += '<td>' + detail.description + '</td>';
                    content += '</tr>';
                });
                content += '</tbody></table>';
                var mywindow = window.open('', 'Print', 'height=600,width=800');
                mywindow.document.write('<html><head><title>Print</title>');
                mywindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
                mywindow.document.write('</head><body>');
                mywindow.document.write(content);
                mywindow.document.write('</body></html>');
                mywindow.document.close();
                mywindow.focus();
                mywindow.print();
                mywindow.close();
            }
        });
    }
    function viewDetails(id) {
        $.ajax({
            url: '<?php echo site_url('accounts/get_journal_entry_details/'); ?>' + id,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var content =  '<p><strong>Voucher Date:</strong> ' + data.journal_entry.voucher_date + '</p>';
                content += '<p><strong>Voucher Type:</strong> ' + convertToHeading(data.journal_entry.voucher_type) + '</p>';
                content += '<p><strong>Voucher Number:</strong> ' + data.journal_entry.voucher_number + '</p>';
                content += '<p><strong>Voucher Narration:</strong> ' + data.journal_entry.voucher_narration + '</p>';
                content += '<hr>';
                content += '<h4>Details</h4>';
                content += '<table class="table table-bordered"><thead><tr><th>Debit Account</th><th>Debit Amount</th><th>Credit Account</th><th>Credit Amount</th><th>Description</th></tr></thead><tbody>';
                data.journal_entry_details.forEach(function(detail) {
                    content += '<tr>';
                    content += '<td>' + detail.debit_account_name + ' (' + detail.debit_account_number + ')</td>';
                    content += '<td>' + detail.debit_amount + '</td>';
                    content += '<td>' + detail.credit_account_name + ' (' + detail.credit_account_number + ')</td>';
                    content += '<td>' + detail.credit_amount + '</td>';
                    content += '<td>' + detail.description + '</td>';
                    content += '</tr>';
                });
                content += '</tbody></table>';
                $('#modal-body-content').html(content);
                $('#journalDetailsModal').modal('show');
            }
        });
    }

    // JavaScript function to convert voucher type to heading format
    function convertToHeading(string) {
        return string.replace(/_/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase() });
    }
</script>