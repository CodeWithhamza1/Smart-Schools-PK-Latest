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
        <h1><i class="fa fa-random"></i> COA Mapping </h1>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-link"></i> Map Fee Types to Accounts</h3>
                    </div>
                    <div class="box-body">
                        <form role="form" action="<?php echo site_url('accounts/save_coa_mapping') ?>" method="post" class="">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <div class="table-responsive mailbox-messages">
                                <table class="table table-striped table-bordered table-hover example">
                                    <thead>
                                    <tr>
                                        <th>Fee Type</th>
                                        <th>Account</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($fee_types as $fee): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($fee['type']); ?></td>
                                            <td style="min-width:320px;">
                                                <select name="mapping[<?php echo (int)$fee['id']; ?>]" class="form-control select2">
                                                    <option value=""><?php echo $this->lang->line('select'); ?></option>
                                                    <?php foreach ($accounts as $acc): ?>
                                                        <?php $selected = (isset($mappings[$fee['id']]) && (int)$mappings[$fee['id']] === (int)$acc['id']) ? 'selected' : ''; ?>
                                                        <option value="<?php echo (int)$acc['id']; ?>" <?php echo $selected; ?>>
                                                            <?php echo $acc['account_name'] . " (" . $acc['account_number'] . ")"; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="row">
                                <div class="col-md-12 col-sm-12">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary pull-right">Save Mapping</button>
                                    </div>
                                </div>
                            </div>
                            <?php if ($this->session->flashdata('msg')) { echo $this->session->flashdata('msg'); } ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(function () {
        try{ $('.select2').select2(); }catch(e){}
    });
</script>



