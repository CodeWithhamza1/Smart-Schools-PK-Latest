<!-- Manage ZKTeco Devices View -->
<div class="content-wrapper" style="min-height: 946px;">
    <!-- Content Header -->
    <section class="content-header">
        <h1>
            <i class="fa fa-cog"></i> Manage ZKTeco Devices
            <small>Configure Attendance Devices</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <!-- Add/Edit Device -->
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-plus"></i> Add/Edit Device</h3>
                    </div>
                    <div class="box-body">
                        <form id="form-device">
                            <?php echo $this->customlib->getCSRF(); ?>
                            <input type="hidden" name="id" id="device_id" value="">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Device Name <span class="text-danger">*</span></label>
                                        <input type="text" name="device_name" id="device_name" class="form-control" placeholder="e.g., Main Entrance" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Device IP <span class="text-danger">*</span></label>
                                        <input type="text" name="device_ip" id="device_ip" class="form-control" placeholder="192.168.1.100" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Port</label>
                                        <input type="number" name="device_port" id="device_port" class="form-control" value="4370" required>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Protocol</label>
                                        <select name="protocol" id="protocol" class="form-control">
                                            <option value="TCP" selected>TCP</option>
                                            <option value="UDP">UDP</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="form-group">
                                        <label>Active</label>
                                        <div class="checkbox">
                                            <label>
                                                <input type="checkbox" name="is_active" id="is_active" value="1" checked>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" id="btn-test" class="btn btn-info btn-block">
                                            <i class="fa fa-plug"></i> Test
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa fa-save"></i> Save Device
                                    </button>
                                    <button type="button" id="btn-reset" class="btn btn-default">
                                        <i class="fa fa-refresh"></i> Reset Form
                                    </button>
                                </div>
                            </div>
                        </form>
                        <div id="test-result" class="alert" style="display: none; margin-top: 15px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Devices List -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-list"></i> Configured Devices</h3>
                        <div class="box-tools pull-right">
                            <a href="<?php echo site_url('admin/attendance'); ?>" class="btn btn-sm btn-default">
                                <i class="fa fa-arrow-left"></i> Back to Attendance
                            </a>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Device Name</th>
                                        <th>IP Address</th>
                                        <th>Port</th>
                                        <th>Protocol</th>
                                        <th>Status</th>
                                        <th>Last Sync</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($devices)): ?>
                                        <?php $counter = 1; ?>
                                        <?php foreach ($devices as $device): ?>
                                            <tr>
                                                <td><?php echo $counter++; ?></td>
                                                <td><?php echo $device['device_name']; ?></td>
                                                <td><?php echo $device['device_ip']; ?></td>
                                                <td><?php echo $device['device_port']; ?></td>
                                                <td><?php echo $device['protocol']; ?></td>
                                                <td>
                                                    <?php if ($device['is_active']): ?>
                                                        <span class="label label-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="label label-danger">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo $device['last_sync'] ? date('d M Y h:i A', strtotime($device['last_sync'])) : 'Never'; ?>
                                                </td>
                                                <td>
                                                    <button class="btn btn-primary btn-sm btn-edit" 
                                                            data-id="<?php echo $device['id']; ?>"
                                                            data-name="<?php echo $device['device_name']; ?>"
                                                            data-ip="<?php echo $device['device_ip']; ?>"
                                                            data-port="<?php echo $device['device_port']; ?>"
                                                            data-protocol="<?php echo $device['protocol']; ?>"
                                                            data-active="<?php echo $device['is_active']; ?>">
                                                        <i class="fa fa-edit"></i> Edit
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center">No devices configured</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- JavaScript -->
<script type="text/javascript">
// Suppress Chrome extension errors (harmless but annoying)
window.addEventListener('error', function(e) {
    if (e.message && e.message.includes('runtime.lastError')) {
        e.preventDefault();
        return false;
    }
}, true);

// Suppress unhandled promise rejections from extensions
window.addEventListener('unhandledrejection', function(e) {
    if (e.reason && e.reason.message && e.reason.message.includes('runtime.lastError')) {
        e.preventDefault();
        return false;
    }
});

