<?php
require_once __DIR__ . '/../config/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <title><?php echo isset($page_title) ? $page_title . ' - ' . SITE_NAME : SITE_NAME; ?></title>
    <meta name="description" content="<?php echo SITE_DESCRIPTION; ?>">

    <!-- Loading third party fonts -->
    <link href="http://fonts.googleapis.com/css?family=Roboto:300,400,700|" rel="stylesheet" type="text/css">
    <link href="<?php echo isset($base_url) ? $base_url : ''; ?>/fonts/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- Loading main css file -->
    <link rel="stylesheet" href="<?php echo isset($base_url) ? $base_url : ''; ?>/style.css">
    <link rel="stylesheet" href="<?php echo isset($base_url) ? $base_url : ''; ?>/assets/css/style.css">
    
    <!-- Chart.js for visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <?php if (isset($additional_css)): ?>
        <?php foreach ($additional_css as $css): ?>
            <link rel="stylesheet" href="<?php echo $css; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!--[if lt IE 9]>
    <script src="<?php echo isset($base_url) ? $base_url : ''; ?>/js/ie-support/html5.js"></script>
    <script src="<?php echo isset($base_url) ? $base_url : ''; ?>/js/ie-support/respond.js"></script>
    <![endif]-->
</head>
<body>
    <div class="site-content">
        <div class="site-header">
            <div class="container">
                <a href="<?php echo isset($base_url) ? $base_url : ''; ?>/index.php" class="branding">
                    <img src="<?php echo isset($base_url) ? $base_url : ''; ?>/images/logo.png" alt="BLIMAS Logo" class="logo">
                    <div class="logo-type">
                        <h1 class="site-title"><?php echo SITE_NAME; ?></h1>
                        <small class="site-description"><?php echo SITE_DESCRIPTION; ?></small>
                    </div>
                </a>

                <?php include __DIR__ . '/navigation.php'; ?>

                <div class="mobile-navigation"></div>
            </div>
        </div> <!-- .site-header -->