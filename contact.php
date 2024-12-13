<?php 
require_once 'connect.php';?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Contact - SwiftStock</title>
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
            background: #f4f4f4;
            color: #121212;
            display: flex;
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
            margin-left: 270px;
            padding: 20px;
            width: calc(100% - 270px);
        }

        .header {
            text-align: center;
            padding: 20px;
            background: #2c3e50;
            color: #fff;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .developer-photo {
            margin: 20px auto;
            text-align: center;
        }

        .developer-photo img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 3px solid #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background: #34495e;
            color: #fff;
        }

        td {
            background: #fff;
            color: #121212;
        }

        a {
            color: #2c3e50;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
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
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <ul>
        <li><a href="index.php"><span class="icon">🏠</span> Home</a></li>
        <li><a href="index.php"><span class="icon">📊</span> Dashboard</a></li>
            <li><a href="login1.php">
                <span class="icon">🔑</span>
                <span class="text">Login</span>
            </a></li>
            <li><a href="contact.php">
                <span class="icon">📞</span>
                <span class="text">Contact</span>
            </a></li>
            <li><a href="aboutus.php">
                <span class="icon">ℹ️</span>
                <span class="text">About</span>
            </a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <header class="header">
            <h1>Contact the Developer</h1>
        </header>

        <!-- Developer Photo -->
        <div class="developer-photo">
            <img src="gambaq.jpg" alt="Developer Photo">
        </div>

        <!-- Contact Information -->
        <table>
            <thead>
                <tr>
                    <th>Occupation</th>
                    <th>Name</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Full-Stack Developer</td>
                    <td>Nur Ahmad Ikmal Bin Azman</td>
                </tr>
            </tbody>
        </table>

        <table>
            <thead>
                <tr>
                    <th>Email</th>
                    <th>Phone Number</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href="mailto:azmanikmal@gmail.com">azmanikmal@gmail.com</a></td>
                    <td><a href="tel:+60179720538">+601-79720538</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