$(document).ready(function() {
    function getCsrfData() {
        var $csrfInput = $('#form-device').find('input[type="hidden"][name]').first();
        if ($csrfInput.length === 0) {
            return null;
        }
        return {
            name: $csrfInput.attr('name'),
            value: $csrfInput.val()
        };
    }

    function refreshCsrf(response) {
        if (response && response.csrf_token_name && response.csrf_hash) {
            $('input[name="' + response.csrf_token_name + '"]').val(response.csrf_hash);
        }
    }

    // Save device
    $('#form-device').submit(function(e) {
        e.preventDefault();

        var formData = {
            id: $('#device_id').val(),
            device_name: $('#device_name').val(),
            device_ip: $('#device_ip').val(),
            device_port: $('#device_port').val(),
            protocol: $('#protocol').val(),
            is_active: $('#is_active').is(':checked') ? 1 : 0
        };
        var csrfData = getCsrfData();
        if (csrfData) {
            formData[csrfData.name] = csrfData.value;
        }

        $.ajax({
            url: '<?php echo site_url("admin/attendance/save_device"); ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                refreshCsrf(response);
                if (response.status === 'success') {
                    alert('Device saved successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                var message = error || status || 'Unknown error';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                    refreshCsrf(xhr.responseJSON);
                } else if (xhr.responseText) {
                    message = xhr.responseText.substring(0, 400);
                }
                alert('Failed to save device: ' + message);
            }
        });
    });

    // Test connection
    $('#btn-test').click(function() {
        var deviceIp = $('#device_ip').val();
        var devicePort = $('#device_port').val();
        var protocol = $('#protocol').val();

        if (!deviceIp) {
            alert('Please enter device IP address');
            return;
        }

        $('#test-result').hide();
        $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Testing...');

        $.ajax({
            url: '<?php echo site_url("admin/attendance/test_connection"); ?>',
            type: 'POST',
            data: (function() {
                var payload = {
                device_ip: deviceIp,
                device_port: devicePort,
                protocol: protocol
                };
                var csrfData = getCsrfData();
                if (csrfData) {
                    payload[csrfData.name] = csrfData.value;
                }
                return payload;
            })(),
            dataType: 'json',
            success: function(response) {
                refreshCsrf(response);
                $('#btn-test').prop('disabled', false).html('<i class="fa fa-plug"></i> Test');
                
                if (response.status === 'success') {
                    $('#test-result')
                        .removeClass('alert-danger')
                        .addClass('alert-success')
                        .html('<strong>Success!</strong> ' + response.message + 
                              '<br>Version: ' + response.version + 
                              '<br>Device Time: ' + response.device_time)
                        .slideDown();
                } else {
                    $('#test-result')
                        .removeClass('alert-success')
                        .addClass('alert-danger')
                        .html('<strong>Error!</strong> ' + response.message)
                        .slideDown();
                }
            },
            error: function(xhr, status, error) {
                $('#btn-test').prop('disabled', false).html('<i class="fa fa-plug"></i> Test');
                var message = error || status || 'Unknown error';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                    refreshCsrf(xhr.responseJSON);
                } else if (xhr.responseText) {
                    message = xhr.responseText.substring(0, 400);
                }
                $('#test-result')
                    .removeClass('alert-success')
                    .addClass('alert-danger')
                    .html('<strong>Error!</strong> Failed to test connection: ' + message)
                    .slideDown();
            }
        });
    });

    // Edit device
    $('.btn-edit').click(function() {
        var data = $(this).data();
        
        $('#device_id').val(data.id);
        $('#device_name').val(data.name);
        $('#device_ip').val(data.ip);
        $('#device_port').val(data.port);
        $('#protocol').val(data.protocol);
        $('#is_active').prop('checked', data.active == 1);

        $('html, body').animate({
            scrollTop: $('#form-device').offset().top - 100
        }, 500);
    });

    // Reset form
    $('#btn-reset').click(function() {
        $('#form-device')[0].reset();
        $('#device_id').val('');
        $('#test-result').hide();
    });
});
</script>

