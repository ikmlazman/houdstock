<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header('Location: login1.php');
    exit();
}

require_once 'connect.php'; // Connect to your database

// Initialize variables for handling form data and messages
$success = '';
$error = '';
$supplier_name = '';
$username = '';
$password = '';
$contact_info = '';
$supplier_id = '';

// Handle the CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        // Add new supplier
        $supplier_name = mysqli_real_escape_string($con, $_POST['supplier_name']);
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $contact_info = mysqli_real_escape_string($con, $_POST['contact_info']);

        if (empty($supplier_name) || empty($username) || empty($password)) {
            $error = "All fields are required.";
        } else {
            $sql = "INSERT INTO supplier (username, password, supplier_name, contact_info) 
                    VALUES ('$username', '$password', '$supplier_name', '$contact_info')";
            if (mysqli_query($con, $sql)) {
                $success = "New supplier added successfully!";
                // Clear the form fields
                $supplier_name = $username = $password = $contact_info = '';
            } else {
                $error = "Error: " . mysqli_error($con);
            }
        }
    } elseif (isset($_POST['edit'])) {
        // Update existing supplier
        $supplier_id = mysqli_real_escape_string($con, $_POST['supplier_id']);
        $supplier_name = mysqli_real_escape_string($con, $_POST['supplier_name']);
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $contact_info = mysqli_real_escape_string($con, $_POST['contact_info']);

        $sql = "UPDATE supplier SET username='$username', password='$password', supplier_name='$supplier_name', 
                contact_info='$contact_info' 
                WHERE supplier_id='$supplier_id'";
        if (mysqli_query($con, $sql)) {
            $success = "Supplier updated successfully!";
            // Clear the form fields
            $supplier_name = $username = $password = $contact_info = '';
            $supplier_id = '';
        } else {
            $error = "Error: " . mysqli_error($con);
        }
    }
} elseif (isset($_GET['delete'])) {
    // Delete supplier
    $supplier_id = mysqli_real_escape_string($con, $_GET['delete']);
    $sql = "DELETE FROM supplier WHERE supplier_id='$supplier_id'";
    if (mysqli_query($con, $sql)) {
        header('Location: add_supplier.php'); // Refresh the page to reflect changes
        exit();
    } else {
        $error = "Error deleting record: " . mysqli_error($con);
    }
} elseif (isset($_GET['edit'])) {
    // Fetch supplier details for editing
    $supplier_id = mysqli_real_escape_string($con, $_GET['edit']);
    $sql = "SELECT * FROM supplier WHERE supplier_id='$supplier_id'";
    $result = mysqli_query($con, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $supplier_name = $row['supplier_name'];
        $username = $row['username'];
        $password = $row['password'];
        $contact_info = $row['contact_info'];
    }
}

// Fetch all suppliers from the database for display
$sql = "SELECT * FROM supplier";
$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Suppliers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f2f2f2;
        }
        h1, h2 {
            color: #333;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
        .btn {
            padding: 5px 10px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .btn-success {
            background-color: green;
        }
        .btn-danger {
            background-color: red;
        }

        .btn-back {
            color: #fff;
            background-color: #6c757d;
            border-color: #6c757d;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            display: inline-block;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }

        .btn-back:hover {
            background-color: #5a6268;
            border-color: #545b62;
            margin-left: auto;
            margin-right: auto;
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border-radius: 8px;
        }

        .form-container input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-container button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<a href="admin_dashboard.php" class="btn-back">Back</a>

<h1>Manage Suppliers</h1>

<!-- Display success/error messages -->
<?php if ($success) echo "<p style='color: green; text-align: center;'>$success</p>"; ?>
<?php if ($error) echo "<p style='color: red; text-align: center;'>$error</p>"; ?>

<!-- Form to add/edit supplier -->
<div class="form-container">
    <h2><?php echo $supplier_id ? 'Edit Supplier' : 'Add New Supplier'; ?></h2>
    <form action="add_supplier.php" method="POST">
        <input type="hidden" name="supplier_id" value="<?php echo $supplier_id; ?>">
        <input type="text" name="supplier_name" placeholder="Supplier Name" value="<?php echo $supplier_name; ?>" required>
        <input type="text" name="username" placeholder="Username" value="<?php echo $username; ?>" required>
        <input type="password" name="password" placeholder="Password" value="<?php echo $supplier_id ? '' : ''; ?>" required>
        <input type="text" name="contact_info" placeholder="Contact Info" value="<?php echo $contact_info; ?>">
        <button type="submit" name="<?php echo $supplier_id ? 'edit' : 'add'; ?>" class="btn btn-success">
            <?php echo $supplier_id ? 'Update Supplier' : 'Add Supplier'; ?>
        </button>
    </form>
</div>

<!-- Supplier List -->
<h2>Supplier List</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Supplier Name</th>
            <th>Username</th>
            <th>Contact Info</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $row['supplier_id']; ?></td>
                <td><?php echo $row['supplier_name']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['contact_info']; ?></td>
                <td>
                    <a href="add_supplier.php?edit=<?php echo $row['supplier_id']; ?>" class="btn btn-success">Edit</a>
                    <a href="add_supplier.php?delete=<?php echo $row['supplier_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this supplier?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
