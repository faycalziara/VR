<?php
if(!isset($user_info)) {
    $user_info = get_user_info($_SESSION['id_user']);
}
$block_tour = false;
$block_tour_msg = "";
if($user_info['role']=='customer') {
    $query = "SELECT block_tour,block_tour_msg FROM svt_virtualtours WHERE id={$_SESSION['id_virtualtour_sel']} LIMIT 1";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows == 1) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $block_tour = $row['block_tour'];
            $block_tour_msg = $row['block_tour_msg'];
        }
    }
}
if($block_tour) : ?>
    <div class="card bg-secondary text-white shadow mb-4">
        <div class="card-body">
            <?php echo (empty($block_tour_msg)) ? _("This tour has been locked. If you didn't purchase a service, please contact support.") : $block_tour_msg; ?>
        </div>
    </div>
    <script>
        if($('#virtualtour_selector').length===0) {
            $('.vt_select_header').remove();
        }
    </script>
<?php exit; endif; ?>