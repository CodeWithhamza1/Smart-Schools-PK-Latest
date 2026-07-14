<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>

</style>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-print"></i> Journal Entries </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i
                                    class="fa fa-search"></i> Journal Entries</h3>
                    </div>
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('accounts/journal_entries') ?>" method="post"
                              class="">

                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="row">
                                <div class="col-md-6 col-sm-6">
                                    <div class="form-group">
                                        <label for="start_date">Start Date:</label>
                                        <input type="date" id="start_date" value="<?php echo $start_date; ?>" name="start_date" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-46 col-sm-6">
                                    <div class="form-group">
                                        <label for="end_date">End Date:</label>
                                        <input type="date" id="end_date" value="<?php echo $end_date; ?>"  name="end_date" class="form-control" required>
                                    </div>
                                </div>

                                <div class="col-md-12 col-sm-12 ">
                                    <div class="form-group">
                                        <!--                                        <div>&nbsp;</div>-->
                                        <button type="submit" class="btn btn-primary pull-right">Get Journal Entries</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="box-header ptbnull">
                    <h3 class="box-title titlefix">Journal Entries</h3>
                    <div class="box-tools pull-right">
                    </div><!-- /.box-tools -->
                </div><!-- /.box-header -->
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="table-responsive mailbox-messages">
                            <div class="download_label"><?php echo "Journal Entries"; ?></div>
                            <div class="box-tools pull-right">
                            </div>

                            <table class="table table-striped table-bordered table-hover example">
                                <thead>
                                <tr>

                                    <th>Sr-</th>
                                    <th>Type</th>
                                    <th>Voucher ID</th>
                                    <th>Narration</th>
                                    <th>Date</th>
                                    <th class="text-right"><?php echo $this->lang->line('action'); ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($journal_entries as $index=> $entry){
                                    $voucher_type = str_replace('_', ' ', $entry['voucher_type']);
                                    ?>
                                    <tr>
                                        <td class="mailbox-name"><?php echo $index+1; ?></td>
                                        <td><?php echo ucwords($voucher_type); ?></td>
                                        <td><?php echo $entry['voucher_number']; ?></td>
                                        <td><?php echo $entry['voucher_narration']; ?></td>
                                        <td><?php echo $entry['voucher_date']; ?></td>
                                        <td class="mailbox-date pull-right">
                                            <button data-placement="left" class="btn btn-default btn-xs"  data-toggle="tooltip" title="Print" onclick="printEntry(<?php echo $entry['id']; ?>)">
                                                <i class="fa fa-print"></i>
                                            </button>

                                            <button data-placement="left" class="btn btn-default btn-xs"  data-toggle="tooltip" title="View" onclick="viewDetails(<?php echo $entry['id']; ?>)" >
                                                <i class="fa fa-eye"></i>
                                            </button>

                                            <a data-placement="left" href="<?php echo base_url(); ?>accounts/delete_journal_entry/<?php echo $entry['id']; ?>"class="btn btn-default btn-xs"  data-toggle="tooltip" title="<?php echo $this->lang->line('delete'); ?>" onclick="return confirm('<?php echo "Deleting this record also delete it relevant entry so be careful as this action is irreversible" ?>');">
                                                <i class="fa fa-remove"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>

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
                content += '<hr>';
                content += '<h4>Details</h4>';
                content += '<table class="table table-bordered"><thead><tr><th>Accounts</th><th>Debit Amount</th><th>Credit Amount</th><th>Description</th></tr></thead><tbody>';
                
                // NO GROUPING - Show each entry separately
                let totalDebit = 0;
                let totalCredit = 0;
                
                data.journal_entry_details.forEach(function(detail) {
                    // Debit row
                    content += '<tr>';
                    content += '<td>' + detail.debit_account_name + ' (' + detail.debit_account_number + ')</td>';
                    content += '<td>' + (parseFloat(detail.debit_amount) || 0).toFixed(2) + '</td>';
                    content += '<td></td>';
                    content += '<td>' + (detail.description || '') + '</td>';
                    content += '</tr>';
                    
                    // Credit row
                    content += '<tr>';
                    content += '<td>' + detail.credit_account_name + ' (' + detail.credit_account_number + ')</td>';
                    content += '<td></td>';
                    content += '<td>' + (parseFloat(detail.credit_amount) || 0).toFixed(2) + '</td>';
                    content += '<td>' + (detail.description || '') + '</td>';
                    content += '</tr>';
                    
                    totalDebit += parseFloat(detail.debit_amount || 0);
                    totalCredit += parseFloat(detail.credit_amount || 0);
                });

                // Add total row
                content += '<tr>';
                content += '<td><strong>Total</strong></td>';
                content += '<td><strong>' + totalDebit.toFixed(2) + '</strong></td>';
                content += '<td><strong>' + totalCredit.toFixed(2) + '</strong></td>';
                content += '<td></td>';
                content += '</tr>';




                /*content += '<tr style="border:none;">';
                content += '<td style="border:none;"></td>';
                content += '<td style="border:none;"></td>';
                content += '<td style="border:none;"></td>';
                content += '<td style="border:none;"></td>';
                content += '</tr>';
                content += '<tr style="border:none;">';
                content += '<td style="border: 10px solid transparent; border-top:2px solid black;"><strong>Prepared By</strong></td>';
                content += '<td style="border: 10px solid transparent; border-top:2px solid black;"><strong>Reviewed By</strong></td>';
                content += '<td style="border: 10px solid transparent; border-top:2px solid black;"><strong>Approved By</strong></td>';
                content += '<td style="border: 10px solid transparent; border-top:2px solid black;"><strong>Received By</strong></td>';
                content += '</tr>';*/
                content += '</tbody></table>';
                content += '<div style="display: flex; justify-content: space-between; margin-top: 20px;">';
                content += '<div style="text-align: center;">';
                content += '<div>____________________________</div>';
                content += '<p><strong>Prepared By:</strong></p>';
                content += '</div>';
                content += '<div style="text-align: center;">';
                content += '<div>____________________________</div>';
                content += '<p><strong>Reviewed By:</strong></p>';
                content += '</div>';
                content += '<div style="text-align: center;">';
                content += '<div>____________________________</div>';
                content += '<p><strong>Approved By:</strong></p>';
                content += '</div>';
                content += '<div style="text-align: center;">';
                content += '<div>____________________________</div>';
                content += '<p><strong>Received By:</strong></p>';
                content += '</div>';
                content += '</div>';
                var mywindow = window.open('', 'Print', 'height=600,width=800');
                mywindow.document.write('<html><head><title>Print</title>');
                mywindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">');
                mywindow.document.write('</head><body>');
                mywindow.document.write(content);
                mywindow.document.write('</body></html>');
                mywindow.document.close();
                mywindow.onload = function() {
                    setTimeout(function() {
                        mywindow.focus(); // Focus on the new window
                        mywindow.print(); // Trigger the print dialog
                        mywindow.close(); // Close the print window after printing
                    }, 10); // Adjust the timeout as needed
                };
            }
        });
    }
    function viewDetails(id) {
        $.ajax({
            url: '<?php echo site_url('accounts/get_journal_entry_details/'); ?>' + id,
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                var je = data.journal_entry;
                var content = '<p><strong>Date:</strong> ' + je.voucher_date + '&nbsp;&nbsp;&nbsp;<strong>Ref. No.:</strong> ' + je.voucher_number + '</p>';
                content += '<p><strong>Narration:</strong> ' + (je.voucher_narration || '') + '</p>';
                content += '<table class="table table-bordered" style="margin-top:10px;">';
                content += '<thead><tr style="background:#1a6fc4;color:#fff;">';
                content += '<th>Nominal Account</th><th class="text-right">Debit</th><th class="text-right">Credit</th><th>Description</th>';
                content += '</tr></thead><tbody>';

                var totalDebit = 0, totalCredit = 0;

                data.journal_entry_details.forEach(function(d) {
                    var dAmt = parseFloat(d.debit_amount)  || 0;
                    var cAmt = parseFloat(d.credit_amount) || 0;
                    // Show debit leg if it has an account and amount > 0
                    if (dAmt > 0 && d.debit_account_name) {
                        content += '<tr>';
                        content += '<td>' + d.debit_account_name + ' (' + d.debit_account_number + ')</td>';
                        content += '<td class="text-right">' + dAmt.toFixed(2) + '</td>';
                        content += '<td></td>';
                        content += '<td>' + (d.description || '') + '</td>';
                        content += '</tr>';
                        totalDebit += dAmt;
                    }
                    // Show credit leg if it has an account and amount > 0
                    if (cAmt > 0 && d.credit_account_name) {
                        content += '<tr>';
                        content += '<td>' + d.credit_account_name + ' (' + d.credit_account_number + ')</td>';
                        content += '<td></td>';
                        content += '<td class="text-right">' + cAmt.toFixed(2) + '</td>';
                        content += '<td>' + (d.description || '') + '</td>';
                        content += '</tr>';
                        totalCredit += cAmt;
                    }
                });

                content += '<tr><td><strong>Total</strong></td>';
                content += '<td class="text-right"><strong>' + totalDebit.toFixed(2) + '</strong></td>';
                content += '<td class="text-right"><strong>' + totalCredit.toFixed(2) + '</strong></td>';
                content += '<td></td></tr>';
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
