<?php
session_start();
$language = $_SESSION['language'];
$id_user = $_SESSION['id_user'];
$user_info = get_user_info($id_user);
$settings = get_settings();
$services = get_services($language);
$app_name = $settings['name'];
$stripe_enabled = $settings['stripe_enabled'];
$stripe_secret_key = $settings['stripe_secret_key'];
$stripe_public_key = $settings['stripe_public_key'];
$paypal_enabled = $settings['paypal_enabled'];
$paypal_client_id = $settings['paypal_client_id'];
$paypal_client_secret = $settings['paypal_client_secret'];
if((empty($stripe_public_key)) || (empty($stripe_secret_key))) {
    $stripe_enabled = 0;
}
if((empty($paypal_client_id)) || (empty($paypal_client_secret))) {
    $paypal_enabled = 0;
}
if($stripe_enabled) {
    $paypal_enabled=0;
    $twocheckout_enabled=0;
} else if($paypal_enabled) {
    $stripe_enabled=0;
    $twocheckout_enabled=0;
}
if(isset($_GET['id_vt'])) {
    $id_vt_sel = $_GET['id_vt'];
} else {
    $id_vt_sel = 0;
}
$uid = 0;
if(isset($_GET['response'])) {
    $response = $_GET['response'];
    if(isset($_GET['uid'])) {
        $uid = $_GET['uid'];
    }
} else {
    $response = null;
}
if(isset($_SESSION['id_service_sel'])) {
    $id_service_sel = $_SESSION['id_service_sel'];
    unset($_SESSION['id_service_sel']);
    $id_vt_sel = $_SESSION['id_virtualtour_sel'];
} else {
    $id_service_sel = 0;
}
$virtual_tours = get_virtual_tours($id_user,'rooms');
$credits_balance = $user_info['services_credits']-get_user_service_used($id_user);
if(empty($credits_balance)) { $credits_balance = 0; }
$z0='';if(array_key_exists('SERVER_ADDR',$_SERVER)){$z0=$_SERVER['SERVER_ADDR'];if(!filter_var($z0,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){$z0=gethostbyname($_SERVER['SERVER_NAME']);}}elseif(array_key_exists('LOCAL_ADDR',$_SERVER)){$z0=$_SERVER['LOCAL_ADDR'];}elseif(array_key_exists('SERVER_NAME',$_SERVER)){$z0=gethostbyname($_SERVER['SERVER_NAME']);}else{if(stristr(PHP_OS,'WIN')){$z0=gethostbyname(php_uname('n'));}else{$b1=shell_exec('/sbin/ifconfig eth0');preg_match('/addr:([\d\.]+)/',$b1,$e2);$z0=$e2[1];}}echo"<input type='hidden' id='vlfc' />";$v3=get_settings();$o5=$z0.'RR'.$v3['purchase_code'];$v6=password_verify($o5,$v3['license']);if(!$v6&&!empty($v3['license2'])){$o5=str_replace("www.","",$_SERVER['SERVER_NAME']).'RR'.$v3['purchase_code'];$v6=password_verify($o5,$v3['license2']);}$o5=$z0.'RE'.$v3['purchase_code'];$w7=password_verify($o5,$v3['license']);if(!$w7&&!empty($v3['license2'])){$o5=str_replace("www.","",$_SERVER['SERVER_NAME']).'RE'.$v3['purchase_code'];$w7=password_verify($o5,$v3['license2']);}$o5=$z0.'E'.$v3['purchase_code'];$r8=password_verify($o5,$v3['license']);if(!$r8&&!empty($v3['license2'])){$o5=str_replace("www.","",$_SERVER['SERVER_NAME']).'E'.$v3['purchase_code'];$r8=password_verify($o5,$v3['license2']);}if($v6){include('license.php');exit;}else if(($r8)||($w7)){}else{include('license.php');exit;}
?>

<?php if($stripe_enabled) : ?>
    <script src="https://js.stripe.com/v3/"></script>
<?php endif; ?>

<?php if($paypal_enabled) : ?>
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo $paypal_client_id; ?>&currency=<?php echo $services[0]['currency']; ?>" data-sdk-integration-source="button-factory"></script>
<?php endif; ?>

<?php if($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip'] && $_SESSION['demo_user_id']!=$id_user) : ?>
    <div class="card bg-warning text-white shadow mb-3">
        <div class="card-body">
            <?php echo _("It is not possible to purchase services on this demo server. This section is shown for demonstration purposes only."); ?>
        </div>
    </div>
<?php endif; ?>

<?php if($response=='success') : ?>
    <?php
    $purchased_service = get_purchased_service($id_user,$language,$uid);
    if($purchased_service) { ?>
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading"><?php echo _("Thank you for your purchase!"); ?></h4>
            <?php if(empty($purchased_service['id_virtualtour'])) : ?>
                <p class="mb-0"><?php echo sprintf(_("Your payment for the <b>%s</b> service has been successfully processed."),$purchased_service['name']); ?></p>
            <?php else: ?>
                <p class="mb-0"><?php echo sprintf(_("Your payment for the <b>%s</b> service on the <b>%s</b> tour has been successfully processed."),$purchased_service['name'],$purchased_service['name_vt']); ?></p>
            <?php endif; ?>
            <p class="mb-0"><?php echo _("If we need more information you will be contacted as soon as possible."); ?></p>
        </div>
        <?php if(!empty($purchased_service['id_virtualtour']) && $purchased_service['block_tour']) : ?>
        <div class="alert alert-secondary" role="alert">
            <p class="mb-0"><?php echo _("Your tour is now on a locked state until our staff has finished working on it."); ?></p>
        </div>
        <?php endif; ?>
    <?php } else { ?>
        <div class="alert alert-warning" role="alert">
            <h4 class="alert-heading"><?php echo _("Oops! An error occurred."); ?></h4>
            <p class="mb-0"><?php echo _("Something went wrong while processing your order.<br>Please contact our customer service: we will be happy to help you resolve it as soon as possible.<br>Thank you for your patience and understanding!"); ?></p>
        </div>
    <?php } ?>
    <div class="text-center">
        <a href="index.php?p=dashboard" class="btn btn-outline-primary"><i class="fas fa-arrow-left"></i>&nbsp;&nbsp;<?php echo _("Back to Dashboard"); ?></a>
        <a href="index.php?p=buy_service" class="btn btn-primary"><i class="fas fa-hand-holding-dollar"></i>&nbsp;&nbsp;<?php echo _("Services"); ?></a>
    </div>
<?php else : ?>
    <div id="pricing_msg" class="text-center mb-3">
        <h3 class="text-primary mb-2"><?php echo _("Choose a service"); ?></h3>
        <h4><?php echo _("Pick what's right for you"); ?></h4>
    </div>
    <div id="services_div" class="pricing-columns">
        <div class="row justify-content-center">
            <?php foreach ($services as $service) {
                if(($service['price']>0 || $service['credits']>0) && $service['visible']==1) { ?>
                    <div class="col-xl-4 col-lg-6 mb-4">
                        <div class="card h-100 noselect">
                            <div class="card-header bg-transparent">
                                <span id="service_name_<?php echo $service['id']; ?>" class="badge badge-primary-soft text-primary badge-pill py-2 px-3 mb-2"><?php echo $service['name']; ?></span>
                                <div class="pricing-columns-price">
                                    <b>
                                        <?php
                                        if($service['type']=='tour_service') {
                                            $price_add_label = '&nbsp;&nbsp;<span style="font-size:14px;font-weight:normal;" class="text-gray-600">'._("for room").'</span>';
                                        } else {
                                            $price_add_label = "";
                                        }
                                        $price = format_currency($service['currency'],$service['price']);
                                        $credits = $service['credits'];
                                        if($service['price']>0 && $service['credits']==0) {
                                            echo $price.$price_add_label;
                                        } else if($service['price']==0 && $service['credits']>0) {
                                            echo $credits." <span style='font-size:16px;'>"._("credits")."</span>".$price_add_label;
                                        } else {
                                            echo $price. " / ".$credits." <span style='font-size:16px;'>".(($credits==1) ? _("credit") : _("credits"))."</span>".$price_add_label;
                                        }
                                        ?>
                                    </b>
                                </div>
                            </div>
                            <div style="flex:0 0 auto;min-height: 120px" class="card-body px-3 py-3">
                                <?php echo $service['description']; ?>
                            </div>
                            <?php if(count($virtual_tours)>0) : ?>
                            <?php if($service['type']=='tour_service' || $service['type']=='tour_generic') : ?>
                                <?php if(count($virtual_tours)>0) : ?>
                                    <div class="mb-3 px-3">
                                        <select data-show-subtext="true" data-live-search="true" title="<?php echo _("Select a tour"); ?>" id="vt_sel_service_<?php echo $service['id']; ?>" class="form-control vt_sel_service">
                                            <?php
                                            foreach ($virtual_tours as $tour) {
                                                if($tour['external']==0) {
                                                    echo "<option ".(($tour['id']==$id_vt_sel) ? 'selected' : '')." style='".(($tour['count_check']==0 && $service['type']=='tour_service') ? 'color:darkorange' : '')."' value='".$tour['id']."' data-type='".$service['type']."' data-count='".$tour['count_check']."' data-name='".$tour['name']."' data-subtext='".$tour['count_check']." ".(($tour['count_check']==1) ? _("room") : _("rooms"))."'>".$tour['name']."</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <?php if($service['type']=='tour_service') : ?>
                                    <div class="input-group px-3 mb-2 disabled">
                                        <input id="num_sel_rooms_<?php echo $service['id']; ?>" type="number" min="1" max="1" class="form-control" />
                                        <div class="input-group-append">
                                            <span class="input-group-text" id="basic-addon2"><?php echo _("No. of Rooms"); ?></span>
                                        </div>
                                    </div>
                                    <script>
                                        $('#vt_sel_service_<?php echo $service['id']; ?>').selectpicker('refresh');
                                        $('#vt_sel_service_<?php echo $service['id']; ?>').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
                                            var id = $(this).attr('id');
                                            change_vt_service(<?php echo $service['id']; ?>,parseInt($("#"+id+' option:selected').attr('data-count')),<?php echo $service['price']; ?>,'<?php echo $service['currency']; ?>',<?php echo $service['credits']; ?>,'<?php echo $service['type']; ?>');
                                        });
                                        window.checkMinMax_input = function(event) {
                                            this.value = valBetween(this.value, this.min, this.max);
                                            var event = new Event('change');
                                            this.dispatchEvent(event);
                                        }
                                        function valBetween(v, min, max) {
                                            return (Math.min(max, Math.max(min, v)));
                                        }
                                        document.getElementById('num_sel_rooms_<?php echo $service['id']; ?>').addEventListener('keyup', checkMinMax_input);
                                        $('#num_sel_rooms_<?php echo $service['id']; ?>').on('change', function (e, clickedIndex, isSelected, previousValue) {
                                            var count = $(this).val();
                                            calculate_service_price(<?php echo $service['id']; ?>,parseInt(count),<?php echo $service['price']; ?>,'<?php echo $service['currency']; ?>',<?php echo $service['credits']; ?>,'<?php echo $service['type']; ?>');
                                        });
                                    </script>
                                <?php else: ?>
                                    <script>
                                        $('#vt_sel_service_<?php echo $service['id']; ?>').selectpicker('refresh');
                                        $('#vt_sel_service_<?php echo $service['id']; ?>').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
                                            var id = $(this).attr('id');
                                            change_vt_service(<?php echo $service['id']; ?>,parseInt($("#"+id+' option:selected').attr('data-count')),<?php echo $service['price']; ?>,'<?php echo $service['currency']; ?>',<?php echo $service['credits']; ?>,'<?php echo $service['type']; ?>');
                                        });
                                    </script>
                                    <div style="opacity:0;pointer-events:none;display:none;" class="input-group px-3 mb-2 disabled">
                                        <input style="opacity:0;pointer-events:none;" id="num_sel_rooms_<?php echo $service['id']; ?>" type="number" min="1" max="1" class="form-control" value="1" />
                                    </div>
                                <?php endif; ?>
                                <?php endif; ?>
                            <?php else : ?>
                                <select style="opacity:0;pointer-events:none;display:none;" class="form-control mb-2"></select>
                                <div style="opacity:0;pointer-events:none;display:none;" class="input-group px-3 mb-2 disabled">
                                    <input style="opacity:0;pointer-events:none;" type="number" class="form-control" />
                                </div>
                            <?php endif; ?>
                            <?php endif; ?>
                            <div>
                                <?php if(count($virtual_tours)==0) : ?>
                                    <div class="px-3 denied_subscribe_msg"><?php echo _("No tours created. To purchase this service, please create a new one first."); ?></div>
                                <?php else : ?>
                                <a onclick="set_session_upload_service(<?php echo $service['id']; ?>);return false;" id="upload_images_div_<?php echo $service['id']; ?>" class="card-footer align-items-center justify-content-between text-decoration-none bg-warning text-white d-none" href="#">
                                    <div><?php echo _("Upload images"); ?></div>
                                    <i class="fas fa-upload"></i>
                                </a>
                                <div style="display:none;" id="upload_images_msg_<?php echo $service['id']; ?>" class="denied_subscribe_msg px-3"><?php echo _("To purchase this service you must first upload your 360 degree images."); ?></div>
                                <?php
                                if(!$stripe_enabled && !$paypal_enabled) { ?>
                                    <a id="purchase_div_<?php echo $service['id']; ?>" class="card-footer align-items-center justify-content-between text-decoration-none bg-primary text-white <?php echo ($id_vt_sel==0 && ($service['type']=='tour_service' || $service['type']=='tour_generic')) ? 'disabled' : ''; ?> <?php echo ($service['price']==0 || count($virtual_tours)==0) ? 'd-none hidden' : 'd-flex'; ?>" href="mailto:<?php echo $settings['contact_email']; ?>?subject=<?php echo $service['name']; ?>">
                                        <div><?php echo _("Contact Us"); ?></div>
                                        <i class="fas fa-envelope"></i>
                                    </a>
                                <?php } else { ?>
                                    <div class="p-3 <?php echo ($service['type']!='generic') ? 'disabled' : ''; ?>">
                                        <textarea maxlength="300" class="form-control" placeholder="<?php echo _("Add notes"); ?>" id="note_<?php echo $service['id']; ?>" rows="2"></textarea>
                                    </div>
                                    <?php if($stripe_enabled) { ?>
                                        <a onclick="redirect_to_checkout_service(<?php echo $service['id']; ?>);return false;" id="purchase_div_<?php echo $service['id']; ?>" class="card-footer align-items-center justify-content-between text-decoration-none bg-primary text-white <?php echo ($id_vt_sel==0 && ($service['type']=='tour_service' || $service['type']=='tour_generic')) ? 'disabled' : ''; ?> <?php echo ($service['price']==0 || count($virtual_tours)==0) ? 'd-none hidden' : 'd-flex'; ?>" href="#">
                                            <div><?php echo _("Purchase"); ?>&nbsp;&nbsp;<b id="purchase_total_<?php echo $service['id']; ?>"></b></div>
                                            <i class="fas fa-shopping-bag"></i>
                                        </a>
                                    <?php } else if($paypal_enabled) { ?>
                                        <div class="<?php echo ($id_vt_sel==0 && ($service['type']=='tour_service' || $service['type']=='tour_generic')) ? 'disabled' : ''; ?> <?php echo ($service['price']==0 || count($virtual_tours)==0) ? 'd-none hidden' : 'd-flex'; ?>" id="purchase_div_<?php echo $service['id']; ?>"></div>
                                        <script>
                                            setTimeout(function() {
                                                paypal.Buttons({
                                                    style: {
                                                        layout: 'vertical',
                                                        color: 'blue',
                                                        shape: 'rect',
                                                        label: 'checkout',
                                                        tagline: false,
                                                        height: 49
                                                    },
                                                    createOrder: function(data, actions) {
                                                        var id_vt = $('#vt_sel_service_<?php echo $service['id']; ?> option:selected').val();
                                                        var count = $('#num_sel_rooms_<?php echo $service['id']; ?>').val();
                                                        return new Promise(function(resolve, reject) {
                                                            $.ajax({
                                                                url: "ajax/calculate_service_price_paypal.php",
                                                                type: "POST",
                                                                data: {
                                                                    id_service: <?php echo $service['id']; ?>,
                                                                    id_vt: id_vt,
                                                                    count: count
                                                                },
                                                                success: function (json) {
                                                                    var rsp = JSON.parse(json);
                                                                    var price = parseFloat(rsp.price);
                                                                    var currency = rsp.currency;
                                                                    var params = rsp.params;
                                                                    resolve(actions.order.create({
                                                                        purchase_units: [{
                                                                            "custom_id": params,
                                                                            "description": "<?php echo $app_name; ?> - <?php echo $service['name']; ?>",
                                                                            "amount": { "currency_code": currency, "value": price },
                                                                            'application_context': { 'shipping_preference': 'NO_SHIPPING' }
                                                                        }]
                                                                    }));
                                                                },
                                                                error: function(err) {
                                                                    console.error("Error fetching price:", err);
                                                                    reject(err);
                                                                }
                                                            });
                                                        });
                                                    },
                                                    onApprove: function(data, actions) {
                                                        return actions.order.capture().then(function(orderData) {
                                                            var note = $('#note_<?php echo $service['id']; ?>').val();
                                                            save_paypal_subscription_id(<?php echo $id_user; ?>,'service',orderData.id,note);
                                                        });
                                                    },
                                                    onError: function(err) {
                                                        console.log(err);
                                                    }
                                                }).render('#purchase_div_<?php echo $service['id']; ?>');
                                            },10);
                                        </script>
                                    <?php } ?>
                                <?php } ?>
                                <a onclick="open_modal_service_credits(<?php echo $service['id']; ?>,<?php echo $service['credits']; ?>);return false;" id="credits_div_<?php echo $service['id']; ?>" class="card-footer align-items-center justify-content-between text-decoration-none bg-primary text-white <?php echo ($paypal_enabled && $service['price']>0) ? 'mt-3' : ''; ?> <?php echo ($id_vt_sel==0 && ($service['type']=='tour_service' || $service['type']=='tour_generic')) ? 'disabled' : ''; ?> <?php echo ($service['credits']==0 || count($virtual_tours)==0) ? 'd-none hidden' : 'd-flex'; ?>" href="#">
                                    <div><?php echo _("Use Credits"); ?>&nbsp;&nbsp;<b id="credits_total_<?php echo $service['id']; ?>"></b></div>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } ?>
        </div>
    </div>

    <div id="modal_redirect_checkout" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <p><?php echo _("Redirecting to checkout page ..."); ?></p>
                </div>
            </div>
        </div>
    </div>

    <div id="modal_credits" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                </div>
                <div class="modal-body">
                    <?php echo _("Credits balance"); ?>: <b id="credits_balance"><?php echo $credits_balance; ?></b><br><br>
                    <div style="display:none;" id="msg_credits_generic"><?php echo _("Are you sure you want to use <b class='credits_needed'>-</b> credits to purchase this service?"); ?></div>
                    <div style="display:none;" id="msg_credits_tour"><?php echo _("Are you sure you want to use <b class='credits_needed'>-</b> credits to purchase this service for the <b class='tour_service'>-</b> tour?"); ?></div>
                </div>
                <div class="modal-footer">
                    <button id="btn_user_credits" onclick="" type="button" class="btn btn-success <?php echo ($demo) ? 'disabled_d' : ''; ?> disabled"><i class="fas fa-arrow-right"></i> <?php echo _("Use Credits"); ?></button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.id_user = <?php echo $id_user; ?>;
        window.id_vt_sel = <?php echo $id_vt_sel; ?>;
        var stripe_enabled = <?php echo $stripe_enabled; ?>;
        $(document).ready(function () {
            if(stripe_enabled) {
                window.stripe = Stripe('<?php echo $stripe_public_key; ?>');
            }
            if(window.id_vt_sel!=0) {
                $('.vt_sel_service').each(function() {
                    $(this).trigger('change');
                })
            }
        });

        window.change_vt_service = function(id_service,count,price,currency,credits,type) {
            $('#note_'+id_service).parent().removeClass('disabled');
            if(count==0 && type=='tour_service') {
                $('#upload_images_div_'+id_service).addClass('d-flex').removeClass('d-none');
                $('#upload_images_msg_'+id_service).show();
                $('#purchase_div_'+id_service).removeClass('d-flex').addClass('d-none');
                $('#credits_div_'+id_service).removeClass('d-flex').addClass('d-none');
                $('#note_'+id_service).parent().hide();
                $('#num_sel_rooms_'+id_service).parent().hide();
                $('#num_sel_rooms_'+id_service).parent().addClass('disabled');
            } else {
                $('#upload_images_div_'+id_service).removeClass('d-flex').addClass('d-none');
                $('#upload_images_msg_'+id_service).hide();
                $('#purchase_div_'+id_service).addClass('d-flex').removeClass('d-none');
                $('#credits_div_'+id_service).addClass('d-flex').removeClass('d-none');
                $('#note_'+id_service).parent().show();
                if(type=='tour_service') {
                    $('#num_sel_rooms_'+id_service).attr('max',count);
                    $('#num_sel_rooms_'+id_service).val(count);
                    $('#num_sel_rooms_'+id_service).parent().show();
                    $('#num_sel_rooms_'+id_service).parent().removeClass('disabled');
                    calculate_service_price(id_service,count,price,currency,credits,type);
                } else {
                    $('#num_sel_rooms_'+id_service).parent().hide();
                    $('#purchase_div_'+id_service).removeClass('disabled');
                    $('#credits_div_'+id_service).removeClass('disabled');
                }
            }
        }

        window.calculate_service_price = function(id_service,count,price,currency,credits,type) {
            if(type=='tour_service') {
                $('#purchase_div_'+id_service).addClass('disabled');
                $('#credits_div_'+id_service).addClass('disabled');
                $.ajax({
                    url: "ajax/calculate_service_price.php",
                    type: "POST",
                    data: {
                        count: count,
                        price: price,
                        currency: currency,
                        credits: credits,
                        type: type
                    },
                    async: true,
                    success: function (json) {
                        var rsp = JSON.parse(json);
                        var price = parseFloat(rsp.price);
                        var credits = parseInt(rsp.credits);
                        if(price>0) {
                            $('#purchase_div_'+id_service).removeClass('disabled');
                            $('#purchase_total_'+id_service).html(rsp.html_price);
                        } else {
                            $('#purchase_div_'+id_service).addClass('disabled');
                            $('#purchase_total_'+id_service).html('');
                        }
                        if(credits>0) {
                            $('#credits_div_'+id_service).removeClass('disabled');
                            $('#credits_total_'+id_service).html(rsp.html_credits);
                        } else {
                            $('#credits_div_'+id_service).addClass('disabled');
                            $('#credits_total_'+id_service).html('');
                        }
                        if(price!=0 || credits!=0) {
                            $('#purchase_div_'+id_service).removeClass('disabled');
                            $('#credits_div_'+id_service).removeClass('disabled');
                        }
                    },
                    error: function() {

                    }
                });
            }
        }

        window.set_session_upload_service = function(id_service) {
            var id_vt = $('#vt_sel_service_'+id_service+' option:selected').val();
            $('#upload_images_div_'+id_service).addClass('disabled');
            $.ajax({
                url: "ajax/set_session_service.php",
                type: "POST",
                data: {
                    id_virtualtour: id_vt,
                    id_service: id_service
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        location.href = 'index.php?p=rooms_bulk';
                    } else {
                        $('#upload_images_div_'+id_service).removeClass('disabled');
                    }
                },
                error: function() {
                    $('#upload_images_div_'+id_service).removeClass('disabled');
                }
            });
        }

        window.open_modal_service_credits = function(id_service,credits) {
            var id_vt = 0;
            if($('#vt_sel_service_'+id_service).length) {
                id_vt = $('#vt_sel_service_'+id_service+' option:selected').val();
            }
            var name_vt = $('#vt_sel_service_'+id_service+' option:selected').attr('data-name');
            var count = parseInt($('#num_sel_rooms_'+id_service).val());
            var type = $('#vt_sel_service_'+id_service+' option:selected').attr('data-type');
            var credits_balance = parseInt($('#credits_balance').html());
            if(type=='tour_service') {
                credits = credits * count;
                $('#msg_credits_generic').hide();
                $('#msg_credits_tour').show();
                $('.tour_service').html(name_vt);
            } else {
                $('#msg_credits_generic').show();
                $('#msg_credits_tour').hide();
            }
            $('#modal_credits .modal-title').html($('#service_name_'+id_service).html());
            $('.credits_needed').html(credits);
            credits = parseInt(credits);
            if(credits<=credits_balance) {
                $('#btn_user_credits').removeClass('disabled');
                $('#btn_user_credits').attr('onclick','use_credits_service('+id_service+','+id_vt+');');
            } else {
                $('#btn_user_credits').addClass('disabled');
                $('#btn_user_credits').attr('onclick','');
            }
            $('#modal_credits').modal('show');
        }

        window.use_credits_service = function(id_service,id_vt) {
            $('#modal_credits button').addClass('disabled');
            var note = $('#note_'+id_service).val();
            var count = parseInt($('#num_sel_rooms_'+id_service).val());
            $.ajax({
                url: "ajax/use_credits_service.php",
                type: "POST",
                data: {
                    id_virtualtour: id_vt,
                    id_service: id_service,
                    count: count,
                    note: note
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if (rsp.status == "ok") {
                        location.href = 'index.php?p=buy_service&response=success&uid='+rsp.uid;
                    } else {
                        $('#modal_credits button').removeClass('disabled');
                    }
                },
                error: function() {
                    $('#modal_credits button').removeClass('disabled');
                }
            });
        }
    })(jQuery); // End of use strict
</script>