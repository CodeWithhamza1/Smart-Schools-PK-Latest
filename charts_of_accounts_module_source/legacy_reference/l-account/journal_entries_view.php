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
                content += '<p><strong>Voucher Narration:</strong> ' + data.journal_entry.voucher_narration + '</p>';
                content += '<hr>';
                content += '<h4>Details</h4>';
                content += '<table class="table table-bordered"><thead><tr><th>Accounts</th><th>Debit Amount</th><th>Credit Amount</th><th>Description</th></tr></thead><tbody>';
                // Group data by debit accounts
                const groupedDetails = {};
                data.journal_entry_details.forEach(function(detail) {
                    const key = detail.debit_account_id;
                    if (!groupedDetails[key]) {
                        groupedDetails[key] = {
                            debit_account_name: detail.debit_account_name,
                            debit_account_number: detail.debit_account_number,
                            total_debit_amount: 0,
                            credit_entries: []
                        };
                    }
                    groupedDetails[key].total_debit_amount += parseFloat(detail.debit_amount || 0);
                    groupedDetails[key].credit_entries.push(detail);
                });

                // Generate table rows for grouped data
                let totalDebit = 0;
                let totalCredit = 0;
                for (const key in groupedDetails) {
                    const group = groupedDetails[key];
                    content += '<tr>';
                    content += '<td>' + group.debit_account_name + ' (' + group.debit_account_number + ')</td>';
                    content += '<td>' + group.total_debit_amount.toFixed(2) + '</td>';
                    content += '<td></td>';
                    content += '<td></td>';
                    content += '</tr>';

                    totalDebit += group.total_debit_amount;

                    group.credit_entries.forEach(function(creditEntry) {
                        content += '<tr>';
                        content += '<td>' + creditEntry.credit_account_name + ' (' + creditEntry.credit_account_number + ')</td>';
                        content += '<td></td>';
                        content += '<td>' + ((creditEntry.credit_amount) || '') + '</td>';
                        content += '<td>' + (creditEntry.description || '') + '</td>';
                        content += '</tr>';

                        totalCredit += parseFloat(creditEntry.credit_amount || 0);
                    });
                }

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
                var content =  '<p><strong>Voucher Date:</strong> ' + data.journal_entry.voucher_date + '</p>';
                content += '<p><strong>Voucher Type:</strong> ' + convertToHeading(data.journal_entry.voucher_type) + '</p>';
                content += '<p><strong>Voucher Number:</strong> ' + data.journal_entry.voucher_number + '</p>';
                content += '<p><strong>Voucher Narration:</strong> ' + data.journal_entry.voucher_narration + '</p>';
                content += '<hr>';
                content += '<h4>Details</h4>';
                content += '<table class="table table-bordered"><thead><tr><th>Accounts</th><th>Debit Amount</th><th>Credit Amount</th><th>Description</th></tr></thead><tbody>';
                // Group data by debit accounts
                const groupedDetails = {};
                data.journal_entry_details.forEach(function(detail) {
                    const key = detail.debit_account_id;
                    if (!groupedDetails[key]) {
                        groupedDetails[key] = {
                            debit_account_name: detail.debit_account_name,
                            debit_account_number: detail.debit_account_number,
                            total_debit_amount: 0,
                            credit_entries: []
                        };
                    }
                    groupedDetails[key].total_debit_amount += parseFloat(detail.debit_amount || 0);
                    groupedDetails[key].credit_entries.push(detail);
                });

                // Generate table rows for grouped data
                let totalDebit = 0;
                let totalCredit = 0;
                for (const key in groupedDetails) {
                    const group = groupedDetails[key];
                    content += '<tr>';
                    content += '<td>' + group.debit_account_name + ' (' + group.debit_account_number + ')</td>';
                    content += '<td>' + group.total_debit_amount.toFixed(2) + '</td>';
                    content += '<td></td>';
                    content += '<td></td>';
                    content += '</tr>';

                    totalDebit += group.total_debit_amount;

                    group.credit_entries.forEach(function(creditEntry) {
                        content += '<tr>';
                        content += '<td>' + creditEntry.credit_account_name + ' (' + creditEntry.credit_account_number + ')</td>';
                        content += '<td></td>';
                        content += '<td>' + ((creditEntry.credit_amount) || '') + '</td>';
                        content += '<td>' + (creditEntry.description || '') + '</td>';
                        content += '</tr>';

                        totalCredit += parseFloat(creditEntry.credit_amount || 0);
                    });
                }

                // Add total row
                content += '<tr>';
                content += '<td><strong>Total</strong></td>';
                content += '<td><strong>' + totalDebit.toFixed(2) + '</strong></td>';
                content += '<td><strong>' + totalCredit.toFixed(2) + '</strong></td>';
                content += '<td></td>';
                content += '</tr>';
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