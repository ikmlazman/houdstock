<?php
session_start();
require_once 'connect.php'; // Database connection

// Ensure the supplier is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'supplier') {
    header('Location: login1.php');
    exit();
}

// Initialize variables
$success = '';
$error = '';
$supplier_id = $_SESSION['supplier_id']; // Assuming supplier ID is stored in session
$supplier_name = '';
$username = '';
$password = '';
$contact_info = '';

// Fetch supplier details from the database
$sql = "SELECT * FROM supplier WHERE supplier_id = '$supplier_id'";
$result = mysqli_query($con, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $supplier = mysqli_fetch_assoc($result);
    $supplier_name = $supplier['supplier_name'];
    $username = $supplier['username'];
    $contact_info = $supplier['contact_info'];
} else {
    $error = "Failed to load supplier details.";
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $supplier_name = mysqli_real_escape_string($con, $_POST['supplier_name']);
    $username = mysqli_real_escape_string($con, $_POST['username']);
    $password = !empty($_POST['password']) ? mysqli_real_escape_string($con, $_POST['password']) : null; // Optional password update
    $contact_info = mysqli_real_escape_string($con, $_POST['contact_info']);

    // Validate required fields
    if (empty($supplier_name) || empty($username)) {
        $error = "Supplier name and username are required.";
    } else {
        // Update query
        $update_query = "UPDATE supplier SET 
                            supplier_name = '$supplier_name', 
                            username = '$username', 
                            contact_info = '$contact_info'";

        // Only update password if provided
        if ($password) {
            $update_query .= ", password = '$password'";
        }

        $update_query .= " WHERE supplier_id = '$supplier_id'";

        if (mysqli_query($con, $update_query)) {
            // Redirect to the supplier dashboard after successful update
            header('Location: supplier_dashboard.php');
            exit(); // Ensure no further code is executed
        } else {
            $error = "Error updating profile: " . mysqli_error($con);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <a href="supplier_dashboard.php" class="btn-home">Home</a>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f2f2f2;
        }
        .container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #333;
            text-align: center;
        }
        input[type="text"], input[type="password"], input[type="email"], textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: green;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: darkgreen;
        }
        .message {
            text-align: center;
            margin: 10px 0;
        }
        .message.success {
            color: green;
        }
        .message.error {
            color: red;
        }
        /* Stylish Home Button */
        .btn-home {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transition: background-color 0.3s, box-shadow 0.3s;
        }
        .btn-home:hover {
            background-color: #45a049;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.3);
        }
        .btn-home:active {
            background-color: #3e8e41;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            transform: translateY(2px);
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Edit Profile</h1>

    <!-- Display success/error messages -->
    <?php if ($success): ?>
        <p class="message success"><?php echo $success; ?></p>
    <?php endif; ?>
    <?php if ($error): ?>
        <p class="message error"><?php echo $error; ?></p>
    <?php endif; ?>

    <!-- Profile Edit Form -->
    <form action="editprof_supplier.php" method="POST">
        <label for="supplier_name">Supplier Name:</label>
        <input type="text" id="supplier_name" name="supplier_name" value="<?php echo $supplier_name; ?>" required>

        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo $username; ?>" required>

        <label for="password">New Password (leave blank to keep current):</label>
        <input type="password" id="password" name="password">

        <label for="contact_info">Contact Info:</label>
        <textarea id="contact_info" name="contact_info"><?php echo $contact_info; ?></textarea>

        <button type="submit" name="update">Update Profile</button>
    </form>
</div>

</body>
</html>
