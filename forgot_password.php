<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

require_once 'connect.php'; // Your database connection
require_once 'PHPMailer/src/Exception.php';
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $role = $_POST['role'];

    // Determine the user table based on the role
    $table = '';
    if ($role === 'admin') {
        $table = 'admin';
    } elseif ($role === 'employee') {
        $table = 'employee';
    } elseif ($role === 'supplier') {
        $table = 'supplier';
    } else {
        echo "Invalid role selected.";
        exit();
    }

    // Prepare the SQL statement
    $stmt = $con->prepare("SELECT * FROM `$table` WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if the user exists
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Check if the email is present in the user data
        if (!isset($user['email'])) {
            echo "Email not found for this user.";
            exit();
        }

        $newPassword = bin2hex(random_bytes(4)); // Generate a new random password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the user's password
        $updateStmt = $con->prepare("UPDATE `$table` SET password = ? WHERE username = ?");
        $updateStmt->bind_param("ss", $hashedPassword, $username);
        $updateStmt->execute();

        // Configure PHPMailer
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'inventorybot2024@gmail.com'; // Your Gmail address
            $mail->Password = 'Ikmal2000$'; // Your Gmail app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('inventorybot2024@gmail.com', 'Inventory Manager');
            $mail->addAddress($user['email']); // Recipient's email address

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Password Reset';
            $mail->Body    = "Hello, your new password is: <strong>$newPassword</strong>";

            // Send email
            $mail->send();
            echo "Password reset email has been sent!";
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        echo "User not found.";
    }
} else {
    // Display the password reset form
    echo '
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Forgot Password</title>
    </head>
    <body>
        <h1>Forgot Password</h1>
        <form action="forgot_password.php" method="POST">
            <input type="text" name="username" placeholder="Enter your username" required>
            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="employee">Employee</option>
                <option value="supplier">Supplier</option>
            </select>
            <button type="submit">Reset Password</button>
        </form>
    </body>
    </html>
    ';
}
?>
