<?php
session_start(); // Resume session to access stored key and expiration time

// Retrieve entered key from form submission
$enteredKey = isset($_POST['key']) ? $_POST['key'] : '';

// Check if verification key exists and has not expired
if (isset($_SESSION['verification_key']) && isset($_SESSION['verification_key_expiry'])) {
    $storedKey = $_SESSION['verification_key'];
    $expiryTime = $_SESSION['verification_key_expiry'];

    if (time() <= $expiryTime && $enteredKey === $storedKey) {
        // Valid key and within expiration time
        // Redirect to dashboard.php
        header("Location: dashboard.php");
        exit;
    }
}

// Invalid key or expired
// Redirect back to login page with error message
header("Location: login.php?error=invalid_key");
exit;
?>
