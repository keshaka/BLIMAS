<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/admin-auth.php';

// Check admin authentication
$auth = new AdminAuth();
$auth->requireAdmin();
$admin = $auth->getCurrentAdmin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title><?php echo isset($page_title) ? $page_title . ' - Admin - ' . SITE_NAME : 'Admin - ' . SITE_NAME; ?></title>
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?> - Admin Panel">

    <!-- Loading third party fonts -->
    <link href="http://fonts.googleapis.com/css?family=Roboto:300,400,700|" rel="stylesheet" type="text/css">
    <link href="../fonts/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- Loading CSS files -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../style.css">
    
    <!-- Chart.js for visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <div class="admin-header">
            <div class="container">
                <div class="admin-brand">
                    <a href="../index.php" class="brand-link">
                        <img src="../images/logo.png" alt="BLIMAS Logo" class="admin-logo">
                        <span class="brand-text"><?php echo SITE_NAME; ?> Admin</span>
                    </a>
                </div>
                
                <div class="admin-nav">
                    <ul class="nav-menu">
                        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
                            <a href="index.php"><i class="fa fa-dashboard"></i> Dashboard</a>
                        </li>
                        <li class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'manage-data.php' ? 'active' : ''; ?>">
                            <a href="manage-data.php"><i class="fa fa-database"></i> Data Management</a>
                        </li>
                        <li class="nav-item">
                            <a href="../index.php" target="_blank"><i class="fa fa-external-link"></i> View Site</a>
                        </li>
                    </ul>
                </div>
                
                <div class="admin-user">
                    <span class="welcome-text">Welcome, <?php echo htmlspecialchars($admin['username']); ?></span>
                    <a href="logout.php" class="logout-btn"><i class="fa fa-sign-out"></i> Logout</a>
                </div>
            </div>
        </div> <!-- .admin-header -->