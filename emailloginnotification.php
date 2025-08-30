$emailaddress = $result['emailaddress']; // Retrieve email address from database
            $fullnames = $result['fullnames']; // Retrieve email address from database
            $subject = 'Research PLUS Database Login';
                    // Get the current timestamp
            // Set the timezone to Nairobi, Kenya (East Africa Time)
            date_default_timezone_set('Africa/Nairobi');

            // Get the current timestamp with local timezone
            $currentTimestamp = date('Y-m-d, H:i:s');

            // Get the client's IP address
            $ipAddress = $_SERVER['REMOTE_ADDR'];

            // Construct the message including the current timestamp and IP address with HTML line breaks
            $message = "Hello,<br><br>" .
                    "This is to confirm that you have successfully logged into the Research PLUS Field team database system at <b>" . $currentTimestamp . "</b> from IP address: <b>" . $ipAddress . "</b>.<br><br>" .
                    "If you did not perform this action, please contact the system administrator immediately.<br><br>" .
                    "Thank you for using Research PLUS Field-Team Database!<br><br>" .
                    "Best regards,<br>" .
                    "Database Administrator.<br>".
                    "<i><b>Going the extra mile.</b></i>";

            include('email.php');

// reset all flagged staff with past flag end date
//$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
//$currentDate = date("Y-m-d");  // Current date
//$query = "UPDATE staffdetails SET flagged = 'None' WHERE flagenddate < :currentDate";
//$stmt = $dbh->prepare($query);
//$stmt->bindParam(':currentDate', $currentDate);
//$stmt->execute();
// End reset flag end date  

