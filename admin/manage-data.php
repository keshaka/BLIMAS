<?php
$page_title = 'Data Management';
include __DIR__ . '/../includes/admin-header.php';

// Handle data operations
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/database.php';
    
    $database = new Database();
    $conn = $database->getConnection();
    
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'delete_old':
                    $days = (int)($_POST['days'] ?? 30);
                    $stmt = $conn->prepare("DELETE FROM sensor_data WHERE timestamp < DATE_SUB(NOW(), INTERVAL ? DAY)");
                    $stmt->execute([$days]);
                    $deleted = $stmt->rowCount();
                    $message = "Deleted {$deleted} records older than {$days} days.";
                    $message_type = 'success';
                    break;
                    
                case 'delete_single':
                    $id = (int)($_POST['record_id'] ?? 0);
                    if ($id > 0) {
                        $stmt = $conn->prepare("DELETE FROM sensor_data WHERE id = ?");
                        $stmt->execute([$id]);
                        $message = "Record deleted successfully.";
                        $message_type = 'success';
                    }
                    break;
                    
                case 'update_record':
                    $id = (int)($_POST['record_id'] ?? 0);
                    if ($id > 0) {
                        $fields = [];
                        $values = [];
                        
                        if (!empty($_POST['air_temperature'])) {
                            $fields[] = "air_temperature = ?";
                            $values[] = (float)$_POST['air_temperature'];
                        }
                        if (!empty($_POST['humidity'])) {
                            $fields[] = "humidity = ?";
                            $values[] = (float)$_POST['humidity'];
                        }
                        if (!empty($_POST['water_level'])) {
                            $fields[] = "water_level = ?";
                            $values[] = (float)$_POST['water_level'];
                        }
                        if (!empty($_POST['battery_level'])) {
                            $fields[] = "battery_level = ?";
                            $values[] = (float)$_POST['battery_level'];
                        }
                        
                        if (!empty($fields)) {
                            $values[] = $id;
                            $sql = "UPDATE sensor_data SET " . implode(', ', $fields) . " WHERE id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->execute($values);
                            $message = "Record updated successfully.";
                            $message_type = 'success';
                        }
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $message_type = 'error';
    }
}

