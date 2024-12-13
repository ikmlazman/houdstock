<?php
session_start();
require_once 'connect.php'; // Include the database connection file

// Initialize error message
$errorMessage = "";

// Check if the form is submitted
if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role']; // Get selected role from form

    // Initialize the table name based on the selected role
    $table = "";
    if ($role === 'admin') {
        $table = 'admin'; // Adjust table name for admin
    } elseif ($role === 'employee') {
        $table = 'employee'; // Adjust table name for employee
    } elseif ($role === 'supplier') {
        $table = 'supplier'; // Adjust table name for supplier
    } else {
        $errorMessage = "Invalid role selected.";
    }

    if ($table) {
        // Use prepared statements to prevent SQL injection
        $stmt = $con->prepare("SELECT * FROM `$table` WHERE username=? AND password=?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        // If user exists
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            // Set session variables
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            $_SESSION['logged_in'] = true;

            // If the user is a supplier, set supplier_id in the session
            if ($role === 'supplier') {
                $_SESSION['supplier_id'] = $row['supplier_id']; // Assuming the supplier table has a supplier_id column
            }

            // Redirect based on role
            if ($role === 'admin') {
                header('Location: admin_dashboard.php');
            } elseif ($role === 'employee') {
                header('Location: employee_dashboard.php');
            } elseif ($role === 'supplier') {
                header('Location: supplier_dashboard.php');
            }
            exit();
        } else {
            $errorMessage = "Invalid Username or Password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Form</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
        /* Keyframes for fade and slide-in animation */
        @keyframes fadeInSlideIn {
            0% {
                transform: translateY(-30px); /* Start from slightly above */
                opacity: 0; /* Fully transparent */
            }
            100% {
                transform: translateY(0); /* Move to original position */
                opacity: 1; /* Fully visible */
            }
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f9;
            flex-direction: column;
            background: url('coffee.jpeg') no-repeat center center/cover;
        }
        .navbar {
            width: 100%;
            background-color: #222;
            display: flex;
            justify-content: center;
            padding: 10px 0;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        .navbar a {
            color: #fff;
            padding: 14px 20px;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .navbar a:hover {
            background-color: #444;
        }
        .navbar a.logout {
            margin-left: auto; /* Align logout to the right */
        }
        .container {
            animation: fadeInSlideIn 2.0s ease forwards; /* Apply the fade and slide-in animation to the container */
            max-width: 400px;
            width: 100%;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-top: 80px;
        }
        .container h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
            animation: fadeInSlideIn 1.0s ease forwards; /* Apply the fade and slide-in animation to the header */
        }
        .input-box {
            position: relative;
            margin-bottom: 15px;
        }
        .input-box input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            outline: none;
            transition: border-color 0.3s;
        }
        .input-box input:focus {
            border-color: #007bff;
        }
        .role-selection {
            margin-bottom: 20px;
            text-align: center;
        }
        .role-selection label {
            margin-right: 10px;
            font-size: 14px;
            color: #555;
        }
        .btn {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            border: none;
            color: #fff;
            font-size: 18px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .alert {
            width: 100%;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="dashboard.php">Dashboard</a>
        <a href="contact.php">Contact</a>
        <a href="aboutus.php">About Us</a>
    </div>

    <div class="container">
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <h1>Login</h1>
        <form action="login1.php" method="POST">
            <div class="input-box">
                <input type="text" placeholder="Username" name="username" required>
            </div>
            <div class="input-box">
                <input type="password" placeholder="Password" name="password" required>
            </div>
            
            <div class="role-selection">
                <label><input type="radio" name="role" value="admin" required> Admin</label>
                <label><input type="radio" name="role" value="employee" required> Employee</label>
                <label><input type="radio" name="role" value="supplier" required> Supplier</label>
            </div>

            <button type="submit" class="btn" name="submit">Login</button>
            
            <!-- Add this link at the bottom of your login form -->
            <p style="text-align: center;">
    <a href="forgot_password.php" style="display: inline;">Forgot Password?</a>
</p>

        </form>
    </div>
</body>
</html>
