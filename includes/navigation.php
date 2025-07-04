<?php
// Determine the base path
$base_path = '';
if (strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) {
    $base_path = '../';
}
?>
<div class="main-navigation">
    <button type="button" class="menu-toggle"><i class="fa fa-bars"></i></button>
    <ul class="menu">
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'current-menu-item' : ''; ?>">
            <a href="<?php echo $base_path; ?>index.php">Home</a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'water-level.php' ? 'current-menu-item' : ''; ?>">
            <a href="<?php echo $base_path; ?>pages/water-level.php">Water Level</a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'temperature.php' ? 'current-menu-item' : ''; ?>">
            <a href="<?php echo $base_path; ?>pages/temperature.php">Temperature</a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'humidity.php' ? 'current-menu-item' : ''; ?>">
            <a href="<?php echo $base_path; ?>pages/humidity.php">Humidity</a>
        </li>
        <li class="menu-item <?php echo basename($_SERVER['PHP_SELF']) == 'water-temperature.php' ? 'current-menu-item' : ''; ?>">
            <a href="<?php echo $base_path; ?>pages/water-temperature.php">Water Temperature</a>
        </li>
    </ul> <!-- .menu -->
</div> <!-- .main-navigation -->