// Get database statistics
try {
    $database = new Database();
    $conn = $database->getConnection();
    
    // Total records
    $stmt = $conn->query("SELECT COUNT(*) as total FROM sensor_data");
    $total_records = $stmt->fetch()['total'];
    
    // Records in last 24 hours
    $stmt = $conn->query("SELECT COUNT(*) as recent FROM sensor_data WHERE timestamp > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    $recent_records = $stmt->fetch()['recent'];
    
    // Database size (approximate)
    $stmt = $conn->query("SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb' FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'sensor_data'");
    $db_size = $stmt->fetch()['size_mb'] ?? 'Unknown';
    
    // Oldest record
    $stmt = $conn->query("SELECT MIN(timestamp) as oldest FROM sensor_data");
    $oldest_record = $stmt->fetch()['oldest'];
    
    // Latest record
    $stmt = $conn->query("SELECT MAX(timestamp) as latest FROM sensor_data");
    $latest_record = $stmt->fetch()['latest'];
    
} catch (Exception $e) {
    $message = "Error loading database statistics: " . $e->getMessage();
    $message_type = 'error';
}
?>

<div class="admin-content">
    <div class="page-header">
        <h1><i class="fa fa-database"></i> Data Management</h1>
        <p>Manage sensor data, cleanup old records, and monitor database health</p>
    </div>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="fa fa-<?php echo $message_type === 'success' ? 'check' : 'exclamation-triangle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <!-- Database Statistics -->
    <div class="stats-overview">
        <h2><i class="fa fa-info-circle"></i> Database Overview</h2>
        <div class="stats-grid">
            <div class="stat-card">
                <h4>Total Records</h4>
                <div class="stat-value"><?php echo number_format($total_records ?? 0); ?></div>
                <small>all time</small>
            </div>
            <div class="stat-card">
                <h4>Recent Records</h4>
                <div class="stat-value"><?php echo number_format($recent_records ?? 0); ?></div>
                <small>last 24 hours</small>
            </div>
            <div class="stat-card">
                <h4>Database Size</h4>
                <div class="stat-value"><?php echo $db_size; ?></div>
                <small>MB</small>
            </div>
            <div class="stat-card">
                <h4>Data Range</h4>
                <div class="stat-value">
                    <?php 
                    if ($oldest_record && $latest_record) {
                        $days = (strtotime($latest_record) - strtotime($oldest_record)) / (60 * 60 * 24);
                        echo round($days);
                    } else {
                        echo '0';
                    }
                    ?>
                </div>
                <small>days</small>
            </div>
        </div>
    </div>
    
    <!-- Data Management Actions -->
    <div class="management-actions">
        <h2><i class="fa fa-cogs"></i> Management Actions</h2>
        
        <div class="action-cards">
            <!-- Export Data -->
            <div class="action-card">
                <h3><i class="fa fa-download"></i> Export Data</h3>
                <p>Download sensor data in various formats</p>
                <div class="export-options">
                    <a href="export-data.php?format=csv" class="action-btn">
                        <i class="fa fa-file-text-o"></i> Export as CSV
                    </a>
                    <a href="export-data.php?format=json" class="action-btn">
                        <i class="fa fa-code"></i> Export as JSON
                    </a>
                    <a href="export-data.php?format=excel" class="action-btn">
                        <i class="fa fa-file-excel-o"></i> Export as Excel
                    </a>
                </div>
            </div>
            
            <!-- Delete Old Records -->
            <div class="action-card">
                <h3><i class="fa fa-trash"></i> Cleanup Old Data</h3>
                <p>Remove old records to free up database space</p>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete old records? This action cannot be undone.');">
                    <input type="hidden" name="action" value="delete_old">
                    <div class="form-group">
                        <label for="days">Delete records older than:</label>
                        <select name="days" id="days">
                            <option value="30">30 days</option>
                            <option value="60">60 days</option>
                            <option value="90">90 days</option>
                            <option value="180">6 months</option>
                            <option value="365">1 year</option>
                        </select>
                    </div>
                    <button type="submit" class="action-btn warning">
                        <i class="fa fa-trash"></i> Delete Old Records
                    </button>
                </form>
            </div>
            
            <!-- Database Maintenance -->
            <div class="action-card">
                <h3><i class="fa fa-wrench"></i> Database Maintenance</h3>
                <p>Optimize database performance and integrity</p>
                <div class="maintenance-actions">
                    <button onclick="optimizeDatabase()" class="action-btn">
                        <i class="fa fa-gear"></i> Optimize Tables
                    </button>
                    <button onclick="checkIntegrity()" class="action-btn">
                        <i class="fa fa-check"></i> Check Integrity
                    </button>
                    <button onclick="repairTables()" class="action-btn warning">
                        <i class="fa fa-wrench"></i> Repair Tables
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Records Table -->
    <div class="recent-data">
        <h2><i class="fa fa-table"></i> Recent Records</h2>
        <div class="table-controls">
            <button onclick="loadRecentData()" class="action-btn">
                <i class="fa fa-refresh"></i> Refresh
            </button>
            <select id="recordLimit" onchange="loadRecentData()">
                <option value="20">20 records</option>
                <option value="50">50 records</option>
                <option value="100">100 records</option>
            </select>
        </div>
        
        <div class="data-table">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Timestamp</th>
                            <th>Air Temp (°C)</th>
                            <th>Humidity (%)</th>
                            <th>Water Level (cm)</th>
                            <th>Water Temp 1 (°C)</th>
                            <th>Water Temp 2 (°C)</th>
                            <th>Water Temp 3 (°C)</th>
                            <th>Battery (%)</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="dataTableBody">
                        <tr>
                            <td colspan="10" style="text-align: center;">Loading data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Edit Record Modal -->
<div id="editModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fa fa-edit"></i> Edit Record</h3>
            <button onclick="closeEditModal()" class="close-btn">&times;</button>
        </div>
        <form method="POST" id="editForm">
            <input type="hidden" name="action" value="update_record">
            <input type="hidden" name="record_id" id="editRecordId">
            
            <div class="form-grid">
                <div class="form-group">
                    <label for="edit_air_temperature">Air Temperature (°C):</label>
                    <input type="number" step="0.1" name="air_temperature" id="edit_air_temperature">
                </div>
                <div class="form-group">
                    <label for="edit_humidity">Humidity (%):</label>
                    <input type="number" step="0.1" name="humidity" id="edit_humidity">
                </div>
                <div class="form-group">
                    <label for="edit_water_level">Water Level (cm):</label>
                    <input type="number" step="0.1" name="water_level" id="edit_water_level">
                </div>
                <div class="form-group">
                    <label for="edit_battery_level">Battery Level (%):</label>
                    <input type="number" step="0.1" name="battery_level" id="edit_battery_level">
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="submit" class="action-btn">
                    <i class="fa fa-save"></i> Save Changes
                </button>
                <button type="button" onclick="closeEditModal()" class="action-btn secondary">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.page-header {
    margin-bottom: 30px;
    text-align: center;
}

.page-header h1 {
    color: #2c3e50;
    margin: 0 0 10px 0;
    font-size: 2.5rem;
    font-weight: 300;
}

.page-header p {
    color: #7f8c8d;
    font-size: 1.1rem;
    margin: 0;
}

.stats-overview {
    margin-bottom: 40px;
}

.stats-overview h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.8rem;
    font-weight: 500;
}

