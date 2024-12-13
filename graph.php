<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-Time Line Graph</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Ensure the canvas is responsive and not too big */
        .container {
            max-width: 600px; /* Maximum width for the graph */
            margin: 0 auto; /* Center the graph */
            background-color: #fff; /* White background for the container */
            padding: 20px; /* Optional: Add some padding around the chart */
            border-radius: 10px; /* Optional: Rounded corners */
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1); /* Optional: Subtle shadow */
        }
        canvas {
            width: 100%; /* Responsive width */
            height: 300px; /* Fixed height */
        }
    </style>
</head>
<body>
    <div class="container">
        <canvas id="myChart"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('myChart').getContext('2d');
        let myChart;

        function fetchData() {
            fetch('fetch_data.php') // Calls PHP file to get data
                .then(response => response.json())
                .then(data => {
                    const productNames = data.map(item => item.product_name);
                    const quantities = data.map(item => item.quantity_in_stock);

                    // If the chart is not initialized, create it
                    if (!myChart) {
                        myChart = new Chart(ctx, {
                            type: 'line', // Line graph
                            data: {
                                labels: productNames, // X-axis labels
                                datasets: [{
                                    label: 'Quantity in Stock',
                                    data: quantities, // Y-axis data
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)', // Background color for the area under the line
                                    borderColor: 'rgba(75, 192, 192, 1)', // Line color
                                    borderWidth: 2,
                                    fill: false, // No background fill, only the line
                                    tension: 0.1 // Smooth the line
                                }]
                            },
                            options: {
                                scales: {
                                    y: {
                                        beginAtZero: true // Start Y-axis at 0
                                    },
                                    x: {
                                        grid: {
                                            color: '#e0e0e0' // Light gray grid lines
                                        }
                                    },
                                    y: {
                                        grid: {
                                            color: '#e0e0e0' // Light gray grid lines
                                        }
                                    }
                                },
                                responsive: true,
                                maintainAspectRatio: false, // Allows better control of height
                                plugins: {
                                    legend: {
                                        labels: {
                                            color: '#333' // Legend text color
                                        }
                                    },
                                    tooltip: {
                                        titleColor: '#333', // Tooltip title color
                                        bodyColor: '#333' // Tooltip body color
                                    }
                                }
                            }
                        });
                    } else {
                        // Update existing chart data
                        myChart.data.labels = productNames;
                        myChart.data.datasets[0].data = quantities;
                        myChart.update();
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        // Fetch data every 5 seconds
        setInterval(fetchData, 5000);
        fetchData(); // Initial fetch
    </script>
</body>
</html>
