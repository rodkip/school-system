<?php 
require_once("includes/dbconnection.php");

if (!empty($_POST["idno"])) {
    $idno = $_POST["idno"];
    
    // Prepare the statement
    $stmt = $dbh->prepare("SELECT * FROM staffdetails WHERE idno=:idno");
    
    // Bind the parameter
    $stmt->bindParam(':idno', $idno, PDO::PARAM_STR);
    
    // Execute the query
    $stmt->execute();
    
    // Fetch the results
    $results = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    // Check if there are any results
    if ($stmt->rowCount() > 0) {
        foreach ($results as $row) {
            echo "<span style='color:red'>".$row->idno. " is registered under:" . $row->staffname . "</span>";
            
            echo "<script>$('#idno').val(''); $('#submit').prop('disabled', false);</script>";

        }
    } else {   
        echo "<span style='color:green'>No Staff with the IdNo</span>";
        echo "<script>$('#submit').prop('disabled', true);</script>";
    }
}
?>
