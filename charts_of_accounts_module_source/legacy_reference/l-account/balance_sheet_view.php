<?php
$currency_symbol = $this->customlib->getSchoolCurrencyFormat();
?>
<style>

</style>
<div class="content-wrapper" style="min-height: 946px;">
    <section class="content-header">
        <h1><i class="fa fa-print"></i> Balance Sheet </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-search"></i> Balance Sheet</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="box">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Assets</h3>
                                    </div>
                                    <div class="box-body">
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Account</th>
                                                <th>Balance</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($assets as $asset): ?>
                                                <tr>
                                                    <td><?php echo $asset->account_name; ?></td>
                                                    <td><?php echo number_format($asset->total_debit - $asset->total_credit, 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="box-footer">
                                        <strong>Total Assets: <?php echo number_format(array_sum(array_map(function($asset) { return $asset->total_debit - $asset->total_credit; }, $assets)), 2); ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="box">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Liabilities</h3>
                                    </div>
                                    <div class="box-body">
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Account</th>
                                                <th>Balance</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($liabilities as $liability): ?>
                                                <tr>
                                                    <td><?php echo $liability->account_name; ?></td>
                                                    <td><?php echo number_format($liability->total_debit - $liability->total_credit, 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="box-footer">
                                        <strong>Total Liabilities: <?php echo number_format(array_sum(array_map(function($liability) { return $liability->total_debit - $liability->total_credit; }, $liabilities)), 2); ?></strong>
                                    </div>
                                </div>
                                <div class="box">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Equity</h3>
                                    </div>
                                    <div class="box-body">
                                        <table class="table table-bordered">
                                            <thead>
                                            <tr>
                                                <th>Account</th>
                                                <th>Balance</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($equity as $eq): ?>
                                                <tr>
                                                    <td><?php echo $eq->account_name; ?></td>
                                                    <td><?php echo number_format($eq->total_debit - $eq->total_credit, 2); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="box-footer">
                                        <strong>Total Equity: <?php echo number_format(array_sum(array_map(function($eq) { return $eq->total_debit - $eq->total_credit; }, $equity)), 2); ?></strong>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <strong>Total Liabilities & Equity: <?php echo number_format(array_sum(array_map(function($liability) { return $liability->opening_balance + $liability->total_debit - $liability->total_credit; }, $liabilities)) + array_sum(array_map(function($eq) { return $eq->opening_balance + $eq->total_debit - $eq->total_credit; }, $equity)), 2); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
