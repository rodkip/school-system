<?php 
// DB credentials.
define('DB_HOST','localhost');
define('DB_USER','root');
define('DB_PASS','');
define('DB_NAME','elgonhills');
// Establish database connection.
try
{
$dbh = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME,DB_USER, DB_PASS,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
}
catch (PDOException $e)
{
exit("Error: " . $e->getMessage());
}
?>
<?php
       // Start with session to access user ID
       session_start();
        if (isset($_SESSION['timer']) && (time() - $_SESSION['timer'] > 60 * 20)) {
            // Inactivity exceeds 10 minutes
            session_unset();
            session_destroy();
            header('Location: index.php?status=loggedout');
            exit(); // Stop further execution after redirect
        } else {
            // User is still active, regenerate session ID and update timer
            session_regenerate_id(true); // Regenerate session ID to prevent fixation
            $_SESSION['timer'] = time(); // Reset the activity timer
        }
?>