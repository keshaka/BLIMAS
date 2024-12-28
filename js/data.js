async function fetchData() {
    console.log("fetchData function started."); // Log start of function
  
    try {
      const response = await fetch('sensor_data.txt?nocache=' + new Date().getTime());
      console.log("Fetch response received:", response.status); // Log HTTP status
  
      if (!response.ok) {
        console.error('Failed to fetch sensor_data.txt:', response.statusText);
        return;
      }
  
      const data = await response.text();
      console.log("Raw data from file:", data); // Log file content
  
      // Split and parse the data
      const lines = data.trim().split("\n");
      if (lines.length === 0) {
        console.error("No data found in sensor_data.txt.");
        return;
      }
  
      const latestData = lines[lines.length - 1];
      console.log("Latest data line:", latestData); // Log the last line
  
      // Regular expression to extract sensor values
      const match = latestData.match(/Temp1: ([\d.]+), Temp2: ([\d.]+), Temp3: ([\d.]+), Humidity: ([\d.]+), TempDHT: ([\d.]+), Distance: ([\d.]+)/);
      if (!match) {
        console.error("Data format is incorrect:", latestData);
        return;
      }
  
      // Extract values
      const [_, temp1, temp2, temp3, humidity, tempDHT, distance] = match;
  
      // Update HTML elements
      document.getElementById('temp1').innerText = temp1 + ' °C';
      document.getElementById('temp2').innerText = temp2 + ' °C';
      document.getElementById('temp3').innerText = temp3 + ' °C';
      document.getElementById('humidity').innerText = humidity + ' %';
      document.getElementById('tempDHT').innerText = tempDHT + ' °C';
      document.getElementById('distance').innerText = distance + ' cm';
  
      console.log("Data updated on the website."); // Log success
    } catch (error) {
      console.error("Error in fetchData:", error); // Log errors
    }
  }
  
  // Refresh data every 5 seconds
  setInterval(fetchData, 5000);
  window.onload = fetchData;