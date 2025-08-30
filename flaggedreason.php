<?php
// Include the file with your database connection code
include('includes/dbconnection.php');

// Check if the IdNo parameter is present in the request
if (isset($_GET['idno'])) {
  $idno = $_GET['idno'];

  // Prepare and execute the query to fetch the flagged reason based on the IdNo
  $stmt = $dbh->prepare('SELECT flaggedreason FROM staffdetails WHERE idno = :idno');
  $stmt->bindParam(':idno', $idno);
  $stmt->execute();

  // Fetch the result
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  // Check if a result was found
  if ($result) {
    $flaggedReason = $result['flaggedreason'];
    echo $flaggedReason; // Return the flagged reason as the response
  } else {
    echo 'Flagged reason not found for the provided IdNo';
  }
} else {
  echo 'IdNo parameter is missing in the request';
}
?>
