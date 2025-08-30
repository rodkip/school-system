<?php
session_start();
error_reporting(0);
include('includes/dbconnection.php');
if (strlen($_SESSION['cpmsaid']==0)) {
  header('location:logout.php');
  } else{
    
?>
<?php
if (isset($_GET['idno'])) {
  $idno = $_GET['idno'];

  // Perform the database query to retrieve the flagged reason based on the idno
  $stmt = $dbh->prepare('SELECT flaggedreason FROM staffdetails WHERE idno = :idno');
  $stmt->bindParam(':idno', $idno, PDO::PARAM_STR);
  $stmt->execute();
  $result = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($result) {
    $flaggedReason = $result['flaggedreason'];
    echo $flaggedReason;
  } else {
    echo ''; // Return an empty string if no flagged reason is found
  }
}
?>

<?php }  ?>