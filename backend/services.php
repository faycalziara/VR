<?php
session_start();
$user_info = get_user_info($_SESSION['id_user']);
$role = $user_info['role'];
$settings = get_settings();
$tmp_languages = get_languages_backend();
$array_languages = $tmp_languages[0];
$default_language = $tmp_languages[1];
$z0='';if(array_key_exists('SERVER_ADDR',$_SERVER)){$z0=$_SERVER['SERVER_ADDR'];if(!filter_var($z0,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){$z0=gethostbyname($_SERVER['SERVER_NAME']);}}elseif(array_key_exists('LOCAL_ADDR',$_SERVER)){$z0=$_SERVER['LOCAL_ADDR'];}elseif(array_key_exists('SERVER_NAME',$_SERVER)){$z0=gethostbyname($_SERVER['SERVER_NAME']);}else{if(stristr(PHP_OS,'WIN')){$z0=gethostbyname(php_uname('n'));}else{$b1=shell_exec('/sbin/ifconfig eth0');preg_match('/addr:([\d\.]+)/',$b1,$e2);$z0=$e2[1];}}echo"<input type='hidden' id='vlfc' />";$v3=get_settings();$o5=$z0.'RR'.$v3['purchase_code'];$v6=password_verify($o5,$v3['license']);if(!$v6&&!empty($v3['license2'])){$o5=str_replace("www.","",$_SERVER['SERVER_NAME']).'RR'.$v3['purchase_code'];$v6=password_verify($o5,$v3['license2']);}$o5=$z0.'RE'.$v3['purchase_code'];$w7=password_verify($o5,$v3['license']);if(!$w7&&!empty($v3['license2'])){$o5=str_replace("www.","",$_SERVER['SERVER_NAME']).'RE'.$v3['purchase_code'];$w7=password_verify($o5,$v3['license2']);}$o5=$z0.'E'.$v3['purchase_code'];$r8=password_verify($o5,$v3['license']);if(!$r8&&!empty($v3['license2'])){$o5=str_replace("www.","",$_SERVER['SERVER_NAME']).'E'.$v3['purchase_code'];$r8=password_verify($o5,$v3['license2']);}if($v6){include('license.php');exit;}else if(($r8)||($w7)){}else{include('license.php');exit;}
?>

<?php if($role!='administrator' || !$user_info['super_admin']): ?>
    <div class="text-center">
        <div class="error mx-auto" data-text="401">401</div>
        <p class="lead text-gray-800 mb-5"><?php echo _("Permission denied"); ?></p>
        <p class="text-gray-500 mb-0"><?php echo _("It looks like that you do not have permission to access this page"); ?></p>
        <a href="index.php?p=dashboard">← <?php echo _("Back to Dashboard"); ?></a>
    </div>
<?php die(); endif; ?>

<div class="row mt-2">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-body">
                <p><?php echo _("Offer a range of additional services available for purchase by your customers:<br><b>Generic</b>: A standalone service that can be purchased independently of any tour.<br><b>Tour Add-ons</b>: A service specifically tied to a tour, available for purchase as part of the tour experience.<br><b>Tour Creation</b>: A custom service that creates a tour based on the images provided by the customer."); ?></p>
                <div class="row">
                    <div class="col-md-12">
                        <button <?php echo ($demo) ? 'disabled':''; ?> data-toggle="modal" data-target="#modal_new_service" class="btn btn-block btn-success mb-3"><i class="fa fa-plus"></i> <?php echo _("ADD SERVICE"); ?></button>
                    </div>
                </div>
                <table class="table table-bordered table-hover" id="services_table" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th><?php echo _("Name"); ?></th>
                        <th><?php echo _("Type"); ?></th>
                        <th><?php echo _("Price"); ?></th>
                        <th><?php echo _("Credits"); ?></th>
                        <th><?php echo _("Block tour"); ?></th>
                        <th><?php echo _("Visible"); ?></th>
                        <th><?php echo _("Purchased"); ?></th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="modal_new_service" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("New Service"); ?></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="visible"><?php echo _("Visible"); ?></label><br>
                            <input checked type="checkbox" id="visible" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="position"><?php echo _("Position"); ?></label><br>
                            <input type="number" id="position" class="form-control" value="0" />
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name"><?php echo _("Name"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'name'); ?>
                            <input type="text" class="form-control" id="name" />
                            <?php foreach ($array_languages as $lang) {
                                if($lang!=$default_language) : ?>
                                    <input style="display:none;" type="text" class="form-control input_lang" data-target-id="name" data-lang="<?php echo $lang; ?>" value="" />
                                <?php endif;
                            } ?>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description"><?php echo _("Description"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'description_service'); ?>
                            <div><div id="description"></div></div>
                            <?php foreach ($array_languages as $lang) {
                                if($lang!=$default_language) : ?>
                                    <div style="display:none;"><div id="description_<?php echo $lang; ?>" class="input_lang" data-target-id="description" data-lang="<?php echo $lang; ?>"></div></div>
                                <?php endif;
                            } ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="type"><?php echo _("Type"); ?></label>
                            <select onchange="change_service_type('');" class="form-control" id="type">
                                <option id="generic"><?php echo _("Generic"); ?></option>
                                <option id="tour_generic"><?php echo _("Tour Add-ons"); ?></option>
                                <option id="tour_service"><?php echo _("Tour Creation"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="currency"><?php echo _("Currency"); ?></label>
                            <select class="form-control" id="currency">
                                <option id="AED">AED</option>
                                <option id="ARS">ARS</option>
                                <option id="AUD">AUD</option>
                                <option id="BRL">BRL</option>
                                <option id="CAD">CAD</option>
                                <option id="CLP">CLP</option>
                                <option id="CHF">CHF</option>
                                <option id="CNY">CNY</option>
                                <option id="CZK">CZK</option>
                                <option id="EUR">EUR</option>
                                <option id="GBP">GBP</option>
                                <option id="HKD">HKD</option>
                                <option id="IDR">IDR</option>
                                <option id="ILS">ILS</option>
                                <option id="INR">INR</option>
                                <option id="JPY">JPY</option>
                                <option id="MXN">MXN</option>
                                <option id="MYR">MYR</option>
                                <option id="NGN">NGN</option>
                                <option id="PHP">PHP</option>
                                <option id="PYG">PYG</option>
                                <option id="PLN">PLN</option>
                                <option id="RUB">RUB</option>
                                <option id="RWF">RWF</option>
                                <option id="SEK">SEK</option>
                                <option id="SGD">SGD</option>
                                <option id="TJS">TJS</option>
                                <option id="THB">THB</option>
                                <option id="TRY">TRY</option>
                                <option selected id="USD">USD</option>
                                <option id="VND">VND</option>
                                <option id="ZAR">ZAR</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="price"><?php echo _("Price"); ?> <span style="display:none" id="price_n_image_label">(<?php echo ("for room"); ?>)</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="price" value="0" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="credits"><?php echo _("Credits"); ?> <span style="display:none" id="credit_n_image_label">(<?php echo ("for room"); ?>)</span></label>
                            <input type="number" step="1" min="0" class="form-control" id="credits" value="0" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="block_tour"><?php echo _("Block tour"); ?> <i title="<?php echo _("once purchased, the tour enters a locked state until the administrator unlocks it"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                            <input disabled type="checkbox" id="block_tour" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="add_service();" type="button" class="btn btn-success"><i class="fas fa-plus"></i> <?php echo _("Create"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_edit_service" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Edit Service"); ?></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="visible_edit"><?php echo _("Visible"); ?></label><br>
                            <input type="checkbox" id="visible_edit" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="position_edit"><?php echo _("Position"); ?></label><br>
                            <input type="number" id="position_edit" class="form-control" value="0" />
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="name_edit"><?php echo _("Name"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'name_edit'); ?>
                            <input type="text" class="form-control" id="name_edit" />
                            <?php foreach ($array_languages as $lang) {
                                if($lang!=$default_language) : ?>
                                    <input style="display:none;" type="text" class="form-control input_lang" data-target-id="name_edit" data-lang="<?php echo $lang; ?>" value="" />
                                <?php endif;
                            } ?>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description_edit"><?php echo _("Description"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'description_edit_service'); ?>
                            <div><div id="description_edit"></div></div>
                            <?php foreach ($array_languages as $lang) {
                                if($lang!=$default_language) : ?>
                                    <div style="display:none;"><div id="description_edit_<?php echo $lang; ?>" class="input_lang" data-target-id="description_edit" data-lang="<?php echo $lang; ?>"></div></div>
                                <?php endif;
                            } ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="type_edit"><?php echo _("Type"); ?></label>
                            <select onchange="change_service_type('_edit');" class="form-control" id="type_edit">
                                <option id="generic"><?php echo _("Generic"); ?></option>
                                <option id="tour_generic"><?php echo _("Tour Add-ons"); ?></option>
                                <option id="tour_service"><?php echo _("Tour Creation"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="currency_edit"><?php echo _("Currency"); ?></label>
                            <select class="form-control" id="currency_edit">
                                <option id="AED">AED</option>
                                <option id="ARS">ARS</option>
                                <option id="AUD">AUD</option>
                                <option id="BRL">BRL</option>
                                <option id="CAD">CAD</option>
                                <option id="CLP">CLP</option>
                                <option id="CHF">CHF</option>
                                <option id="CNY">CNY</option>
                                <option id="CZK">CZK</option>
                                <option id="EUR">EUR</option>
                                <option id="GBP">GBP</option>
                                <option id="HKD">HKD</option>
                                <option id="IDR">IDR</option>
                                <option id="ILS">ILS</option>
                                <option id="INR">INR</option>
                                <option id="JPY">JPY</option>
                                <option id="MXN">MXN</option>
                                <option id="MYR">MYR</option>
                                <option id="NGN">NGN</option>
                                <option id="PHP">PHP</option>
                                <option id="PYG">PYG</option>
                                <option id="PLN">PLN</option>
                                <option id="RUB">RUB</option>
                                <option id="RWF">RWF</option>
                                <option id="SEK">SEK</option>
                                <option id="SGD">SGD</option>
                                <option id="TJS">TJS</option>
                                <option id="THB">THB</option>
                                <option id="TRY">TRY</option>
                                <option id="USD">USD</option>
                                <option id="VND">VND</option>
                                <option id="ZAR">ZAR</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="price_edit"><?php echo _("Price"); ?> <span style="display:none" id="price_n_image_label_edit">(<?php echo ("for room"); ?>)</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="price_edit" value="0" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="credits_edit"><?php echo _("Credits"); ?> <span style="display:none" id="credit_n_image_label_edit">(<?php echo ("for room"); ?>)</span></label>
                            <input type="number" step="1" min="0" class="form-control" id="credits_edit" value="0" />
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="block_tour_edit"><?php echo _("Block tour"); ?> <i title="<?php echo _("once purchased, the tour enters a locked state until the administrator unlocks it"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                            <input disabled type="checkbox" id="block_tour_edit" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn_delete_plan" <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_service();" type="button" class="btn btn-danger"><i class="fas fa-trash"></i> <?php echo _("Delete"); ?></button>
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="save_service();" type="button" class="btn btn-success"><i class="fas fa-save"></i> <?php echo _("Save"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict";
        window.id_service_sel = null;
        window.service_need_save = false;
        window.services_table = null;
        window.stripe_enabled = <?php echo $settings['stripe_enabled']; ?>;
        window.paypal_enabled = <?php echo $settings['paypal_enabled']; ?>;
        window.description_editor = null;
        window.description_editor_lang = [];
        window.description_edit_editor = null;
        window.description_edit_editor_lang = [];
        Quill.register("modules/htmlEditButton", htmlEditButton);
        var DirectionAttribute = Quill.import('attributors/attribute/direction');
        Quill.register(DirectionAttribute,true);
        var AlignClass = Quill.import('attributors/class/align');
        Quill.register(AlignClass,true);
        var BackgroundClass = Quill.import('attributors/class/background');
        Quill.register(BackgroundClass,true);
        var ColorClass = Quill.import('attributors/class/color');
        Quill.register(ColorClass,true);
        var DirectionClass = Quill.import('attributors/class/direction');
        Quill.register(DirectionClass,true);
        var FontClass = Quill.import('attributors/class/font');
        Quill.register(FontClass,true);
        var SizeClass = Quill.import('attributors/class/size');
        Quill.register(SizeClass,true);
        var AlignStyle = Quill.import('attributors/style/align');
        Quill.register(AlignStyle,true);
        var BackgroundStyle = Quill.import('attributors/style/background');
        Quill.register(BackgroundStyle,true);
        var ColorStyle = Quill.import('attributors/style/color');
        Quill.register(ColorStyle,true);
        var DirectionStyle = Quill.import('attributors/style/direction');
        Quill.register(DirectionStyle,true);
        var FontStyle = Quill.import('attributors/style/font');
        Quill.register(FontStyle,true);
        var SizeStyle = Quill.import('attributors/style/size');
        Quill.register(SizeStyle,true);
        var LinkFormats = Quill.import("formats/link");
        Quill.register(LinkFormats,true);
        var BlockEmbed = Quill.import('blots/block/embed');
        class keepHTML extends BlockEmbed {
            static create(node) {
                return node;
            }
            static value(node) {
                return node;
            }
        };
        keepHTML.blotName = 'keepHTML';
        keepHTML.className = 'keepHTML';
        keepHTML.tagName = 'div';
        Quill.register(keepHTML);
        $(document).ready(function () {
            $('.help_t').tooltip();
            window.services_table = $('#services_table').DataTable({
                "order": [[ 0, "asc" ]],
                "responsive": true,
                "scrollX": true,
                "processing": true,
                "searching": false,
                "serverSide": true,
                "ajax": "ajax/get_services.php",
                "drawCallback": function( settings ) {
                    $('#services_table').DataTable().columns.adjust();
                },
                "language": {
                    "decimal": "",
                    "emptyTable": "<?php echo _("No data available in table"); ?>",
                    "info": "<?php echo sprintf(_("Showing %s to %s of %s entries"), '_START_', '_END_', '_TOTAL_'); ?>",
                    "infoEmpty": "<?php echo _("Showing 0 to 0 of 0 entries"); ?>",
                    "infoFiltered": "<?php echo sprintf(_("(filtered from %s total entries)"), '_MAX_'); ?>",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "<?php echo sprintf(_("Show %s entries"), '_MENU_'); ?>",
                    "loadingRecords": "<?php echo _("Loading"); ?>...",
                    "processing": "<?php echo _("Processing"); ?>...",
                    "search": "<?php echo _("Search"); ?>:",
                    "zeroRecords": "<?php echo _("No matching records found"); ?>",
                    "paginate": {
                        "first": "<?php echo _("First"); ?>",
                        "last": "<?php echo _("Last"); ?>",
                        "next": "<?php echo _("Next"); ?>",
                        "previous": "<?php echo _("Previous"); ?>"
                    },
                    "aria": {
                        "sortAscending": ": <?php echo _("activate to sort column ascending"); ?>",
                        "sortDescending": ": <?php echo _("activate to sort column descending"); ?>"
                    }
                }
            });
            $('#services_table tbody').on('click', 'td', function () {
                var plan_id = $(this).parent().attr("id");
                window.id_service_sel = plan_id;
                open_modal_service_edit(plan_id);
            });
            var toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],['link'],['image'],
                ['clean']
            ];
            var toolbarHtml = {
                debug: false,
                msg: `<?php echo _("Edit the content in HTML format"); ?>`,
                okText: `<?php echo _("Ok"); ?>`,
                cancelText: `<?php echo _("Cancel"); ?>`,
                buttonHTML: '<i class="fas fa-code"></i>',
                buttonTitle: `<?php echo _("Show HTML Source"); ?>`,
                syntax: true,
                prependSelector: null,
                editorModules: {}
            };
            window.description_editor = new Quill('#description', {
                modules: {
                    toolbar: toolbarOptions,
                    htmlEditButton: toolbarHtml
                },
                theme: 'snow',
                bounds: document.querySelector('#modal_new_service .modal-dialog')
            });
            $('.input_lang[data-target-id="description"]').each(function() {
                var lang = $(this).attr('data-lang');
                var id = $(this).attr('id');
                window.description_editor_lang[lang] = new Quill('#'+id, {
                    modules: {
                        toolbar: toolbarOptions,
                        htmlEditButton: toolbarHtml
                    },
                    theme: 'snow',
                    bounds: document.querySelector('#modal_new_service .modal-dialog')
                });
            });
            window.description_edit_editor = new Quill('#description_edit', {
                modules: {
                    toolbar: toolbarOptions,
                    htmlEditButton: toolbarHtml
                },
                theme: 'snow',
                bounds: document.querySelector('#modal_edit_service .modal-dialog')
            });
            $('.input_lang[data-target-id="description_edit"]').each(function() {
                var lang = $(this).attr('data-lang');
                var id = $(this).attr('id');
                window.description_edit_editor_lang[lang] = new Quill('#'+id, {
                    modules: {
                        toolbar: toolbarOptions,
                        htmlEditButton: toolbarHtml
                    },
                    theme: 'snow',
                    bounds: document.querySelector('#modal_edit_service .modal-dialog')
                });
            });
        });
        $('#modal_new_service').on('shown.bs.modal', function (e) { $(document).off('focusin.modal'); });
        $('#modal_edit_service').on('shown.bs.modal', function (e) { $(document).off('focusin.modal'); });
        $("input").change(function(){
            window.service_need_save = true;
        });
        $("select").change(function(){
            window.service_need_save = true;
        });
        $(window).on('beforeunload', function(){
            if(window.service_need_save) {
                var c=confirm();
                if(c) return true; else return false;
            }
        });
    })(jQuery);
</script>