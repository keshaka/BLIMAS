<?php $page_title = "BLIMAS - Water Temperature"; ?>
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
                        <i class="fas fa-temperature-low"></i>
                    </div>
                    <h1 class="display-4 text-warning">Water Temperature</h1>
                    <p class="lead text-muted">Temperature variations across different depths</p>
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
                    <canvas id="waterTempChart"></canvas>
                    <div class="chart-loader" id="chartLoader">
                        <div class="spinner-border text-warning" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>

                <!-- Depth Stats -->
                <div class="row mt-5" data-aos="fade-up" data-aos-delay="300">
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="depth-card surface">
                            <div class="depth-header">
                                <i class="fas fa-water"></i>
                                <h4>Surface Level</h4>
                            </div>
                            <div class="depth-stats">
                                <div class="stat-item">
                                    <span class="label">Current:</span>
                                    <span class="value" id="currentSurface">--°C</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">Max:</span>
                                    <span class="value" id="maxSurface">--°C</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">Min:</span>
                                    <span class="value" id="minSurface">--°C</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">Avg:</span>
                                    <span class="value" id="avgSurface">--°C</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="depth-card middle">
                            <div class="depth-header">
                                <i class="fas fa-minus"></i>
                                <h4>Mid Level</h4>
                            </div>
                            <div class="depth-stats">
                                <div class="stat-item">
                                    <span class="label">Current:</span>
                                    <span class="value" id="currentMid">--°C</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">Max:</span>
                                    <span class="value" id="maxMid">--°C</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">Min:</span>
                                    <span class="value" id="minMid">--°C</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">Avg:</span>
                                    <span class="value" id="avgMid">--°C</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="depth-card bottom">
                            <div class="depth-header">
                                <i class="fas fa-arrow-down"></i>
                                <h4>Bottom Level</h4>
                            </div>
                            <div class="depth-stats">
                                <div class="stat-item">
                                    <span class="label">Current:</span>
                                    <span class="value" id="currentBottom">--°C</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">Max:</span>
                                    <span class="value" id="maxBottom">--°C</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">Min:</span>
                                    <span class="value" id="minBottom">--°C</span>
                                </div>
                                <div class="stat-item">
                                    <span class="label">Avg:</span>
                                    <span class="value" id="avgBottom">--°C</span>
                                </div>
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
        script.src = 'assets/js/water-temperature.js';
        script.onload = function() {
            console.log('Water temperature script loaded');
        };
        script.onerror = function() {
            console.error('Failed to load water temperature script');
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