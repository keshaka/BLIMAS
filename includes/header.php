<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'BLIMAS - Bolgoda Lake Monitoring'; ?></title>
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/analysis.css">
</head>
<body>
    <!-- Loader -->
    <div id="loader" class="loader-wrapper">
        <div class="loader">
            <div class="water-wave"></div>
            <div class="loader-text">Loading BLIMAS...</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-water me-2"></i>BLIMAS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="air-temperature.php"><i class="fas fa-thermometer-half me-1"></i>Air Temp</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="humidity.php"><i class="fas fa-tint me-1"></i>Humidity</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="water-level.php"><i class="fas fa-chart-line me-1"></i>Water Level</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="water-temperature.php"><i class="fas fa-temperature-low me-1"></i>Water Temp</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="analysis.php"><i class="fas fa-brain me-1"></i>AI Analysis</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>