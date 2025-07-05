// Update these paths if needed
const API_BASE = '/api/'; // or './api/' or '../api/' depending on your structure

// In the loadHistoricalData method, use:
const response = await fetch(`${API_BASE}get_historical_data.php?type=${type}&hours=${hours}`);