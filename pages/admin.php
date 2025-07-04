<?php
/**
 * Admin Panel
 * BLIMAS - Bolgoda Lake Information Monitoring & Analysis System
 */

require_once '../config/database.php';
require_once '../config/config.php';

session_start();

// Simple authentication (in production, use proper authentication)
$admin_password = 'blimas_admin_2025'; // Change this in production
$is_authenticated = $_SESSION['admin_authenticated'] ?? false;

if ($_POST['password'] ?? false) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_authenticated'] = true;
        $is_authenticated = true;
    } else {
        $error_message = 'Invalid password';
    }
}

if ($_GET['logout'] ?? false) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// Get database statistics
$database = new Database();
$pdo = $database->connect();
$stats = [];

if ($pdo && $is_authenticated) {
    try {
        // Get record counts
        $stmt = $pdo->query("SELECT COUNT(*) as total_records FROM sensor_data");
        $stats['total_records'] = $stmt->fetch()['total_records'];
        
        $stmt = $pdo->query("SELECT COUNT(*) as active_alerts FROM alerts WHERE is_active = 1");
        $stats['active_alerts'] = $stmt->fetch()['active_alerts'];
        
        $stmt = $pdo->query("SELECT MAX(timestamp) as last_reading FROM sensor_data");
        $stats['last_reading'] = $stmt->fetch()['last_reading'];
        
        // Get recent data
        $stmt = $pdo->query("SELECT * FROM sensor_data ORDER BY timestamp DESC LIMIT 10");
        $recent_data = $stmt->fetchAll();
        
        // Get active alerts
        $stmt = $pdo->query("SELECT * FROM alerts WHERE is_active = 1 ORDER BY created_at DESC LIMIT 10");
        $active_alerts = $stmt->fetchAll();
        
    } catch (Exception $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Admin Panel</title>
    
    <link href="http://fonts.googleapis.com/css?family=Roboto:300,400,700|" rel="stylesheet" type="text/css">
    <link href="../fonts/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .login-form {
            max-width: 400px;
            margin: 100px auto;
            padding: 40px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        
        .admin-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        .data-table th,
        .data-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        .data-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        
        .alert-item {
            padding: 10px;
            margin: 5px 0;
            border-left: 4px solid;
            border-radius: 4px;
            background: #f8f9fa;
        }
        
        .alert-critical { border-left-color: #dc3545; }
        .alert-high { border-left-color: #fd7e14; }
        .alert-medium { border-left-color: #ffc107; }
        .alert-low { border-left-color: #28a745; }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
    </style>
</head>
<body>

<?php if (!$is_authenticated): ?>
    <div class="login-form">
        <h2 style="text-align: center; margin-bottom: 30px;">BLIMAS Admin Panel</h2>
        
        <?php if (isset($error_message)): ?>
            <div style="color: red; text-align: center; margin-bottom: 15px;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div style="margin-bottom: 20px;">
                <label for="password">Admin Password:</label>
                <input type="password" id="password" name="password" required 
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin-top: 5px;">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
        </form>
        
        <p style="text-align: center; margin-top: 20px; color: #666; font-size: 14px;">
            Default password: blimas_admin_2025<br>
            (Change this in production)
        </p>
    </div>

<?php else: ?>
    <div class="admin-container">
        <div class="admin-header">
            <h1>BLIMAS Admin Panel</h1>
            <div>
                <a href="../index.php" class="btn btn-primary">View Dashboard</a>
                <a href="?logout=1" class="btn btn-danger">Logout</a>
            </div>
        </div>

        <!-- System Statistics -->
        <div class="admin-grid">
            <div class="dashboard-card" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                <div class="card-header">
                    <h4 class="card-title">Total Records</h4>
                    <i class="fa fa-database card-icon"></i>
                </div>
                <div class="card-value">
                    <span><?php echo number_format($stats['total_records'] ?? 0); ?></span>
                </div>
            </div>
            
            <div class="dashboard-card" style="background: linear-gradient(135deg, #dc3545 0%, #e91e63 100%);">
                <div class="card-header">
                    <h4 class="card-title">Active Alerts</h4>
                    <i class="fa fa-exclamation-triangle card-icon"></i>
                </div>
                <div class="card-value">
                    <span><?php echo $stats['active_alerts'] ?? 0; ?></span>
                </div>
            </div>
            
            <div class="dashboard-card" style="background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);">
                <div class="card-header">
                    <h4 class="card-title">System Status</h4>
                    <i class="fa fa-heartbeat card-icon"></i>
                </div>
                <div class="card-value">
                    <span style="font-size: 1.5rem;">Online</span>
                </div>
            </div>
        </div>

        <!-- Data Export -->
        <div class="admin-card">
            <h3>Data Export</h3>
            <p>Export sensor data in various formats for analysis and backup.</p>
            
            <div style="margin: 15px 0;">
                <a href="../api/export-data.php?format=json&limit=100" class="btn btn-primary">Export JSON (100 records)</a>
                <a href="../api/export-data.php?format=csv&limit=100" class="btn btn-success">Export CSV (100 records)</a>
                <a href="../api/export-data.php?format=xml&limit=100" class="btn btn-primary">Export XML (100 records)</a>
            </div>
            
            <div style="margin: 15px 0;">
                <strong>By Data Type:</strong><br>
                <a href="../api/export-data.php?format=csv&type=temperature&limit=50" class="btn">Temperature Data</a>
                <a href="../api/export-data.php?format=csv&type=humidity&limit=50" class="btn">Humidity Data</a>
                <a href="../api/export-data.php?format=csv&type=water_level&limit=50" class="btn">Water Level Data</a>
                <a href="../api/export-data.php?format=csv&type=water_temperature&limit=50" class="btn">Water Temperature Data</a>
            </div>
        </div>

        <!-- Active Alerts -->
        <?php if (!empty($active_alerts)): ?>
        <div class="admin-card">
            <h3>Active Alerts</h3>
            <?php foreach ($active_alerts as $alert): ?>
                <div class="alert-item alert-<?php echo $alert['severity']; ?>">
                    <strong><?php echo ucfirst($alert['alert_type']); ?> Alert</strong> - 
                    <?php echo htmlspecialchars($alert['message']); ?>
                    <br>
                    <small>Created: <?php echo $alert['created_at']; ?></small>
                    <a href="../api/alerts.php?action=acknowledge&id=<?php echo $alert['id']; ?>" class="btn btn-success" style="float: right;">Acknowledge</a>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Recent Data -->
        <div class="admin-card">
            <h3>Recent Sensor Readings</h3>
            <?php if (!empty($recent_data)): ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Air Temp</th>
                            <th>Humidity</th>
                            <th>Water Level</th>
                            <th>Battery</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_data as $row): ?>
                        <tr>
                            <td><?php echo $row['timestamp']; ?></td>
                            <td><?php echo $row['air_temp']; ?>°C</td>
                            <td><?php echo $row['humidity']; ?>%</td>
                            <td><?php echo $row['water_level']; ?>cm</td>
                            <td><?php echo $row['battery_level']; ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No recent data available.</p>
            <?php endif; ?>
        </div>

        <!-- System Information -->
        <div class="admin-card">
            <h3>System Information</h3>
            <table class="data-table">
                <tr>
                    <td><strong>PHP Version</strong></td>
                    <td><?php echo PHP_VERSION; ?></td>
                </tr>
                <tr>
                    <td><strong>Server Time</strong></td>
                    <td><?php echo date('Y-m-d H:i:s'); ?></td>
                </tr>
                <tr>
                    <td><strong>Last Data Reading</strong></td>
                    <td><?php echo $stats['last_reading'] ?? 'No data'; ?></td>
                </tr>
                <tr>
                    <td><strong>Database Status</strong></td>
                    <td><?php echo $pdo ? 'Connected' : 'Disconnected'; ?></td>
                </tr>
            </table>
        </div>

        <!-- API Testing -->
        <div class="admin-card">
            <h3>API Testing</h3>
            <p>Test various API endpoints:</p>
            <div>
                <a href="../api/get-data.php" class="btn btn-primary" target="_blank">Test Sensor Data API</a>
                <a href="../api/weather.php" class="btn btn-primary" target="_blank">Test Weather API</a>
                <a href="../api/alerts.php?action=check" class="btn btn-primary" target="_blank">Test Alert System</a>
            </div>
        </div>
    </div>
<?php endif; ?>

</body>
</html>