<style type="text/css">
    .lead_template {

        font-size: 16px;
        font-weight: 300;
        line-height: 1.4;
        padding: 0px;
        margin-bottom: 5px;
    }
    .lead_template_variable {
        font-size: 16px;
        font-weight: 300;
        line-height: 1.4;
        padding: 0px;
        margin-bottom: 5px;
    }
    .template_note_list {
        margin: 0;
        padding-left: 18px;
    }
    .template_note_list li {
        margin-bottom: 4px;
    }
</style>

<div class="row">
    <div class="col-md-12">
        <p class="lead_template"><?php echo $this->lang->line($record->type); ?></p>
        <input type="hidden" name="temp_id" value="<?php echo $record->id; ?>">
        <div class="form-group">
            <label for="form_message"><?php echo $this->lang->line('subject'); ?></label><small class="req"> *</small>
            <input type="text" id="template_subject" name="template_subject" class="form-control" value="<?php echo $record->subject; ?>">
            <div class="text text-danger template_subject_error"></div>            
        </div>
    </div>
    <div class="col-md-12">
       
    
        <div class="form-group">
            <label for="form_message"><?php echo $this->lang->line('template_id'); ?></label>
            <input type="text" id="template_id" name="template_id" class="form-control" value="<?php echo $record->template_id; ?>">
            <p class="text-muted" style="margin-top:6px;">
                SMS: DLT/Provider Template ID. WhatsApp: Meta approved template name or Twilio Content SID (for example, <code>HXxxxxxxxxxxxx</code>).
            </p>
            <div class="text text-danger template_id_error"></div>            
        </div>
    </div>
	<div class="col-md-12">        
        <div class="form-group">
            <label for="form_message"><?php echo $this->lang->line('template'); ?></label><small class="req"> *</small>
            <textarea id="form_message" name="template_message" class="form-control" rows="7"><?php echo $record->template; ?></textarea>
            <div class="text text-danger template_message_error"></div>
            <div class="hide_in_read">
                <p class="lead_template_variable"><?php echo $this->lang->line('you_can_use_variables'); ?></p>
                <b>
                    <?php echo $record->variables; ?>
                </b>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="alert alert-info" style="margin-top:8px;">
            <strong>WhatsApp Compliance and Template Notes</strong> (operational guidance, not legal advice)
            <ul class="template_note_list">
                <li>Use WhatsApp only for opted-in, transactional/academic notifications.</li>
                <li>Do not send promotional content without explicit consent and a clear opt-out process.</li>
                <li>For Meta Cloud API, message body placeholders and language must exactly match the approved template.</li>
                <li>For Twilio WhatsApp, map this event to the correct Content Template SID in Template ID.</li>
                <li>Use only listed dynamic variables for this event to avoid provider template mismatch errors.</li>
            </ul>
        </div>
    </div>

</div>
