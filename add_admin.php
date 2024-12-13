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
$username = '';
$email = '';
$admin_id = '';

// Handle the CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        // Add new admin
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $email = mysqli_real_escape_string($con, $_POST['email']);

        if (empty($username) || empty($password) || empty($email)) {
            $error = "All fields are required.";
        } else {
            $sql = "INSERT INTO admin (username, password, email) VALUES ('$username', '$password', '$email')";
            if (mysqli_query($con, $sql)) {
                $success = "New admin added successfully!";
                // Clear the form fields
                $username = $email = '';
            } else {
                $error = "Error: " . mysqli_error($con);
            }
        }
    } elseif (isset($_POST['edit'])) {
        // Update existing admin
        $admin_id = mysqli_real_escape_string($con, $_POST['admin_id']);
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $email = mysqli_real_escape_string($con, $_POST['email']);

        $sql = "UPDATE admin SET username='$username', password='$password', email='$email' WHERE admin_id='$admin_id'";
        if (mysqli_query($con, $sql)) {
            $success = "Admin updated successfully!";
            // Clear the form fields
            $username = $email = '';
            $admin_id = '';
        } else {
            $error = "Error: " . mysqli_error($con);
        }
    }
} elseif (isset($_GET['delete'])) {
    // Delete admin
    $admin_id = mysqli_real_escape_string($con, $_GET['delete']);
    $sql = "DELETE FROM admin WHERE admin_id='$admin_id'";
    if (mysqli_query($con, $sql)) {
        header('Location: add_admin.php'); // Refresh the page to reflect changes
        exit();
    } else {
        $error = "Error deleting record: " . mysqli_error($con);
    }
} elseif (isset($_GET['edit'])) {
    // Fetch admin details for editing
    $admin_id = mysqli_real_escape_string($con, $_GET['edit']);
    $sql = "SELECT * FROM admin WHERE admin_id='$admin_id'";
    $result = mysqli_query($con, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $username = $row['username'];
        $email = $row['email'];
    }
}

// Fetch all admins from the database for display
$sql = "SELECT * FROM admin";
$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Admins</title>
    <style>
        body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 20px;
    background-color: #f2f2f2;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
}

h1, h2 {
    color: #333;
}

.form-container {
    width: 100%;
    max-width: 600px; /* Adjust the max-width as needed */
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

form input, form button {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 4px;
}

form button {
    background-color: #28a745;
    border: none;
    color: white;
    font-size: 16px;
}

form button:hover {
    background-color: #218838;
}

form input[type="password"] {
    width: 100%;
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
}

.btn-back:hover {
    background-color: #5a6268;
    border-color: #545b62;
}

    
    </style>
</head>
<body>


<a href="admin_dashboard.php" class="btn-back">Back</a>

<h1>Manage Admins</h1>



<!-- Display success/error messages -->
<?php if ($success) echo "<p style='color: green;'>$success</p>"; ?>
<?php if ($error) echo "<p style='color: red;'>$error</p>"; ?>

<!-- Form to add/edit admin -->
<h2><?php echo $admin_id ? 'Edit Admin' : 'Add New Admin'; ?></h2>
<form action="add_admin.php" method="POST">
    <input type="hidden" name="admin_id" value="<?php echo $admin_id; ?>">
    <input type="text" name="username" placeholder="Username" value="<?php echo $username; ?>" required>
    <input type="password" name="password" placeholder="Password" value="<?php echo $admin_id ? '' : ''; ?>" required>
    <input type="email" name="email" placeholder="Email" value="<?php echo $email; ?>" required>
    <button type="submit" name="<?php echo $admin_id ? 'edit' : 'add'; ?>" class="btn btn-success">
        <?php echo $admin_id ? 'Update Admin' : 'Add Admin'; ?>
    </button>
</form>





<!-- Admin List -->
<h2>Admin List</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $row['admin_id']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td>
                    <a href="add_admin.php?edit=<?php echo $row['admin_id']; ?>" class="btn btn-success">Edit</a>
                    <a href="add_admin.php?delete=<?php echo $row['admin_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this admin?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
