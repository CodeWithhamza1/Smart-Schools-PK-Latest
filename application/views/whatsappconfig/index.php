<?php

function check_in_array($find, $array)
{
    foreach ($array as $element) {
        if ($find == $element->type) {
            return $element;
        }
    }

    $object            = new stdClass();
    $object->id        = "";
    $object->type      = "";
    $object->api_id    = "";
    $object->username  = "";
    $object->url       = "";
    $object->name      = "";
    $object->contact   = "";
    $object->password  = "";
    $object->authkey   = "";
    $object->senderid  = "";
    $object->language  = "";
    $object->is_active = "";
    return $object;
}

$twilio_result = check_in_array('twilio', $list);
$meta_result   = check_in_array('meta', $list);
$asset_version = '?v=' . time();
?>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-whatsapp"></i> <?php echo $this->lang->line('whatsapp_messaging_setting'); ?></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="nav-tabs-custom theme-shadow">
                    <div class="box-body">
                        <?php if (!empty($active_whatsapp)) { ?>
                            <div class="alert alert-success">
                                <strong>Active Provider:</strong> <?php echo ucfirst($active_whatsapp->type); ?>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-warning">
                                No active WhatsApp provider selected. Configure one provider and set status to <strong>Enabled</strong>.
                            </div>
                        <?php } ?>

                        <div class="alert alert-info">
                            After configuring provider credentials here, enable WhatsApp per event in
                            <strong>System Settings -> Notification Setting</strong>.
                        </div>
                    </div>

                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab_meta" data-toggle="tab"><?php echo $this->lang->line('meta_whatsapp_official'); ?></a></li>
                        <li><a href="#tab_twilio" data-toggle="tab"><?php echo $this->lang->line('twilio'); ?></a></li>
                        <li><a href="#tab_guide" data-toggle="tab">Setup Guide</a></li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="tab_meta">
                            <form id="metawhatsapp" action="<?php echo site_url('whatsappconfig/metawhatsapp'); ?>" class="form-horizontal" method="post">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="minheight170">
                                            <div class="col-md-7">
                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label"><?php echo $this->lang->line('access_token'); ?><small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" name="meta_access_token" value="<?php echo $meta_result->authkey; ?>">
                                                        <span class="text text-danger meta_access_token_error"></span>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label">Phone Number ID<small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" name="meta_sender_phone_number" value="<?php echo $meta_result->contact; ?>">
                                                        <small class="text-muted">Use Meta WhatsApp Cloud API Phone Number ID</small>
                                                        <span class="text text-danger meta_sender_phone_number_error"></span>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label"><?php echo $this->lang->line('language'); ?><small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" name="meta_language" value="<?php echo !empty($meta_result->language) ? $meta_result->language : ''; ?>">
                                                        <small class="text-muted">Example: en, en_US, ur</small>
                                                        <span class="text text-danger meta_language_error"></span>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label"><?php echo $this->lang->line('status'); ?><small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <select class="form-control" name="meta_status">
                                                            <?php foreach ($statuslist as $s_key => $s_value) { ?>
                                                                <option value="<?php echo $s_key; ?>" <?php echo ($meta_result->is_active == $s_key) ? "selected=selected" : ""; ?>>
                                                                    <?php echo $s_value; ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                        <span class="text text-danger meta_status_error"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-5 text text-center disblock">
                                                <a href="https://business.facebook.com/" target="_blank">
                                                    <img src="<?php echo base_url(); ?>backend/images/meta.jpg<?php echo $asset_version; ?>">
                                                    <p>https://business.facebook.com/</p>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="box-footer">
                                    <div class="col-md-offset-3">
                                        <?php if ($this->rbac->hasPrivilege('whatsapp_messaging', 'can_view')) { ?>
                                            <button type="submit" class="btn btn-primary btnleftinfo"><?php echo $this->lang->line('save'); ?></button>
                                            &nbsp;&nbsp;<span class="meta_loader"></span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane" id="tab_twilio">
                            <form id="twilio" action="<?php echo site_url('whatsappconfig/twilio'); ?>" class="form-horizontal" method="post">
                                <div class="box-body">
                                    <div class="row">
                                        <div class="minheight170">
                                            <div class="col-md-7">
                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label"><?php echo $this->lang->line('twilio_account_sid'); ?><small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" name="twilio_account_sid" value="<?php echo $twilio_result->username; ?>">
                                                        <span class="text text-danger twilio_account_sid_error"></span>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label"><?php echo $this->lang->line('authentication_token'); ?><small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <input type="password" class="form-control" name="twilio_auth_token" value="<?php echo $twilio_result->password; ?>">
                                                        <span class="text text-danger twilio_auth_token_error"></span>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label">Twilio WhatsApp Number<small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <input type="text" class="form-control" name="twilio_sender_phone_number" value="<?php echo $twilio_result->contact; ?>">
                                                        <small class="text-muted">Example: +14155238886 (Twilio Sandbox/approved sender)</small>
                                                        <span class="text text-danger twilio_sender_phone_number_error"></span>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="col-sm-5 control-label"><?php echo $this->lang->line('status'); ?><small class="req"> *</small></label>
                                                    <div class="col-sm-7">
                                                        <select class="form-control" name="twilio_status">
                                                            <?php foreach ($statuslist as $s_key => $s_value) { ?>
                                                                <option value="<?php echo $s_key; ?>" <?php echo ($twilio_result->is_active == $s_key) ? "selected=selected" : ""; ?>>
                                                                    <?php echo $s_value; ?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                        <span class="text text-danger twilio_status_error"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-5 text text-center disblock">
                                                <a href="https://console.twilio.com/" target="_blank">
                                                    <img src="<?php echo base_url(); ?>backend/images/twilio.png<?php echo $asset_version; ?>">
                                                    <p>https://console.twilio.com/</p>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="box-footer">
                                    <div class="col-md-offset-3">
                                        <?php if ($this->rbac->hasPrivilege('whatsapp_messaging', 'can_view')) { ?>
                                            <button type="submit" class="btn btn-primary btnleftinfo"><?php echo $this->lang->line('save'); ?></button>
                                            &nbsp;&nbsp;<span class="twilio_loader"></span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane" id="tab_guide">
                            <div class="box-body">
                                <h4>WhatsApp Setup Checklist</h4>
                                <ol>
                                    <li>Configure either Meta or Twilio and set status to Enabled.</li>
                                    <li>Open Notification Setting and enable WhatsApp for required events.</li>
                                    <li>Set valid Template ID for each event in Notification Setting.</li>
                                    <li>For Meta, ensure approved template and language code match exactly.</li>
                                    <li>For Twilio, ensure Content Template SID is used in event Template ID.</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
(function ($) {
    "use strict";

    var img_path = "<?php echo base_url('backend/images/loading.gif'); ?>";

    $(document).on('submit', '#twilio', function (e) {
        e.preventDefault();
        $("[class$='_error']").html('');
        $(".twilio_loader").html('<img src="' + img_path + '">');

        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function (data) {
                if (data.st === 1) {
                    $.each(data.msg, function (key, value) {
                        $('.' + key + '_error').html(value);
                    });
                } else {
                    successMsg(data.msg);
                }
                $(".twilio_loader").html('');
            },
            error: function () {
                $(".twilio_loader").html('');
            }
        });
    });

    $(document).on('submit', '#metawhatsapp', function (e) {
        e.preventDefault();
        $("[class$='_error']").html('');
        $(".meta_loader").html('<img src="' + img_path + '">');

        $.ajax({
            type: "POST",
            dataType: "JSON",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function (data) {
                if (data.st === 1) {
                    $.each(data.msg, function (key, value) {
                        $('.' + key + '_error').html(value);
                    });
                } else {
                    successMsg(data.msg);
                }
                $(".meta_loader").html('');
            },
            error: function () {
                $(".meta_loader").html('');
            }
        });
    });
})(jQuery);
</script>
