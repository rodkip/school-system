<?php
session_start();
require_once('includes/dbconnection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'check_parentname') {
    $response = ['exists' => false];
    
    if (!empty($_POST['parentname'])) {
        $parentname = trim($_POST['parentname']);
        
        try {
            $stmt = $dbh->prepare("SELECT COUNT(*) FROM parentdetails WHERE parentname = ?");
            $stmt->execute([$parentname]);
            $count = $stmt->fetchColumn();
            $response['exists'] = ($count > 0);
        } catch (PDOException $e) {
            $response['error'] = $e->getMessage();
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}