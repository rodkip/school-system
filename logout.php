<?php
session_start();
include('includes/dbconnection.php');

// Check if 'username' is passed in the URL
if (isset($_GET['username'])) {
    $username = $_GET['username'];

    // Use prepared statements to prevent SQL injection
    $stmt = $dbh->prepare("UPDATE tbladmin SET status='offline' WHERE username = :username");
    $stmt->bindParam(':username', $username);
    $stmt->execute();
}

// Unset all session variables and destroy the session
session_unset();
session_destroy();

// Redirect to the index page
header('Location: index.php');
exit(); // Stop further script execution
?>
