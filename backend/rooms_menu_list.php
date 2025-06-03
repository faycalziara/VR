<?php
session_start();
if(isset($_SESSION['id_virtualtour_sel'])) {
    $id_vt_rm = $_SESSION['id_virtualtour_sel'];
    if(!isset($virtual_tour)) {
        $virtual_tour = get_virtual_tour($id_vt_rm,$_SESSION['id_user']);
    }
    if(!isset($tmp_languages)) {
        $tmp_languages = get_languages_vt();
    }
    $array_languages = $tmp_languages[0];
    $default_language = $tmp_languages[1];
    $show_in_ui = $virtual_tour['show_list_alt'];
    if(count($array_languages)>1) {
        $show_language = true;
        $col = 4;
    } else {
        $show_language = false;
        $col = 6;
    }
} else {
    $id_vt_rm = '';
}
?>

<div class="d-sm-flex align-items-center justify-content-between mb-3">
    <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-fw fa-list-alt text-gray-700"></i> <?php echo _("ROOMS LIST"); ?> <i style="font-size:12px;vertical-align:middle;color:<?php echo ($show_in_ui>0)?'green':'orange'; ?>" <?php echo ($show_in_ui==0)?'title="'._("Not visible in the tour, enable it in the Editor UI").'"':''; ?> class="<?php echo ($show_in_ui==0)?'help_t':''; ?> show_in_ui fas fa-circle"></i></h1>
    <?php if($show_language) : ?>
        <div class="float-right d-inline-block">
            <label class="mb-0"><?php echo _("Language"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'rooms_menu_list'); ?>
        </div>
    <?php endif; ?>
</div>
<div class="row">
    <div class="col-md-12">
        <div id="rooms_list">
            <div class="card mb-4 py-3">
                <div class="card-body" style="padding-top: 0;padding-bottom: 0;">
                    <div style="display: none;max-width: 600px;" class="row add_cat_div">
                        <div class="col-md-12 mb-2">
                            <div class="form-group">
                                <div class="input-group mb-1">
                                    <input type="text" class="form-control bg-white" id="add_cat" placeholder="<?php echo _("Add Category"); ?>" value="">
                                    <div class="input-group-append">
                                        <button onclick="add_menu_list_cat()" class="btn btn-success btn-xs">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php echo _("Drag and drop rooms into categories or up/down to change its order"); ?>
                        </div>
                    </div>
                    <div id="rooms_menu_list" class="row list_div">
                        <div style="line-height:40px;" class="col-md-8 text-center text-sm-center text-md-left text-lg-left">
                            <?php echo _("LOADING MENU LIST ..."); ?>
                        </div>
                        <div class="col-md-4 text-center text-sm-center text-md-right text-lg-right">
                            <a href="#" class="btn btn-primary btn-circle">
                                <i class="fas fa-spin fa-spinner"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.id_vt_rm = '<?php echo $id_vt_rm; ?>';
        window.selected_language = '';
        window.default_language = '<?php echo $default_language; ?>';
        window.rooms_list_show_lang = <?php echo ($show_language) ? 1 : 0; ?>;

        jQuery.fn.scrollParent = function() {
            var overflowRegex = /(auto|scroll)/,
                position = this.css( "position" ),
                excludeStaticParent = position === "absolute",
                scrollParent = this.parents().filter( function() {
                    var parent = $( this );
                    if ( excludeStaticParent && parent.css( "position" ) === "static" ) {
                        return false;
                    }
                    var overflowState = parent.css(["overflow", "overflowX", "overflowY"]);
                    return (overflowRegex).test( overflowState.overflow + overflowState.overflowX + overflowState.overflowY );
                }).eq( 0 );

            return position === "fixed" || !scrollParent.length ? $( this[ 0 ].ownerDocument || document ) : scrollParent;
        };

        $(document).ready(function () {
            $('.help_t').tooltip();
            if(window.id_vt_rm!='') {
                get_rooms_menu_list(window.id_vt_rm);
            }
        });
    })(jQuery); // End of use strict
</script>