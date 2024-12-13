<?php
session_start();

// Destroy all session data
session_unset();
session_destroy();

// Clear "Remember Me" cookies by setting them to expire in the past
if (isset($_COOKIE['username']) && isset($_COOKIE['remember_token'])) {
    setcookie("username", "", time() - 3600, "/");
    setcookie("remember_token", "", time() - 3600, "/");
}

// Redirect to the login page
header("Location: login1.php");
exit();
?>
