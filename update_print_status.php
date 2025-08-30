<?php
include('includes/dbconnection.php');

header('Content-Type: application/json');

if (isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $currentDateTime = date('Y-m-d H:i:s');
    
    try {
        $sql = "UPDATE feepayments SET printed = TRUE, print_date = :print_date WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->bindParam(':print_date', $currentDateTime);
        $query->execute();
        
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No ID provided']);
}
?>