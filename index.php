<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HoudStock - Inventory Manager</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
    /* General Styles */
body {
    margin: 0;
    font-family: 'Orbitron', sans-serif;
    background: linear-gradient(to right, #1f1c2c, #928DAB);
    color: #fff;
    padding: 0;
    overflow-x: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    background-image: url('pixelcut-export.jpeg');
    background-size: cover;
    background-repeat: no-repeat;
    background-position: center;
    background-attachment: fixed;
}

/* Navigation Bar */
nav {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background: #2c3e50;
    box-shadow: 0 0 15px rgba(245, 245, 245, 0.2);
    padding: 10px 0;
    z-index: 100;
}

nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
    text-align: center;
    display: flex;
    justify-content: center;
}

nav ul li {
    margin: 0 15px;
}

nav ul li a {
    text-decoration: none;
    color: whitesmoke;
    font-size: 1rem;
    font-weight: bold;
    letter-spacing: 1px;
    position: relative;
    padding: 8px 12px;
    border: 2px solid transparent;
    border-radius: 5px;
    transition: color 0.3s ease, transform 0.3s ease;
}

nav ul li a:hover {
    color: black;
    border-color: whitesmoke;
    transform: scale(1.1);
}

/* Main Content */
.main-content {
    margin-top: 80px;
    padding: 20px;
    text-align: center;
    animation: fadeIn 1.5s ease-in-out;
}

.main-content h1 {
    font-size: 2.5rem;
    font-weight: bold;
    font-style: italic;
    color: #000;
}

.main-content p {
    font-size: 1rem;
    color: #000;
    margin: 10px 0;
}

.btn-primary {
    background: linear-gradient(90deg, #000, #333);
    color: #fff;
    border: 2px solid rgba(255, 255, 255, 0.6);
    padding: 10px 20px;
    font-size: 1rem;
    border-radius: 8px;
    letter-spacing: 2px;
    text-transform: uppercase;
    box-shadow: 0 0 10px rgba(255, 255, 255, 0.6), 0 0 20px rgba(255, 255, 255, 0.4);
    transition: all 0.3s ease, box-shadow 0.3s ease;
    margin-top: 20px;
}

.btn-primary:hover {
    background: linear-gradient(90deg, #333, #000);
    border-color: #00ff00;
    color: #000;
    transform: scale(1.1);
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.8), 0 0 30px rgba(255, 255, 255, 0.6);
    cursor: pointer;
}

/* Floating Dashboard */
.floating-dashboard {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90%;
    max-width: 400px;
    background-color: #f9f9f9;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    padding: 20px;
    border-radius: 10px;
    display: none;
    z-index: 1000;
}

.floating-dashboard h2 {
    margin-top: 0;
}

.floating-dashboard button {
    margin-top: 20px;
    padding: 10px 15px;
    background-color: #ff4d4d;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.floating-dashboard button:hover {
    background-color: #e60000;
}

/* Overlay */
.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    z-index: 999;
}

.modal {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 1000;
    width: 100%;
    height: 80%;
    max-width: 600px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    padding: 20px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-close {
    cursor: pointer;
    font-size: 20px;
    font-weight: bold;
    color: #333;
    border: none;
    background: none;
}

.modal-close:hover {
    color: red;
}

/* Animation */
@keyframes fadeIn {
    0% {
        opacity: 0;
        transform: translateY(50px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Design */
@media (max-width: 768px) {
    nav ul {
        flex-direction: column;
        padding: 10px 0;
    }

    nav ul li {
        margin: 10px 0;
    }

    .main-content h1 {
        font-size: 1.8rem;
    }

    .main-content p {
        font-size: 0.9rem;
    }

    .btn-primary {
        padding: 10px 15px;
        font-size: 0.9rem;
    }
}

@media (min-width: 769px) {
    .main-content {
        margin-top: 100px;
        padding: 40px;
    }

    .main-content h1 {
        font-size: 3rem;
    }

    .btn-primary {
        padding: 12px 30px;
        font-size: 1.2rem;
    }
}

       
    </style>
</head>
<body>

    <!-- Navigation Bar -->
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="login1.php">Login</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="aboutus.php">About</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Welcome to HoudStock</h1>
        <br>
        <p>"Your futuristic inventory manager with real-time notifications and advanced features."</p>
        <button id="startButton" class="btn-primary">Dashboard</button>
    </div>
    <br>

     <!-- Overlay -->
     <div id="overlay" class="overlay"></div>

<!-- Modal -->
<div id="modal" class="modal">
    <div class="modal-header">
        <h2>Real-Time Line Graph</h2>
        <button id="closeModal" class="modal-close">&times;</button>
    </div>
    <div class="modal-body">
        <canvas id="myChart"></canvas>
    </div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const overlay = document.getElementById('overlay');
        const modal = document.getElementById('modal');
        const startButton = document.getElementById('startButton');
        const closeModal = document.getElementById('closeModal');

        // Show modal function
        startButton.addEventListener('click', () => {
            overlay.style.display = 'block';
            modal.style.display = 'block';
        });

        // Close modal function
        closeModal.addEventListener('click', () => {
            overlay.style.display = 'none';
            modal.style.display = 'none';
        });

        // Close modal if overlay is clicked
        overlay.addEventListener('click', () => {
            overlay.style.display = 'none';
            modal.style.display = 'none';
        });

        // Chart.js logic
        const ctx = document.getElementById('myChart').getContext('2d');
        let myChart;

        function fetchData() {
            fetch('fetch_data.php')
                .then(response => response.json())
                .then(data => {
                    const productNames = data.map(item => item.product_name);
                    const quantities = data.map(item => item.quantity_in_stock);

                    if (!myChart) {
                        myChart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: productNames,
                                datasets: [{
                                    label: 'Quantity in Stock',
                                    data: quantities,
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    borderWidth: 2
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false
                            }
                        });
                    } else {
                        myChart.data.labels = productNames;
                        myChart.data.datasets[0].data = quantities;
                        myChart.update();
                    }
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        setInterval(fetchData, 5000);
        fetchData();
    </script>
</body>
</html>
