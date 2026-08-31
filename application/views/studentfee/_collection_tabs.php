<?php $family_tab = isset($collection_tab) && $collection_tab === 'family'; ?>
<nav class="fee-workflow-tabs" aria-label="Fee collection methods">
    <a href="<?php echo site_url('studentfee'); ?>" class="<?php echo $family_tab ? '' : 'active'; ?>">
        <i class="fa fa-user"></i> Student Collection
    </a>
    <a href="<?php echo site_url('studentfee/barCodeFeeCollectView'); ?>" class="<?php echo $family_tab ? 'active' : ''; ?>">
        <i class="fa fa-users"></i> Family Collection
    </a>
</nav>
