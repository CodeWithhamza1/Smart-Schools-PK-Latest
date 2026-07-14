
<script type="text/javascript">
    $(document).ready(function() {
        var date_format = '<?php echo $result = strtr($this->customlib->getSchoolDateFormat(), ['d' => 'dd', 'm' => 'mm', 'Y' => 'yyyy']) ?>';
        $('.date').datepicker({
            format: date_format,
            autoclose: true,
            todayHighlight: true
        });
    });
</script>
<?php
// Get date values from controller data or POST data or use current date as default
if (isset($date_from) && !empty($date_from)) {
    // Use date from controller (passed via AJAX)
    $date_from = $date_from;
} elseif (isset($_POST['date_from']) && !empty($_POST['date_from'])) {
    // Use date from POST data (form submission)
    $date_from = date($this->customlib->getSchoolDateFormat(), $this->customlib->datetostrtotime($_POST['date_from']));
} else {
    // Use current date as default
    $date_from = date($this->customlib->getSchoolDateFormat());
}

if (isset($date_to) && !empty($date_to)) {
    // Use date from controller (passed via AJAX)
    $date_to = $date_to;
} elseif (isset($_POST['date_to']) && !empty($_POST['date_to'])) {
    // Use date from POST data (form submission)
    $date_to = date($this->customlib->getSchoolDateFormat(), $this->customlib->datetostrtotime($_POST['date_to']));
} else {
    // Use current date as default
    $date_to = date($this->customlib->getSchoolDateFormat());
}
?>
<div class="col-sm-6 col-md-3">
    <div class="form-group">
        <label><?php echo $this->lang->line('date_from'); ?></label>
        <input name="date_from" id="date_from" placeholder="" type="text" class="form-control date" value="<?php echo $date_from; ?>"  />
        <span class="text-danger"><?php echo form_error('date_from'); ?></span>
    </div>
</div> 

<div class="col-sm-6 col-md-3">
    <div class="form-group">
        <label><?php echo $this->lang->line('date_to'); ?></label>
        <input  name="date_to" id="date_to" placeholder="" type="text" class="form-control date" value="<?php echo $date_to; ?>"  />
        <span class="text-danger"><?php echo form_error('date_to'); ?></span>
    </div>
</div> 
