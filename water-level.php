<?php $page_title = "BLIMAS - Water Level"; ?>
<?php include 'includes/header.php'; ?>

<main class="main-content chart-page">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <!-- Back Button -->
                <div class="back-button-container" data-aos="fade-right">
                    <a href="index.php" class="btn btn-outline-primary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>

                <!-- Page Header -->
                <div class="page-header text-center" data-aos="fade-up">
                    <div class="page-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h1 class="display-4 text-success">Water Level</h1>
                    <p class="lead text-muted">Lake water level monitoring and trends</p>
                </div>

                <!-- Controls -->
                <div class="chart-controls" data-aos="fade-up" data-aos-delay="100">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3>Time Period:</h3>
                        </div>
                        <div class="col-md-6">
                            <select id="periodSelect" class="form-select form-select-lg">
                                <option value="day">Today</option>
                                <option value="week">Last 7 Days</option>
                                <option value="month">Last 30 Days</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Chart Container -->
                <div class="chart-container" data-aos="zoom-in" data-aos-delay="200">
                    <canvas id="waterLevelChart"></canvas>
                    <div class="chart-loader" id="chartLoader">
                        <div class="spinner-border text-success" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row mt-5" data-aos="fade-up" data-aos-delay="300">
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="stat-card">
                            <div class="stat-icon text-success">
                                <i class="fas fa-water"></i>
                            </div>
                            <div class="stat-content">
                                <h4>Current</h4>
                                <span class="stat-value" id="currentLevel">-- m</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="stat-card">
                            <div class="stat-icon text-primary">
                                <i class="fas fa-arrow-up"></i>
                            </div>
                            <div class="stat-content">
                                <h4>Highest</h4>
                                <span class="stat-value" id="maxLevel">-- m</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="stat-card">
                            <div class="stat-icon text-danger">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                            <div class="stat-content">
                                <h4>Lowest</h4>
                                <span class="stat-value" id="minLevel">-- m</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="stat-card">
                            <div class="stat-icon text-info">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div class="stat-content">
                                <h4>Average</h4>
                                <span class="stat-value" id="avgLevel">-- m</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function waitForChart() {
    if (typeof Chart !== 'undefined') {
        console.log('Chart.js loaded successfully');
        const script = document.createElement('script');
        script.src = 'assets/js/water-level.js';
        script.onload = function() {
            console.log('Water level script loaded');
        };
        script.onerror = function() {
            console.error('Failed to load water level script');
        };
        document.head.appendChild(script);
    } else {
        console.log('Waiting for Chart.js...');
        setTimeout(waitForChart, 100);
    }
}

document.addEventListener('DOMContentLoaded', waitForChart);
</script>

<?php include 'includes/footer.php'; ?>