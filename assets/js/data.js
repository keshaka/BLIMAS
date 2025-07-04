async function fetchData() {
    console.log("fetchData function started."); // Debugging log
  
    try {
      const response = await fetch('sensor_data.txt?nocache=' + new Date().getTime());
      if (!response.ok) {
        console.error("Failed to fetch sensor_data.txt:", response.statusText);
        return;
      }
  
      const data = await response.text();
      console.log("Raw data fetched:", data); // Debugging log
  
      const lines = data.trim().split("\n");
      if (lines.length === 0) {
        console.error("No data in sensor_data.txt.");
        return;
      }
  
      const latestData = lines[lines.length - 1];
      console.log("Latest data line:", latestData); // Debugging log
  
      // Update the regular expression to handle negative numbers
      const match = latestData.match(/Temp1: (-?[\d.]+), Temp2: (-?[\d.]+), Temp3: (-?[\d.]+), Humidity: (-?[\d.]+), TempDHT: (-?[\d.]+), Distance: (-?[\d.]+)/);
      if (!match) {
        console.error("Data format is incorrect or incomplete:", latestData);
        return;
      }
  
      const [_, temp1, temp2, temp3, humidity, tempDHT, distance] = match;
  
      // Update HTML elements
      document.getElementById('temp1').innerText = temp1 + ' °C';
      document.getElementById('temp2').innerText = temp2 + ' °C';
      document.getElementById('temp3').innerText = temp3 + ' °C';
      document.getElementById('humhum').innerText = humidity + ' %';
      document.getElementById('tempDHT').innerText = tempDHT + ' °C';
      document.getElementById('distance').innerText = distance + ' cm';
  
      console.log("Data updated on the website."); // Debugging log
    } catch (error) {
      console.error("Error in fetchData:", error);
    }
  }
  
  // Refresh data every 5 seconds
  setInterval(fetchData, 5000);
  window.onload = fetchData;
  