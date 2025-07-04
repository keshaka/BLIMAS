<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?php echo SITE_NAME; ?></title>
    
    <!-- Loading third party fonts -->
    <link href="http://fonts.googleapis.com/css?family=Roboto:300,400,700|" rel="stylesheet" type="text/css">
    <link href="fonts/font-awesome.min.css" rel="stylesheet" type="text/css">
    
    <!-- Loading main css file -->
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    
    <!-- Chart.js for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="site-content">
        <div class="site-header">
            <div class="container">
                <a href="index.php" class="branding">
                    <img src="images/logo.png" alt="" class="logo">
                    <div class="logo-type">
                        <h1 class="site-title"><?php echo SITE_NAME; ?></h1>
                        <small class="site-description"><?php echo SITE_DESCRIPTION; ?></small>
                    </div>
                </a>

                <!-- Navigation -->
                <?php include 'includes/navigation.php'; ?>

                <div class="mobile-navigation"></div>
            </div>
        </div> <!-- .site-header -->