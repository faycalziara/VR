<?php
session_start();
$id_user = $_SESSION['id_user'];
$id_virtualtour_sel = $_SESSION['id_virtualtour_sel'];
$can_create = get_plan_permission($id_user)['enable_learning'];
$virtual_tour = get_virtual_tour($id_virtualtour_sel,$id_user);
$settings = get_settings();
$theme_color = $settings['theme_color'];
if(empty($_SESSION['lang'])) {
    $lang = $settings['language'];
} else {
    $lang = $_SESSION['lang'];
}
?>

<?php include("check_plan.php"); ?>

<?php include("check_block_tour.php"); ?>

<?php if($virtual_tour['external']==1) : ?>
    <div class="card bg-warning text-white shadow mb-4">
        <div class="card-body">
            <?php echo _("You cannot use Learning on an external virtual tour!"); ?>
        </div>
    </div>
<?php exit; endif; ?>

<?php if(!$can_create) : ?>
    <div class="card bg-warning text-white shadow mb-4">
        <div class="card-body">
            <?php echo sprintf(_('Your "%s" plan not allow to use Learning!'),$user_info['plan'])." ".$msg_change_plan; ?>
        </div>
    </div>
<?php exit; endif; ?>

<div class="row">
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-warning shadow h-100 p-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"><?php echo _("Sessions"); ?></div>
                        <div id="num_sessions" class="h5 mb-0 font-weight-bold text-gray-800">--</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-user-group fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-info shadow h-100 p-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1"><?php echo _("Rooms visited"); ?></div>
                        <div id="num_rooms_visited" class="h5 mb-0 font-weight-bold text-gray-800">--</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-vector-square fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-info shadow h-100 p-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1"><?php echo _("POIs visited"); ?></div>
                        <div id="num_pois_visited" class="h5 mb-0 font-weight-bold text-gray-800">--</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-bullseye fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6 mb-3">
        <div class="card border-left-success shadow h-100 p-1">
            <div class="card-body p-2">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1"><?php echo _("Average Score"); ?></div>
                        <div id="average_score" class="h5 mb-0 d-inline-block font-weight-bold text-gray-800">--</div>
                        <div id="total_score" style="font-size:12px;" class="mb-0 d-inline-block text-gray-800"> / <span>--</span></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-ranking-star fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-line"></i> <?php echo _("Learning Sessions"); ?></h6>
            </div>
            <div class="card-body p-2">
                <div id="chart_learning_sessions"></div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-body">
                <table class="table table-bordered table-hover" id="learning_table" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th><?php echo _("E-mail"); ?></th>
                        <th><?php echo _("Start at"); ?></th>
                        <th><?php echo _("Updated at"); ?></th>
                        <th style="width:200px;"><?php echo _("Rooms visited"); ?></th>
                        <th style="width:200px;"><?php echo _("Score"); ?></th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.id_user = '<?php echo $id_user; ?>';
        window.id_virtualtour = '<?php echo $id_virtualtour_sel; ?>';
        $(document).ready(function () {
            get_statistics_learning('summary');
            get_statistics_learning('chart_learning_sessions');
            $('#learning_table').DataTable({
                "order": [[ 1, "desc" ]],
                "stateSave": false,
                "responsive": true,
                "scrollX": true,
                "processing": true,
                "searching": true,
                "serverSide": true,
                "ajax": "ajax/get_statistics_learning_list.php?id_virtualtour=<?php echo $id_virtualtour_sel; ?>",
                "drawCallback": function( settings ) {
                    $('#learning_table').DataTable().columns.adjust();
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
        });
        var xhrPool = [];
        $(document).ajaxSend(function(e, jqXHR, options){
            xhrPool.push(jqXHR);
        });
        $(document).ajaxComplete(function(e, jqXHR, options) {
            xhrPool = $.grep(xhrPool, function(x){return x!=jqXHR});
        });
        var abort = function() {
            $.each(xhrPool, function(idx, jqXHR) {
                jqXHR.abort();
            });
        };
        var oldbeforeunload = window.onbeforeunload;
        window.onbeforeunload = function() {
            var r = oldbeforeunload ? oldbeforeunload() : undefined;
            if (r == undefined) {
                abort();
            }
            return r;
        }
    })(jQuery); // End of use strict
</script>