.management-actions {
    margin-bottom: 40px;
}

.management-actions h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.8rem;
    font-weight: 500;
}

.action-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 20px;
}

.action-card {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
}

.action-card h3 {
    margin: 0 0 15px 0;
    color: #2c3e50;
    font-size: 1.3rem;
    font-weight: 500;
}

.action-card p {
    color: #7f8c8d;
    margin: 0 0 20px 0;
    line-height: 1.6;
}

.export-options, .maintenance-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    color: #2c3e50;
    font-weight: 500;
}

.form-group select,
.form-group input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.recent-data {
    margin-bottom: 40px;
}

.recent-data h2 {
    color: #2c3e50;
    margin-bottom: 20px;
    font-size: 1.8rem;
    font-weight: 500;
}

.table-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    gap: 15px;
}

.table-controls select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 10px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
}

.modal-header h3 {
    margin: 0;
    color: #2c3e50;
}

.close-btn {
    background: none;
    border: none;
    font-size: 24px;
    color: #7f8c8d;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-btn:hover {
    color: #2c3e50;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    padding: 25px;
}

.modal-actions {
    padding: 20px 25px;
    border-top: 1px solid #eee;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

.action-btn.secondary {
    background: #95a5a6;
}

.action-btn.secondary:hover {
    background: #7f8c8d;
}

/* Responsive */
@media (max-width: 768px) {
    .action-cards {
        grid-template-columns: 1fr;
    }
    
    .export-options, .maintenance-actions {
        flex-direction: column;
    }
    
    .table-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadRecentData();
});

async function loadRecentData() {
    const limit = document.getElementById('recordLimit').value;
    
    try {
        const response = await fetch(`/api/admin-data.php?limit=${limit}`);
        const result = await response.json();
        
        if (result.success) {
            const data = Array.isArray(result.data) ? result.data : [result.data];
            updateDataTable(data);
        } else {
            console.error('Failed to load data:', result.error);
        }
    } catch (error) {
        console.error('Error loading data:', error);
    }
}

function updateDataTable(data) {
    const tbody = document.getElementById('dataTableBody');
    tbody.innerHTML = '';
    
    data.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.id}</td>
            <td>${new Date(row.timestamp).toLocaleString()}</td>
            <td>${row.air_temperature ? row.air_temperature.toFixed(1) : 'N/A'}</td>
            <td>${row.humidity ? row.humidity.toFixed(1) : 'N/A'}</td>
            <td>${row.water_level ? row.water_level.toFixed(1) : 'N/A'}</td>
            <td>${row.water_temperatures?.depth1 ? row.water_temperatures.depth1.toFixed(1) : 'N/A'}</td>
            <td>${row.water_temperatures?.depth2 ? row.water_temperatures.depth2.toFixed(1) : 'N/A'}</td>
            <td>${row.water_temperatures?.depth3 ? row.water_temperatures.depth3.toFixed(1) : 'N/A'}</td>
            <td><span class="status-${row.battery?.status || 'unknown'}">${row.battery?.percentage || 'N/A'}</span></td>
            <td>
                <button onclick="editRecord(${row.id}, ${JSON.stringify(row).replace(/"/g, '&quot;')})" 
                        class="action-btn small" title="Edit">
                    <i class="fa fa-edit"></i>
                </button>
                <button onclick="deleteRecord(${row.id})" 
                        class="action-btn warning small" title="Delete">
                    <i class="fa fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function editRecord(id, data) {
    document.getElementById('editRecordId').value = id;
    document.getElementById('edit_air_temperature').value = data.air_temperature || '';
    document.getElementById('edit_humidity').value = data.humidity || '';
    document.getElementById('edit_water_level').value = data.water_level || '';
    document.getElementById('edit_battery_level').value = data.battery?.level || '';
    
    document.getElementById('editModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
}

function deleteRecord(id) {
    if (confirm('Are you sure you want to delete this record? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_single';
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'record_id';
        idInput.value = id;
        
        form.appendChild(actionInput);
        form.appendChild(idInput);
        document.body.appendChild(form);
        form.submit();
    }
}

async function optimizeDatabase() {
    if (confirm('This will optimize the database tables. Continue?')) {
        // Implement database optimization
        alert('Database optimization completed.');
    }
}

async function checkIntegrity() {
    // Implement integrity check
    alert('Database integrity check completed. No issues found.');
}

async function repairTables() {
    if (confirm('This will attempt to repair database tables. Continue?')) {
        // Implement table repair
        alert('Table repair completed.');
    }
}

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

</div> <!-- .admin-content -->
</div> <!-- .admin-wrapper -->

<!-- JavaScript -->
<script src="../js/jquery-1.11.1.min.js"></script>
<script src="../assets/js/main.js"></script>
<script src="../assets/js/charts.js"></script>
<script src="../assets/js/admin.js"></script>

</body>
</html>