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
$name = '';
$username = '';
$email = '';
$phone = '';
$position = '';
$date_of_hire = '';
$employee_id = '';

// Handle the CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add'])) {
        // Add new employee
        $name = mysqli_real_escape_string($con, $_POST['name']);
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $phone = mysqli_real_escape_string($con, $_POST['phone']);
        $position = mysqli_real_escape_string($con, $_POST['position']);
        $date_of_hire = mysqli_real_escape_string($con, $_POST['date_of_hire']);

        if (empty($name) || empty($username) || empty($password) || empty($email) || empty($position)) {
            $error = "All fields are required.";
        } else {
            $sql = "INSERT INTO employee (name, username, password, email, phone, position, date_of_hire) 
                    VALUES ('$name', '$username', '$password', '$email', '$phone', '$position', '$date_of_hire')";
            if (mysqli_query($con, $sql)) {
                $success = "New employee added successfully!";
                // Clear the form fields
                $name = $username = $email = $phone = $position = $date_of_hire = '';
            } else {
                $error = "Error: " . mysqli_error($con);
            }
        }
    } elseif (isset($_POST['edit'])) {
        // Update existing employee
        $employee_id = mysqli_real_escape_string($con, $_POST['employee_id']);
        $name = mysqli_real_escape_string($con, $_POST['name']);
        $username = mysqli_real_escape_string($con, $_POST['username']);
        $password = mysqli_real_escape_string($con, $_POST['password']);
        $email = mysqli_real_escape_string($con, $_POST['email']);
        $phone = mysqli_real_escape_string($con, $_POST['phone']);
        $position = mysqli_real_escape_string($con, $_POST['position']);
        $date_of_hire = mysqli_real_escape_string($con, $_POST['date_of_hire']);

        $sql = "UPDATE employee SET name='$name', username='$username', password='$password', email='$email', 
                phone='$phone', position='$position', date_of_hire='$date_of_hire' 
                WHERE employee_id='$employee_id'";
        if (mysqli_query($con, $sql)) {
            $success = "Employee updated successfully!";
            // Clear the form fields
            $name = $username = $email = $phone = $position = $date_of_hire = '';
            $employee_id = '';
        } else {
            $error = "Error: " . mysqli_error($con);
        }
    }
} elseif (isset($_GET['delete'])) {
    // Delete employee
    $employee_id = mysqli_real_escape_string($con, $_GET['delete']);
    $sql = "DELETE FROM employee WHERE employee_id='$employee_id'";
    if (mysqli_query($con, $sql)) {
        header('Location: add_employee.php'); // Refresh the page to reflect changes
        exit();
    } else {
        $error = "Error deleting record: " . mysqli_error($con);
    }
} elseif (isset($_GET['edit'])) {
    // Fetch employee details for editing
    $employee_id = mysqli_real_escape_string($con, $_GET['edit']);
    $sql = "SELECT * FROM employee WHERE employee_id='$employee_id'";
    $result = mysqli_query($con, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $name = $row['name'];
        $username = $row['username'];
        $email = $row['email'];
        $phone = $row['phone'];
        $position = $row['position'];
        $date_of_hire = $row['date_of_hire'];
    }
}

// Fetch all employees from the database for display
$sql = "SELECT * FROM employee";
$result = mysqli_query($con, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Employees</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f2f2f2;
        }
        h1, h2 {
            color: #333;
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
            padding: 8px 15px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.3s;
        }
        .btn-success {
            background-color: green;
        }
        .btn-danger {
            background-color: red;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
        }
        .form-container input, .form-container button {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .form-container button {
            background-color: green;
            color: white;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: darkgreen;
        }
        .back-btn {
            padding: 8px 15px;
            background-color: #ccc;
            text-decoration: none;
            color: black;
            border-radius: 5px;
            margin-top: 20px;
            display: inline-block;
        }
        .back-btn:hover {
            background-color: #aaa;
        }
    </style>
</head>
<body>

<h1>Manage Employees</h1>

<!-- Display success/error messages -->
<?php if ($success) echo "<p style='color: green;'>$success</p>"; ?>
<?php if ($error) echo "<p style='color: red;'>$error</p>"; ?>

<!-- Form to add/edit employee -->
<div class="form-container">
    <h2><?php echo $employee_id ? 'Edit Employee' : 'Add New Employee'; ?></h2>
    <form action="add_employee.php" method="POST">
        <input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>">
        <input type="text" name="name" placeholder="Full Name" value="<?php echo $name; ?>" required>
        <input type="text" name="username" placeholder="Username" value="<?php echo $username; ?>" required>
        <input type="password" name="password" placeholder="Password" value="<?php echo $employee_id ? '' : ''; ?>" required>
        <input type="email" name="email" placeholder="Email" value="<?php echo $email; ?>" required>
        <input type="text" name="phone" placeholder="Phone" value="<?php echo $phone; ?>">
        <input type="text" name="position" placeholder="Position" value="<?php echo $position; ?>" required>
        <input type="date" name="date_of_hire" value="<?php echo $date_of_hire; ?>" required>
        <button type="submit" name="<?php echo $employee_id ? 'edit' : 'add'; ?>" class="btn btn-success">
            <?php echo $employee_id ? 'Update Employee' : 'Add Employee'; ?>
        </button>
    </form>
</div>

<!-- Employee List -->
<h2>Employee List</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Position</th>
            <th>Date of Hire</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td><?php echo $row['employee_id']; ?></td>
                <td><?php echo $row['name']; ?></td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['email']; ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td><?php echo $row['position']; ?></td>
                <td><?php echo $row['date_of_hire']; ?></td>
                <td>
                    <a href="add_employee.php?edit=<?php echo $row['employee_id']; ?>" class="btn btn-success">Edit</a>
                    <a href="add_employee.php?delete=<?php echo $row['employee_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this employee?');">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<a href="admin_dashboard.php" class="back-btn">Back to Dashboard</a>

</body>
</html>
