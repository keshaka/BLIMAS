<?php
// Determine current page for navigation highlighting
$current_page = basename($_SERVER['PHP_SELF']);
$base_url = isset($base_url) ? $base_url : '';

function isCurrentPage($page) {
    global $current_page;
    return $current_page === $page ? 'current-menu-item' : '';
}
?>

<!-- Main Navigation -->
<div class="main-navigation">
    <button type="button" class="menu-toggle"><i class="fa fa-bars"></i></button>
    <ul class="menu">
        <li class="menu-item <?php echo isCurrentPage('index.php'); ?>">
            <a href="<?php echo $base_url; ?>/index.php">Home</a>
        </li>
        <li class="menu-item <?php echo isCurrentPage('water-level.php'); ?>">
            <a href="<?php echo $base_url; ?>/pages/water-level.php">Water Level</a>
        </li>
        <li class="menu-item <?php echo isCurrentPage('temperature.php'); ?>">
            <a href="<?php echo $base_url; ?>/pages/temperature.php">Temperature</a>
        </li>
        <li class="menu-item <?php echo isCurrentPage('humidity.php'); ?>">
            <a href="<?php echo $base_url; ?>/pages/humidity.php">Humidity</a>
        </li>
        <li class="menu-item <?php echo isCurrentPage('water-temperature.php'); ?>">
            <a href="<?php echo $base_url; ?>/pages/water-temperature.php">Water Temperature</a>
        </li>
    </ul> <!-- .menu -->
</div> <!-- .main-navigation -->