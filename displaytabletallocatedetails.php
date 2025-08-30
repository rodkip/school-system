<?php 
require_once("includes/dbconnection.php");

if (!empty($_POST["tabletserialno"])) {
    $tabletserialno = $_POST["tabletserialno"];    
    // Prepare the statement
    $stmt = $dbh->prepare("SELECT * FROM tabletsdetails WHERE tabletserialno=:tabletserialno");
    
    // Bind the parameter
    $stmt->bindParam(':tabletserialno', $tabletserialno, PDO::PARAM_STR);
    
    // Execute the query
    $stmt->execute();
    
    // Fetch the results
    $resultss = $stmt->fetchAll(PDO::FETCH_OBJ);
    
    // Check if there are any results
    if ($stmt->rowCount() > 0) {
        foreach ($resultss as $rww) {
            echo "<span style='color:green'>" . $rww->tabletserialno." - ". $rww->barcode." - ". $rww->type. "</span>";
            echo "<script>$('#submit').prop('disabled', true);</script>";          

        }
    } else {   
        echo "<span style='color:red'>No Tablet with the SerialNo</span>";        
        echo "<script>$('#tabletserialno').val(''); $('#submit').prop('disabled', false);</script>";
    }
}
?>
