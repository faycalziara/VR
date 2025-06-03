<?php
session_start();
$id_user = $_SESSION['id_user'];
$id_virtualtour_sel = $_SESSION['id_virtualtour_sel'];
$virtual_tour = get_virtual_tour($id_virtualtour_sel,$id_user);
$vt_versions = get_virtual_tour_versions($id_virtualtour_sel);
$s3_params = check_s3_tour_enabled($id_virtualtour_sel);
$s3_enabled = false;
$s3_url = "";
if(!empty($s3_params)) {
    $s3_url = init_s3_client($s3_params);
    if($s3_url!==false) {
        $s3_enabled = true;
    }
}
?>

<?php include("check_plan.php"); ?>
<?php include("check_block_tour.php"); ?>

<div class="row">
    <div class="col-md-12">
        <?php if($virtual_tour['external']==0) : ?>
        <div id="toolbar_preview" style="display: none;" class="card shadow mb-0 noselect">
            <div class="card-body p-1 pb-0 text-center">
                <div class="text-center toolbar_preview_loading px-1">
                    <i class="fas fa-spin fa-circle-notch"></i>&nbsp;&nbsp;<span><?php echo _("initializing"); ?>... </span>
                </div>
                <div class="toolbar_preview_buttons">
                    <img id="preview_room_image" src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==" /><span class="btn btn-sm btn-light no-click" id="preview_room_name"></span>&nbsp;&nbsp;&nbsp;
                    <a id="btn_preview_edit_room" href="#" target="_blank" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit"></i> <?php echo _("edit room"); ?>
                    </a>
                    <a id="btn_preview_markers" href="#" target="_blank" class="btn btn-sm btn-info">
                        <i class="fas fa-caret-square-up"></i> <?php echo _("markers"); ?> <span class="badge badge-light">0</span>
                    </a>
                    <a id="btn_preview_pois" href="#" target="_blank" class="btn btn-sm btn-info">
                        <i class="fas fa-bullseye"></i> <?php echo _("pois"); ?> <span class="badge badge-light">0</span>
                    </a>
                    <a id="btn_preview_measures" href="#" target="_blank" class="btn btn-sm btn-info">
                        <i class="fas fa-ruler-combined"></i> <?php echo _("measures"); ?> <span class="badge badge-light">0</span>
                    </a>&nbsp;&nbsp;
                    <?php if(count($vt_versions)>0) : ?>
                        <select id="version" style="width:200px;" class="form-control form-control-sm d-inline-block">
                            <option id="0"><?php echo _("Main"); ?></option>
                            <?php foreach ($vt_versions as $vt_version) { ?>
                                <option id="<?php echo $vt_version['id']; ?>"><?php echo $vt_version['version']; ?></option>
                            <?php } ?>
                        </select>
                    <?php endif; ?>
                    <button style="pointer-events:none" onclick="switch_preview_mode('desktop');" id="btn_preview_switch_desktop" class="btn btn-sm btn-dark">
                        <i class="fas fa-desktop"></i>
                    </button>
                    <button onclick="switch_preview_mode('mobile');" id="btn_preview_switch_mobile" class="btn btn-sm btn-outline-dark">
                        <i class="fas fa-mobile"></i>
                    </button>
                    <button id="btn_preview_reload" class="btn btn-sm btn-secondary" onclick="">
                        <i class="fas fa-redo"></i>
                    </button>
                    <script>
                        function switch_preview_mode(mode) {
                            switch(mode) {
                                case 'desktop':
                                    $('#btn_preview_switch_desktop').removeClass('btn-outline-dark').addClass('btn-dark');
                                    $('#btn_preview_switch_mobile').addClass('btn-outline-dark').removeClass('btn-dark');
                                    $('#btn_preview_switch_desktop').css('pointer-events', 'none');
                                    $('#btn_preview_switch_mobile').css('pointer-events', 'initial');
                                    $('#iframe_div').removeClass('mobile');
                                    break;
                                case 'mobile':
                                    $('#btn_preview_switch_desktop').addClass('btn-outline-dark').removeClass('btn-dark');
                                    $('#btn_preview_switch_mobile').removeClass('btn-outline-dark').addClass('btn-dark');
                                    $('#btn_preview_switch_desktop').css('pointer-events', 'initial');
                                    $('#btn_preview_switch_mobile').css('pointer-events', 'none');
                                    $('#iframe_div').addClass('mobile');
                                    break;
                            }
                            $('#btn_preview_reload').trigger('click');
                            $(window).trigger('resize');
                        }
                    </script>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <div class="card shadow mb-2">
            <div class="card-body p-0" style="overflow: hidden;">
                <p style="display: none;padding: 15px 15px 0;" id="msg_no_room"><?php echo sprintf(_('No rooms created for this Virtual Tour. Go to %s and create a new one!'),'<a href="index.php?p=rooms">'._("Rooms").'</a>'); ?></p>
                <div style="display: none;margin-bottom:-10px;" id="iframe_div"></div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.s3_enabled = <?php echo ($s3_enabled) ? 1 : 0; ?>;
        window.s3_url = '<?php echo $s3_url; ?>';
        window.id_user = '<?php echo $id_user; ?>';
        window.id_virtualtour = '<?php echo $id_virtualtour_sel; ?>';
        $(document).ready(function () {
            if($('#toolbar_preview').length) {
                var container_h = $('#content-wrapper').height() - 195;
            } else {
                var container_h = $('#content-wrapper').height() - 155;
            }
            if($('#tawk-container').length) {
                container_h = container_h - 20;
            }
            preview_vt(window.id_virtualtour,container_h,0);
        });
        $(window).resize(function () {
            if($('#toolbar_preview').length) {
                var container_h = $('#content-wrapper').height() - 195;
            } else {
                var container_h = $('#content-wrapper').height() - 155;
            }
            if($('#tawk-container').length) {
                container_h = container_h - 20;
            }
            $('#iframe_div iframe').attr('height',container_h+'px');
            if($('#iframe_div').hasClass('mobile')) {
                $('#iframe_div').css('width',(container_h*9/16)+'px');
            } else {
                $('#iframe_div').css('width','100%');
            }
        });
        window.addEventListener('message', function(event) {
            if(event.data.payload==='change_room') {
                $('.toolbar_preview_loading').addClass('hidden');
                $('.toolbar_preview_buttons').show();
                var id_room = event.data.id_room;
                var name_room = event.data.name_room;
                var image_room = event.data.image_room.replace('panoramas/','panoramas/thumb/');
                image_room = image_room.replace('mobile/','');
                $('#preview_room_name').html(name_room);
                $('#preview_room_image').attr('src',((window.s3_enabled) ? window.s3_url : '../')+'viewer/'+image_room);
                $('#btn_preview_edit_room').attr('href','?p=edit_room&id='+id_room);
                $('#btn_preview_markers').attr('href','?p=markers&id_room='+id_room);
                $('#btn_preview_pois').attr('href','?p=pois&id_room='+id_room);
                $('#btn_preview_measures').attr('href','?p=measurements&id_room='+id_room);
                $('#btn_preview_markers .badge').html(event.data.count_marker);
                $('#btn_preview_pois .badge').html(event.data.count_poi);
                $('#btn_preview_measures .badge').html(event.data.count_measure);
                if($('#toolbar_preview').length) {
                    var container_h = $('#content-wrapper').height() - 195;
                } else {
                    var container_h = $('#content-wrapper').height() - 155;
                }
                if($('#tawk-container').length) {
                    container_h = container_h - 20;
                }
                $('#btn_preview_reload').attr('onclick','preview_vt('+window.id_virtualtour+','+container_h+','+id_room+')');
            }
        });
    })(jQuery); // End of use strict
</script>