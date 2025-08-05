<?php $page_title = "BLIMAS - Home"; ?>
<?php include 'includes/header.php'; ?>

<main class="main-content">
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6" data-aos="fade-right">
                    <h1 class="display-4 text-primary mb-4">
                        <i class="fas fa-water me-3"></i>BLIMAS
                    </h1>
                    <h2 class="h3 text-secondary mb-4">Bolgoda Lake Information Monitoring and Analysis System</h2>
                    <p class="lead">Real-time environmental monitoring for sustainable lake management</p>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <div class="hero-image">
                        <div class="floating-card">
                            <i class="fas fa-chart-line fa-3x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Real-time Data Section -->
    <section class="py-5" id="data-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center mb-5" data-aos="fade-up">
                    <h2 class="display-5 mb-3">Real-time Lake Data</h2>
                    <p class="lead text-muted">Live updates every 5 minutes</p>
                    <div class="last-update">
                        <small class="text-muted">Last updated: <span id="lastUpdate">Loading...</span></small>
                    </div>
                </div>
            </div>

            <!-- Data Cards -->
            <div class="row g-4" id="dataCards">
                <!-- Air Temperature Card -->
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                    <div class="data-card gradient-blue">
                        <div class="card-icon">
                            <i class="fas fa-thermometer-half"></i>
                        </div>
                        <div class="card-content">
                            <h3>Air Temperature</h3>
                            <div class="data-value">
                                <span id="airTemp">--</span>
                                <small>°C</small>
                            </div>
                        </div>
                        <div class="card-trend" id="airTempTrend"></div>
                    </div>
                </div>

                <!-- Humidity Card -->
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                    <div class="data-card gradient-cyan">
                        <div class="card-icon">
                            <i class="fas fa-tint"></i>
                        </div>
                        <div class="card-content">
                            <h3>Humidity</h3>
                            <div class="data-value">
                                <span id="humidity">--</span>
                                <small>%</small>
                            </div>
                        </div>
                        <div class="card-trend" id="humidityTrend"></div>
                    </div>
                </div>

                <!-- Water Level Card -->
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                    <div class="data-card gradient-green">
                        <div class="card-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="card-content">
                            <h3>Water Level</h3>
                            <div class="data-value">
                                <span id="waterLevel">--</span>
                                <small>m</small>
                            </div>
                        </div>
                        <div class="card-trend" id="waterLevelTrend"></div>
                    </div>
                </div>

                <!-- Water Temperature Card -->
                <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="400">
                    <div class="data-card gradient-purple">
                        <div class="card-icon">
                            <i class="fas fa-temperature-low"></i>
                        </div>
                        <div class="card-content">
                            <h3>Water Temperature</h3>
                            <div class="water-temps">
                                <div class="temp-depth">
                                    <small>Surface:</small>
                                    <span id="waterTemp1">--</span>°C
                                </div>
                                <div class="temp-depth">
                                    <small>Mid:</small>
                                    <span id="waterTemp2">--</span>°C
                                </div>
                                <div class="temp-depth">
                                    <small>Bottom:</small>
                                    <span id="waterTemp3">--</span>°C
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="row mt-5">
                <div class="col-12 text-center" data-aos="fade-up">
                    <h3 class="mb-4">Detailed Analysis</h3>
                    <div class="quick-links">
                        <a href="air-temperature.php" class="btn btn-outline-primary btn-lg me-3 mb-3">
                            <i class="fas fa-thermometer-half me-2"></i>Air Temperature Trends
                        </a>
                        <a href="humidity.php" class="btn btn-outline-info btn-lg me-3 mb-3">
                            <i class="fas fa-tint me-2"></i>Humidity Analysis
                        </a>
                        <a href="water-level.php" class="btn btn-outline-success btn-lg me-3 mb-3">
                            <i class="fas fa-chart-line me-2"></i>Water Level History
                        </a>
                        <a href="water-temperature.php" class="btn btn-outline-warning btn-lg mb-3">
                            <i class="fas fa-temperature-low me-2"></i>Water Temperature Depths
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script src="assets/js/dashboard.js"></script>

<?php include 'includes/footer.php'; ?>