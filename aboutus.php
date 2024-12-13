<?php 
require_once 'connect.php';?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>About Us</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
/* Reset Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Poppins', sans-serif;
    background: white;
    color: #fff;
    display: flex;
    flex-direction: column;  /* Ensure everything is stacked vertically */
}


        /* Navigation Bar */
        nav {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            background: #2c3e50;
            color: #fff;
            display: flex;
            flex-direction: column;
            padding: 20px;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.2);
        }

        nav ul {
            list-style: none;
            padding: 0;
        }

        nav ul li {
            margin: 20px 0;
        }

        nav ul li a {
            text-decoration: none;
            color: #fff;
            font-size: 1rem;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        nav ul li a:hover {
            background: #34495e;
            transform: translateX(5px);
        }

        nav ul li a span.icon {
            margin-right: 10px;
        }

/* Main Container */
.container {
    margin-left: 250px;  /* Adjusted margin to match nav width */
    padding: 20px;
    width: 100%;
    margin-top: 0; /* Ensure no space above the container */
}

/* Developer Photo (Logo) */
.developer-photo {
    display: flex;
    justify-content: center;  /* Horizontally center */
    align-items: center;      /* Vertically center */
    width: 100%;
    height: 100%;
    margin-bottom: 0;  /* Remove any bottom margin */
}

.developer-photo img {
    border-radius: 50%;
    width: 150px;            /* Fixed image size */
    height: 150px;           /* Fixed image size */
    object-fit: cover;
    border: 4px solid #ffffff;
    box-shadow: 0 2px 10px rgba(255, 255, 255, 0.1);
}

/* Info Section */
.info {
    margin-top: 20px;
    font-size: 17px;
    line-height: 1.6;
    color: black;
}

.info h2 {
    font-size: 28px;
    margin-bottom: 10px;
    color: black;
}

.info p {
    margin-bottom: 10px;
}

/* Responsive Design */
@media (max-width: 768px) {
    nav {
        width: 100%;
        height: auto;
        flex-direction: row;
        justify-content: space-around;
    }

    .container {
        margin-left: 0;
        width: 100%;
    }

    /* Center the developer photo on smaller screens */
    .developer-photo {
        display: flex;
        justify-content: center;  /* Horizontally center */
        align-items: center;      /* Vertically center */
        width: 100%;
        height: 100%;
        margin-bottom: 0;  /* Remove any bottom margin */
    }

    /* Maintain image size */
    .developer-photo img {
        width: 150px;
        height: 150px;
    }
}


    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <ul>
            <li><a href="index.php"><span class="icon">🏠</span> Home</a></li>
            <li><a href="dashboard.php"><span class="icon">📊</span> Dashboard</a></li>
            <li><a href="login1.php"><span class="icon">🔑</span> Login</a></li>
            <li><a href="contact.php"><span class="icon">📞</span> Contact</a></li>
            <li><a href="aboutus.php"><span class="icon">ℹ️</span> About</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <header class="header">
            <h1>About Us</h1>
        </header>

        <div class="developer-photo">
            <img src="telegram.png" alt="Developer Photo">
        </div>

        <div class="info">
            <h2>About the Project</h2>
            <p>This project is the <strong>Web-Based Inventory Management System Integrating with Telegram Notifications</strong>, designed specifically for cafes and suppliers.
            It addresses inefficiencies in traditional inventory management by providing real-time notifications, simplifying inventory tracking, and ensuring better management practices for both café owners and suppliers.
            The system incorporates User Acceptance Testing (UAT) and User Experience Testing (UET) to ensure it meets the needs of café owners, staff, and suppliers effectively.
        </div>
    </div>
</body>
</html>
