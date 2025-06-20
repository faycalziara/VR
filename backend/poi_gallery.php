<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
require_once(__DIR__."/functions.php");
$id_user = $_SESSION['id_user'];
$id_virtualtour_sel = $_SESSION['id_virtualtour_sel'];
$settings = get_settings();
$user_info = get_user_info($id_user);
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}
$id_poi = $_GET['id_poi'];
$upload_content = true;
if($user_info['plan_status']=='expired') {
    $upload_content = false;
}
$demo = $_SESSION['demo'];
$virtual_tour = get_virtual_tour($id_virtualtour_sel,$id_user);
if($virtual_tour!==false) {
    $tmp_languages = get_languages_vt();
    $array_languages = $tmp_languages[0];
    $default_language = $tmp_languages[1];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-grip-horizontal"></i> <?php echo _("Images List"); ?> <i style="font-size:12px">(<?php echo _("drag images to change order"); ?>)</i></h6>
            </div>
            <div class="card-body">
                <?php if($upload_content) : ?><form action="ajax/upload_gallery_image.php" class="dropzone mb-3 noselect" id="gallery-dropzone"></form><?php endif; ?>
                <div id="list_images" class="noselect">
                    <p><?php echo _("Loading images ..."); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_caption" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="title"><?php echo _("Title"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'title'); ?>
                            <input type="text" class="form-control" id="title" />
                            <?php foreach ($array_languages as $lang) {
                                if($lang!=$default_language) : ?>
                                    <input style="display:none;" type="text" class="form-control input_lang" data-target-id="title" data-lang="<?php echo $lang; ?>" value="" />
                                <?php endif;
                            } ?>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description"><?php echo _("Description"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'description'); ?>
                            <textarea id="description" class="form-control" rows="3"></textarea>
                            <?php foreach ($array_languages as $lang) {
                                if($lang!=$default_language) : ?>
                                    <textarea rows="3" style="display:none;" class="form-control input_lang" data-target-id="description" data-lang="<?php echo $lang; ?>"></textarea>
                                <?php endif;
                            } ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_save_caption" onclick="" type="button" class="btn btn-success"><i class="fas fa-save"></i> <?php echo _("Save"); ?></button>
                <button onclick="close_modal('modal_caption');" type="button" class="btn btn-secondary"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.id_poi = '<?php echo $id_poi; ?>';
        window.gallery_images = [];
        Dropzone.autoDiscover = false;
        $(document).ready(function () {
            get_poi_gallery_images(id_poi);
            if($('#gallery-dropzone').length) {
                var gallery_dropzone = new Dropzone("#gallery-dropzone", {
                    url: "ajax/upload_gallery_image.php",
                    parallelUploads: 1,
                    maxFilesize: 20,
                    timeout: 120000,
                    dictDefaultMessage: "<?php echo _("Drop files or click here to upload"); ?>",
                    dictFallbackMessage: "<?php echo _("Your browser does not support drag'n'drop file uploads."); ?>",
                    dictFallbackText: "<?php echo _("Please use the fallback form below to upload your files like in the olden days."); ?>",
                    dictFileTooBig: "<?php echo sprintf(_("File is too big (%sMiB). Max filesize: %sMiB."),'{{filesize}}','{{maxFilesize}}'); ?>",
                    dictInvalidFileType: "<?php echo _("You can't upload files of this type."); ?>",
                    dictResponseError: "<?php echo sprintf(_("Server responded with %s code."),'{{statusCode}}'); ?>",
                    dictCancelUpload: "<?php echo _("Cancel upload"); ?>",
                    dictCancelUploadConfirmation: "<?php echo _("Are you sure you want to cancel this upload?"); ?>",
                    dictRemoveFile: "<?php echo _("Remove file"); ?>",
                    dictMaxFilesExceeded: "<?php echo _("You can not upload any more files."); ?>",
                    acceptedFiles: 'image/*'
                });
                gallery_dropzone.on("addedfile", function(file) {
                    $('#list_images').addClass('disabled');
                });
                gallery_dropzone.on("success", function(file,rsp) {
                    if(rsp !== "" && !rsp.startsWith("ERROR")) {
                        add_image_to_poi_gallery(id_poi, rsp);
                    }
                });
                gallery_dropzone.on("queuecomplete", function() {
                    $('#list_images').removeClass('disabled');
                    gallery_dropzone.removeAllFiles();
                });
            }
        });
    })(jQuery); // End of use strict
</script>