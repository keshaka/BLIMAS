<?php $page_title = "BLIMAS - AI Environmental Analysis"; ?>
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
                        <i class="fas fa-brain"></i>
                    </div>
                    <h1 class="display-4">Environmental Analysis System</h1>
                    <p class="lead text-muted">Intelligent insights using real sensor data</p>
                    
                    <!-- Network Status Indicator -->
                    <div class="network-status" id="networkStatus">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Status:</strong> Using enhanced data-driven analysis with your actual sensor readings
                        </div>
                    </div>
                </div>

                <!-- Analysis Controls -->
                <div class="chart-controls" data-aos="fade-up" data-aos-delay="100">
                    <div class="row">
                        <div class="col-lg-4 col-md-6 mb-3">
                            <label for="analysisType" class="form-label"><strong>Analysis Type:</strong></label>
                            <select id="analysisType" class="form-select form-select-lg">
                                <option value="comprehensive">Comprehensive Analysis</option>
                                <option value="water_quality">Water Quality Focus</option>
                                <option value="climate_impact">Climate Impact</option>
                                <option value="ecosystem_health">Ecosystem Health</option>
                                <option value="alerts">Environmental Alerts</option>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-6 mb-3">
                            <label for="analysisPeriod" class="form-label"><strong>Time Period:</strong></label>
                            <select id="analysisPeriod" class="form-select form-select-lg">
                                <option value="day">Last 24 Hours</option>
                                <option value="week" selected>Last 7 Days</option>
                                <option value="month">Last 30 Days</option>
                            </select>
                        </div>
                        <div class="col-lg-4 col-md-12 mb-3">
                            <label class="form-label"><strong>Action:</strong></label><br>
                            <button id="generateAnalysis" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-chart-line me-2"></i>Generate Analysis
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Analysis Results -->
                <div class="analysis-container" id="analysisContainer" style="display: none;" data-aos="zoom-in">
                    <div class="analysis-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3><i class="fas fa-microscope me-2"></i>Environmental Analysis Results</h3>
                            <div class="analysis-meta">
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Generated: <span id="analysisTimestamp">--</span>
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="analysis-content" id="analysisContent">
                        <!-- Analysis will be inserted here -->
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="analysis-stats" id="analysisStats" style="display: none;">
                        <h4><i class="fas fa-chart-bar me-2"></i>Data Summary</h4>
                        <div class="row" id="statsCards">
                            <!-- Stats cards will be inserted here -->
                        </div>
                    </div>
                </div>

                <!-- Loading State -->
                <div class="analysis-loading" id="analysisLoading" style="display: none;">
                    <div class="loading-content">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h4 class="mt-3">Analyzing Environmental Data...</h4>
                        <p class="text-muted">Processing your Bolgoda Lake sensor readings</p>
                        <div class="progress mt-3">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>

                <!-- Features Overview -->
                <div class="features-overview" id="featuresOverview" data-aos="fade-up" data-aos-delay="200">
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <div class="feature-card">
                                <h4><i class="fas fa-database me-2 text-primary"></i>Data-Driven Analysis</h4>
                                <p>Our system analyzes your <strong>actual sensor readings</strong> from Bolgoda Lake to provide:</p>
                                <ul>
                                    <li>Real environmental condition assessment</li>
                                    <li>Trend analysis based on your data</li>
                                    <li>Customized recommendations</li>
                                    <li>Alert identification from actual readings</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-6 mb-4">
                            <div class="feature-card">
                                <h4><i class="fas fa-cogs me-2 text-success"></i>Analysis Types</h4>
                                <p>Choose from specialized analysis focuses:</p>
                                <ul>
                                    <li><strong>Comprehensive:</strong> Overall environmental health</li>
                                    <li><strong>Water Quality:</strong> Lake condition assessment</li>
                                    <li><strong>Climate Impact:</strong> Weather pattern analysis</li>
                                    <li><strong>Ecosystem Health:</strong> Biodiversity indicators</li>
                                    <li><strong>Alerts:</strong> Critical condition monitoring</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="assets/js/analysis.js"></script>

<?php include 'includes/footer.php'; ?>