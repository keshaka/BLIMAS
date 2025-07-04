document.addEventListener("DOMContentLoaded", function () {
    const ctx = document.getElementById('lineChart').getContext('2d');
  
    const lineChart = new Chart(ctx, {
      type: 'line', // Specifies the type of chart as a Line Chart
      data: {
        labels: ['January', 'February', 'March', 'April', 'May', 'June'], // X-axis labels
        datasets: [
          {
            label: 'Monthly Sales',
            data: [10, 25, 14, 32, 20, 45], // Y-axis data points
            borderColor: 'rgba(75, 192, 192, 1)', // Line color
            backgroundColor: 'rgba(75, 192, 192, 0.2)', // Background transparency
            borderWidth: 2,
            fill: true,
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          x: {
            beginAtZero: false,
          },
          y: {
            beginAtZero: true,
          }
        }
      }
    });
  });
  