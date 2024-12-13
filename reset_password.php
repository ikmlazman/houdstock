<?php 
require_once 'connect.php'; 
$errorMessage = "";
$successMessage = "";

if (isset($_GET['token']) && isset($_GET['role'])) {
    $token = $_GET['token'];
    $role = $_GET['role'];
    $table = ($role === 'admin') ? 'admin' : (($role === 'employee') ? 'employee' : 'supplier');

    $stmt = $con->prepare("SELECT * FROM `$table` WHERE reset_token=? AND token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        if (isset($_POST['reset_password'])) {
            $newPassword = password_hash($_POST['new_password'], PASSWORD_BCRYPT); // Hash the new password
            $stmt = $con->prepare("UPDATE `$table` SET password=?, reset_token=NULL, token_expiry=NULL WHERE reset_token=?");
            $stmt->bind_param("ss", $newPassword, $token);
            $stmt->execute();

            $successMessage = "Password reset successfully!";
        }
    } else {
        $errorMessage = "Invalid or expired token.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><title>Reset Password</title></head>
<body>
    <?php if ($errorMessage): ?>
        <p><?php echo $errorMessage; ?></p>
    <?php elseif ($successMessage): ?>
        <p><?php echo $successMessage; ?></p>
    <?php else: ?>
        <form action="" method="POST">
            <input type="password" name="new_password" placeholder="Enter new password" required>
            <button type="submit" name="reset_password">Reset Password</button>
        </form>
    <?php endif; ?>
</body>
</html>
