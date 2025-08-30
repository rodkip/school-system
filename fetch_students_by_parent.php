<?php
include('includes/dbconnection.php');

header('Content-Type: application/json');

$response = [
    'parentName' => '',
    'html' => ''
];

if(isset($_GET['idno']) && isset($_GET['role'])) {
    $idno = $_GET['idno'];
    $role = $_GET['role'];

    // Get parent name
    $parentQuery = $dbh->prepare("SELECT parentname FROM parentdetails WHERE idno = :idno LIMIT 1");
    $parentQuery->bindParam(':idno', $idno, PDO::PARAM_STR);
    $parentQuery->execute();
    $parent = $parentQuery->fetch(PDO::FETCH_OBJ);
    $response['parentName'] = $parent ? $parent->parentname : 'Unknown Parent';

    // Prepare main query
    $sql = "SELECT 
              sd.studentadmno, 
              sd.studentname,
              sd.motheridno, 
              sd.fatheridno, 
              sd.guardianidno,
              ce.gradefullname, 
              ce.stream
            FROM studentdetails sd
            LEFT JOIN classentries ce ON sd.studentadmno = ce.studentadmno
            WHERE ce.gradefullname = (
                SELECT MAX(gradefullname) FROM classentries 
                WHERE classentries.studentadmno = sd.studentadmno
            ) AND ";

    switch($role) {
        case 'mother':
            $sql .= "sd.motheridno = :idno";
            break;
        case 'father':
            $sql .= "sd.fatheridno = :idno";
            break;
        case 'guardian':
            $sql .= "sd.guardianidno = :idno";
            break;
    }

    $sql .= " ORDER BY sd.StudentName ASC";

    $query = $dbh->prepare($sql);
    $query->bindParam(':idno', $idno, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    $cnt = 1;
    if($query->rowCount() > 0) {
        foreach($results as $row) {
            $response['html'] .= '<tr>';
            $response['html'] .= '<td>'.$cnt.'</td>';
            $response['html'] .= '<td>'.htmlentities($row->studentadmno).'</td>';
            $response['html'] .= '<td>'.htmlentities($row->studentname).'</td>';
            $response['html'] .= '<td>'.htmlentities($row->gradefullname).'</td>';
            $response['html'] .= '<td>'.htmlentities($row->stream).'</td>';
            $response['html'] .= '</tr>';
            $cnt++;
        }
    } else {
        $response['html'] = '<tr><td colspan="5" class="text-center">No students found</td></tr>';
    }
} else {
    $response['html'] = '<tr><td colspan="5" class="text-center">Invalid request</td></tr>';
}

echo json_encode($response);
?>
