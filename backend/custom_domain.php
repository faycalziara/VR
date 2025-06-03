<?php
session_start();
$id_user = $_SESSION['id_user'];
if($settings['enable_custom_domain']) {
    if($user_info['role']!='editor') {
        $can_create = get_plan_permission($id_user)['enable_custom_domain'];
        $custom_domain = true;
    } else {
        $custom_domain = false;
    }
} else {
    $custom_domain = false;
}
switch($user_info['role']) {
    case 'administrator':
        $count_custom_domains = -1;
        break;
    case 'customer':
        $count_custom_domains = check_plan_custom_domain_count($id_user);
        break;
}
?>

<?php include("check_plan.php"); ?>

<?php if(!$custom_domain): ?>
    <div class="text-center">
        <div class="error mx-auto" data-text="401">401</div>
        <p class="lead text-gray-800 mb-5"><?php echo _("Permission denied"); ?></p>
        <p class="text-gray-500 mb-0"><?php echo _("It looks like that you do not have permission to access this page"); ?></p>
        <a href="index.php?p=dashboard">← <?php echo _("Back to Dashboard"); ?></a>
    </div>
<?php die(); endif; ?>

<?php if(!$can_create) : ?>
    <div class="card bg-warning text-white shadow mb-4">
        <div class="card-body">
            <?php echo sprintf(_('Your "%s" plan not allow to use Custom Domains!'),$user_info['plan'])." ".$msg_change_plan; ?>
        </div>
    </div>
<?php exit; endif; ?>

<?php if(($user_info['plan_status']=='active') || ($user_info['plan_status']=='expiring')) { ?>
    <?php if($count_custom_domains>=0) : ?>
        <div class="card bg-warning text-white shadow mb-3">
            <div class="card-body">
                <?php echo sprintf(_('You have %s remaining custom domains!'),$count_custom_domains); ?>
            </div>
        </div>
    <?php endif; ?>
<?php } else { $count_custom_domains=0; } ?>

<?php if($count_custom_domains==-1 || $count_custom_domains>0) : ?>
<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card shadow mb-12">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-globe"></i> <?php echo _("New Request"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-12">
                        <?php echo _("Before adding a new domain, make sure your A record is pointing to our server using the following details:"); ?>
                    </div>
                </div>
                <hr>
                <div class="row mb-3">
                    <div class="col-4">
                        <label><?php echo _("Type"); ?></label><br>
                        <b>A</b>
                    </div>
                    <div class="col-4">
                        <label><?php echo _("Name"); ?></label><br>
                        <b><?php echo _("empty"); ?></b> <?php echo _("or"); ?> <b>@</b> <?php echo _("or"); ?> <b><?php echo _("subdomain"); ?></b>
                    </div>
                    <div class="col-4">
                        <label><?php echo _("Value"); ?></label><br>
                        <b id="custom_domain_ip_server" class="permitselect"><?php echo (!empty($settings['custom_domain_ip_address']) ? $settings['custom_domain_ip_address'] : get_ip_server()) ?></b>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <div id="accordion">
                            <div class="card">
                                <div style="cursor: pointer" class="card-header collapsed" id="headingOne" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                                    <span><i class="fas fa-question-circle"></i>&nbsp;&nbsp;<?php echo _("Show instructions"); ?></span>
                                </div>
                                <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                                    <div class="card-body">
                                        <?php echo _("Integrating a custom domain with DNS settings typically involves the following steps:<br>1. <b>Purchase a domain name</b>: You'll need to purchase a domain name from a domain registrar such as GoDaddy, Namecheap, or other DNS providers.<br>2. <b>Obtain your DNS records</b>: Once you have a domain provider, they will provide you with DNS records that you'll need to configure for your domain. These records will typically include an A record & CNAME record.<br>3. <b>Configure DNS settings</b>: Log in to your domain registrar's account and navigate to the DNS management section. You need to add a new DNS record, choose the record type A and enter the corresponding value above.<br>4. <b>Wait for propagation</b>: Once you've made the changes to your DNS settings, it can take up to 48 hours for the changes to propagate throughout the internet.<br>5. <b>Approval</b>: Once your DNS record has been propagated, you can submit your custom domain request and wait for administrator approval.."); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="custom_domain"><?php echo _("Custom Domain"); ?></label>
                            <input id="custom_domain" type="text" class="form-control" value="">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="d-none d-md-block" style="opacity:0;pointer-events:none">.</label>
                            <button <?php echo ($demo) ? 'disabled' : ''; ?> id="btn_new_custom_domain" class="btn btn-block btn-success" onclick="new_custom_domain();"><i class="fa fa-circle-plus"></i> <?php echo _("VERIFY & ADD"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
if($user_info['role']=='administrator') {
    $query = "SELECT COUNT(*) as num FROM svt_custom_domains WHERE status IN(0,-2);";
    $result = $mysqli->query($query);
    if($result->num_rows == 1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $num = $row['num'];
        if($num > 0) { ?>
            <div class="card bg-warning text-white shadow mb-3">
                <div class="card-body">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo sprintf(_('You have %s pending request of custom domains to manage!'),$num); ?>
                </div>
            </div>
        <?php }
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-body">
                <table class="table table-bordered table-hover" id="custom_domain_table" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th><?php echo _("Date"); ?></th>
                        <th><?php echo _("User"); ?></th>
                        <th><?php echo _("Custom Domain"); ?></th>
                        <th><?php echo _("Status"); ?></th>
                        <th><?php echo _("Connected to"); ?></th>
                        <th><?php echo _("Actions"); ?></th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="modal_add_custom_domain" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("New Custom Domain"); ?></h5>
            </div>
            <div class="modal-body">
                <p><b class="custom_domain_value">--</b>&nbsp;&nbsp;<i class="fa-solid fa-arrow-right-long"></i>&nbsp;&nbsp;<span class="custom_domain_ip_value">--</span></p>
                <p><?php echo _("Are you sure you want to send the request for a new custom domain?"); ?></p>
            </div>
            <div class="modal-footer">
                <button id="btn_verify_custom_domain" onclick="verify_custom_domain('modal_add_custom_domain','');" type="button" class="btn btn-primary"><i class="fas fa-search"></i> <?php echo _("Verify"); ?></button>
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_add_custom_domain" onclick="add_custom_domain();" type="button" class="btn btn-success"><i class="fas fa-add"></i> <?php echo _("Add"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_status_custom_domain" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("New Custom Domain"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Your custom domain request has been submitted successfully!<br>Now you have to wait for it to be approved by an administrator."); ?></p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="reload_custom_domain();" type="button" class="btn btn-success"><i class="fas fa-check"></i> <?php echo _("Ok"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_verify_custom_domain" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Verify Custom Domain"); ?></h5>
            </div>
            <div class="modal-body">
                <p><b class="custom_domain_value">--</b>&nbsp;&nbsp;<i class="fa-solid fa-arrow-right-long"></i>&nbsp;&nbsp;<span class="custom_domain_ip_value">--</span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_defaults_custom_domain" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Custom Domain: Set Defaults"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Set this custom domain (<b class='custom_domain_default'>--</b>) as default when you create one of these items:"); ?></p>
                <div class="row">
                    <div style="white-space:nowrap" class="col-4 text-left">
                        <div class="form-check px-0">
                            <input class="form-check-input" type="checkbox" id="default_tour">
                            <label class="form-check-label" for="default_tour">
                                <i class='fas fa-route'></i> <?php echo _("Tour"); ?>
                            </label>
                        </div>
                    </div>
                    <div style="white-space:nowrap" class="col-4 text-center">
                        <div class="form-check px-0">
                            <input class="form-check-input" type="checkbox" id="default_showcase">
                            <label class="form-check-label" for="default_showcase">
                                <i class='fas fa-object-group'></i> <?php echo _("Showcase"); ?>
                            </label>
                        </div>
                    </div>
                    <div style="white-space:nowrap" class="col-4 text-right">
                        <div class="form-check px-0">
                            <input class="form-check-input" type="checkbox" id="default_globe">
                            <label class="form-check-label" for="default_globe">
                                <i class='fas fa-globe-americas'></i> <?php echo _("Globe"); ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_save_defaults" onclick="" type="button" class="btn btn-success"><i class="fas fa-save"></i> <?php echo _("Save"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_apply_bulk_custom_domain" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Custom Domain: Apply"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Apply or remove this custom domain (<b class='custom_domain_default'>--</b>) to these existing items:"); ?></p>
                <div class="row">
                    <div style="white-space:nowrap" class="col-4 text-left">
                        <div class="form-check px-0">
                            <input onchange="change_apply_checkoxs();" class="form-check-input" type="checkbox" id="apply_default_tour">
                            <label class="form-check-label" for="apply_default_tour">
                                <i class='fas fa-route'></i> <?php echo _("Tour"); ?>
                            </label>
                        </div>
                    </div>
                    <div style="white-space:nowrap" class="col-4 text-center">
                        <div class="form-check px-0">
                            <input onchange="change_apply_checkoxs();" class="form-check-input" type="checkbox" id="apply_default_showcase">
                            <label class="form-check-label" for="apply_default_showcase">
                                <i class='fas fa-object-group'></i> <?php echo _("Showcase"); ?>
                            </label>
                        </div>
                    </div>
                    <div style="white-space:nowrap" class="col-4 text-right">
                        <div class="form-check px-0">
                            <input onchange="change_apply_checkoxs();" class="form-check-input" type="checkbox" id="apply_default_globe">
                            <label class="form-check-label" for="apply_default_globe">
                                <i class='fas fa-globe-americas'></i> <?php echo _("Globe"); ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_apply_bulk" onclick="" type="button" class="btn btn-success disabled"><i class="fas fa-check"></i> <?php echo _("Apply"); ?></button>
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_remove_bulk" onclick="" type="button" class="btn btn-danger disabled"><i class="fas fa-xmark"></i> <?php echo _("Remove"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.id_user = '<?php echo $id_user; ?>';
        $(document).ready(function () {
            $('#custom_domain_table').DataTable({
                "order": [[ 0, "desc" ]],
                "responsive": true,
                "scrollX": true,
                "processing": true,
                "searching": false,
                "serverSide": true,
                "ajax": "ajax/get_custom_domains.php",
                "drawCallback": function( settings ) {
                    $('#custom_domain_table').DataTable().columns.adjust();
                    $('.tooltip_btn').tooltipster({
                        delay: 10,
                        hideOnClick: true
                    });
                },
                "language": {
                    "decimal":        "",
                    "emptyTable":     "<?php echo _("No data available in table"); ?>",
                    "info":           "<?php echo sprintf(_("Showing %s to %s of %s entries"),'_START_','_END_','_TOTAL_'); ?>",
                    "infoEmpty":      "<?php echo _("Showing 0 to 0 of 0 entries"); ?>",
                    "infoFiltered":   "<?php echo sprintf(_("(filtered from %s total entries)"),'_MAX_'); ?>",
                    "infoPostFix":    "",
                    "thousands":      ",",
                    "lengthMenu":     "<?php echo sprintf(_("Show %s entries"),'_MENU_'); ?>",
                    "loadingRecords": "<?php echo _("Loading"); ?>...",
                    "processing":     "<?php echo _("Processing"); ?>...",
                    "search":         "<?php echo _("Search"); ?>:",
                    "zeroRecords":    "<?php echo _("No matching records found"); ?>",
                    "paginate": {
                        "first":      "<?php echo _("First"); ?>",
                        "last":       "<?php echo _("Last"); ?>",
                        "next":       "<?php echo _("Next"); ?>",
                        "previous":   "<?php echo _("Previous"); ?>"
                    },
                    "aria": {
                        "sortAscending":  ": <?php echo _("activate to sort column ascending"); ?>",
                        "sortDescending": ": <?php echo _("activate to sort column descending"); ?>"
                    }
                }
            });
            window.new_custom_domain = function() {
                var custom_domain = $('#custom_domain').val();
                if(!validateDomain(custom_domain)) {
                    $('#custom_domain').addClass("error-highlight");
                } else {
                    $('#custom_domain').removeClass("error-highlight");
                    $('#modal_add_custom_domain .custom_domain_value').html(custom_domain);
                    $('#modal_add_custom_domain .custom_domain_ip_value').html("--");
                    $('#modal_add_custom_domain').modal('show');
                    verify_custom_domain('modal_add_custom_domain',custom_domain);
                }
            }
            window.add_custom_domain = function() {
                var custom_domain = $('#custom_domain').val();
                $('#modal_add_custom_domain button').addClass("disabled");
                $.ajax({
                    url: "ajax/add_custom_domain.php",
                    type: "POST",
                    data: {
                        custom_domain: custom_domain,
                    },
                    async: true,
                    success: function (json) {
                        var rsp = JSON.parse(json);
                        switch(rsp.status) {
                            case 'ok':
                                $.ajax({
                                    url: "ajax/send_email.php",
                                    type: "POST",
                                    data: {
                                        type: 'custom_domain_request',
                                        id_user: window.id_user,
                                        id_custom_domain: rsp.id
                                    },
                                    timeout: 15000,
                                    async: true,
                                    success: function () {}
                                });
                                $('#modal_status_custom_domain').modal('show');
                                break;
                            case 'exist':
                                alert(window.backend_labels.custom_domain_exist);
                                $('#modal_add_custom_domain button').removeClass("disabled");
                                $('#modal_add_custom_domain').modal('hide');
                                $('#custom_domain').addClass("error-highlight");
                                break;
                            default:
                                $('#modal_add_custom_domain button').removeClass("disabled");
                                alert(window.backend_labels.generic_error_msg);
                                break;
                        }
                    },
                    error: function() {
                        $('#modal_add_custom_domain button').removeClass("disabled");
                    }
                });
            }
            window.reload_custom_domain = function() {
                $('#modal_status_custom_domain').modal('hide');
                location.reload();
            }
            window.open_verify_custom_domain = function(custom_domain) {
                $('#modal_verify_custom_domain .custom_domain_value').html(custom_domain);
                $('#modal_verify_custom_domain .custom_domain_ip_value').html("--");
                $('#modal_verify_custom_domain').modal('show');
                verify_custom_domain('modal_verify_custom_domain',custom_domain);
            }
            window.open_set_defaults_custom_domain = function(id_custom_domain,custom_domain,default_tour,default_showcase,default_globe) {
                $('#modal_defaults_custom_domain .custom_domain_default').html(custom_domain);
                $('#default_tour').prop('checked',(parseInt(default_tour)==1) ? true : false);
                $('#default_showcase').prop('checked',(parseInt(default_showcase)==1) ? true : false);
                $('#default_globe').prop('checked',(parseInt(default_globe)==1) ? true : false);
                $('#btn_save_defaults').attr('onclick','save_custom_domain_defaults('+id_custom_domain+')')
                $('#modal_defaults_custom_domain').modal('show');
            }
            window.open_apply_bulk_custom_domain = function(id_custom_domain,custom_domain) {
                $('#modal_apply_bulk_custom_domain .custom_domain_default').html(custom_domain);
                $('#apply_default_tour').prop('checked',false);
                $('#apply_default_showcase').prop('checked',false);
                $('#apply_default_globe').prop('checked',false);
                $('#btn_apply_bulk').addClass('disabled');
                $('#btn_remove_bulk').addClass('disabled');
                $('#btn_apply_bulk').attr('onclick','apply_bulk_custom_domain('+id_custom_domain+',\'apply\')');
                $('#btn_remove_bulk').attr('onclick','apply_bulk_custom_domain('+id_custom_domain+',\'remove\')');
                $('#modal_apply_bulk_custom_domain').modal('show');
            }
            window.change_apply_checkoxs = function() {
                if($('#apply_default_tour').is(':checked') || $('#apply_default_showcase').is(':checked') || $('#apply_default_globe').is(':checked')) {
                    $('#btn_apply_bulk').removeClass('disabled');
                    $('#btn_remove_bulk').removeClass('disabled');
                } else {
                    $('#btn_apply_bulk').addClass('disabled');
                    $('#btn_remove_bulk').addClass('disabled');
                }
            }
            window.apply_bulk_custom_domain = function(id,mode) {
                $('#modal_apply_bulk_custom_domain button').addClass('disabled');
                $.ajax({
                    url: "ajax/apply_bulk_custom_domain.php",
                    type: "POST",
                    data: {
                        id: id,
                        mode: mode,
                        default_tour: $('#apply_default_tour').is(':checked') ? 1 : 0,
                        default_showcase: $('#apply_default_showcase').is(':checked') ? 1 : 0,
                        default_globe: $('#apply_default_globe').is(':checked') ? 1 : 0,
                    },
                    async: true,
                    success: function (json) {
                        var rsp = JSON.parse(json);
                        if (rsp.status == "ok") {
                            location.reload();
                        } else {
                            $('#modal_apply_bulk_custom_domain button').addClass('disabled');
                        }
                    },
                    error: function() {
                        $('#modal_apply_bulk_custom_domain button').removeClass('disabled');
                    }
                });
            }
            window.save_custom_domain_defaults = function(id) {
                $('#modal_defaults_custom_domain button').addClass('disabled');
                $.ajax({
                    url: "ajax/save_custom_domain_defaults.php",
                    type: "POST",
                    data: {
                        id: id,
                        default_tour: $('#default_tour').is(':checked') ? 1 : 0,
                        default_showcase: $('#default_showcase').is(':checked') ? 1 : 0,
                        default_globe: $('#default_globe').is(':checked') ? 1 : 0,
                    },
                    async: true,
                    success: function (json) {
                        var rsp = JSON.parse(json);
                        if (rsp.status == "ok") {
                            location.reload();
                        } else {
                            $('#modal_defaults_custom_domain button').addClass('disabled');
                        }
                    },
                    error: function() {
                        $('#modal_defaults_custom_domain button').removeClass('disabled');
                    }
                });
            }
            window.verify_custom_domain = function(modal,custom_domain) {
                $('#'+modal+' .custom_domain_ip_value').html('<i class="fas fa-circle-notch fa-spin"></i>');
                $('#btn_verify_custom_domain').addClass("disabled");
                if(custom_domain=='') custom_domain = $('#custom_domain').val();
                $.ajax({
                    url: "ajax/verify_custom_domain.php",
                    type: "POST",
                    data: {
                        custom_domain: custom_domain
                    },
                    async: true,
                    success: function (json) {
                        var rsp = JSON.parse(json);
                        var custom_domain_ip_server = $('#custom_domain_ip_server').html();
                        if (rsp.status == "ok") {
                            if(rsp.ip==custom_domain_ip_server) {
                                $('#'+modal+' .custom_domain_ip_value').html('<i style="color:green" class="fas fa-circle-check"></i>&nbsp;&nbsp;'+rsp.ip);
                            } else {
                                $('#'+modal+' .custom_domain_ip_value').html('<i style="color:red" class="fas fa-circle-xmark"></i>&nbsp;&nbsp;'+rsp.ip);
                            }
                        } else {
                            $('#'+modal+' .custom_domain_ip_value').html('<i style="color:red" class="fas fa-circle-xmark"></i>&nbsp;&nbsp;'+window.backend_labels.dns_record_not_exists);
                        }
                        if(modal=='modal_add_custom_domain') $('#btn_verify_custom_domain').removeClass("disabled");
                    },
                    error: function() {
                        $('#'+modal+' .custom_domain_ip_value').html('<i style="color:red" class="fas fa-circle-xmark"></i>&nbsp;&nbsp;'+window.backend_labels.generic_error_msg);
                        if(modal=='modal_add_custom_domain') $('#btn_verify_custom_domain').removeClass("disabled");
                    }
                });
            }
            window.save_custom_domain = function(id,mode) {
                switch(mode) {
                    case 'remove':
                        var retVal = confirm(window.backend_labels.custom_domain_delete_msg);
                        break;
                    case 'approve':
                        var retVal = confirm(window.backend_labels.custom_domain_approve_msg);
                        break;
                    case 'reject':
                        var retVal = confirm(window.backend_labels.custom_domain_reject_msg);
                        break;
                }
                if( retVal == true ) {
                    $.ajax({
                        url: "ajax/save_custom_domain.php",
                        type: "POST",
                        data: {
                            id: id,
                            mode: mode
                        },
                        async: true,
                        success: function (json) {
                            var rsp = JSON.parse(json);
                            if (rsp.status == "ok") {
                                if(mode=='remove') {
                                    $.ajax({
                                        url: "ajax/send_email.php",
                                        type: "POST",
                                        data: {
                                            type: 'custom_domain_remove',
                                            id_user: window.id_user,
                                            id_custom_domain: id
                                        },
                                        timeout: 15000,
                                        async: true,
                                        success: function () {}
                                    });
                                }
                                location.reload();
                            }
                        },
                        error: function() {
                        }
                    });
                }
            }
            window.delete_custom_domain = function(id) {
                var retVal = confirm(window.backend_labels.custom_domain_delete_msg);
                if( retVal == true ) {
                    $.ajax({
                        url: "ajax/delete_custom_domain.php",
                        type: "POST",
                        data: {
                            id: id,
                        },
                        async: true,
                        success: function (json) {
                            var rsp = JSON.parse(json);
                            if (rsp.status == "ok") {
                                location.reload();
                            }
                        },
                        error: function() {
                        }
                    });
                }
            }
            function validateDomain(domain) {
                const domainRegex = /^(?!:\/\/)([a-zA-Z0-9-_]+\.)*[a-zA-Z0-9-]{2,63}\.[a-zA-Z]{2,63}$/;
                return domainRegex.test(domain);
            }
        });
    })(jQuery); // End of use strict
</script>